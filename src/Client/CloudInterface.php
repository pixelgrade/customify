<?php
/**
 * Cloud interface
 *
 * @package Pixelgrade Customify
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify\Client;

/**
 * Segregated interface of something that should communicate with a cloud to provide design assets and send stats.
 */
interface CloudInterface {
	/**
	 * Fetch the design assets data.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function fetch_design_assets(): array;

	/**
	 * Send stats.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data     The data to be sent.
	 * @param bool  $blocking Optional. Whether this should be a blocking request. Defaults to false.
	 *
	 * @return array|\WP_Error
	 */
	public function send_stats( $data = [], $blocking = false );
}
