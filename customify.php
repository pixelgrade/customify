<?php
/*
Plugin Name: Customify
Plugin URI:  https://wordpress.org/plugins/customify/
Description: A Theme Customizer Booster
Version: 1.5.4
Author: Pixelgrade
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

$current_data = get_option( $config['settings-key'] );

if ( $current_data === false ) {
	add_option( $config['settings-key'], $defaults );
} elseif ( count( array_diff_key( $defaults, $current_data ) ) != 0)  {
	$plugindata = array_merge( $defaults, $current_data );
	update_option( $config['settings-key'], $plugindata );
}
# else: data is available; do nothing

/**
 * Returns the main instance of PixCustomifyPlugin to prevent the need to use globals.
 *
 * @since  1.5.0
 * @return PixCustomifyPlugin
 */
function PixCustomifyPlugin() {

	require_once( plugin_dir_path( __FILE__ ) . 'class-pixcustomify.php' );
	$instance = PixCustomifyPlugin::instance( __FILE__, '1.5.4' );
	return $instance;
}

// Now get the party started
// We will keep this global variable for legacy
$pixcustomify_plugin = PixCustomifyPlugin();

// Load custom modules
require_once( 'features/class-CSS_Editor.php' );
require_once( 'features/class-Font_Selector.php' );