<?php
/**
 * Options class to handle all options management.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Provider;

use Pixelgrade\Customify\Utils\ArrayHelpers;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Class Options to handle all options management (including their configuration).
 * WordPress does all the heavy work for caching get_option() data,
 * so we don't have to do that. But we want to minimize cyclomatic complexity
 * of calling a bunch of WP functions, thus we will cache them in a class as well.
 *
 * @since 3.0.0
 */
class Options extends AbstractHookProvider {

	const MINIMAL_DETAILS_CACHE_KEY = 'pixelgrade_customify_options_minimal_details';
	const EXTRA_DETAILS_CACHE_KEY = 'pixelgrade_customify_options_extra_details';
	const DETAILS_CACHE_TIMESTAMP_KEY = 'pixelgrade_customify_options_details_timestamp';
	const CUSTOMIZER_CONFIG_CACHE_KEY = 'pixelgrade_customify_customizer_config';
	const CUSTOMIZER_CONFIG_CACHE_TIMESTAMP_KEY = 'pixelgrade_customify_customizer_config_timestamp';
	const CUSTOMIZER_OPT_NAME_CACHE_KEY = 'pixelgrade_customify_customizer_opt_name';
	const CUSTOMIZER_OPT_NAME_CACHE_TIMESTAMP_KEY = 'pixelgrade_customify_customizer_opt_name_timestamp';

	/**
	 * The cached options with just the minimal details.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	protected array $minimal_details = [];

	/**
	 * The cached full options details.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	protected array $details = [];

	/**
	 * The current option name as defined by the theme.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected string $opt_name = '';

	/**
	 * The cached Customizer config.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private array $customizer_config = [];

	/**
	 * Plugin settings.
	 *
	 * @var PluginSettings
	 */
	protected PluginSettings $plugin_settings;

