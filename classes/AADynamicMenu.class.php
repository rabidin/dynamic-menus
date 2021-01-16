<?php 

class AADynamicMenu {

	//  verify the nonce is correct
	static function check_nonce() {	
		$nonce = $_REQUEST['tag_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'tag-nonce' ) ) {
			// This nonce is not valid.
			die( __( '<p> &nbsp; </p>We have a Security issue . . .', 'textdomain' ) ); 
		} 
	}


	// tag_delete_full_menu() deletes the menu and all of its submenus
	static function delete_full_menu( $post_delete_id ) {
		global $wpdb;
		
		// find & delete all submenu posts id's 
		$get_submenus = $wpdb->get_results( 
			"
			SELECT 	id
			FROM " . TAG_POSTS_TABLE . "
			WHERE ( post_parent = '" . $post_delete_id . "')
			"
		);	
		foreach ( $get_submenus as $submenus ) {
			$delete_submenu_id	= esc_attr( $submenus->id );
			$delete_postmeta = $wpdb->get_results( 
				"
				DELETE FROM " . TAG_POST_META_TABLE . " 
				WHERE post_id = '" . $delete_submenu_id . "' 
				"
			);	
		}
		// delete top level menu
		$delete_menu_n_all_subs = $wpdb->get_results( 
			"
			DELETE FROM " . TAG_POSTS_TABLE . " 
			WHERE id = '" . $post_delete_id . "' OR post_parent = '" . $post_delete_id . "' 
			"
		);
		// delete top level menu meta data					
		$delete_postmeta = $wpdb->get_results( 
			"
			DELETE FROM " . TAG_POST_META_TABLE . " 
			WHERE post_id = '" . $post_delete_id . "' 
			"
		);			
	}


	// tag_action_message() styles a message to the admin after an action is completed. 
	static function action_message( $message ) {
		return "<div align='center'><span style='line-height:5.0em;font-size:1.5em;font-weight:bold;color:maroon'>$message</span></div>";
	}


	/*********************************************************************************
		tag_menu_control_function() presents the ratio buttons for the admin to select when the menus are displayed.
	*********************************************************************************/
	static function menu_control_function( $post_id, $x ) {
		global $wpdb;
		
		$get_dynamic_menus = $wpdb->get_results( 
			"
			SELECT 	id, post_content_filtered
			FROM " . TAG_POSTS_TABLE . " 
			WHERE id = '" . $post_id . "'
			ORDER by menu_order
			"
		);	
		foreach ( $get_dynamic_menus as $dynamic_menu ) {
			$post_control_array	= unserialize( $dynamic_menu->post_content_filtered);
			$url_post_name	= $post_control_array['url_post_name'];
			$url_post_value	= $post_control_array['url_post_value'];
			$logged_in_user	= $post_control_array['logged_in_user'];		
		}
		$date_checked				= '';
		$is_logged_in_checked		= '';
		$isnot_logged_in_checked	= '';
		$is_front_page_checked		= '';
		$isnot_front_page_checked	= '';
		$is_home_index_checked		= '';
		$isnot_home_index_checked	= '';
		$url_post_control_checked	= '';
		
		if( $post_id == 0 ) {	// new menu item is added ( $post_id == 0 )
			$date_checked	= 'CHECKED'; 
			$url_post_name	= '';
			$url_post_value	= '';
			$logged_in_user	= '';
		}

		if( isset($post_control_array['date']) && $post_control_array['date'] == 1) 						$date_checked				= 'CHECKED';
		if( isset($post_control_array['is_logged_in']) 		&& $post_control_array['is_logged_in'] == 1) 	$is_logged_in_checked		= 'CHECKED';
		if( isset($post_control_array['isnot_logged_in']) 	&& $post_control_array['isnot_logged_in'] == 1) $isnot_logged_in_checked	= 'CHECKED';
		if( isset($post_control_array['is_front_page']) 	&& $post_control_array['is_front_page'] == 1) 	$is_front_page_checked		= 'CHECKED';
		if( isset($post_control_array['isnot_front_page'])	&& $post_control_array['isnot_front_page'] == 1)$isnot_front_page_checked	= 'CHECKED';
		if( isset($post_control_array['is_home_index']) 	&& $post_control_array['is_home_index'] == 1) 	$is_home_index_checked		= 'CHECKED';
		if( isset($post_control_array['isnot_home_index']) 	&& $post_control_array['isnot_home_index'] == 1)$isnot_home_index_checked	= 'CHECKED';
		if( isset($post_control_array['url_post_control']) 	&& $post_control_array['url_post_control'] == 1)$url_post_control_checked	= 'CHECKED';

		$control_buffer = "\n\n<table class='rla_table' ><tr>\n";
		$control_buffer .= "<td>&nbsp; <input type='radio' name='menu_control_$x' value='date'			$date_checked>Date</td>\n";

		$control_buffer .= "<td>&nbsp; <input type='radio' name='menu_control_$x' value='is_logged_in'	$is_logged_in_checked>User Logged In\n 
							&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input size='10' name='logged_in_user_$x' value='$logged_in_user'> *4 \n
							<br>&nbsp; <input type='radio' name='menu_control_$x' value='isnot_logged_in'	$isnot_logged_in_checked>User NOT Logged In";

		$control_buffer .= "<td>&nbsp; <input type='radio' name='menu_control_$x' value='is_front_page'	$is_front_page_checked>Is Front Page
							<br>&nbsp; <input type='radio' name='menu_control_$x' value='isnot_front_page'	$isnot_front_page_checked>Is NOT Front Page</td>\n";
		$control_buffer .= "<td>&nbsp; <input type='radio' name='menu_control_$x' value='is_home_index'	$is_home_index_checked>Is Home (Blog) *5
							<br>&nbsp; <input type='radio' name='menu_control_$x' value='isnot_home_index'	$isnot_home_index_checked>Is NOT Home (Blog) </td>\n";
		$control_buffer .= "<td>&nbsp; <input type='radio' name='menu_control_$x' value='url_post_control' $url_post_control_checked>URL or Post Value \n
							<br>&nbsp; Name:<input size='14' name='url_post_name_$x' value='$url_post_name'> &nbsp; Value:<input name='url_post_value_$x' value='$url_post_value' size='4'></td>\n";	
		
		$control_buffer .= "</tr></table>\n\n";
		
		return $control_buffer;
	}



