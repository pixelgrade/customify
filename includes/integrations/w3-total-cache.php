<?php
/**
 * This is logic for integrating with the W3 Total Cache plugin.
 *
 * @link https://wordpress.org/plugins/w3-total-cache/
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Exclude the webfontloader script and the inline script.
add_filter( 'w3tc_minify_js_script_tags', function ( $script_tags ) {
	if ( ! empty( $script_tags ) && is_array( $script_tags ) ) {
		$webfontloader_script_url = trailingslashit( dirname( plugin_basename( PixCustomifyPlugin::instance()->get_file() ) ) ) . 'js/vendor/webfontloader';
		$webfontloader_inline_script = 'customifyFontLoader = function()';
		foreach ( $script_tags as $key => $tag ) {
			if ( is_string( $tag ) &&
			     ( false !== strpos( $tag, $webfontloader_script_url ) || false !== strpos( $tag, $webfontloader_inline_script ) ) ) {

				unset( $script_tags[ $key ] );
			}
		}
	}

	return $script_tags;
}, 10, 1 );
