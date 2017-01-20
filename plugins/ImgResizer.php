<?php
/*
Plugin Name: Image Resizer
Description: This plugins let's you dynamically generate resized images from your uploads folder. It supports resizing by width, height, fit in dimensions and fill (by cropping) in dimensions. It allows you to define allowed resolutions and quality of the generated images. It also automatically caches, resized images.
Version: 1.03
Author: Michał Gańko
Author URI: http://foureyes.pl
*/

# get correct id for plugin
$thisfile=basename(__FILE__, '.php');

# register plugin
register_plugin(
	$thisfile, 
	'Image Resizer', 	
	'1.03', 		
	'Michał Gańko',
	'http://foureyes.pl', 
	'This plugins let\'s you dynamically generate resized images from your uploads folder. It supports resizing by width, height, fit in dimensions and fill (by cropping) in dimensions. It allows you to define allowed resolutions and quality of the generated images. It also automatically caches, resized images.',
	'plugins',
	'ir_admin_tab'  
);

if ( !is_frontend() ){
    
    $res_currFile = strtolower(basename($_SERVER['PHP_SELF'])); //currently loadded file
    
    global $LANG;
    i18n_merge('ImgResizer', substr($LANG,0,2)) || i18n_merge('ImgResizer','en');

    add_action('header', 'ir_on_header'); 
    add_action('plugins-sidebar', 'createSideMenu', array($thisfile, i18n_r('ImgResizer/CONFIGURE') ));   
}

function ir_on_header(){
    global $res_currFile;
    
    if ( $res_currFile == 'load.php' && @$_GET['id'] == 'ImgResizer' ){
        require_once('ImgResizer/ImgResizerClass.php');
        $res = ImgResizerClass::getInstance();
        $res->configHeader();
    }
}

function ir_admin_tab() {
    require_once('ImgResizer/IRImage.php'); //used on config form
    require_once('ImgResizer/ImgResizerClass.php');
    $res = ImgResizerClass::getInstance();
    
    $saved = false;
    
    if (isset($_POST['save'])){
        $res->configSave(); 
        $saved = true;
    }  

    if (isset($_GET['clear'])){
        $res->clearCache(); 
    }
    
    $res->configForm($saved); 
}


//FRONTEND FUNCTIONS
function image_resizer_src($mode = null, $resolution = null, $img = null){
    global $SITEURL;

    return $SITEURL.'plugins/ImgResizer/img/?'.
           ($mode ? 'mode='.urlencode($mode).'&' : '').($resolution ? 'resolution='.urlencode($resolution).'&' : '').'img='.urlencode($img);

}