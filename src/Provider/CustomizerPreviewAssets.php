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
 * Customizer Preview assets provider class.
 *
 * @since 3.0.0
 */
class CustomizerPreviewAssets extends AbstractHookProvider {
	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_action( 'customize_preview_init', [ $this, 'register_assets' ], 1 );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 3.0.0
	 */
	public function register_assets() {
		$scripts_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script( 'pixelgrade_customify-previewer',
			$this->plugin->get_url( 'dist/js/customizer-preview' . $scripts_suffix . '.js' ),
			[
				'jquery',
				'lodash',
				'customize-preview',
				'underscore',
			],
			VERSION, true );
	}
}
