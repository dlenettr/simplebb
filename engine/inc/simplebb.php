<?php
/*
=============================================
 Name      : MWS SimpleBB v2.0
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : http://dle.net.tr/   (c) 2015
 License   : MIT License
=============================================
*/

if ( ! defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if ( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}


require_once ROOT_DIR . "/language/" . $config['langs'] . "/simplebb.lng";

require_once ENGINE_DIR . "/data/simplebb.conf.php";

if ( ! is_writable(ENGINE_DIR . '/data/simplebb.conf.php' ) ) {
	$lang['stat_system'] = str_replace( "{file}", "engine/data/simplebb.conf.php", $lang['stat_system'] );
	$fail = "<div class=\"alert alert-error\">{$lang['stat_system']}</div>";
} else $fail = "";

if ( $_REQUEST['action'] == "save" ) {
	if ( $member_id['user_group'] != 1 ) { msg( "error", $lang['opt_denied'], $lang['opt_denied'] ); }
	if ( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) { die( "Hacking attempt! User not found" ); }

	$save_con = $_POST['save_con'];
	$save_con['id'] = intval($save_con['id']);
	$save_con['title_limit'] = intval($save_con['title_limit']);
	$save_con['post_limit'] = intval($save_con['post_limit']);
	$save_con['stat_title_limit'] = intval($save_con['stat_title_limit']);
	$save_con['use_subdomain'] = intval($save_con['use_subdomain']);
	$save_con['show_banners'] = intval($save_con['show_banners']);
	$save_con['optimized_sql'] = intval($save_con['optimized_sql']);
	$save_con['use_app'] = intval($save_con['use_app']);
	$save_con['show_subforums'] = intval($save_con['show_subforums']);
	$save_con['show_subcount'] = intval($save_con['show_subcount']);


	$find = array(); $replace = array();
	$find[] = "'\r'"; $replace[] = "";
	$find[] = "'\n'"; $replace[] = "";

	//$save_con = $save_con + $sbbsett;

	$handler = fopen( ENGINE_DIR . '/data/simplebb.conf.php', "w" );

	fwrite( $handler, "<?PHP \n\n//MWS SimpleBB Settings\n\n\$sbbsett = array (\n" );
	foreach ( $save_con as $name => $value ) {
		$value = ( is_array( $value ) ) ? implode(",", $value ) : $value;
		$value = trim(strip_tags(stripslashes( $value )));
		$value = htmlspecialchars( $value, ENT_QUOTES, $config['charset']);
		$value = preg_replace( $find, $replace, $value );
		$name = trim(strip_tags(stripslashes( $name )));
		$name = htmlspecialchars( $name, ENT_QUOTES, $config['charset'] );
		$name = preg_replace( $find, $replace, $name );
		$value = str_replace( "$", "&#036;", $value );
		$value = str_replace( "{", "&#123;", $value );
		$value = str_replace( "}", "&#125;", $value );
		$value = str_replace( ".", "", $value );
		$value = str_replace( chr(92), "", $value );
		$value = str_replace( chr(0), "", $value );
		$value = str_replace( '(', "", $value );
		$value = str_replace( ')', "", $value );
		$value = str_ireplace( "base64_decode", "base64_dec&#111;de", $value );
		$name = str_replace( "$", "&#036;", $name );
		$name = str_replace( "{", "&#123;", $name );
		$name = str_replace( "}", "&#125;", $name );
		$name = str_replace( ".", "", $name );
		$name = str_replace( '/', "", $name );
		$name = str_replace( chr(92), "", $name );
		$name = str_replace( chr(0), "", $name );
		$name = str_replace( '(', "", $name );
		$name = str_replace( ')', "", $name );
		$name = str_ireplace( "base64_decode", "base64_dec&#111;de", $name );
		fwrite( $handler, "'{$name}' => '{$value}',\n" );
	}
	fwrite( $handler, ");\n\n?>" );
	fclose( $handler );

	msg( "info", $lang['opt_sysok'], $lang['opt_sysok_1'], "{$PHP_SELF}?mod=simplebb" );

}

function en_serialize( $value ) { return str_replace( '"', "'", serialize( $value ) ); }
function de_serialize( $value ) { return unserialize( str_replace("'", '"', $value ) ); }

function showRow($title = "", $description = "", $field = "", $id = "") {
	$_id = ( ! empty( $id ) ) ? " id=\"{$id}\"" : "";
	echo "<tr{$_id}><td class=\"col-xs-10 col-sm-6 col-md-7\"><h6>{$title}</h6><span class=\"note large\">{$description}</span></td><td class=\"col-xs-2 col-md-5 alsettingstd\">{$field}</td></tr>";
}

function showSep( ) {
	echo "<tr><td class=\"col-xs-10 col-sm-6 col-md-7\" colspan=\"2\">&nbsp;</td></tr>";
}

function makeDropDown($options, $name, $selected) {
	$output = "<select class=\"uniform\" style=\"min-width:100px;\" name=\"{$name}\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"{$value}\"";
		if( $selected == $value ) {
			$output .= " selected ";
		}
		$output .= ">{$description}</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function makeCheckBox($name, $selected) {
	$selected = $selected ? "checked" : "";
	return "<input class=\"iButton-icons-tab\" type=\"checkbox\" name=\"{$name}\" value=\"1\" {$selected}>";
}

function makeMultiSelect($options, $name, $selected, $class = '') {
	$selected = explode( ",", $selected );
	$class = ( $class != '' ) ? " {$class}" : "";
	$size = (count($options) >= 6) ? 6 : count($options);
	$output = "<select class=\"uniform{$class}\" style=\"min-width:100px;\" size=\"".$size."\" name=\"{$name}[]\" multiple=\"multiple\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"{$value}\"";
		for ($x = 0; $x <= count($selected); $x++) {
			if ($value == $selected[$x]) $output .= " selected ";
		}
		$output .= ">{$description}</option>\n";
	}
	$output .= "</select>";
	return $output;
}

