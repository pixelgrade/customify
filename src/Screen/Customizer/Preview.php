<?php
/**
 * Customizer screen preview functionality provider.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package PixelgradeLT
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Customizer screen preview provider class.
 *
 * @since 3.0.0
 */
class Preview extends AbstractHookProvider {

	/**
	 * Create the Customizer screen preview.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		$this->add_action( 'customize_preview_init', 'enqueue_assets', 99999 );

		$this->add_action( 'wp_footer', 'output_color_palettes_preview_overlay' );
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 3.0.0
	 */
	protected function enqueue_assets() {
		wp_enqueue_script( 'pixelgrade_customify-previewer' );
	}

	/**
	 * Output a wrapper for the color palettes preview overlay.
	 *
	 * @since 3.0.0
	 */
	protected function output_color_palettes_preview_overlay() {
		if ( is_customize_preview() ) {
			echo '<div id="sm-color-palettes-preview"></div>';
		}
	}
}
