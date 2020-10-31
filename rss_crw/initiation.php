<?php
function rss_muk_create_plugin_database_table()
{
    global $table_prefix, $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	
    $tblname='rss_muk_link';
    $wp_track_table = $table_prefix."$tblname";
    if($wpdb->get_var("show tables like $wp_track_table")!= $wp_track_table) 
    {	
		$sql="CREATE TABLE $wp_track_table (
		id bigint(32) NOT NULL AUTO_INCREMENT,
		link text NOT NULL,
		last_visited bigint(32) NOT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;";
        require_once( ABSPATH .'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
	
	$tblname='rss_muk_domain';
    $wp_track_table = $table_prefix."$tblname";
    if($wpdb->get_var("show tables like $wp_track_table")!= $wp_track_table) 
    {	
		$sql="CREATE TABLE $wp_track_table (
		id bigint(32) NOT NULL AUTO_INCREMENT,
		link text NOT NULL,
		last_visited bigint(32) NOT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;";
        require_once( ABSPATH .'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
	
	$tblname='rss_muk_g_phrase';
    $wp_track_table = $table_prefix."$tblname";
    if($wpdb->get_var("show tables like $wp_track_table")!= $wp_track_table) 
    {	
		$sql="CREATE TABLE $wp_track_table (
		id bigint(32) NOT NULL AUTO_INCREMENT,
		phrase text NOT NULL,
		page_no bigint(32) NOT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;";
        require_once( ABSPATH .'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
	
	$tblname='rss_muk_link_data';
    $wp_track_table = $table_prefix."$tblname";
    if($wpdb->get_var("show tables like $wp_track_table")!= $wp_track_table) 
    {	
		$sql="CREATE TABLE $wp_track_table (
		id bigint(32) NOT NULL AUTO_INCREMENT,
		link_id bigint(32) NOT NULL,
		head text NOT NULL,
		main_img text NOT NULL,
		data mediumtext NOT NULL,
		last_visited bigint(32) NOT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;";
        require_once( ABSPATH .'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
	
	$tblname='rss_muk_img_data';
    $wp_track_table = $table_prefix."$tblname";
    if($wpdb->get_var("show tables like $wp_track_table")!= $wp_track_table) 
    {	
		$sql="CREATE TABLE $wp_track_table (
		id bigint(32) NOT NULL AUTO_INCREMENT,
		link_id bigint(32) NOT NULL,
		img_url text NOT NULL,
		height int(6) NULL,
		width int(6) NULL,
		PRIMARY KEY  (id)
		) $charset_collate;";
        require_once( ABSPATH .'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
}


