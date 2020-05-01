<?php
/**
 * This is logic for integrating with the Autoptimize plugin.
 *
 * @link https://wordpress.org/plugins/autoptimize/
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Prevent moving the webfontloader script.
add_filter( 'autoptimize_filter_js_dontmove', function( $dontmove ) {
	$dontmove[] = 'js/vendor/webfontloader';

	return $dontmove;
}, 10, 1 );

// Exclude the webfontloader script.
add_filter( 'autoptimize_filter_js_exclude', function( $excludeJS ) {
	if ( is_string( $excludeJS ) ) {
		$excludeJS .= ',js/vendor/webfontloader';
	} elseif ( is_array( $excludeJS ) ) {
		$excludeJS[] = 'js/vendor/webfontloader';
	}

	return $excludeJS;
}, 10, 1 );
