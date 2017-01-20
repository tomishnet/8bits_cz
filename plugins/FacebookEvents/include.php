<?php
$opts = array(
  'http'=>array(
	'ignore_errors' => true
  )
);
$context = stream_context_create($opts);
$jsonurl = "http://graph.facebook.com/".$username;
$json = file_get_contents($jsonurl, false, $context);

$json_output = json_decode($json);

$json_error = $json_output->error;
$json_message = $json_error->message;

if (isset($json_error)) {
	echo "<h3>ERROR: ".$json_message." </h3>
	<p style='font-family:\"Courier New\", Courier, monospace; color:red; background:rgba(255,255,255,.5); padding:5px;'><strong>Web Developer:</strong> Please make sure your source is publicly accessible. If <a href='".$json_output->{'link'}."'>this</a> is not your source please make the appropriate changes.</p>";
} else {
$facebook_id = $json_output->{'id'};
$facebook_name = $json_output->{'name'};
$facebook_link = $json_output->{'link'};

//get page number
//if page num was not set, default to page 1
$page_num = isset( $_GET['page'] ) ? $_GET['page'] : 1;

//we have to set timezone to California
date_default_timezone_set('America/Los_Angeles');

//include our class
include 'pull_fb.class.php';

//pass appId, appSecret, and page number
$pull_fb = new PullFb( '383665608369702', '4d9f92aae5fde0441f445d0ac14c7dcb', $page_num );

//pass your fan page id
$event_result = $pull_fb->getEvents( $facebook_id );

if ($event_result == NULL) {
	//just a heading
	/*
echo "<h3>Sorry, we have no recorded upcoming events.</h3>";
echo "Get updated from our ";
echo "<a href='".$facebook_link."?sk=events'>";
echo $facebook_name." Facebook Page</a> when we add some";
*/
} else {

//just a heading
/*

echo "<div style='font-weight: bold; margin: 0 0 20px 0;'>";
echo "Novinky z naší facebookové nástěnky <!--Event list taken from our--> ";
echo "<a href='".$facebook_link."?sk=events'>";
echo $facebook_name."  <!--Facebook Page--></a></div>";
*/

echo "<div class='intro'><h2>Novinky na Facebooku</h2></div>";
?>
<style>
h3.field_item {margin-top:0px;display:inline-block;padding-right:4px;}
.object_item img {padding-bottom: 10px;}
.object_item {padding:10px; margin:10px; background:rgba(255, 255, 255 , .2);}
.object_item:hover {background:rgba(255, 255, 255 , .4); color: black;}
.object_item:active {background:rgba(255, 255, 255 , .3); color: #333;}
.val {display:inline-block;margin-left:3px;overflow:hidden;}
.field_item h4 {display:inline-block;margin-top:5px}
.field_item {min-width:100%;}
.nolink {text-decoration:none;color:none;}
</style>
<?php
//looping through retrieved data
foreach( $event_result as $key => $value ){

	//see here http://php.net/manual/en/function.date.php for the date format I used
	//The pattern string I used 'l, F d, Y g:i a'
	//will output something like this: July 30, 2015 6:30 pm

	//getting 'start' and 'end' date,
	//'l, F d, Y' pattern string will give us
	//something like: Thursday, July 30, 2015
	/*
	$end_date = date( 'l, F d, Y', $value['end_time'] );
		
	$start_date = date( 'l, F d, Y', strtotime( $value['start_time'] ) );
	$end_date = date( 'l, F d, Y', strtotime(  $value['end_time'] ) );
	*/
	/*
	var_dump($value['start_time']);
	var_dump($value['end_time']);
	exit;
	*/
	
	$end_date = "";
	if($value['end_time']!=null)
	$end_date = date( 'j. n. Y ', $value['end_time'] );
		
	$start_date = date( 'j. n. Y ', strtotime( $value['start_time'] ) );

	if($value['end_time']!=null)
	$end_date = date( 'j. n. Y ', strtotime(  $value['end_time'] ) );
	
	//getting 'start' and 'end' time
	//'g:i a' will give us something
	//like 6:30 pm
	/*
	$start_time = date( 'g:i a', strtotime( $value['start_time'] ) );
	$end_time = date( 'g:i a', strtotime( $value['end_time'] ) );
	*/
	
	$start_time = date( 'h:i', strtotime( $value['start_time'] ) );
		
	if($value['end_time']!=null)
	$end_time = date( 'h:i', strtotime( $value['end_time'] ) );
	
	$eventid = $value['eid'];
	//display the album details
	$linkurl = 'https://www.facebook.com/events/'.$eventid.'/';
	echo '<div class="object_item" >';

	//event image
	echo "<div style='float: left; width: 20%; margin: 0 8px 0 0;'>";
			echo "<img src={$value['pic_big']} />";
	echo "</div>";
		
		echo "<div style='float: left; width: 70%;'>";
			
			//event name
			echo "<h3 class='field_item'>";
						echo $value['name'];
			echo "</h3>";
			
			echo "<div class='field_item'>";
			//event date / time
				if( $start_date == $end_date ){
						//if $start_date and $end_date is the same
						//it means the event will happen on the same day
						//so we will have a format something like:
						//July 30, 2015 - 6:30 pm to 9:30 pm
						//echo "<div class='val'>v {$start_date} - {$start_time} do {$end_time}</div>";
						echo "<h4>Kdy:</h4><div class='val'>{$start_date}</div>";
				}else{
						//else if $start_date and $end_date is NOT the equal
						//it means that the event will will be
						//extended to another day
						//so we will have a format something like:
						//July 30, 2013 9:00 pm to Wednesday, July 31, 2013 at 1:00 am
						//echo "<div class='val'>dne {$start_date} {$start_time} do {$end_date} v {$end_time}</div>";
						echo "<h4>Kdy:</h4><div class='val'>{$start_date} </div>";
				}
			echo "</div>";
			
			if ( $value['location'] != "") {
			//event location
			echo "<div class='field_item'>";
				echo "<h4>Kde: <!--Location:--></h4>";
				echo "<div class='val'>" . $value['location'] . "</div>";
			echo "</div>";
			}
			
			if ( $value['description'] != "") {
			//event description
			echo "<div class='field_item'>";
				echo "<!--<h4>More Info:</h4>-->";
				echo "<div class='val'>";
				
// The Regular Expression filter
$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

// The Text you want to filter for urls
$text = $value['description'];

// Check if there is a url in the text
if(preg_match($reg_exUrl, $text, $url)) {
	   // echo text without URL string.
	   echo str_replace($url[0], "", $text);
       // echo URL
       echo '<a href=\''.$url[0].'\'">[link]</a>';

} else {
       // if no urls in the text just return the text
       echo '<p>'.$text."</p>";
}
			echo "</div>"; // val end div

			echo "</div>";
		}
			echo "<a style='display:block; float: right;' href='".$linkurl."' target='_blank'>[Více na našem Facebooku <!--View on Facebook-->]</a>";
			echo "<div style='clear: both'></div>";
			
		echo "</div>";
	
	echo "<div style='clear: both'></div>";
	
	echo "</div>";
}
}       
}

?>