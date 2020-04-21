<?php
/**
 * This is the class that handles the overall logic for design assets.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       1.7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Design_Assets' ) ) {

class Customify_Design_Assets {

	/**
	 * Holds the only instance of this class.
	 * @var null|Customify_Design_Assets
	 * @access protected
	 * @since 1.7.4
	 */
	protected static $_instance = null;

	/**
	 * The current design assets config.
	 * @var     array
	 * @access  public
	 * @since   1.7.4
	 */
	protected $design_assets = null;

	/**
	 * The cloud API object used to communicate with the cloud.
	 * @var     Customify_Cloud_Api
	 * @access  public
	 * @since   1.7.4
	 */
	protected $cloud_api = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.4
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize this module.
	 *
	 * @since 1.7.4
	 */
	public function init() {
		/**
		 * Initialize the Cloud API logic.
		 */
		require_once 'class-customify-cloud-api.php';
		$this->cloud_api = new Customify_Cloud_Api();
	}

	/**
	 * Get the design assets configuration.
	 *
	 * @since 1.7.4
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array
	 */
	public function get( $skip_cache = false ) {
		if ( ! is_null( $this->design_assets ) && false === $skip_cache ) {
			return $this->design_assets;
		}

		$this->design_assets = $this->maybe_fetch( $skip_cache );

		// Determine if we should use the config in the theme root and skip the external config entirely.
		if ( defined('CUSTOMIFY_SM_LOAD_THEME_ROOT_CONFIG') && true === CUSTOMIFY_SM_LOAD_THEME_ROOT_CONFIG ) {
			$this->design_assets = $this->maybe_load_theme_config_from_theme_root( $this->design_assets );
		}

		return apply_filters( 'customify_style_manager_get_design_assets', $this->design_assets );
	}

	/**
	 * Fetch the design assets data from the Pixelgrade Cloud.
	 *
	 * Caches the data for 12 hours. Use local defaults if not available.
	 *
	 * @since 1.7.4
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached data or fetch a new one.
	 *
	 * @return array|false
	 */
	protected function maybe_fetch( $skip_cache = false ) {
		// First try and get the cached data
		$data = get_option( self::get_cache_key() );

		// For performance reasons, we will ONLY fetch remotely when in the WP ADMIN area or via an ADMIN AJAX call, regardless of settings.
		if ( ! is_admin() ) {
			return  $data;
		}

		// We don't force skip the cache for AJAX requests for performance reasons.
		if ( ! wp_doing_ajax() && defined('CUSTOMIFY_SM_ALWAYS_FETCH_DESIGN_ASSETS' ) && true === CUSTOMIFY_SM_ALWAYS_FETCH_DESIGN_ASSETS ) {
			$skip_cache = true;
		}

		$expire_timestamp = false;

		// Only try to get the expire timestamp if we really need to.
		if ( true !== $skip_cache && false !== $data ) {
			// Get the cache data expiration timestamp.
			$expire_timestamp = get_option( self::get_cache_key() . '_timestamp' );
		}

		// The data isn't set, is expired or we were instructed to skip the cache; we need to fetch fresh data.
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {
			// Fetch the design assets from the cloud.
			$fetched_data = $this->cloud_api->fetch_design_assets();
			// Bail in case of failure to retrieve data.
			// We will return the data already available.
			if ( false === $fetched_data || null === $fetched_data ) {
				return $data;
			}

			$data = $fetched_data;

			// Cache the data in an option for 6 hours
			update_option( self::get_cache_key(), $data, true );
			update_option( self::get_cache_key() . '_timestamp', time() + 6 * HOUR_IN_SECONDS, true );
		}

		return apply_filters( 'customify_style_manager_maybe_fetch_design_assets', $data );
	}

	/**
	 * Get the design assets cache key.
	 *
	 * @since 1.7.4
	 *
	 * @return string
	 */
	private static function get_cache_key() {
		return 'customify_style_manager_design_assets';
	}

	public static function invalidate_cache() {
		update_option( self::get_cache_key() . '_timestamp' , time() - 24 * HOUR_IN_SECONDS, true );
	}

	/**
	 * Include the customify "external" config file in the theme root and overwrite the existing theme configs.
	 *
	 * @since 1.7.4
	 *
	 * @param array $design_assets
	 *
	 * @return array
	 */
	protected function maybe_load_theme_config_from_theme_root( $design_assets ) {
		$file_name = 'customify_theme_root.php';

		// First gather details about the current (parent) theme.
		$theme = wp_get_theme( get_template() );
		// Bail if for some strange reason we couldn't find the theme.
		if ( ! $theme->exists() ) {
			return $design_assets;
		}

		$file = trailingslashit( $theme->get_template_directory() ) . $file_name;
		if ( ! file_exists( $file ) ) {
			return $design_assets;
		}

		// We expect to get from the file include a $config variable with the entire Customify (partial) config.
		include $file;

		if ( ! isset( $config ) || ! is_array( $config ) || empty( $config['sections'] ) ) {
			// Alert the developers that things are not alright.
			_doing_it_wrong( __METHOD__, 'The Customify theme root config is not good - the `sections` entry is missing. Please check it! We will not apply it.', null );

			return $design_assets;
		}

		// Construct the pseudo-external theme config.
		// Start with a clean slate.
		$design_assets['theme_configs'] = array();

		$design_assets['theme_configs']['theme_root'] = array(
			'id'            => 1,
			'name'          => $theme->get( 'Name' ),
			'slug'          => $theme->get_stylesheet(),
			'txtd'          => $theme->get( 'TextDomain' ),
			'loose_match'   => true,
			'config'        => $config,
			'created'       => date('Y-m-d H:i:s'),
			'last_modified' => date('Y-m-d H:i:s'),
			'hashid'        => 'theme_root',
		);

		return $design_assets;
	}

	/**
	 * Main Customify_Design_Assets Instance
	 *
	 * Ensures only one instance of Customify_Design_Assets is loaded or can be loaded.
	 *
	 * @since  1.7.4
	 * @static
	 *
	 * @return Customify_Design_Assets Main Customify_Design_Assets instance
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.7.4
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html__( 'You should not do that!', 'customify' ), null );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.7.4
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ),  null );
	}
}

// Handle various cache invalidations, in a proactive manner.
// We need to do the hooking here because by the time the class might be instantiated it might be too late.
add_action( 'customify_invalidate_all_caches', array( 'Customify_Design_Assets', 'invalidate_cache' ), 1 );

}
