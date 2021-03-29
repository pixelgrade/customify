<?php
/**
 * Deprecated functionality, mainly for backwards compatibility.
 *
 * @package PixelgradeLT
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

namespace { // global code

	/**
	 * Returns the main instance of PixCustomifyPlugin to prevent the need to use globals.
	 *
	 * @deprecated Use Pixelgrade\Customify\plugin() instead.
	 * @since  1.5.0
	 * @return Pixelgrade\Customify\Plugin
	 */
	function PixCustomifyPlugin() {
		_deprecated_function( __FUNCTION__, '3.0.0', 'Pixelgrade\Customify\plugin()' );

		return Pixelgrade\Customify\plugin();
	}
}

namespace Pixelgrade\Customify {

}