echoheader( "<i class=\"icon-comments\"></i>MWS SimpleBB", $lang['sbb_a_0'] );

$_ACTION = ( isset( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : false;

if ( ! $_ACTION ) {

echo <<< HTML
{$fail}
<form action="{$PHP_SELF}?mod=simplebb&action=save" name="conf" id="conf" method="post">
<div class="box">
	<div class="box-header">
		<div class="title">{$lang['sbb_a_1']}</div>
		<!--ul class="box-toolbar">
			<li class="toolbar-link">
				<a href="{$PHP_SELF}?mod=simplebb&action=list"><i class="icon-reorder"></i> Listeye Bak</a>
			</li>
		</ul-->
	</div>
	<div class="box-content">
		<table class="table table-normal">
HTML;

	$main_cats = array( "" => $lang['sbb_a_14'] );
	foreach( $cat_info as $cat ) { if ( $cat['parentid'] == "0" ) { $main_cats[$cat['id']] = $cat['name']; } }

	showRow( $lang['sbb_a_15'], $lang['sbb_a_16'], makeDropDown( $main_cats, "save_con[id]", "{$sbbsett['id']}" ) );

	showRow( $lang['sbb_a_6'], $lang['sbb_a_7'], "<input type=\"text\" style=\"text-align: center;\"  name=\"save_con[title_limit]\" value=\"{$sbbsett['title_limit']}\" size=\"20\" />" );

	showRow( $lang['sbb_a_8'], $lang['sbb_a_7'], "<input type=\"text\" style=\"text-align: center;\"  name=\"save_con[post_limit]\" value=\"{$sbbsett['post_limit']}\" size=\"20\" />" );

	showRow( $lang['sbb_a_9'], $lang['sbb_a_7'], "<input type=\"text\" style=\"text-align: center;\"  name=\"save_con[stat_title_limit]\" value=\"{$sbbsett['stat_title_limit']}\" size=\"20\" />" );

	showRow( $lang['sbb_a_2'], $lang['sbb_a_3'], makeCheckBox( "save_con[use_subdomain]", "{$sbbsett['use_subdomain']}" ) );

	showRow( $lang['sbb_a_10'], $lang['sbb_a_11'], makeCheckBox( "save_con[show_banners]", "{$sbbsett['show_banners']}" ) );

	showRow( $lang['sbb_a_17'], $lang['sbb_a_18'], makeCheckBox( "save_con[use_app]", "{$sbbsett['use_app']}" ) );

	showRow( $lang['sbb_a_19'], $lang['sbb_a_20'], "<input type=\"text\" style=\"text-align: center;\"  name=\"save_con[comments_tpl]\" value=\"{$sbbsett['comments_tpl']}\" size=\"30\" />" );

	showRow( $lang['sbb_a_21'], $lang['sbb_a_22'], "<input type=\"text\" style=\"text-align: center;\"  name=\"save_con[addcomm_tpl]\" value=\"{$sbbsett['addcomm_tpl']}\" size=\"30\" />" );

	showRow( $lang['sbb_a_23'], $lang['sbb_a_24'], makeCheckBox( "save_con[show_subforums]", "{$sbbsett['show_subforums']}" ) );

	showRow( $lang['sbb_a_25'], $lang['sbb_a_26'], makeCheckBox( "save_con[show_subcount]", "{$sbbsett['show_subcount']}" ) );


	showRow( "", "", "<div style=\"margin-top:10px;\" align=\"right\">
		<input type=\"hidden\" name=\"user_hash\" value=\"{$dle_login_hash}\" />
		<input type=\"submit\" class=\"btn btn-green\" value=\"{$lang['user_save']}\">
		</div>"
	);

echo <<< HTML
		</table>
	</div>
</div>
</form>
HTML;

}

echofooter();

?>