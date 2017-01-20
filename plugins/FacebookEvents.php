<?php
/*
Plugin Name: Facebook Events
Description: Pulls Events from Facebook to display on the site.
Version: 1.0.1
Author: Michael Lindahl
Author URI: http://getsimple.michaellindahl.com/facebook-events/
*/

# get correct id for plugin
$thisfile_facebookeventsplugin=basename(__FILE__, ".php");
#$facebookevents_file=GSDATAOTHERPATH .'FacebookEventsSettings.xml';

# add in this plugin's language file
i18n_merge($thisfile_facebookeventsplugin) || i18n_merge($thisfile_facebookeventsplugin, 'en_US');

# register plugin
register_plugin(
	$thisfile_facebookeventsplugin, //Plugin id
	'Facebook Events', 	//Plugin name
	'1.0', 		//Plugin version
	'Michael Lindahl',  //Plugin author
	'http://www.michaellindahl.com/', //author website
	'This plugin allows you to pull events from Facebook and display them in your website. Just add this php \'get_facebook_events_from_username(\'USERNAME\');\' to a template.', //Plugin description
	'plugins', //page type - on which admin tab to display
	'fb_events_show'  //main function (administration)
);

/*
function check_version() {
	$my_plugin_id = 001; // replace this with yours
	
	$apiback = file_get_contents('http://get-simple.info/api/extend/?id='.$my_plugin_id);
	$response = json_decode($apiback);
	if ($response->status == 'successful') {
		// Successful api response sent back. 
		$facebookevents_current_ver = $response->version;
			return $facebookevents_current_ver;
	}
}*/

function get_facebook_events_from_username($username) {
include('FacebookEvents/include.php');
} 

?>