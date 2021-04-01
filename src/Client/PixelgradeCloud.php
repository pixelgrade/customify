<?php
/**
 * This is the class that handles the communication with the Pixelgrade Cloud.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Client;

use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;
use const Pixelgrade\Customify\VERSION;

/**
 * Provides the interface to communicate with the Pixelgrade Cloud.
 *
 * @since 3.0.0
 */
class PixelgradeCloud implements CloudInterface {

	/**
	 * Endpoints configuration.
	 *
	 * @var array
	 */
	protected array $endpoints;

	/**
	 * Logger.
	 *
	 * @var LoggerInterface
	 */
	protected LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param LoggerInterface $logger Logger.
	 */
	public function __construct(
		array $endpoints,
		LoggerInterface $logger
	) {
		$this->endpoints = $endpoints;
		$this->logger = $logger;
	}

	/**
	 * Fetch the design assets data from the Pixelgrade Cloud.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function fetch_design_assets(): array {
		$request_data = [
			'site_url' => home_url('/'),
			// We are only interested in data needed to identify the theme and eventually deliver only design assets suitable for it.
			'theme_data' => $this->get_active_theme_data(),
			// We are only interested in data needed to identify the plugin version and eventually deliver design assets suitable for it.
			'site_data' => $this->get_site_data(),
			// Extra post statuses besides `publish`.
			'post_status' => [],
		];

		// Handle development and testing constants.
		if ( defined('SM_FETCH_DRAFT_ASSETS') && true === SM_FETCH_DRAFT_ASSETS ) {
			$request_data['post_status'][] = 'draft';
		}
		if ( defined('SM_FETCH_PRIVATE_ASSETS') && true === SM_FETCH_PRIVATE_ASSETS ) {
			$request_data['post_status'][] = 'private';
		}
		if ( defined('SM_FETCH_FUTURE_ASSETS') && true === SM_FETCH_FUTURE_ASSETS ) {
			$request_data['post_status'][] = 'future';
		}

		// Allow others to filter the data we send.
		$request_data = apply_filters( 'customify_pixelgrade_cloud_request_data', $request_data, $this );

		$request_args = [
			'method' => $this->endpoints['cloud']['getDesignAssets']['method'],
			'timeout'   => 5,
			'blocking'  => true,
			'body'      => $request_data,
			'sslverify' => false,
		];
		// Get the design assets from the cloud.
		$response = wp_remote_request( $this->endpoints['cloud']['getDesignAssets']['url'], $request_args );
		// Bail in case of decode error or failure to retrieve data.
		// We will return the data already available.
		if ( is_wp_error( $response ) ) {
			return [];
		}
		$response_data = json_decode( wp_remote_retrieve_body( $response ), true );
		// Bail in case of decode error or failure to retrieve data.
		// We will return the data already available.
		if ( null === $response_data || empty( $response_data['data'] ) || empty( $response_data['code'] ) || 'success' !== $response_data['code'] ) {
			return [];
		}

		return apply_filters( 'customify_style_manager_fetch_design_assets', $response_data['data'] );
	}

	/**
	 * Get the active theme data.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	protected function get_active_theme_data(): array {
		$theme_data = [];

		$slug = basename( get_template_directory() );

		$theme_data['slug'] = $slug;

		// Get the current theme style.css data.
		$current_theme = wp_get_theme( get_template() );
		if ( ! empty( $current_theme ) && ! is_wp_error( $current_theme ) ) {
			$theme_data['name'] = $current_theme->get('Name');
			$theme_data['themeuri'] = $current_theme->get('ThemeURI');
			$theme_data['version'] = $current_theme->get('Version');
			$theme_data['textdomain'] = $current_theme->get('TextDomain');
		}

		// Maybe get the WUpdates theme info if it's a theme delivered from WUpdates.
		$wupdates_ids = apply_filters( 'wupdates_gather_ids', [] );
		if ( ! empty( $wupdates_ids[ $slug ] ) ) {
			$theme_data['wupdates'] = $wupdates_ids[ $slug ];
		}

		return apply_filters( 'customify_style_manager_get_theme_data', $theme_data );
	}

	/**
	 * Get the site data.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	protected function get_site_data(): array {
		$site_data = [
			'url' => home_url('/'),
			'is_ssl' => is_ssl(),
			'wp' => [
				'version' => get_bloginfo('version'),
			],
			'customify' => [
				'version' => VERSION,
			],
		];

		return apply_filters( 'customify_style_manager_get_site_data', $site_data );
	}

	/**
	 * Send stats to the Pixelgrade Cloud.
	 *
	 * @since 3.0.0
	 *
	 * @param array $data     The data to be sent.
	 * @param bool  $blocking Optional. Whether this should be a blocking request. Defaults to false.
	 *
	 * @return array|\WP_Error
	 */
	public function send_stats( $data = [], $blocking = false ) {
		if ( empty( $data ) ) {
			// This is what we send by default.
			$data = [
				'site_url' => home_url('/'),
				// We are only interested in data needed to identify the theme and eventually deliver only design assets suitable for it.
				'theme_data' => $this->get_active_theme_data(),
				// We are only interested in data needed to identify the plugin version and eventually deliver design assets suitable for it.
				'site_data' => $this->get_site_data(),
			];
		}

		/**
		 * Filters request data sent to the cloud.
		 *
		 * @param array  $data
		 * @param object $this @todo This argument is no longer needed and should be removed when Pixelgrade Care doesn't rely on it.
		 */
		$data = apply_filters( 'customify_pixelgrade_cloud_request_data', $data, $this );

		$request_args = [
			'method' => $this->endpoints['cloud']['stats']['method'],
			'timeout'   => 5,
			'blocking'  => $blocking,
			'body'      => $data,
			'sslverify' => false,
		];

		// Make the request and return the response.
		return wp_remote_request( $this->endpoints['cloud']['stats']['url'], $request_args );
	}
}
