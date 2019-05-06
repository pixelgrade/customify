<?php
/**
 * PixCustomify.
 * @package   PixCustomify
 * @author    Pixelgrade <contact@pixelgrade.com>
 * @license   GPL-2.0+
 * @link      https://pixelgrade.com
 * @copyright 2014-2018 Pixelgrade
 */

/**
 * Plugin class.
 * @package   PixCustomify
 * @author    Pixelgrade <contact@pixelgrade.com>
 */
class PixCustomifyPlugin {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 * @since   1.5.0
	 * @const   string
	 */
	protected $_version;
	/**
	 * Unique identifier for your plugin.
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_slug = 'customify';

	/**
	 * Instance of this class.
	 * @since    1.5.0
	 * @var      object
	 */
	protected static $_instance = null;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.5.0
	 */
	public $file;

	/**
	 * Settings class object.
	 * @var Customify_Settings
	 * @access  public
	 * @since   2.4.0
	 */
	public $settings = null;

	/**
	 * Customizer class object.
	 * @var Customify_Customizer
	 * @access  public
	 * @since   2.4.0
	 */
	public $customizer = null;

	/**
	 * Style Manager class object.
	 * @var Customify_Style_Manager
	 * @access  public
	 * @since   1.0.0
	 */
	public $style_manager = null;

	/**
	 * Gutenberg class object.
	 * @var Customify_Gutenberg
	 * @access  public
	 * @since   2.2.0
	 */
	public $gutenberg = null;

	protected $options_minimal_details = array();
	protected $options_details = array();

	protected $opt_name;

	protected $jetpack_default_modules = array();
	protected $jetpack_blocked_modules = array();
	protected $jetpack_sharing_default_options = array();

	private $customizer_config = array();

	/**
	 * Minimal Required PHP Version
	 * @var string
	 * @access  private
	 * @since   1.5.0
	 */
	private $minimalRequiredPhpVersion = '5.2';

	protected function __construct( $file, $version = '1.0.0' ) {
		//the main plugin file (the one that loads all this)
		$this->file = $file;
		//the current plugin version
		$this->_version = $version;

		if ( $this->php_version_check() ) {
			// Only load and run the init function if we know PHP version can parse it.
			$this->init();
		}
	}

	/**
	 * Initialize plugin
	 */
	private function init() {
		// We don't want to put extra load on the heartbeat AJAX request.
		if ( wp_doing_ajax() && isset( $_REQUEST['action'] ) && 'heartbeat' === $_REQUEST['action'] ) {
			return;
		}

		// Handle the install and uninstall logic
		register_activation_hook( $this->get_file(), array( 'PixCustomifyPlugin', 'install' ) );

		/* Initialize the plugin settings logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-settings.php' );
		if ( is_null( $this->settings ) ) {
			$this->settings = Customify_Settings::instance( $this->get_file(), $this->get_slug(), $this->get_version() );
		}

		/* Initialize the Customizer logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-customizer.php' );
		if ( is_null( $this->customizer ) ) {
			$this->customizer = Customify_Customizer::instance();
		}

		/* Initialize the Style Manager logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-style-manager.php' );
		if ( is_null( $this->style_manager ) ) {
			$this->style_manager = Customify_Style_Manager::instance();
		}

		/* Initialize the Gutenberg logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-gutenberg.php' );
		if ( is_null( $this->gutenberg ) ) {
			$this->gutenberg = Customify_Gutenberg::instance();
		}

		// Register all the needed hooks
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	function register_hooks() {

		/*
		 * Load plugin text domain
		 */
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		/*
		 * Load the upgrade logic.
		 */
		add_action( 'admin_init', array( $this, 'upgrade' ) );

		/*
		 * Handle the force clearing of the caches. We clear in a proactive manner.
		 */
		add_action( 'activated_plugin', array( $this, 'invalidate_customizer_config_cache' ), 1 );
		add_action( 'activated_plugin', array( $this, 'invalidate_options_details_cache' ), 1 );
		add_action( 'activated_plugin', array( $this, 'invalidate_customizer_opt_name_cache' ), 1 );

		add_action( 'deactivated_plugin', array( $this, 'invalidate_customizer_config_cache' ), 1 );
		add_action( 'deactivated_plugin', array( $this, 'invalidate_options_details_cache' ), 1 );
		add_action( 'deactivated_plugin', array( $this, 'invalidate_customizer_opt_name_cache' ), 1 );

		add_action( 'switch_theme', array( $this, 'invalidate_customizer_config_cache' ), 1 );
		add_action( 'switch_theme', array( $this, 'invalidate_options_details_cache' ), 1 );
		add_action( 'switch_theme', array( $this, 'invalidate_customizer_opt_name_cache' ), 1 );

		add_action( 'upgrader_process_complete', array( $this, 'invalidate_customizer_config_cache' ), 1 );
		add_action( 'upgrader_process_complete', array( $this, 'invalidate_options_details_cache' ), 1 );
		add_action( 'upgrader_process_complete', array( $this, 'invalidate_customizer_opt_name_cache' ), 1 );

		// Whenever we update data from the Customizer, we will invalidate the options details (that include the value).
		add_filter( 'customize_changeset_save_data', array( $this, 'filter_invalidate_options_details_cache' ), 50, 1 );

		// We also want to invalidate the cache whenever the Pixelgrade Care license is updated since it may unlock new features
		// and so unlock new Customify options.
		add_filter( 'pre_set_theme_mod_pixcare_license', array( $this, 'filter_invalidate_customizer_config_cache' ), 10, 1 );
		add_filter( 'pre_set_theme_mod_pixcare_license', array( $this, 'filter_invalidate_options_details_cache' ), 10, 1 );
		add_filter( 'pre_set_theme_mod_pixcare_license', array( $this, 'filter_invalidate_customizer_opt_name_cache' ), 10, 1 );
	}

