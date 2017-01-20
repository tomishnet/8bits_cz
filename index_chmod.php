<?php

function chmod_r($Path) {
    $dp = opendir($Path);
     while($File = readdir($dp)) {
       if($File != "." AND $File != "..") {
         if(is_dir($File)){
            chmod($File, 0755);
            chmod_r($Path."/".$File);
         }else{
             chmod($Path."/".$File, 0775);
         }
       }
     }
   closedir($dp);
}

function delete_dir($src) { 
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                delete_dir($src . '/' . $file); 
            } 
            else { 
                unlink($src . '/' . $file); 
            } 
        } 
    } 
    rmdir($src);
    closedir($dir); 
}

// session start
session_start();

//chmod("../new/.htaccess", 0775);
chmod_r("data");
//chmod_r("backups");

//delete_dir("data");
//echo realpath(dirname(__FILE__));
//phpinfo();