<?php
/*
Plugin Name: TS_LINKCHANGER
Description: Changes old siteurls in current urls.
Version: 0.1
Author: Lars Flintzak
Author URI: 
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, 	# ID of plugin, should be filename minus php
	'TS LinkChanger', 	# Title of plugin
	'0.2', 		# Version of plugin
	'Lars Flintzak',	# Author of plugin
	'http://www.gowep.de/gsplugins', 	# Author URL
	'Changes old siteurls in current urls.', 	# Plugin Description
	'pages', 	# Page type of plugin
	'LinkChanger_tab'  	# Function that displays content
);

# activate filter
// add_action('common','NoAbsLink_on_common'); 
add_filter('content','do_LinkChanger');
add_action('plugins-sidebar', 'createSideMenu', array($thisfile, 'TS LinkChanger'));

function LinkChanger_on_common()
{
// die("Aha");
}

function LinkChanger_tab() {

	if (isset($_POST['old_site'])){ //is post, saving
		$success =  ts_LinkChanger_save_settings( $_POST['old_site']	);
	}
	$settings = ts_LinkChanger_get_settings();
	echo '<form class="largeform" id="edit" action="load.php?id=ts_linkchanger" method="post">
<h3 class="Floated">TS LinkChanger</h3>

	<p>
		<label for="append">Link to change (Multiple separate by ","):</label>
		<input class="text" id="old_site" name="old_site" type="text" value="'.$settings->old_site.'" />
	</p>
	<p>
		<input name="post" type="submit" class="submit" value="Save changes" />
	</p>
</form>';
}

function do_LinkChanger($contents)
{
	$p  = ts_LinkChanger_get_settings();


	$s = explode(",", $p->old_site);

	$r = get_site_url(false);

	$contents = str_replace($s, $r, $contents);
	return $contents;
}

function ts_LinkChanger_get_settings() 
{
    $file = GSDATAOTHERPATH . 'ts_LinkChanger.xml';
	if (!file_exists($file)) {
		ts_LinkChanger_save_settings(); //create empty one
	}
	$data = getXML($file);
	$settings = new stdClass();
	$settings->old_site = (string) $data->old_site;
	return $settings;
}

function ts_LinkChanger_save_settings($s = '<yoursite>') 
{
    $file = GSDATAOTHERPATH . 'ts_LinkChanger.xml';
	$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><settings></settings>');
	$obj = $xml->addChild('old_site');
	$obj->addCData($s);
	return XMLsave($xml, $file) === true ? true : false;
}
?>
