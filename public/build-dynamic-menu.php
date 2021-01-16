<?php
defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

/******************************************************************
		Use dynamic menu entries in posts & postmeta tables
*******************************************************************/

add_action( 'wp', function(){
	
	global $wpdb, $current_datetime, $current_date, $current_user;

   // Single item
    /**
     * @param $menu_slug
     * @param $title
     * @param $url
     * @param $order
     * @param $parent
     * @param null $ID
     */

	 /******* get post parents ********/
	 $get_post_parents = $wpdb->get_results( 
		"
		SELECT 	id, post_title, post_date, guid, menu_order, post_parent
		FROM " . TAG_POSTS_TABLE . " 
		WHERE post_type = 'nav_menu_item_dynam' AND post_status = 'publish' AND post_parent = '0'
		ORDER BY id
		"
	);	
	$post_parent_0 = array();
	if( $wpdb->num_rows > 0 ) {
		$menu_slug		= '';
		$top_menu_off	= -1;
		foreach ( $get_post_parents as $dynamic_menu_exist ) {
			$post_id	= esc_attr( $dynamic_menu_exist->id );
			$post_title	= esc_attr( $dynamic_menu_exist->post_title );
			$post_date	= esc_attr( $dynamic_menu_exist->post_date );
		}
	}
	
	
	/******* get ALL posts ********/
	 $get_all_posts = $wpdb->get_results( 
		"
		SELECT 	id, post_title, post_content, post_content_filtered, guid, menu_order, post_parent
		FROM " . TAG_POSTS_TABLE . " 
		WHERE post_type = 'nav_menu_item_dynam' AND post_status = 'publish'
		ORDER BY id
		"
	);	
	
	$menu_num = array();	
	if( $wpdb->num_rows > 0 ) {
		$menu_slug		= '';
		$top_menu_off	= -1;
		$x=0;
		foreach ( $get_all_posts as $all_posts ) {
			$post_id			= esc_attr( $all_posts->id );
			$post_title			= esc_attr( $all_posts->post_title );
			$post_parent		= esc_attr( $all_posts->post_parent );
			$post_url			= esc_attr( $all_posts->guid );
			$menu_order			= esc_attr( $all_posts->menu_order );
			$post_date_array	= unserialize( $all_posts->post_content);
			$post_control_array	= unserialize( $all_posts->post_content_filtered);

			// get the meta slugs and the menu numbers from postmeta table
			$dynamic_menu_meta = $wpdb->get_results( 
				"
				SELECT 	meta_id, meta_key, meta_value
				FROM " . TAG_POST_META_TABLE . " 
				WHERE post_id = '" . $post_id . "'
				"
			);	

			foreach ( $dynamic_menu_meta as $menu_meta ) {
				$meta_key	= esc_attr( $menu_meta->meta_key );
				if( $meta_key == 'dynamic_menu_slug' && $post_parent == 0 )	$menu_slug	= $menu_meta->meta_value;
				if( $meta_key == 'dynamic_menu_num'  && $post_parent == 0 )	{
					// set the array offset equal to the post_patent 
					$menu_num[$post_id]	= esc_attr( $menu_meta->meta_value );
					//echo "<br>TP85 post=$post_id, men_num=" . $menu_num[$post_id];
				}
			}
			
			$wp_menu_array	= unserialize( $menu_slug );							
			$wp_menu_count	= count( $wp_menu_array );

			$datetime1		= new DateTime($post_date);
			$datetime2		= date_create(date('Y-m-d'));
			$interval		= $datetime2->diff($datetime1);
			$diff 			= $interval->format('%R%a days');


			$menu_on_off = AADynamicMenu::dynamic_menu_on_off_control ( $post_date_array, $post_control_array, $post_title );
			
			if( $post_parent == 0 && $menu_on_off == 0 ) {
				// do not display any part of this menu
				$top_menu_off	= $post_id;
			}
			
			if ( $menu_on_off == 1 ) {	
				for( $x=0; $x<$wp_menu_count; $x++ ) {
					if( $post_parent == 0 ) {
						CustomMenuItems::add_item($wp_menu_array[$x], $post_title, $post_url, $menu_order, $post_parent, $menu_num[$post_id] ); 
					}
					else {	
						if( $post_parent != $top_menu_off ) {
							CustomMenuItems::add_item($wp_menu_array[$x], $post_title, $post_url, $menu_order, $menu_num[$post_parent] ); 
						}
					}
				}
			}
			$x++;	
		}
	}		
} );
