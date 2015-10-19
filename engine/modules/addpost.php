<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 http://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004,2013 SoftNews Media Group
=====================================================
 Bu kodlar telif hakkı ile korunuyor
-----------------------------------------------------
 Dosya Adı : addpost.php
=====================================================
*/

if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

$allow_addnews = true;

include ENGINE_DIR . '/data/simplebb.conf.php';
require_once ROOT_DIR . "/language/" . $config['langs'] . "/simplebb.lng";

if ( ! isset( $_REQUEST['cat'] ) ) {
	msgbox( $lang['sbb_s_2'], $lang['sbb_s_1'] . " <a href=\"$PHP_SELF?do=addpost\">$lang[add_noch]</a> $lang[add_or] <a href=\"{$config['http_home_url']}\">$lang[all_prev]</a>" );
	die();
}

$sel_cat = intval( $db->safesql( $_REQUEST['cat'] ) );

include_once ENGINE_DIR . '/modules/show.forum.php';
$forum = new SimpleBB( $config, $db, $tpl, $cat_info, $user_groups, $member_id ); // category


include_once ENGINE_DIR . '/classes/parse.class.php';
$parse = new ParseFilter( Array (), Array (), 1, 1 );

if( $config['max_moderation'] AND ! $user_group[$member_id['user_group']]['moderation'] ) {
	
	$stats_approve = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE approve != '1'" );
	$stats_approve = $stats_approve['count'];
	
	if( $stats_approve >= $config['max_moderation'] ) $allow_addnews = false;

}

if ($is_logged AND $config['news_restricted'] AND (($_TIME - $member_id['reg_date']) < ($config['news_restricted'] * 86400)) ) {
	$lang['add_err_9'] = str_replace( '{days}', intval($config['news_restricted']), $lang['news_info_7'] );
	$allow_addnews = false;
}

if( $member_id['restricted'] and $member_id['restricted_days'] and $member_id['restricted_date'] < $_TIME ) {
	
	$member_id['restricted'] = 0;
	$db->query( "UPDATE LOW_PRIORITY " . USERPREFIX . "_users SET restricted='0', restricted_days='0', restricted_date='' WHERE user_id='{$member_id['user_id']}'" );

}

if( $member_id['restricted'] == 1 or $member_id['restricted'] == 3 ) {
	
	if( $member_id['restricted_days'] ) {
		
		$lang['news_info_4'] = str_replace( '{date}', langdate( "j M Y H:i", $member_id['restricted_date'] ), $lang['news_info_4'] );
		$lang['add_err_9'] = $lang['news_info_4'];
	
	} else {
		
		$lang['add_err_9'] = $lang['news_info_5'];
	
	}
	
	$allow_addnews = false;

}


