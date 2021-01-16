<?php 
/**************************************************************************
***************************************************************************
	Manage the dynamic menus.
	Add/Modify/Delete menus and associate them with existing WP site menus
***************************************************************************
**************************************************************************/


function aa_admin_menu_data() {
	global $wpdb, $current_date, $current_user;
	///////////////////////////// $dynamic_submenu_countxxxxxx, 
 
	$admin_page = 'tag-dynamic-menus-landing-page';
 	$arr_params = array( 'select_menu', 'add_menu', 'post_id'); 

	//Check if any WP menus were deleted and then cleanup the dynamic menus' associations with the current WP menu list
	AADynamicMenu::cleanup_wp_menu_list();

?>

	
<style>
	.red_button {
	  background-color: red;
	  border: none;
	  border-radius: 12px;
	  color: white;
	  padding: 10px 25px;
	  text-align: center;
	  text-decoration: none;
	  display: inline-block;
	  font-size: 11px;
	  margin: 4px 2px;
	  cursor: pointer;
	}
	
	.black_button {
	  background-color: black;
	  border: none;
	  border-radius: 12px;
	  color: white;
	  padding: 10px 25px;
	  text-align: center;
	  text-decoration: none;
	  display: inline-block;
	  font-size: 11px;
	  margin: 4px 2px;
	  cursor: pointer;
	}

	.rla_table {
	  border-collapse: collapse;
	  width: 100%;
	  border: 1px solid grey;
	  padding: 25px;
	}


</style>

<?php 

	/*******************************************************************
			Delete menu from Dynamic Menu Listing Page
	 *******************************************************************/
	if( isset($_REQUEST['delete_menu']) ) {	
		AADynamicMenu::check_nonce();	
		if( isset( $_REQUEST["confirmed"] ) ) {		
			AADynamicMenu::delete_full_menu( $_REQUEST["post_delete"] );	
			echo AADynamicMenu::action_message( $_REQUEST["post_title"] . " has been deleted and ALL of its Submenus." );
		}
		else {	
			$nonce = wp_create_nonce( 'tag-nonce' );

			echo "<h4>Are You sure you want to delete " .  $_REQUEST["post_title"] . " Menu and all of its Submenus?</h4>
			<form action='' method='POST'>
			
				<input type='hidden' name='tag_nonce' value='$nonce'>
				<input type='hidden' name='post_delete' value='" .	$_REQUEST["post_delete"] . "'>
				<input type='hidden' name='post_title' value='" . 	$_REQUEST["post_title"] . "'>
				<input type='hidden' name='confirmed' value='1'>
				<input type='submit' name='delete_menu' value='Yes, Delete Menu'>
			</form>
			<br><br>No, ABORT and <a href=\"javascript:history.back(-1)\">Return to Menu Listing</a>";
			return;
		}
	}






	/*******************************************************************
			Associate (move) Dynamic Menu to a WP Menu
	 *******************************************************************/
 	if( isset($_REQUEST['associate_menu']) ) {
	
		AADynamicMenu::check_nonce();	
		// get top level and sub menus for this dynamic menu 
		$get_dynamic_posts = $wpdb->get_results( 
			"
			SELECT 	id, post_title
			FROM " . TAG_POSTS_TABLE . " 
			WHERE id = '" . $_REQUEST['post_id'] . "' 
			"
		);	

		foreach ( $get_dynamic_posts as $dynamic_menu ) {
			$post_id	= esc_attr( $dynamic_menu->id );
			$post_title	= esc_attr( $dynamic_menu->post_title );
		
			$new_slug_array = array();
			$max_count = $_REQUEST['menu_count'];
			// if there are no more WP menus (max_wp_menus), reduce the count by 1.
			if( $_REQUEST['max_wp_menus'] == $_REQUEST['menu_count'] ) $max_count--;
			
			$z = 0;
			for( $x=0; $x<=$max_count; $x++ ) {
				// go thru the list of WP menus that this menu is associated with. 
				if( isset( $_REQUEST["select_wp_menus_$x"] ) && $_REQUEST["select_wp_menus_$x"] != '' ) {
					$new_slug_array[$z] = $_REQUEST["select_wp_menus_$x"];
					$z++;
				}
			}
			$counter1 = count($new_slug_array); 
			$new_slugs_serialized = serialize( $new_slug_array );
			
			/******** update dynamic menu's meta data  ***********/
			$update_menus = $wpdb->get_results( 
				"
				UPDATE " . TAG_POST_META_TABLE . " SET	
				meta_value	 	='" . $new_slugs_serialized . "' 
				WHERE ( post_id = '" . $post_id . "' AND meta_key = 'dynamic_menu_slug' )
				"
			);					
		}
	}



	/*******************************************************************
			Update a Dynamic Menu
	 *******************************************************************/
 	if( isset($_REQUEST['update_menu']) ) {
		AADynamicMenu::check_nonce();
		AADynamicMenu::menu_order_resort();
		$delete_top_post	= 0;	
		
		for( $x=0; $x<$_REQUEST['num_posts']; $x++ ) {
			$title_sanitized	= sanitize_text_field( $_REQUEST["post_title_$x"] );
			$url_sanitized		= sanitize_text_field( $_REQUEST["post_url_$x"] );
			$url_sanitized		= str_replace("\'", '', $url_sanitized);

			if( !is_numeric( $_REQUEST["menu_order_$x"] ) ) $_REQUEST["menu_order_$x"] = 99;
			
			// Delete selected menus - delete **ALL** if top level is selected
			if( isset( $_REQUEST["post_delete_$x"] ) ) {
				if( $x == 0 ) {
					$delete_top_post = 1;		
					AADynamicMenu::delete_full_menu( $_REQUEST["post_delete_$x"] );	
					echo AADynamicMenu::action_message( $title_sanitized . " has been deleted and ALL of its Submenus." );
					
				}
				else {	//only delete submenu(s)		
					$delete_sub_menu = $wpdb->get_results( 
						"
						DELETE FROM " . TAG_POSTS_TABLE . " 
						WHERE id = '" . $_REQUEST["post_delete_$x"] . "' 
						"
					);	
					$delete_postmeta = $wpdb->get_results( 
						"
						DELETE FROM " . TAG_POST_META_TABLE . " 
						WHERE post_id = '" . $_REQUEST["post_delete_$x"] . "' 
						"
					);	
				}
				if( isset($_REQUEST['top_page_delete'])) $delete_top_post ;
			}
						
			if( $delete_top_post == 0 ) {
				$_REQUEST['select_menu'] = 1;
				$_REQUEST['post_id'] = $_REQUEST["post_id_0"];
			}

			$date_start1		= date_create($_REQUEST["date_start_$x"]);
			$date_start	 		= date_format($date_start1, 'Y-m-d');					
			$date_end1			= date_create($_REQUEST["date_end_$x"]);
			$date_end	 		= date_format($date_end1, 'Y-m-d');					

			$post_dates_serialized 	= serialize( array( $date_start, $date_end ) );
			
			$date				= 0;
			$is_logged_in		= 0;
			$isnot_logged_in	= 0;
			$is_front_page		= 0;
			$isnot_front_page	= 0;
			$is_home_index		= 0;
			$isnot_home_index	= 0;
			$url_post_control	= 0;
			$url_post_name		= '';
			$url_post_value		= '';
			$logged_in_user		= '';
			
			if( $_REQUEST["menu_control_$x"]  == 'date' )				$date 				= 1;
			if( $_REQUEST["menu_control_$x"]  == 'is_logged_in' )		$is_logged_in 		= 1;
			if( $_REQUEST["menu_control_$x"]  == 'isnot_logged_in' )	$isnot_logged_in	= 1;
			
			if( $_REQUEST["menu_control_$x"]  == 'is_front_page' )		$is_front_page 		= 1;
			if( $_REQUEST["menu_control_$x"]  == 'isnot_front_page' )	$isnot_front_page 	= 1;
			if( $_REQUEST["menu_control_$x"]  == 'is_home_index' )		$is_home_index		= 1;
			if( $_REQUEST["menu_control_$x"]  == 'isnot_home_index' )	$isnot_home_index 	= 1;
			if( $_REQUEST["menu_control_$x"]  == 'url_post_control' )	$url_post_control 	= 1;
			if( $url_post_control  == 1 )	{
				$url_post_name	= sanitize_text_field( $_REQUEST["url_post_name_$x"] );
				$url_post_name	= str_replace("\'", '', $url_post_name);
				$url_post_value	= sanitize_text_field( $_REQUEST["url_post_value_$x"] );
				$url_post_value	= str_replace("\'", '', $url_post_value);
			}
			
			if( $is_logged_in  == 1 ) {
				$logged_in_user	= sanitize_text_field( $_REQUEST["logged_in_user_$x"] );				
				$logged_in_user	= str_replace("\'", '', $logged_in_user);
			}
			
			if( $isnot_logged_in  == 1 ) {
				$logged_in_user	= sanitize_text_field( $_REQUEST["logged_in_user_$x"] );				
				$logged_in_user	= str_replace("\'", '', $logged_in_user);
			}
			
			$menu_option_serialized	= serialize( array( 'date'=>"$date", 'is_logged_in'=>"$is_logged_in", 'logged_in_user'=>"$logged_in_user", 'isnot_logged_in'=>"$isnot_logged_in", 'is_front_page'=>"$is_front_page", 'isnot_front_page'=>"$isnot_front_page", 'is_home_index'=>"$is_home_index", 'isnot_home_index'=>"$isnot_home_index", 'url_post_control'=>"$url_post_control", 'url_post_name'=>"$url_post_name", 'url_post_value'=>"$url_post_value") );		
			
			/******** update menu  ***********/
			$update_menus = $wpdb->get_results( 
				"
				UPDATE " . TAG_POSTS_TABLE . " SET	
					post_title	 			='" . $title_sanitized . "', 	
					post_author				='" . $current_user->ID . "',				
					post_content			='" . $post_dates_serialized . "',  					
					post_content_filtered	='" . $menu_option_serialized . "', 
					guid		 			='" . $url_sanitized . "',
					menu_order	 			='" . $_REQUEST["menu_order_$x"] . "'  
					WHERE id = '" . $_REQUEST["post_id_$x"] . "'
				"
			);		
		}	

		if( empty($_REQUEST["date_start_$x"]) )	$_REQUEST["date_start_$x"]	= $current_date;
		if( empty($_REQUEST["date_end_$x"]) )	$_REQUEST["date_end_$x"] 	= $current_date;

		$post_dates_serialized 	= serialize( array( $_REQUEST["date_start_$x"], $_REQUEST["date_end_$x"] ) );
			$date				= 0;
			$is_logged_in		= 0;
			$isnot_logged_in	= 0;
			$is_front_page		= 0;
			$isnot_front_page	= 0;
			$is_home_index		= 0;
			$isnot_home_index	= 0;
			$url_post_control	= 0;
			$url_post_name		= '';
			$url_post_value		= '';
			$logged_in_user		= '';

			if( isset($_REQUEST["menu_control_$x"]) && $_REQUEST["menu_control_$x"]  == 'date' )			$date 				= 1;
			if( isset($_REQUEST["menu_control_$x"]) && $_REQUEST["menu_control_$x"]  == 'is_logged_in' )	$is_logged_in 		= 1;
			if( isset($_REQUEST["menu_control_$x"]) && $_REQUEST["menu_control_$x"]  == 'isnot_logged_in' )	$isnot_logged_in	= 1;
			if( isset($_REQUEST["menu_control_$x"]) && $_REQUEST["menu_control_$x"]  == 'is_front_page' )	$is_front_page 		= 1;
			if( isset($_REQUEST["menu_control_$x"]) && $_REQUEST["menu_control_$x"]  == 'isnot_front_page' )$isnot_front_page 	= 1;
			if( isset($_REQUEST["menu_control_$x"]) && $_REQUEST["menu_control_$x"]  == 'is_home_index' )	$is_home_index 		= 1;
			if( isset($_REQUEST["menu_control_$x"]) && $_REQUEST["menu_control_$x"]  == 'isnot_home_index' )$isnot_home_index 	= 1;
			if( isset($_REQUEST["menu_control_$x"]) && $_REQUEST["menu_control_$x"]  == 'url_post_control' )$url_post_control 	= 1;
			if( $url_post_control  == 1 )	{
				$url_post_name	= sanitize_text_field( $_REQUEST["url_post_name_$x"] );
				$url_post_name	= str_replace("\'", '', $url_post_name);
				$url_post_value	= sanitize_text_field( $_REQUEST["url_post_value_$x"] );
				$url_post_value	= str_replace("\'", '', $url_post_value);
			}			
			if( $is_logged_in  == 1 ) {
				$logged_in_user	= sanitize_text_field( $_REQUEST["logged_in_user_$x"] );				
				$logged_in_user	= str_replace("\'", '', $logged_in_user);
			}			
			if( $isnot_logged_in  == 1 ) {
				$logged_in_user		= sanitize_text_field( $_REQUEST["logged_in_user_$x"] );			
				$logged_in_user	= str_replace("\'", '', $logged_in_user);
			}

		$menu_option_serialized	= serialize( array( 'date'=>"$date", 'is_logged_in'=>"$is_logged_in", 'logged_in_user'=>"$logged_in_user", 'isnot_logged_in'=>"$isnot_logged_in", 'is_front_page'=>"$is_front_page", 'isnot_front_page'=>"$isnot_front_page", 'is_home_index'=>"$is_home_index", 'isnot_home_index'=>"$isnot_home_index", 'url_post_control'=>"$url_post_control", 'url_post_name'=>"$url_post_name", 'url_post_value'=>"$url_post_value") );		

		$added_submenu	= str_replace(' ', '', $_REQUEST["post_title_$x"]);	
		if( !empty( $added_submenu ) ) {
		
			$title_sanitized	= sanitize_text_field( $_REQUEST["post_title_$x"] );
			$url_sanitized		= sanitize_text_field( $_REQUEST["post_url_$x"] );
			if( !is_numeric( $_REQUEST["menu_order_$x"] ) ) $_REQUEST["menu_order_$x"] = 99;
		
			$rows_affected = $wpdb->insert( TAG_POSTS_TABLE, array( 
				'post_title'			=> $title_sanitized,
				'post_author'			=> $current_user->ID,
				'post_content'			=> $post_dates_serialized, 					
				'post_content_filtered'	=> $menu_option_serialized, 
				'post_name'				=> $title_sanitized,
				'post_type'				=> 'nav_menu_item_dynam',
				'guid' 					=> $url_sanitized, 
				'menu_order'			=> $_REQUEST["menu_order_$x"],
				'post_parent'			=> $_REQUEST['post_id_0']
				) 
			);
		}
		echo AADynamicMenu::action_message( $_REQUEST["post_title_0"] . " has been Updated." );
	}
	
	
	
 
	/*******************************************************************
			Add a New Dynamic Menu
	 *******************************************************************/
 	if( isset($_REQUEST['add_menu']) ) {
		
		AADynamicMenu::check_nonce();

		$existing_dynamic_menus = $wpdb->get_results( 
			"
			SELECT 	id, post_title
			FROM " . TAG_POSTS_TABLE . " 
			WHERE post_parent = '0' AND post_type = 'nav_menu_item_dynam'
			"
		);	
		$menu_count = $wpdb->num_rows;
		$_REQUEST['post_id'] = AA_AddDynamicMenu::add_menu( $existing_dynamic_menus, 1 );	
		$_REQUEST['select_menu'] = 1;
	}
	
	
	
	/*******************************************************************
			 A Dynamic Menu has been selected
	 *******************************************************************/
 	if( isset($_REQUEST['select_menu']) ) {
		
		AADynamicMenu::check_nonce();

		$get_dynamic_menus = $wpdb->get_results( 
			"
			SELECT 	id, post_title, post_name, post_content, post_content_filtered, guid, menu_order, post_parent
			FROM " . TAG_POSTS_TABLE . " 
			WHERE id = '" . $_REQUEST['post_id'] . "'
			ORDER by menu_order
			"
		);	
		$menu_count = $wpdb->num_rows;
				
		$menu_buffer = "<form action='" .  esc_url( remove_query_arg( $arr_params ) ) . "' method='POST'>\n	";
						

		if( $menu_count > 0 ) {
			$url_size 		= 50;
			$position_note	= ' *3';
			foreach ( $get_dynamic_menus as $dynamic_menu ) {
				$post_id			= esc_attr( $dynamic_menu->id );
				$post_title			= esc_attr( $dynamic_menu->post_title );
				$post_name			= esc_attr( $dynamic_menu->post_name );
				$post_parent		= esc_attr( $dynamic_menu->post_parent );
				$post_url			= esc_attr( $dynamic_menu->guid );
				$menu_order			= esc_attr( $dynamic_menu->menu_order );					
				$post_control_array	= unserialize( $dynamic_menu->post_content_filtered);			
				$post_date_array	= unserialize( $dynamic_menu->post_content);
				$date_start1		= date_create($post_date_array[0]);
				$date_start	 		= date_format($date_start1, 'Y-m-d');					
				$date_end1			= date_create($post_date_array[1]);
				$date_end	 		= date_format($date_end1, 'Y-m-d');	
			
				$menu_header = "<h4> Modify Dynamic Menu - <span style='line-height:1.0em;font-size:1.1em;font-weight:bold;color:maroon'>$post_title</span> </h4>\n
				<table border='1' width='95%' cellspacing='0' cellpadding='5'>\n
						<tr><th>Top Level Title</th><th>URL</th><th>Menu Order</th><th>Date</th><th>Delete</th></tr>\n
						<tr><td>\n";

				$menu_buffer .= "<input type='hidden' name='post_id_0' value='$post_id'>\n
				<input name='post_title_0' value='$post_title'></td>\n
				<td><input name='post_url_0' value='$post_url' size='$url_size'></td>\n";
				
				$menu_buffer .= "<td><input name='menu_order_0' value='$menu_order' size='2'> *1</td>\n
				<input name='menu_order_orig_0' value='$menu_order' type='hidden'>\n
				<td>Start: <input size='10' name='date_start_0'  id='dyn_mem_datepicker_0' value='$date_start' >
				<br>End: &nbsp;<input size='10' name='date_end_0'  id='dyn_mem_datepicker_1' value='$date_end' ></td>\n
				<td> &nbsp; &nbsp; <input type='checkbox' name='post_delete_0' value='$post_id'> *2 </td></tr>\n";

				$menu_buffer .= "<tr><td colspan='5'><span style='font-size:0.8em'>Controls: " . AADynamicMenu::menu_control_function( $post_id, 0 ) . "</span></td></tr></table>";
				
				
				$get_dynamic_menus2 = $wpdb->get_results( 
					"
					SELECT 	id, post_title, post_content, post_content_filtered, guid, menu_order, post_parent
					FROM " . TAG_POSTS_TABLE . " 
					WHERE post_parent = '" . $_REQUEST['post_id'] . "'
					ORDER BY menu_order 
					"
				);	
				$submenu_count = $wpdb->num_rows;
							
?>
<script>
    jQuery(document).ready(function($) {
<?php
	$date_picker_count = 2 * ($submenu_count +1) + 1;
	for( $x=0; $x<=$date_picker_count; $x++) {
		echo "\n
		$('#dyn_mem_datepicker_$x').datepicker( {dateFormat: 'yy-mm-dd'} );\n";
	}
?>
    });
</script>

<?php 
				$x = 1;
				$y = 0;
				if( $submenu_count > 0 ) {
					foreach ( $get_dynamic_menus2 as $dynamic_menu ) {
						$y 	= $y + 2;				
						$yy = $y + 1;				
						$post_id		= esc_attr( $dynamic_menu->id );
						$post_title		= esc_attr( $dynamic_menu->post_title );
						$post_parent	= esc_attr( $dynamic_menu->post_parent );
						$post_url		= esc_attr( $dynamic_menu->guid );
						$menu_order		= esc_attr( $dynamic_menu->menu_order );
						$post_date_array	= unserialize( $dynamic_menu->post_content);
						$date_start1		= date_create($post_date_array[0]);
						$date_start	 		= date_format($date_start1, 'Y-m-d');							
						$date_end1			= date_create($post_date_array[1]);
						$date_end	 		= date_format($date_end1, 'Y-m-d');	

						$menu_buffer 	.= "<br><br><table border='1' width='95%' cellspacing='0' cellpadding='5'>\n
						<tr><th>SubMenu Title</th><th>URL</th><th>Menu Order</th><th>Date</th><th>Delete</th></tr>\n
						<tr><td>\n
						
						<input type='hidden' name='post_id_$x' value='$post_id'>\n
						 &nbsp; <input name='post_title_$x' value='$post_title'></td>\n";
						$menu_buffer 	.= "<td><input name='post_url_$x' value='$post_url' size='$url_size'></td>\n";
						$menu_buffer 	.= "<td><input name='menu_order_$x' value='$x' size='2'>$position_note</td>\n
						<input name='menu_order_orig_$x' value='$x' type='hidden'>\n
						
						<td>Start: <input size='10' name='date_start_$x'  id='dyn_mem_datepicker_$y' value='$date_start'>
						<br>End: &nbsp;<input size='10' name='date_end_$x'  id='dyn_mem_datepicker_$yy' value='$date_end'></td>\n
						<td> &nbsp; &nbsp; <input type='checkbox' name='post_delete_$x' value='$post_id'></td></tr>\n";
						
						$menu_buffer .= "<tr><td colspan='5'><span style='font-size:0.8em'>Controls: " . AADynamicMenu::menu_control_function( $post_id, $x ) . "</span>
						<input type='hidden' name='menucontrol_0' value='here I am'></td></tr></table>";

						$x++;
						$position_note = '';
					}
				}					
				$y 	= $y + 2;				
				$yy = $y + 1;	
				$menu_buffer 	.= "<br>Add another Drop-Down Menu\n";
				$menu_buffer 	.= "<br><table border='1' width='95%' cellspacing='0' cellpadding='5'>\n
						<tr><th>SubMenu Title</th><th>URL</th><th>Menu Order</th><th>Date</th><th> &nbsp; &nbsp; &nbsp; </th></tr>\n
						<tr><td>\n
						<input type='hidden' name='post_id_$x' value='0'>\n
				&nbsp; <input name='post_title_$x' value=''></td>\n";
				$menu_buffer 	.= "<td><input name='post_url_$x' value='' size='$url_size'></td>\n";
				$menu_buffer 	.= "<td><input name='menu_order_$x' value='' size='2'> 
				<input name='menu_order_orig_$x' value='99' type='hidden' > </td>				
				<td>Start: <input size='10' name='date_start_$x' id='dyn_mem_datepicker_$y' type='text' value=''>
				<br>End: &nbsp;<input size='10' name='date_end_$x' id='dyn_mem_datepicker_$yy' type='text' value=''></td></tr>\n";
				
				$menu_buffer .= "<tr><td colspan='5'><span style='font-size:0.8em'>Controls:" . AADynamicMenu::menu_control_function( 0, $x ) . "</span></td></tr>";

				$menu_buffer 	.= "<input type='hidden' name='num_posts' value='$x'>\n";

				$nonce = wp_create_nonce( 'tag-nonce' );
				$menu_buffer 	.= "<input type='hidden' name='tag_nonce' value='$nonce'>\n";
			}

			$menu_buffer .= "</tr></table><br><br><input type='submit' name='update_menu' value='Update Dynamic Menu'>\n</form>\n";

			$menu_buffer .= "<p><br>Notes:<ol>
			<li>Counting from Left to Right of existing menu; starting with 0.</li>
			<li>By deleting the top level menu you will delete all sub menus associated with it.</li>
			<li>Sub-Menu Vertical Position in Drop-Down Menu.</li>
			<li>Enter specific User Login.  If left blank, it will control based on any user login.</li>
			<li>This is the Blog Index page (NOT the website Front Page).</li>
			</ol></p>";

			echo $menu_header . $menu_buffer;
		}		
		return;
	}
 
 
 
 
	/*******************************************************************
			Drop into the list of dynamic menus landing page  
	 *******************************************************************/
  	AADynamicMenu::cleanup_wp_menu_list();
	$nonce = wp_create_nonce( 'tag-nonce' );

 	$get_dynamic_menus = $wpdb->get_results( 
		"
		SELECT 	id, post_title
		FROM " . TAG_POSTS_TABLE . " 
		WHERE post_type = 'nav_menu_item_dynam' AND post_parent = '0'
		"
	);	
	$menu_count = $wpdb->num_rows;

	$menu_notice = '';
	$menu_header = "<h4 align='center'>" . $menu_count . " Dynamic Menu(s) Found</h4>\n";
	$menu_buffer = "<table border='0' > ";
	$x = 0;
	if( $menu_count > 0 ) {
		$menu_buffer .= "<ul>\n";
		foreach ( $get_dynamic_menus as $dynamic_menu ) {
			$post_id	= esc_attr( $dynamic_menu->id );
			$post_title	= esc_attr( $dynamic_menu->post_title );
			
			$get_dynamic_sub_menus = $wpdb->get_results( 
				"
				SELECT 	id
				FROM " . TAG_POSTS_TABLE . " 
				WHERE post_parent = '" . $post_id . "'
				"
			);		
			$submenu_count = $wpdb->num_rows;
			
			$menu_buffer 	.= "<form action='" .  esc_url( remove_query_arg( $arr_params ) ) . "' method='POST'>\n 
			<input type='hidden' name='post_id' value='$post_id'>";
			$menu_list = AADynamicMenu::select_wp_menu_names( $post_id );
			
			$move_submit = "<button class='black_button' type='submit' name='associate_menu' >Associate $post_title Menu</button>";
			if( $menu_list[2] == 0 ) 	$move_submit = '';
			
			$submenu_plural = 'submenus';
			if( $submenu_count == 1 ) $submenu_plural = 'submenu';
			
			$menu_buffer 	.= "<tr><td colspan='2' valign='center'><br><li><a href='?page=$admin_page&select_menu=1&post_id=$post_id&tag_nonce=$nonce'>$post_title</a> ( has $submenu_count $submenu_plural )</li></td></tr> 
			<tr><td valign='center'>Assigned to \n$menu_list[0]  $move_submit </td>
			<td valign='center'>&nbsp; &nbsp; <button class='red_button' name='delete_menu'>Delete this Menu</button>
		
			<input type='hidden'	name='tag_nonce'	value='$nonce'>
			<input type='hidden'	name='post_delete'	value='$post_id'>
			<input type='hidden'	name='post_title'	value='$post_title'>
			
			
			
			
			</td></tr>
			<tr><td colspan='2' align='center'>
			<img  src='" . plugin_dir_url( __DIR__ ) . "includes/images/grey.png' height='2' width='90%'></td></tr>\n 
			<input type='hidden' name='menu_count' value='$menu_list[1]'>\n 
			<input type='hidden' name='max_wp_menus' value='$menu_list[2]'>\n 
			</form>\n";			
			$x++;
		}

		$menu_buffer .= "</ul></table>\n";
		
	}
	else {
		$menu_header = "<h4 align='center'>No Dynamic Menus in the System</h4>\n";
	}
	$menu_notice = '';
	
	if( isset( $menu_list[2] ) &&  $menu_list[2] == 0 ) 	$menu_notice = "<p align='center'>\n 
	<span style='line-height:1.0em;font-size:1.1em;font-weight:bold;color:maroon'>There are no WP Menus on the site. <br>You must create a WP menu to associate your dynamic menu to. <br>Go to Appearance->Menus on the Dashboard to create at least one WP menu.</span></p>";

	$menu_buffer .= "<h3><br><a href='?page=$admin_page&add_menu=1&post_id=0&tag_nonce=$nonce'>Add a New Menu</a>  </h3> \n";

	echo $menu_notice . $menu_header . $menu_buffer;
}