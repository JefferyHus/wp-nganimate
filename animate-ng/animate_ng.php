<?php
/**
 * Plugin Name: Animate New Logo
 * Plugin URI: /
 * Version: 1.0
 * Author: jefferyhus
 * Author URI: http://github.com/jefferyhus
 * Tags: logo, animate, animate logo, create new logo
 */

define("NGVERSION", '1.0');
define("NGPATH", __DIR__ . DIRECTORY_SEPARATOR); // plugin_dir_path(__FILE__)
define("NGDS", DIRECTORY_SEPARATOR);

require_once NGPATH . "lib" . NGDS . "nglogo.php";

// make sure the core exists
if (!class_exists("Nglogo")) {
	echo "Sorry but we can't work without the existance of our core class";
	exit;
}

register_activation_hook(NGPATH . 'animate_ng.php', array('Nglogo', 'install'));
add_action( "init", array('Nglogo', '_init'), $priority, $accepted_args );