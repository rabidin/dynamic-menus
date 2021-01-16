<?php 
/*********************************************************************************
		Settings for the dynamic menus plugin.
*********************************************************************************/

function aa_admin_menu_settings_data() {
	global $wpdb, $current_datetime;
	//////////////////////////////, $dynamic_submenu_countxxxx;

	if( isset( $_REQUEST['update_submenu_num'] )) {

		AADynamicMenu::check_nonce();	
		if( !is_numeric( $_REQUEST['num_submenus'] ) || $_REQUEST['num_submenus'] < 0 ) $_REQUEST['num_submenus'] = 0;
		$_REQUEST['num_submenus'] = intval( $_REQUEST['num_submenus']);
		
		//get the serialized settings array in the options table 
		$dynamic_menu_settings = $wpdb->get_results( 
			"
			SELECT 	option_value
			FROM " . TAG_OPTIONS_TABLE . " 
			WHERE option_name = 'nav_menu_item_dynam'
			"
		);
		foreach ( $dynamic_menu_settings as $menu_settings ) {
			$settings_array	= unserialize( $menu_settings->option_value );
		}
		$settings_array[0]['submenu_count']	= $_REQUEST['num_submenus']; //update the submenu number
		$settings_serialized				= serialize( $settings_array);
		
		$update_menus = $wpdb->get_results( 
			"
			UPDATE " . TAG_OPTIONS_TABLE . " SET	
			option_value	 	= '" . $settings_serialized . "'
			WHERE ( option_name = 'nav_menu_item_dynam' )
			"
		);
		echo AADynamicMenu::action_message( "Sub Menu count changed to " . $_REQUEST['num_submenus'] . "</strong>");	
	}



/*********************************************************************************
	drop to settings update screen
*********************************************************************************/
	$nonce = wp_create_nonce( 'tag-nonce' );

	AADynamicMenu::determine_dynamic_menu_settings();
	
	$dynamic_menu_settings = $wpdb->get_results( 
		"
		SELECT 	option_value
		FROM " . TAG_OPTIONS_TABLE . " 
		WHERE option_name = 'nav_menu_item_dynam'
		"
	);	
	if( $wpdb->num_rows > 0 ) { 
		foreach ( $dynamic_menu_settings as $menu_settings ) {
			$settings_array	= unserialize( $menu_settings->option_value );
			
			echo "<h3 align='center'>Dynamic Menu Settings <br><br></h3>
			<table border='1' cellpadding='11' cellspacing='0'>
			<form action='' method='POST'>
			<tr><td>" . $settings_array[1]['submenu_count_title'] . "
			<br><input name='num_submenus' value='" . $settings_array[0]['submenu_count'] . "' size='2'></td>\n 
			<td><input type='submit' name='update_submenu_num' value='Update Submenu Count'></td><td>"
			. $settings_array[2]['submenu_count_descr'] . "</td></tr>
			<input type='hidden' name='tag_nonce' value='$nonce'> 
			<form></table>";
		}
	}
}
