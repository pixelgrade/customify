<?php
/**
 * This is the class that handles the communication with the cloud.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       1.7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Cloud_Api' ) ) {

class Customify_Cloud_Api {

	/**
	 * External REST API endpoints used for communicating with the Pixelgrade Cloud.
	 * @var array
	 * @access public
	 * @since    1.7.4
	 */
	public static $externalApiEndpoints;

	/**
	 * Constructor.
	 *
	 * @since 1.7.4
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Initialize this module.
	 *
	 * @since 1.7.4
	 */
	public function init() {
		// Make sure our constants are in place, if not already defined.
		defined( 'PIXELGRADE_CLOUD__API_BASE' ) || define( 'PIXELGRADE_CLOUD__API_BASE', 'https://cloud.pixelgrade.com/' );

		// Save the external API endpoints in a easy to get property.
		self::$externalApiEndpoints = apply_filters( 'customify_style_manager_external_api_endpoints', array(
			'cloud' => array(
				'getDesignAssets' => array(
					'method' => 'GET',
					'url'    => PIXELGRADE_CLOUD__API_BASE . 'wp-json/pixcloud/v1/front/design_assets',
				),
				'stats'           => array(
					'method' => 'POST',
					'url'    => PIXELGRADE_CLOUD__API_BASE . 'wp-json/pixcloud/v1/front/stats',
				),
			),
		) );
	}

	/**
	 * Fetch the design assets data from the Pixelgrade Cloud.
	 *
	 * @since 1.7.4
	 *
	 * @return array|false
	 */
	public function fetch_design_assets() {
		$request_data = array(
			'site_url' => home_url('/'),
			// We are only interested in data needed to identify the theme and eventually deliver only design assets suitable for it.
			'theme_data' => $this->get_active_theme_data(),
			// We are only interested in data needed to identify the plugin version and eventually deliver design assets suitable for it.
			'site_data' => $this->get_site_data(),
			// Extra post statuses besides `publish`.
			'post_status' => array(),
		);

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

		$request_args = array(
			'method' => self::$externalApiEndpoints['cloud']['getDesignAssets']['method'],
			'timeout'   => 5,
			'blocking'  => true,
			'body'      => $request_data,
			'sslverify' => false,
		);
		// Get the design assets from the cloud.
		$response = wp_remote_request( self::$externalApiEndpoints['cloud']['getDesignAssets']['url'], $request_args );
		// Bail in case of decode error or failure to retrieve data.
		// We will return the data already available.
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$response_data = json_decode( wp_remote_retrieve_body( $response ), true );
		// Bail in case of decode error or failure to retrieve data.
		// We will return the data already available.
		if ( null === $response_data || empty( $response_data['data'] ) || empty( $response_data['code'] ) || 'success' !== $response_data['code'] ) {
			return false;
		}

		return apply_filters( 'customify_style_manager_fetch_design_assets', $response_data['data'] );
	}

	/**
	 * Get the active theme data.
	 *
	 * @since 1.7.4
	 *
	 * @return array
	 */
	public function get_active_theme_data() {
		$theme_data = array();

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
		$wupdates_ids = apply_filters( 'wupdates_gather_ids', array() );
		if ( ! empty( $wupdates_ids[ $slug ] ) ) {
			$theme_data['wupdates'] = $wupdates_ids[ $slug ];
		}

		return apply_filters( 'customify_style_manager_get_theme_data', $theme_data );
	}

	/**
	 * Get the site data.
	 *
	 * @since 1.7.4
	 *
	 * @return array
	 */
	public function get_site_data() {
		$site_data = array(
			'url' => home_url('/'),
			'is_ssl' => is_ssl(),
		);

		$site_data['wp'] = array(
			'version' => get_bloginfo('version'),
		);

		$site_data['customify'] = array(
			'version' => PixCustomifyPlugin()->get_version(),
		);

		return apply_filters( 'customify_style_manager_get_site_data', $site_data );
	}

	/**
	 * Send stats to the Pixelgrade Cloud.
	 *
	 * @since 1.7.4
	 *
	 * @param array $request_data The data to be sent.
	 * @param bool $blocking Optional. Whether this should be a blocking request. Defaults to false.
	 *
	 * @return array|false
	 */
	public function send_stats( $request_data = array(), $blocking = false ) {
		if ( empty( $request_data ) ) {
			// This is what we send by default.
			$request_data = array(
				'site_url' => home_url('/'),
				// We are only interested in data needed to identify the theme and eventually deliver only design assets suitable for it.
				'theme_data' => $this->get_active_theme_data(),
				// We are only interested in data needed to identify the plugin version and eventually deliver design assets suitable for it.
				'site_data' => $this->get_site_data(),
			);
		}

		/**
		 * Filters request data sent to the cloud.
		 *
		 * @param array $request_data
		 * @param object $this @todo This argument is no longer needed and should be removed when Pixelgrade Care doesn't rely on it.
		 */
		$request_data = apply_filters( 'customify_pixelgrade_cloud_request_data', $request_data, $this );

		$request_args = array(
			'method' => self::$externalApiEndpoints['cloud']['stats']['method'],
			'timeout'   => 5,
			'blocking'  => $blocking,
			'body'      => $request_data,
			'sslverify' => false,
		);

		// Make the request and return the response.
		return wp_remote_request( self::$externalApiEndpoints['cloud']['stats']['url'], $request_args );
	}
}

}
