<?php
/**
 * PixCustomizer.
 * @package   PixCustomizer
 * @author    Pixelgrade <contact@pixelgrade.com>
 * @license   GPL-2.0+
 * @link      http://pixelgrade.com
 * @copyright 2014 Pixelgrade
 */

/**
 * Plugin class.
 * @package   PixCustomizer
 * @author    Pixelgrade <contact@pixelgrade.com>
 */
class PixCustomizerPlugin {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 * @since   1.0.0
	 * @const   string
	 */
	protected $version = '1.0.0';
	/**
	 * Unique identifier for your plugin.
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_slug = 'pixcustomizer';

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

	protected $wp_customize = array();

	public static $plugin_settings;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 * @since     1.0.0
	 */
	protected function __construct() {

		$this->plugin_basepath = plugin_dir_path( __FILE__ );
		self::$config          = self::get_config();
		self::$plugin_settings = get_option( 'pixcustomizer_settings' );

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . 'pixcustomizer.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );


		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 99999999999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

//		add_action( 'plugins_loaded', array( $this, 'register_metaboxes' ), 14 );
		add_action( 'customize_register', array( $this, 'register_customizer' ), 12 );
		add_action( 'customize_register', array( $this, 'debug_remove_default_sections' ), 11 );

		/**
		 * Ajax Callbacks
		 */
//		add_action( 'wp_ajax_pixcustomizer_image_click', array( &$this, 'ajax_click_on_photo' ) );
//		add_action( 'wp_ajax_nopriv_pixcustomizer_image_click', array( &$this, 'ajax_click_on_photo' ) );
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

	protected static function get_config() {
		// @TODO maybe check this
		return include 'plugin-config.php';
	}

	public static function config_option( $option, $default = null ) {

		if ( isset( self::$config[ $option ] ) ) {
			return self::$config[ $option ];
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

	/**
	 * Register and enqueue admin-specific style sheet.
	 * @since     1.0.0
	 * @return    null    Return early if no settings page is registered.
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
	 * Register and enqueue admin-specific JavaScript.
	 * @since     1.0.0
	 * @return    null    Return early if no settings page is registered.
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
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 * @since    1.0.0
	 */
	function enqueue_styles() {

		if ( ! wp_style_is( 'wpgrade-main-style' ) ) {
			wp_enqueue_style( 'pixcustomizer_inuit', plugins_url( 'css/inuit.css', __FILE__ ), array(), $this->version );
			wp_enqueue_style( 'pixcustomizer_magnific-popup', plugins_url( 'css/mangnific-popup.css', __FILE__ ), array(), $this->version );
		}

		//		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), array('wpgrade-main-style'), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version, true );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_options_page( __( 'PixCustomizer', $this->plugin_slug ), __( 'PixCustomizer', $this->plugin_slug ), 'edit_plugins', $this->plugin_slug, array(
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
		return array_merge( array( 'settings' => '<a href="' . admin_url( 'options-general.php?page=pixcustomizer' ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>' ), $links );
	}

	protected function register_customizer_controls() {

		// first get the base customizer extend class
		require_once( self::get_base_path() . '/features/customizer/class-Pix_Customize_Control.php');

		// now get all the controls
		$path = self::get_base_path() . '/features/customizer/controls/';
		pixcustomizer::require_all($path);
	}

	function register_customizer( $wp_customize ) {

		$this->register_customizer_controls();

		$customizer_settings = self::config_option( 'pixcustomizer_settings' );

		if ( ! empty ( $customizer_settings ) ) {

			// first check the very needed options name
			if ( ! isset ( $customizer_settings['opt-name'] ) || empty( $customizer_settings['opt-name'] ) ) {
				return;
			}

			$options_name = $customizer_settings['opt-name'];

			// let's check if we have sections or panels

			if ( isset( $customizer_settings['panels'] ) && ! empty( $customizer_settings['panels'] ) ) {

				foreach ( $customizer_settings['panels'] as $panel_id => $panel_settings ) {

					if ( ! empty( $panel_id ) && isset( $panel_settings['sections'] ) && ! empty( $panel_settings['sections'] ) ) {

						$panel_id = $options_name . '[' . $panel_id . ']';
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

							if ( ! empty( $section_id ) && isset( $section_settings['settings'] ) && ! empty( $section_settings['settings'] ) ) {

								$section_id = $options_name . '[' . $section_id . ']';

								$section_args = array(
									'priority'   => 10,
									'capability' => 'edit_theme_options',
									'title'      => __( 'Title Section is required', 'textdomain' ),
									'panel'      => $panel_id,
								);

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

								foreach ( $section_settings['settings'] as $setting_id => $setting_config ) {

									if ( empty( $setting_id ) || ! isset( $setting_config['type'] ) ) {
										continue;
									}

									$setting_id = $options_name . '[' . $setting_id . ']';

									$this->register_field( $section_id, $setting_id, $setting_config, $wp_customize );
								}

							}

						}

					}

				}

			}

			if ( isset( $customizer_settings['sections'] ) && ! empty( $customizer_settings['sections'] ) ) {

				foreach ( $customizer_settings['sections'] as $section_id => $section_settings ) {
//					$this->register_customizer_section( $section_id, $section_settings, $wp_customize );
				}
			}
		}
	}

	protected function register_field($section_id, $setting_id, $setting_config, $wp_customize) {

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

		// sanitize settings
		if ( isset( $setting_config['transport'] ) && ! empty( $setting_config['transport'] ) ) {
			$setting_args['transport'] = $setting_config['transport'];
		}

		if ( isset( $setting_config['default'] ) && ! empty( $setting_config['default'] ) ) {
			$setting_args['default'] = $setting_config['default'];
		}

		if ( isset( $setting_config['capability'] ) && ! empty( $setting_config['capability'] ) ) {
			$setting_args['capability'] = $setting_config['capability'];
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

		$control_args['type'] = $setting_config['type'];

		// select the control type
		// but first init a default
		$control_class_name = 'Pix_Customize_Text_Control';

		// if is a standard wp field type call it here and skip the rest
		if ( in_array( $setting_config['type'], array('text', 'textarea', 'checkbox', 'dropdown-pages', 'url', 'date', 'time', 'datetime', 'week', 'search') ) ) {
			$wp_customize->add_control($setting_id . '_control', $control_args);
			return;
		} elseif ( in_array( $setting_config['type'], array( 'radio', 'select' ) ) && isset( $setting_config['choices'] ) && ! empty( $setting_config['choices'] ) ) {
			$control_args['choices'] = $setting_config['choices'];
			$wp_customize->add_control($setting_id . '_control', $control_args);
			return;
		} elseif ( in_array( $setting_config['type'], array( 'range' ) ) && isset( $setting_config['input_attrs'] ) && ! empty( $setting_config['input_attrs'] ) ) {

			$control_args['input_attrs'] = $setting_config['input_attrs'];

//			var_dump( $control_args );

			$wp_customize->add_control($setting_id . '_control', $control_args);
		}

		// if we arrive here this means we have a custom field control
		switch ( $setting_config['type'] ) {

			case 'color':

				$control_class_name = 'WP_Customize_Color_Control';//'Pix_Customize_' . ucfirst( $setting_config['type'] ) . '_Control';
				break;

			case 'upload':

				$control_class_name = 'WP_Customize_Upload_Control';//'Pix_Customize_' . ucfirst( $setting_config['type'] ) . '_Control';
				break;

			case 'image':

				$control_class_name = 'WP_Customize_Image_Control';//'Pix_Customize_' . ucfirst( $setting_config['type'] ) . '_Control';
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


		$wp_customize->add_control( $this_control );
	}

	function debug_remove_default_sections( $wp_customize ) {

		$wp_customize->remove_section( 'nav' );
		$wp_customize->remove_section( 'static_front_page' );
		$wp_customize->remove_section( 'featured_content' );
		$wp_customize->remove_section( 'title_tagline' );

		$wp_customize->remove_section( 'colors' );
		$wp_customize->remove_section( 'header_image' );
		$wp_customize->remove_section( 'background_image' );
		$wp_customize->remove_section( 'static_front_page' );
		$wp_customize->remove_section( 'sidebar-widgets-sidebar-3' );
		$wp_customize->remove_section( 'sidebar-widgets-sidebar-2' );
		$wp_customize->remove_section( 'sidebar-widgets-sidebar-1' );

	}

	static function get_base_path() {
		return plugin_dir_path( __FILE__ );
	}
}