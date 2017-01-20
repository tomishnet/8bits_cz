<?php
/*
Plugin Name: TS FastReplacer
Description: Changes items in XML-Files.
Version: 0.1
Author: Lars Flintzak
Author URI: 
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, 	# ID of plugin, should be filename minus php
	'TS FastReplacer', 	# Title of plugin
	'0.1', 		# Version of plugin
	'Lars Flintzak',	# Author of plugin
	'http://www.gowep.de/gsplugins', 	# Author URL
	'Changes items in XML-Files.', 	# Plugin Description
	'pages', 	# Page type of plugin
	'FastReplacer_tab'  	# Function that displays content
);

# activate filter
// add_filter('content','do_FastReplacer');
add_action('plugins-sidebar', 'createSideMenu', array($thisfile, 'TS FastReplacer'));

function read_dir($dir,$ext) 
{
   $array = array();
   $d = dir($dir);
   while (false !== ($entry = $d->read())) {
       if($entry!='.' && $entry!='..') {
           $entry = $dir.'/'.$entry;
           if(is_dir($entry)) {
               // $array[] = $entry;
               $array = array_merge($array, read_dir($entry,$ext));
           } else {
                if(substr($entry, -strlen($ext)) == $ext)  $array[] = $entry;
           }
       }
   }
   $d->close();
   return $array;
}

function FastReplacer_on_common()
{
// die("Aha");
}

function FastReplacer_tab() {

	if (isset($_POST['save']))
	{
		$success =  ts_FastReplacer_save_settings( $_POST['search'],$_POST['replace']);
	}
	if (isset($_POST['do']))
	{
		$success =  do_FastReplacer();
	}
	$settings = ts_FastReplacer_get_settings();
	echo '<form class="largeform" id="edit" action="load.php?id=ts_fastreplacer" method="post">
<h3 class="Floated">TS FastReplacer</h3>

Enter one item per line. Only XML-Files in "pages" will be affected.<br>
<br>
<h3>Example:</h3><br>
<b>Search:</b><br>
a<br>
b<br>
<br>
<b>Replace:</b><br>
1<br>
2<br>
<br>
Any "a" character will be replaced with the number "1".<br>
Any "b" character will be replaced with the number "2".<br>
<br>
<br>
<h3>Be carefull!</h3>
<br>
<br>
	<p>
		<label for="append">Search:</label>
		<textarea id="search" name="search" style="height:100px">'.$settings->search.'</textarea>
		<label for="append">Replace:</label>
		<textarea id="replace" name="replace" style="height:100px">'.$settings->replace.'</textarea>
	</p>
	<p>
		<input name="save" type="submit" class="submit" value="Save changes" />
		<input name="do" type="submit" class="submit" value="Do Replace" />
	</p>
</form>';
}

function do_FastReplacer()
{
	$settings  = ts_FastReplacer_get_settings();
	$ss = explode("\n", $settings->search);
	$rr = explode("\n", $settings->replace);
	
	// print_r($ss);
	// print_r($rr);
	
	$array1 = read_dir("../data/pages","xml");
	for ($x = 0; $x < sizeof($array1); ++$x)
    {
    $a = file_get_contents(current($array1));
    $b = str_replace($ss, $rr, $a);
    file_put_contents(current($array1), $b);
    next($array1);
    }
	
	// $contents = str_replace($s, $r, $contents);
}

function ts_FastReplacer_get_settings() 
{
    $file = GSDATAOTHERPATH . 'ts_FastReplacer.xml';
	if (!file_exists($file)) {
		ts_FastReplacer_save_settings(); //create empty one
	}
	$data = getXML($file);
	$settings = new stdClass();
	$settings->search = (string) $data->search;
	$settings->replace = (string) $data->replace;
	return $settings;
}

function ts_FastReplacer_save_settings($s = '',$r = '') 
{
    $file = GSDATAOTHERPATH . 'ts_FastReplacer.xml';
	$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><settings></settings>');
	$obj = $xml->addChild('search');
	$obj->addCData($s);
	$obj = $xml->addChild('replace');
	$obj->addCData($r);
	return XMLsave($xml, $file) === true ? true : false;
}
?>