	/*****************************************************************************************
		determine_dynamic_menu_settings() - ?????
	******************************************************************************************/

	static function determine_dynamic_menu_settings(){
		global $wpdb, $dynamic_submenu_count;

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
				$dynamic_submenu_count = $settings_array[0]['submenu_count'] ;
			}
		}
	}


		

	/*********************************************************************************
		tag_cleanup_wp_menu_list() - Cleanup the list of WP menus in the postmeta->meta_key->dynamic_menu_slug 
		so when WP Menus are deleted, they are removed from the dynamic menu slug
	*********************************************************************************/

	// add_action('admin_init', 'tag_cleanup_wp_menu_list');
	static function cleanup_wp_menu_list() {
		global $wpdb;

		$get_wp_menu_names = $wpdb->get_results( 
			"
			SELECT 	tt.term_id, tt.name, tt.slug, tx.taxonomy
			FROM " . TAG_TERMS_TABLE . " as tt 
			LEFT JOIN " . TAG_TERM_TAX_TABLE . " as tx on tt.term_id = tx.term_id
			WHERE ( tx.taxonomy = 'nav_menu' )
			ORDER BY 	tt.name "
		);

		$wp_menu_name			= array();
		$wp_menu_slug			= array();
		$new_menu_slug_array	= array();

		$x = 0;
		foreach ( $get_wp_menu_names as $menu_names ) {
			$wp_menu_name[$x]	= esc_attr( $menu_names->name );
			$wp_menu_slug[$x]	= esc_attr( $menu_names->slug );
			$x++;
		}
		$wp_menu_name_count = count( $wp_menu_name );
		
		$get_top_level_menus = $wpdb->get_results( 
			"
			SELECT 	id
			FROM " . TAG_POSTS_TABLE . " 
			WHERE post_parent = '0'	AND post_type = 'nav_menu_item_dynam'	
			"
		);	
	 
		foreach ( $get_top_level_menus as $top_menu ) {
			$post_id	= $top_menu->id;
			
			$get_dynamic_meta = $wpdb->get_results( 
				"
				SELECT 	meta_id, post_id, meta_value
				FROM " . TAG_POST_META_TABLE . " 
				WHERE meta_key = 'dynamic_menu_slug' AND post_id = '" . $post_id . "'		
				"
			);	
			$menu_name_select 		= '';	
			unset($new_menu_slug_array); 
			$new_menu_slug_array	= array();	
			$associated_wp_menus	= array();

			if( $wpdb->num_rows > 0 ) {  // no cleanup if no dynamic menus found
				foreach ( $get_dynamic_meta as $dynamic_meta ) {	
					$meta_id					= esc_attr( $dynamic_meta->meta_id );				

					$dynamic_menu_slug_array	= unserialize( $dynamic_meta->meta_value );
					if( !is_array( $dynamic_menu_slug_array ) ) break; // if no menus have been set up				
					$dynamic_slug_count			= count( $dynamic_menu_slug_array );

					$x = 0;
					$last_wp_menu = '';
					for( $y=0; $y<$dynamic_slug_count; $y++ ) {	
						for( $z=0; $z<$wp_menu_name_count; $z++ ) {
							if( $last_wp_menu != $dynamic_menu_slug_array[$y] ) {
								if( $wp_menu_slug[$z] == $dynamic_menu_slug_array[$y] ) {
									$new_menu_slug_array[$x] = $wp_menu_slug[$z];
									$x++;
								}
							}
						}
						$last_wp_menu = $dynamic_menu_slug_array[$y];					
					} 
					$menu_slug_serialized = serialize( $new_menu_slug_array );
					
					//update menu meta data with new WP Menu array 
					$update_menus = $wpdb->get_results( 
						"
						UPDATE " . TAG_POST_META_TABLE . " SET	
							meta_value	 	='" . $menu_slug_serialized . "' 
							WHERE meta_id = '" . $meta_id . "'
						"
					); 			
				}
			}
		}	
	}


	/******************************************************************
			tag_menu_order_resort() - re-sort the submenus (not the top level)
	*******************************************************************/
	static function menu_order_resort() {
		
		for( $x=1; $x<=$_REQUEST['num_posts']; $x++ ) {
			if( !is_numeric( $_REQUEST["menu_order_$x"])) $_REQUEST["menu_order_$x"] = 99;
					
			if( $_REQUEST["menu_order_$x"] > $_REQUEST["menu_order_orig_$x"]) {
				$_REQUEST["menu_order_$x"] = $_REQUEST["menu_order_$x"] + 0.5;
			}
			elseif( $_REQUEST["menu_order_$x"] < $_REQUEST["menu_order_orig_$x"]) {
					$_REQUEST["menu_order_$x"] = $_REQUEST["menu_order_$x"] - 0.5;
			}
			$_REQUEST["menu_order_$x"] = $_REQUEST["menu_order_$x"] * 10;
		}
		return;
	}
		



	/******************************************************************
		tag_select_wp_menu_names( $post_id ) - Create the html select tags for each WP site menu.
		The user can then select and associate a dynamic menu with any of the WP site menus.
	*******************************************************************/
	static function select_wp_menu_names( $post_id ){
		global $wpdb;
		
		$get_wp_menu_names = $wpdb->get_results( 
			"
			SELECT 	tt.term_id, tt.name, tt.slug, tx.taxonomy
			FROM " . TAG_TERMS_TABLE . " as tt 
			LEFT JOIN " . TAG_TERM_TAX_TABLE . " as tx on tt.term_id = tx.term_id
			WHERE ( tx.taxonomy = 'nav_menu' )
			ORDER BY 	tt.name "
		);

		$wp_menu_name	= array();
		$wp_menu_slug	= array();
		$x = 0;
		foreach ( $get_wp_menu_names as $menu_names ) {
			$wp_menu_name[$x]	= esc_attr( $menu_names->name );
			$wp_menu_slug[$x]	= esc_attr( $menu_names->slug );
			$x++;
		}
		$wp_menu_name_count = count( $wp_menu_name );
		
		$name_selected	= '';
		
		// find the current menus that this menu is attached to 
		$get_dynamic_meta = $wpdb->get_results( 
			"
			SELECT 	meta_value
			FROM " . TAG_POST_META_TABLE . " 
			WHERE post_id = '" . $post_id . "' AND meta_key = 'dynamic_menu_slug'
			"
		);	
		$menu_name_select 		= '';
		$associated_wp_menus	= array();
		foreach ( $get_dynamic_meta as $dynamic_meta ) {	
			$menu_slug					= $dynamic_meta->meta_value;
			$dynamic_menu_slug_array	= unserialize( $menu_slug );			
			$dynamic_slug_count			= count( $dynamic_menu_slug_array );
			
			$select_spacer = '';
			for( $y=0; $y<$dynamic_slug_count; $y++ ) {			
				$menu_name_select	.= "$select_spacer<select name='select_wp_menus_$y'><option value=''>Select Menu Name</option>\n";		
				$select_spacer = ' - ';
				for( $x=0; $x<$wp_menu_name_count; $x++ ) {			
					$name_selected = '';	
					if( $wp_menu_slug[$x] == $dynamic_menu_slug_array[$y] ) {
						$name_selected = 'SELECTED';								
						array_push( $associated_wp_menus, $dynamic_menu_slug_array[$y] );
					}
					$menu_name_select .= "<option value='$wp_menu_slug[$x]'	$name_selected >$wp_menu_name[$x]</option>\n";
				}
				$menu_name_select .="<option value=''>Remove this Menu</option>\n</select>\n";
			}
			$assigned_wp_menus_count = count( $associated_wp_menus );
			if( $y < $wp_menu_name_count ) {
				$menu_name_select .= "$select_spacer<select name='select_wp_menus_$dynamic_slug_count'><option value=''>Select Menu Name</option>\n";	
				for( $x=0; $x<$wp_menu_name_count; $x++ ) {
					$menu_association_found = 0;
					for($z=0; $z<$assigned_wp_menus_count; $z++ ) {
						if( $wp_menu_slug[$x] == $associated_wp_menus[$z] ) $menu_association_found = 1;
					}
					$name_selected = '';	
					if( $menu_association_found == 0 ) $menu_name_select .= "<option value='$wp_menu_slug[$x]' >$wp_menu_name[$x]</option>\n";
				}
				$menu_name_select .="</select>\n";
			}
		}
		return array( $menu_name_select, $dynamic_slug_count, $wp_menu_name_count );
	}


	/****************************************************************************************************
		tag_dynamic_menu_on_off_control() determines whether to display or not display each menu on the frontend.
	*****************************************************************************************************/
	static function dynamic_menu_on_off_control( $post_date_array, $post_control_array, $post_title ) {
		global $wpdb, $current_user;

			/********** Is between start & End Dates **********/
			if( $post_control_array['date'] == 1 ) {
			$datetime1	= new DateTime($post_date_array[0]);
			$datetime2	= date_create(date('Y-m-d'));			
			$interval	= $datetime2->diff($datetime1);
			$diff 		= $interval->format('%R%a days');			
			$stringDate = $datetime1->format('Y-m-d');
			
			if( $diff <= 0 ) {
				$datetime1	= new DateTime($post_date_array[1]);
				$datetime2	= date_create(date('Y-m-d'));
				$interval	= $datetime2->diff($datetime1);
				$diff 		= $interval->format('%R%a days');			
				$stringDate = $datetime1->format('Y-m-d');
				
				if( $diff >= 0 ) {
					return 1;
				}
				else {
					return 0;
				}				
			}
			else {
				return 0;
			}
		}
		
		/********** Is Logged In **********/
		elseif( $post_control_array['is_logged_in'] == 1 ) {
			if ( is_user_logged_in() ) {
				//echo "<h4>Logged in: user=" . $current_user->user_login . "</h4>";
				if( !empty($post_control_array['logged_in_user'])  ) {
					if( $post_control_array['logged_in_user'] == $current_user->user_login ) {
						return 1;
					}
					else {
						return 0;
					}
				}
				return 1;
			}
			else {
				return 0;
			}	
		}
		
		/********** IsNOT Logged In **********/
		elseif( $post_control_array['isnot_logged_in'] == 1 ) {
			if ( is_user_logged_in() ) {
				if( !empty($post_control_array['logged_in_user'])  ) {
					if( $post_control_array['logged_in_user'] == $current_user->user_login ) {
						return 0;
					}
					else {
						return 1;
					}
				}
				return 0;
			}
			return 1;	
		}
		
		/********** Is Front Page **********/
		elseif( $post_control_array['is_front_page'] == 1 ) {
			if ( is_front_page() ) {
				return 1;
			}
			return 0;
		}
		
		/********** IsNOT Front Page **********/
		elseif( $post_control_array['isnot_front_page'] == 1 ) {
			if ( is_front_page() ) {
				return 0;
			}
			return 1;
		}
		
		/********** Is Home Index **********/
		elseif( $post_control_array['is_home_index'] == 1 ) {
			if ( is_home() ) {
				return 1;
			}
			return 0;
		}

		/********** IsNOT Home Index **********/
		elseif( $post_control_array['isnot_home_index'] == 1 ) {
			if ( is_home() ) {
				return 0;
			}
			return 1;
		}
			
		/********** URL or POST Control **********/
		elseif( $post_control_array['url_post_control'] == 1 ) {
			$test = count($_REQUEST);
			if( empty( $post_control_array['url_post_name'] ) && $test > 0  ) {
				return 1;
			}
			else {
				foreach ($_REQUEST as $key => $value) {
					if( $post_control_array['url_post_name'] == $key ) {
						if( $post_control_array['url_post_value'] == $value || empty($post_control_array['url_post_value']) ) {
							return 1;
						}
					}
				}
			}
			return 0;
		}
	}
}	 