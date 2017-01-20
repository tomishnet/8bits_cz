<?php

// register plugin
$thisfile = basename(__FILE__, ".php");
register_plugin(
	$thisfile,
	'Czechoslovak Transliteration',
	'0.1',
	'Tomáš Janeček & Carlos Navarro',
	'#',
	'Slug/URL transliteration for Czech and Slovak languages.'
);

$i18n['TRANSLITERATION'] = array(
	  //special Czech chars with diacritics
    "ě"=>"e","Ě"=>"E","š"=>"s","Š"=>"S","č"=>"c",
    "Č"=>"C","ř"=>"r","Ř"=>"R","ž"=>"z","Ž"=>"Z",
    "ý"=>"y","Ý"=>"Y","á"=>"a","Á"=>"A","í"=>"i",
    "Í"=>"I","é"=>"e","É"=>"E","ú"=>"u","Ú"=>"U",
    "ů"=>"u","Ů"=>"U","ť"=>"t","Ť"=>"T","ó"=>"o",
    "Ó"=>"O","ď"=>"d","Ď"=>"D","ň"=>"n","Ň"=>"N",
    
    //special Slovakian chars with diacritics (except those which are the same with Czech ones)
    "ä"=>"a","ĺ"=>"l","ľ"=>"l","ô"=>"o","ŕ"=>"r", 
    "Ä"=>"A","Ĺ"=>"L","Ľ"=>"L","Ô"=>"O","Ŕ"=>"R", 
    
    //other special chars
    " "=>"-","-"=>"-","»"=>">","."=>"."
    
);

// end of file