<?php
/**
 * Plugin Name: Customify
 * Plugin URI:  https://wordpress.org/plugins/customify/
 * Description: A Theme Customizer Booster to easily and consistently customize Fonts, Colors, and other options for your site.
 * Version: 2.10.5
 * Author: Pixelgrade
 * Author URI: https://pixelgrade.com
 * Author Email: contact@pixelgrade.com
 * Text Domain: customify
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages/
 * Requires at least: 4.9.14
 * Tested up to: 5.9.5
 * Requires PHP: 5.6.40
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once 'includes/lib/class-customify-array.php';
require_once 'includes/extras.php';

/**
 * Returns the main instance of PixCustomifyPlugin to prevent the need to use globals.
 *
 * @since  1.5.0
 * @return PixCustomifyPlugin
 */
function PixCustomifyPlugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pixcustomify.php';

	return PixCustomifyPlugin::instance( __FILE__, '2.10.4' );
}

// Now get the party started.
// We will keep this global variable for legacy reasons.
$pixcustomify_plugin = PixCustomifyPlugin();

// Load all third-party plugins integrations.
require_once 'includes/integrations.php';
