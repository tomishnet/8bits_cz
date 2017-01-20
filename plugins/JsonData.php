<?php if(!defined('IN_GS')){die('You cannot load this file directly!');} // Security Check
/*
Plugin Name: Hello World
Description: Echos "Hello World" in footer of theme
Version: 1.0
Author: Chris Cagle
Author URI: http://www.cagintranet.com/
*/

# get correct id for plugin

# Define some important stuff
define('THISFILE', basename(__FILE__, ".php"));
//$thisfile=basename(__FILE__, ".php");

// language support
i18n_merge('JsonData') || i18n_merge('JsonData', 'en_US');



# register plugin
register_plugin(
	THISFILE, 
	//i18n_r(THISFILE.'/PLUGIN_TITLE'),
	'JsonData',
	'1.0', 		
	'Tomáš Plachý',
	'http://www.tomish.net/', 
	'Implements Json template for ajax server side scripts',
	'JsonData',
	'jsonData_show'
);

# activate filter
//add_action('theme-footer','hello_world'); 
//add_action( 'theme-sidebar', 'createSideMenu', array( $thisfile, 'Hello World description', 'hello_world' ) );

//add_action('nav-tab','createNavTab',array('JsonData',THISFILE, 'Chat', 'manage'));

//add_action('JsonData-sidebar','createSideMenu',array(THISFILE, 'Live Helper Chat', 'manage'));
//add_action('JsonData-sidebar','createSideMenu',array(THISFILE,  i18n_r(THISFILE.'/JSON_DATA'), 'manage'));

//add_action('JsonData-sidebar','createSideMenu',array(THISFILE, i18n_r(THISFILE.'/SETTINGS'), 'settings'));
# functions
function hello_world() {
	echo '<p>Hello World</p>';
}

function jsonData_show() {
	global $SITEURL; // Declare GLOBAL variables

}

?>