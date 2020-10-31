<?php
/**
 * @package rss_crw
 */
/*
Plugin Name: rss crw
Plugin URI: https://raabnits.com/
Description: developed for personal use only
Version: 4.1.2
Author: mukul
Author URI: https://raabnits.com/
License: MIT Lisense
Text Domain: none
*/
require "initiation.php";
require "admin_menu.php";

register_activation_hook( __FILE__, 'rss_muk_create_plugin_database_table' );
add_action('admin_menu','rss_muk_add_admin_page'); 

function rss_muk() {
    wp_register_style('rss_muk', plugins_url('css/rss_muk.css',__FILE__ ));
    wp_enqueue_style('rss_muk');
}

add_action( 'admin_init','rss_muk');



function rss_muk_custom_rewrite_basic2() {
  add_rewrite_rule('^word/([^/]*)/?', '/index.php?page_id=43&word=$matches[1]', 'top');
}
//add_action('init', 'custom_rewrite_basic',10,0);

add_filter('query_vars','rss_muk_my_add_ut_query_var');
function rss_muk_my_add_ut_query_var($vars) {
    array_push($vars, 'rss_muk_phrase');
	array_push($vars, 'rss_muk_url');
    return $vars;
}



// Add function to register event to WordPress init
add_action( 'init', 'rss_muk_register_revision_cron_event');

// Function which will register the event
function rss_muk_register_revision_cron_event() {
	// Make sure this event hasn't been scheduled
	if( !wp_next_scheduled( 'rss_muk_next_link_data_fetch_action' ) ) {
		// Schedule the event
		wp_schedule_event( time(), 'one_minute', 'rss_muk_next_link_data_fetch_action' );
	}
	if( !wp_next_scheduled( 'rss_muk_next_g_src_action' ) ) {
		// Schedule the event
		wp_schedule_event( time(), 'three_minutes', 'rss_muk_next_g_src_action' );
	}
}

// Add custom cron interval
add_filter( 'cron_schedules', 'rss_muk_add_custom_cron_intervals', 10, 1 );

function rss_muk_add_custom_cron_intervals( $schedules ) {
	// $schedules stores all recurrence schedules within WordPress
	$schedules['ten_minutes'] = array(
		'interval'	=> 600,	// Number of seconds, 600 in 10 minutes
		'display'	=> 'Once Every 10 Minutes'
	);

	$schedules['30_seconds'] = array(
		'interval'	=> 30,	
		'display'	=> 'Once Every 30 secs'
	);
	
	$schedules['15_seconds'] = array(
		'interval'	=> 15,	
		'display'	=> 'Once Every 15 secs'
	);
	$schedules['three_minutes'] = array(
		'interval'	=> 180,	
		'display'	=> 'Once Every 3 Minutes'
	);
	$schedules['two_minutes'] = array(
		'interval'	=> 120,	
		'display'	=> 'Once Every 2 Minutes'
	);
	$schedules['one_minute'] = array(
		'interval'	=> 60,
		'display'	=> 'Once Every 1 Minute'
	);
	
	// Return our newly added schedule to be merged into the others
	return (array)$schedules; 
}

//add_action( 'the_content', 'my_thank_you_text' );
/*
function custom_rewrite_rule() {
    add_rewrite_rule('^d/word/([^/]+)/?','index.php?page_id=43&word=$matches[1]','top');
}

add_action('init', 'custom_rewrite_rule');
function add_custom_query($query_vars){
	$query_vars[] = 'word';
	return $query_vars;
}
 add_filter( 'query_vars', 'add_custom_query' );

// flush_rewrite_rule();
/*
function set_query_varaible( $query_vars )
{
    $query_vars[] = 'word';

   return $query_vars;
}

   add_filter( 'query_vars', 'set_query_varaible' );

function custom_rewrite_rule() {
    add_rewrite_rule('^d/?([^/]*)/?','index.php?page_id=43&word=$matches[1]','top');
}
add_action('init', 'custom_rewrite_rule', 10, 0);
*/
//add_shortcode('rss_crw', 'rss_ini');
/*
function rss_ini ($atts, $content = null ) {
	/*
	echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
  <script>';
  * /
  
  $d_url=site_url()."/d/";
  $search_btn='
  <center>
  <div>

<form class="search-form">
<label>
<span class="screen-reader-text">Search dictionary word (english): </span>
  <input type="text" class="search-field" placeholder="enter rss url.." name="rss_crw_url">
  </label>
  <button type="submit" class="search-submit">Search</button>
</form>

</div></center>';
$dict_content="";
$dict_content.='<div class="dict_container">';
	if (isset( $_REQUEST['rss_muk_phrase'] )) {
		$word=$_REQUEST['rss_muk_phrase'];
		
		//$dict_content.=$word;
		$dict_content.=$search_btn;
	
	$wlen=strlen($word);
	$dict_content.=src1($word);
	//echo $wlen;
	//echo $word[$wlen-3].$word[$wlen-2].$word[$wlen-1];
	//--ing is handled
	if($wlen>3)
	if($word[$wlen-3]=='i'&&$word[$wlen-2]=='n'&&$word[$wlen-1]=='g'){
		//echo "-ing is added!<br>";
		 $dict_content.=src1(substr($word,0,$wlen-3));
	}
	//--ed is handled
	if($wlen>2)
	if($word[$wlen-2]=='e'&&$word[$wlen-1]=='d'){
		//echo "-ing is added!<br>";
		 $dict_content.=src1(substr($word,0,$wlen-2));
	}
    $dict_content.='</div>';
		
		
		
		
		
		
		//echo $dict_content;
		return $dict_content;
	}	
    return $dict_content.$search_btn."</div>";
}
*/

