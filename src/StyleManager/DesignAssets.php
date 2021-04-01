<?php
/**
 * This is the class that handles the overall logic for design assets.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\StyleManager;

use Pixelgrade\Customify\Client\CloudInterface;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;

/**
 * Provides the design assets logic.
 *
 * @since 3.0.0
 */
class DesignAssets extends AbstractHookProvider {

	const CACHE_KEY = 'customify_style_manager_design_assets';
	const CACHE_TIMESTAMP_KEY = 'customify_style_manager_design_assets_timestamp';

	/**
	 * The current design assets config.
	 * @var     array|null
	 */
	protected ?array $design_assets = null;

	/**
	 * Cloud client.
	 *
	 * @var CloudInterface
	 */
	protected CloudInterface $cloud_client;

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
	 * @param CloudInterface  $cloud_client Cloud client.
	 * @param LoggerInterface $logger       Logger.
	 */
	public function __construct(
		CloudInterface $cloud_client,
		LoggerInterface $logger
	) {
		$this->cloud_client = $cloud_client;
		$this->logger = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		// Handle various cache invalidations, in a proactive manner.
		$this->add_action( 'customify_invalidate_all_caches', 'invalidate_cache', 1 );
	}

	/**
	 * Get the entire design assets data.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array
	 */
	public function get( $skip_cache = false ): array {
		if ( ! is_null( $this->design_assets ) && false === $skip_cache ) {
			return $this->design_assets;
		}

		$this->design_assets = $this->maybe_fetch( $skip_cache );

		// Determine if we should use the config in the theme root and skip the external config entirely.
		if ( defined('CUSTOMIFY_SM_LOAD_THEME_ROOT_CONFIG') && true === CUSTOMIFY_SM_LOAD_THEME_ROOT_CONFIG ) {
			$this->design_assets = $this->maybe_load_theme_config_from_theme_root( $this->design_assets );
		}

		$this->design_assets = apply_filters( 'customify_style_manager_get_design_assets', $this->design_assets );
		if ( ! is_array( $this->design_assets ) ) {
			$this->design_assets = [];
		}

		return $this->design_assets;
	}

	/**
	 * Get a certain design assets entry data.
	 *
	 * @since 3.0.0
	 *
	 * @param string $entry The entry to return the design asset data.
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array|null The entry data. If the entry is not found, null will be returned.
	 */
	public function get_entry( string $entry, $skip_cache = false ): ?array {
		$this->get( $skip_cache );

		if ( isset( $this->design_assets[ $entry ] ) ) {
			return $this->design_assets[ $entry ];
		}

		return null;
	}

	/**
	 * Fetch the design assets data from the Pixelgrade Cloud.
	 *
	 * Caches the data for 12 hours. Use local defaults if not available.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached data or fetch a new one.
	 *
	 * @return array|false
	 */
	protected function maybe_fetch( $skip_cache = false ) {
		// First try and get the cached data
		$data = get_option( self::CACHE_KEY );

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
			$expire_timestamp = get_option( self::CACHE_TIMESTAMP_KEY );
		}

		// The data isn't set, is expired or we were instructed to skip the cache; we need to fetch fresh data.
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {
			// Fetch the design assets from the cloud.
			$fetched_data = $this->cloud_client->fetch_design_assets();
			// Bail in case of failure to retrieve data.
			// We will return the data already available.
			if ( false === $fetched_data || null === $fetched_data ) {
				return $data;
			}

			$data = $fetched_data;

			// Cache the data in an option for 6 hours
			update_option( self::CACHE_KEY, $data, true );
			update_option( self::CACHE_TIMESTAMP_KEY, time() + 6 * HOUR_IN_SECONDS, true );
		}

		return apply_filters( 'customify_style_manager_maybe_fetch_design_assets', $data );
	}

	protected static function invalidate_cache() {
		update_option( self::CACHE_TIMESTAMP_KEY , time() - 24 * HOUR_IN_SECONDS, true );
	}

	/**
	 * Include the customify "external" config file in the theme root and overwrite the existing theme configs.
	 *
	 * @since 3.0.0
	 *
	 * @param array $design_assets
	 *
	 * @return array
	 */
	protected function maybe_load_theme_config_from_theme_root( array $design_assets ): array {
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
			_doing_it_wrong( __METHOD__, 'The Customify theme root config is not good - the `sections` entry is missing. Please check it! For now, we will ignore it.', null );

			return $design_assets;
		}

		// Construct the pseudo-external theme config.

		// Start with a clean slate.
		$design_assets['theme_configs'] = [];

		$design_assets['theme_configs']['theme_root'] = [
			'id'            => 1,
			'name'          => $theme->get( 'Name' ),
			'slug'          => $theme->get_stylesheet(),
			'txtd'          => $theme->get( 'TextDomain' ),
			'loose_match'   => true,
			'config'        => $config,
			'created'       => date('Y-m-d H:i:s'),
			'last_modified' => date('Y-m-d H:i:s'),
			'hashid'        => 'theme_root',
		];

		return $design_assets;
	}
}
