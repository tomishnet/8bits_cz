<?php
/*
Plugin Name: Adsense Enabler
Description: Allow Addsense adds to be shown on your website
Version: 1.0
Author: Johannes Pretorius
Author URI: http://www.dominion-it.co.za/
*/
//Global  values
$addSense_cfg = array(array("ad_id" => "default ID",'ad_client' => 'enter client id','ad_slot' => 'enter slot','ad_width' => '300','ad_height' => '250'));

//get correct id for plugin
$thisfile=basename(__FILE__, ".php");


// register plugin
register_plugin(
	$thisfile, 
	'Adsense Enabler', 	
	'1.0', 		
	'Johannes Pretorius',
	'http://www.dominion-it.co.za/', 
	'Allow Adsense adds to be shown on your website',
	'pages',
	'show_add'  
);

/* activate filter*/
add_filter('content','content_adsense_show'); 
add_action('pages-sidebar','createSideMenu',array($thisfile,'Addsense'));

//functions


/*
  Configure the Addsense entry and store to file.
*/
function show_add() {
    global $addSense_cfg;
    
    $configExist = FALSE;
    $targetID = isset($_GET['addid'])?$_GET['addid']:NULL;
    
	if (is_file(GSDATAOTHERPATH. '/addsense.cfg')) { 
      $tmpS = implode("", @file(GSDATAOTHERPATH. '/addsense.cfg'));
	  $addSense_cfg =  unserialize($tmpS);    
      unset($tmpS);      
      $configExist = TRUE;
    }
    
    if(isset($_POST['add_new']) && $_POST['add_new'] == 'Add New Add') {
       $aIndex = getAddIndex("new ID");
       if ($aIndex == -1) {
         createNewAddEntry();
       } 
       $targetID = "new ID";       
       saveCurrentDatainMemory();
    }
    
    if(isset($_POST['stoor']) && $_POST['stoor'] == 'Save') {
            
            $fV  = $_POST['first_add_id'] ;
            $aIndex = getAddIndex($fV);
            saveData($aIndex,$_POST['ad_id'],$_POST['ad_client'],$_POST['ad_slot'],$_POST['ad_width'],$_POST['ad_height']);
            
            $targetID = $_POST['ad_id'];
    }
    
    
    
    //used to filter the contents of the form to this ID, if no config yet, take the defaul as setup in global array
    if ($configExist == FALSE) {
       //take the current Item (deafult one)
       $tmpA = $addSense_cfg[0];
       saveCurrentDatainMemory();
    } else {
       //have config.. check if there is one selected by user.. else take 1st one in the array itself
       if (isset($targetID)) {
         //search for item id if there is filter on it...
         $aIndex = getAddIndex($targetID);
         if ($aIndex != -1) {
           $tmpA = $addSense_cfg[$aIndex];
         }  else {
           $tmpA = $addSense_cfg[0];
         }
       } else {
         $tmpA = $addSense_cfg[0];
       } 
    }
    $currentAddID =$tmpA['ad_id'];
    
    //Todo : Add code if item not found,, not to happen. but everthing is possible
?>    
    <h3>Insert Adsense Client information</h3>
    <p>This form allows you to insert the client information that Google requires to allow Adsense to work, please review 
       the information google has given you to enable this plugin. Note that the Add ID is used as a unique identifier for the add that you asign yourself. 
       This gets used to specify what add you want to show when calling the appropriate fuction.<br/>
       <u>Functions</u><br/>
       <i>adsense_show($add_id)</i>  - Function to show the add in your template or component code. <br/>
       <i>(%ad:ad_id%)</i> - Is content marker. This gets placed inside the Pages text to specify where the add must be placed if you dont want to edit the template code and want to control
       the adds per page.<br/>
       Example : (%ad:top%) will place the add with unique Add ID = top at the spot where I placed the marker in my page.
       </p>
    <form action="<?php	echo $_SERVER ['REQUEST_URI']?>"  method="post" id="management">
    <input type='hidden' name='first_add_id' value='<?php echo $currentAddID; ?>'>
    <input type='submit' name='add_new' value='Add New Add'><br/>
    Add ID : <br/>
	<input name="ad_id" type="text" value="<?php echo $currentAddID; ?>" /><br/> 
    Client ID : <br/>
	<input name="ad_client" type="text" value="<?php echo $tmpA['ad_client']; ?>" /><br/> 
    Slot : <br/>
	<input name="ad_slot" type="text" value="<?php echo $tmpA['ad_slot']; ?>" /><br/> 
    Width : <br/>
	<input name="ad_width" type="text" value="<?php echo $tmpA['ad_width']; ?>" /><br/> 
    Height : <br/>
	<input name="ad_height" type="text" value="<?php echo $tmpA['ad_height']; ?>" /><br/> 
    <input type="submit" name="stoor" value="Save" />
    </form><br/>
    <table>
        <tr>
        <th>Add Id</th>
        <th>Client Code</th>
        <th>Width</th>
        <th>Height</th>
        </tr>
    
    <?php
      $url = $_SERVER['REQUEST_URI'];
      if (strpos($url,'&addid') !=  FALSE) {
        $url = substr_replace($url,'',strpos($url,'&addid'));
      }  
      foreach ($addSense_cfg as $checkTmp) {
        echo "<tr>";
        echo "<td><a href='$url&addid={$checkTmp['ad_id']}'>{$checkTmp['ad_id']}</a></td>";
        echo "<td><a href='$url&addid={$checkTmp['ad_id']}'>{$checkTmp['ad_client']}</a></td>";
        echo "<td>{$checkTmp['ad_width']}</td>";
        echo "<td>{$checkTmp['ad_height']}</td>";
        echo "</tr>";
      }
    ?>
    </table>

    

<?php
}

