<?php
/**
 * HTTP exception.
 *
 * @package Pixelgrade Customify
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify\Exception;

use Throwable;
use WP_Http as HTTP;

/**
 * HTTP exception class.
 *
 * @since 3.0.0
 */
class HttpException extends \Exception implements CustomifyException {
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $status_code;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param string         $message     Message.
	 * @param int            $status_code Optional. HTTP status code. Defaults to 500.
	 * @param int            $code        Exception code.
	 * @param Throwable|null $previous    Previous exception.
	 */
	public function __construct(
		string $message,
		int $status_code = HTTP::INTERNAL_SERVER_ERROR,
		int $code = 0,
		Throwable $previous = null
	) {
		$this->status_code = $status_code;
		$message           = $message ?: 'Internal Server Error';

		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Create an exception for a forbidden resource request.
	 *
	 * @since 3.0.0.
	 *
	 * @param int            $code     Optional. The Exception code.
	 * @param Throwable|null $previous Optional. The previous throwable used for the exception chaining.
	 *
	 * @return HTTPException
	 */
	public static function forForbiddenResource(
		int $code = 0,
		Throwable $previous = null
	): HttpException {
		$user_id     = get_current_user_id();
		$request_uri = $_SERVER['REQUEST_URI'];
		$message     = "Forbidden resource requested; User: {$user_id}; URI: {$request_uri}";

		return new static( $message, HTTP::FORBIDDEN, $code, $previous );
	}

	/**
	 * Retrieve the HTTP status code.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function getStatusCode(): int {
		return $this->status_code;
	}
}