	/**
	 * Handle the logic to upgrade between versions. It will run only one per version change.
	 */
	public function upgrade() {
		$customify_dbversion = get_option( 'customify_dbversion', '0.0.1' );
		if ( $this->get_version() === $customify_dbversion ) {
			return;
		}

		// For versions, previous of version 2.0.0 (the Color Palettes v2.0 release).
		if ( version_compare( $customify_dbversion, '2.0.0', '<' ) ) {
			// Delete the option holding the fact that the user offered feedback.
			delete_option( 'style_manager_user_feedback_provided' );
		}

		// Put the current version in the database.
		update_option( 'customify_dbversion', $this->get_version(), true );
	}

	public function get_options_key( $skip_cache = false ) {
		if ( ! empty( $this->opt_name ) ) {
			return $this->opt_name;
		}

		// First try and get the cached data
		$data = get_option( $this->get_customizer_opt_name_cache_key() );

		// Get the cache data expiration timestamp.
		$expire_timestamp = get_option( $this->get_customizer_opt_name_cache_key() . '_timestamp' );

		// The data isn't set, is expired or we were instructed to skip the cache; we need to regenerate the config.
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {

			$data = $this->get_customizer_config( 'opt-name' );

			// Cache the data in an option for 6 hours
			update_option( $this->get_customizer_opt_name_cache_key() , $data, true );
			update_option( $this->get_customizer_opt_name_cache_key() . '_timestamp' , time() + 6 * HOUR_IN_SECONDS, true );
		}

		$this->opt_name = $data;
		return $data;
	}

	private function get_customizer_opt_name_cache_key() {
		return 'customify_customizer_opt_name';
	}

	public function invalidate_customizer_opt_name_cache() {
		update_option( $this->get_customizer_opt_name_cache_key() . '_timestamp' , time() - 24 * HOUR_IN_SECONDS, true );
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

		// We will first look for cached data

		// We don't force skip the cache for AJAX requests for performance reasons.
		if ( defined('CUSTOMIFY_ALWAYS_GENERATE_CUSTOMIZER_CONFIG' )
		     && true === CUSTOMIFY_ALWAYS_GENERATE_CUSTOMIZER_CONFIG ) {
			$skip_cache = true;
		}

		$data = $this->options_minimal_details = get_option( $this->get_options_minimal_details_cache_key() );
		if ( false !== $data && false === $only_minimal_details ) {
			$extra_details_data = get_option( $this->get_options_extra_details_cache_key() );
			if ( is_array( $extra_details_data ) ) {
				$data = $this->options_details = Customify_Array::array_merge_recursive_distinct( $data, $extra_details_data );
			} else {
				// Something is wrong with the extra details and we need to regenerate.
				$skip_cache = true;
			}
		}

		// For performance reasons, we will use the cached data (even if stale)
		// when a user is not logged in or a user without administrative capabilities is logged in.
		if ( false !== $data && false === $skip_cache && ! current_user_can( 'manage_options' ) ) {
			return $data;
		}

		// Get the cached data expiration timestamp.
		$expire_timestamp = get_option( $this->get_options_details_cache_timestamp_key() );

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
				'load_all_weights',
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

			// Cache the data for 6 hours
			update_option( $this->get_options_minimal_details_cache_key() , $options_minimal_details, true );
			update_option( $this->get_options_extra_details_cache_key() , $options_extra_details, false ); // we will not autoload extra details for performance reasons.
			update_option( $this->get_options_details_cache_timestamp_key(), time() + 6 * HOUR_IN_SECONDS, true );

			$data = $this->options_minimal_details = $options_minimal_details;
			$this->options_details = Customify_Array::array_merge_recursive_distinct( $options_minimal_details, $options_extra_details );
			if ( false === $only_minimal_details ) {
				$data = $this->options_details;
			}
		}

