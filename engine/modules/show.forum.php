<?php
/*
=============================================
 Name      : MWS SimpleBB v2.2
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : http://dle.net.tr/   (c) 2017
 License   : MIT License
=============================================
*/

if ( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


class SimpleBB {
	private $config = Null;
	private $db = Null;
	private $tpl = Null;
	private $cats = Null;
	private $groups = Null;
	private $member = Null;
	private $ON = Null;
	private $OFF = Null;
	private $html = "";
	private $sett = array();
	private $tpls = array();
	private $_main_cats = array();
	private $_main_cat_ids = array();
	private $_main_forums = array();
	private $_forum_cat_ids = array();
	private $_forum_ids = array();
	private $lastpost = array();
	private $comments = array();
	public $bbname = "";

	public function SimpleBB( &$config, &$db, &$tpl, &$cat_info, &$user_groups, &$member_id ) {
		include ENGINE_DIR . "/data/simplebb.conf.php";
		$this->config = $config;
		$this->sett = $sbbsett;
		$this->db = $db;
		$this->tpl = $tpl;
		$this->cats = $cat_info;
		$this->groups = $user_groups;
		$this->member = $member_id;
		$this->ON = ( $this->config['version_id'] > "10.1" ) ? "1" : "yes";
		$this->OFF = ( $this->config['version_id'] > "10.1" ) ? "0" : "no";
		$this->bbname = $this->cats[ $this->sett['id'] ]['alt_name'];
		$this->sett['avatarsize'] = "100";
	}

	private function FindMainCats( ) {
		foreach( $this->cats as $cat ) {
			if ( $cat['parentid'] == $this->sett['id'] ) {
				$this->_main_cats[ $cat['id'] ] = $cat;
			}
		}
		$this->_main_cat_ids = array_keys( $this->_main_cats );
		foreach( $this->_main_cat_ids as $main_cat_id ) {
			foreach( $this->cats as $main_forum ) {
				if ( $main_forum['parentid'] == $main_cat_id ) {
					$this->_forum_ids[] = $main_forum['id'];
				}
			}
		}
		$this->_forum_ids = array_unique( $this->_forum_ids );
	}

	private function FindMainForums( ) {
		$this->GetForumInfos( );
		foreach( $this->_main_cat_ids as $main_cat_id ) {
			$this->_forum_cat_ids[] = $main_cat_id;
			$this->_main_forums[ $main_cat_id ] = array();
			foreach( $this->cats as $main_forum ) {
				if ( $main_forum['parentid'] == $main_cat_id ) {
					$this->_main_forums[ $main_cat_id ][ $main_forum['id'] ] = $this->GetForumInfo( $main_forum['id'] );
					$this->_forum_cat_ids[] = $main_forum['id'];
				}
			}
		}
	}

	private function GenerateStats( ) {
		$where = implode( ",", $this->_forum_cat_ids );
		if ( count( $this->_forum_cat_ids ) > 0 ) {
			$row = $this->db->super_query( "SELECT title, autor, id, alt_name, date, category FROM " . PREFIX . "_post WHERE category IN ({$where}) ORDER BY date DESC LIMIT 0,1" );
			$_p = $row; unset( $row );
			$this->db->query( "SELECT COUNT(id) as count, approve FROM " . PREFIX . "_post WHERE category IN ({$where}) GROUP BY approve" );
			while( $row = $this->db->get_row() ) {
				if ( $row['approve'] == "1" ) $_p['ocount'] = $row['count'];
				else $_p['ncount'] = $row['count'];
			}
			$_p['tcount'] = $_p['ncount'] + $_p['ocount'];
			$row = $this->db->super_query( "SELECT COUNT(c.id) as count FROM " . PREFIX . "_comments as c LEFT JOIN " . PREFIX . "_post as p ON p.id = c.post_id WHERE p.category IN ({$where}) AND c.approve = '1'" );
			$_p['ccount'] = $row['count'];
			unset( $row, $where );
		} else $_p = array();
		return $_p;
	}

	private function CreateHTML( ) {
		foreach( $this->_main_forums as $cat_id => $forum_ids ) {
			$_cat = $this->GetCategoryInfo( $cat_id );
			$_forum_html = "";
			foreach( $forum_ids as $forum_id => $forum ) {
				$_forum = $this->GetCategoryInfo( $forum_id, $is_forum = True );
				$_forum_html .= $this->_ForumTemplate( $_cat, $_forum, $forum );
			}
			$_cat_html .= $this->_CategoryTemplate( $_cat, $_forum_html );
		}
		$_stats = $this->GenerateStats( );
		$_stats_html = $this->_StatTemplate( $_stats );
		$_main_html = $this->_MainTemplate( $_cat_html, $_stats_html );
		$this->html = $_main_html;
	}

	private function _StatTemplate( $_post ) {
		$template = $this->tpls['stats'];
		$_post['upage'] = ( $this->config['allow_alt_url'] == $this->ON ) ? $this->config['http_home_url'] . "user/" . urlencode( $_post['autor'] ) . "/" : $PHP_SELF . "?subaction=userinfo&user=" . urlencode( $_post['autor'] );
		$_post['url'] = ( $this->config['allow_alt_url'] == $this->ON ) ? $this->config['http_home_url'] . $this->bbname . "/" . $this->cats[ $this->cats[ $_post['category'] ]['parentid'] ]['alt_name'] . "/" . $this->cats[ $_post['category'] ]['alt_name'] . "/" . $_post['id'] . "-" . $_post['alt_name'] . ".html" : $this->config['http_home_url'] . "index.php?newsid=" . $_post['id'];
		$template = str_replace( "{posts}", $_post['tcount'], $template );
		$template = str_replace( "{posts-ok}", intval( $_post['ocount'] ), $template );
		$template = str_replace( "{posts-no}", intval( $_post['ncount'] ), $template );
		$template = str_replace( "{comments}", intval( $_post['ccount'] ), $template );
		$template = str_replace( "{date}", $_post['date'], $template );
		$template = str_replace( "{title}", $this->_SubSTR( $this->_NormalName( $_post['title'] ), $this->sett['stat_title_limit'] ), $template );
		$template = str_replace( "{author}", $_post['autor'], $template );
		$template = str_replace( "{author-link}", $_post['upage'], $template );
		$template = str_replace( "{author-box}", "<a onclick=\"ShowProfile('" . urlencode( $_post['autor'] ) . "', '" . $_post['upage'] . "', '" . $this->groups[$this->member['user_group']]['admin_editusers'] . "'); return false;\" href=\"" . $user_page . "\">", $template );
		$template = str_replace( "{url}", $_post['url'], $template );
		return $template;
	}

	private function _ForumTemplate( $_cat, $_forum, $forum ) {
		$template = $this->tpls['forum'];
		$_forum['url'] = ( $this->config['allow_alt_url'] == $this->ON ) ? $this->config['http_home_url'] . $this->bbname . "/" . $_cat['alt_name'] . "/" . $_forum['alt_name'] . "/" : $this->config['http_home_url'] . "index.php?do=cat&category=" . $_forum['alt_name'];
		$_forum['rlink'] = ( $this->config['allow_alt_url'] == $this->ON ) ? $this->config['http_home_url'] . $this->bbname . "/" . $_cat['alt_name'] . "/" . $_forum['alt_name'] . "/rss.xml" : $this->config['http_home_url'] . "engine/rss.php?do=cat&category=" . $_forum['alt_name'];
		$_forum['lpost'] = $this->_SubSTR( $this->_NormalName( $forum['lastpost'] ), $this->sett['post_limit'] );
		$_forum['upage'] = ( $this->config['allow_alt_url'] == $this->ON ) ? $this->config['http_home_url'] . "user/" . urlencode( $forum['lastposter'] ) . "/" : $PHP_SELF . "?subaction=userinfo&user=" . urlencode( $forum['lastposter'] );
		$_forum['lurl'] = ( $this->config['allow_alt_url'] == $this->ON ) ? $this->config['http_home_url'] . $this->bbname . "/" . $_cat['alt_name'] . "/" . $_forum['alt_name'] . "/" . $forum['post_id'] . "-" . $forum['url'] . ".html" : $this->config['http_home_url'] . "index.php?newsid=" . $forum['post_id'];
		$template = str_replace( "{title}", $this->_SubSTR( $this->_NormalName( $_forum['name'] ), $this->sett['title_limit'] ), $template );
		$template = str_replace( "{url}", $_forum['url'], $template );
		$template = str_replace( "{rss-link}", $_forum['rlink'], $template );
		$template = str_replace( "[link]", "<a href=\"" . $_forum['url'] . "\">", $template );
		$template = str_replace( "[/link]", "</a>", $template );
		$template = str_replace( "{metatitle}", $_forum['metatitle'], $template );
		$template = str_replace( "{icon}", ( empty( $_forum['icon'] ) ) ? "envelope" : $_forum['icon'], $template );
		$template = str_replace( "{desc}", $_forum['descr'], $template );
		$template = str_replace( "{posts}", intval( $forum['posts'] ), $template );
		$template = str_replace( "{comments}", intval( $_forum['comm'] ), $template );
		if ( empty( $forum['lastposter'] ) ) {
			$template = preg_replace( "'\\[not-lastpost\\](.*?)\\[/not-lastpost\\]'si", "$1", $template );
			$template = preg_replace( "'\\[lastpost\\](.*?)\\[/lastpost\\]'si", "", $template );
		} else {
			$template = str_replace( "{lastposter}", $forum['lastposter'], $template );
			$template = str_replace( "{lastposter-url}", $_forum['upage'], $template );
			$template = str_replace( "{lastposter-foto}", $forum['avatar'], $template );
			$template = str_replace( "{lastposter-link}", "<a onclick=\"ShowProfile('" . urlencode( $forum['lastposter'] ) . "', '" . $_forum['upage'] . "', '" . $this->groups[$this->member['user_group']]['admin_editusers'] . "'); return false;\" href=\"" . $_forum['upage'] . "\">" . $forum['lastposter'] . "</a>", $template );
			$template = str_replace( "{lastposter-box}", "onclick=\"ShowProfile('" . urlencode( $forum['lastposter'] ) . "', '" . $_forum['upage'] . "', '" . $this->groups[$this->member['user_group']]['admin_editusers'] . "'); return false;\"", $template );
			$template = str_replace( "{lastpost}", $_forum['lpost'], $template );
			$template = str_replace( "{lastpost-url}", $_forum['lurl'], $template );
			$template = str_replace( "{lastpost-link}", "<a href=\"" . $_forum['lurl'] . "\">" . $_forum['lpost'] . "</a>", $template );
			$template = str_replace( "{lastpost-date}", $forum['date'], $template );
			$template = preg_replace( "'\\[not-lastpost\\](.*?)\\[/not-lastpost\\]'si", "", $template );
			$template = preg_replace( "'\\[lastpost\\](.*?)\\[/lastpost\\]'si", "$1", $template );
		}
		return $template;
	}

	private function _CategoryTemplate( $_cat, $_forum_html ) {
		$template = $this->tpls['category'];
		$_cat['url'] = ( $this->config['allow_alt_url'] == $this->ON ) ? $this->config['http_home_url'] . $this->bbname . "/" . $_cat['alt_name'] . "/" : $this->config['http_home_url'] . "index.php?do=cat&category=" . $_cat['alt_name'];
		$template = str_replace( "{title}", $_cat['name'], $template );
		$template = str_replace( "{metatitle}", $_cat['metatitle'], $template );
		$template = str_replace( "{desc}", $_cat['descr'], $template );
		$template = str_replace( "{icon}", $_cat['icon'], $template );
		$template = str_replace( "{url}", $_cat['url'], $template );
		$template = str_replace( "[link]", "<a href=\"" . $_cat['url'] . "\">", $template );
		$template = str_replace( "[/link]", "</a>", $template );
		$template = str_replace( "{forums}", $_forum_html, $template );
		return $template;
	}

	private function _MainTemplate( $_cat_html, $_stats_html ) {
		$template = $this->tpls['main'];
		$template = preg_replace( "#\\[categories\\](.*?)\\[/categories\\]#is", $_cat_html, $template );
		$template = preg_replace( "#\\[depth=1\\](.*?)\\[/depth=1\\]#is", "$1", $template );
		$template = preg_replace( "#\\[depth=2\\](.*?)\\[/depth=2\\]#is", "", $template );
		$template = preg_replace( "#\\[depth=3\\](.*?)\\[/depth=3\\]#is", "", $template );
		$template = preg_replace( "#\\[depth=4\\](.*?)\\[/depth=4\\]#is", "", $template );
		$template = str_replace( "{forum-stats}", $_stats_html, $template );

		if ( $this->config['allow_banner'] && $this->sett['show_banners'] && stripos( $template, "{banner" ) !== false ) {
			$db = &$this->db;
			include_once ENGINE_DIR . '/modules/banners.php';
			if ( count ( $banners ) ) {
				foreach ( $banners as $name => $value ) {
					$template = str_replace( "{banner_" . $name . "}", $value, $template );
					if ( $value ) {
						$template = str_replace( "[banner_" . $name . "]", "", $template );
						$template = str_replace( "[/banner_" . $name . "]", "", $template );
					}
				}
			}
			$template = preg_replace( "'{banner_(.*?)}'si", "", $template );
			$template = preg_replace( "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", "", $template );
		}

		if ( stripos( $template, "{custom" ) !== false ) {
			$template = preg_replace_callback ( "#\\{custom(.+?)\\}#i", "custom_print", $template );
		}
		return $template;
	}

	private function _NormalName( $text ) {
		return htmlspecialchars( strip_tags( stripslashes( $text ) ), ENT_QUOTES, $this->config['charset'] );
	}

	private function _SubSTR( $text, $limit ) {
		if ( empty( $limit ) OR $limit == "0" ) {
			return $text;
		} else {
			return dle_substr( $text, 0, $limit, $this->config['charset'] );
		}

	}

	private function GetCategoryInfo( $cat_id, $is_forum = False ) {
		$data = $this->cats[ $cat_id ];
		if ( $is_forum ) {
			$data['comm'] = $this->comments[ $cat_id ];
		}
		return $data;
	}

	private function GetForumInfo( $cat_id ) {
		$result = $this->lastpost[ $cat_id ];
		return $result;
	}

	private function GetForumInfos( ) {
		if ( count( $this->_forum_ids ) > 0 ) {
			$where = implode( ",", $this->_forum_ids );
			$this->db->query("SELECT COUNT(p.id) as posts, p.title, p.autor, p.id, p.comm_num, p.alt_name, p.date, p.category, u.foto FROM (SELECT title, autor, id, comm_num, alt_name, date, category, approve FROM " . PREFIX . "_post ORDER BY date DESC) p LEFT JOIN " . PREFIX . "_users u ON u.name = p.autor WHERE p.category IN ({$where}) AND p.approve = '1' GROUP BY p.category");
			while( $data = $this->db->get_row() ) {
				if ( strpos( $data['foto'], "@" ) !== false ) { $data['foto'] = "http://www.gravatar.com/avatar/" . md5( trim( $data['foto'] ) ) . "?s=" . intval( $this->sett['avatarsize'] ); } else if ( empty( $data['foto'] ) ) { $data['foto'] = $this->config['http_home_url'] . "templates/" . $this->config['skin'] . "/dleimages/noavatar.png"; }
				$this->lastpost[ $data['category'] ] = array(
					"lastpost" 		=> $data['title'],
					"lastposter" 	=> $data['autor'],
					"comments" 		=> $data['comm_num'],
					"url" 			=> $data['alt_name'],
					"post_id" 		=> $data['id'],
					"date" 			=> $data['date'],
					"posts" 		=> $data['posts'],
					"avatar"		=> $data['foto']
				);
			}
			$this->db->free();
		}
	}

	private function FindComments( ) {
		if ( count( $this->_forum_ids ) > 0 ) {
			$where = implode( ",", $this->_forum_ids );
			$this->db->query("SELECT SUM(comm_num) as comm, category FROM " . PREFIX . "_post WHERE category IN ({$where}) AND approve = '1' GROUP BY category" );
			while( $data = $this->db->get_row() ) {
				$this->comments[ $data['category'] ] = $data['comm'];
			}
		}
	}

	private function LoadTemplates( ) {
		$this->tpl->load_template( "forum/stats.tpl" );
		$this->tpls['stats'] = $this->tpl->copy_template;
		$this->tpl->load_template( "forum/main.tpl" );
		$this->tpls['main'] = $this->tpl->copy_template;
		preg_match_all( "#\\[forums\\](.*?)\\[/forums\\]#is", $this->tpls['main'], $matches );
		$this->tpls['forum'] = $matches[1][0];
		preg_match_all( "#\\[categories\\](.*?)\\[/categories\\]#is", $this->tpls['main'], $matches );
		$matches[1][0] = preg_replace( "#\\[forums\\](.*?)\\[/forums\\]#is", "{forums}", $matches[1][0] );
		$this->tpls['category'] = $matches[1][0];
		$this->tpl->clear();
	}

	public function run( ) {
		$this->LoadTemplates();
		$this->FindMainCats();
		$this->FindMainForums();
		$this->FindComments();
		$this->CreateHTML();
	}

	public function get_forums( ) {
		$this->FindMainCats();
		return $this->_forum_ids;
	}

	public function get_cats( ) {
		$this->FindMainCats();
		return array_keys( $this->_main_cats );
	}

	public function get_all( ) {
		$this->FindMainCats();
		return array_merge( $this->_forum_ids, array_keys( $this->_main_cats ) );
	}

	public function get_stats( ) {
		$this->LoadTemplates();
		$this->FindMainCats();
		$this->FindMainForums();
		$_stats = $this->GenerateStats( );
		return $this->_StatTemplate( $_stats );
	}

	public function html( ) {
		return $this->html;
	}

}

?>
