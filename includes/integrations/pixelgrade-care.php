<?php
/**
 * This is logic for integrating with the Pixelgrade Care plugin.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// We want to invalidate caches whenever the Pixelgrade Care license is updated since it may unlock new features,
// and so unlock new Customify options.
add_filter( 'pre_set_theme_mod_pixcare_license', array( PixCustomifyPlugin(), 'filter_invalidate_all_caches' ), 10, 1 );
