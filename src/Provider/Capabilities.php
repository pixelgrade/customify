<?php
/**
 * Capabilities provider.
 *
 * @package PixelgradeLT
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify\Provider;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Capabilities as Caps;

/**
 * Capabilities provider class.
 *
 * @since 3.0.0
 */
class Capabilities extends AbstractHookProvider {
	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		add_filter( 'map_meta_cap', [ $this, 'map_meta_cap' ], 10, 4 );
	}

	/**
	 * Map meta capabilities to primitive capabilities.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $caps Returns the user's actual capabilities.
	 * @param string $cap  Capability name.
	 * @return array
	 */
	public function map_meta_cap( array $caps, string $cap ): array {
		switch ( $cap ) {
//			case Caps::DOWNLOAD_PACKAGE:
//				$caps = [ Caps::DOWNLOAD_PACKAGES ];
//				break;
//
//			case Caps::VIEW_PACKAGE:
//				$caps = [ Caps::VIEW_PACKAGES ];
//				break;
		}

		return $caps;
	}
}
