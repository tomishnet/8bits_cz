<?php
require 'fb-sdk/src/facebook.php';

class PullFb{
	
	//set properties
	public $facebook;
	public $page_num;
	
	//number of items you want to see per page
	//you can add for other objects such as videos, etc.
	public $albums_per_page = 2;
	public $photos_per_page = 4;
	
	//if you want to exclude some albums like the Profile Pictures and Wall Photos, do something like:
	//$excluded_albums = "AND name <> 'Profile Pictures' AND name <> 'Wall Photos'";
	//in my example, I'm gonna exclude the wall photos only
	public $excluded_albums = "AND name <> 'Wall Photos'";
	
	//same with excluding photos, just state the pid
	public $excluded_photos = "AND pid <> '221167777906963_1513599' AND pid <> '221167777906963_1513596'";
	
	public function __construct( $appId, $secret, $page_num ){
		
		//create facebook instance
		$this->facebook = new Facebook(array(
		  'appId'  => $appId,
		  'secret' => $secret,
		  'cookie' => true, // enable optional cookie support
		));
		
		//for the page number
		$this->page_num = $page_num;
	}
	
	//this will get facebook albums based on the $owner or fan page id
	public function getAlbums( $owner ){
		
		//we have to get the total number of albums first
		//i don't know why the count function is not working
		$fql = "SELECT aid FROM album WHERE owner = {$owner} {$this->excluded_albums}";
		
		//calculatePaging() will give us the paging settings
		//pass the fql and type of object
		$settings = $this->calculatePaging( $fql, 'album' );
		
		//get start and end limit for the next fql query
		$start_limit = $settings['start_limit'];
		$end_limit = $settings['end_limit'];
		
		//in this query we will include the paging based on the page number
		$fql = "SELECT 
					aid, object_id, owner, cover_pid, cover_object_id, name, created, modified,
					description, location, size, link, visible, modified_major, edit_link,
					type, can_upload, photo_count, video_count, 
					like_info, comment_info
				FROM
					album 
				WHERE 
					owner = {$owner} {$this->excluded_albums}
				LIMIT 
					{$start_limit}, {$end_limit}";
					
		//set params
		$params = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);
		
		//get recordset
		$result = $this->facebook->api( $params );
		
		//we will include the settings to our result variable
		$result['pull_fb'] = $settings;
		