if( ! $allow_addnews || ! in_array( $sel_cat, $forum->get_forums() ) ) {

	if ( ! $allow_addnews ) {
		msgbox( $lang['all_info'], $lang['add_err_9'] . "<br /><br /><a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );
	}

	if ( ! in_array( $sel_cat, $forum->get_forums() ) ) {
		msgbox( $lang['sbb_s_2'], $lang['sbb_s_3'] . "<br /><br /><a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );
	}

} else {
	
	if( isset( $_REQUEST['mod'] ) and $_REQUEST['mod'] == "addnews" and $is_logged and $user_group[$member_id['user_group']]['allow_adds'] ) {
		
		$stop = "";
	
		$allow_comm = intval( $_POST['allow_comm'] );

		if( $user_group[$member_id['user_group']]['allow_main'] ) $allow_main = intval( $_POST['allow_main'] );
		else $allow_main = 0;
		
		$approve = ! intval( $sbbsett['use_app'] );
		$allow_rating = intval( $_POST['allow_rating'] );
		
		if( $user_group[$member_id['user_group']]['allow_fixed'] ) $news_fixed = intval( $_POST['news_fixed'] );
		else $news_fixed = 0;
		

		$category_list = $sel_cat;
	
		if( ! $config['allow_add_tags'] ) $_POST['tags'] = "";
		elseif( @preg_match( "/[\||\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_POST['tags'] ) ) $_POST['tags'] = "";
		else $_POST['tags'] = @$db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_POST['tags'] ) ) ), ENT_COMPAT, $config['charset'] ) );

		if ( $_POST['tags'] ) {
	
			$temp_array = array();
			$tags_array = array();
			$temp_array = explode (",", $_POST['tags']);
	
			if (count($temp_array)) {
	
				foreach ( $temp_array as $value ) {
					if( trim($value) ) $tags_array[] = trim( $value );
				}
	
			}
	
			if ( count($tags_array) ) $_POST['tags'] = implode(", ", $tags_array); else $_POST['tags'] = "";
	
		}

		// обработка опроса
		if( trim( $_POST['vote_title'] != "" ) ) {
			
			$add_vote = 1;
			$vote_title =  $db->safesql( trim($parse->process($_POST['vote_title'])) );
			$frage =  $db->safesql( trim($parse->process($_POST['frage'])) );
			$vote_body = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['vote_body'] ), false ) );
			$allow_m_vote = intval( $_POST['allow_m_vote'] );
		
		} else
			$add_vote = 0;
		
		if( ! $user_group[$member_id['user_group']]['moderation'] ) {
			$approve = 0;
			$allow_comm = 1;
			$allow_main = 1;
			$allow_rating = 1;
			$news_fixed = 0;
		}
		
		if( $approve ) $msg = $lang['add_ok_1'];
		else $msg = $lang['add_ok_2'];
		
		$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_add'] );
		
		if( $user_group[$member_id['user_group']]['moderation'] ) {
			foreach ( $catlist as $selected ) {
				if( $allow_list[0] != "all" and ! in_array( $selected, $allow_list ) and $member_id['user_group'] != "1" ) {
					$approve = 0;
					$msg = $lang['add_ok_3'];
				}
			}
		}


		$allow_list = explode( ',', $user_group[$member_id['user_group']]['cat_allow_addnews'] );
		
		if( $allow_list[0] != "all" ) {
			foreach ( $catlist as $selected ) {
				if( !in_array( $selected, $allow_list ) AND $member_id['user_group'] != "1" ) {
					$stop .= "<li>" . $lang['news_err_41'] . "</li>";
				}
			}
		}


		if ( !$user_group[$member_id['user_group']]['allow_html'] ) {

			$config['allow_site_wysiwyg'] = 0;
			$_POST['short_story'] = strip_tags ($_POST['short_story']);
			$_POST['full_story'] = strip_tags ($_POST['full_story']);

		}
		
		if( $config['allow_site_wysiwyg'] ) {

			$parse->allow_code = false;			
			$full_story = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['full_story'] ) ) );
			$short_story = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['short_story'] ) ) );
			$allow_br = 0;
		
		} else {
			
			$full_story = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['full_story'] ), false ) );
			$short_story = $db->safesql( $parse->BB_Parse( $parse->process( $_POST['short_story'] ), false ) );
			$allow_br = 1;
		
		}


		if( $parse->not_allowed_text ) {
			$stop .= "<li>" . $lang['news_err_39'] . "</li>";
		}

		if ( $config['version_id'] < "10.6" ) {
			$parse->ParseFilter();
		}
		$title = $db->safesql( $parse->process( trim( strip_tags ($_POST['title']) ) ) );
		$alt_name = trim( $parse->process( stripslashes( $_POST['alt_name'] ) ) );
		if ( $config['version_id'] < "10.6" ) {
			$parse = new ParseFilter( Array (), Array (), 1, 1 );
		}

		$add_module = "yes";
		$xfieldsaction = "init";
		$category = $catlist;
		include (ENGINE_DIR . '/inc/xfields.php');
		
		if( $alt_name == "" or ! $alt_name ) $alt_name = totranslit( stripslashes( $title ), true, false );
		else $alt_name = totranslit( $alt_name, true, false );
		
		if( $title == "" or ! $title ) $stop .= $lang['add_err_1'];
		if( dle_strlen( $title, $config['charset'] ) > 200 ) $stop .= $lang['add_err_2'];

		if ($config['create_catalog']) $catalog_url = $db->safesql( dle_substr( htmlspecialchars( strip_tags( stripslashes( trim( $title ) ) ), ENT_QUOTES, $config['charset'] ), 0, 1, $config['charset'] ) ); else $catalog_url = "";

		if ( $user_group[$member_id['user_group']]['disable_news_captcha'] AND $member_id['news_num'] >= $user_group[$member_id['user_group']]['disable_news_captcha'] ) {

			$user_group[$member_id['user_group']]['news_question'] = false;
			$user_group[$member_id['user_group']]['news_sec_code'] = false;

		}
		
		if( $user_group[$member_id['user_group']]['news_sec_code']) {
			
			if ($config['allow_recaptcha']) {
	
				require_once ENGINE_DIR . '/classes/recaptcha.php';
				$sec_code = 1;
				$sec_code_session = false;
	
				if ($_POST["recaptcha_response_field"] AND $_POST["recaptcha_response_field"]) {
				
					$resp = recaptcha_check_answer ($config['recaptcha_private_key'],
				                                     $_SERVER["REMOTE_ADDR"],
				                                     $_POST["recaptcha_challenge_field"],
				                                     $_POST["recaptcha_response_field"]);
				
				        if (!$resp->is_valid) {
	
							$stop .= "<li>" . $lang['news_err_30'] . "</li>";
	
				        }
	
				} else $stop .= "<li>" . $lang['news_err_30'] . "</li>";
	
			} elseif( $_REQUEST['sec_code'] != $_SESSION['sec_code_session'] OR !$_SESSION['sec_code_session'] ) $stop .= "<li>" . $lang['news_err_30'] . "</li>";

		
		}

		if( $user_group[$member_id['user_group']]['news_question'] ) {
	
			if ( intval($_SESSION['question']) ) {
	
				$answer = $db->super_query("SELECT id, answer FROM " . PREFIX . "_question WHERE id='".intval($_SESSION['question'])."'");
	
				$answers = explode( "\n", $answer['answer'] );
	
				$pass_answer = false;
	
				if( function_exists('mb_strtolower') ) {
					$question_answer = trim(mb_strtolower($_POST['question_answer'], $config['charset']));
				} else {
					$question_answer = trim(strtolower($_POST['question_answer']));
				}
	
				if( count($answers) AND $question_answer ) {
					foreach( $answers as $answer ){
	
						if( function_exists('mb_strtolower') ) {
							$answer = trim(mb_strtolower($answer, $config['charset']));
						} else {
							$answer = trim(strtolower($answer));
						}
	
						if( $answer AND $answer == $question_answer ) {
							$pass_answer	= true;
							break;
						}
					}
				}
	
				if( !$pass_answer ) $stop .= $lang['reg_err_24'];
	
			} else $stop .= $lang['reg_err_24'];
		
		}

		if( $user_group[$member_id['user_group']]['flood_news'] ) {
			if( flooder( $member_id['name'],  $user_group[$member_id['user_group']]['flood_news'] )) {
				$stop .= "<li>" .$lang['news_err_4'] . " " . $lang['news_err_43'] . " {$user_group[$member_id['user_group']]['flood_news']} " . $lang['news_err_6']. "</li>";
			}
		}

		$max_detected = false;
		if( $user_group[$member_id['user_group']]['max_day_news'] ) {
			$row = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE date >= '".date("Y-m-d", $_TIME)."' AND date < '".date("Y-m-d", $_TIME)."' + INTERVAL 24 HOUR AND autor = '{$member_id['name']}'");
			if ($row['count'] >= $user_group[$member_id['user_group']]['max_day_news'] ) {
				$stop .= "<li>" .$lang['news_err_44'] . "</li>";
				$max_detected = true;
			}
		}

		if( $stop ) {
			$stop = "<ul>" . $stop . "</ul><a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>";
			msgbox( $lang['add_err_6'], $stop  );
		}
		
		if( ! $stop ) {
			
			$_SESSION['sec_code_session'] = 0;
			$_SESSION['question'] = false;
			$id = (isset( $_REQUEST['id'] )) ? intval( $_REQUEST['id'] ) : 0;
			$found = false;
			
			if( $id ) {
				$row = $db->super_query( "SELECT id, autor, tags FROM " . PREFIX . "_post where id = '$id' and approve = '0'" );
				if( $id == $row['id'] and ($member_id['name'] == $row['autor'] or $user_group[$member_id['user_group']]['allow_all_edit']) ) $found = true;
				else $found = false;
			}
			
			if( $found ) {
				
				$db->query( "UPDATE " . PREFIX . "_post set title='$title', short_story='$short_story', full_story='$full_story', xfields='$filecontents', category='$category_list', alt_name='$alt_name', allow_comm='$allow_comm', approve='$approve', allow_main='$allow_main', fixed='$news_fixed', allow_br='$allow_br', tags='" . $_POST['tags'] . "' WHERE id='$id'" );
				$db->query( "UPDATE " . PREFIX . "_post_extras SET allow_rate='{$allow_rating}', votes='{$add_vote}' WHERE news_id='{$id}'" );				

				// Облако тегов
				if( $_POST['tags'] != $row['tags'] or $approve ) {
					$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$row['id']}'" );
					
					if( $_POST['tags'] != "" and $approve ) {
						
						$tags = array ();
						
						$_POST['tags'] = explode( ",", $_POST['tags'] );
						
						foreach ( $_POST['tags'] as $value ) {
							
							$tags[] = "('" . $row['id'] . "', '" . trim( $value ) . "')";
						}
						
						$tags = implode( ", ", $tags );
						$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tags );
					
					}
				}


				if( $add_vote ) {
					
					$count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_poll WHERE news_id = '{$id}'" );
					
					if( $count['count'] ) $db->query( "UPDATE  " . PREFIX . "_poll set title='$vote_title', frage='$frage', body='$vote_body', multiple='$allow_m_vote' WHERE news_id = '{$id}'" );
					else $db->query( "INSERT INTO " . PREFIX . "_poll (news_id, title, frage, body, votes, multiple, answer) VALUES('{$id}', '$vote_title', '$frage', '$vote_body', 0, '$allow_m_vote', '')" );
				
				} else {
					$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id='$item_db[0]'" );
					$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='$item_db[0]'" );
				}
			
			} else {

				if ( $max_detected ) die( "Hacking attempt!" );
				$added_time = time() + ($config['date_adjust'] * 60);
				$thistime = date( "Y-m-d H:i:s", $added_time );
				$approve = ( $config['forum_use_app'] ) ? "0" : "1";

				$db->query( "INSERT INTO " . PREFIX . "_post (date, autor, short_story, full_story, xfields, title, keywords, category, alt_name, allow_comm, approve, allow_main, fixed, allow_br, symbol, tags) values ('$thistime', '{$member_id['name']}', '$short_story', '$full_story', '$filecontents', '$title', '', '$category_list', '$alt_name', '$allow_comm', '$approve', '$allow_main', '$news_fixed', '$allow_br', '$catalog_url', '" . $_POST['tags'] . "')" );
				
				$row['id'] = $db->insert_id();

				$db->query( "INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, user_id) VALUES('{$row['id']}', '{$allow_rating}', '{$add_vote}','{$member_id['user_id']}')" );

				if( $add_vote ) {
					$db->query( "INSERT INTO " . PREFIX . "_poll (news_id, title, frage, body, votes, multiple, answer) VALUES('{$row['id']}', '{$vote_title}', '{$frage}', '{$vote_body}', 0, '{$allow_m_vote}', '')" );
				}

				$member_id['name'] = $db->safesql($member_id['name']);

				$db->query( "UPDATE " . PREFIX . "_images set news_id='{$row['id']}' where author = '{$member_id['name']}' AND news_id = '0'" );
				$db->query( "UPDATE " . PREFIX . "_files set news_id='{$row['id']}' where author = '{$member_id['name']}' AND news_id = '0'" );
				$db->query( "UPDATE " . USERPREFIX . "_users set news_num=news_num+1 where user_id='{$member_id['user_id']}'" );

				if( $user_group[$member_id['user_group']]['flood_news'] ) {
					$db->query( "INSERT INTO " . PREFIX . "_flood (id, ip, flag) values ('$_TIME', '{$member_id['name']}', '1')" );
				}
				
				if( $_POST['tags'] != "" and $approve ) {
					
					$tags = array ();
					
					$_POST['tags'] = explode( ",", $_POST['tags'] );
					
					foreach ( $_POST['tags'] as $value ) {
						
						$tags[] = "('" . $row['id'] . "', '" . trim( $value ) . "')";
					}
					
					$tags = implode( ", ", $tags );
					$db->query( "INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES " . $tags );
				
				}
				
				if( ! $approve and $config['mail_news'] ) {
					
					include_once ENGINE_DIR . '/classes/mail.class.php';
					$mail = new dle_mail( $config );
					
					$row = $db->super_query( "SELECT template FROM " . PREFIX . "_email WHERE name='new_news' LIMIT 0,1" );
					
					$row['template'] = stripslashes( $row['template'] );
					$row['template'] = str_replace( "{%username%}", $member_id['name'], $row['template'] );
					$row['template'] = str_replace( "{%date%}", langdate( "j F Y H:i", $added_time ), $row['template'] );
					$row['template'] = str_replace( "{%title%}", stripslashes( stripslashes( $title ) ), $row['template'] );
					
					$category_list = explode( ",", $category_list );
					$my_cat = array ();
					
					foreach ( $category_list as $element ) {
						
						$my_cat[] = $cat_info[$element]['name'];
					
					}
					
					$my_cat = stripslashes( implode( ', ', $my_cat ) );
					
					$row['template'] = str_replace( "{%category%}", $my_cat, $row['template'] );
					
					$mail->send( $config['admin_mail'], $lang['mail_news'], $row['template'] );
				
				}
			
			}
			
			$addn_link = ( $config['allow_alt_url'] ) ? $config['http_home_url'] . "addpost/" . $sel_cat . "/" : $PHP_SELF . "?do=addpost&cat=" . $sel_cat;

			if ( $approve ) {
				msgbox( $lang['add_ok'], "{$msg} <a href=\"{$addn_link}\">$lang[add_noch]</a> $lang[add_or] <a href=\"{$config['http_home_url']}\">$lang[all_prev]</a>" );
			} else {
				msgbox( $lang['add_ok'], "{$msg} <a href=\"{$addn_link}\">$lang[add_noch]</a> $lang[add_or] <a href=\"{$config['http_home_url']}\">$lang[all_prev]</a>" );
			}
			
			if( $approve ) {

				clear_cache( array('news_', 'forum_', 'tagscloud_', 'topnews_', 'rss') );

			}
		
		}
	
	} elseif( $is_logged and $user_group[$member_id['user_group']]['allow_adds'] ) {
		
		$tpl->load_template( 'forum/addpost.tpl' );
		
		$addtype = "addnews";

		if ( !$user_group[$member_id['user_group']]['allow_html'] ) {

			$config['allow_site_wysiwyg'] = 0;

		}
		
		if( $config['allow_site_wysiwyg'] ) {
			
			include_once ENGINE_DIR . '/editor/shortsite.php';
			include_once ENGINE_DIR . '/editor/fullsite.php';
			$bb_code = "";
		
		} else {
			$bb_editor = true;
			include_once ENGINE_DIR . '/modules/bbcode.php';
		}

		if( !$config['allow_site_wysiwyg'] ) {
			
			$tpl->set( '[not-wysywyg]', '' );
			$tpl->set( '[/not-wysywyg]', '' );
		
		} else
			$tpl->set_block( "'\\[not-wysywyg\\].*?\\[/not-wysywyg\\]'si", '' );
		
		if( $config['allow_site_wysiwyg'] ) {
			
			$tpl->set( '{shortarea}', $shortarea );
			$tpl->set( '{fullarea}', $fullarea );
		
		} else {
			$tpl->set( '{shortarea}', '' );
			$tpl->set( '{fullarea}', '' );
		}
		
		$id = (isset( $_REQUEST['id'] )) ? intval( $_REQUEST['id'] ) : 0;
		$found = false;
		
		if( $id ) {
			$row = $db->super_query( "SELECT * FROM " . PREFIX . "_post LEFT JOIN " . PREFIX . "_post_extras ON (" . PREFIX . "_post.id=" . PREFIX . "_post_extras.news_id) WHERE id = '{$id}' and approve = '0'" );
			if( $id == $row['id'] and ($member_id['name'] == $row['autor'] or $user_group[$member_id['user_group']]['allow_all_edit']) ) $found = true;
			else $found = false;
		}
		
		if( $found ) {
			
			$cat_list = explode( ',', $row['category'] );
			$categories_list = CategoryNewsSelection( $cat_list, 0 );
			$tpl->set( '{title}', $parse->decodeBBCodes( $row['title'], false ) );
			$tpl->set( '{alt-name}', $row['alt_name'] );
			
			if( $config['allow_site_wysiwyg'] or $row['allow_br'] != '1' ) {
				$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], TRUE, $config['allow_site_wysiwyg'] );
				$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], TRUE, $config['allow_site_wysiwyg'] );
			} else {
				$row['short_story'] = $parse->decodeBBCodes( $row['short_story'], false );
				$row['full_story'] = $parse->decodeBBCodes( $row['full_story'], false );
			}
			
			$tpl->set( '{short-story}', $row['short_story'] );
			$tpl->set( '{full-story}', $row['full_story'] );
			$tpl->set( '{tags}', $row['tags'] );

			if( $row['votes'] ) {
				$poll = $db->super_query( "SELECT * FROM " . PREFIX . "_poll where news_id = '{$row['id']}'" );
				$poll['title'] = $parse->decodeBBCodes( $poll['title'], false );
				$poll['frage'] = $parse->decodeBBCodes( $poll['frage'], false );
				$poll['body'] = $parse->decodeBBCodes( $poll['body'], false );
				$poll['multiple'] = $poll['multiple'] ? "checked" : "";

				$tpl->set( '{votetitle}', $poll['title'] );
				$tpl->set( '{frage}', $poll['frage'] );
				$tpl->set( '{votebody}', $poll['body'] );
				$tpl->set( '{allowmvote}', $poll['multiple'] );
			}
		
		} else {
			
			$categories_list = CategoryNewsSelection( 0, 0 );
			$tpl->set( '{title}', '' );
			$tpl->set( '{alt-name}', '' );
			$tpl->set( '{short-story}', '' );
			$tpl->set( '{full-story}', '' );
			$tpl->set( '{tags}', '' );
			$tpl->set( '{votetitle}', '' );
			$tpl->set( '{frage}', '' );
			$tpl->set( '{votebody}', '' );
			$tpl->set( '{allowmvote}', '' );
		
		}
		$tpl->set( '{selected-cat}', $cat_info[ $sel_cat ]['name'] );

		$tpl->copy_template .= <<< HTML
