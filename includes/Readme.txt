My_dynamic_menu Plugin Functionality Overview:

	My_dynamic_menu provides the capability to create dynamic menus that can be controlled by 
		*	Date Window
			Displays menu if the current date is greater than or equal to the start date AND less than or equal to the end date.
	
		*	User is logged in
			The menu will be displayed if a user is logged in. 
			There is a user_login name field where if the user_login is not provided the system will display the menu if anyone is logged in.
			If a user_login name is provided, the system will display the menu only if that user is logged in.

		*	User is not logged in
			Similar process as above except, if the user_login is not provided the system will not display the menu if anyone is logged in.
			If a user_login name is provided, the system will not display the menu only if that user is logged in.
		
		*	Is Front Page
			The menu will be displayed only on the website Homepage. 

		*	Is not Front Page
			The menu will not be displayed on the website Homepage. It will be displayed on all other web pages.

		*	Is Home (Blog) Page
			The menu will be displayed only on the Home Index Page for Blogs. 

		*	Is not Home (Blog) Page
			The menu will not be displayed on the Home Index Page for Blogs. It will be displayed on all other Blog pages.

		*	URL or POST Parameter (caSe sEnsitive)
			The menu will be displayed if there is a parameter in the url or there is a POST parameter.  If the parameter field is left blank, any url or POST parameter will display the menu. 
			If the parameter is provided but the value is not provided, any value for that parameter will display the menu.
			If the parameter and a value is provided, the parameter and the provided value must both match to display the menu.

	My Dynamic Menu is based on original work by Jonathan Daggerhart.








