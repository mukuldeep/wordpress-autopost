<?php
require "crw.php";
require "wordpress_fnc.php";




function rss_muk_view_all(){
	echo "<div class='rss_muk_admin_container'><div class='rss_muk_admin_item'>spiders are here! stay away!</div></div>";
	global $table_prefix, $wpdb;
	$link_table = $table_prefix."rss_muk_link";
	$domain_table = $table_prefix."rss_muk_domain";
	$g_phrase_table = $table_prefix."rss_muk_g_phrase";
	$link_data_table = $table_prefix."rss_muk_link_data";
	$img_data_table = $table_prefix."rss_muk_img_data";  
	
	echo "<div class='rss_muk_admin_container'>";
	echo "<div class='rss_muk_admin_item'>";
		$res = $wpdb->get_results("SELECT COUNT(id) as cnt FROM ".$link_table);
		echo "<p>link table total: ".$res[0]->cnt."</p>";
		$res = $wpdb->get_results("SELECT COUNT(id) as cnt FROM ".$domain_table);
		echo "<p> domain table total: ".$res[0]->cnt."</p>";
		$res = $wpdb->get_results("SELECT COUNT(id) as cnt FROM ".$g_phrase_table);
		echo "<p>g phrase table total: ".$res[0]->cnt."</p>";
		$res = $wpdb->get_results("SELECT COUNT(id) as cnt FROM ".$link_data_table);
		echo "<p>link data table total: ".$res[0]->cnt."</p>";
		$res = $wpdb->get_results("SELECT COUNT(id) as cnt FROM ".$img_data_table);
		echo "<p>img data table total: ".$res[0]->cnt."</p>";
	echo "</div>";
	
	
	echo "</div>";
	
}

function rss_muk_ins_new_domain($domain){
	global $table_prefix, $wpdb;
	$domain_table = $table_prefix."rss_muk_domain";
	$res = $wpdb->get_results("SELECT COUNT(id) as cnt FROM ".$domain_table." WHERE link='".$domain."'");
		if($res[0]->cnt>0){
			echo '<div class="updated error"><p>domain already exists!</p></div>';
		}else{
			$ins_data=array(
				'link' => $domain,
				'last_visited' => 0
			 );
			 if($wpdb->insert( $domain_table, $ins_data)){
					echo '<div class="updated notice"><p>domain has successfully been saved</p></div>';
			}else{
				echo '<div class="updated error"><p>error while saving the domain</p></div>';
			}
		}
}
function rss_muk_get_domain_from_link($link){
	$arr = parse_url($link);
	return $arr['scheme'].'://'.$arr['host'];
}

function rss_muk_ins_new_link($link){
	global $table_prefix, $wpdb;
	$link_table = $table_prefix."rss_muk_link";
	$res = $wpdb->get_results("SELECT COUNT(id) as cnt FROM ".$link_table." WHERE link='".$link."'");
			if($res[0]->cnt>0){
				echo '<div class="updated error"><p>link already exists!</p></div>';
			}else{
				rss_muk_ins_new_domain(rss_muk_get_domain_from_link($link));
				$ins_data=array(
					'link' => $link,
					'last_visited' => 0
				 );
				 if($wpdb->insert( $link_table, $ins_data)){
					 	echo '<div class="updated notice"><p>link has successfully been saved</p></div>';
				}else{
					echo '<div class="updated error"><p>error while saving the link</p></div>';
				}
			}
}
function rss_muk_ins_link_data($link_id,$title,$content){
	global $table_prefix, $wpdb;
	$link_data_table = $table_prefix."rss_muk_link_data";
	
	$ins_data=array(
			'link_id' => $link_id,
			'head'=>$title,
			'main_img'=>"",
			'data'=>$content,
			'last_visited' => time()
			);
	if($wpdb->insert( $link_data_table, $ins_data)){
		echo '<div class="updated notice"><p>link data has successfully been saved</p></div>';
	}else{
		echo '<div class="updated error"><p>error while saving the link data</p></div>';
	}
}
function rss_muk_ins_new_img_link($link,$link_id){
	global $table_prefix, $wpdb;
	$img_table = $table_prefix."rss_muk_img_data";
	$res = $wpdb->get_results("SELECT COUNT(id) as cnt FROM ".$img_table." WHERE img_url='".$link."'");
			if($res[0]->cnt>0){
				echo '<div class="updated error"><p>img link already exists!</p></div>';
			}else{
				rss_muk_ins_new_domain(rss_muk_get_domain_from_link($link));
				$ins_data=array(
					'link_id' => $link_id,
					'img_url' => $link,
					'height' => 0,
					'width'=>0
				 );
				 if($wpdb->insert( $img_table, $ins_data)){
					 	echo '<div class="updated notice"><p>img link has successfully been saved</p></div>';
				}else{
					echo '<div class="updated error"><p>error while saving the img link</p></div>';
				}
			}
}

