<?php
/**
 * W3 Total Cache plugin integration.
 *
 * @link    https://wordpress.org/plugins/w3-total-cache/
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Integration;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * W3 Total Cache plugin integration provider class.
 *
 * @since 3.0.0
 */
class W3TotalCache extends AbstractHookProvider {
	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		$this->add_filter( 'w3tc_minify_js_script_tags', 'exclude_scripts_from_minify', 10, 1 );
	}

	/**
	 * Prevent minifying the webfontloader script and the inline script.
	 *
	 * @since 3.0.0
	 *
	 * @param $script_tags
	 *
	 * @return mixed
	 */
	protected function exclude_scripts_from_minify( $script_tags ) {
		if ( ! empty( $script_tags ) && is_array( $script_tags ) ) {
			$webfontloader_script_url = $this->plugin->get_url('vendor_js/webfontloader');
			$webfontloader_inline_script = 'customifyFontLoader = function()';
			foreach ( $script_tags as $key => $tag ) {
				if ( is_string( $tag ) &&
				     ( false !== strpos( $tag, $webfontloader_script_url ) || false !== strpos( $tag, $webfontloader_inline_script ) ) ) {

					unset( $script_tags[ $key ] );
				}
			}
		}

		return $script_tags;
	}
}
