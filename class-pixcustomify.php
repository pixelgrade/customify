<?php
/**
 * PixCustomify.
 * @package   PixCustomify
 * @author    Pixelgrade <contact@pixelgrade.com>
 * @license   GPL-2.0+
 * @link      https://pixelgrade.com
 * @copyright 2014-2017 Pixelgrade
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
	 * Slug of the plugin screen.
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	public $display_admin_menu = false;

	private $config;

	private $customizer_config;

	public $plugin_settings;

	protected $localized = array();

	protected $current_values = array();

	protected $options_list = array();

	protected $media_queries = array();

	protected $opt_name;

	protected $typo_settings;

	protected $google_fonts = null;

	protected $theme_fonts = null;

	// these properties will get 'px' as a default unit
	protected static $pixel_dependent_css_properties = array(
		'width',
		'max-width',
		'min-width',

		'height',
		'max-height',
		'min-height',

		'padding',
		'padding-left',
		'padding-right',
		'padding-top',
		'padding-bottom',

		'margin',
		'margin-right',
		'margin-left',
		'margin-top',
		'margin-bottom',

		'right',
		'left',
		'top',
		'bottom',

		'font-size',
		'letter-spacing',

		'border-size',
		'border-width',
		'border-bottom-width',
		'border-left-width',
		'border-right-width',
		'border-top-width'
	);

	protected $jetpack_default_modules = array();
	protected $jetpack_blocked_modules = array();
	protected $jetpack_sharing_default_options = array();

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.5.0
	 */
	public $file;

	/**
	 * Style Manager class object.
	 * @var Customify_Style_Manager
	 * @access  public
	 * @since   1.0.0
	 */
	public $style_manager = null;

	/**
	 * Minimal Required PHP Version
	 * @var string
	 * @access  private
	 * @since   1.5.0
	 */
	private $minimalRequiredPhpVersion = 5.2;

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
		// Load the config file
		$this->config = $this->get_config();
		// Load the plugin's settings from the DB
		$this->plugin_settings = get_option( $this->config['settings-key'] );

		/* Initialize the Style Manager logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-style-manager.php' );
		if ( is_null( $this->style_manager ) ) {
			$this->style_manager = Customify_Style_Manager::instance( $this );
		}

		// Register all the needed hooks
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	function register_hooks() {
		/*
		 * Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		 */
		register_activation_hook( $this->file, array( $this, 'activate' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );

		/*
		 * Load plugin text domain
		 */
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		/*
		 * Load the upgrade logic.
		 */
		add_action( 'admin_init', array( $this, 'upgrade' ) );

		/*
		 * Prepare and load the configuration
		 */
		$this->init_plugin_configs();

		// We need to load the configuration as late as possible so we allow all that want to influence it
		// We need the init hook and not after_setup_theme because there a number of plugins that fire up on init (like certain modules from Jetpack)
		// We need to be able to load things like components configs depending on those firing up or not
		// DO NOT TRY to use the Customify values before this!
		add_action( 'init', array( $this, 'load_plugin_configs' ), 15 );

		/*
		 * Now setup the admin side of things
		 */
		// Starting with the menu item for this plugin
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( $this->file ) . 'pixcustomify.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		/*
		 * Now it's time for the Customizer logic to kick in
		 */
		// Styles for the Customizer
		add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_styles' ), 10 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_styles' ), 10 );
		// Scripts enqueued in the Customizer
		add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_scripts' ), 15 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_scripts' ), 15 );

		// Scripts enqueued only in the theme preview
		add_action( 'customize_preview_init', array( $this, 'customizer_live_preview_register_scripts' ), 10 );
		add_action( 'customize_preview_init', array( $this, 'customizer_live_preview_enqueue_scripts' ), 99999 );

		// Add extra settings data to _wpCustomizeSettings.settings of the parent window.
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'customize_pane_settings_additional_data' ), 10000 );

		// The frontend effects of the Customizer controls
		$load_location = $this->get_plugin_setting( 'style_resources_location', 'wp_head' );

		add_action( $load_location, array( $this, 'output_dynamic_style' ), 99 );
		add_action( 'wp_head', array( $this, 'output_typography_dynamic_style' ), 10 );

		add_action( 'customize_register', array( $this, 'remove_default_sections' ), 11 );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 12 );
		// Maybe the theme has instructed us to do things like removing sections or controls.
		add_action( 'customize_register', array( $this, 'maybe_process_config_extras' ), 13 );

		if ( $this->get_plugin_setting( 'enable_editor_style', true ) ) {
			add_action( 'admin_head', array( $this, 'add_customizer_settings_into_wp_editor' ) );
		}

		add_action( 'rest_api_init', array( $this, 'add_rest_routes_api' ) );

		/*
		 * Development related
		 */
		if ( defined( 'CUSTOMIFY_DEV_FORCE_DEFAULTS' ) && true === CUSTOMIFY_DEV_FORCE_DEFAULTS ) {
			// If the development constant CUSTOMIFY_DEV_FORCE_DEFAULTS has been defined we will not save anything in the database
			// Always go with the default
			add_filter( 'customize_changeset_save_data', array( $this, 'prevent_changeset_save_in_devmode' ), 50, 2 );
			// Add a JS to display a notification
			add_action( 'customize_controls_print_footer_scripts', array( $this, 'prevent_changeset_save_in_devmode_notification' ), 100 );
		}
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

	/**
	 * Initialize Configs, Options and Values methods.
	 */
	function init_plugin_configs() {
		$this->customizer_config = get_option( 'pixcustomify_config' );

		// no option so go for default.
		if ( empty( $this->customizer_config ) ) {
			$this->customizer_config = $this->get_config_option( 'default_options' );
		}

		if ( empty( $this->customizer_config ) ) {
			$this->customizer_config = array();
		}
	}

	/**
	 * Load the plugin configuration and options.
	 */
	function load_plugin_configs() {

		// Allow themes or other plugins to filter the config.
		$this->customizer_config = apply_filters( 'customify_filter_fields', $this->customizer_config );
		// We apply a second filter for those that wish to work with the final config and not rely on a a huge priority number.
		$this->customizer_config = apply_filters( 'customify_final_config', $this->customizer_config );

		$this->opt_name          = $this->localized['options_name'] = $this->customizer_config['opt-name'];
		$this->options_list      = $this->get_options();

		// Load the current options values.
		$this->current_values = $this->get_current_values();

		if ( $this->import_button_exists() ) {
			$this->localized['import_rest_url']   = get_rest_url( '/customify/1.0/' );
			$this->localized['import_rest_nonce'] = wp_create_nonce( 'wp_rest' );

			$this->register_import_api();
		}

		$this->localized['theme_fonts'] = $this->theme_fonts = Customify_Font_Selector::instance()->get_theme_fonts();

		$this->localized['ajax_url'] = admin_url( 'admin-ajax.php' );
		$this->localized['style_manager_user_feedback_nonce'] = wp_create_nonce( 'customify_style_manager_user_feedback' );
		$this->localized['style_manager_user_feedback_provided'] = get_option( 'style_manager_user_feedback_provided', false );
	}

	public function get_version() {
		return $this->_version;
	}

	public function get_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Fired when the plugin is activated.
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		//@todo Define activation functionality here
	}

	/**
	 * Fired when the plugin is deactivated.
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	static function deactivate( $network_wide ) {
		//@todo Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 * @since    1.0.0
	 */
	function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		load_plugin_textdomain( $domain, false, basename( dirname( $this->file ) ) . '/languages/' );
	}

	/** === RESOURCES === **/

	/**
	 * Register Customizer admin styles
	 */
	function register_admin_customizer_styles() {
		wp_register_style( 'customify_select2', plugins_url( 'js/select2/css/select2.css', $this->file ), array(), $this->_version );
		wp_register_style( 'customify_style', plugins_url( 'css/customizer.css', $this->file ), array( 'customify_select2' ), $this->_version );
	}

	/**
	 * Enqueue Customizer admin styles
	 */
	function enqueue_admin_customizer_styles() {
		wp_enqueue_style( 'customify_style' );
	}

	/**
	 * Register Customizer admin scripts
	 */
	function register_admin_customizer_scripts() {

		wp_register_script( 'customify_select2', plugins_url( 'js/select2/js/select2.js', $this->file ), array( 'jquery' ), $this->_version );
		wp_register_script( 'jquery-react', plugins_url( 'js/jquery-react.js', $this->file ), array( 'jquery' ), $this->_version );

		wp_register_script( 'customify-scale', plugins_url( 'js/customizer/scale-iframe.js', $this->file ), array( 'jquery' ), $this->_version );
		wp_register_script( 'customify-fontselectfields', plugins_url( 'js/customizer/font-select-fields.js', $this->file ), array( 'jquery' ), $this->_version );

		wp_register_script( $this->plugin_slug . '-customizer-scripts', plugins_url( 'js/customizer.js', $this->file ), array(
			'jquery',
			'customify_select2',
			'underscore',
			'customize-controls',
			'customify-fontselectfields',

			'customify-scale',
		), $this->_version );
	}

	/**
	 * Enqueue Customizer admin scripts
	 */
	function enqueue_admin_customizer_scripts() {
		wp_enqueue_script( 'jquery-react' );
		wp_enqueue_script( $this->plugin_slug . '-customizer-scripts' );

		wp_localize_script( $this->plugin_slug . '-customizer-scripts', 'customify_settings', apply_filters( 'customify_localized_js_settings', $this->localized ) );
	}

	/** Register Customizer scripts loaded only on previewer page */
	function customizer_live_preview_register_scripts() {
		wp_register_script( $this->plugin_slug . 'CSSOM', plugins_url( 'js/CSSOM.js', $this->file ), array( 'jquery' ), $this->_version, true );
		wp_register_script( $this->plugin_slug . 'cssUpdate', plugins_url( 'js/jquery.cssUpdate.js', $this->file ), array(), $this->_version, true );
		wp_register_script( $this->plugin_slug . '-previewer-scripts', plugins_url( 'js/customizer_preview.js', $this->file ), array(
			'jquery',
			'customize-preview',
			$this->plugin_slug . 'CSSOM',
			$this->plugin_slug . 'cssUpdate'
		), $this->_version, true );
	}

	/** Enqueue Customizer scripts loaded only on previewer page */
	function customizer_live_preview_enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-previewer-scripts' );

		// when a live preview field is in action we need to know which props need 'px' as defaults
		$this->localized['px_dependent_css_props'] = self::$pixel_dependent_css_properties;

		wp_localize_script( $this->plugin_slug . '-previewer-scripts', 'customify_settings', $this->localized );
	}

	/**
	 * Add dynamic style only on the previewer page
	 */

	/**
	 * Settings page styles
	 */
	function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'css/admin.css', $this->file ), array(), $this->_version );
		}
	}

	/**
	 * Settings page scripts
	 */
	function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', $this->file ), array( 'jquery' ), $this->_version );
			wp_localize_script( $this->plugin_slug . '-admin-script', 'customify_settings', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'wp_rest' => array(
					'root'  => esc_url_raw( rest_url() ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
					'customify_settings_nonce' => wp_create_nonce( 'customify_settings_nonce' )
				),
			) );
		}

		wp_localize_script( $this->plugin_slug . '-customizer-scripts', 'WP_API_Settings', array(
			'root'  => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' )
		) );
	}

	function add_rest_routes_api(){
		register_rest_route( 'customify/v1', '/delete_theme_mod', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'delete_theme_mod' ),
			'permission_callback' => array( $this, 'permission_nonce_callback' ),
		) );
	}

	function delete_theme_mod(){
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error('You don\'t have admin privileges.');
		}

		$config = apply_filters('customify_filter_fields', array() );

		if ( empty( $config['opt-name'] ) ) {
			wp_send_json_error('no option key');
		}

		$key = $config['opt-name'];

		remove_theme_mod( $key );

		wp_send_json_success('Deleted ' . $key . ' theme mod!');
	}

	function permission_nonce_callback() {
		return wp_verify_nonce( $this->get_nonce(), 'customify_settings_nonce' );
	}

	private function get_nonce() {
		$nonce = null;

		if ( isset( $_REQUEST['customify_settings_nonce'] ) ) {
			$nonce = wp_unslash( $_REQUEST['customify_settings_nonce'] );
		} elseif ( isset( $_POST['customify_settings_nonce'] ) ) {
			$nonce = wp_unslash( $_POST['customify_settings_nonce'] );
		}

		return $nonce;
	}

	/**
	 * Public style generated by customizer
	 */
	function output_dynamic_style() {
		$custom_css = '';

		foreach ( $this->options_list as $option_id => $option ) {

			if ( isset( $option['css'] ) && ! empty( $option['css'] ) ) {
				// now process each
				$custom_css .= $this->convert_setting_to_css( $option_id, $option['css'] );
			}

			if ( isset( $option['type'] ) && $option['type'] === 'custom_background' ) {
				$option['value']         = $this->get_option( $option_id );
				$custom_css .= $this->process_custom_background_field_output( $option_id, $option ) . PHP_EOL;
			}
		}

		if ( ! empty( $this->media_queries ) ) {

			foreach ( $this->media_queries as $media_query => $properties ) {

				if ( empty( $properties ) ) {
					continue;
				}

				$custom_css .= PHP_EOL . '@media ' . $media_query . " { " . PHP_EOL . PHP_EOL;

				foreach ( $properties as $key => $property ) {
					$property_settings = $property['property'];
					$property_value    = $property['value'];
					$custom_css .= "\t" . $this->proccess_css_property( $property_settings, $property_value ) . PHP_EOL;
				}

				$custom_css .= "}" . PHP_EOL;

			}
		}
		?>
		<style id="customify_output_style">
		<?php echo apply_filters( 'customify_dynamic_style', $custom_css ); ?>
		</style><?php

		/**
		 * from now on we output only style tags only for the preview purpose
		 * so don't cry if you see 30+ style tags for each section
		 */
		if ( ! isset( $GLOBALS['wp_customize'] ) ) {
			return;
		}

		foreach ( $this->options_list as $option_id => $options ) {

			if ( isset( $options['type'] ) && $options['type'] === 'custom_background' ) {
				$options['value']         = $this->get_option( $option_id );
				$custom_background_output = $this->process_custom_background_field_output( $option_id, $options ); ?>

				<style id="custom_background_output_for_<?php echo sanitize_html_class( $option_id ); ?>">
					<?php
					if ( isset( $custom_background_output ) && ! empty( $custom_background_output )) {
						echo $custom_background_output;
					} ?>
				</style>
			<?php }

			if ( ! isset( $options['live'] ) || $options['live'] !== true ) {
				continue;
			}

			$this_value = $this->get_option( $option_id );
			if ( ! empty( $options['css'] ) ) {
				foreach ( $options['css'] as $key => $properties_set ) {
					// We need to use a class because we may have multiple <style>s with the same "ID" for example
					// when targeting the same property but with different selectors.
					?>
					<style class="dynamic_setting_<?php echo sanitize_html_class( $option_id ) . '_property_' . str_replace( '-', '_', $properties_set['property'] ) . '_' . $key; ?>"
					       type="text/css"><?php

					if ( isset( $properties_set['media'] ) && ! empty( $properties_set['media'] ) ) {
						echo '@media '. $properties_set['media'] . " {" . PHP_EOL;
					}

					if ( isset( $properties_set['selector'] ) && isset( $properties_set['property'] ) ) {
						echo $this->proccess_css_property($properties_set, $this_value) . PHP_EOL;
					}

					if ( isset( $properties_set['media'] ) && ! empty( $properties_set['media'] ) ) {
						echo "}" . PHP_EOL;
					} ?>
					</style>
				<?php }
			}
		}
	}

	protected function load_google_fonts() {
		$fonts_path = plugin_dir_path( $this->file ) . 'features/customizer/controls/resources/google.fonts.php';

		if ( file_exists( $fonts_path ) ) {
			$this->google_fonts = require( $fonts_path );
		}

		if ( ! empty( $this->google_fonts ) ) {
			return $this->google_fonts;
		}

		return false;
	}

	function output_typography_dynamic_style() {
		$this->get_typography_fields( $this->options_list, 'type', 'typography', $this->typo_settings );

		if ( empty( $this->typo_settings ) ) {
			return;
		}

		$families = '';

		foreach ( $this->typo_settings as $id => $font ) {
			if ( isset ( $font['value'] ) ) {

				$load_all_weights = false;
				if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
					$load_all_weights = true;
				}

				// shim the time when this was an array
				// @todo Is this really needed? Or does it make sense?
				if ( is_array( $font['value'] ) ) {
					$font['value'] = stripslashes_deep( $font['value'] );
					$font['value'] = json_encode( $font['value'] );
				}

				$value = wp_unslash( PixCustomifyPlugin::decodeURIComponent( $font['value'] ) );
				if ( is_string( $value ) ) {
					$value = json_decode( $value, true );
				}

				// In case the value is still null, try default value (mostly for google fonts)
				if ( $value === null || ! is_array( $value ) ) {
					$value = $this->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
				}

				// Bail if by this time we don't have a value of some sort
				if ( empty( $value ) ) {
					continue;
				}

				// Handle special logic for when the $value array is not an associative array
				if ( ! $this->is_assoc( $value ) ) {
					$value = $this->standardize_non_associative_font_default( $value );
				}

				// Bail if empty or we don't have an array
				if ( empty( $value ) || ! is_array( $value ) ) {
					continue;
				}

				if ( isset( $value['font_family'] ) && isset( $value['type'] ) && $value['type'] == 'google' ) {
					$families .= "'" . $value['font_family'];

					if ( $load_all_weights && is_array( $value['variants'] ) ) {
						$families .= ":" . implode( ',', $value['variants'] );
					} elseif ( isset( $value['selected_variants'] ) && ! empty( $value['selected_variants'] ) ) {
						if ( is_array( $value['selected_variants'] ) ) {
							$families .= ":" . implode( ',', $value['selected_variants'] );
						} elseif ( is_string( $value['selected_variants'] ) || is_numeric( $value['selected_variants'] ) ) {
							$families .= ":" . $value['selected_variants'];
						}
					} elseif ( isset( $value['variants'] ) && ! empty( $value['variants'] ) ) {
						if ( is_array( $value['variants'] ) ) {
							$families .= ":" . implode( ',', $value['variants'] );
						} else {
							$families .= ":" . $value['variants'];
						}
					}

					if ( isset( $value['selected_subsets'] ) && ! empty( $value['selected_subsets'] ) ) {
						if ( is_array( $value['selected_subsets'] ) ) {
							$families .= ":" . implode( ',', $value['selected_subsets'] );
						} else {
							$families .= ":" . $value['selected_subsets'];
						}
					} elseif ( isset( $value['subsets'] ) && ! empty( $value['subsets'] ) ) {
						if ( is_array( $value['subsets'] ) ) {
							$families .= ":" . implode( ',', $value['subsets'] );
						} else {
							$families .= ":" . $value['subsets'];
						}
					}

					$families .= '\',';
				}
			}
		}

		if ( ! empty ( $families ) && $this->get_plugin_setting( 'typography', '1' ) && $this->get_plugin_setting( 'typography_google_fonts', 1 ) ) { ?>
			<script type="text/javascript">
				if (typeof WebFont !== 'undefined') {<?php // if there is a WebFont object, use it ?>
					WebFont.load({
						google: {families: [<?php echo( rtrim( $families, ',' ) ); ?>]},
						classes: false,
						events: false
					});
				} else {<?php // basically when we don't have the WebFont object we create the google script dynamically  ?>

					var tk = document.createElement('script');
					tk.src = '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
					tk.type = 'text/javascript';

					tk.onload = tk.onreadystatechange = function () {
						WebFont.load({
							google: {families: [<?php echo( rtrim( $families, ',' ) ); ?>]},
							classes: false,
							events: false
						});
					};

					var s = document.getElementsByTagName('script')[0];
					s.parentNode.insertBefore(tk, s);
				}
			</script>
		<?php } ?>
		<style id="customify_typography_output_style">
			<?php
			foreach ( $this->typo_settings as $key => $font ) {
				$load_all_weights = false;
				if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
					$load_all_weights = true;
				}

				if ( isset( $font['selector'] ) && isset( $font['value'] ) && ! empty( $font['value'] ) ) {
					// Make sure that the value is in the proper format
					$value = PixCustomifyPlugin::decodeURIComponent( $font['value'] );
					if ( is_string( $value ) ) {
						$value = json_decode( $value, true );
					}

					// In case the value is null (most probably because the json_decode failed),
					// try the default value (mostly for google fonts)
					if ( $value === null ) {
						$value = $this->get_font_defaults_value( $font['value'] );
					}

					// Shim the old case when the default was only the font name
					if ( ! empty( $value ) && is_string( $value ) ) {
						$value = array( 'font_family' => $value );
					}

					// Handle special logic for when the $value array is not an associative array
					if ( ! $this->is_assoc( $value ) ) {
						$value = $this->standardize_non_associative_font_default( $value );
					}

					// Bail if empty or we don't have an array
					if ( empty( $value ) || ! is_array( $value ) ) {
						continue;
					}

					$selected_variant = '';
					if ( ! empty( $value['selected_variants'] ) ) {
						if ( is_array( $value['selected_variants'] ) ) {
							$selected_variant = $value['selected_variants'][0];
						} else {
							$selected_variant = $value['selected_variants'];
						}
					}

					// First handle the case where we have the font-family in the selected variant (usually this means a custom font from our Fonto plugin)
					if ( ! empty( $selected_variant ) && is_array( $selected_variant ) && ! empty( $selected_variant['font-family'] ) ) {
						// The variant's font-family
						echo $font['selector'] . " {\nfont-family: " . $selected_variant['font-family'] . ";\n";

						if ( ! $load_all_weights ) {
							// If this is a custom font (like from our plugin Fonto) with individual styles & weights - i.e. the font-family says it all
							// we need to "force" the font-weight and font-style
							if ( ! empty( $value['type'] ) && 'custom_individual' == $value['type'] ) {
								$selected_variant['font-weight'] = '400 !important';
								$selected_variant['font-style'] = 'normal !important';
							}

							// Output the font weight, if available
							if ( ! empty( $selected_variant['font-weight'] ) ) {
								echo "font-weight: " . $selected_variant['font-weight'] . ";\n";
							}

							// Output the font style, if available
							if ( ! empty( $selected_variant['font-style'] ) ) {
								echo "font-style: " . $selected_variant['font-style'] . ";\n";
							}
						}

						echo "}\n";
					} elseif ( isset( $value['font_family'] ) ) {
						// The selected font family
						echo $font['selector'] . " {\n font-family: " . $value['font_family'] . ";\n";

						if ( ! empty( $selected_variant ) && ! $load_all_weights ) {
							$weight_and_style = strtolower( $selected_variant );

							$italic_font = false;

							//determine if this is an italic font (the $weight_and_style is usually like '400' or '400italic' )
							if ( strpos( $weight_and_style, 'italic' ) !== false ) {
								$weight_and_style = str_replace( 'italic', '', $weight_and_style);
								$italic_font = true;
							}

							if ( ! empty( $weight_and_style ) ) {
								//a little bit of sanity check - in case it's not a number
								if( $weight_and_style === 'regular' ) {
									$weight_and_style = 'normal';
								}
								echo "font-weight: " . $weight_and_style . ";\n";
							}

							if ( $italic_font ) {
								echo "font-style: italic;\n";
							}
						}

						echo "}\n";
					}
				}
			} ?>
		</style>
	<?php }

	/**
	 * Handle special logic for when the $value array is not an associative array
	 * Return a new associative array with proper keys
	 */
	public function standardize_non_associative_font_default( $value ) {
		// If the value provided is not array, simply return it
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$new_value = array();

		// Let's determine some type of font
		if ( ! isset( $value[2] ) || 'google' == $value[2] ) {
			$new_value = $this->get_font_defaults_value( $value[0] );
		} else {
			$new_value['type'] = $value[2];
		}

		if ( null == $new_value ) {
			$new_value = array();
		}

		// The first entry is the font-family
		if ( isset( $value[0] ) ) {
			$new_value['font_family'] = $value[0];
		}

		// In case we don't have an associative array
		// The second entry is the variants
		if ( isset( $value[1] ) ) {
			$new_value['selected_variants'] = $value[1];
		}

		return $new_value;
	}

	/**
	 *
	 * @param $font_name
	 *
	 * @return null
	 */
	public function get_font_defaults_value( $font_name ) {

		if ( empty( $this->google_fonts ) ) {
			$this->load_google_fonts();
		}

		if ( isset( $this->google_fonts[ $font_name ] ) ) {
			$value                = $this->google_fonts[ $font_name ];
			$value['font_family'] = $font_name;
			$value['type']        = 'google';

			return $value;
		} elseif ( isset( $this->theme_fonts[ $font_name ] ) ) {
			$value['type']        = 'theme_font';
			$value['src']         = $this->theme_fonts[ $font_name ]['src'];
			$value['variants']    = $this->theme_fonts[ $font_name ]['variants'];
			$value['font_family'] = $this->theme_fonts[ $font_name ]['family'];

			return $value;
		}

		return null;
	}

	/**
	 * Turn css options into a valid CSS output
	 *
	 * @param $option_id
	 * @param array $css_config
	 *
	 * @return string
	 */
	protected function convert_setting_to_css( $option_id, $css_config = array() ) {
		$output = '';

		$this_value = $this->get_option( $option_id );

		if ( empty( $css_config ) ) {
			return $output;
		}

		foreach ( $css_config as $css_property ) {

			if ( isset( $css_property['media'] ) && ! empty( $css_property['media'] ) ) {
				$this->media_queries[ $css_property['media'] ][ $option_id ] = array(
					'property' => $css_property,
					'value'    => $this_value
				);
				continue;
			}

			if ( isset( $css_property['selector'] ) && isset( $css_property['property'] ) ) {
				$output .= $this->proccess_css_property( $css_property, $this_value ) . PHP_EOL;
			}
		}

		return $output;
	}

	protected function proccess_css_property( $css_property, $this_value ) {
		$unit = '';

		if ( isset( $css_property['unit'] ) ) {
			$unit = $css_property['unit'];
		}

		// if the unit isn't specified but the property should have a unit force 'px' as it
		if ( empty( $unit ) && in_array( $css_property['property'], self::$pixel_dependent_css_properties ) ) {
			$unit = 'px';
		}
		// lose the tons of tabs
		$css_property['selector'] = trim( preg_replace( '/\t+/', '', $css_property['selector'] ) );

		$this_property_output = $css_property['selector'] . ' { ' . $css_property['property'] . ': ' . $this_value . $unit . "; }" . PHP_EOL;

		// Handle the value filter callback.
		if ( isset( $css_property['filter_value_cb'] ) ) {
			$this_value = $this->maybe_apply_filter( $css_property['filter_value_cb'], $this_value );
		}

		// Handle output callback.
		if ( isset( $css_property['callback_filter'] ) && is_callable( $css_property['callback_filter'] ) ) {
			$this_property_output = call_user_func( $css_property['callback_filter'], $this_value, $css_property['selector'], $css_property['property'], $unit );
		}

		return $this_property_output;
	}

	/**
	 * Apply a filter (config) to a value.
	 *
	 * We currently handle filters like these:
	 *  // Elaborate filter config
	 *  array(
	 *      'callback' => 'is_post_type_archive',
	 *      // The arguments we should pass to the check function.
	 *      // Think post types, taxonomies, or nothing if that is the case.
	 *      // It can be an array of values or a single value.
	 *      'args' => array(
	 *          'jetpack-portfolio',
	 *      ),
	 *  ),
	 *  // Simple filter - just the function name
	 *  'is_404',
	 *
	 * @param array|string $filter
	 * @param mixed $value The value to apply the filter to.
	 *
	 * @return mixed The filtered value.
	 */
	public function maybe_apply_filter( $filter, $value ) {
		// Let's get some obvious things off the table.
		// On invalid data, we just return what we've received.
		if ( empty( $filter ) ) {
			return $value;
		}

		// First, we handle the shorthand version: just a function name
		if ( is_string( $filter ) && is_callable( $filter ) ) {
			$value = call_user_func( $filter );
		} elseif ( is_array( $filter ) && ! empty( $filter['callback'] ) && is_callable( $filter['callback'] ) ) {
			if ( empty( $filter['args'] ) ) {
				$filter['args'] = array();
			}
			// The value is always the first argument.
			$filter['args'] = array( $value ) + $filter['args'];

			$value = call_user_func_array( $filter['callback'], $filter['args'] );
		}

		return $value;
	}

	protected function process_custom_background_field_output( $option_id, $options ) {
		$selector = $output = '';

		if ( ! isset( $options['value'] ) ) {
			return false;
		}
		$value = $options['value'];

		if ( ! isset( $options['output'] ) ) {
			return $selector;
		} elseif ( is_string( $options['output'] ) ) {
			$selector = $options['output'];
		} elseif ( is_array( $options['output'] ) ) {
			$selector = implode( ' ', $options['output'] );
		}


		$output .= $selector . " {";
		if ( isset( $value['background-image'] ) && ! empty( $value['background-image'] ) ) {
			$output .= "background-image: url( " . $value['background-image'] . ");";
		} else {
			$output .= "background-image: none;";
		}

		if ( isset( $value['background-repeat'] ) && ! empty( $value['background-repeat'] ) ) {
			$output .= "background-repeat:" . $value['background-repeat'] . ";";
		}

		if ( isset( $value['background-position'] ) && ! empty( $value['background-position'] ) ) {
			$output .= "background-position:" . $value['background-position'] . ";";
		}

		if ( isset( $value['background-size'] ) && ! empty( $value['background-size'] ) ) {
			$output .= "background-size:" . $value['background-size'] . ";";
		}

		if ( isset( $value['background-attachment'] ) && ! empty( $value['background-attachment'] ) ) {
			$output .= "background-attachment:" . $value['background-attachment'] . ";";
		}
		$output .= "}\n";

		return $output;
	}

	/**
	 * add our customizer styling edits into the wp_editor
	 */
	function add_customizer_settings_into_wp_editor() {

		ob_start();
		$this->output_typography_dynamic_style();
		$this->output_dynamic_style();

		$custom_css = ob_get_clean(); ?>
		<script type="text/javascript">
			/* <![CDATA[ */
			(function ($) {
				$(window).load(function () {
					/**
					 * @param iframe_id the id of the frame you want to append the style
					 * @param style_element the style element you want to append
					 */
					var append_script_to_iframe = function (ifrm_id, scriptEl) {
						var myIframe = document.getElementById(ifrm_id);

						var script = myIframe.contentWindow.document.createElement("script");
						script.type = "text/javascript";
						script.innerHTML = scriptEl.innerHTML;

						myIframe.contentWindow.document.head.appendChild(script);
					};

					var append_style_to_iframe = function (ifrm_id, styleElment) {
						var ifrm = window.frames[ifrm_id];
                        if ( typeof ifrm === "undefined" ) {
                            return;
                        }
						ifrm = ( ifrm.contentDocument || ifrm.contentDocument || ifrm.document );
						var head = ifrm.getElementsByTagName('head')[0];

						if (typeof styleElment !== "undefined") {
							head.appendChild(styleElment);
						}
					};

					var xmlString = <?php echo json_encode( str_replace( "\n", "", $custom_css ) ); ?>,
						parser = new DOMParser(),
						doc = parser.parseFromString(xmlString, "text/html");

					if (typeof window.frames['content_ifr'] !== 'undefined') {

						$.each(doc.head.childNodes, function (key, el) {
							if (typeof el !== "undefined" && typeof el.tagName !== "undefined") {

								switch (el.tagName) {
									case 'STYLE' :
										append_style_to_iframe('content_ifr', el);
										break;
									case 'SCRIPT' :
										append_script_to_iframe('content_ifr', el);
										break;
									default:
										break;
								}
							}
						});
					}
				});
			})(jQuery);
			/* ]]> */
		</script>
	<?php }

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_options_page( __( 'Customify', $this->plugin_slug ), __( 'Customify', $this->plugin_slug ), 'edit_plugins', $this->plugin_slug, array(
			$this,
			'display_plugin_admin_page'
		) );
	}

	/**
	 * Render the settings page for this plugin.
	 */
	function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 */
	function add_action_links( $links ) {
		return array_merge( array( 'settings' => '<a href="' . admin_url( 'options-general.php?page=pixcustomify' ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>' ), $links );
	}

	protected function register_customizer_controls() {

		// first get the base customizer extend class
		require_once( $this->get_base_path() . '/features/customizer/class-Pix_Customize_Control.php' );

		// now get all the controls
		$path = $this->get_base_path() . '/features/customizer/controls/';
		pixcustomify::require_all( $path );
	}

	/**
	 * Maybe process certain "commands" from the config.
	 *
	 * Mainly things like removing sections, controls, etc.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function maybe_process_config_extras( $wp_customize ) {
		// Bail if we have no external theme config data.
		if ( empty( $this->customizer_config ) || ! is_array( $this->customizer_config ) ) {
			return;
		}

		// Maybe remove panels
		if ( ! empty( $this->customizer_config['remove_panels'] ) ) {
			// Standardize it.
			if ( is_string( $this->customizer_config['remove_panels'] ) ) {
				$this->customizer_config['remove_panels'] = array( $this->customizer_config['remove_panels'] );
			}

			foreach ( $this->customizer_config['remove_panels'] as $panel_id ) {
				$wp_customize->remove_panel( $panel_id );
			}
		}

		// Maybe change panel props.
		if ( ! empty( $this->customizer_config['change_panel_props'] ) ) {
			foreach ( $this->customizer_config['change_panel_props'] as $panel_id => $panel_props ) {
				if ( ! is_array( $panel_props ) ) {
					continue;
				}

				$panel = $wp_customize->get_panel( $panel_id );
				if ( empty( $panel ) || ! $panel instanceof WP_Customize_Panel ) {
					continue;
				}

				$public_props = get_class_vars( get_class( $panel ) );
				foreach ( $panel_props as $prop_name => $prop_value ) {

					if ( ! in_array( $prop_name, array_keys( $public_props ) ) ) {
						continue;
					}

					$panel->$prop_name = $prop_value;
				}
			}
		}

		// Maybe remove sections
		if ( ! empty( $this->customizer_config['remove_sections'] ) ) {
			// Standardize it.
			if ( is_string( $this->customizer_config['remove_sections'] ) ) {
				$this->customizer_config['remove_sections'] = array( $this->customizer_config['remove_sections'] );
			}

			foreach ( $this->customizer_config['remove_sections'] as $section_id ) {

				if ( 'widgets' === $section_id ) {
					global $wp_registered_sidebars;

					foreach ( $wp_registered_sidebars as $widget => $settings ) {
						$wp_customize->remove_section( 'sidebar-widgets-' . $widget );
					}
					continue;
				}

				$wp_customize->remove_section( $section_id );
			}
		}

		// Maybe change section props.
		if ( ! empty( $this->customizer_config['change_section_props'] ) ) {
			foreach ( $this->customizer_config['change_section_props'] as $section_id => $section_props ) {
				if ( ! is_array( $section_props ) ) {
					continue;
				}

				$section = $wp_customize->get_section( $section_id );
				if ( empty( $section ) || ! $section instanceof WP_Customize_Section ) {
					continue;
				}

				$public_props = get_class_vars( get_class( $section ) );
				foreach ( $section_props as $prop_name => $prop_value ) {

					if ( ! in_array( $prop_name, array_keys( $public_props ) ) ) {
						continue;
					}

					$section->$prop_name = $prop_value;
				}
			}
		}

		// Maybe remove settings
		if ( ! empty( $this->customizer_config['remove_settings'] ) ) {
			// Standardize it.
			if ( is_string( $this->customizer_config['remove_settings'] ) ) {
				$this->customizer_config['remove_settings'] = array( $this->customizer_config['remove_settings'] );
			}

			foreach ( $this->customizer_config['remove_settings'] as $setting_id ) {
				$wp_customize->remove_setting( $setting_id );
			}
		}

		// Maybe change setting props.
		if ( ! empty( $this->customizer_config['change_setting_props'] ) ) {
			foreach ( $this->customizer_config['change_setting_props'] as $setting_id => $setting_props ) {
				if ( ! is_array( $setting_props ) ) {
					continue;
				}

				$setting = $wp_customize->get_setting( $setting_id );
				if ( empty( $setting ) || ! $setting instanceof WP_Customize_Setting ) {
					continue;
				}

				$public_props = get_class_vars( get_class( $setting ) );
				foreach ( $setting_props as $prop_name => $prop_value ) {

					if ( ! in_array( $prop_name, array_keys( $public_props ) ) ) {
						continue;
					}

					$setting->$prop_name = $prop_value;
				}
			}
		}

		// Maybe remove controls
		if ( ! empty( $this->customizer_config['remove_controls'] ) ) {
			// Standardize it.
			if ( is_string( $this->customizer_config['remove_controls'] ) ) {
				$this->customizer_config['remove_controls'] = array( $this->customizer_config['remove_controls'] );
			}

			foreach ( $this->customizer_config['remove_controls'] as $control_id ) {
				$wp_customize->remove_control( $control_id );
			}
		}

		// Maybe change control props.
		if ( ! empty( $this->customizer_config['change_control_props'] ) ) {
			foreach ( $this->customizer_config['change_control_props'] as $control_id => $control_props ) {
				if ( ! is_array( $control_props ) ) {
					continue;
				}

				$control = $wp_customize->get_control( $control_id );
				if ( empty( $control ) || ! $control instanceof WP_Customize_Control ) {
					continue;
				}

				$public_props = get_class_vars( get_class( $control ) );
				foreach ( $control_props as $prop_name => $prop_value ) {

					if ( ! in_array( $prop_name, array_keys( $public_props ) ) ) {
						continue;
					}

					$control->$prop_name = $prop_value;
				}
			}
		}
	}

	/**
	 * @param WP_Customize_Manager $wp_customize
	 */
	function register_customizer( $wp_customize ) {

		$this->register_customizer_controls();

		$customizer_settings = $this->customizer_config;

		if ( ! empty ( $customizer_settings ) ) {

			// first check the very needed options name
			if ( empty( $customizer_settings['opt-name'] ) ) {
				return;
			}
			$options_name              = $customizer_settings['opt-name'];
			$wp_customize->options_key = $options_name;

			// let's check if we have sections or panels
			if ( isset( $customizer_settings['panels'] ) && ! empty( $customizer_settings['panels'] ) ) {

				foreach ( $customizer_settings['panels'] as $panel_id => $panel_config ) {

					if ( ! empty( $panel_id ) && isset( $panel_config['sections'] ) && ! empty( $panel_config['sections'] ) ) {

						// If we have been explicitly given a panel ID we will use that
						if ( ! empty( $panel_config['panel_id'] ) ) {
							$panel_id = $panel_config['panel_id'];
						} else {
							$panel_id   = $options_name . '[' . $panel_id . ']';
						}

						$panel_args = array(
							'priority'                 => 10,
							'capability'               => 'edit_theme_options',
							'title'                    => __( 'Panel title is required', 'customify' ),
							'description'              => __( 'Description of what this panel does.', 'customify' ),
							'auto_expand_sole_section' => false,
						);

						if ( isset( $panel_config['priority'] ) && ! empty( $panel_config['priority'] ) ) {
							$panel_args['priority'] = $panel_config['priority'];
						}

						if ( isset( $panel_config['title'] ) && ! empty( $panel_config['title'] ) ) {
							$panel_args['title'] = $panel_config['title'];
						}

						if ( isset( $panel_config['description'] ) && ! empty( $panel_config['description'] ) ) {
							$panel_args['description'] = $panel_config['description'];
						}

						if ( isset( $panel_config['auto_expand_sole_section'] ) ) {
							$panel_args['auto_expand_sole_section'] = $panel_config['auto_expand_sole_section'];
						}


						$wp_customize->add_panel( $panel_id, $panel_args );

						foreach ( $panel_config['sections'] as $section_id => $section_config ) {
							if ( ! empty( $section_id ) && isset( $section_config['options'] ) && ! empty( $section_config['options'] ) ) {
								$this->register_section( $panel_id, $section_id, $options_name, $section_config, $wp_customize );
							}
						}
					}
				}
			}

			if ( isset( $customizer_settings['sections'] ) && ! empty( $customizer_settings['sections'] ) ) {

				foreach ( $customizer_settings['sections'] as $section_id => $section_config ) {
					if ( ! empty( $section_id ) && isset( $section_config['options'] ) && ! empty( $section_config['options'] ) ) {
						$this->register_section( $panel_id = false, $section_id, $options_name, $section_config, $wp_customize );
					}
				}
			}

			if ( $this->plugin_settings['enable_reset_buttons'] ) {
				// create a toolbar section which will be present all the time
				$reset_section_settings = array(
					'title'   => 'Customify Toolbox',
					'capability' => 'manage_options',
					'priority' => 999999999,
					'options' => array(
						'reset_all_button' => array(
							'type'   => 'button',
							'label'  => 'Reset Customify',
							'action' => 'reset_customify',
							'value'  => 'Reset'
						),
					)
				);

				$wp_customize->add_section(
					'customify_toolbar',
					$reset_section_settings
				);

				$wp_customize->add_setting(
					'reset_customify',
					array()
				);
				$wp_customize->add_control( new Pix_Customize_Button_Control(
					$wp_customize,
					'reset_customify',
					array(
						'label'    => __( 'Reset All Customify Options to Default', 'customify' ),
						'section'  => 'customify_toolbar',
						'settings' => 'reset_customify',
						'action'   => 'reset_customify',
					)
				) );
			}

			// register typekit options
			if ( isset( $customizer_settings['typekit_options'] ) ) {

				// create a toolbar section which will be present all the time
				$reset_section_settings = array(
					'title'      => 'Customify Typekit Options',
					'capability' => 'manage_options',
					'options'    => array(
						'typkit_user'     => array(
							'type'  => 'text',
							'label' => 'Typekit Username',
						),
						'typkit_password' => array(
							'type'  => 'text',
							'label' => 'Typekit Username',
						),
					)
				);
			}
		}

		do_action( 'customify_create_custom_control', $wp_customize );
	}

	/**
	 * @param string $panel_id
	 * @param string $section_key
	 * @param string $options_name
	 * @param array $section_config
	 * @param WP_Customize_Manager $wp_customize
	 */
	protected function register_section( $panel_id, $section_key, $options_name, $section_config, $wp_customize ) {

		if ( isset( $this->plugin_settings['disable_customify_sections'] ) && isset( $this->plugin_settings['disable_customify_sections'][ $section_key ] ) ) {
			return;
		}

		// If we have been explicitly given a section ID we will use that
		if ( ! empty( $section_config['section_id'] ) ) {
			$section_id = $section_config['section_id'];
		} else {
			$section_id = $options_name . '[' . $section_key . ']';
		}

		// Add the new section to the Customizer, but only if it is not already added.
		if ( ! $wp_customize->get_section( $section_id ) ) {
			// Merge the section settings with the defaults
			$section_args = wp_parse_args( $section_config, array(
				'priority'   => 10,
				'panel'      => $panel_id,
				'capability' => 'edit_theme_options',
				'theme_supports' => '',
				'title'      => esc_html__( 'Title Section is required', 'customify' ),
				'description' => '',
				'type' => 'default',
				'description_hidden' => false,
			) );

			$wp_customize->add_section( $section_id, $section_args );
		}

		// Now go through each section option and add the fields
		foreach ( $section_config['options'] as $option_id => $option_config ) {

			if ( empty( $option_id ) || ! isset( $option_config['type'] ) ) {
				continue;
			}

			// If we have been explicitly given a setting ID we will use that
			if ( ! empty( $option_config['setting_id'] ) ) {
				$setting_id = $option_config['setting_id'];
			} else {
				$setting_id = $options_name . '[' . $option_id . ']';
			}

			// Add the option config to the localized array so we can pass the info to JS.
			// @todo Maybe we should ensure that the connected_fields configs passed here follow the same format and logic as the ones in ::customize_pane_settings_additional_data() thus maybe having the data in the same place.
			$this->localized['settings'][ $setting_id ] = $option_config;

			// Generate a safe option ID (not the final setting ID) to us in HTML attributes like ID or class
			$this->localized['settings'][ $setting_id ]['html_safe_option_id'] = sanitize_html_class( $option_id );

			$this->register_field( $section_id, $setting_id, $option_config, $wp_customize );
		}

	}

	/**
	 * Register a Customizer field (setting and control).
	 *
	 * @see WP_Customize_Setting
	 * @see WP_Customize_Control
	 *
	 * @param string $section_id
	 * @param string $setting_id
	 * @param array $field_config
	 * @param WP_Customize_Manager $wp_customize
	 */
	protected function register_field( $section_id, $setting_id, $field_config, $wp_customize ) {

		$add_control = true;
		// defaults
		$setting_args = array(
			'default'    => '',
			'capability' => 'edit_theme_options',
			'transport'  => 'refresh',
		);
		$control_args = array(
			'priority' => 10,
			'label'    => '',
			'section'  => $section_id,
			'settings' => $setting_id,
		);

		// sanitize settings
		if ( ! empty( $field_config['live'] ) || $field_config['type'] === 'font' ) {
			$setting_args['transport'] = 'postMessage';
		}

		if ( isset( $field_config['default'] ) ) {
			$setting_args['default'] = $field_config['default'];
		}

		if ( ! empty( $field_config['capability'] ) ) {
			$setting_args['capability'] = $field_config['capability'];
		}

		// If the setting defines it's own type we will respect that, otherwise we will follow the global plugin setting.
		if ( ! empty( $field_config['setting_type'] ) ) {
			if ( 'option' === $field_config['setting_type'] ) {
				$setting_args['type'] = 'option';
			}
		} elseif ( $this->plugin_settings['values_store_mod'] === 'option' ) {
			$setting_args['type'] = 'option';
		}

		// if we arrive here this means we have a custom field control
		switch ( $field_config['type'] ) {

			case 'checkbox':

				$setting_args['sanitize_callback'] = array( $this, 'setting_sanitize_checkbox' );
				break;

			default:
				break;
		}

		if ( ! empty( $field_config['sanitize_callback'] ) && is_callable( $field_config['sanitize_callback'] ) ) {
			$setting_args['sanitize_callback'] = $field_config['sanitize_callback'];
		}

		// Add the setting
		$wp_customize->add_setting( $setting_id, $setting_args );

		// now sanitize the control
		if ( ! empty( $field_config['label'] ) ) {
			$control_args['label'] = $field_config['label'];
		}

		if ( ! empty( $field_config['priority'] ) ) {
			$control_args['priority'] = $field_config['priority'];
		}

		if ( ! empty( $field_config['desc'] ) ) {
			$control_args['description'] = $field_config['desc'];
		}

		if ( ! empty( $field_config['active_callback'] ) ) {
			$control_args['active_callback'] = $field_config['active_callback'];
		}


		$control_args['type'] = $field_config['type'];

		// select the control type
		// but first init a default
		$control_class_name = 'Pix_Customize_Text_Control';

		// If is a standard wp field type call it here and skip the rest.
		if ( in_array( $field_config['type'], array(
			'checkbox',
			'dropdown-pages',
			'url',
			'date',
			'time',
			'datetime',
			'week',
			'search'
		) ) ) {
			$wp_customize->add_control( $setting_id . '_control', $control_args );

			return;
		} elseif ( in_array( $field_config['type'], array(
				'radio',
				'select'
			) ) && ! empty( $field_config['choices'] )
		) {
			$control_args['choices'] = $field_config['choices'];
			$wp_customize->add_control( $setting_id . '_control', $control_args );

			return;
		} elseif ( in_array( $field_config['type'], array( 'range' ) ) && ! empty( $field_config['input_attrs'] ) ) {

			$control_args['input_attrs'] = $field_config['input_attrs'];

			$wp_customize->add_control( $setting_id . '_control', $control_args );
		}

		// If we arrive here this means we have a custom field control.
		switch ( $field_config['type'] ) {

			case 'text':
				if ( isset( $field_config['live'] ) ) {
					$control_args['live'] = $field_config['live'];
				}

				$control_class_name = 'Pix_Customize_Text_Control';
				break;

			case 'textarea':
				if ( isset( $field_config['live'] ) ) {
					$control_args['live'] = $field_config['live'];
				}

				$control_class_name = 'Pix_Customize_Textarea_Control';
				break;

			case 'color':
				$control_class_name = 'WP_Customize_Color_Control';
				break;

			case 'color_drop':
				$control_class_name = 'Pix_Customize_Color_Drop_Control';
				break;

			case 'ace_editor':
				if ( isset( $field_config['live'] ) ) {
					$control_args['live'] = $field_config['live'];
				}

				if ( isset( $field_config['editor_type'] ) ) {
					$control_args['editor_type'] = $field_config['editor_type'];
				}

				$control_class_name = 'Pix_Customize_Ace_Editor_Control';
				break;

			case 'upload':
				$control_class_name = 'WP_Customize_Upload_Control';
				break;

			case 'image':
				$control_class_name = 'WP_Customize_Image_Control';
				break;

			case 'media':
				$control_class_name = 'WP_Customize_Media_Control';
				break;

			case 'custom_background':
				if ( isset( $field_config['field'] ) ) {
					$control_args['field'] = $field_config['field'];
				}

				$control_class_name = 'Pix_Customize_Background_Control';
				break;

			case 'cropped_image':
			case 'cropped_media': // 'cropped_media' no longer works
				if ( isset( $field_config['width'] ) ) {
					$control_args['width'] = $field_config['width'];
				}

				if ( isset( $field_config['height'] ) ) {
					$control_args['height'] = $field_config['height'];
				}

				if ( isset( $field_config['flex_width'] ) ) {
					$control_args['flex_width'] = $field_config['flex_width'];
				}

				if ( isset( $field_config['flex_height'] ) ) {
					$control_args['flex_height'] = $field_config['flex_height'];
				}

				if ( isset( $field_config['button_labels'] ) ) {
					$control_args['button_labels'] = $field_config['button_labels'];
				}

				$control_class_name = 'WP_Customize_Cropped_Image_Control';
				break;

			// Custom types
			case 'typography' :
				$use_typography = $this->get_plugin_setting( 'typography', '1' );

				if ( $use_typography === false ) {
					$add_control = false;
					continue;
				}

				$control_class_name = 'Pix_Customize_Typography_Control';

				if ( isset( $field_config['backup'] ) ) {
					$control_args['backup'] = $field_config['backup'];
				}

				if ( isset( $field_config['font_weight'] ) ) {
					$control_args['font_weight'] = $field_config['font_weight'];
				}

				if ( isset( $field_config['subsets'] ) ) {
					$control_args['subsets'] = $field_config['subsets'];
				}

				if ( isset( $field_config['recommended'] ) ) {
					$control_args['recommended'] = array_flip( $field_config['recommended'] );
				}

				if ( isset( $field_config['load_all_weights'] ) ) {
					$control_args['load_all_weights'] = $field_config['load_all_weights'];
				}

				if ( isset( $field_config['default'] ) ) {
					$control_args['default'] = $field_config['default'];
				}

				break;

			case 'font' :
				$use_typography = $this->get_plugin_setting( 'typography', '1' );

				if ( $use_typography === false ) {
					$add_control = false;
					continue;
				}

				$control_class_name = 'Pix_Customize_Font_Control';

				if ( isset( $field_config['backup'] ) ) {
					$control_args['backup'] = $field_config['backup'];
				}

				if ( isset( $field_config['font_weight'] ) ) {
					$control_args['font_weight'] = $field_config['font_weight'];
				}

				if ( isset( $field_config['subsets'] ) ) {
					$control_args['subsets'] = $field_config['subsets'];
				}

				if ( isset( $field_config['recommended'] ) ) {
					$control_args['recommended'] = array_flip( $field_config['recommended'] );
				}

				if ( isset( $field_config['load_all_weights'] ) ) {
					$control_args['load_all_weights'] = $field_config['load_all_weights'];
				}

				if ( isset( $field_config['default'] ) ) {
					$control_args['default'] = $field_config['default'];
				}

				if ( isset( $field_config['fields'] ) ) {
					$control_args['fields'] = $field_config['fields'];
				}
				$control_args['live'] = true;

				break;

			case 'select2' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				$control_class_name = 'Pix_Customize_Select2_Control';
				break;
				
			case 'sm_radio' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				$control_class_name = 'Pix_Customize_SM_radio_Control';
				break;

			case 'sm_palette_filter' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				$control_class_name = 'Pix_Customize_SM_palette_filter_Control';
				break;
				
			case 'sm_switch' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				$control_class_name = 'Pix_Customize_SM_switch_Control';
				break;

			case 'preset' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				if ( isset( $field_config['choices_type'] ) || ! empty( $field_config['choices_type'] ) ) {
					$control_args['choices_type'] = $field_config['choices_type'];
				}

				if ( isset( $field_config['desc'] ) || ! empty( $field_config['desc'] ) ) {
					$control_args['description'] = $field_config['desc'];
				}


				$control_class_name = 'Pix_Customize_Preset_Control';
				break;

			case 'radio_image' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				if ( isset( $field_config['choices_type'] ) || ! empty( $field_config['choices_type'] ) ) {
					$control_args['choices_type'] = $field_config['choices_type'];
				}

				if ( isset( $field_config['desc'] ) || ! empty( $field_config['desc'] ) ) {
					$control_args['description'] = $field_config['desc'];
				}


				$control_class_name = 'Pix_Customize_Radio_Image_Control';
				break;

			case 'button' :
				if ( ! isset( $field_config['action'] ) || empty( $field_config['action'] ) ) {
					return;
				}

				$control_args['action'] = $field_config['action'];

				$control_class_name = 'Pix_Customize_Button_Control';

				break;

			case 'html' :
				if ( isset( $field_config['html'] ) || ! empty( $field_config['html'] ) ) {
					$control_args['html'] = $field_config['html'];
				}

				$control_class_name = 'Pix_Customize_HTML_Control';
				break;

			case 'import_demo_data' :
				if ( isset( $field_config['html'] ) || ! empty( $field_config['html'] ) ) {
					$control_args['html'] = $field_config['html'];
				}

				if ( ! isset( $field_config['label'] ) || empty( $field_config['label'] ) ) {
					$control_args['label'] = esc_html__( 'Import', 'customify' );
				} else {
					$control_args['label'] = $field_config['label'];
				}

				if ( isset( $field_config['notices'] ) && ! empty( $field_config['notices'] ) ) {
					$control_args['notices'] = $field_config['notices'];
				}

				$control_class_name = 'Pix_Customize_Import_Demo_Data_Control';
				break;

			default:
				// if we don't have a real control just quit, it doesn't even matter
				return;
				break;
		}

		$this_control = new $control_class_name(
			$wp_customize,
			$setting_id . '_control',
			$control_args
		);

		if ( $add_control ) {
			$wp_customize->add_control( $this_control );
		}
	}

	/**
	 * Remove the sections selected by user
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	function remove_default_sections( $wp_customize ) {
		global $wp_registered_sidebars;

		$to_remove = $this->get_plugin_setting( 'disable_default_sections' );

		if ( ! empty( $to_remove ) ) {
			foreach ( $to_remove as $section => $nothing ) {

				if ( $section === 'widgets' ) {
					foreach ( $wp_registered_sidebars as $widget => $settings ) {
						$wp_customize->remove_section( 'sidebar-widgets-' . $widget );
					}
					continue;
				}

				$wp_customize->remove_section( $section );
			}
		}
	}

	/**
	 * Print JavaScript for adding additional data to _wpCustomizeSettings.settings object of the main window (not the preview window).
	 */
	public function customize_pane_settings_additional_data() {
		/**
		 * @global WP_Customize_Manager $wp_customize
		 */
		global $wp_customize;

		// Without an options name we can't do much.
		if ( empty( $this->customizer_config['opt-name'] ) ) {
			return;
		}

		$options_name = $this->customizer_config['opt-name'];
		$customizer_settings = $wp_customize->settings();
		?>
		<script type="text/javascript">
            if ( 'undefined' === typeof _wpCustomizeSettings.settings ) {
                _wpCustomizeSettings.settings = {};
            }

			<?php
			echo "(function ( sAdditional ){\n";

			$options = $this->get_options();
			foreach ( $options as $option_id => $option_config ) {
				// If we have been explicitly given a setting ID we will use that
				if ( ! empty( $option_config['setting_id'] ) ) {
					$setting_id = $option_config['setting_id'];
				} else {
					$setting_id = $options_name . '[' . $option_id . ']';
				}
				// @todo Right now we only handle the connected_fields key - make this more dynamic by adding the keys that are not returned by WP_Customize_Setting->json()
				if ( ! empty( $customizer_settings[ $setting_id ] ) && ! empty( $option_config['connected_fields'] ) ) {
					// Pass through all the connected fields and make sure the id is in the final format
					$connected_fields = array();
					foreach ( $option_config['connected_fields'] as $key => $connected_field_config ) {
						$connected_field_data = array();

						if ( is_string( $connected_field_config ) ) {
							$connected_field_id = $connected_field_config;
						} elseif ( is_array( $connected_field_config ) ) {
							// We have a full blown connected field config
							if ( is_string( $key ) ) {
								$connected_field_id = $key;
							} else {
								continue;
							}

							// We will pass to JS all the configured connected field details.
							$connected_field_data = $connected_field_config;
						}

						// Continue if we don't have a connected field ID to work with.
						if ( empty( $connected_field_id ) ) {
							continue;
						}

						// If the connected setting is not one of our's, we will use it's ID as it is.
						if ( ! array_key_exists( $connected_field_id, $options ) ) {
							$connected_field_data['setting_id'] = $connected_field_id;
						}
						// If the connected setting specifies a setting ID, we will not prefix it and use it as it is.
						elseif ( ! empty( $options[ $connected_field_id ] ) && ! empty( $options[ $connected_field_id ]['setting_id'] ) ) {
							$connected_field_data['setting_id'] = $options[ $connected_field_id ]['setting_id'];
						} else {
							$connected_field_data['setting_id'] = $options_name . '[' . $connected_field_id . ']';
						}

						$connected_fields[] = $connected_field_data;
					}

					printf(
						"sAdditional[%s].%s = %s;\n",
						wp_json_encode( $setting_id ),
						'connected_fields',
						wp_json_encode( $connected_fields, JSON_FORCE_OBJECT )
					);
				}
			}
			echo "})( _wpCustomizeSettings.settings );\n";
			?>
		</script>
		<?php
	}

	public function get_file() {
		return $this->file;
	}

	public function get_base_path() {
		return plugin_dir_path( $this->file );
	}

	public function get_typography_fields( $array, $key, $value, &$results, $input_key = 0 ) {
		if ( ! is_array( $array ) ) {
			return;
		}

		if ( isset( $array[ $key ] ) && $array[ $key ] == $value ) {
			$results[ $input_key ] = $array;

			$default = null;

			if ( isset( $array['default'] ) && is_array( $array['default'] ) ) {
				$default = json_encode( $array['default'] );
			}

			$results[ $input_key ]['value'] = $this->get_option( $input_key, $default );
		}

		foreach ( $array as $i => $subarray ) {
			$this->get_typography_fields( $subarray, $key, $value, $results, $i );
		}
	}

	function get_options_key() {
		if ( ! empty( $this->opt_name ) ) {
			return $this->opt_name;
		}

		return false;
	}

	/**
	 * Use this function when you need to know if an import button is used
	 * @return bool
	 */
	function import_button_exists() {

		if ( empty( $this->options_list ) ) {
			$this->options_list = $this->get_options();
		}

		foreach ( $this->options_list as $option ) {
			if ( isset( $option['type'] ) && 'import_demo_data' === $option['type'] ) {
				return true;
				break;
			}
		}

		return false;
	}

	/** == Helpers == */

	protected function get_current_values( $opt_name = null ) {
		// Fallback to the global $opt_name
		if ( empty( $opt_name ) ) {
			$opt_name = $this->opt_name;
		}

		// Bail as we have nothing to work with
		if ( empty( $opt_name ) ) {
			return false;
		}

		$store_type = $this->get_plugin_setting( 'values_store_mod', 'option' );
		if ( $store_type === 'option' ) {
			return get_option( $opt_name );
		} elseif ( $store_type === 'theme_mod' ) {
			return get_theme_mod( $opt_name );
		}

		return false;
	}

	public function get_options() {

		$settings = array();

		if ( isset ( $this->customizer_config['panels'] ) ) {

			foreach ( $this->customizer_config['panels'] as $pane_id => $panel_settings ) {

				if ( isset( $panel_settings['sections'] ) ) {
					foreach ( $panel_settings['sections'] as $section_id => $section_settings ) {
						if ( isset( $section_settings['options'] ) ) {
							foreach ( $section_settings['options'] as $option_id => $option ) {
								$settings[ $option_id ] = $option;
								if ( isset( $this->current_values[ $option_id ] ) ) {
									$settings[ $option_id ]['value'] = $this->current_values[ $option_id ];
								}
							}
						}
					}
				}
			}
		}

		if ( isset ( $this->customizer_config['sections'] ) ) {
			foreach ( $this->customizer_config['sections'] as $section_id => $section_settings ) {
				if ( isset( $section_settings['options'] ) ) {
					foreach ( $section_settings['options'] as $option_id => $option ) {
						$settings[ $option_id ] = $option;
						if ( isset( $this->current_values[ $option_id ] ) ) {
							$settings[ $option_id ]['value'] = $this->current_values[ $option_id ];
						}
					}
				}
			}
		}

		return $settings;
	}

	protected function get_value( $option_id, $alt_opt_name ) {
		global $wp_customize;

		$options_name = $this->opt_name;
		$values   = $this->current_values;

		// In case someone asked for a DB value too early but it has given us the options_name under which to search, let's do it
		if ( empty( $options_name ) && ! empty( $alt_opt_name ) ) {
			$options_name = $alt_opt_name;
			$values   = $this->get_current_values( $options_name );
		}

		if ( ! empty( $wp_customize ) && method_exists( $wp_customize, 'get_setting' ) ) {
			// Get the field config.
			$option_config = $this->get_option_customizer_config( $option_id );

			if ( empty( $option_id ) || ! isset( $option_config['type'] ) ) {
				return null;
			}

			// If we have been explicitly given a setting ID we will use that
			if ( ! empty( $option_config['setting_id'] ) ) {
				$setting_id = $option_config['setting_id'];
			} else {
				$setting_id = $options_name . '[' . $option_id . ']';
			}

			$setting    = $wp_customize->get_setting( $setting_id );
			if ( ! empty( $setting ) ) {
				return $setting->value();
			}
		}

		// shim
		if ( strpos( $option_id, $options_name . '[' ) !== false ) {
			// get only the setting id
			$option_id = explode( '[', $option_id );
			$option_id = rtrim( $option_id[1], ']' );
		}

		if ( isset( $values[ $option_id ] ) ) {
			return $values[ $option_id ];
		}

		return null;
	}

	/**
	 * A public function to get an option's value.
	 * If there is a value and return it.
	 * Otherwise try to get the default parameter or the default from config.
	 *
	 * @param $option_id
	 * @param mixed $default Optional.
	 * @param string $alt_opt_name Optional. We can use this to bypass the fact that the DB values are loaded on after_setup_theme, if we know what to look for.
	 *
	 * @return bool|null|string
	 */
	public function get_option( $option_id, $default = null, $alt_opt_name = null ) {
		// If the development constant CUSTOMIFY_DEV_FORCE_DEFAULTS has been defined we will not retrieve anything from the database
		// Always go with the default
		if ( defined( 'CUSTOMIFY_DEV_FORCE_DEFAULTS' ) && true === CUSTOMIFY_DEV_FORCE_DEFAULTS && ! $this->skip_dev_mode_force_defaults( $option_id ) ) {
			$return = null;
		} else {
			// Get the field config.
			$option_config = $this->get_option_customizer_config( $option_id );

			if ( empty( $option_id ) ) {
				$return = null;
			} elseif ( isset( $option_config['setting_type'] ) && $option_config['setting_type'] === 'option' ) {
				// We have a setting that is saved in the wp_options table, not in theme_mods.
				// We will fetch it directly.

				// If we have been explicitly given a setting ID we will use that
				if ( ! empty( $option_config['setting_id'] ) ) {
					$setting_id = $option_config['setting_id'];
				} else {
					$setting_id = $this->opt_name . '[' . $option_id . ']';
				}

				$return = get_option( $setting_id, null );
			} else {
				// Get the value stores in theme_mods.
				$return = $this->get_value( $option_id, $alt_opt_name );
			}
		}

		if ( $return !== null ) {
			return $return;
		} elseif ( $default !== null ) {
			return $default;
		} elseif ( isset( $this->options_list[ $option_id ] ) && isset( $this->options_list[ $option_id ]['default'] ) ) {
			return $this->options_list[ $option_id ]['default'];
		}

		return null;
	}

	public function has_option( $option ) {

		if ( isset( $this->options_list[ $option ] ) ) {
			return true;
		}

		return false;
	}

	public function get_customizer_config() {
		return $this->customizer_config;
	}

	/**
	 * Get the Customify configuration of a certain option.
	 *
	 * @param string $option_id
	 *
	 * @return array|false The option config or false on failure.
	 */
	public function get_option_customizer_config( $option_id ) {
		// We need to search for the option configured under the given id (the array key)
		if ( isset ( $this->customizer_config['panels'] ) ) {
			foreach ( $this->customizer_config['panels'] as $panel_id => $panel_settings ) {
				if ( isset( $panel_settings['sections'] ) ) {
					foreach ( $panel_settings['sections'] as $section_id => $section_settings ) {
						if ( isset( $section_settings['options'] ) ) {
							foreach ( $section_settings['options'] as $id => $option_config ) {
								if ( $id === $option_id ) {
									return $option_config;
								}
							}
						}
					}
				}
			}
		}

		if ( isset ( $this->customizer_config['sections'] ) ) {
			foreach ( $this->customizer_config['sections'] as $section_id => $section_settings ) {
				if ( isset( $section_settings['options'] ) ) {
					foreach ( $section_settings['options'] as $id => $option_config ) {
						if ( $id === $option_id ) {
							return $option_config;
						}
					}
				}
			}
		}

		return false;
	}

	protected function get_config() {
		if ( file_exists( $this->get_base_path() . 'plugin-config.php' ) ) {
			return include( $this->get_base_path() . 'plugin-config.php' );
		}

		return false;
	}

	/**
	 * Get an option's value from the config file
	 *
	 * @param $option
	 * @param null $default
	 *
	 * @return bool|null
	 */
	public function get_config_option( $option, $default = null ) {

		if ( isset( $this->config[ $option ] ) ) {
			return $this->config[ $option ];
		} elseif ( $default !== null ) {
			return $default;
		}

		return false;
	}

	public function get_plugin_setting( $option, $default = null ) {

		if ( isset( $this->plugin_settings[ $option ] ) ) {
			return $this->plugin_settings[ $option ];
		} elseif ( $default !== null ) {
			return $default;
		}

		return false;
	}

	/**
	 * Sanitize functions
	 */

	/**
	 * Sanitize the checkbox.
	 *
	 * @param boolean $input .
	 *
	 * @return boolean true if is 1 or '1', false if anything else
	 */
	function setting_sanitize_checkbox( $input ) {
		if ( 1 == $input ) {
			return true;
		} else {
			return false;
		}
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

	function register_import_api() {

		include_once( $this->get_base_path() . '/features/class-Customify_Importer.php' );
		$controller = new Customify_Importer_Controller();
		$controller->init();
	}

	public function get_options_configs() {
		return $this->options_list;
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
		} else {
			var_dump( 'boooom' );
			die;
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

	/**
	 * Prevent saving of plugin options in the Customizer
	 *
	 * @param array $data The data to save
	 * @param array $filter_context
	 *
	 * @return array
	 */
	public function prevent_changeset_save_in_devmode( $data, $filter_context ) {
		// Get the options key
		$options_key = $this->customizer_config['opt-name'];
		if ( ! empty( $options_key ) ) {
			// Remove any Customify data thus preventing it from saving
			foreach ( $data as $option_id => $value ) {
				if ( false !== strpos( $option_id, $options_key ) && ! $this->skip_dev_mode_force_defaults( $option_id ) ) {
					unset( $data[ $option_id ] );
				}
			}
		}

		return $data;
	}

	/**
	 * Determine if we should NOT enforce the CUSTOMIFY_DEV_FORCE_DEFAULTS behavior on a certain option.
	 *
	 * @param string $option_id
	 *
	 * @return bool
	 */
	private function skip_dev_mode_force_defaults( $option_id ) {
		// Preprocess the $option_id.
		if ( false !== strpos( $option_id, '::' ) ) {
			$option_id = substr( $option_id, strpos( $option_id, '::' ) + 2 );
		}
		if ( false !== strpos( $option_id, '[' ) ) {
			$option_id = explode( '[', $option_id );
			$option_id = rtrim( $option_id[1], ']' );
		}

		$option_config = $this->get_option_customizer_config( $option_id );
		if ( empty( $option_config ) ) {
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

	public function prevent_changeset_save_in_devmode_notification() { ?>
		<script type="application/javascript">
            (function ( $, exports, wp ) {
                'use strict';
                // when the customizer is ready add our notification
                wp.customize.bind('ready', function () {
                    wp.customize.notifications.add( 'customify_force_defaults', new wp.customize.Notification(
                        'customify_force_defaults',
                        {
                            type: 'warning',
                            message: '<strong style="margin-bottom: ">Customify: Development Mode</strong><p>All the options are switched to default. While they are changing in the live preview, they will not be kept when publish.</p>'
                        }
                    ) );
                });
            })(jQuery, window, wp);
		</script>
	<?php }

	/**
	 * Return an instance of this class.
	 * @since     1.0.0
	 * @return    PixCustomifyPlugin    A single instance of this class.
	 */
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

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ), esc_html( $this->_version ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.5.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ), esc_html( $this->_version ) );
	} // End __wakeup ()
}