<input type="hidden" value="{$sel_cat}" name="sel_cat" id="sel_cat" />
<script>
$(document).ready(function() {
	var obj = $("#sel_cat");
	onCategoryChange( obj );
});
</script>
HTML;
		
		$xfieldsaction = "categoryfilter";
		include_once ENGINE_DIR . '/inc/xfields.php';
		
		if( $config['allow_multi_category'] ) {
			
			$cats = "<select data-placeholder=\"{$lang['addnews_cat_sel']}\" name=\"catlist[]\" id=\"category\" onchange=\"onCategoryChange(this)\" style=\"width:1070px;height:140px;\" multiple=\"multiple\">";
		
		} else {
			
			$cats = "<select data-placeholder=\"{$lang['addnews_cat_sel']}\" name=\"catlist[]\" id=\"category\" onchange=\"onCategoryChange(this)\" style=\"width:1070px;\">";
		}
		
		$cats .= $categories_list;
		$cats .= "</select>";
		
		$tpl->set( '{bbcode}', $bb_code );
		$tpl->set( '{category}', $cats );
		
		if( $user_group[$member_id['user_group']]['moderation'] ) {
			
			$admintag = "<input type=\"checkbox\" name=\"allow_comm\" id=\"allow_comm\" value=\"1\" checked=\"checked\" />&nbsp;<label for=\"allow_comm\">" . $lang['add_al_com'] . "</label>";
			
			if( $user_group[$member_id['user_group']]['allow_main'] ) $admintag .= "&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" name=\"allow_main\" id=\"allow_main\" value=\"1\" checked=\"checked\" />&nbsp;<label for=\"allow_main\">" . $lang['add_al_m'] . "</label>";
			
			$admintag .= "<br /><input type=\"checkbox\" name=\"approve\" id=\"approve\" value=\"1\" checked=\"checked\" /><label for=\"approve\"> {$lang['add_al_ap']}</label><br /><input type=\"checkbox\" name=\"allow_rating\" id=\"allow_rating\" value=\"1\" checked=\"checked\" /><label for=\"allow_rating\"> {$lang['addnews_allow_rate']}</label>";
			
			if( $user_group[$member_id['user_group']]['allow_fixed'] ) $admintag .= "<br /><input type=\"checkbox\" name=\"news_fixed\" id=\"news_fixed\" value=\"1\" /><label for=\"news_fixed\"> {$lang['add_al_fix']}</label>";
			
			$tpl->set( '{admintag}', $admintag );
		
		} else
			$tpl->set( '{admintag}', '' );
		
		if( $is_logged and $member_id['user_group'] < 3 ) {
			
			$tpl->set( '[urltag]', '' );
			$tpl->set( '[/urltag]', '' );
		
		} else
			$tpl->set_block( "'\\[urltag\\].*?\\[/urltag\\]'si", "" );
		
		if( $found ) {
			
			$xfieldsaction = "list";
			$xfieldmode = "site";
			$xfieldsid = $row['xfields'];
			$xfieldscat = $row['category'];
			include (ENGINE_DIR . '/inc/xfields.php');
		
		} else {
			
			$xfieldsaction = "list";
			$xfieldmode = "site";
			$xfieldsadd = true;
			include (ENGINE_DIR . '/inc/xfields.php');
		
		}

		if( !$config['allow_site_wysiwyg'] ) $output = str_replace("<!--panel-->", $bb_code, $output);
		
		$tpl->set( '{xfields}', $output );

		if ( $user_group[$member_id['user_group']]['disable_news_captcha'] AND $member_id['news_num'] >= $user_group[$member_id['user_group']]['disable_news_captcha'] ) {

			$user_group[$member_id['user_group']]['news_question'] = false;
			$user_group[$member_id['user_group']]['news_sec_code'] = false;

		}

		if( $user_group[$member_id['user_group']]['news_question'] ) {

			$tpl->set( '[question]', "" );
			$tpl->set( '[/question]', "" );

			$question = $db->super_query("SELECT id, question FROM " . PREFIX . "_question ORDER BY RAND() LIMIT 1");
			$tpl->set( '{question}', htmlspecialchars( stripslashes( $question['question'] ), ENT_QUOTES, $config['charset'] ) );

			$_SESSION['question'] = $question['id'];

		} else {

			$tpl->set_block( "'\\[question\\](.*?)\\[/question\\]'si", "" );
			$tpl->set( '{question}', "" );

		}
		
		if( $user_group[$member_id['user_group']]['news_sec_code'] ) {

			if ( $config['allow_recaptcha'] ) {

				$tpl->set( '[recaptcha]', "" );
				$tpl->set( '[/recaptcha]', "" );

				$tpl->set( '{recaptcha}', '
<script type="text/javascript">
<!--
	var RecaptchaOptions = {
        theme: \''.$config['recaptcha_theme'].'\',
        lang: \''.$lang['wysiwyg_language'].'\'
	};

//-->
</script>
<script type="text/javascript" src="//www.google.com/recaptcha/api/challenge?k='.$config['recaptcha_public_key'].'"></script>' );

				$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );
				$tpl->set( '{reg_code}', "" );

			} else {

				$tpl->set( '[sec_code]', "" );
				$tpl->set( '[/sec_code]', "" );

				if ( $config['version_id'] < "10.6" ) {
					$path = parse_url( $config['http_home_url'] );
					$tpl->set( '{sec_code}', "<a onclick=\"reload(); return false;\" href=\"#\" title=\"{$lang['reload_code']}\"><span id=\"dle-captcha\"><img src=\"" . $path['path'] . "engine/modules/antibot/antibot.php\" alt=\"{$lang['reload_code']}\" width=\"160\" height=\"80\" /></span></a>" );
				} else {
					$tpl->set( '{sec_code}', "<a onclick=\"reload(); return false;\" href=\"#\" title=\"{$lang['reload_code']}\"><span id=\"dle-captcha\"><img src=\"engine/modules/antibot/antibot.php\" alt=\"{$lang['reload_code']}\" width=\"160\" height=\"80\" /></span></a>" );
				}

				$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
				$tpl->set( '{recaptcha}', "" );
			}

		} else {

			$tpl->set( '{sec_code}', "" );
			$tpl->set( '{recaptcha}', "" );
			$tpl->set_block( "'\\[recaptcha\\](.*?)\\[/recaptcha\\]'si", "" );
			$tpl->set_block( "'\\[sec_code\\](.*?)\\[/sec_code\\]'si", "" );

		}

		if ( $config['version_id'] < "10.6" ) {
			if (!isset($path['path'])) $path['path'] = "/";
		}

		if( $config['allow_site_wysiwyg'] == "2" ) $save = "tinyMCE.triggerSave();"; else $save = "";		

		$script_reload = "";
		if ( $config['version_id'] < "10.6" ) {
			$script_reload = <<< HTML
function reload () {
	var rndval = new Date().getTime(); 
	document.getElementById('dle-captcha').innerHTML = '<img src="{$path['path']}engine/modules/antibot/antibot.php?rndval=' + rndval + '" width="160" height="80" alt="" />';
};
HTML;
		}


		$script = "
