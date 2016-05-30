<?php
/**
 * PixCustomify.
 * @package   PixCustomify
 * @author    Pixelgrade <contact@pixelgrade.com>
 * @license   GPL-2.0+
 * @link      http://pixelgrade.com
 * @copyright 2014 Pixelgrade
 */

/**
 * Plugin class.
 * @package   PixCustomify
 * @author    Pixelgrade <contact@pixelgrade.com>
 */
class PixCustomifyPlugin {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 * @since   1.0.0
	 * @const   string
	 */
	protected $version = '1.2.3';
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
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

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

	protected static $config;

	protected static $customizer_config;

	public static $plugin_settings;

	protected static $localized = array();

	protected static $current_values = array();

	protected static $options_list = array();

	protected static $media_queries = array();

	protected static $customizer_values;

	protected static $opt_name;

	protected static $typo_settings;

	protected static $google_fonts = null;

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

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 * @since     1.0.0
	 */
	protected function __construct() {

		$this->plugin_basepath = plugin_dir_path( __FILE__ );
		self::$config          = self::get_config();
		self::$plugin_settings = get_option( 'pixcustomify_settings' );

		self::check_for_customizer_values();

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_loaded', array( $this, 'init_plugin_configs' ), 5 );

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . 'pixcustomify.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );


		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

//		add_action( 'plugins_loaded', array( $this, 'register_metaboxes' ), 14 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_styles' ), 10 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_scripts' ), 10 );
		add_action( 'customize_preview_init', array( $this, 'customizer_live_preview_enqueue_scripts' ), 99999 );

		$load_location = self::get_plugin_option( 'style_resources_location', 'wp_head' );

		add_action( $load_location, array( $this, 'output_dynamic_style' ), 99999 );
		add_action( 'wp_head', array( $this, 'output_typography_dynamic_style' ), 10 );

		// add things to the previewer
//		add_action( 'customize_preview_init', array( $this, 'customize_preview_js' ) );

		add_action( 'customize_register', array( $this, 'remove_default_sections' ), 11 );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 12 );

		if ( self::get_plugin_option( 'enable_editor_style', true ) ) {
			add_action( 'admin_head', array( $this, 'add_customizer_settings_into_wp_editor' ) );
		}

