<?php

$thisfile = basename(__FILE__, ".php");

register_plugin(
	$thisfile, 
	'Redirect', 	
	'0.1', 		
	'Carlos Navarro',
	'http://www.cyberiada.org/cnb/', 
	'Simple page redirection'
);

if (!defined('REDIRECTTOKEN')) define('REDIRECTTOKEN','redirect=');

add_action('index-pretemplate','redirect_main');

function redirect_main() {
  global $title, $metad, $metak;
  redirect_check($title);
  redirect_check($metad);
  redirect_check($metak);
}

function redirect_check($str) {
  if (strpos($str, REDIRECTTOKEN) !== false) {
    $s = $str;
    $s = preg_replace('/[[:cntrl:]]/', ' ', $s);
    $s = str_replace(REDIRECTTOKEN.' ', REDIRECTTOKEN, $s);
    $s = str_replace('_'.REDIRECTTOKEN, REDIRECTTOKEN, $s);
    $a = explode(' ',trim($s));
    foreach ($a as $s) if (strpos($s, REDIRECTTOKEN) === 0) {
      $s = rtrim($s,',');
      $s = substr($s, strlen(REDIRECTTOKEN));
      if (substr($s, 0, 4) != 'http') {
        if (strpos($s, '.')) {
          $s = 'http://'.$s;
        } else {
          if (function_exists('returnPageField')) {
            $p = returnPageField($s, 'parent');
          } else {
            $p = '';
          }
          if (function_exists('find_i18n_url'))
            $s = find_i18n_url($s, $p);
          else
            $s = find_url($s, $p);
        }
      }
      redirect($s);
    }
  }
}
