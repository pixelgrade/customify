<?php
/**
 * Response body interface.
 *
 * @package Pixelgrade Customify
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify\HTTP\ResponseBody;

/**
 * Response body interface.
 *
 * @since 3.0.0
 */
interface ResponseBody {
	/**
	 * Emit the response body.
	 */
	public function emit();
}