<script language=\"javascript\" type=\"text/javascript\">
<!--
function preview(){";
		
		if( $config['allow_site_wysiwyg'] == "1" ) {
			
			$script .= "submit_all_data();";
		
		}
		
		$script .= "if(document.entryform.title.value == ''){ DLEalert('$lang[add_err_7]', dle_info); }
    else{
        dd=window.open('','prv','height=400,width=750,resizable=0,scrollbars=1')
        document.entryform.mod.value='preview';document.entryform.action='{$config['http_home_url']}engine/preview.php';document.entryform.target='prv'
        document.entryform.submit();dd.focus()
        setTimeout(\"document.entryform.mod.value='addnews';document.entryform.action='';document.entryform.target='_self'\",500)
    }
}";
		
		$script .= <<<HTML
	{$script_reload}

	function find_relates ( )
	{
		var title = document.getElementById('title').value;

		ShowLoading('');

		$.post( dle_root + 'engine/ajax/find_relates.php', { title: title, mode: 1 }, function(data){
	
			HideLoading('');
	
			$('#related_news').html(data);
	
		});

		return false;

	};

	function checkxf ( )
	{

		var status = '';

		{$save}

		$('[uid=\"essential\"]:visible').each(function(indx) {

			if($.trim($(this).find('[rel=\"essential\"]').val()).length < 1) {
			
				DLEalert('{$lang['addnews_xf_alert']}', dle_info);

				status = 'fail';
			
			}

		});

		if(document.entryform.title.value == ''){

			DLEalert('{$lang['add_err_7']}', dle_info); 

			status = 'fail';

		}

		return status;

	};
//-->
</script>
HTML;

		if( $config['allow_add_tags'] ) {

			if ( $config['version_id'] < "10.6" ) {

				$script .= "
<script language=\"javascript\" type=\"text/javascript\">
<!--
	$(function(){
		function split( val ) {
			return val.split( /,\s*/ );
		}
		function extractLast( term ) {
			return split( term ).pop();
		}
 
		$( '#tags' ).autocomplete({
			source: function( request, response ) {
				$.getJSON( 'engine/ajax/find_tags.php', {
					term: extractLast( request.term )
				}, response );
			},
			search: function() {
				var term = extractLast( this.value );
				if ( term.length < 3 ) {
					return false;
				}
			},
			focus: function() {
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				terms.pop();
				terms.push( ui.item.value );
				terms.push( '' );
				this.value = terms.join( ', ' );
				return false;
			}
		});

	});
//-->
</script>";

			} else {

				$onload_scripts[] = <<<HTML
function split( val ) {
	return val.split( /,\s*/ );
}
function extractLast( term ) {
	return split( term ).pop();
}
 
$( '#tags' ).autocomplete({
	source: function( request, response ) {
		$.getJSON( 'engine/ajax/find_tags.php', {
			term: extractLast( request.term )
		}, response );
	},
	search: function() {
		var term = extractLast( this.value );
		if ( term.length < 3 ) {
			return false;
		}
	},
	focus: function() {
		return false;
	},
	select: function( event, ui ) {
		var terms = split( this.value );
		terms.pop();
		terms.push( ui.item.value );
		terms.push( '' );
		this.value = terms.join( ', ' );
		return false;
	}
});
HTML;
			}
		}
		
		$script .= "<form method=\"post\" name=\"entryform\" id=\"entryform\" onsubmit=\"if(checkxf()=='fail') return false;\" action=\"\">";
		
		$tpl->copy_template = $categoryfilter . $script . $tpl->copy_template . "<input type=\"hidden\" name=\"mod\" value=\"addnews\" /></form>";
		
		$tpl->compile( 'content' );
		$tpl->clear();
	
	} else
		msgbox( $lang['all_info'], "$lang[add_err_8]<br /><a href=\"javascript:history.go(-1)\">$lang[all_prev]</a>" );

}
?>
