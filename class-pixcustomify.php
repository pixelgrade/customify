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

	/**
	 * Path to the plugin.
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_basepath = null;

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

		// Remember our base path
		$this->plugin_basepath = plugin_dir_path( $file );

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

		// Register all the needed hooks
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 *
	 * @return null
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
		add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_scripts' ), 10 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_scripts' ), 10 );

		// Scripts enqueued only in the theme preview
		add_action( 'customize_preview_init', array( $this, 'customizer_live_preview_register_scripts' ), 10 );
		add_action( 'customize_preview_init', array( $this, 'customizer_live_preview_enqueue_scripts' ), 99999 );

		// The frontend effects of the Customizer controls
		$load_location = $this->get_plugin_setting( 'style_resources_location', 'wp_head' );

		add_action( $load_location, array( $this, 'output_dynamic_style' ), 99 );
		add_action( 'wp_head', array( $this, 'output_typography_dynamic_style' ), 10 );

		add_action( 'customize_register', array( $this, 'remove_default_sections' ), 11 );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 12 );

		if ( $this->get_plugin_setting( 'enable_editor_style', true ) ) {
			add_action( 'admin_head', array( $this, 'add_customizer_settings_into_wp_editor' ) );
		}

		/*
		 * Jetpack Related
		 */
		add_action( 'init', array( $this, 'set_jetpack_sharing_config') );
		add_filter( 'default_option_jetpack_active_modules', array( $this, 'default_jetpack_active_modules' ), 10, 1 );
		add_filter( 'jetpack_get_available_modules', array( $this, 'jetpack_hide_blocked_modules' ), 10, 1 );
		add_filter( 'default_option_sharing-options', array( $this, 'default_jetpack_sharing_options' ), 10, 1 );

		add_action( 'rest_api_init', array( $this, 'add_rest_routes_api' ) );
	}

	/**
	 * Initialize Configs, Options and Values methods
	 */
	function init_plugin_configs() {
		$this->customizer_config = get_option( 'pixcustomify_config' );

		// no option so go for default
		if ( empty( $this->customizer_config ) ) {
			$this->customizer_config = $this->get_config_option( 'default_options' );
		}

		if ( empty( $this->customizer_config ) ) {
			$this->customizer_config = array();
		}
	}

	/**
	 * Load the plugin configuration and options
	 */
	function load_plugin_configs() {

		// allow themes or other plugins to filter the config
		$this->customizer_config = apply_filters( 'customify_filter_fields', $this->customizer_config );
		$this->opt_name          = $this->localized['options_name'] = $this->customizer_config['opt-name'];
		$this->options_list      = $this->get_options();

		// Load the current options values
		$this->current_values = $this->get_current_values();

		if ( $this->import_button_exists() ) {
			$this->localized['import_rest_url']   = get_rest_url( '/customify/1.0/' );
			$this->localized['import_rest_nonce'] = wp_create_nonce( 'wp_rest' );

			$this->register_import_api();
		}

		$this->localized['theme_fonts'] = $this->theme_fonts = Customify_Font_Selector::instance()->get_theme_fonts();
	}

	function set_jetpack_sharing_config() {
		// Allow others to change the sharing config here
		$this->jetpack_sharing_default_options = apply_filters ( 'customify_filter_jetpack_sharing_default_options', array() );
	}

	/**
	 * Control the default modules that are activated in Jetpack.
	 * Use the `customify_filter_jetpack_default_modules` to set your's.
	 *
	 * @param array $default The default value to return if the option does not exist
	 *                        in the database.
	 *
	 * @return array
	 */
	function default_jetpack_active_modules( $default ) {
		if ( ! is_array( $default ) ) {
			$default = array();
		}
		$jetpack_default_modules = array();

		$theme_default_modules = get_theme_mod( 'pixelgrade_jetpack_default_active_modules', array() );

		if ( ! is_array( $theme_default_modules ) ) {
			return array_merge( $default, $jetpack_default_modules );
		}

		foreach ( $theme_default_modules as $module ) {
			array_push( $jetpack_default_modules, $module );
		}

		return array_merge( $default, $jetpack_default_modules );
	}

	/**
	 * Control the default Jetpack Sharing options.
	 * Use the `customify_filter_jetpack_sharing_default_options` to set your's.
	 *
	 * @param array $default The default value to return if the option does not exist
	 *                        in the database.
	 *
	 * @return array
	 */
	function default_jetpack_sharing_options( $default ) {
		if ( ! is_array( $default ) ) {
			$default = array();
		}

		return array_merge( $default, $this->jetpack_sharing_default_options );
	}

	/**
	 * Control the modules that are available in Jetpack (hide some of them).
	 * Use the `customify_filter_jetpack_blocked_modules` filter to set your's.
	 *
	 * @param array $modules
	 *
	 * @return array
	 */
	function jetpack_hide_blocked_modules( $modules ) {
		return array_diff_key( $modules, array_flip( $this->jetpack_blocked_modules ) );
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
		wp_register_script( $this->plugin_slug . '-customizer-scripts', plugins_url( 'js/customizer.js', $this->file ), array(
			'jquery',
			'customify_select2',
			'underscore',
			'customize-controls'
		), $this->_version );
	}

	/**
	 * Enqueue Customizer admin scripts
	 */
	function enqueue_admin_customizer_scripts() {
		wp_enqueue_script( 'jquery-react' );
		wp_enqueue_script( $this->plugin_slug . '-customizer-scripts' );

		wp_localize_script( $this->plugin_slug . '-customizer-scripts', 'customify_settings', $this->localized );
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
		register_rest_route( 'customfiy/v1', '/delete_theme_mod', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'delete_theme_mod' ),
			'permission_callback' => array( $this, 'permission_nonce_callback' ),
		) );
	}

	function delete_theme_mod(){
		$user = wp_get_current_user();
		if ( ! $user->caps['administrator'] ) {
			wp_send_json_error('no admin');
		}

		$config = apply_filters('customify_filter_fields', array() );

		if ( empty( $config['opt-name'] ) ) {
			wp_send_json_error('no option key');
		}

		$key = $config['opt-name'];

		remove_theme_mod( $key );

		wp_send_json_success('Bby ' . $key . '!');
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
		$custom_css = "\n";

		foreach ( $this->options_list as $option_id => $option ) {

			if ( isset( $option['css'] ) && ! empty( $option['css'] ) ) {
				// now process each
				$custom_css .= $this->convert_setting_to_css( $option_id, $option['css'] );
			}

			if ( $option['type'] === 'custom_background' ) {
				$option['value']         = $this->get_option( $option_id );
				$custom_css .= $this->process_custom_background_field_output( $option_id, $option );
			}
		}

		if ( ! empty( $this->media_queries ) ) {

			foreach ( $this->media_queries as $media_query => $properties ) {

				if ( empty( $properties ) ) {
					continue;
				}

				$custom_css .= '@media ' . $media_query . " { ";

				foreach ( $properties as $key => $property ) {
					$property_settings = $property['property'];
					$property_value    = $property['value'];
					$custom_css .= $this->proccess_css_property( $property_settings, $property_value );
				}

				$custom_css .= " }\n";

			}
		}

		$custom_css .= "\n";
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

			if ( $options['type'] === 'custom_background' ) {
				$options['value']         = $this->get_option( $option_id );
				$custom_background_output = $this->process_custom_background_field_output( $option_id, $options ); ?>

				<style id="custom_backgorund_output_for_<?php echo $option_id; ?>">
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
			foreach ( $options['css'] as $key => $properties_set ) { ?>
				<style id="dynamic_setting_<?php echo $option_id . '_property_' . str_replace( '-', '_', $properties_set['property'] ); ?>"
				       type="text/css"><?php

					if ( isset( $properties_set['media'] ) && ! empty( $properties_set['media'] ) ) {
						echo '@media '. $properties_set['media'] . " {\n";
					}

					if ( isset( $properties_set['selector'] ) && isset( $properties_set['property'] ) ) {
						echo $this->proccess_css_property($properties_set, $this_value);
					}

					if ( isset( $properties_set['media'] ) && ! empty( $properties_set['media'] ) ) {
						echo "}\n";
					} ?>
				</style>
			<?php }
		}

		if ( ! empty( $this->media_queries ) ) {

			foreach ( $this->media_queries as $media_query => $properties ) {

				if ( empty( $properties ) ) {
					continue;
				}

				$display = false;
				$media_q = '@media ' . $media_query . " {\n";

				foreach ( $properties as $key => $property ) {

					if ( ! isset( $options['live'] ) || $options['live'] !== true ) {
						continue;
					}

					$display = true; ?>
					<style id="dynamic_setting_<?php echo $key; ?>" type="text/css"><?php
						$property_settings = $property['property'];
						$property_value    = $property['value'];
						$media_q .= "\t" . $this->proccess_css_property( $property_settings, $property_value );?>
					</style>
				<?php }

				$media_q .= "\n}\n";

				if ( $display ) {
					$custom_css .= $media_q;
				}
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
				if ( is_array( $font['value'] ) ) {
					$font['value'] = stripslashes_deep( $font['value'] );
					$font['value'] = json_encode( $font['value'] );
				}

				$value = json_decode( wp_unslash( PixCustomifyPlugin::decodeURIComponent( $font['value'] ) ), true );

				// in case the value is still null, try default value(mostly for google fonts)
				if ( ! is_array( $value ) || $value === null ) {
					$value = $this->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
				}

				//bail if by this time we don't have a value of some sort
				if ( empty( $value ) ) {
					continue;
				}

				//Handle special logic for when the $value array is not an associative array
				if ( ! $this->is_assoc( $value ) ) {
					$value = $this->process_a_not_associative_font_default( $value );
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

					$value = json_decode( PixCustomifyPlugin::decodeURIComponent( $font['value'] ), true );
					// in case the value is still null, try default value(mostly for google fonts)
					if ( $value === null ) {
						$value = $this->get_font_defaults_value( $font['value'] );
					}

					// shim the old case when the default was only the font name
					if ( is_string( $value ) && ! empty( $value ) ) {
						$value = array( 'font_family' => $value );
					}

					//Handle special logic for when the $value array is not an associative array
					if ( ! $this->is_assoc( $value ) ) {
						$value = $this->process_a_not_associative_font_default( $value );
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
						//the variant's font-family
						echo $font['selector'] . " {\nfont-family: " . $selected_variant['font-family'] . ";\n";

						if ( ! $load_all_weights ) {
							// if this is a custom font (like from our plugin Fonto) with individual styles & weights - i.e. the font-family says it all
							// we need to "force" the font-weight and font-style
							if ( ! empty( $value['type'] ) && 'custom_individual' == $value['type'] ) {
								$selected_variant['font-weight'] = '400 !important';
								$selected_variant['font-style'] = 'normal !important';
							}

							// output the font weight, if available
							if ( ! empty( $selected_variant['font-weight'] ) ) {
								echo "font-weight: " . $selected_variant['font-weight'] . ";\n";
							}

							// output the font style, if available
							if ( ! empty( $selected_variant['font-style'] ) ) {
								echo "font-style: " . $selected_variant['font-style'] . ";\n";
							}
						}

						echo "}\n";
					} elseif ( isset( $value['font_family'] ) ) {
						// the selected font family
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
	public function process_a_not_associative_font_default( $value ) {

		$new_value = array();

		//Let's determine some type of font
		if ( ! isset( $value[2] ) || ( isset( $value[2] ) && 'google' == $value[2] ) ) {
			$new_value = $this->get_font_defaults_value( $value[0] );
		} else {
			$new_value['type'] = $value[2];
		}

		if ( null == $new_value ) {
			$new_value = array();
		}

		//The first entry is the font-family
		if ( isset( $value[0] ) ) {
			$new_value['font_family'] = $value[0];
		}

		//In case we don't have an associative array
		//The second entry is the variants
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

			if ( ! isset( $css_property['selector'] ) || isset( $css_property['property'] ) ) {
				$output .= $this->proccess_css_property( $css_property, $this_value );
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

		$this_property_output = $css_property['selector'] . ' { ' . $css_property['property'] . ': ' . $this_value . $unit . "; }\n";

		if ( isset( $css_property['callback_filter'] ) && function_exists( $css_property['callback_filter'] ) ) {
			$this_property_output = call_user_func( $css_property['callback_filter'], $this_value, $css_property['selector'], $css_property['property'], $unit );
		}

		return $this_property_output;
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
					 * @param iframe_id the id of the frame you whant to append the style
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
	 * @param WP_Customize_Manager $wp_customize
	 */
	function register_customizer( $wp_customize ) {

		$this->register_customizer_controls();

		$customizer_settings = $this->customizer_config;

		if ( ! empty ( $customizer_settings ) ) {

			// first check the very needed options name
			if ( ! isset ( $customizer_settings['opt-name'] ) || empty( $customizer_settings['opt-name'] ) ) {
				return;
			}
			$options_name              = $customizer_settings['opt-name'];
			$wp_customize->options_key = $options_name;

			// let's check if we have sections or panels
			if ( isset( $customizer_settings['panels'] ) && ! empty( $customizer_settings['panels'] ) ) {

				foreach ( $customizer_settings['panels'] as $panel_id => $panel_settings ) {

					if ( ! empty( $panel_id ) && isset( $panel_settings['sections'] ) && ! empty( $panel_settings['sections'] ) ) {

						$panel_id   = $options_name . '[' . $panel_id . ']';
						$panel_args = array(
							'priority'    => 10,
							'capability'  => 'edit_theme_options',
							'title'       => __( 'Panel title is required', 'pixcustomify' ),
							'description' => __( 'Description of what this panel does.', 'pixcustomify' ),
						);

						if ( isset( $panel_settings['priority'] ) && ! empty( $panel_settings['priority'] ) ) {
							$panel_args['priority'] = $panel_settings['priority'];
						}

						if ( isset( $panel_settings['title'] ) && ! empty( $panel_settings['title'] ) ) {
							$panel_args['title'] = $panel_settings['title'];
						}

						if ( isset( $panel_settings['description'] ) && ! empty( $panel_settings['description'] ) ) {
							$panel_args[10] = $panel_settings['description'];
						}

						$wp_customize->add_panel( $panel_id, $panel_args );

						foreach ( $panel_settings['sections'] as $section_id => $section_settings ) {
							if ( ! empty( $section_id ) && isset( $section_settings['options'] ) && ! empty( $section_settings['options'] ) ) {
								$this->register_section( $panel_id, $section_id, $options_name, $section_settings, $wp_customize );
							}
						}
					}
				}
			}

			if ( isset( $customizer_settings['sections'] ) && ! empty( $customizer_settings['sections'] ) ) {

				foreach ( $customizer_settings['sections'] as $section_id => $section_settings ) {
					if ( ! empty( $section_id ) && isset( $section_settings['options'] ) && ! empty( $section_settings['options'] ) ) {
						$this->register_section( $panel_id = false, $section_id, $options_name, $section_settings, $wp_customize );
					}
				}
			}

			if ( $this->plugin_settings['enable_reset_buttons'] ) {
				// create a toolbar section which will be present all the time
				$reset_section_settings = array(
					'title'   => 'Customify toolbar',
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
					array(
						'title'    => '',
						'priority' => 999999999
					)
				);

				$wp_customize->add_setting(
					'reset_customify',
					array()
				);
				$wp_customize->add_control( new Pix_Customize_Button_Control(
					$wp_customize,
					'reset_customify',
					array(
						'label'    => __( 'Reset Customify to Defaults', 'customify' ),
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

	protected function register_section( $panel_id, $section_key, $options_name, $section_settings, $wp_customize ) {

		if ( isset( $this->plugin_settings['disable_customify_sections'] ) && isset( $this->plugin_settings['disable_customify_sections'][ $section_key ] ) ) {
			return;
		}

		$section_args = array(
			'priority'   => 10,
			'capability' => 'edit_theme_options',
			'title'      => __( 'Title Section is required', '' ),
			'panel'      => $panel_id,
		);
		$section_id   = $options_name . '[' . $section_key . ']';

		if ( isset( $section_settings['priority'] ) && ! empty( $section_settings['priority'] ) ) {
			$section_args['priority'] = $section_settings['priority'];
		}

		if ( isset( $section_settings['title'] ) && ! empty( $section_settings['title'] ) ) {
			$section_args['title'] = $section_settings['title'];
		}

		if ( isset( $section_settings['theme_supports'] ) && ! empty( $section_settings['theme_supports'] ) ) {
			$section_args['theme_supports'] = $section_settings['theme_supports'];
		}

		if ( isset( $section_settings['description'] ) && ! empty( $section_settings['description'] ) ) {
			$section_args['description'] = $section_settings['description'];
		}

		$wp_customize->add_section( $section_id, $section_args );

		foreach ( $section_settings['options'] as $option_id => $option_config ) {

			if ( empty( $option_id ) || ! isset( $option_config['type'] ) ) {
				continue;
			}

			$option_id = $options_name . '[' . $option_id . ']';

			$this->register_field( $section_id, $option_id, $option_config, $wp_customize );
		}

	}

	/**
	 * @param string $section_id
	 * @param string $setting_id
	 * @param array $setting_config
	 * @param WP_Customize_Manager $wp_customize
	 */
	protected function register_field( $section_id, $setting_id, $setting_config, $wp_customize ) {

		$add_control = true;
		// defaults
		$setting_args = array(
			'default'    => '',
			'capability' => 'edit_theme_options',
			'transport'  => 'refresh',
		);
		$control_args = array(
			'label'    => '',
			'section'  => $section_id,
			'settings' => $setting_id,
		);

		$this->localized['settings'][ $setting_id ] = $setting_config;

		// sanitize settings
		if ( ( isset( $setting_config['live'] ) && $setting_config['live'] ) || $setting_config['type'] === 'font' ) {
			$setting_args['transport'] = 'postMessage';
		}

		if ( isset( $setting_config['default'] ) ) {
			$setting_args['default'] = $setting_config['default'];
		}

		if ( isset( $setting_config['capability'] ) && ! empty( $setting_config['capability'] ) ) {
			$setting_args['capability'] = $setting_config['capability'];
		}

		if ( $this->plugin_settings['values_store_mod'] == 'option' ) {
			$setting_args['type'] = 'option';
		}

		// if we arrive here this means we have a custom field control
		switch ( $setting_config['type'] ) {

			case 'checkbox':

				$setting_args['sanitize_callback'] = array( $this, 'setting_sanitize_checkbox' );
				break;

			default:
				break;
		}

		if ( isset( $setting_config['sanitize_callback'] ) && ! empty( $setting_config['sanitize_callback'] ) && function_exists( $setting_config['sanitize_callback'] ) ) {
			$setting_args['sanitize_callback'] = $setting_config['sanitize_callback'];
		}

		// and add it
		$wp_customize->add_setting( $setting_id, $setting_args );

		// now sanitize the control
		if ( isset( $setting_config['label'] ) && ! empty( $setting_config['label'] ) ) {
			$control_args['label'] = $setting_config['label'];
		}

		if ( isset( $setting_config['priority'] ) && ! empty( $setting_config['priority'] ) ) {
			$control_args['priority'] = $setting_config['priority'];
		}

		if ( isset( $setting_config['desc'] ) && ! empty( $setting_config['desc'] ) ) {
			$control_args['description'] = $setting_config['desc'];
		}

		if ( isset( $setting_config['active_callback'] ) && ! empty( $setting_config['active_callback'] ) ) {
			$control_args['active_callback'] = $setting_config['active_callback'];
		}


		$control_args['type'] = $setting_config['type'];

		// select the control type
		// but first init a default
		$control_class_name = 'Pix_Customize_Text_Control';

		// if is a standard wp field type call it here and skip the rest
		if ( in_array( $setting_config['type'], array(
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
		} elseif ( in_array( $setting_config['type'], array(
				'radio',
				'select'
			) ) && isset( $setting_config['choices'] ) && ! empty( $setting_config['choices'] )
		) {
			$control_args['choices'] = $setting_config['choices'];
			$wp_customize->add_control( $setting_id . '_control', $control_args );

			return;
		} elseif ( in_array( $setting_config['type'], array( 'range' ) ) && isset( $setting_config['input_attrs'] ) && ! empty( $setting_config['input_attrs'] ) ) {

			$control_args['input_attrs'] = $setting_config['input_attrs'];

			$wp_customize->add_control( $setting_id . '_control', $control_args );
		}

		// if we arrive here this means we have a custom field control
		switch ( $setting_config['type'] ) {

			case 'text':
				if ( isset( $setting_config['live'] ) ) {
					$control_args['live'] = $setting_config['live'];
				}

				$control_class_name = 'Pix_Customize_Text_Control';
				break;

			case 'textarea':
				if ( isset( $setting_config['live'] ) ) {
					$control_args['live'] = $setting_config['live'];
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
				if ( isset( $setting_config['live'] ) ) {
					$control_args['live'] = $setting_config['live'];
				}

				if ( isset( $setting_config['editor_type'] ) ) {
					$control_args['editor_type'] = $setting_config['editor_type'];
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
				if ( isset( $setting_config['field'] ) ) {
					$control_args['field'] = $setting_config['field'];
				}

				$control_class_name = 'Pix_Customize_Background_Control';
				break;

			case 'cropped_media':
				if ( isset( $setting_config['width'] ) ) {
					$control_args['width'] = $setting_config['width'];
				}

				if ( isset( $setting_config['height'] ) ) {
					$control_args['height'] = $setting_config['height'];
				}

				if ( isset( $setting_config['flex_width'] ) ) {
					$control_args['flex_width'] = $setting_config['flex_width'];
				}

				if ( isset( $setting_config['flex_height'] ) ) {
					$control_args['flex_height'] = $setting_config['flex_height'];
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

				if ( isset( $setting_config['backup'] ) ) {
					$control_args['backup'] = $setting_config['backup'];
				}

				if ( isset( $setting_config['font_weight'] ) ) {
					$control_args['font_weight'] = $setting_config['font_weight'];
				}

				if ( isset( $setting_config['subsets'] ) ) {
					$control_args['subsets'] = $setting_config['subsets'];
				}

				if ( isset( $setting_config['recommended'] ) ) {
					$control_args['recommended'] = array_flip( $setting_config['recommended'] );
				}

				if ( isset( $setting_config['load_all_weights'] ) ) {
					$control_args['load_all_weights'] = $setting_config['load_all_weights'];
				}

				if ( isset( $setting_config['default'] ) ) {
					$control_args['default'] = $setting_config['default'];
				}

				break;

			// Custom types
			case 'font' :
				$use_typography = $this->get_plugin_setting( 'typography', '1' );

				if ( $use_typography === false ) {
					$add_control = false;
					continue;
				}

				$control_class_name = 'Pix_Customize_Font_Control';

				if ( isset( $setting_config['backup'] ) ) {
					$control_args['backup'] = $setting_config['backup'];
				}

				if ( isset( $setting_config['font_weight'] ) ) {
					$control_args['font_weight'] = $setting_config['font_weight'];
				}

				if ( isset( $setting_config['subsets'] ) ) {
					$control_args['subsets'] = $setting_config['subsets'];
				}

				if ( isset( $setting_config['recommended'] ) ) {
					$control_args['recommended'] = array_flip( $setting_config['recommended'] );
				}

				if ( isset( $setting_config['load_all_weights'] ) ) {
					$control_args['load_all_weights'] = $setting_config['load_all_weights'];
				}

				if ( isset( $setting_config['default'] ) ) {
					$control_args['default'] = $setting_config['default'];
				}

				if ( isset( $setting_config['fields'] ) ) {
					$control_args['fields'] = $setting_config['fields'];
				}
				$control_args['live'] = true;

				break;

			case 'select2' :
				if ( ! isset( $setting_config['choices'] ) || empty( $setting_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $setting_config['choices'];

				$control_class_name = 'Pix_Customize_Select2_Control';
				break;

			case 'preset' :
				if ( ! isset( $setting_config['choices'] ) || empty( $setting_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $setting_config['choices'];

				if ( isset( $setting_config['choices_type'] ) || ! empty( $setting_config['choices_type'] ) ) {
					$control_args['choices_type'] = $setting_config['choices_type'];
				}

				if ( isset( $setting_config['desc'] ) || ! empty( $setting_config['desc'] ) ) {
					$control_args['description'] = $setting_config['desc'];
				}


				$control_class_name = 'Pix_Customize_Preset_Control';
				break;

			case 'radio_image' :
				if ( ! isset( $setting_config['choices'] ) || empty( $setting_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $setting_config['choices'];

				if ( isset( $setting_config['choices_type'] ) || ! empty( $setting_config['choices_type'] ) ) {
					$control_args['choices_type'] = $setting_config['choices_type'];
				}

				if ( isset( $setting_config['desc'] ) || ! empty( $setting_config['desc'] ) ) {
					$control_args['description'] = $setting_config['desc'];
				}


				$control_class_name = 'Pix_Customize_Radio_Image_Control';
				break;

			case 'button' :
				if ( ! isset( $setting_config['action'] ) || empty( $setting_config['action'] ) ) {
					return;
				}

				$control_args['action'] = $setting_config['action'];

				$control_class_name = 'Pix_Customize_Button_Control';

				break;

			case 'html' :
				if ( isset( $setting_config['html'] ) || ! empty( $setting_config['html'] ) ) {
					$control_args['html'] = $setting_config['html'];
				}

				$control_class_name = 'Pix_Customize_HTML_Control';
				break;

			case 'import_demo_data' :
				if ( isset( $setting_config['html'] ) || ! empty( $setting_config['html'] ) ) {
					$control_args['html'] = $setting_config['html'];
				}

				if ( ! isset( $setting_config['label'] ) || empty( $setting_config['label'] ) ) {
					$control_args['label'] = esc_html__( 'Import', 'customify' );
				} else {
					$control_args['label'] = $setting_config['label'];
				}

				if ( isset( $setting_config['notices'] ) && ! empty( $setting_config['notices'] ) ) {
					$control_args['notices'] = $setting_config['notices'];
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

	protected function get_value( $option, $alt_opt_name ) {
		global $wp_customize;

		$opt_name = $this->opt_name;
		$values   = $this->current_values;

		// In case someone asked for a DB value too early but it has given us the opt_name under which to search, let's do it
		if ( empty( $opt_name ) && ! empty( $alt_opt_name ) ) {
			$opt_name = $alt_opt_name;
			$values   = $this->get_current_values( $opt_name );
		}

		if ( ! empty( $wp_customize ) && method_exists( $wp_customize, 'get_setting' ) ) {

			$option_key = $opt_name . '[' . $option . ']';
			$setting    = $wp_customize->get_setting( $option_key );
			if ( ! empty( $setting ) ) {
				$value = $setting->value();

				return $value;
			}
		}

		// shim
		if ( strpos( $option, $opt_name . '[' ) !== false ) {
			// get only the setting id
			$option = explode( '[', $option );
			$option = rtrim( $option[1], ']' );
		}

		if ( isset( $values[ $option ] ) ) {
			return $values[ $option ];
		}

		return null;
	}

	/**
	 * A public function to retreat an option's value
	 * If there is a value and return it
	 * Otherwise try to get the default parameter or the default from config
	 *
	 * @param $option
	 * @param mixed $default Optional.
	 * @param string $alt_opt_name Optional. We can use this to bypass the fact that the DB values are loaded on after_setup_theme, if we know what to look for.
	 *
	 * @return bool|null|string
	 */
	public function get_option( $option, $default = null, $alt_opt_name = null ) {

		$return = $this->get_value( $option, $alt_opt_name );

		if ( $return !== null ) {
			return $return;
		} elseif ( $default !== null ) {
			return $default;
		} elseif ( isset( $this->options_list[ $option ] ) && isset( $this->options_list[ $option ]['default'] ) ) {
			return $this->options_list[ $option ]['default'];
		}

		return null;
	}

	public function has_option( $option ) {

		if ( isset( $this->options_list[ $option ] ) ) {
			return true;
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

	function get_options_configs() {
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
		//if we get an array we just let it be
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
	 * @return object Main PixCustomifyPlugin instance
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