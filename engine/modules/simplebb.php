<?php
/*
=====================================================
 MWS SimpleBB Forum v1.0 - Mehmet Hanoğlu
-----------------------------------------------------
 http://dle.net.tr/ -  Copyright (c) 2014
-----------------------------------------------------
 Mail: m.hanoglu55@gmail.com
-----------------------------------------------------
 Lisans : MIT License
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}


if ( ! in_array( $dle_module, array() ) ) {

	if ( $dle_module == "main" ) $forum_where = "";

	if ( $forum_compile == "before" ) {

		$forum_main_tpl = file_get_contents( ROOT_DIR . "/templates/" . $config['skin'] . "/forum/main.tpl" );

		if ( $forum_where == "cat" ) {
			$forum_main_tpl = preg_replace( "#\\[depth=1\\](.*?)\\[/depth=1\\]#is", "", $forum_main_tpl );
			$forum_main_tpl = preg_replace( "#\\[depth=2\\](.*?)\\[/depth=2\\]#is", "$1", $forum_main_tpl );
			$forum_main_tpl = preg_replace( "#\\[depth=3\\](.*?)\\[/depth=3\\]#is", "", $forum_main_tpl );
			$forum_main_tpl = preg_replace( "#\\[depth=4\\](.*?)\\[/depth=4\\]#is", "", $forum_main_tpl );
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
		if (stripos ( $tpl->result['main'], "{category" ) !== false) {
			$tpl->result['main'] = preg_replace_callback ( "#\\{category(.+?)\\}#i", "custom_cat_print", $tpl->result['main'] );
		}
		// Category Echo

		if ( $config['forum_use_subdomain'] AND ! empty( $cat_info[ $config['forum_id'] ]['alt_name'] ) ) {
			$main_host = str_replace( $cat_info[ $config['forum_id'] ]['alt_name'] . ".", "", $_SERVER['HTTP_HOST'] );
			$tpl->result['main'] = str_replace ( "http://www." . $main_host . "/" . $cat_info[ $config['forum_id'] ]['alt_name'], "http://" . $cat_info[ $config['forum_id'] ]['alt_name'] . "." . $main_host, $tpl->result['main'] );
			$tpl->result['main'] = str_replace ( "http://" . $main_host . "/" . $cat_info[ $config['forum_id'] ]['alt_name'], "http://" . $cat_info[ $config['forum_id'] ]['alt_name'] . "." . $main_host, $tpl->result['main'] );
		}

		if ( strpos( $tpl->result['main'], "{forum-stats}" ) !== false ) {
			if ( ! isset( $forum ) ) {
				include_once (ENGINE_DIR . '/modules/show.forum.php');
				$forum = new SimpleBB( $config, $db, $tpl, $cat_info, $user_groups, $member_id );
			}
			$tpl->result['main'] = str_replace( "{forum-stats}", $forum->get_stats(), $tpl->result['main'] );
		}

		if ( stripos( $tpl->result['main'], "[forum" ) !== false ) {
			if ( in_array( $forum_where, array( "main", "cat", "forum", "thread" ) ) ) {
				$tpl->result['main'] = preg_replace( "#\\[forum\\](.*?)\\[/forum\\]#is", "$1", $tpl->result['main'] );
			} else {
				$tpl->result['main'] = preg_replace( "#\\[forum\\](.*?)\\[/forum\\]#is", "", $tpl->result['main'] );
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

}

?>
