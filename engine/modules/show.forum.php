<?php
/*
=============================================
 Name      : MWS SimpleBB v2.3
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : https://dle.net.tr/
 License   : MIT License
 Date      : 05.02.2018
=============================================
*/

if ( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

require_once ENGINE_DIR . "/data/simplebb.conf.php";

if ( $dle_module == "main" ) $forum_where = "";

if ( $forum_compile == "before" ) {

	$forum_main_tpl = file_get_contents( ROOT_DIR . "/templates/" . $config['skin'] . "/forum/main.tpl" );

	if ( preg_match("#<meta property=\"og:title\" content=\"(.*?)\">#is", $metatags, $title ) ) {
		$forum_main_tpl = str_replace('{thread-title}', trim( $title[1] ), $forum_main_tpl );
	}
	$forum_main_tpl = str_replace('{thread-title}', "", $forum_main_tpl );
	$forum_main_tpl = str_replace('{count_all}', intval( $count_all ), $forum_main_tpl );

	if ( $forum_where == "cat" ) {
		$forum_main_tpl = preg_replace( "#\\[depth=1\\](.*?)\\[/depth=1\\]#is", "", $forum_main_tpl );
		$forum_main_tpl = preg_replace( "#\\[depth=2\\](.*?)\\[/depth=2\\]#is", "$1", $forum_main_tpl );
		$forum_main_tpl = preg_replace( "#\\[depth=3\\](.*?)\\[/depth=3\\]#is", "", $forum_main_tpl );
		$forum_main_tpl = preg_replace( "#\\[depth=4\\](.*?)\\[/depth=4\\]#is", "", $forum_main_tpl );

		if ( isset( $category_id ) && $sbbsett['show_subforums'] ) {
			$subforums = "";
			foreach ( $cat_info as $cid => $cat ) { if ( $cat['parentid'] == $category_id ) { $subcats[] = $cat['id']; } }
			if ( count( $subcats ) > 0 && $sbbsett['show_subcount'] ) {
				$subcounts = array();
				$db->query( "SELECT COUNT(id) as count, category FROM " . PREFIX . "_post WHERE category IN(" . implode( ",", $subcats ) . ") GROUP BY category" );
				while( $d = $db->get_row() ) {
					$subcounts[ $d['category'] ] = $d['count'];
				}
				$db->free();
			}
			$main_host = str_replace( $cat_info[ $sbbsett['id'] ]['alt_name'] . ".", "", $_SERVER['HTTP_HOST'] );
			foreach ( $subcats as $cid ) {
				$parent = $cat_info[ $cat_info[ $cid ]['parentid'] ];
				if ( $sbbsett['use_subdomain'] ) {
					$_furl = ( $config['allow_alt_url'] == "1" ) ? $parent['alt_name'] . "/" . $cat_info[$cid]['alt_name'] . "/" : "index.php?do=cat&category=" . $cat_info[$cid]['alt_name'];
					$http_prefix = ( $config['only_ssl'] ) ? "https://" : "http://";
					$subforums .= "<li><a href=\"{$http_prefix}{$cat_info[ $sbbsett['id'] ]['alt_name']}.{$main_host}/{$_furl}\">" . $cat_info[$cid]['name'] . "</a>";
				} else {
					$_furl = ( $config['allow_alt_url'] == "1" ) ? $cat_info[ $sbbsett['id'] ]['alt_name'] . "/" . $parent['alt_name'] . "/" . $cat_info[$cid]['alt_name'] . "/" : "index.php?do=cat&category=" . $cat_info[$cid]['alt_name'];
					$subforums .= "<li><a href=\"{$config['http_home_url']}{$_furl}\">" . $cat_info[$cid]['name'] . "</a>";
				}

				if ( $sbbsett['show_subcount'] ) {
					$subforums .= " <span>( " . intval( $subcounts[$cid] ) . " )</span></li>";
				} else { $subforums .= "</li>"; }
			}
			$forum_main_tpl = str_replace( "{subforums}", $subforums, $forum_main_tpl );
			$forum_main_tpl = preg_replace( "#\\[sub-forums\\](.*?)\\[/sub-forums\\]#is", "$1", $forum_main_tpl );
		} else {
			$forum_main_tpl = preg_replace( "#\\[sub-forums\\](.*?)\\[/sub-forums\\]#is", "", $forum_main_tpl );
		}

		$forum_main_tpl = str_replace( "{threads.tpl}", $tpl->result['content'], $forum_main_tpl );
	} else if ( $forum_where == "forum" ) {
		$forum_main_tpl = preg_replace( "#\\[depth=1\\](.*?)\\[/depth=1\\]#is", "", $forum_main_tpl );
		$forum_main_tpl = preg_replace( "#\\[depth=2\\](.*?)\\[/depth=2\\]#is", "", $forum_main_tpl );
		$forum_main_tpl = preg_replace( "#\\[depth=3\\](.*?)\\[/depth=3\\]#is", "$1", $forum_main_tpl );
		$forum_main_tpl = preg_replace( "#\\[depth=4\\](.*?)\\[/depth=4\\]#is", "", $forum_main_tpl );
		$forum_main_tpl = str_replace( "{threads.tpl}", $tpl->result['content'], $forum_main_tpl );
	} else if ( $forum_where == "thread" ) {
		$forum_main_tpl = preg_replace( "#\\[depth=1\\](.*?)\\[/depth=1\\]#is", "", $forum_main_tpl );
		$forum_main_tpl = preg_replace( "#\\[depth=2\\](.*?)\\[/depth=2\\]#is", "", $forum_main_tpl );
		$forum_main_tpl = preg_replace( "#\\[depth=3\\](.*?)\\[/depth=3\\]#is", "", $forum_main_tpl );
		$forum_main_tpl = preg_replace( "#\\[depth=4\\](.*?)\\[/depth=4\\]#is", "$1", $forum_main_tpl );
		$forum_main_tpl = str_replace( "{post.tpl}", $tpl->result['content'], $forum_main_tpl );
	}

	if ( in_array( $forum_where, array( "cat", "forum", "thread" ) ) ) {
		$tpl->result['content'] = str_replace( "{content}", $tpl->result['content'], $forum_main_tpl );
	}

} else if ( $forum_compile == "after" ) {

	// Category Echo
	if ( stripos( $tpl->result['main'], "{category" ) !== false ) {
		$tpl->result['main'] = preg_replace_callback ( "#\\{category(.+?)\\}#i", "custom_cat_print", $tpl->result['main'] );
	}
	// Category Echo

	if ( $sbbsett['use_subdomain'] AND ! empty( $cat_info[ $sbbsett['id'] ]['alt_name'] ) ) {
		$main_host = str_replace( $cat_info[ $sbbsett['id'] ]['alt_name'] . ".", "", $_SERVER['HTTP_HOST'] );
		$http_prefix = ( $config['only_ssl'] ) ? "https://" : "http://";

		$tpl->result['main'] = str_replace (
			$http_prefix . "www." . $main_host . "/" . $cat_info[ $sbbsett['id'] ]['alt_name'],
			$http_prefix . $cat_info[ $sbbsett['id'] ]['alt_name'] . "." . $main_host,
			$tpl->result['main']
		);

		$tpl->result['main'] = str_replace (
			$http_prefix . $main_host . "/" . $cat_info[ $sbbsett['id'] ]['alt_name'],
			$http_prefix . $cat_info[ $sbbsett['id'] ]['alt_name'] . "." . $main_host,
			$tpl->result['main']
		);
	}

	if ( strpos( $tpl->result['main'], "{forum-stats}" ) !== false ) {
		if ( ! isset( $forum ) ) {
			include_once (ENGINE_DIR . '/modules/simplebb.php');
			$forum = new SimpleBB( $config, $db, $tpl, $cat_info, $user_groups, $member_id );
		}
		$tpl->result['main'] = str_replace( "{forum-stats}", $forum->get_stats(), $tpl->result['main'] );
	}

	if ( stripos( $tpl->result['main'], "[forum" ) !== false ) {
		if ( in_array( $forum_where, array( "main", "cat", "forum", "thread" ) ) || $dle_module == "addpost" ) {
			$tpl->result['main'] = preg_replace( "#\\[forum\\](.*?)\\[/forum\\]#is", "$1", $tpl->result['main'] );
			$tpl->result['main'] = preg_replace( "#\\[not-forum\\](.*?)\\[/not-forum\\]#is", "", $tpl->result['main'] );
		} else {
			$tpl->result['main'] = preg_replace( "#\\[forum\\](.*?)\\[/forum\\]#is", "", $tpl->result['main'] );
			$tpl->result['main'] = preg_replace( "#\\[not-forum\\](.*?)\\[/not-forum\\]#is", "$1", $tpl->result['main'] );
		}
		if ( $forum_where == "forum" OR $forum_where == "cat" ) {
			foreach ( $banners as $name => $value ) {
				$tpl->result['main'] = str_replace ( "{banner_" . $name . "}", $value, $tpl->result['main'] );
				if ( $value ) {
					$tpl->result['main'] = str_replace ( "[banner_" . $name . "]", "", $tpl->result['main'] );
					$tpl->result['main'] = str_replace ( "[/banner_" . $name . "]", "", $tpl->result['main'] );
				}
			}
			if ( stripos( $tpl->result['main'], "{custom" ) !== false) {
				$tpl->result['main'] = preg_replace_callback ( "#\\{custom(.+?)\\}#i", "custom_print", $tpl->result['main'] );
			}
			$tpl->result['main'] = preg_replace( "#\\[forum:inside\\](.*?)\\[/forum:inside\\]#is", "$1", $tpl->result['main'] );
		}
		if ( ! empty( $forum_where ) && $forum_where != "inside" ) {
			$tpl->result['main'] = preg_replace( "#\\[forum:" . $forum_where . "\\](.*?)\\[/forum:" . $forum_where . "\\]#is", "$1", $tpl->result['main'] );
		}
		$tpl->result['main'] = preg_replace( "#(\\[forum:([a-z]+)\\](.*?)\\[/forum:([a-z]+)\\])#is", "", $tpl->result['main'] );
		$tpl->result['main'] = preg_replace( "#(\\[/forum:([a-z]+)\\])#is", "", $tpl->result['main'] );
	}

}


?>
