<?php
/**
 * Options class to handle all options management.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package PixelgradeLT
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify;

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

	protected array $options_minimal_details = [];
	protected array $options_details = [];

	protected string $opt_name = '';

	private array $customizer_config = [];

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
		$this->add_filter( 'customize_changeset_save_data', 'filter_invalidate_options_details_cache', 50, 1 );
	}

	/**
	 * Get the Customify configuration (and value, hence "details") of a certain option.
	 *
	 * @param string $option_id
	 * @param bool   $minimal_details Optional. Whether to return only the minimum amount of details (mainly what is needed on the frontend).
	 *                              The advantage is that these details are cached, thus skipping the customizer_config!
	 * @param bool   $skip_cache Optional.
	 *
	 * @return array|false The option config or false on failure.
	 */
	public function get_option_details( string $option_id, $minimal_details = false, $skip_cache = false ) {
		if ( empty( $option_id ) ) {
			return false;
		}

		$options_details = $this->get_options_details( $minimal_details, $skip_cache );
		if ( ! empty( $options_details ) && is_array( $options_details ) && isset( $options_details[ $option_id ] ) ) {
			return $options_details[ $option_id ];
		}

		return false;
	}

	/**
	 * This is just a wrapper for get_options_details() for backwards compatibility.
	 *
	 * @param bool $only_minimal_details
	 * @param bool $skip_cache
	 *
	 * @return array|mixed|void
	 */
	public function get_options_configs( $only_minimal_details = false, $skip_cache = false ) {
		return $this->get_options_details( $only_minimal_details, $skip_cache );
	}

	/**
	 * Get the value of a setting ID saved in a wp_options array entry.
	 *
	 * @param string $option_id  This is only the option ID, that may differ from setting ID ( like in `body_font` vs `rosa_opt[body_font]`)
	 * @param string $setting_id We will use this to get the Customizer value, when in that context.
	 *
	 * @return mixed|null
	 */
	protected function get_option_mod_value( string $option_id, string $setting_id ) {
		global $wp_customize;

		if ( empty( $option_id ) || empty( $setting_id ) ) {
			return null;
		}

		if ( ! empty( $wp_customize ) && method_exists( $wp_customize, 'get_setting' ) ) {
			$setting    = $wp_customize->get_setting( $setting_id );
			if ( ! empty( $setting ) ) {
				return $setting->value();
			}
		}

		$values = get_option( $this->get_options_key() );

		if ( ! empty( $values ) && is_array( $values ) && isset( $values[ $option_id ] ) ) {
			return $values[ $option_id ];
		}

		return null;
	}

	/**
	 * Get the value of a certain setting ID saved in the theme mod array.
	 *
	 * @param string $option_id  This is only the option ID, that may differ from setting ID ( like in `body_font` vs `rosa_opt[body_font]`)
	 * @param string $setting_id We will use this to get the Customizer value, when in that context.
	 *
	 * @return mixed|null
	 */
	protected function get_theme_mod_value( string $option_id, string $setting_id ) {
		global $wp_customize;

		if ( empty( $option_id ) || empty( $setting_id ) ) {
			return null;
		}

		if ( ! empty( $wp_customize ) && method_exists( $wp_customize, 'get_setting' ) ) {
			$setting    = $wp_customize->get_setting( $setting_id );
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

		$values = get_theme_mod( $this->get_options_key() );

		if ( ! empty( $values ) && is_array( $values ) && isset( $values[ $option_id ] ) ) {
			return $values[ $option_id ];
		}

		return null;
	}

	/**
	 * A public function to get an option's value.
	 * If there is a value and return it.
	 * Otherwise try to get the default parameter or the default from config.
	 *
	 * @param string $option_id
	 * @param mixed $default        Optional.
	 * @param array $option_details Optional.
	 *
	 * @return bool|null|string
	 */
	public function get_option( string $option_id, $default = null, $option_details = null ) {

		if ( null === $option_details ) {
			// Get the field config.
			$option_details = $this->get_option_details( $option_id, true );
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
					$value = get_option( $setting_id, null );
				}

				// If we don't have a value, we will grab the setting value from the array of values stored in either
				// a wp_option entry or in the theme_mods.
				// The "save as array" behavior happens even in the case of 'option' setting type if
				// the setting ID is of the form 'rosa_option[some_key]' (aka a multidimensional setting ID).
				if ( null === $value ) {
					if ( ! empty( PixCustomifyPlugin()->settings ) && PixCustomifyPlugin()->settings->get_plugin_setting( 'values_store_mod' ) === 'option' ) {
						// Get the value stored in a option.
						$value = $this->get_option_mod_value( $option_id, $setting_id );
					} else {
						// Get the value stored in theme_mods.
						$value = $this->get_theme_mod_value( $option_id, $setting_id );
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
	 * Determine if we should NOT enforce the CUSTOMIFY_DEV_FORCE_DEFAULTS behavior on a certain option.
	 *
	 * @param string $option_id
	 * @param array  $option_config Optional.
	 *
	 * @return bool
	 */
	public function skip_dev_mode_force_defaults( string $option_id, $option_config = null ) {
		// Preprocess the $option_id.
		if ( false !== strpos( $option_id, '::' ) ) {
			$option_id = substr( $option_id, strpos( $option_id, '::' ) + 2 );
		}
		if ( false !== strpos( $option_id, '[' ) ) {
			$option_id = explode( '[', $option_id );
			$option_id = rtrim( $option_id[1], ']' );
		}

		if ( null === $option_config ) {
			$option_config = $this->get_option_details( $option_id, true );
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
	 * @since 2.6.0
	 */
	public function invalidate_all_caches() {
		$this->invalidate_customizer_config_cache();
		$this->invalidate_options_details_cache();
		$this->invalidate_customizer_opt_name_cache();
		$this->invalidate_options_details_cache();

		do_action( 'customify_invalidate_all_caches' );
	}

	/**
	 * This will clear any instance properties that are used as local cache during a request to avoid
	 * fetching the data from DB on each method call.
	 *
	 * This may be called during a request when something happens that (potentially) invalidates our data mid-request.
	 */
	public function clear_locally_cached_data() {
		$this->opt_name = '';

		$this->customizer_config = [];

		$this->options_minimal_details = [];
		$this->options_details = [];
	}

	public function get_options_key( $skip_cache = false ) {
		if ( ! empty( $this->opt_name ) ) {
			return $this->opt_name;
		}

		if ( $this->should_force_skip_cache() ) {
			$skip_cache = true;
		}

		// First try and get the cached data
		$data = get_option( $this->get_customizer_opt_name_cache_key() );
		$expire_timestamp = false;

		// Only try to get the expire timestamp if we really need to.
		if ( true !== $skip_cache && false !== $data ) {
			// Get the cache data expiration timestamp.
			$expire_timestamp = get_option( $this->get_customizer_opt_name_cache_key() . '_timestamp' );
		}

		// The data isn't set, is expired or we were instructed to skip the cache; we need to regenerate the config.
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {

			$data = $this->get_customizer_config( 'opt-name' );

			if ( true !== $skip_cache ) {
				// Cache the data in an option for 24 hours, but only if we are not supposed to skip the cache entirely.
				update_option( $this->get_customizer_opt_name_cache_key(), $data, true );
				update_option( $this->get_customizer_opt_name_cache_key() . '_timestamp', time() + 24 * HOUR_IN_SECONDS, true );
			}
		}

		$this->opt_name = $data;
		return $data;
	}

	private function get_customizer_opt_name_cache_key() {
		return 'customify_customizer_opt_name';
	}

	public function invalidate_customizer_opt_name_cache() {
		update_option( $this->get_customizer_opt_name_cache_key() . '_timestamp' , time() - 24 * HOUR_IN_SECONDS, true );

		$this->clear_locally_cached_data();
	}

	public function filter_invalidate_customizer_opt_name_cache( $value ) {
		$this->invalidate_customizer_opt_name_cache();

		return $value;
	}


	public function get_options_details( $only_minimal_details = false, $skip_cache = false ) {

		// If we already have the data, do as little as possible.
		if ( true === $only_minimal_details && ! empty( $this->options_minimal_details ) ) {
			return $this->options_minimal_details;
		}
		if ( ! empty( $this->options_details ) ) {
			return $this->options_details;
		}

		if ( $this->should_force_skip_cache() ) {
			$skip_cache = true;
		}

		// We will first look for cached data

		$data = $this->options_minimal_details = get_option( $this->get_options_minimal_details_cache_key() );
		if ( false !== $data && false === $only_minimal_details ) {
			$extra_details_data = get_option( $this->get_options_extra_details_cache_key() );
			if ( is_array( $extra_details_data ) ) {
				$data = $this->options_details = ArrayHelpers::array_merge_recursive_distinct( $data, $extra_details_data );
			} else {
				// Something is wrong with the extra details and we need to regenerate.
				$this->invalidate_options_details_cache();
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
			$expire_timestamp = get_option( $this->get_options_details_cache_timestamp_key() );
		}

		// The data isn't set, is expired or we were instructed to skip the cache; we need to regenerate the config.
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {
			$options_minimal_details = array();
			$options_extra_details = array();

			$minimal_detail_keys = array(
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
			);

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

										$options_minimal_details[ $option_id ]['value'] = $this->get_option( $option_id, null, $option_config );
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

								$options_minimal_details[ $option_id ]['value'] = $this->get_option( $option_id, null, $option_config );
							}
						}
					}
				}
			}

			if ( true !== $skip_cache ) {
				// Cache the data for 24 hours, but only if we are not supposed to skip the cache entirely.
				update_option( $this->get_options_minimal_details_cache_key(), $options_minimal_details, true );
				update_option( $this->get_options_extra_details_cache_key(), $options_extra_details, false ); // we will not autoload extra details for performance reasons.
				update_option( $this->get_options_details_cache_timestamp_key(), time() + 24 * HOUR_IN_SECONDS, true );
			}

			$data = $this->options_minimal_details = $options_minimal_details;
			$this->options_details = ArrayHelpers::array_merge_recursive_distinct( $options_minimal_details, $options_extra_details );
			if ( false === $only_minimal_details ) {
				$data = $this->options_details;
			}
		}

		return $data;
	}

	private function should_force_skip_cache(): bool {
		// If our development constant is defined and true, we will always skip the cache, except for AJAX calls.
		// Other, more specific cases may impose skipping the cache also on AJAX calls.
		if ( ! wp_doing_ajax()
		     && defined('CUSTOMIFY_ALWAYS_GENERATE_CUSTOMIZER_CONFIG' )
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

	private function get_options_minimal_details_cache_key(): string {
		return 'customify_options_minimal_details';
	}

	private function get_options_extra_details_cache_key(): string {
		return 'customify_options_extra_details';
	}

	private function get_options_details_cache_timestamp_key(): string {
		return 'customify_options_details_timestamp';
	}

	public function invalidate_options_details_cache() {
		update_option( $this->get_options_details_cache_timestamp_key(), time() - 24 * HOUR_IN_SECONDS, true );

		$this->clear_locally_cached_data();
	}

	public function filter_invalidate_options_details_cache( $value ) {
		$this->invalidate_options_details_cache();

		return $value;
	}

	public function has_option( $option ): bool {

		$options_details  = $this->get_options_details(true);
		if ( isset( $options_details[ $option ] ) ) {
			return true;
		}

		return false;
	}




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
	 * Set the customizer configuration.
	 *
	 * @since 2.2.1
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or generate a new one.
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
		$data = get_option( $this->get_customizer_config_cache_key() );

		// For performance reasons, we will use the cached data (even if stale)
		// when a user is not logged in or a user without administrative capabilities is logged in.
		if ( false !== $data && false === $skip_cache && ! current_user_can( 'manage_options' ) ) {
			$this->customizer_config = $data;
			return $data;
		}

		$expire_timestamp = false;

		// Only try to get the expire timestamp if we really need to.
		if ( true !== $skip_cache && false !== $data ) {
			// Get the cache data expiration timestamp.
			$expire_timestamp = get_option( $this->get_customizer_config_cache_key() . '_timestamp' );
		}

		// The data isn't set, is expired or we were instructed to skip the cache; we need to regenerate the config.
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {
			// Allow themes or other plugins to filter the config.
			$data = apply_filters( 'customify_filter_fields', array() );
			// We apply a second filter for those that wish to work with the final config and not rely on a a huge priority number.
			$data = apply_filters( 'customify_final_config', $data );

			if ( true !== $skip_cache ) {
				// Cache the data in an option for 24 hours, but only if we are not supposed to skip the cache entirely.
				update_option( $this->get_customizer_config_cache_key(), $data, false );
				update_option( $this->get_customizer_config_cache_key() . '_timestamp', time() + 24 * HOUR_IN_SECONDS, true );
			}
		}

		$this->customizer_config = $data;

		return $data;
	}

	private function get_customizer_config_cache_key(): string {
		return 'customify_customizer_config';
	}

	public function invalidate_customizer_config_cache() {
		update_option( $this->get_customizer_config_cache_key() . '_timestamp' , time() - 24 * HOUR_IN_SECONDS, true );

		$this->clear_locally_cached_data();
	}

	/**
	 * Invalidate the customizer config cache, when hooked via a filter (just pass through the value).
	 *
	 * @since 2.4.0
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function filter_invalidate_customizer_config_cache( $value ) {
		$this->invalidate_customizer_config_cache();

		return $value;
	}
}