		return $result;
	}


	//this function was made for paging
	public function calculatePaging( $fql, $type ){
	
		//set the params based on passed fql
		//to count the total number of records
		$params = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);
		
		//get recordset
		$result = $this->facebook->api( $params );
		
		//get the total number of items
		$number_of_items = count( $result );
		
		//we will have the following calculations for the pagination
		//we just need some simple math
		
		//decide how many albums to show per page
		//values was actually declared as class property
		//you can easily add for other types of object
		if( $type == 'album' ){
			$items_per_page = $this->albums_per_page;
		}else if( $type == 'photo' ){
			$items_per_page = $this->photos_per_page;
		}
		
		//this is the current page
		$curr_page = $this->page_num;
		
		//previous page will be the current page MINUS one
		$prev_page = $curr_page - 1;
		
		//next page will be the current page PLUS one
		$next_page = $curr_page + 1;
		
		//no need to calculate for the first page, obviously, it's 1
		//calculate last page
		$last_page = round( $number_of_items / $items_per_page );
		
		//detect if prev button will be visible
		if( $curr_page != 1 ){
			$prev_button = true;
		}
		
		//get $albums_shown value
		//it is the number of photos from the first page up to the current page
		$items_shown = $items_per_page * $curr_page;
		
		//detect if next button will be visible
		//if the $number_of_albums were still higher than the $albums_shown, show the next page button,
		//but if they are equal, don't show the next page button
		if( $number_of_items > $items_shown ){
			$next_button = true;
		}
		
		//get start limit for the fql query
		$start_limit = $items_per_page * $prev_page;
		
		//get end limit
		//i'm not sure why i had to + 1, maybe it's a facebook bug?
		$end_limit = $items_per_page + 1;
		
		//these are the values or settings returned
		//i made it to an array
		$settings = array(
			'number_of_items' => $number_of_items,
			'prev_page' => $prev_page,
			'next_page' => $next_page,
			'prev_button' => $prev_button,
			'next_button' => $next_button,
			'start_limit' => $start_limit,
			'end_limit' => $end_limit,
			'last_page' => $last_page
		);
		
		return $settings;
	}
	
	//to get photos of an album, we have to pass the album id
	public function getPhotos( $album_id ){

		//we have to get total number of photos first
		//i don't know why the count function is not working
		$fql = "SELECT object_id FROM photo WHERE aid = '" . $album_id ."' ORDER BY position DESC";
		
		//calculatePaging() will give us the paging settings
		//pass the fql and type of object, this is 'photo'
		$settings = $this->calculatePaging( $fql, 'photo' );
		
		//get start and end limit for the next fql query
		$start_limit = $settings['start_limit'];
		$end_limit = $settings['end_limit'];
		
		//query the photos
		$fql = "SELECT 
						object_id, pid, src_small, src, src_big, link, caption, created, modified, position, like_info, comment_info
					FROM 
						photo
					WHERE 
						aid = '" . $album_id ."' {$this->excluded_photos}
					ORDER BY 
						position DESC
					LIMIT 
						{$start_limit}, {$end_limit}";
		
		//set the parameters
		$params = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);

		//get photos recordset
		$result = $this->facebook->api( $params );
		
		//add the settings to the result
		$result['pull_fb'] = $settings;
		
		return $result;
	}
	
	//to get album cover, you need to pass the cover_id or id of the photo
	public function getAlbumCover( $cover_pid ){
	
		//get album cover query
		$fql = "select src_big from photo where pid = '" . $cover_pid . "'";
		
		$params = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);
		
		//run the query
		$result = $this->facebook->api( $params );
		
		//the the value and return it
		$value = $result[0]['src_big'];
		
		return $value;
	}
	
	//to get comments, you need to pass the object id, it can be a video, photo, album etc
	//check the fql tables to know object id https://developers.facebook.com/docs/reference/fql/
	public function getComments( $object_id ){
	
		//query the comment
		$fql = "SELECT
				text, time, fromid, likes
			FROM
				comment
			WHERE 
				object_id = " . $object_id;
		
		//set parameters
		$params = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);
		
		//get recordset
		$result = $this->facebook->api( $params );
		
		return $result;
	}
	
	//to get profile name, you need to pass fromid or the profile id
	public function getProfileName( $fromid ){
		//query commenter / profile name
		$fql = "SELECT
				name
			FROM
				profile
			WHERE 
				id = " . $fromid;
		
		//set the paramters
		$params = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);
		
		//get the resulting value
		$result = $this->facebook->api( $params );
		$value = $result[0]['name'];
		
		return $value;
	}
	
	//this time we will just get the profile name and profile thumbnail
	public function getProfileDetails( $fromid ){
	
		//select name and pic_square which can be used as profile thumbnail
		$fql = "SELECT
				name, pic_square
			FROM
				profile
			WHERE 
				id = " . $fromid;
		
		//set parameters
		$params = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);
		
		//get and return the result
		$result = $this->facebook->api( $params );
		
		return $result;
	}
	
	//to get album name, pass the album id
	public function getAlbumName( $aid ){
	
		//query album name
		$fql = "SELECT
				name
			FROM
				album
			WHERE 
				aid = '{$aid}'";
		
		//set parameters
		$params = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);
		
		//return result value for album name
		$result = $this->facebook->api( $params );
		$value = $result[0]['name'];
		
		return $value;
	}
	
	//to get events, pass the uid or you fan page id
	public function getEvents( $uid ){
		//query the events
		//we will eid, name, pic_big, start_time, end_time, location, description  this time
		//but there are other data that you can get on the event table (https://developers.facebook.com/docs/reference/fql/event/)
		//as you've noticed, we have TWO select statement here
		//since we can't just do "WHERE creator = your_fan_page_id".
		//only eid is indexable in the event table, so we have to retrieve
		//list of events by eids
		//and this was achieved by selecting all eid from
		//event_member table where the uid is the id of your fanpage.
		//*yes, you fanpage automatically becomes an event_member
		//once it creates an event
		$fql = "SELECT 
					eid, name, pic_big, start_time, end_time, location, description 
				FROM 
					event
				WHERE 
					eid IN ( SELECT eid FROM event_member WHERE uid = {$uid} ) 
				ORDER BY start_time asc";
								
		//set parameters
		$param = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);

		//get recordset and retur results
		$result = $this->facebook->api($param);
		return $result;
	}
	
	//this function will get the profile invited to the event
	//pass eid or event id
	public function getEventMembers( $eid ){
		
		//query the members
		$fql = "SELECT 
					eid, uid, rsvp_status 
				FROM 
					event_member 
				where eid = {$eid}";
		
		//set params
		$param = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);
		
		//get result and return it
		$result = $this->facebook->api( $param );
		return $result;
	}
	
	//get the videos
	//pass the owner or the fan page id
	public function getVideos( $owner ){
	
		//query to get videos
		//specify you fan page id, I got 221167777906963
		//you can also use the LIMIT clause here if you want to show limited number of videos only
		$fql = "SELECT 
					vid, owner, title, description, thumbnail_link, 
					embed_html, updated_time, created_time, link
				FROM 
					video
				WHERE owner={$owner}";
					
		//set parameters
		$param = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);
		
		//get recordset and return it
		$result = $this->facebook->api($param);
		return $result;
	}
	
}
?>