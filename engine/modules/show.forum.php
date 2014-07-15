<?php
/*
=====================================================
 MWS SimpleBB Forum v1.0 - Mehmet Hanoğlu
-----------------------------------------------------
 http://dle.net.tr/ -  Copyright (c) 2014
-----------------------------------------------------
 Mail: m.hanoglu55@gmail.com
-----------------------------------------------------
 Lisans : GPL License
-----------------------------------------------------
 Tarih : 15.07.2014
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
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
	private $tpls = array();
	private $_main_cats = array();
	private $_main_cat_ids = array();
	private $_main_forums = array();
	private $_forum_cat_ids = array();
	private $_forum_ids = array();
	private $lastpost = array();
	private $comments = array();
	public $bbname = "";
	public $optimize = "";

	public function SimpleBB( &$config, &$db, &$tpl, &$cat_info, &$user_groups, &$member_id ) {
		$this->config = $config;
		$this->db = $db;
		$this->tpl = $tpl;
		$this->cats = $cat_info;
		$this->groups = $user_groups;
		$this->member = $member_id;
		$this->ON = ( $this->config['version_id'] > "10.1" ) ? "1" : "yes";
		$this->OFF = ( $this->config['version_id'] > "10.1" ) ? "0" : "no";
		$this->bbname = $this->cats[ $this->config['forum_id'] ]['alt_name'];
		$this->optimize = $this->config['forum_optimized_sql'];
	}

	private function FindMainCats( ) {
		foreach( $this->cats as $cat ) {
			if ( $cat['parentid'] == $this->config['forum_id'] ) {
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
		if ( $this->optimize == $this->ON ) { $this->GetForumInfos( ); }
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
			if ( $this->optimize == $this->ON ) {
				$this->db->query( "SELECT COUNT(id) as count, approve FROM " . PREFIX . "_post WHERE category IN ({$where}) GROUP BY approve" );
				while( $row = $this->db->get_row() ) {
					if ( $row['approve'] == "1" ) $_p['ocount'] = $row['count'];
					else $_p['ncount'] = $row['count'];
				}
				$_p['tcount'] = $_p['ncount'] + $_p['ocount'];
				$row = $this->db->super_query( "SELECT COUNT(c.id) as count FROM " . PREFIX . "_comments as c LEFT JOIN " . PREFIX . "_post as p ON p.id = c.post_id WHERE p.category IN ({$where}) AND c.approve = '1'" );
				$_p['ccount'] = $row['count'];
			} else {
				$row = $this->db->super_query( "SELECT COUNT(id) as count FROM " . PREFIX . "_post WHERE category IN ({$where})" );
				$_p['tcount'] = $row['count'];
				$row = $this->db->super_query( "SELECT COUNT(id) as count FROM " . PREFIX . "_post WHERE approve ='1' AND category IN ({$where})" );
				$_p['ocount'] = $row['count'];
				$row = $this->db->super_query( "SELECT COUNT(c.id) as count FROM " . PREFIX . "_comments as c LEFT JOIN " . PREFIX . "_post as p ON p.id = c.post_id WHERE p.category IN ({$where}) AND c.approve = '1'" );
				$_p['ccount'] = $row['count'];
				$_p['ncount'] = $_p['tcount'] - $_p['ocount'];
			}
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
		$template = str_replace( "{title}", dle_substr( $this->_NormalName( $_post['title'] ), 0, $this->config['forum_stat_title_limit'], $this->config['charset'] ), $template );
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
		$_forum['lpost'] = dle_substr( $this->_NormalName( $forum['lastpost'] ), 0, $this->config['forum_post_limit'], $this->config['charset'] );
		$_forum['upage'] = ( $this->config['allow_alt_url'] == $this->ON ) ? $this->config['http_home_url'] . "user/" . urlencode( $forum['lastposter'] ) . "/" : $PHP_SELF . "?subaction=userinfo&user=" . urlencode( $forum['lastposter'] );
		$_forum['lurl'] = ( $this->config['allow_alt_url'] == $this->ON ) ? $this->config['http_home_url'] . $this->bbname . "/" . $_cat['alt_name'] . "/" . $_forum['alt_name'] . "/" . $forum['post_id'] . "-" . $forum['url'] . ".html" : $this->config['http_home_url'] . "index.php?newsid=" . $forum['post_id'];
		$template = str_replace( "{title}", dle_substr( $this->_NormalName( $_forum['name'] ), 0, $this->config['forum_title_limit'], $this->config['charset'] ), $template );
		$template = str_replace( "{url}", $_forum['url'], $template );
		$template = str_replace( "{rss-link}", $_forum['rlink'], $template );
		$template = str_replace( "{lastpost}", $_forum['lpost'], $template );
		$template = str_replace( "[link]", "<a href=\"" . $_forum['url'] . "\">", $template );
		$template = str_replace( "[/link]", "</a>", $template );
		$template = str_replace( "{lastpost-url}", $_forum['lurl'], $template );
		$template = str_replace( "{lastpost-link}", "<a href=\"" . $_forum['lurl'] . "\">" . $_forum['lpost'] . "</a>", $template );
		$template = str_replace( "{lastpost-date}", $forum['date'], $template );
		$template = str_replace( "{lastposter}", $forum['lastposter'], $template );
		$template = str_replace( "{metatitle}", $_forum['metatitle'], $template );
		$template = str_replace( "{icon}", $_forum['icon'], $template );
		$template = str_replace( "{desc}", $forum['name'], $template );
		$template = str_replace( "{posts}", intval( $forum['posts'] ), $template );
		$template = str_replace( "{comments}", intval( $_forum['comm'] ), $template );
		$template = str_replace( "{lastposter-url}", $_forum['upage'], $template );
		$template = str_replace( "{lastposter-link}", "<a onclick=\"ShowProfile('" . urlencode( $forum['lastposter'] ) . "', '" . $_forum['upage'] . "', '" . $this->groups[$this->member['user_group']]['admin_editusers'] . "'); return false;\" href=\"" . $_forum['upage'] . "\">" . $forum['lastposter'] . "</a>", $template );
		$template = str_replace( "{lastposter-box}", "onclick=\"ShowProfile('" . urlencode( $forum['lastposter'] ) . "', '" . $_forum['upage'] . "', '" . $this->groups[$this->member['user_group']]['admin_editusers'] . "'); return false;\"", $template );
		if ( empty( $forum['lastposter'] ) ) {
			$template = preg_replace( "'\\[not-lastpost\\](.*?)\\[/not-lastpost\\]'si", "$1", $template );
			$template = preg_replace( "'\\[lastpost\\](.*?)\\[/lastpost\\]'si", "", $template );
		} else {
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
		$template = str_replace( "{categories}", $_cat_html, $template );
		$template = str_replace( "{forum-stats}", $_stats_html, $template );

		if ( $this->config['allow_banner'] && $this->config['forum_show_banners'] && stripos( $template, "{banner" ) !== false ) {
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

	private function GetCategoryInfo( $cat_id, $is_forum = False ) {
		if ( $this->optimize == $this->ON ) {
			$data = $this->cats[ $cat_id ];
		} else {
			$data = $this->db->super_query("SELECT * FROM " . PREFIX . "_category WHERE id = '{$cat_id}'" );
		}
		if ( $is_forum ) {
			$data['comm'] = $this->comments[ $cat_id ];
		}
		return $data;
	}

	private function GetForumInfo( $cat_id ) {
		if ( $this->optimize == $this->ON ) {
			$result = $this->lastpost[ $cat_id ];
		} else {
			$data = $this->db->super_query("SELECT title, autor, id, comm_num, alt_name, date FROM " . PREFIX . "_post WHERE category = '{$cat_id}' AND approve = '1' ORDER BY date DESC" );
			$result = array( 
				"lastpost" 		=> $data['title'],
				"lastposter" 	=> $data['autor'],
				"comments" 		=> $data['comm_num'],
				"url" 			=> $data['alt_name'],
				"post_id" 		=> $data['id'],
				"date" 			=> $data['date']
			);
			$data = $this->db->super_query("SELECT COUNT(id) as posts FROM " . PREFIX . "_post WHERE category = '{$cat_id}' AND approve = '1'");
			$result['posts'] = $data['posts'];
			$this->db->free();
		}
		return $result;
	}

	private function GetForumInfos( ) {
		if ( count( $this->_forum_ids ) > 0 ) {
			$where = implode( ",", $this->_forum_ids );
			$this->db->query("SELECT COUNT(id) as posts, title, autor, id, comm_num, alt_name, date, category FROM " . PREFIX . "_post WHERE category IN ({$where}) AND approve = '1' GROUP BY category" );
			while( $data = $this->db->get_row() ) {
				$this->lastpost[ $data['category'] ] = array( 
					"lastpost" 		=> $data['title'],
					"lastposter" 	=> $data['autor'],
					"comments" 		=> $data['comm_num'],
					"url" 			=> $data['alt_name'],
					"post_id" 		=> $data['id'],
					"date" 			=> $data['date'],
					"posts" 		=> $data['posts']
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
		$this->tpl->load_template( "forum/category.tpl" );
		$this->tpls['category'] = $this->tpl->copy_template;
		$this->tpl->load_template( "forum/forum.tpl" );
		$this->tpls['forum'] = $this->tpl->copy_template;
		$this->tpl->load_template( "forum/main.tpl" );
		$this->tpls['main'] = $this->tpl->copy_template;
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
