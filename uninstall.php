<?php 
/*
	Delete all plugin data
*/

// exit if uninstall constant is not defined
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

global $wpdb;
//	DEFINITIONS
	define( 'TAG_POSTS_TABLE',		$wpdb->prefix . 'posts');
	define( 'TAG_POST_META_TABLE',	$wpdb->prefix . 'postmeta');

//	delete plugin options
	delete_option('nav_menu_item_dynam');

//	delete dynamic menus in posts table
	$delete_dyn_menu_posts = $wpdb->get_results( 
		"DELETE FROM " . TAG_POSTS_TABLE . " 
		 WHERE post_type = 'nav_menu_item_dynam' "
	);
	
//	delete post meta	
	$delete_dyn_menu_posts = $wpdb->get_results( 
		"DELETE FROM " . TAG_POST_META_TABLE . " 
		 WHERE meta_key = 'dynamic_menu_num' or meta_key = 'dynamic_menu_slug'"
	);
	