	/**
	 * Create the options provider.
	 *
	 * @since 3.0.0
	 *
	 * @param PluginSettings  $plugin_settings Plugin settings.
	 */
	public function __construct(
		PluginSettings $plugin_settings
	) {
		$this->plugin_settings = $plugin_settings;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {

		/*
		 * Handle the force clearing of the caches. We clear in a proactive manner.
		 */
		$this->add_action( 'after_switch_theme', 'invalidate_all_caches', 1 );
		$this->add_action( 'upgrader_process_complete', 'invalidate_all_caches', 1 );

		// Whenever we update data from the Customizer, we will invalidate the options details (that include the value).
		// Customize save (publish) used the same changeset save logic, so this filter is fired then also.
		$this->add_filter( 'customize_changeset_save_data', 'filter_invalidate_details_cache', 50, 1 );
	}

	/**
	 * Get an option's value, if there is a value, and return it.
	 * Otherwise, try to get the default parameter or the default from config.
	 *
	 * @since 3.0.0
	 *
	 * @param string     $option_id
	 * @param mixed|null $default        Optional.
	 * @param array|null $option_details Optional.
	 *
	 * @return mixed
	 */
	public function get( string $option_id, $default = null, $option_details = null ) {

		if ( null === $option_details ) {
			// Get the field config.
			$option_details = $this->get_details( $option_id, true );
		}

		// If the development constant CUSTOMIFY_DEV_FORCE_DEFAULTS has been defined we will not retrieve anything from the database
		// Always go with the default
		if ( defined( 'CUSTOMIFY_DEV_FORCE_DEFAULTS' )
		     && true === CUSTOMIFY_DEV_FORCE_DEFAULTS
		     && ! $this->skip_dev_mode_force_defaults( $option_id, $option_details ) ) {

			$value = null;
		} else {

			if ( empty( $option_id ) || empty( $option_details ) || ! is_array( $option_details ) ) {
				$value = null;
			} elseif ( isset( $option_details['value'] ) ) {
				// If we already have the value cached in the option details, we will use that.
				$value = $option_details['value'];
			} else {
				$value = null;

				/*
				 * First determine the setting ID.
				 */
				$setting_id = $this->get_options_key() . '[' . $option_id . ']';
				// If we have been explicitly given a setting ID we will use that.
				if ( ! empty( $option_details['setting_id'] ) ) {
					$setting_id = $option_details['setting_id'];
				}

				/*
				 * Second, try to get the stored value of the setting.
				 */

				// If we have a setting that directly declares it (not deduced like when registering fields in the Customizer)
				// should be saved in the wp_options table, not in theme_mods, we will attempt to fetch it directly, first.
				if ( isset( $option_details['setting_type'] ) && $option_details['setting_type'] === 'option' ) {
					$value = \get_option( $setting_id, null );
				}

				// If we don't have a value, we will grab the setting value from the array of values stored in either
				// a wp_option entry or in the theme_mods.
				// The "save as array" behavior happens even in the case of 'option' setting type if
				// the setting ID is of the form 'rosa_option[some_key]' (aka a multidimensional setting ID).
				if ( null === $value ) {
					if ( $this->plugin_settings->get( 'values_store_mod' ) === 'option' ) {
						// Get the value stored in a option.
						$value = $this->get_wpoptions_value( $option_id, $setting_id );
					} else {
						// Get the value stored in theme_mods.
						$value = $this->get_thememod_value( $option_id, $setting_id );
					}
				}
			}
		}

		// If we have a non-null value, return it.
		if ( $value !== null ) {
			return $value;
		}

		// If we have a non-null default, return it.
		if ( $default !== null ) {
			return $default;
		}

		// Finally, attempt to use the default value set in the config, if available.
		if ( ! empty( $option_details ) && is_array( $option_details ) && isset( $option_details['default'] ) ) {
			return $option_details['default'];
		}

		return null;
	}

	/**
	 * Determine if a certain option exists.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key The option key.
	 *
	 * @return bool
	 */
	public function has_option( string $key ): bool {
		$options_details = $this->get_details_all( true );
		if ( isset( $options_details[ $key ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the Customify configuration (and value, hence "details") of a certain option.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option_id
	 * @param bool   $minimal_details Optional. Whether to return only the minimum amount of details (mainly what is needed on the frontend).
	 *                                The advantage is that these details are cached, thus skipping the customizer_config!
	 * @param bool   $skip_cache      Optional.
	 *
	 * @return array|false The option config or false on failure.
	 */
	public function get_details( string $option_id, $minimal_details = false, $skip_cache = false ) {
		if ( empty( $option_id ) ) {
			return false;
		}

		$options_details = $this->get_details_all( $minimal_details, $skip_cache );
		if ( ! empty( $options_details ) && is_array( $options_details ) && isset( $options_details[ $option_id ] ) ) {
			return $options_details[ $option_id ];
		}

		return false;
	}

	/**
	 * Get the value of a setting ID saved in a wp_options array entry.
	 *
	 * @since 3.0.0
	 *
	 * @param string                 $option_id  This is only the option ID, that may differ from setting ID ( like in `body_font` vs `rosa_opt[body_font]`)
	 * @param string                 $setting_id We will use this to get the Customizer value, when in that context.
	 *
	 * @return mixed|null
	 * @global \WP_Customize_Manager $wp_customize
	 *
	 */
	protected function get_wpoptions_value( string $option_id, string $setting_id ) {
		global $wp_customize;

		if ( empty( $option_id ) || empty( $setting_id ) ) {
			return null;
		}

		if ( ! empty( $wp_customize ) && method_exists( $wp_customize, 'get_setting' ) ) {
			$setting = $wp_customize->get_setting( $setting_id );
			if ( ! empty( $setting ) ) {
				return $setting->value();
			}
		}

		$values = \get_option( $this->get_options_key() );

		if ( ! empty( $values ) && is_array( $values ) && isset( $values[ $option_id ] ) ) {
			return $values[ $option_id ];
		}

		return null;
	}

	/**
	 * Get the value of a certain setting ID saved in the theme mod array.
	 *
	 * @since 3.0.0
	 *
	 * @param string                 $option_id  This is only the option ID, that may differ from setting ID ( like in `body_font` vs `rosa_opt[body_font]`)
	 * @param string                 $setting_id We will use this to get the Customizer value, when in that context.
	 *
	 * @return mixed|null
	 * @global \WP_Customize_Manager $wp_customize
	 *
	 */
	protected function get_thememod_value( string $option_id, string $setting_id ) {
		global $wp_customize;

		if ( empty( $option_id ) || empty( $setting_id ) ) {
			return null;
		}

		if ( ! empty( $wp_customize ) && method_exists( $wp_customize, 'get_setting' ) ) {
			$setting = $wp_customize->get_setting( $setting_id );
			if ( ! empty( $setting ) ) {
				return $setting->value();
			} elseif ( $wp_customize->is_preview() ) {
				// If the setting is not registered (like in asking for the value before wp_loaded), we will read directly from the posted values via the changeset.
				// Not really the best way, but ok.
				$post_values = $wp_customize->unsanitized_post_values();
				if ( array_key_exists( $setting_id, $post_values ) ) {
					$value = $post_values[ $setting_id ];
					// Skip validation and sanitization since it is too early.
					if ( ! is_null( $value ) && ! is_wp_error( $value ) ) {
						return $value;
					}
				}
			}
		}

		$values = \get_theme_mod( $this->get_options_key() );

		if ( ! empty( $values ) && is_array( $values ) && isset( $values[ $option_id ] ) ) {
			return $values[ $option_id ];
		}

		return null;
	}

	/**
	 * Determine if we should NOT enforce the CUSTOMIFY_DEV_FORCE_DEFAULTS behavior on a certain option.
	 *
	 * @since 3.0.0
	 *
	 * @param string     $option_id
	 * @param array|null $option_config Optional.
	 *
	 * @return bool
	 */
	public function skip_dev_mode_force_defaults( string $option_id, $option_config = null ): bool {
		// Preprocess the $option_id.
		if ( false !== strpos( $option_id, '::' ) ) {
			$option_id = substr( $option_id, strpos( $option_id, '::' ) + 2 );
		}
		if ( false !== strpos( $option_id, '[' ) ) {
			$option_id = explode( '[', $option_id );
			$option_id = rtrim( $option_id[1], ']' );
		}

		if ( null === $option_config ) {
			$option_config = $this->get_details( $option_id, true );
		}
		if ( empty( $option_config ) || ! is_array( $option_config ) ) {
			return false;
		}

		// We will skip certain field types that generally don't have a default value.
		if ( ! empty( $option_config['type'] ) ) {
			switch ( $option_config['type'] ) {
				case 'cropped_image':
				case 'cropped_media':
				case 'image':
				case 'media':
				case 'custom_background':
				case 'upload':
					return true;
					break;
				default:
					break;
			}
		}

		return false;
	}

	/**
	 * Invalidate all caches.
	 *
	 * @since 3.0.0
	 */
	public function invalidate_all_caches() {
		$this->invalidate_customizer_config_cache();
		$this->invalidate_details_cache();
		$this->invalidate_customizer_opt_name_cache();
		$this->invalidate_details_cache();

		\do_action( 'customify_invalidate_all_caches' );
	}

	/**
	 * This will clear any instance properties that are used as local cache during a request to avoid
	 * fetching the data from DB on each method call.
	 *
	 * This may be called during a request when something happens that (potentially) invalidates our data mid-request.
	 *
	 * @since 3.0.0
	 */
	protected function clear_locally_cached_data() {
		$this->opt_name = '';

		$this->customizer_config = [];

		$this->minimal_details = [];
		$this->details         = [];
	}

	/**
	 * Get the key under which all options are saved.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $skip_cache Optional. Whether to skip the options cache and regenerate.
	 *                         Defaults to using the cache.
	 *
	 * @return string
	 */
	public function get_options_key( bool $skip_cache = false ): string {
		if ( ! empty( $this->opt_name ) ) {
			return $this->opt_name;
		}

		if ( $this->should_force_skip_cache() ) {
			$skip_cache = true;
		}

		// First try and get the cached data
		$data             = \get_option( self::CUSTOMIZER_OPT_NAME_CACHE_KEY );
		$expire_timestamp = false;

		// Only try to get the expire timestamp if we really need to.
		if ( true !== $skip_cache && false !== $data ) {
			// Get the cache data expiration timestamp.
			$expire_timestamp = \get_option( self::CUSTOMIZER_OPT_NAME_CACHE_TIMESTAMP_KEY );
		}

		// The data isn't set, is expired or we were instructed to skip the cache; we need to regenerate the config.
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {

			$data = (string) $this->get_customizer_config( 'opt-name' );

			if ( true !== $skip_cache ) {
				// Cache the data in an option for 24 hours, but only if we are not supposed to skip the cache entirely.
				\update_option( self::CUSTOMIZER_OPT_NAME_CACHE_KEY, $data, true );
				\update_option( self::CUSTOMIZER_OPT_NAME_CACHE_TIMESTAMP_KEY, time() + 24 * HOUR_IN_SECONDS, true );
			}
		}

		$this->opt_name = $data;

		return $data;
	}

	/**
	 * Invalidate the Customizer options name (opt-name) cache.
	 *
	 * @since 3.0.0
	 */
	public function invalidate_customizer_opt_name_cache() {
		update_option( self::CUSTOMIZER_OPT_NAME_CACHE_TIMESTAMP_KEY, time() - 24 * HOUR_IN_SECONDS, true );

		$this->clear_locally_cached_data();
	}

	/**
	 * Wrapper to invalidate_customizer_opt_name_cache() for hooking into filters.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $value The value is just passed along, not modified.
	 *
	 * @return mixed
	 */
	public function filter_invalidate_customizer_opt_name_cache( $value ) {
		$this->invalidate_customizer_opt_name_cache();

		return $value;
	}

	/**
	 * Get all options' details.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $only_minimal_details Optional. Whether to return only the minimal details.
	 *                                   Defaults to returning all details.
	 * @param bool $skip_cache           Optional. Whether to skip the options cache and regenerate.
	 *                                   Defaults to using the cache.
	 *
	 * @return array
	 */
	public function get_details_all( $only_minimal_details = false, $skip_cache = false ): array {

		// If we already have the data, do as little as possible.
		if ( true === $only_minimal_details && ! empty( $this->minimal_details ) ) {
			return $this->minimal_details;
		}
		if ( ! empty( $this->details ) ) {
			return $this->details;
		}

		if ( $this->should_force_skip_cache() ) {
			$skip_cache = true;
		}

		// We will first look for cached data

		$data = \get_option( self::MINIMAL_DETAILS_CACHE_KEY );
		if ( false !== $data && false === $only_minimal_details ) {
			$this->minimal_details = $data;

			$extra_details_data = \get_option( self::EXTRA_DETAILS_CACHE_KEY );
			if ( is_array( $extra_details_data ) ) {
				$data = $this->details = ArrayHelpers::array_merge_recursive_distinct( $data, $extra_details_data );
			} else {
				// Something is wrong with the extra details and we need to regenerate.
				$this->invalidate_details_cache();
			}
		}

		// For performance reasons, we will use the cached data (even if stale)
		// when a user is not logged in or a user without administrative capabilities is logged in.
		if ( false !== $data && false === $skip_cache && ! current_user_can( 'manage_options' ) ) {
			return $data;
		}

		$expire_timestamp = false;

		// Only try to get the expire timestamp if we really need to.
		if ( true !== $skip_cache && false !== $data ) {
			// Get the cached data expiration timestamp.
			$expire_timestamp = \get_option( self::DETAILS_CACHE_TIMESTAMP_KEY );
		}

		// The data isn't set, is expired or we were instructed to skip the cache; we need to regenerate the config.
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {
			$options_minimal_details = [];
			$options_extra_details   = [];

			$minimal_detail_keys = [
				'type',
				'setting_type',
				'setting_id',
				'default',
				'css',
				'output',
				'value',
				'selector',
				'callback',
				'active_callback',
			];

			$customizer_config = $this->get_customizer_config();

			if ( isset ( $customizer_config['panels'] ) ) {
				foreach ( $customizer_config['panels'] as $pane_id => $panel_settings ) {
					if ( isset( $panel_settings['sections'] ) ) {
						foreach ( $panel_settings['sections'] as $section_id => $section_settings ) {
							if ( isset( $section_settings['options'] ) ) {
								foreach ( $section_settings['options'] as $option_id => $option_config ) {
									if ( is_array( $option_config ) ) {
										foreach ( $option_config as $key => $value ) {
											if ( in_array( $key, $minimal_detail_keys ) ) {
												$options_minimal_details[ $option_id ][ $key ] = $value;
											} else {
												$options_extra_details[ $option_id ][ $key ] = $value;
											}
										}

										$options_minimal_details[ $option_id ]['value'] = $this->get( $option_id, null, $option_config );
									}
								}
							}
						}
					}
				}
			}

			if ( isset ( $customizer_config['sections'] ) ) {
				foreach ( $customizer_config['sections'] as $section_id => $section_settings ) {
					if ( isset( $section_settings['options'] ) ) {
						foreach ( $section_settings['options'] as $option_id => $option_config ) {
							if ( is_array( $option_config ) ) {
								foreach ( $option_config as $key => $value ) {
									if ( in_array( $key, $minimal_detail_keys ) ) {
										$options_minimal_details[ $option_id ][ $key ] = $value;
									} else {
										$options_extra_details[ $option_id ][ $key ] = $value;
									}
								}

								$options_minimal_details[ $option_id ]['value'] = $this->get( $option_id, null, $option_config );
							}
						}
					}
				}
			}

			if ( true !== $skip_cache ) {
				// Cache the data for 24 hours, but only if we are not supposed to skip the cache entirely.
				\update_option( self::MINIMAL_DETAILS_CACHE_KEY, $options_minimal_details, true );
				\update_option( self::EXTRA_DETAILS_CACHE_KEY, $options_extra_details, false ); // we will not autoload extra details for performance reasons.
				\update_option( self::DETAILS_CACHE_TIMESTAMP_KEY, time() + 24 * HOUR_IN_SECONDS, true );
			}

			$data          = $this->minimal_details = $options_minimal_details;
			$this->details = ArrayHelpers::array_merge_recursive_distinct( $options_minimal_details, $options_extra_details );
			if ( false === $only_minimal_details ) {
				$data = $this->details;
			}
		}

		return $data;
	}

	/**
	 * @return bool
	 */
	private function should_force_skip_cache(): bool {
		// If our development constant is defined and true, we will always skip the cache, except for AJAX calls.
		// Other, more specific cases may impose skipping the cache also on AJAX calls.
		if ( ! wp_doing_ajax()
		     && defined( 'CUSTOMIFY_ALWAYS_GENERATE_CUSTOMIZER_CONFIG' )
		     && true === CUSTOMIFY_ALWAYS_GENERATE_CUSTOMIZER_CONFIG ) {
			return true;
		}

		// If we are in the Customizer and the request has a $_POST['customized'] parameter, we will skip the cache
		// since this means that the preview is being reloaded with temporary settings values.
		if ( ! empty( $_POST['customized'] ) ) {
			return true;
		}

		// If we are currently previewing a theme without being actually active, we should not use cached data.
		if ( ! empty( $_REQUEST['theme'] ) || ! empty( $_REQUEST['customize_theme'] ) ) {
			return true;
		}

		/** @var \WP_Customize_Manager $wp_customize */
		global $wp_customize;
		if ( ! empty( $wp_customize )
		     && method_exists( $wp_customize, 'is_theme_active' )
		     && ! $wp_customize->is_theme_active() ) {

			return true;
		}

		return false;
	}

	/**
	 * Invalidate the options details cache.
	 *
	 * @since 3.0.0
	 */
	protected function invalidate_details_cache() {
		\update_option( self::DETAILS_CACHE_TIMESTAMP_KEY, time() - 24 * HOUR_IN_SECONDS, true );

		$this->clear_locally_cached_data();
	}

	/**
	 * Wrapper to invalidate_options_details_cache() for hooking into filters.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $value The value is just passed along, not modified.
	 *
	 * @return mixed
	 */
	protected function filter_invalidate_details_cache( $value ) {
		$this->invalidate_details_cache();

		return $value;
	}

	/**
	 * Get the entire Customizer fields config or a certain entry key.
	 *
	 * @since 3.0.0
	 *
	 * @param bool|string $key
	 *
	 * @return array|mixed|null
	 */
	public function get_customizer_config( $key = false ) {
		$customizer_config = $this->load_customizer_config();

		if ( false !== $key ) {
			if ( is_array( $customizer_config ) && isset( $customizer_config[ $key ] ) ) {
				return $customizer_config[ $key ];
			}

			return null;
		}

		return $customizer_config;
	}

	/**
	 * Load and set the customizer configuration.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or generate a new one.
	 *
	 * @return array
	 */
	protected function load_customizer_config( $skip_cache = false ): array {
		if ( ! empty( $this->customizer_config ) ) {
			return $this->customizer_config;
		}

		if ( $this->should_force_skip_cache() ) {
			$skip_cache = true;
		}

		// First try and get the cached data
		$data = \get_option( self::CUSTOMIZER_CONFIG_CACHE_KEY );

		// For performance reasons, we will use the cached data (even if stale)
		// when a user is not logged in or a user without administrative capabilities is logged in.
		if ( false !== $data && false === $skip_cache && ! current_user_can( \Pixelgrade\Customify\Capabilities::MANAGE_OPTIONS ) ) {
			$this->customizer_config = $data;

			return $data;
		}

		$expire_timestamp = false;

		// Only try to get the expire timestamp if we really need to.
		if ( true !== $skip_cache && false !== $data ) {
			// Get the cache data expiration timestamp.
			$expire_timestamp = \get_option( self::CUSTOMIZER_CONFIG_CACHE_TIMESTAMP_KEY );
		}

		// The data isn't set, is expired or we were instructed to skip the cache; we need to regenerate the config.
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {
			// Allow themes or other plugins to filter the config.
			$data = \apply_filters( 'customify_filter_fields', [] );
			// Make sure that we have an array.
			if ( ! is_array( $data ) ) {
				\_doing_it_wrong( __METHOD__, esc_html__( 'The Customify fields configuration should be an array. Please check the filters that are hooked into \'customify_filter_fields\'.', '__plugin_txtd' ), null );

				$data = [];
			}
			// We apply a second filter for those that wish to work with the final config and not rely on a huge priority number.
			$data = apply_filters( 'customify_final_config', $data );
			// Make sure that we have an array.
			if ( ! is_array( $data ) ) {
				\_doing_it_wrong( __METHOD__, esc_html__( 'The Customify fields configuration should be an array. Please check the filters that are hooked into \'customify_final_config\'.', '__plugin_txtd' ), null );

				$data = [];
			}

			if ( true !== $skip_cache ) {
				// Cache the data in an option for 24 hours, but only if we are not supposed to skip the cache entirely.
				\update_option( self::CUSTOMIZER_CONFIG_CACHE_KEY, $data, false );
				\update_option( self::CUSTOMIZER_CONFIG_CACHE_TIMESTAMP_KEY, time() + 24 * HOUR_IN_SECONDS, true );
			}
		}

		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->customizer_config = $data;

		return $data;
	}

	/**
	 * Invalidate the Customizer fields config cache.
	 *
	 * @since 3.0.0
	 */
	protected function invalidate_customizer_config_cache() {
		\update_option( self::CUSTOMIZER_CONFIG_CACHE_TIMESTAMP_KEY, time() - 24 * HOUR_IN_SECONDS, true );

		$this->clear_locally_cached_data();
	}
}
