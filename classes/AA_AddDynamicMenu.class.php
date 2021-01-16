<?php


/*********************************************************************************
	1. Setup default plugin settings.
	2. Seed the dynamic menu if there are no entries.  Add additional dynamic menu if called by admin.
*********************************************************************************/

class AA_AddDynamicMenu {

	/*********************************************************************************
		Seed the dynamic menu if there are no entries during activation ($user_created = 0).  
		Add additional dynamic menu if called by admin ($user_created = 1).
	*********************************************************************************/
	static function add_menu( $dynamic_menu_exists, $user_created ) {
		global $wpdb, $current_datetime, $current_date, $dynamic_submenu_count;

			// Seed the dynamic menu with a notional WP menu called 'top-nav-menu'.  Plugin needs at least one WP menu to associate with.
			$menu_slug		= serialize( array( 'top-nav-menu', 'randy' ) );

			$menu_options	= serialize( array( 'date'=>'1', 'is_logged_in'=>'0', 'logged_in_user'=>'', 'isnot_logged_in'=>'0', 'not_logged_in_user'=>'', 'is_front_page'=>'0', 'isnot_front_page'=>'0', 'is_home_index'=>'0', 'isnot_home_index'=>'0', 'url_post_control'=>'0', 'url_post_name'=>'', 'url_post_value'=>'') );		
			$post_parent	= 0;		
			$date_offset2		= 	date_create(date('Y-m-d'));
									date_modify($date_offset2, '+7 day');
			$datetime_offset2 	= 	date_format($date_offset2, 'Y-m-d H:i:s');

			$top_level_title = 'Top Level';
			if( $user_created == 1 ) { // admin creating menu
				foreach ( $dynamic_menu_exists as $dynamic_menu ) {
					$post_title		= esc_attr( $dynamic_menu->post_title );
					if( $post_title == $top_level_title  ) $top_level_title = 'New Top Level ' . rand(100,999);
				}
				$menu_title_array = array( $top_level_title );
				for( $x=1; $x<=$dynamic_submenu_count; $x++ ) {
					array_push($menu_title_array,"Child$x");
				}

				$menu_order_array = array( '3' );
				for( $x=1; $x<=$dynamic_submenu_count; $x++ ) {
					array_push($menu_order_array,"$x");
				}
				$menu_meta_url_array = array( '' );
				for( $x=1; $x<=$dynamic_submenu_count; $x++ ) {
					array_push($menu_meta_url_array,'');
				}
				$array_count = $dynamic_submenu_count + 1;
			}
			else { // activation creating menu
				$menu_title_array = array( 
					$top_level_title,
					"Child I",
					"Child II",
					"Child III",
					"Child IV",
					"Child V"
				);
				
				$array_count = count( $menu_title_array );
				if( $user_created == 1 ) $array_count = $dynamic_submenu_count + 1;
				
				$menu_order_array = array( 
					"3",
					"1",
					"2",
					"3",
					"4",
					"5"
				);
				
				$menu_meta_url_array = array(  
					"http://www.abidin.com",
					"http://www.abidin.com/test/",
					"http://www.moorestown.com",
					"http://www.moorestownglax.com",
					"http://www.abidin.com",
					"http://www.abidin.com"
				);

			}
			
			
			
			$menu_num		= 9876;
			
			//do any dynamic menus exist? 
			$do_menus_exist = $wpdb->get_results( 
				"
				SELECT 	meta_value
				FROM " . TAG_POST_META_TABLE . " 
				WHERE ( meta_key ='dynamic_menu_num' )
				"
			);
			if( $wpdb->num_rows > 0 ) {
				$get_menu_num = $wpdb->get_results( 
					"
					SELECT 	MAX( meta_value ) AS max_menu_num
					FROM " . TAG_POST_META_TABLE . " 
					WHERE ( meta_key ='dynamic_menu_num' AND meta_value > '1' )
					"
				);
				foreach ( $get_menu_num as $menu_number ) {
					$menu_num = esc_attr( $menu_number->max_menu_num ) + 1;
				}
			}
			
			// get the wp menu name 
			$wp_menu_name = 'TAG - Dynamic Menu';
			
							
			$post_dates_serialized = serialize( array( $current_date, $current_date ));
			
			for( $x=0; $x<$array_count; $x++ ){					
				$rows_affected = $wpdb->insert( TAG_POSTS_TABLE, array( 
					'post_author' 			=> 32,
					'post_title' 			=> $menu_title_array[$x], 
					'post_name'				=> $wp_menu_name, 
					'post_parent'			=> $post_parent,  
					'menu_order'			=> $menu_order_array[$x], 
					'guid'		 			=> $menu_meta_url_array[$x],
					'post_type' 			=> 'nav_menu_item_dynam',
					'post_status' 			=> 'publish',				
					'post_content'			=> $post_dates_serialized, 		
					'post_content_filtered'	=> $menu_options, 
					'post_date'				=> $current_datetime, 
					'post_modified'			=> $current_datetime		
					) 
				);
				$post_id = $wpdb->insert_id;
				if( $x == 0 ) {
					$post_parent = $post_id; // the first menu item is the parent

					$rows_affected = $wpdb->insert( TAG_POST_META_TABLE, array( 
						'post_ID'	 	=> $post_id,
						'meta_key' 		=> 'dynamic_menu_slug', 
						'meta_value'	=> $menu_slug
						) 
					);
					
					$rows_affected = $wpdb->insert( TAG_POST_META_TABLE, array( 
						'post_ID'	 	=> $post_id,
						'meta_key' 		=> 'dynamic_menu_num', 
						'meta_value'	=> $menu_num
						) 
					);
				}
			}
		return $post_parent;
	}


}

