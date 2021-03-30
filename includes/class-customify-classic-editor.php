<?php
/**
 * This is the class that handles the overall logic for integration with the classic editor (TinyMCE).
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Classic_Editor' ) ) {

	class Customify_Classic_Editor {

		/**
		 * Holds the only instance of this class.
		 * @var     null|Customify_Classic_Editor
		 * @access  protected
		 * @since   2.7.0
		 */
		protected static $_instance = null;

		/**
		 * Constructor.
		 *
		 * @since 2.7.0
		 */
		protected function __construct() {
			// We will initialize the logic after the plugin has finished with it's configuration (at priority 15).
			add_action( 'init', array( $this, 'init' ), 15 );
		}

		/**
		 * Initialize this module.
		 *
		 * @since 2.7.0
		 */
		public function init() {

			// Hook up.
			$this->add_hooks();
		}

		/**
		 * Initiate our hooks
		 *
		 * @since 2.7.0
		 */
		public function add_hooks() {

		}



		/**
		 * Main Customify_Classic_Editor Instance
		 *
		 * Ensures only one instance of Customify_Classic_Editor is loaded or can be loaded.
		 *
		 * @return Customify_Classic_Editor Main Customify_Classic_Editor instance
		 * @since  2.7.0
		 * @static
		 *
		 */
		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 2.7.0
		 */
		public function __clone() {

			_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', '__plugin_txtd' ), null );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 2.7.0
		 */
		public function __wakeup() {

			_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', '__plugin_txtd' ), null );
		}
	}
}
