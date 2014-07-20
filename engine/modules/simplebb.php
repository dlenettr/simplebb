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

$tpl->result['main'] = str_replace ( "{category-name}", $cat_info[ $category_id ]['name'], $tpl->result['main'] );
$tpl->result['main'] = str_replace ( "{category-id}", $category_id, $tpl->result['main'] );
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

?>
