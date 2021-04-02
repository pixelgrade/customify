<?php
/**
 * Admin dashboard assets provider.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Provider;

use PixCustomify_Customizer;
use Pixelgrade\Customify\Utils\ScriptsEnqueue;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use const Pixelgrade\Customify\VERSION;

/**
 * Admin dashboard assets provider class.
 *
 * @since 3.0.0
 */
class AdminAssets extends AbstractHookProvider {
	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 1 );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 3.0.0
	 */
	public function register_assets() {
		$scripts_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$rtl_suffix     = is_rtl() ? '-rtl' : '';

		wp_register_script(
			'pixelgrade_customify-admin',
			$this->plugin->get_url( 'assets/js/admin.js' ),
			[ 'jquery', 'wp-backbone', 'wp-util' ],
			VERSION,
			true
		);

		wp_register_script(
			'pixelgrade_customify-settings',
			$this->plugin->get_url( 'dist/js/settings' . $scripts_suffix . '.js' ),
			[ 'jquery' ],
			VERSION,
			true
		);

		wp_add_inline_script( 'pixelgrade_customify-settings',
			ScriptsEnqueue::getlocalizeToWindowScript( 'customify',
				[
					'config' => [
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'wp_rest'  => [
							'root'                     => esc_url_raw( rest_url() ),
							'nonce'                    => wp_create_nonce( 'wp_rest' ),
							'customify_settings_nonce' => wp_create_nonce( 'customify_settings_nonce' ),
						],
					],
				]
			) );

		wp_register_style(
			'pixelgrade_customify-settings',
			$this->plugin->get_url( 'dist/css/settings' . $rtl_suffix . '.css' ),
			[],
			VERSION
		);

		/**
		 * BLOCK EDITOR RELATED
		 */
		wp_register_script(
			'pixelgrade_customify-web-font-loader',
			$this->plugin->get_url( 'vendor_js/webfontloader-1-6-28.min.js' ),
			[ 'wp-editor' ], null );
	}
}
