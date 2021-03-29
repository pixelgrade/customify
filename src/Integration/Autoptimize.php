<?php
/**
 * Autoptimize plugin integration.
 *
 * @link    https://wordpress.org/plugins/autoptimize/
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package PixelgradeLT
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Integration;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Autoptimize plugin integration provider class.
 *
 * @since 3.0.0
 */
class Autoptimize extends AbstractHookProvider {
	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		$this->add_filter( 'autoptimize_filter_js_dontmove', 'js_dontmove', 10, 1 );
		$this->add_filter( 'autoptimize_filter_js_exclude', 'js_exclude', 10, 1 );
	}

	/**
	 * Prevent moving the webfontloader script.
	 *
	 * @since 3.0.0
	 *
	 * @param array $dontmove
	 *
	 * @return array
	 */
	protected function js_dontmove( array $dontmove ): array {
		$dontmove[] = 'js/vendor/webfontloader';

		return $dontmove;
	}

	/**
	 * Exclude the webfontloader script.
	 *
	 * @since 3.0.0
	 *
	 * @param $excludeJS
	 *
	 * @return mixed|string
	 */
	protected function js_exclude( $excludeJS ) {
		if ( is_string( $excludeJS ) ) {
			$excludeJS .= ',js/vendor/webfontloader';
		} elseif ( is_array( $excludeJS ) ) {
			$excludeJS[] = 'js/vendor/webfontloader';
		}

		return $excludeJS;
	}
}