		return $data;
	}

	private function get_options_minimal_details_cache_key() {
		return 'customify_options_minimal_details';
	}

	private function get_options_extra_details_cache_key() {
		return 'customify_options_extra_details';
	}

	private function get_options_details_cache_timestamp_key() {
		return 'customify_options_details_timestamp';
	}

	public function invalidate_options_details_cache() {
		update_option( $this->get_options_details_cache_timestamp_key(), time() - 24 * HOUR_IN_SECONDS, true );
	}

	public function filter_invalidate_options_details_cache( $value ) {
		$this->invalidate_options_details_cache();

		return $value;
	}

	public function has_option( $option ) {

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
	protected function load_customizer_config( $skip_cache = false ) {
		if ( ! empty( $this->customizer_config ) && true !== $skip_cache ) {
			return $this->customizer_config;
		}

		// First try and get the cached data
		$data = get_option( $this->get_customizer_config_cache_key() );

		// We don't force skip the cache for AJAX requests for performance reasons.
		if ( ! wp_doing_ajax()
		     && defined('CUSTOMIFY_ALWAYS_GENERATE_CUSTOMIZER_CONFIG' )
		     && true === CUSTOMIFY_ALWAYS_GENERATE_CUSTOMIZER_CONFIG ) {
			$skip_cache = true;
		}

		// For performance reasons, we will use the cached data (even if stale)
		// when a user is not logged in or a user without administrative capabilities is logged in.
		if ( false !== $data && false === $skip_cache && ! current_user_can( 'manage_options' ) ) {
			$this->customizer_config = $data;
			return $data;
		}

		// Get the cache data expiration timestamp.
		$expire_timestamp = get_option( $this->get_customizer_config_cache_key() . '_timestamp' );

		// The data isn't set, is expired or we were instructed to skip the cache; we need to regenerate the config.
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {
			// Allow themes or other plugins to filter the config.
			$data = apply_filters( 'customify_filter_fields', array() );
			// We apply a second filter for those that wish to work with the final config and not rely on a a huge priority number.
			$data = apply_filters( 'customify_final_config', $data );

			// Cache the data in an option for 6 hours
			update_option( $this->get_customizer_config_cache_key() , $data, false );
			update_option( $this->get_customizer_config_cache_key() . '_timestamp' , time() + 6 * HOUR_IN_SECONDS, true );
		}

		$this->customizer_config = $data;
		return $data;
	}

	private function get_customizer_config_cache_key() {
		return 'customify_customizer_config';
	}

	public function invalidate_customizer_config_cache() {
		update_option( $this->get_customizer_config_cache_key() . '_timestamp' , time() - 24 * HOUR_IN_SECONDS, true );
	}

	/**
	 * Invalidate the customizer config cache, when hooked via a filter.
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

	/**
	 * Get the Customify configuration (and value, hence "details") of a certain option.
	 *
	 * @param string $option_id
	 * @param bool $minimal_details Optional. Whether to return only the minimum amount of details (mainly what is needed on the frontend).
	 *                              The advantage is that these details are cached, thus skipping the customizer_config!
	 * @param bool $skip_cache Optional.
	 *
	 * @return array|false The option config or false on failure.
	 */
	public function get_option_details( $option_id, $minimal_details = false, $skip_cache = false ) {
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

	protected function get_theme_mod_value( $setting_id ) {
		global $wp_customize;

		if ( empty( $setting_id ) ) {
			return null;
		}

		if ( ! empty( $wp_customize ) && method_exists( $wp_customize, 'get_setting' ) ) {
			$setting    = $wp_customize->get_setting( $setting_id );
			if ( ! empty( $setting ) ) {
				return $setting->value();
			}
		}

		$values = get_theme_mod( $this->get_options_key() );

		if ( ! empty( $values ) && is_array( $values ) && isset( $values[ $setting_id ] ) ) {
			return $values[ $setting_id ];
		}

		return null;
	}

	/**
	 * A public function to get an option's value.
	 * If there is a value and return it.
	 * Otherwise try to get the default parameter or the default from config.
	 *
	 * @param $option_id
	 * @param mixed $default        Optional.
	 * @param array $option_details Optional.
	 *
	 * @return bool|null|string
	 */
	public function get_option( $option_id, $default = null, $option_details = null ) {

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
				$setting_id = $this->get_options_key() . '[' . $option_id . ']';
				// If we have been explicitly given a setting ID we will use that
				if ( ! empty( $option_details['setting_id'] ) ) {
					$setting_id = $option_details['setting_id'];
				}

				if ( isset( $option_details['setting_type'] ) && $option_details['setting_type'] === 'option' ) {
					// We have a setting that is saved in the wp_options table, not in theme_mods.
					// We will fetch it directly.
					$value = get_option( $setting_id, null );
				} else {
					// Get the value stores in theme_mods.
					$value = $this->get_theme_mod_value( $option_id );
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
	 * @param array $option_config Optional.
	 *
	 * @return bool
	 */
	public function skip_dev_mode_force_defaults( $option_id, $option_config = null ) {
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

	public function get_version() {
		return $this->_version;
	}

	public function get_slug() {
		return $this->plugin_slug;
	}

	public function get_file() {
		return $this->file;
	}

	public function get_base_path() {
		return plugin_dir_path( $this->file );
	}

	/**
	 * Load the plugin text domain for translation.
	 * @since    1.0.0
	 */
	function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		load_plugin_textdomain( $domain, false, basename( dirname( $this->file ) ) . '/languages/' );
	}

	/**
	 * Checks whether an array is associative or not
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	public function is_assoc( $array ) {

		if ( ! is_array( $array ) ) {
			return false;
		}

		// Keys of the array
		$keys = array_keys( $array );

		// If the array keys of the keys match the keys, then the array must
		// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
		return array_keys( $keys ) !== $keys;
	}

	/**
	 * Does the same thing the JS encodeURIComponent() does
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public static function encodeURIComponent( $str ) {
		//if we get an array we just let it be
		if ( is_string( $str ) ) {
			$revert = array( '%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')' );
			$str = strtr( rawurlencode( $str ), $revert );
		}

		return $str;
	}

	/**
	 * Does the same thing the JS decodeURIComponent() does
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public static function decodeURIComponent( $str ) {
		// If we get an array we just let it be
		if ( is_string( $str ) ) {
			$revert = array( '!' => '%21', '*' => '%2A', "'" => '%27', '(' => '%28', ')' => '%29' );
			$str    = rawurldecode( strtr( $str, $revert ) );
		}

		return $str;
	}

	/**
	 * PHP version check
	 */
	protected function php_version_check() {

		if ( version_compare( phpversion(), $this->minimalRequiredPhpVersion ) < 0 ) {
			add_action( 'admin_notices', array( $this, 'notice_php_version_wrong' ) );

			return false;
		}

		return true;
	}

	/*
	 * Install everything needed
	 */
	static public function install() {
		$config = Customify_Settings::get_plugin_config();

		$defaults = array(

			# Hidden fields
			'settings_saved_once'                   => '0',
			# General
			'values_store_mod'                => 'theme_mod',

			'typography' => true,
			'typography_standard_fonts' => true,
			'typography_google_fonts' => true,
			'typography_group_google_fonts' => true,
			'disable_default_sections' => array(),
			'disable_customify_sections' => array(),
			'enable_reset_buttons' => false,
			'enable_editor_style' => true,
			'style_resources_location' => 'wp_head'
		);

		$current_data = get_option( $config['settings-key'] );

		if ( $current_data === false ) {
			add_option( $config['settings-key'], $defaults );
		} elseif ( count( array_diff_key( $defaults, $current_data ) ) != 0)  {
			$plugin_data = array_merge( $defaults, $current_data );
			update_option( $config['settings-key'], $plugin_data );
		}
	}

	/**
	 * Main PixCustomifyPlugin Instance
	 *
	 * Ensures only one instance of PixCustomifyPlugin is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @param string $file File.
	 * @param string $version Version.
	 *
	 * @see    PixCustomifyPlugin()
	 * @return PixCustomifyPlugin Main PixCustomifyPlugin instance
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.5.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ), null );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.5.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ), null );
	} // End __wakeup ()
}