class AA_ActivationSeedDMenu {

	/*********************************************************************************
		During Activation seed default plugin setting: Submenu count - value, title, description
	*********************************************************************************/
	static function activation_setup_menu_settings() {
		global $wpdb;
	 
		$dynamic_menu_settings_exists = $wpdb->get_results( 
			"
			SELECT 	option_id, option_name, option_value
			FROM " . TAG_OPTIONS_TABLE . " 
			WHERE option_name = 'nav_menu_item_dynam'
			"
		);
		// proceed if there are no settings created yet (at activation).
		if( $wpdb->num_rows == 0 ) {

			$value_array = array( 'submenu_count'		=>'5');
									
			$title_array = array( 'submenu_count_title'	=> 'SubMenu Count:');
									
			$descr_array = array( 'submenu_count_descr'	=> "When a new dynamic menu is created, there are submenus added to that menu.  
															This is the count of the submenus.
															<br><br><b>Note:</b> The number of submenus is limited by the Input variables setting in your server`s php.ini file.  To increase the limit, change max_input_vars in php.ini.
															<p>In the test environment for this plugin (PHP v7.4), there was a limit of 80 submenus due to max_input_vars in php.ini having a default value of 1000.</p>");
			
			$settings_array 		= array( $value_array, $title_array, $descr_array );
			$settings_serialized 	= serialize( $settings_array);
			
			$rows_affected = $wpdb->insert( TAG_OPTIONS_TABLE, array( 
				'option_name' 	=> 'nav_menu_item_dynam', 
				'option_value'	=> $settings_serialized
				) 
			);
		}
	}


	/*********************************************************************************
		During Activation seed the dynamic menu if there are no existing entries. 
	*********************************************************************************/
	static function activation_load_menu_data() {
		global $wpdb, $current_datetime, $dynamic_submenu_count;
	 
		$dynamic_menu_exists = $wpdb->get_results( 
			"
			SELECT 	id, post_title
			FROM " . TAG_POSTS_TABLE . " 
			WHERE POST_TYPE = 'nav_menu_item_dynam'
			"
		);	
		// proceed if there are no dynamic menus created yet  - at activation.
		if( $wpdb->num_rows == 0 ) {		
			return AA_AddDynamicMenu::add_menu( $dynamic_menu_exists, 0 );
		}
	}
}