<?php 
/*********************************************************************************
		Notes for the dynamic menus plugin.
*********************************************************************************/

function aa_admin_menu_notes_data() {

?>
<h2 align='center'>My_dynamic_menu Plugin Functionality Overview</h2>

My_dynamic_menu provides the capability to create dynamic menus that can be controlled by:</dt>
	<dl>
		<dt>Date Window
			<dd>Displays menu if the current date is greater than or equal to the start date AND less than or equal to the end date.</dd>
		</dt>
	
		<dt>User is logged in
			<dd>The menu will be displayed if a user is logged in. 
			<br>There is a user_login name field where if the user_login is not provided the system will display the menu if anyone is logged in.
			<br>If a user_login name is provided, the system will display the menu only if that user is logged in.</dd>
		</dt>

		<dt>User is not logged in
			<dd>Similar process as above except, if the user_login is not provided the system will not display the menu if anyone is logged in.
			<br>If a user_login name is provided, the system will not display the menu only if that user is logged in.</dd>
		</dt>
		
		<dt>Is Front Page
			<dd>The menu will be displayed only on the website Homepage.</dd>
		</dt>

		<dt>Is not Front Page
			<dd>The menu will not be displayed on the website Homepage. It will be displayed on all other web pages.</dd>
		</dt>

		<dt>Is Home (Blog) Page
			<dd>The menu will be displayed only on the Home Index Page for Blogs.</dd>
		</dt>

		<dt>Is not Home (Blog) Page
			<dd>The menu will not be displayed on the Home Index Page for Blogs. It will be displayed on all other Blog pages.</dd>
		</dt>

		<dt>URL or POST Parameter
			<dd>The menu will be displayed if there is a parameter in the url or there is a POST parameter.  If the parameter field is left blank, any url or POST parameter will display the menu. 
			<br>If the parameter is provided but the value is not provided, any value for that parameter will display the menu.
			<br>If the parameter and a value is provided, the parameter and the provided value must both match to display the menu.</dd>
		</dt>
	</dl>
	
<?php
}