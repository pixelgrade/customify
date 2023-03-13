<?php
/**
 * This is the class that handles the plugin settings page.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Customify_Settings {

	/**
	 * Instance of this class.
	 * @var      object
	 */
	protected static $_instance = null;

	/**
	 * Slug of the plugin screen.
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	public $plugin_settings;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 */
	public $file;

	public $slug;
	public $version;

	private $plugin_config = array();

	protected function __construct( $file, $slug, $version = '1.0.0' ) {
		$this->file = $file;
		$this->slug = $slug;
		$this->version = $version;

		require plugin_dir_path( $this->file ) . 'includes/admin-settings/core/bootstrap.php';

		// Load the config file
		$this->plugin_config = self::get_plugin_config();
		// Load the plugin's settings from the DB
		$this->plugin_settings = get_option( $this->plugin_config['settings-key'] );

		// Register all the needed hooks
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	function register_hooks() {

		// Starting with the menu item for this plugin
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		add_filter( 'plugin_action_links_' . plugin_basename( $this->file ), array( $this, 'add_action_links' ) );

		// Load admin stylesheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );


		add_action( 'rest_api_init', array( $this, 'add_rest_routes_api' ) );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_options_page(
			esc_html__( 'Customify', 'customify' ),
			esc_html__( 'Customify', 'customify' ),
			'manage_options',
			$this->slug,
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 */
	function display_plugin_admin_page() {
		// Check the nonce, in case the form was submitted.
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			check_admin_referer( 'customify_settings_save', '_wpnonce-customify-settings' );
		}

		$config = Customify_Settings::get_plugin_config();

		// Invoke the processor.
		/**
		 * @var PixCustomifyProcessorImpl $processor
		 */
		$processor = pixcustomify::processor( $config );
		$status    = $processor->status();
		$errors    = $processor->errors();

		// Do the saving and display the form.
		include_once plugin_dir_path( $this->file ) . 'includes/admin-settings/views/admin.php';
	}

	/**
	 * Settings page styles
	 */
	function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id === $this->plugin_screen_hook_suffix ) {
			$rtl_suffix = is_rtl() ? '-rtl' : '';
			wp_enqueue_style( $this->slug . '-admin-styles', plugins_url( 'css/admin' . $rtl_suffix . '.css', $this->file ), array(), $this->version );
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
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_enqueue_script( $this->slug . '-settings-page-script',
				plugins_url( 'js/settings-page' . $suffix . '.js', $this->file ),
				array( 'jquery' ), $this->version );

			wp_add_inline_script( $this->slug . '-settings-page-script',
				PixCustomify_Customizer::getlocalizeToWindowScript( 'customify',
					array(
						'config' => array(
							'ajax_url' => admin_url( 'admin-ajax.php' ),
							'wp_rest' => array(
								'root'  => esc_url_raw( rest_url() ),
								'nonce' => wp_create_nonce( 'wp_rest' ),
								'customify_settings_nonce' => wp_create_nonce( 'customify_settings_nonce' )
							),
						)
					)
				) );
		}

		wp_localize_script( $this->slug . '-customizer-scripts', 'WP_API_Settings', array(
			'root'  => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' )
		) );
	}

	/**
	 * Add settings action link to the plugins page.
	 */
	public function add_action_links( $links ) {
		return array_merge( array( 'settings' => '<a href="' . esc_url( menu_page_url( $this->slug, false ) ) . '">' . esc_html__( 'Settings', 'customify' ) . '</a>' ), $links );
	}

	public function add_rest_routes_api() {
		register_rest_route( 'customify/v1', '/delete_theme_mod', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'delete_theme_mod' ),
			'permission_callback' => array( $this, 'permission_nonce_callback' ),
		) );
	}

	public function delete_theme_mod() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__('You don\'t have admin privileges.', 'customify' ) );
		}

		$key = PixCustomifyPlugin()->get_options_key();

		if ( empty( $key ) ) {
			wp_send_json_error('no option key');
		}

		remove_theme_mod( $key );

		PixCustomifyPlugin()->invalidate_all_caches();

		wp_send_json_success('Deleted ' . $key . ' theme mod!');
	}

	public function permission_nonce_callback() {
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

	static public function get_plugin_config() {

		$debug = false;
		if ( isset( $_GET['debug'] ) && $_GET['debug'] === 'true' ) {
			$debug = true;
		}

		return array(
			'plugin-name'    => 'pixcustomify',
			'settings-key'   => 'pixcustomify_settings',
			'textdomain'     => 'customify',
			'template-paths' => array(
				plugin_dir_path( __FILE__ ) . 'admin-settings/core/views/form-partials/',
				plugin_dir_path( __FILE__ ) . 'admin-settings/views/form-partials/',
			),
			'fields'         => array(
				'hiddens'    => array(
					'type'    => 'group',
					'options' => array(
						'settings_saved_once' => array(
							'default' => '0',
							'value'   => '1',
							'type'    => 'hidden',
						),
					),
				),
				'general'    => array(
					'type'    => 'postbox',
					'label'   => esc_html__( 'General Settings', 'customify' ),
					'options' => array(
						'values_store_mod' => array(
							'name'    => 'values_store_mod',
							'label'   => esc_html__( 'Store values as:', 'customify' ),
							'desc'    => esc_html__( 'You can store the values globally so you can use them with other themes or store them as a "theme_mod" which will make an individual set of options only for the current theme', 'customify' ),
							'default' => 'theme_mod',
							'type'    => 'select',
							'options' => array(
								'option'    => esc_html__( 'Option (global options)', 'customify' ),
								'theme_mod' => esc_html__( 'Theme Mod (per theme options)', 'customify' ),
							),
						),

						'disable_default_sections' => array(
							'name'    => 'disable_default_sections',
							'label'   => esc_html__( 'Disable default sections', 'customify' ),
							'desc'    => esc_html__( 'You can disable default sections', 'customify' ),
							'type'    => 'multicheckbox',
							'options' => array(
								'nav'               => esc_html__( 'Navigation', 'customify' ),
								'static_front_page' => esc_html__( 'Front Page', 'customify' ),
								'title_tagline'     => esc_html__( 'Title', 'customify' ),
								'colors'            => esc_html__( 'Colors', 'customify' ),
								'background_image'  => esc_html__( 'Background', 'customify' ),
								'header_image'      => esc_html__( 'Header', 'customify' ),
								'widgets'           => esc_html__( 'Widgets', 'customify' ),
							),
						),

						'enable_reset_buttons' => array(
							'name'    => 'enable_reset_buttons',
							'label'   => esc_html__( 'Enable Reset Buttons', 'customify' ),
							'desc'    => esc_html__( 'You can enable "Reset to defaults" buttons for panels / sections or all settings. We have disabled this feature by default to avoid accidental resets. If you are sure that you need it please enable this.', 'customify' ),
							'default' => false,
							'type'    => 'switch',
						),

						'enable_editor_style' => array(
							'name'    => 'enable_editor_style',
							'label'   => esc_html__( 'Enable Editor Style', 'customify' ),
							'desc'    => esc_html__( 'The styling added by Customify in front-end can be added in the WordPress editor too by enabling this option', 'customify' ),
							'default' => true,
							'type'    => 'switch',
						),
					),
				),
				'output'     => array(
					'type'    => 'postbox',
					'label'   => esc_html__( 'Output Settings', 'customify' ),
					'options' => array(
						'style_resources_location' => array(
							'name'    => 'style_resources_location',
							'label'   => esc_html__( 'Styles location:', 'customify' ),
							'desc'    => esc_html__( 'Here you can decide where to put your style output, in header or footer', 'customify' ),
							'default' => 'wp_footer',
							'type'    => 'select',
							'options' => array(
								'wp_head'   => esc_html__( 'In header (just before the head tag)', 'customify' ),
								'wp_footer' => esc_html__( 'Footer (just before the end of the body tag)', 'customify' ),
							),
						),
					),
				),
				'typography' => array(
					'type'    => 'postbox',
					'label'   => esc_html__( 'Typography Settings', 'customify' ),
					'options' => array(
						'typography' => array(
							'label'          => esc_html__( 'Enable Typography Options', 'customify' ),
							'default'        => true,
							'type'           => 'switch',
							'show_group'     => 'typography_group',
							'display_option' => true,
						),

						'typography_group' => array(
							'type'    => 'group',
							'options' => array(
								'typography_system_fonts'     => array(
									'name'    => 'typography_system_fonts',
									'label'   => esc_html__( 'Use system fonts', 'customify' ),
									'desc'    => esc_html__( 'Would you like to have system fonts available in the font controls?', 'customify' ),
									'default' => true,
									'type'    => 'switch',
								),
								'typography_google_fonts'       => array(
									'name'           => 'typography_google_fonts',
									'label'          => esc_html__( 'Use Google fonts:', 'customify' ),
									'desc'           => esc_html__( 'Would you like to have Google fonts available in the font controls?', 'customify' ),
									'default'        => true,
									'type'           => 'switch',
									'show_group'     => 'typography_google_fonts_group',
									'display_option' => true,
								),
								'typography_google_fonts_group' => array(
									'type'    => 'group',
									'options' => array(
										'typography_group_google_fonts' => array(
											'name'    => 'typography_group_google_fonts',
											'label'   => esc_html__( 'Group Google fonts:', 'customify' ),
											'desc'    => esc_html__( 'You can chose to see the Google fonts in groups', 'customify' ),
											'default' => true,
											'type'    => 'switch',
										),
									),
								),
								'typography_cloud_fonts'       => array(
									'name'           => 'typography_cloud_fonts',
									'label'          => esc_html__( 'Use cloud fonts', 'customify' ),
									'desc'           => esc_html__( 'Would you to have Cloud fonts available in the font controls?', 'customify' ),
									'default'        => true,
									'type'           => 'switch',
									'display_option' => true,
								),
							),
						),
					),
				),
				'tools'      => array(
					'type'    => 'postbox',
					'label'   => esc_html__( 'Tools', 'customify' ),
					'options' => array(
						'reset_theme_mod' => array(
							'name'  => 'reset_theme_mod',
							'label' => esc_html__( 'Reset', 'customify' ),
							'type'  => 'reset_theme_mod',
						),
					),
				),
			),
			'callbacks' => array(
				'invalidate_caches' => 'pixcustomify_cache_invalidate_cache',
			),
			'processor'      => array(
				// callback signature: (array $input, customifyProcessor $processor)
				'preupdate' => array(
					// callbacks to run before update process
					// cleanup and validation has been performed on data
				),
				'postupdate' => array(
					'invalidate_caches'
				),
			),
			'cleanup'        => array(
				'switch' => array( 'switch_not_available' ),
			),
			'checks'         => array(
				'counter' => array( 'is_numeric', 'not_empty' ),
			),
			'errors'         => array(
				'not_empty' => __( 'Invalid Value.', 'customify' ),
			),
			// shows exception traces on error
			'debug'          => $debug,

		); # config
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

		if ( isset( $this->plugin_config[ $option ] ) ) {
			return $this->plugin_config[ $option ];
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
	 * Main Customify_Settings Instance
	 *
	 * Ensures only one instance of Customify_Settings is loaded or can be loaded.
	 *
	 * @static
	 *
	 * @param string $file File.
	 * @param string $slug Plugin slug.
	 * @param string $version Optional.
	 *
	 * @return Customify_Settings Main Customify_Settings instance
	 */
	public static function instance( $file, $slug, $version = '1.0.0' ) {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $slug, $version );
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.5.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ), null );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.5.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ), null );
	}
}