/*
  Show the addsense entry
*/
function adsense_show($add_id = "default ID") {
    global $addSense_cfg;
	if(is_file(GSDATAOTHERPATH. '/addsense.cfg')) { 
      $tmpS = implode("", @file(GSDATAOTHERPATH. '/addsense.cfg'));
	  $addSense_cfg =  unserialize($tmpS); 
      unset($tmpS);      
    }
    $dieIndex = getAddIndex($add_id);
    $AddSenseString = '';
    if ($dieIndex != -1) {
       $AddSenseString = "<script type='text/javascript'><!-- \r\n google_ad_client = '{$addSense_cfg[$dieIndex]['ad_client']}'; \r\n google_ad_slot = '".$addSense_cfg[$dieIndex]['ad_slot']."'; \r\n ".
                       " google_ad_width = ".$addSense_cfg[$dieIndex]['ad_width']."; \r\n  google_ad_height = ".$addSense_cfg[$dieIndex]['ad_height']."; \r\n //--> \r\n  </script> ".
                       " <script type='text/javascript' src='http://pagead2.googlesyndication.com/pagead/show_ads.js'> \r\n </script> ";
      
    } 
    echo $AddSenseString;                                            
   
}

/*
 Outputs adsense code instead of echo'ing it.
*/

function adsense_return($add_id = "default ID") {
    global $addSense_cfg;
	if(is_file(GSDATAOTHERPATH. '/addsense.cfg')) { 
      $tmpS = implode("", @file(GSDATAOTHERPATH. '/addsense.cfg'));
	  $addSense_cfg =  unserialize($tmpS); 
      unset($tmpS);      
    }
    $dieIndex = getAddIndex($add_id);
    $AddSenseString = '';
    if ($dieIndex != -1) {
    
      $AddSenseString = "<script type='text/javascript'><!-- \r\n google_ad_client = '{$addSense_cfg[$dieIndex]['ad_client']}'; \r\n google_ad_slot = '".$addSense_cfg[$dieIndex]['ad_slot']."'; \r\n ".
                       " google_ad_width = ".$addSense_cfg[$dieIndex]['ad_width']."; \r\n  google_ad_height = ".$addSense_cfg[$dieIndex]['ad_height']."; \r\n //--> \r\n  </script> ".
                       " <script type='text/javascript' src='http://pagead2.googlesyndication.com/pagead/show_ads.js'> \r\n </script> ";
    }                   
   return $AddSenseString;                       
}

/*
  Filter Content for adsense markers (%ad_id%)
    the add of that id will be inserted in the markers section of the conent
*/
function content_adsense_show($contents){

    $tmpContent = $contents;
	preg_match_all('/\(%(.*)ad(.*):(.*)%\)/i',$tmpContent,$tmpArr,PREG_PATTERN_ORDER);
    
    $AlltoReplace = $tmpArr[count($tmpArr)-1];
    $totalToReplace = count($AlltoReplace);
    for ($x = 0;$x < $totalToReplace;$x++) {
       $targetAdd= str_replace('&nbsp;',' ',$AlltoReplace[$x]);
       $targetAdd = trim($targetMp3);
      $adTeks = adsense_return($targetAdd);
      $tmpContent = preg_replace("/\(%(.*)ad(.*):(.*)$targetAdd(.*)%\)/i",$adTeks,$tmpContent);
    }
    
  return $tmpContent;
}



//Internal functions not really part of pulblic section
function getAddIndex($searchID){
  
  global $addSense_cfg;
  $x = 0;
  foreach ($addSense_cfg as $checkTmp) {
    if ($checkTmp['ad_id'] == $searchID) {
      return $x;
    }
    $x++;
  }
  return -1;
}

function createNewAddEntry(){
  global $addSense_cfg;
   
  $addSense_cfg[] = array('ad_id' => "new ID",'ad_client' => 'enter client id','ad_slot' => 'enter slot','ad_width' => '300','ad_height' => '250');
}

function saveData($indexAdd,$ad_id,$ad_client,$ad_slot,$ad_width,$ad_height){
    global $addSense_cfg;
    $addSense_cfg[$indexAdd]['ad_id']  = $ad_id;
    $addSense_cfg[$indexAdd]['ad_client'] = $ad_client;
    $addSense_cfg[$indexAdd]['ad_slot'] = $ad_slot;
    $addSense_cfg[$indexAdd]['ad_width'] = $ad_width;
    $addSense_cfg[$indexAdd]['ad_height'] = $ad_height ;
    
    saveCurrentDatainMemory();

}
function saveCurrentDatainMemory(){
    global $addSense_cfg;
    
    $tmpS = serialize($addSense_cfg);

    $filePointer = fopen(GSDATAOTHERPATH. '/addsense.cfg',"w");
    fwrite($filePointer,$tmpS);
    fclose($filePointer);
    unset($tmpS,$filePointer);      

}
?>