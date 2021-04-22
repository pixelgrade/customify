<?php
/**
 * Customizer assets provider.
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
 * Customizer assets provider class.
 *
 * @since 3.0.0
 */
class CustomizerAssets extends AbstractHookProvider {
	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_action( 'customize_controls_init', [ $this, 'register_assets' ], 1 );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 3.0.0
	 */
	public function register_assets() {
		$scripts_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$rtl_suffix     = is_rtl() ? '-rtl' : '';

		/**
		 * GENERAL CUSTOMIZER RELATED
		 */
		wp_register_script( 'pixelgrade_customify-select2',
			$this->plugin->get_url( 'vendor_js/select2-4.0.13/dist/js/select2.full' . $scripts_suffix . '.js' ),
			[ 'jquery' ],
			VERSION );
		wp_register_script( 'jquery-react',
			$this->plugin->get_url( 'vendor_js/jquery-react' . $scripts_suffix . '.js' ),
			[ 'jquery' ],
			VERSION );
		wp_register_script( 'pixelgrade_customify-regression',
			$this->plugin->get_url( 'vendor_js/regression' . $scripts_suffix . '.js' ),
			[],
			VERSION );
		wp_register_script( 'pixelgrade_customify-chroma',
			$this->plugin->get_url( 'vendor_js/chroma' . $scripts_suffix . '.js' ),
			[],
			VERSION );
		wp_register_script( 'pixelgrade_customify-previewer-resizer',
			$this->plugin->get_url( 'dist/js/customizer-preview-resizer' . $scripts_suffix . '.js' ),
			[
				'customize-preview',
			],
			VERSION, true );
		wp_register_script( 'pixelgrade_customify-customizer',
			$this->plugin->get_url( 'dist/js/customizer' . $scripts_suffix . '.js' ),
			[
				'jquery',
				'jquery-react',
				'pixelgrade_customify-chroma',
				'pixelgrade_customify-previewer-resizer',
				'pixelgrade_customify-select2',
				'pixelgrade_customify-regression',
				'react',
				'react-dom',
				'underscore',
				'customize-controls',
			],
			VERSION );
		wp_localize_script( 'pixelgrade_customify-customizer', 'WP_API_Settings', [
			'root'  => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		] );

		wp_register_style(
			'pixelgrade_customify-customizer',
			$this->plugin->get_url( 'dist/js/customizer' . $rtl_suffix . '.css' ),
			[],
			VERSION
		);

		/**
		 * CUSTOMIZER CONTROLS RELATED
		 */
		wp_register_script( 'pixelgrade_customify-ace-editor',
			$this->plugin->get_url( 'vendor_js/ace/ace.js' ),
			[ 'jquery' ],
			VERSION,
			true );

		/**
		 * STYLE MANAGER RELATED
		 */
		wp_register_script(
			'pixelgrade_customify-dark-mode',
			$this->plugin->get_url( 'dist/js/dark-mode' . $scripts_suffix . '.js' ),
			[ 'jquery' ],
			VERSION
		);

		/**
		 * COLOR PALETTES RELATED
		 */


		/**
		 * FONT PALETTES RELATED
		 */
		//		wp_register_script( 'pixelgrade_customify-font-palettes',
		//			$this->plugin->get_url( 'js/customizer/font-palettes' . $scripts_suffix . '.js' ),
		//			[
		//				'pixelgrade_customify-regression',
		//				'jquery',
		//				//'pixelgrade_customify-fontfields',
		//			],
		//			VERSION );

		/**
		 * CONTROLS SEARCH FIELD RELATED
		 */
		wp_register_script( 'pixelgrade_customify-fuse',
			$this->plugin->get_url( 'vendor_js/fuse-6.0.0/fuse.basic' . $scripts_suffix . '.js' ),
			[],
			null );

		wp_register_script( 'pixelgrade_customify-customizer-search',
			$this->plugin->get_url( 'dist/js/customizer-search' . $scripts_suffix . '.js' ),
			[ 'jquery', 'pixelgrade_customify-fuse', ],
			VERSION );


	}
}
