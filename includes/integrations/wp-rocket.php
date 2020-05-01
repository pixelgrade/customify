<?php
/**
 * This is logic for integrating with the WP-Rocket plugin.
 *
 * @link https://wp-rocket.me/
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Exclude the webfontloader script and the inline script.
function customify_wp_rocket_exclude_webfontloader_script( $list ) {
	$list[] = rocket_clean_exclude_file( plugins_url( 'js/vendor/webfontloader-1-6-28.min.js', PixCustomifyPlugin()->get_file() ) );

	return $list;
}
add_filter( 'rocket_exclude_js', 'customify_wp_rocket_exclude_webfontloader_script', 10, 1 );
add_filter( 'rocket_exclude_defer_js', 'customify_wp_rocket_exclude_webfontloader_script', 10, 1 );
add_filter( 'rocket_exclude_cache_busting', 'customify_wp_rocket_exclude_webfontloader_script', 10, 1 );

add_filter( 'rocket_excluded_inline_js_content', function( $inline_js ) {
	$webfontloader_inline_script = 'customifyFontLoader = function()';
	$inline_js[] = $webfontloader_inline_script;

	return $inline_js;
}, 10, 1 );
