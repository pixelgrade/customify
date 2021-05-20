<?php
/**
 * General assets provider.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Provider;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use const Pixelgrade\Customify\VERSION;

/**
 * General assets provider class.
 *
 * @since 3.0.0
 */
class GeneralAssets extends AbstractHookProvider {

	/**
	 * Options.
	 *
	 * @var Options
	 */
	protected Options $options;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Options         $options Options.
	 */
	public function __construct(
		Options $options
	) {
		$this->options = $options;
	}

	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_action( 'init', [ $this, 'register_assets' ], 1 );
		add_action( 'wp_print_scripts', [ $this, 'output_customizer_config' ], 1 );
		add_action( 'admin_print_scripts', [ $this, 'output_customizer_config' ], 1 );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 3.0.0
	 */
	public function register_assets() {

		wp_register_style(
			'pixelgrade_customify-sm-colors-custom-properties',
			$this->plugin->get_url( 'dist/css/sm-colors-custom-properties.css' ),
			[],
			VERSION
		);

		$option = PixCustomifyPlugin()->get_option( 'sm_advanced_palette_output' );

		$css = sm_get_palette_output_from_color_config( $option );

		wp_add_inline_style( 'pixelgrade_customify-sm-colors-custom-properties', $css );
	}

	/**
	 * Initialize global variable with customizer config.
	 *
	 * @since 3.0.0
	 */
	public function output_customizer_config() {
		echo '<script>';
		echo 'window.customify_config = ' . json_encode( $this->options->get_details_all() ) . ';';
		echo '</script>';
	}
}