function rss_muk_update_img_data(){//INCOMPLETE to be cronned
	echo "update image<br>";
	global $table_prefix, $wpdb;
	$img_table = $table_prefix."rss_muk_img_data";
	$res = $wpdb->get_results("SELECT id,link_id,img_url FROM ".$img_table." WHERE height=0 ORDER BY id ASC LIMIT 1");//h=0 w=0 
	if(COUNT($res)==1){
		
		echo "<img src='".$res[0]->img_url."'/>";
	   
	}
}

add_action( 'rss_muk_next_link_data_fetch_action', 'rss_muk_next_link_data_fetch' );
function rss_muk_next_link_data_fetch(){//to be cronned
	global $table_prefix, $wpdb;
	$link_table = $table_prefix."rss_muk_link";
	$res = $wpdb->get_results("SELECT id,link,last_visited FROM ".$link_table." WHERE last_visited=(SELECT MIN(last_visited) FROM ".$link_table.") ORDER BY RAND() LIMIT 1");//MIN(page_no) | ORDER BY id ASC LIMIT 1
	if(COUNT($res)==1){
		//*
		$lv=time();
		$curr_id=$res[0]->id;
		$wpdb->update($link_table, 
			array( 
				'last_visited' =>$lv
			), 
			array( 'id' =>$curr_id)
		);
		//*/
				$data_from_link=rss_muk_data_fetch_from_link($res[0]->link,$res[0]->id);

	}
}
add_action( 'rss_muk_next_g_src_action', 'rss_muk_next_g_src' );
function rss_muk_next_g_src(){//to be cronned
	global $table_prefix, $wpdb;
	$g_phrase_table = $table_prefix."rss_muk_g_phrase";
	
	$res = $wpdb->get_results("SELECT id,phrase,page_no FROM ".$g_phrase_table." WHERE page_no=(SELECT MIN(page_no) FROM ".$g_phrase_table.") ORDER BY RAND() LIMIT 1");//MIN(page_no) | ORDER BY id ASC LIMIT 1
	if(COUNT($res)==1){
		$link_from_g=rss_muk_retrieve_from_google($res[0]->phrase,($res[0]->page_no)+1);
		foreach($link_from_g as $ins_link){
			echo $ins_link;
			rss_muk_ins_new_link($ins_link);
		}
		
		$pg=(($res[0]->page_no)+1);
		$curr_id=$res[0]->id;
		$wpdb->update($g_phrase_table, 
			array( 
				'page_no' =>$pg
			), 
			array( 'id' =>$curr_id)
		);
	}
	

	/*
	*/
}
function rss_muk_next_g_src2($ins_phrase){
	$res=rss_muk_retrieve_from_google($ins_phrase,1);
	foreach($res as $ins_link){
		rss_muk_ins_new_link($ins_link);
	}
}
function rss_muk_ins_g_phrase($ins_phrase){
	global $table_prefix, $wpdb;
	$g_phrase_table = $table_prefix."rss_muk_g_phrase";
	$res = $wpdb->get_results("SELECT COUNT(id) as cnt FROM ".$g_phrase_table." WHERE phrase='".$ins_phrase."'");
	if($res[0]->cnt>0){
		echo '<div class="updated error"><p>phrase already exists!</p></div>';
	}else{
		$ins_data=array(
			'phrase' => $ins_phrase,
			'page_no' => 0
		 );
		if($wpdb->insert( $g_phrase_table, $ins_data)){
			echo '<div class="updated notice"><p>phrase has successfully been saved</p></div>';
			//next_g_src($ins_phrase);
		}else{
			echo '<div class="updated error"><p>error while saving the phrase</p></div>';
		}
	}
}

