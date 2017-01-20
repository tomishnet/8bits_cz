<?php
/* --------------------------------------------------------------------------------------------------------
   Image Resizer for GetSimple

  ----------------------------------------------------------------------------------------------------------- */

require_once('../IRImage.php');

require_once('../../../gsconfig.php');
$isDebug = defined('GSDEBUG') && GSDEBUG;

if (!$isDebug){
    error_reporting(0);
    ini_set('display_errors', 0);
}

$host = $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT'] != '80' ? ':'.$_SERVER['SERVER_PORT'] : '');
$pathParts = pathinfo($_SERVER['PHP_SELF']);
$subDir = str_replace('plugins/ImgResizer/img', '', $pathParts['dirname']);
$port = ($p=$_SERVER['SERVER_PORT'])!='80'&&$p!='443'?':'.$p:'';
$siteHost = http_protocol()."://". $host . $port;

//change this if you changed data dir for GS
$pluginDirPart = 'other/ImgResizer/';
$dataDirPart = 'data/';
$datadir = substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__), DIRECTORY_SEPARATOR.'plugins')) . '/'.$dataDirPart;
$cachedir = $datadir.$pluginDirPart . 'cache/'; // where to store the generated re-sized images, remember to change in plugin this too if you need

$settings = null;
if (file_exists($datadir.$pluginDirPart . 'imgresizer_settings.php')){ //check that file with settingsexists
   include ($datadir.$pluginDirPart . 'imgresizer_settings.php');

   if (isset($imgresizer_settings))
        $settings = $imgresizer_settings;
}

$browser_cache = 60*60*24*7; // How long the BROWSER cache should last (seconds, minutes, hours, days. 7days by default)

$requested_mode = @$_GET['mode'];
$resolution_string = @$_GET['resolution']; //store temporarly
$requested_width = null;
$requested_height = null;

$requested_path =  @$_GET['img']; //requested path

//delete site host and dir and data from image url
if ( substr($requested_path, 0, strlen($siteHost.$subDir.$dataDirPart) ) == $siteHost.$subDir.$dataDirPart) {
    $requested_path = substr($requested_path, strlen($siteHost.$subDir.$dataDirPart));
}
else if (substr($requested_path, 0, strlen($subDir.$dataDirPart) ) == $subDir.$dataDirPart){ //if its root relative (ex. /data/uploads/image.jpg)
    $requested_path = substr($requested_path, strlen($subDir.$dataDirPart));
}
else{ //wrong path, reset to fail later
    $requested_path = '';
}

//re add data dir part and safety replace ../ relative path 
$requested_path = removerelativepath( $requested_path );

if (strcasecmp(substr($requested_path, 0, strlen($pluginDirPart) ) , $pluginDirPart) == 0)
    sendError("Cannot use cached image as source.");


$requested_file = basename($requested_path);
$requested_path = substr($requested_path, 0, - strlen($requested_file)); //delete file name from path

$source_file    = $datadir.$requested_path.$requested_file;

if (!$settings){
    sendError("Settings not found, configure plugin!");
}

//check requested by
if ($requested_mode !== 'height' && $requested_mode !== 'width' && $requested_mode !== 'fill' && $requested_mode !== 'fit'){
    sendError('Invalid "mode".');
}

//cast to integers
if ($requested_mode == 'height'){
	//check that requested size exists
	if (!in_array ( $resolution_string , $settings[$requested_mode], true )){
		sendError('Requested height not defined in settings.');
	}
	$requested_height = (int)$resolution_string; //cast to int
}
else if($requested_mode == 'width'){
	//check that requested size exists
	if (!in_array ( $resolution_string , $settings[$requested_mode], true )){
		sendError('Requested width not defined in settings.');
	}
	$requested_width = (int)$resolution_string; //
}
else{
    list($requested_width, $requested_height) = array_map('intval', explode('x', $resolution_string));

	if (!$requested_width || !$requested_height)
		sendError('Requested resolution wrong defined or 0 in value.');

	//check that requested size exists
	if (!in_array ( $resolution_string , $settings[$requested_mode], true )){
		sendError('Requested resolution not defined in settings.');
	}
}

// check if the file exists at all
if (!$requested_file || !file_exists($source_file)) {

  if (!$isDebug){
      header("Status: 404 Not Found");
      echo '404, not found: '.$requested_path.$requested_file;
  }
  else{
      sendError('Image not found.');
  }
  exit();
}

/* whew might the cache file be? */
$cache_file = $cachedir.'by-'.$requested_mode.'/'.$resolution_string.'/'.$requested_path.$requested_file;

$cache_dir = dirname($cache_file);

try {
    $useCached = file_exists($cache_file) && filemtime($source_file) <= filemtime($cache_file);

    $image = new IRImage($useCached ? $cache_file : $source_file);

    if (!$useCached){
        if ($requested_mode == 'height'){
            $res = $image->resize(null, $requested_height, 'fit', $settings['sharpen'] == 1);
        }
        else if($requested_mode == 'width'){
            $res = $image->resize($requested_width, null, 'fit', $settings['sharpen'] == 1);
        }
        else{
            $res = $image->resize($requested_width, $requested_height, $requested_mode, $settings['sharpen'] == 1);
        }

        if ($res){ //save only when image was not too small

            // does the directory exist already?
            if (!is_dir($cache_dir)) {
                if (!mkdir($cache_dir, 0755, true)) {
                  // check again if it really doesn't exist to protect against race conditions
                  if (!is_dir($cache_dir)) {
                    sendError("Failed to create cache directory: $cache_dir");
                  }
                }
            }

            $image->save($cache_file, $settings['quality']);
        }
    }
    $image->render($browser_cache);
} catch (Exception $e) {
    echo sendError($e->getMessage());
}


//removes relative path from path
function removerelativepath($file) {
    return preg_replace ('/\.\.?[\\\\\/]/', '' , $file);
}

function sendError($message){
    global $requested_path, $requested_file, $requested_width, $requested_height, $requested_mode;

    $message .= "\n\nPARAMETERS";
    $message .= "\nimg: ".@$_GET['img'];
    $message .= "\npath: ".$requested_path;
    $message .= "\nfilename: ".$requested_file;
    $message .= "\nrequested width: ".$requested_width . ' requested height: '.$requested_height;
    $message .= "\nmode: ".$requested_mode;

    IRImage::renderError($message, 650, 200);

}

function http_protocol() {
	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
	  return 'https';
	} else {
		return 'http';
	}
}