<?php

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

add_shortcode('php_info', 'aa_php_sys_info');

function aa_php_sys_info(){ 
	phpinfo();
}	