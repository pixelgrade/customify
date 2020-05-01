<?php
/**
 * This is logic for integrating with the WP-Optimize plugin.
 *
 * @link https://wordpress.org/plugins/wp-optimize/
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Try to exclude the webfontloader script by adding a default rule in the plugin options.
// The free version doesn't do minification right now.
