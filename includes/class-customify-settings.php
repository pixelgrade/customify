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

	}

	/**
	 * Settings page scripts
	 */
	function enqueue_admin_scripts() {

	}

	public function get_plugin_setting( $option, $default = null ) {

		if ( isset( $this->plugin_settings[ $option ] ) ) {
			return $this->plugin_settings[ $option ];
		} elseif ( $default !== null ) {
			return $default;
		}

		return false;
	}
}
