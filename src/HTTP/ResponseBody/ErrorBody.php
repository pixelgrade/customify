<?php
/**
 * HTTP error message body.
 *
 * @package Pixelgrade Customify
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify\HTTP\ResponseBody;

use WP_Http as HTTP;

/**
 * HTTP error message body class.
 *
 * @since 0.1.0
 */
class ErrorBody implements ResponseBody {
	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected string $message;

	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected int $status_code;

	/**
	 * Create an error message body.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message     Error message.
	 * @param int    $status_code Optional. HTTP status code.
	 */
	public function __construct( string $message, int $status_code = HTTP::INTERNAL_SERVER_ERROR ) {
		$this->message     = $message;
		$this->status_code = $status_code;
	}

	/**
	 * Display the error message.
	 *
	 * @since 0.1.0
	 */
	public function emit() {
		wp_die(
			wp_kses_data( $this->message ),
			absint( $this->status_code )
		);
	}
}
