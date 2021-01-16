<?php
/*
Plugin Name: My Dynamic Menus
Plugin URI:  https://github.com/rabidin/dynamic-menus
Description: Adds dynamic menu items to WordPress Menus Programmatically.  Menu items are visible/not visible based on date, is_logged_in, is_home_index, is_front_page or URL or POST parameter.  These are assigned to each menu item. Based on original work by Jonathan Daggerhart.
Version:     0.9.5
Author:      Randy Abidin, Abidin's Apps.
Author URI:  http://abidinsapps.com
License:     GPL2
*/

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

global $wpdb, $current_datetime, $current_date;

// SETUP CURRENT DATE/TIME
	date_default_timezone_set('America/New_York');
	$current_datetime1	= date_create(date('Y-m-d  H:i:s'));
	$current_datetime 	= date_format($current_datetime1, 'Y-m-d H:i:s');
	$current_date	 	= date_format($current_datetime1, 'Y-m-d');


// LOAD jQuery DATEPICKER
	function aa_enqueue_datepicker() {
		// Load the datepicker script (pre-registered in WordPress).
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// Styling the datepicker.
		wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'jquery-ui' );  
	}
	//enable datepicker in both front-end and back-end.
	add_action( 'wp_enqueue_scripts',		'aa_enqueue_datepicker' );
	add_action( 'admin_enqueue_scripts',	'aa_enqueue_datepicker' );
	
	
//	INCLUDE FUNCTION FILES
	require_once 'classes/CustomMenuItems.class.php';
	require_once 'classes/AA_AddDynamicMenu.class.php';
	require_once 'classes/AADynamicMenu.class.php';
	add_action('admin_init', 'AADynamicMenu::determine_dynamic_menu_settings');
	add_action('admin_init', 'AADynamicMenu::cleanup_wp_menu_list');

	//require_once 'includes/functions.php';
	require_once 'public/build-dynamic-menu.php';
	require_once 'admin/admin-menus.php';
	require_once 'admin/admin-menus-settings.php';
	require_once 'admin/admin-menus-notes.php';
	require_once 'admin/admin-plugin-menu.php';
	add_action('admin_menu', 'aa_dynamic_menu');
	require_once 'utilities/utilities-misc.php';

//	DEFINITIONS
	define( 'TAG_POSTS_TABLE',		$wpdb->prefix . 'posts');
	define( 'TAG_POST_META_TABLE',	$wpdb->prefix . 'postmeta');
	define( 'TAG_OPTIONS_TABLE',	$wpdb->prefix . 'options');
	define( 'TAG_TERMS_TABLE',		$wpdb->prefix . 'terms');
	define( 'TAG_TERM_TAX_TABLE',	$wpdb->prefix . 'term_taxonomy');

//	ACTIVATION INITIALIZATIONS
	//include('activate/activate-seed-menu.php');
	register_activation_hook( __FILE__, 'AA_ActivationSeedDMenu::activation_load_menu_data' );
	register_activation_hook( __FILE__, 'AA_ActivationSeedDMenu::activation_setup_menu_settings' );

//	ACTIVATION ERROR COLLECTION
	add_action('activated_plugin','tag_interation_activation_error');
	function tag_interation_activation_error()	{
		file_put_contents( dirname(__file__).'/error_activation.txt', ob_get_contents() );
	}
