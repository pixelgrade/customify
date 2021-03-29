<?php
/**
 * Health check provider.
 *
 * @package PixelgradeLT
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify\Provider;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\HTTP\Request;
use WP_Error;
use WP_Http as HTTP;

use function PixelgradeLT\Records\get_authorization_header;

/**
 * Class to check the health of the system.
 *
 * @since 3.0.0
 */
class HealthCheck extends AbstractHookProvider {
	/**
	 * Server request.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Request $request Request instance.
	 */
	public function __construct( Request $request ) {
		$this->request = $request;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
//		add_action( 'admin_post_nopriv_pixelgradelt_records_check_authorization_header', [ $this, 'handle_authorization_request' ] );
	}

	/**
	 * Display a notice.
	 *
	 * @since 3.0.0
	 *
	 * @param string $message Message to display.
	 */
	protected static function display_notice( $message ) {
		printf(
			'<div class="notice notice-error"><p><strong>%s:</strong> %s</p></div>',
			esc_html__( 'Health Check', '__plugin_txtd' ),
			wp_kses(
				$message,
				[
					'a' => [
						'href'   => true,
						'rel'    => true,
						'target' => true,
					],
				]
			)
		);
	}

	/**
	 * Display a health check admin notice if a check fails.
	 *
	 * @since 3.0.0
	 */
	public static function display_authorization_notice() {
		try {
			self::check_authorization_header();
		} catch ( \Exception $e ) {
			self::display_notice( $e->getMessage() );
		}
	}

	/**
	 * Display a notice if pretty permalinks aren't enabled.
	 *
	 * @since 3.0.0
	 */
	public static function display_permalink_notice() {
		$value = get_option( 'permalink_structure', '' );
		if ( ! empty( $value ) ) {
			return;
		}

		$message = sprintf(
			/* translators: %s: permalink screen URL */
			__( 'Pixelgrade Customify requires pretty permalinks to be enabled. <a href="%s">Enable permalinks</a>.', '__plugin_txtd' ),
			esc_url( admin_url( 'options-permalink.php' ) )
		);

		self::display_notice( $message );
	}

	/**
	 * Check whether authorization headers are supported.
	 *
	 * @since 3.0.0
	 *
	 * @throws \UnexpectedValueException If the response could not be handled or parsed.
	 * @throws \RuntimeException If the authorization check fails.
	 * @return boolean True if authorization headers are supported.
	 */
	public static function check_authorization_header() {
		$url = add_query_arg(
			[
				'action' => 'pixelgradelt_records_check_authorization_header',
			],
			admin_url( 'admin-post.php' )
		);

		$response = wp_remote_get(
			$url,
			[
				'headers'   => [
					// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'Authorization' => 'Basic ' . base64_encode( '%api_key%:pixelgradelt_records' ),
				],
				'timeout'   => 10,
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			]
		);

		if ( is_wp_error( $response ) ) {
			throw new \UnexpectedValueException(
				sprintf(
					'The authorization header check encountered an unexpected error. %s',
					$response->get_error_message()
				)
			);
		}

		$json = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! isset( $json->success ) ) {
			throw new \UnexpectedValueException( 'The authorization header check failed; the response could not be parsed as JSON.' );
		}

		if ( ! $json->success ) {
			throw new \RuntimeException( $json->data->message );
		}

		return true;
	}

	/**
	 * Handle authorization check requests.
	 *
	 * @since 3.0.0
	 */
	public function handle_authorization_request() {
		$header = $this->request->get_header( 'authorization' );
		if ( empty( $header ) ) {
			$this->send_json_error(
				'missing_header',
				sprintf(
					'The authorization header check failed; the header was missing. <a href="%s" target="_blank" rel="noopener noreferer">Learn more about this issue</a>.',
					'https://github.com/pixelgradelt/pixelgradelt-records/blob/develop/docs/troubleshooting.md#basic-auth-not-working'
				)
			);
		}

		$user = $this->request->get_header( 'PHP_AUTH_USER' );
		if ( empty( $user ) ) {
			$this->send_json_error(
				'missing_user',
				'The authorization header check failed; The PHP_AUTH_USER variable was missing.'
			);
		}

		$password = $this->request->get_header( 'PHP_AUTH_PW' );
		if ( empty( $password ) || '__plugin_txtd' !== $password ) {
			$this->send_json_error(
				'invalid_password',
				'The authorization header check failed; the password was invalid.'
			);
		}

		wp_send_json_success();
	}

	/**
	 * Send a JSON error response.
	 *
	 * @since 3.0.0
	 *
	 * @param  string $code    Error code.
	 * @param  string $message Error message.
	 * @param  int    $status  Optional. HTTP status code. Defaults to 401.
	 */
	protected function send_json_error( string $code, string $message, int $status = HTTP::UNAUTHORIZED ) {
		wp_send_json_error(
			[
				'code'    => $code,
				'message' => $message,
			],
			$status
		);
	}
}
