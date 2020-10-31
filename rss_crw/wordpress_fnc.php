<?php
function rss_muk_ins_post($post_title,$post_content){
	// Create post object
$my_post = array(
  'post_title'    => wp_strip_all_tags( $post_title ),
  'post_content'  => $post_content,
  'post_status'   => 'publish',
  'post_author'   => 1
);
// Insert the post into the database
wp_insert_post( $my_post );
	
}
