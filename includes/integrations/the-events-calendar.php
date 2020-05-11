<?php
/**
 * This is logic for integrating with the The Events Calendar plugin (including the PRO version).
 *
 * @link https://wordpress.org/plugins/the-events-calendar/
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// These mess up our Select2.
add_action( 'admin_enqueue_scripts', function () {
	if ( ! is_customize_preview() ) {
		return;
	}

	wp_deregister_script( 'tribe-select2' );
	wp_register_script( 'tribe-select2', '' );

	wp_deregister_style( 'tribe-select2-css' );
	wp_register_style( 'tribe-select2-css', '' );
}, 99 );