function rss_muk_ins(){
	global $table_prefix, $wpdb;
	$g_phrase_table = $table_prefix."rss_muk_g_phrase";
	$page="";
	$ins_phrase="";
	$ins_url="";
	echo "<div class='rss_muk_admin_container'>";
	echo "<div class='rss_muk_admin_item'>";
	 //rss_muk_update_img_data();
	//rss_muk_next_link_data_fetch();
	//rss_muk_next_g_src();
		if (isset( $_REQUEST['page'] )){
			$page=$_REQUEST['page'];
		}
		if (isset( $_REQUEST['rss_muk_phrase'] )) {
			if($_REQUEST['rss_muk_phrase']!="" && $_REQUEST['rss_muk_phrase']!=" "){
				$ins_phrase=$_REQUEST['rss_muk_phrase'];
				echo '<div class="updated notice"><p>you have submitted "'.$ins_phrase.'"</p></div>';
				
				rss_muk_ins_g_phrase($ins_phrase);
				
			}else{
				echo '<div class="updated error"><p>please check the input again!</p></div>';
			}
			
		}
		if (isset( $_REQUEST['rss_muk_url'] )) {
			if($_REQUEST['rss_muk_url']!="" && $_REQUEST['rss_muk_url']!=" "){
				$ins_url=$_REQUEST['rss_muk_url'];
				echo '<div class="updated notice"><p>you have submitted "'.$ins_url.'"</p></div>';
				rss_muk_ins_new_link($ins_url);
			}else{
				echo '<div class="updated error"><p>please check the input again!</p></div>';
			}
			
		}
		
	echo "</div>";
	
	echo "<div class='rss_muk_admin_item'>";
	echo '<center>
		  <div>
		<form class="search-form">
			<label>
			<span class="screen-reader-text">add new phrase: </span>
			<input type="hidden" name="page" value="'.$page.'">
			<input type="text" class="search-field" placeholder="enter phrase" value="'.$ins_phrase.'" name="rss_muk_phrase">
			</label>
			<button type="submit" class="button">Add</button>
		</form>

		</div>
		<div>
		<form class="search-form">
			<label>
			<span class="screen-reader-text">add new link: </span>
			<input type="hidden" name="page" value="'.$page.'">
			<input type="text" class="search-field" placeholder="enter new link" value="'.$ins_url.'" name="rss_muk_url">
			</label>
			<button type="submit" class="button">Add</button>
		</form>

		</div></center>';	
		
	echo "</div>";	
	echo "</div>";
}

function rss_muk_add_admin_page(){

add_menu_page('Recent Bids', 'Rss Muk', 'manage_options','rss_muk_adm','rss_muk_view_all','dashicons-building', 56);   

add_submenu_page(
    'rss_muk_adm',       // parent slug
    'View All',    // page title
    'View All',             // menu title
    'manage_options',           // capability
    'rss_muk_adm', // slug
    'rss_muk_view_all' // callback
); 


add_submenu_page(
    'rss_muk_adm',       // parent slug
    'insert link/phrase',    // page title
    'insert link/phrase',             // menu title
    'manage_options',           // capability
    'rss_muk_insert', // slug
    'rss_muk_ins' // callback
); 

add_submenu_page(
    'rss_muk_adm',       // parent slug
    'Customer Bids',    // page title
    'Customer Bids',             // menu title
    'manage_options',           // capability
    'wc-acutions-customers-bids', // slug
    'acutions_customers_bids_list' // callback
);  

}

