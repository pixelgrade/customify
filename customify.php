<?php
/*
Plugin Name: Customify
Plugin URI:  https://pixelgrade.com
Description: A Theme Customizer Booster
Version: 1.4.1
Author: PixelGrade
Author URI: https://pixelgrade.com
Author Email: contact@pixelgrade.com
Text Domain: customify
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Domain Path: /languages/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// ensure EXT is defined
if ( ! defined('EXT')) {
	define('EXT', '.php');
}

require 'core/bootstrap'.EXT;

$config = include 'plugin-config'.EXT;

// set textdomain
pixcustomify::settextdomain('customify');

// Ensure Test Data
// ----------------

$defaults = include 'plugin-defaults'.EXT;

$current_data = get_option($config['settings-key']);

if ($current_data === false) {
	add_option($config['settings-key'], $defaults);
}
else if (count(array_diff_key($defaults, $current_data)) != 0) {
	$plugindata = array_merge($defaults, $current_data);
	update_option($config['settings-key'], $plugindata);
}
# else: data is available; do nothing

// Load Callbacks
// --------------

$basepath = dirname(__FILE__).DIRECTORY_SEPARATOR;
$callbackpath = $basepath.'callbacks'.DIRECTORY_SEPARATOR;
pixcustomify::require_all($callbackpath);

require_once( plugin_dir_path( __FILE__ ) . 'class-pixcustomify.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'PixCustomifyPlugin', 'activate' ) );
//register_deactivation_hook( __FILE__, array( 'customifyPlugin', 'deactivate' ) );


function customify_init_plugin () {
	global $pixcustomify_plugin;
	$pixcustomify_plugin = PixCustomifyPlugin::get_instance();
}
add_action('plugins_loaded', 'customify_init_plugin', 20 );