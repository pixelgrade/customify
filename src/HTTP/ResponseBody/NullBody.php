<?php
/**
 * Null response body.
 *
 * @package PixelgradeLT
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify\HTTP\ResponseBody;

/**
 * Null response body class.
 *
 * @since 3.0.0
 */
class NullBody implements ResponseBody {
	/**
	 * Emit the body.
	 *
	 * @since 3.0.0
	 */
	public function emit() {
		// Silence.
	}
}
