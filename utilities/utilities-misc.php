<?php

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );
echo "<h1 align=center>TP4 Utility - php info</h1>";

add_shortcode('php_info', 'aa_php_sys_info');

function aa_php_sys_info(){ 
	phpinfo();
}	