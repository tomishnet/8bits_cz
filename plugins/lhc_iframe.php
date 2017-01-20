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
define('LHC_IFRAME_FILE', basename(__FILE__, ".php"));
//$thisfile=basename(__FILE__, ".php");

// language support
i18n_merge('lhc_iframe') || i18n_merge('lhc_iframe', 'en_US');


# register plugin
register_plugin(
    LHC_IFRAME_FILE,
    i18n_r(LHC_IFRAME_FILE . '/PLUGIN_TITLE'),
    '1.0',
    'Tomáš Plachý',
    'http://www.tomish.net/',
    'Implements iframe with Live helper chat in admin back office',
    'lhc_iframe',
    'lhc_show'
);

# activate filter
//add_action('theme-footer','hello_world'); 
//add_action( 'theme-sidebar', 'createSideMenu', array( $thisfile, 'Hello World description', 'hello_world' ) );
add_action('nav-tab', 'createNavTab', array('lhc_iframe', LHC_IFRAME_FILE, 'Chat', 'manage'));
//add_action('lhc_iframe-sidebar','createSideMenu',array(LHC_IFRAME_FILE, 'Live Helper Chat', 'manage'));
add_action('lhc_iframe-sidebar', 'createSideMenu', array(LHC_IFRAME_FILE, i18n_r(LHC_IFRAME_FILE . '/LIVE_HELPER_CHAT'), 'manage'));
add_action('lhc_iframe-sidebar', 'createSideMenu', array(LHC_IFRAME_FILE, i18n_r(LHC_IFRAME_FILE . '/SETTINGS'), 'settings'));
# functions

function lhc_show()
{
    global $SITEURL; // Declare GLOBAL variables


    if (isset($_GET['manage'])) {
        echo '<h3>Live Helper chat <a href="' . $SITEURL . 'lhc/index.php/site_admin/user/login" target="_blank"><i class="fa fa-share-square-o"></i></a></h3>';
        echo '<iframe width="640" height="500" src="' . $SITEURL . 'lhc/index.php/site_admin/user/login" style="border: none;"></iframe>';
    }

    if (isset($_GET['settings'])) {
        echo '<h3>Live Helper chat settings</h3>';
    }
}

?>