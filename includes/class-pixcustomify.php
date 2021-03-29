<?php
/**
 * PixCustomify.
 * @package   PixCustomify
 * @author    Pixelgrade <contact@pixelgrade.com>
 * @license   GPL-2.0+
 * @link      https://pixelgrade.com
 * @copyright 2014-2020 Pixelgrade
 */

/**
 * Main plugin class.
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
	 * Customizer class object to handle customizer controls and logic.
	 * @var PixCustomify_Customizer
	 * @access  public
	 * @since   2.4.0
	 */
	public $customizer = null;

	/**
	 * Fonts class object to handle fonts global logic.
	 * @var Customify_Fonts_Global
	 * @access  public
	 * @since   2.7.0
	 */
	public $fonts_global = null;

	/**
	 * Style Manager class object.
	 * @var Customify_Style_Manager
	 * @access  public
	 * @since   1.0.0
	 */
	public $style_manager = null;

	/**
	 * Block Editor class object.
	 * @var Customify_Block_Editor
	 * @access  public
	 * @since   2.7.0
	 */
	public $block_editor = null;

	/**
	 * Classic Editor class object.
	 * @var Customify_Classic_Editor
	 * @access  public
	 * @since   2.7.0
	 */
	public $classic_editor = null;

	/**
	 * Customizer Search class object.
	 * @var Customify_Customizer_Search
	 * @access  public
	 * @since   2.9.0
	 */
	public $customizer_search = null;



	/**
	 * Minimal Required PHP Version
	 * @var string
	 * @access  private
	 * @since   1.5.0
	 */
	private $minimalRequiredPhpVersion = '5.4';

	protected function __construct( $file, $version = '1.0.0' ) {
		// The main plugin file (the one that loads all this).
		$this->file = $file;
		// The current plugin version.
		$this->_version = $version;

		$this->init();
	}

	/**
	 * Initialize plugin
	 */
	private function init() {

		/* Initialize the plugin settings logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-settings.php' );
		if ( is_null( $this->settings ) ) {
			$this->settings = Customify_Settings::instance( $this->get_file(), $this->get_slug(), $this->get_version() );
		}

		/* Initialize the Customizer logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-customizer.php' );
		if ( is_null( $this->customizer ) ) {
			$this->customizer = PixCustomify_Customizer::instance();
		}

		/* Initialize the Fonts logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-fonts-global.php' );
		if ( is_null( $this->fonts_global ) ) {
			$this->fonts_global = Customify_Fonts_Global::instance();
		}

		/* Initialize the Style Manager logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-style-manager.php' );
		if ( is_null( $this->style_manager ) ) {
			$this->style_manager = Customify_Style_Manager::instance();
		}

		/* Initialize the Block Editor integration logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-block-editor.php' );
		if ( is_null( $this->block_editor ) ) {
			$this->block_editor = Customify_Block_Editor::instance();
		}

		/* Initialize the Classic Editor integration logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-classic-editor.php' );
		if ( is_null( $this->classic_editor ) ) {
			$this->classic_editor = Customify_Classic_Editor::instance();
		}

		/* Initialize the Customizer Search logic. */
		require_once( $this->get_base_path() . 'includes/class-customify-customizer-search.php' );
		if ( is_null( $this->customizer_search ) ) {
			$this->customizer_search = Customify_Customizer_Search::instance();
		}

		// Register all the needed hooks
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	function register_hooks() {

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
		return wp_normalize_path( plugin_dir_path( $this->file ) );
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
	 * @param mixed $str
	 *
	 * @return mixed
	 */
	public static function decodeURIComponent( $str ) {
		// Nothing to do if we receive an array.
		if ( is_array( $str ) ) {
			return $str;
		}

		if ( is_string( $str ) ) {
			$revert = array( '!' => '%21', '*' => '%2A', "'" => '%27', '(' => '%28', ')' => '%29' );
			$str    = rawurldecode( strtr( $str, $revert ) );
		}

		return $str;
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

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', '__plugin_txtd' ), null );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.5.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', '__plugin_txtd' ), null );
	}
}