		/**
		 * Ajax Callbacks
		 */
//		add_action( 'wp_ajax_pixcustomify_image_click', array( &$this, 'ajax_click_on_photo' ) );
//		add_action( 'wp_ajax_nopriv_pixcustomify_image_click', array( &$this, 'ajax_click_on_photo' ) );
	}

	/**
	 * Return an instance of this class.
	 * @since     1.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Configs, Options and Values methods
	 */

	function init_plugin_configs() {
		self::$customizer_config = get_option( 'pixcustomify_config' );

		// no option so go for default
		if ( empty( self::$customizer_config ) ) {
			self::$customizer_config = self::get_config_option( 'default_options' );
		}

		if ( empty( self::$customizer_config ) ) {
			self::$customizer_config = array();
		}

		// allow themes or other plugins to filter the config
		self::$customizer_config = apply_filters( 'customify_filter_fields', self::$customizer_config );

		self::$opt_name = self::$localized['options_name'] = self::$customizer_config['opt-name'];

		self::get_current_values();
		self::$options_list = $this->get_options();

		if ( $this->import_button_exists() ) {
			self::$opt_name = self::$localized['import_rest_url'] = get_rest_url( '/customify/1.0/' );
			self::$opt_name = self::$localized['import_rest_nonce'] = wp_create_nonce( 'wp_rest' );

			$this->register_import_api();
		}

		// load custom modules
		include_once( self::get_base_path() . '/features/class-CSS_Editor.php' );
	}

	protected static function check_for_customizer_values() {
		if ( isset( $_POST['customized'] ) && $_POST['customized'] !== '{}' ) {
			$the_value               = $_POST['customized'];
			self::$customizer_values = json_decode( $the_value, true );

			/**
			 * if still empty, use stripslashes_deep to ensure compatibility with 5.2
			 * http://stackoverflow.com/questions/28698165/json-data-cannot-be-accessed-in-php-version-5-2-17
			 */
			if ( empty( self::$customizer_values ) ) {
				$stripped_value          = stripslashes_deep( $the_value );
				self::$customizer_values = json_decode( $stripped_value, true );
			}
		} else {
			self::$customizer_values = false;
		}
	}

	protected static function get_current_values() {
		$store_type = self::get_plugin_option( 'values_store_mod', 'option' );
		if ( $store_type === 'option' ) {
			self::$current_values = get_option( self::$opt_name );
		} elseif ( $store_type === 'theme_mod' ) {
			self::$current_values = get_theme_mod( self::$opt_name );
		}
	}

	public function get_options() {

		$settings = array();

		if ( isset ( self::$customizer_config['panels'] ) ) {

			foreach ( self::$customizer_config['panels'] as $pane_id => $panel_settings ) {

				if ( isset( $panel_settings['sections'] ) ) {
					foreach ( $panel_settings['sections'] as $section_id => $section_settings ) {
						if ( isset( $section_settings['options'] ) ) {
							foreach ( $section_settings['options'] as $option_id => $option ) {
								$settings[ $option_id ] = $option;
								if ( isset( self::$current_values[ $option_id ] ) ) {
									$settings[ $option_id ]['value'] = self::$current_values[ $option_id ];
								}
							}
						}
					}
				}
			}
		}

		if ( isset ( self::$customizer_config['sections'] ) ) {
			foreach ( self::$customizer_config['sections'] as $section_id => $section_settings ) {
				if ( isset( $section_settings['options'] ) ) {
					foreach ( $section_settings['options'] as $option_id => $option ) {
						$settings[ $option_id ] = $option;
						if ( isset( self::$current_values[ $option_id ] ) ) {
							$settings[ $option_id ]['value'] = self::$current_values[ $option_id ];
						}
					}
				}
			}
		}

		return $settings;
	}

	protected static function get_value( $option ) {

		if ( isset( self::$customizer_values[ self::$opt_name . '[' . $option . ']' ] ) ) {
			return self::$customizer_values[ self::$opt_name . '[' . $option . ']' ];
		}

		if ( strpos( $option, self::$opt_name . '[' ) !== false ) {
			var_dump( 'this is old and it shouldn\'t be here!' );

			// get only the setting id
			$option = explode( '[', $option );
			$option = rtrim( $option[1], ']' );
		}

		if ( isset( self::$current_values[ $option ] ) ) {
			return self::$current_values[ $option ];
		}

		return null;
	}

	/**
	 * A public function to retreat an option's value
	 * If there is a value and return it
	 * Otherwise try to get the default parameter or the default from config
	 *
	 * @param $option
	 * @param null $default
	 *
	 * @return bool|null|string
	 */
	static function get_option( $option, $default = null ) {

		$return = self::get_value( $option );

		if ( $return !== null ) {
			return $return;
		} elseif ( $default !== null ) {
			return $default;
		} elseif ( isset( self::$options_list[ $option ] ) && isset( self::$options_list[ $option ]['default'] ) ) {
			return self::$options_list[ $option ]['default'];
		}

		return null;
	}

	static function has_option( $option ) {

		if ( isset( self::$options_list[ $option ] ) ) {
			return true;
		}

		return false;
	}

	protected static function get_config() {
		// @TODO maybe check this
		return include 'plugin-config.php';
	}

	/**
	 * Get an option's value from the config file
	 *
	 * @param $option
	 * @param null $default
	 *
	 * @return bool|null
	 */
	public static function get_config_option( $option, $default = null ) {

		if ( isset( self::$config[ $option ] ) ) {
			return self::$config[ $option ];
		} elseif ( $default !== null ) {
			return $default;
		}

		return false;
	}

	static function get_plugin_option( $option, $default = null ) {

		if ( isset( self::$plugin_settings[ $option ] ) ) {
			return self::$plugin_settings[ $option ];
		} elseif ( $default !== null ) {
			return $default;
		}

		return false;
	}

	/**
	 * Fired when the plugin is activated.
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

	}

	/**
	 * Fired when the plugin is deactivated.
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 * @since    1.0.0
	 */
	function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/lang/' );
	}

	/** === RESOURCES === **/

	/**
	 * Customizer admin styles
	 */
	function enqueue_admin_customizer_styles() {
		wp_enqueue_style( 'select2', plugins_url( 'js/select2/select2.css', __FILE__ ), array(), $this->version );
		wp_enqueue_style( 'customify_style', plugins_url( 'css/customizer.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Customizer admin scripts
	 */
	function enqueue_admin_customizer_scripts() {

		wp_enqueue_script( 'select2', plugins_url( 'js/select2/select2.js', __FILE__ ), array( 'jquery' ), $this->version );
		wp_enqueue_script( 'jquery-react', plugins_url( 'js/jquery-react.js', __FILE__ ), array( 'jquery' ), $this->version );
		wp_enqueue_script( $this->plugin_slug . '-customizer-scripts', plugins_url( 'js/customizer.js', __FILE__ ), array(
			'jquery',
			'select2'
		), $this->version );

		wp_localize_script( $this->plugin_slug . '-customizer-scripts', 'customify_settings', self::$localized );

	}


	/** Customizer scripts loaded only on previewer page */
	function customizer_live_preview_enqueue_scripts() {

		wp_register_script( $this->plugin_slug . 'CSSOM', plugins_url( 'js/CSSOM.js', __FILE__ ), array( 'jquery' ), $this->version, true );
		wp_register_script( $this->plugin_slug . 'cssUpdate', plugins_url( 'js/jquery.cssUpdate.js', __FILE__ ), array(), $this->version, true );
		wp_enqueue_script( $this->plugin_slug . '-previewer-scripts', plugins_url( 'js/customizer_preview.js', __FILE__ ), array(
			'jquery',
			$this->plugin_slug . 'CSSOM',
			$this->plugin_slug . 'cssUpdate'
		), $this->version, true );

		// when a live preview field is in action we need to know which props need 'px' as defaults
		self::$localized['px_dependent_css_props'] = self::$pixel_dependent_css_properties;

		wp_localize_script( $this->plugin_slug . '-previewer-scripts', 'customify_settings', self::$localized );
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
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), $this->version );
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
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
			wp_localize_script( $this->plugin_slug . '-admin-script', 'locals', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			) );
		}


		wp_localize_script( $this->plugin_slug . '-customizer-scripts', 'WP_API_Settings', array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 * @since    1.0.0
	 */
	function enqueue_styles() {
		// wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array(), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		//wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version, true );

		// quit adding google fonts as a static resource ... we will add it dynamically as a fallback when the typekit library isn't used
		// wp_enqueue_script( 'google-fonts', '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js' );
	}

	/**
	 * Public style generated by customizer
	 */
	function output_dynamic_style() {
		$custom_css = "\n";

		foreach ( self::$options_list as $option_id => $option ) {

			if ( isset( $option['css'] ) && ! empty( $option['css'] ) ) {
				// now process each
				$custom_css .= $this->convert_setting_to_css( $option_id, $option['css'] );
			}

			if ( $option['type'] === 'custom_background' ) {
				$custom_css .= $this->process_custom_background_field_output( $option_id, $option );
			}
		}

		if ( ! empty( self::$media_queries ) ) {

			foreach ( self::$media_queries as $media_query => $properties ) {

				if ( empty( $properties ) ) {
					continue;
				}

				$custom_css .= '@media ' . $media_query . " {\n";

				foreach ( $properties as $key => $property ) {
					$property_settings = $property['property'];
					$property_value    = $property['value'];
					$custom_css .= "\t" . self::proccess_css_property( $property_settings, $property_value );
				}

				$custom_css .= "\n}\n";

			}
		}

		$custom_css .= "\n";

		//@todo maybe add a filter to this output ?>
		<style id="customify_output_style">
			<?php 	echo( $custom_css ); ?>
		</style>
		<?php


		/**
		 * from now on we output only style tags only for the preview purpose
		 * so don't cry if you see 30+ style tags for each section
		 */
		if ( ! isset( $GLOBALS['wp_customize'] ) ) {
			return;
		}

		foreach ( self::$options_list as $option_id => $options ) {

			if ( $options['type'] === 'custom_background' ) {
				$options['value']         = self::get_option( $option_id );
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

			$this_value = self::get_option( $option_id );
			foreach ( $options['css'] as $key => $properties_set ) { ?>
				<style id="dynamic_setting_<?php echo $option_id . '_property_' . str_replace( '-', '_', $properties_set['property'] ); ?>" type="text/css"><?php

					if ( isset( $properties_set['media'] ) && ! empty( $properties_set['media'] ) ) {
						echo '@media '. $properties_set['media'] . " {\n";
					}

					if ( isset( $properties_set['selector'] ) && isset( $properties_set['property'] ) ) {
						echo self::proccess_css_property($properties_set, $this_value);
					}

					if ( isset( $properties_set['media'] ) && ! empty( $properties_set['media'] ) ) {
						echo "}\n";
					} ?>
				</style>
			<?php }
		}

		if ( ! empty( self::$media_queries ) ) {

			foreach ( self::$media_queries as $media_query => $properties ) {

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
						$media_q .= "\t" . self::proccess_css_property( $property_settings, $property_value );?>
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

		$fonts_path = plugin_dir_path( __FILE__ ) . 'features/customizer/controls/resources/google.fonts.php';

		if ( file_exists( $fonts_path ) ) {
			self::$google_fonts = require( $fonts_path );
		}

		if ( ! empty( self::$google_fonts ) ) {
			return self::$google_fonts;
		}

		return false;
	}

	function output_typography_dynamic_style() {

		self::get_typography_fields( self::$options_list, 'type', 'typography', self::$typo_settings );

		if ( empty( self::$typo_settings ) ) {
			return;
		}

		$families = '';

		foreach ( self::$typo_settings as $id => $font ) {
			if ( isset ( $font['value'] ) ) {

				$load_all_weights = false;

				if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
					$load_all_weights = true;
				}

				// shim the time when this was an array
				if ( is_array( $font['value'] ) ) {
//					$font['value']['font_family'] = $font['value']['font-family'];
//					unset( $font['value']['font-family'] );
//
//					if ( isset( $font['value']['google'] ) && $font['value']['google'] ) {
//						$font['value']['type'] = 'google';
//						unset($font['value']['type']);
//					}
//
//					foreach ($font['value']['font-options']['variants'] as $variant ) {
//						$font['value']['variants'][$variant['id']] = $variant['name'];
//					}
//
//					foreach ($font['value']['font-options']['subsets'] as $subsets ) {
//						$font['value']['subsets'][$subsets['id']] = $subsets['name'];
//					}
//
//					unset( $font['value']['font-options'] );

					$font['value'] = stripslashes_deep( $font['value'] );
					$font['value'] = json_encode( $font['value'] );
				}

				$value = json_decode( wp_unslash( $font['value'] ), true );

				// in case the value is still null, try default value(mostly for google fonts)
				if ( ! is_array( $value ) || $value === null ) {
					$value = $this->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
				}

				//bail if by this time we don't have a value of some sort
				if ( empty( $value ) ) {
					continue;
				}

				//Handle special logic for when the $value array is not an associative array
				if ( ! self::is_assoc( $value ) ) {
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


		if ( ! empty ( $families ) && self::get_plugin_option( 'typography', '1' ) && self::get_plugin_option( 'typography_google_fonts', 1 ) ) { ?>
			<script type="text/javascript">
				if ( typeof WebFont !== 'undefined' ) {<?php // if there is a WebFont object, use it ?>
					WebFont.load( {
						google: {families: [<?php echo( rtrim( $families, ',' ) ); ?>]},
						classes: false,
						events: false
					} );
				} else {<?php // basically when we don't have the WebFont object we create the google script dynamically  ?>

					var tk = document.createElement( 'script' );
					tk.src = '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
					tk.type = 'text/javascript';

					tk.onload = tk.onreadystatechange = function() {
						WebFont.load( {
							google: {families: [<?php echo( rtrim( $families, ',' ) ); ?>]},
							classes: false,
							events: false
						} );
					};

					var s = document.getElementsByTagName( 'script' )[0];
					s.parentNode.insertBefore( tk, s );
				}
			</script>
		<?php } ?>
		<style id="customify_typography_output_style">
			<?php
			foreach ( self::$typo_settings as $key => $font ) {
				$load_all_weights = false;
				if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
					$load_all_weights = true;
				}

				if ( isset( $font['selector'] ) && isset( $font['value'] ) && ! empty( $font['value'] ) ) {

					$value = json_decode( $font['value'], true );
					// in case the value is still null, try default value(mostly for google fonts)
					if ( $value === null ) {
						$value = $this->get_font_defaults_value( $font['value'] );
					}

					// shim the old case when the default was only the font name
					if ( is_string( $value ) && ! empty( $value ) ) {
						$value = array( 'font_family' => $value );
					}

					//Handle special logic for when the $value array is not an associative array
					if ( ! self::is_assoc( $value ) ) {
						$value = $this->process_a_not_associative_font_default( $value );
					}

					if ( isset( $value['font_family'] ) ) {
						echo $font['selector'] . " {\n font-family: " . $value['font_family'] . ";";

						if ( isset( $value['selected_variants'] ) && ! $load_all_weights ) {

							if ( is_array( $value['selected_variants'] ) ) {
								$the_weight = $value['selected_variants'][0];
							} else {
								$the_weight = $value['selected_variants'];
							}

							$italic_font = false;

							if ( strpos( $the_weight, 'italic' ) !== false ) {
								$the_weight = str_replace( 'italic', '', $the_weight);
								$italic_font = true;
							}

							if ( ! empty( $the_weight ) ) {
								if($the_weight === 'regular') {
									$the_weight = 'normal';
								}
								echo "\nfont-weight: " . $the_weight . ";\n";
							}

							if ( $italic_font ) {
								echo "\nfont-style: italic;\n";
							}
						}

						echo "\n}\n";
					}
				}
			} ?>
		</style>
	<?php }

	/**
	 * Handle special logic for when the $value array is not an associative array
	 * Return a new associative array with proper keys
	 */
	protected function process_a_not_associative_font_default( $value ) {

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
	protected function get_font_defaults_value( $font_name ) {

		if ( empty( self::$google_fonts ) ) {
			$this->load_google_fonts();
		}

		if ( isset( self::$google_fonts[ $font_name ] ) ) {
			$value                = self::$google_fonts[ $font_name ];
			$value['font_family'] = $font_name;
			$value['type']        = 'google';

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

		$this_value = self::get_option( $option_id );

		if ( empty( $css_config ) ) {
			return $output;
		}

		foreach ( $css_config as $css_property ) {

			if ( isset( $css_property['media'] ) && ! empty( $css_property['media'] ) ) {
				self::$media_queries[ $css_property['media'] ][ $option_id ] = array(
					'property' => $css_property,
					'value'    => $this_value
				);
				continue;
			}

			if ( ! isset( $css_property['selector'] ) || isset( $css_property['property'] ) ) {
				$output .= self::proccess_css_property( $css_property, $this_value );
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

		$this_property_output = $css_property['selector'] . ' { ' . $css_property['property'] . ': ' . $this_value . $unit . "; } \n";

		if ( isset( $css_property['callback_filter'] ) && function_exists( $css_property['callback_filter'] ) ) {
			$this_property_output = call_user_func( $css_property['callback_filter'], $this_value, $css_property['selector'], $css_property['property'], $unit );
		}

		return $this_property_output;
	}

	protected function process_custom_background_field_output( $option_id, $options ) {
		$selector = '';

		if ( ! isset( $options['value'] ) ) {
			return false;
		}
		$value    = $options['value'];

		if ( ! isset( $options['output'] ) ) {
			return $selector;
		} elseif ( is_string( $options['output'] ) ) {
			$selector = $options['output'];
		} elseif ( is_array( $options['output'] ) ) {
			$selector = implode( ' ', $options['output'] );
		}
		ob_start();

		echo "\n" . $selector . " { \n";
		if ( isset( $value['background-image'] ) && ! empty( $value['background-image'] ) ) {
			echo "background-image: url( " . $value['background-image'] . ");\n";
		} else {
			echo "background-image: none;\n";
		}

		if ( isset( $value['background-repeat'] ) && ! empty( $value['background-repeat'] ) ) {
			echo "background-repeat:" . $value['background-repeat'] . ";\n";
		}

		if ( isset( $value['background-position'] ) && ! empty( $value['background-position'] ) ) {
			echo "background-position:" . $value['background-position'] . ";\n";
		}

		if ( isset( $value['background-size'] ) && ! empty( $value['background-size'] ) ) {
			echo "background-size:" . $value['background-size'] . ";\n";
		}

		if ( isset( $value['background-attachment'] ) && ! empty( $value['background-attachment'] ) ) {
			echo "background-attachment:" . $value['background-attachment'] . ";\n";
		}
		echo "\n}\n";

		return ob_get_clean();
	}

	// addd editor style

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
			(function( $ ) {
				$( window ).load( function() {
					/**
					 * @param iframe_id the id of the frame you whant to append the style
					 * @param style_element the style element you want to append
					 */
					var append_script_to_iframe = function( ifrm_id, scriptEl ) {
						var myIframe = document.getElementById( ifrm_id );

						var script = myIframe.contentWindow.document.createElement( "script" );
						script.type = "text/javascript";
						script.innerHTML = scriptEl.innerHTML;

						myIframe.contentWindow.document.head.appendChild( script );
					};

					var append_style_to_iframe = function( ifrm_id, styleElment ) {
						var ifrm = window.frames[ifrm_id];
						ifrm = ( ifrm.contentDocument || ifrm.contentDocument || ifrm.document );
						var head = ifrm.getElementsByTagName( 'head' )[0];

						if ( typeof styleElment !== "undefined" ) {
							head.appendChild( styleElment );
						}
					};

					var xmlString = <?php echo json_encode( str_replace( "\n", "", $custom_css ) ); ?>,
						parser = new DOMParser(),
						doc = parser.parseFromString( xmlString, "text/html" );

					if ( typeof window.frames['content_ifr'] !== 'undefined' ) {

						$.each( doc.head.childNodes, function( key, el ) {

							if ( typeof el !== "undefined" && typeof el.tagName !== "undefined" ) {

								switch ( el.tagName ) {

									case 'STYLE' :
										append_style_to_iframe( 'content_ifr', el );
										break;

									case 'SCRIPT' :
										append_script_to_iframe( 'content_ifr', el );
										break;
									default:
										break;
								}
							}
						} );
					}
				} );
			})( jQuery );
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
		require_once( self::get_base_path() . '/features/customizer/class-Pix_Customize_Control.php' );

		// now get all the controls
		$path = self::get_base_path() . '/features/customizer/controls/';
		pixcustomify::require_all( $path );
	}

	function register_customizer( $wp_customize ) {

		$this->register_customizer_controls();

		$customizer_settings = self::$customizer_config;

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
							'title'       => __( 'Panel title is required', 'textdomain' ),
							'description' => __( 'Description of what this panel does.', 'textdomain' ),
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

			if ( self::$plugin_settings['enable_reset_buttons'] ) {
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
						'label'    => __( 'Reset Customify to Defaults', 'customify_txtd' ),
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

		if ( isset( self::$plugin_settings['disable_customify_sections'] ) && isset( self::$plugin_settings['disable_customify_sections'][ $section_key ] ) ) {
			return;
		}

		$section_args = array(
			'priority'   => 10,
			'capability' => 'edit_theme_options',
			'title'      => __( 'Title Section is required', 'textdomain' ),
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
		$partials_array = array();
		foreach ( $section_settings['options'] as $option_id => $option_config ) {

			if ( empty( $option_id ) || ! isset( $option_config['type'] ) ) {
				continue;
			}

			$option_id = $options_name . '[' . $option_id . ']';

			$this->register_field( $section_id, $option_id, $option_config, $wp_customize );
			$partials_array[] = $option_id;
		}
		$wp_customize->selective_refresh->add_partial( 'mymy_partial', array(
			'selector' => 'body',
			'settings' => $partials_array,
			'render_callback' => 'do_nothing_jon_snow',
		) );

	}

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

		self::$localized['settings'][ $setting_id ] = $setting_config;

		// sanitize settings
		if ( isset( $setting_config['live'] ) && $setting_config['live'] ) {
			$setting_args['transport'] = 'postMessage';
		}

		if ( isset( $setting_config['default'] ) ) {
			$setting_args['default'] = $setting_config['default'];
		}

		if ( isset( $setting_config['capability'] ) && ! empty( $setting_config['capability'] ) ) {
			$setting_args['capability'] = $setting_config['capability'];
		}

		if ( self::$plugin_settings['values_store_mod'] == 'option' ) {
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

				$use_typography = self::get_plugin_option( 'typography', '1' );

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

	function do_nothing_jon_snow() {
		echo '1';
	}

	/**
	 * Remove the sections selected by user
	 *
	 * @param $wp_customize
	 */
	function remove_default_sections( $wp_customize ) {
		global $wp_registered_sidebars;

		$to_remove = self::get_plugin_option( 'disable_default_sections' );

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

	static function get_base_path() {
		return plugin_dir_path( __FILE__ );
	}

	protected static function get_typography_fields( $array, $key, $value, &$results, $input_key = 0 ) {
		if ( ! is_array( $array ) ) {
			return;
		}

		if ( isset( $array[ $key ] ) && $array[ $key ] == $value ) {
			$results[ $input_key ] = $array;

			$default = null;

			if ( isset( $array['default'] ) && is_array( $array['default'] ) ) {
				$default = json_encode( $array['default'] );
			}

			$results[ $input_key ]['value'] = self::get_option( $input_key, $default );
		}

		foreach ( $array as $i => $subarray ) {
			self::get_typography_fields( $subarray, $key, $value, $results, $i );
		}
	}

	function get_options_key() {
		if ( ! empty( self::$opt_name ) ) {
			return self::$opt_name;
		}

		return false;
	}

	/**
	 * Use this function when you need to know if an import button is used
	 * @return bool
	 */
	function import_button_exists() {

		if ( empty( self::$options_list ) ) {
			self::$options_list = $this->get_options();
		}

		foreach ( self::$options_list as $option ) {
			if ( isset( $option['type'] ) && 'import_demo_data' === $option['type'] ) {
				return true;
				break;
			}
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
	public static function is_assoc( $array ) {

		if ( ! is_array( $array ) ) {
			return false;
		}

		// Keys of the array
		$keys = array_keys( $array );

		// If the array keys of the keys match the keys, then the array must
		// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
		return array_keys( $keys ) !== $keys;
	}

	function register_import_api(){

		include_once( self::get_base_path() . '/features/class-Customify_Importer.php' );
		$controller = new Customify_Importer_Controller();
		$controller->init();
	}

	function get_options_configs () {
		return self::$options_list;
	}
}
