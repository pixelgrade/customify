<?php
/**
 * This is logic for integrating with the WP Fastest Cache plugin.
 *
 * @link https://wordpress.org/plugins/wp-fastest-cache/
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Try to exclude the webfontloader script by adding a default rule in the plugin options.
add_filter( 'default_option_WpFastestCacheExclude', function ( $default ) {
	$webfontloader_script_url = trailingslashit( dirname( plugin_basename( PixCustomifyPlugin::instance()->get_file() ) ) ) . 'js/vendor/webfontloader';
	if ( empty( $default ) ) {
		$default = json_encode( [
			[
				'prefix' => 'contain',
				'content' => $webfontloader_script_url,
				'type' => 'js',
			]
		] );
	}

	return $default;
}, 10, 1 );
