<?php

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

function aa_dynamic_menu(){ 
	//global $wpdb;
	
	add_menu_page('Dynamic Menus', 'Dynamic Menus', 'manage_options', 'tag-dynamic-menus-landing-page', 'aa_admin_menu_data', plugins_url( 'my_dynamic_menu/includes/images/menu-icon-24x24.png' ), 4.6); 

	add_submenu_page( 'tag-dynamic-menus-landing-page', 'Settings', 'Settings', 'manage_options', 'tag-dynamic-menus-settings-page', 'aa_admin_menu_settings_data');	

	add_submenu_page( 'tag-dynamic-menus-landing-page', 'Notes', 'Notes', 'manage_options', 'tag-dynamic-menus-notes-page', 'aa_admin_menu_notes_data');	
}	