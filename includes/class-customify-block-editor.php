<?php
/**
 * This is the class that handles the overall logic for integration with the new Gutenberg Editor (WordPress 5.0+).
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Block_Editor' ) ) {

	class Customify_Block_Editor {

		/**
		 * Holds the only instance of this class.
		 * @var     null|Customify_Block_Editor
		 * @access  protected
		 * @since   2.2.0
		 */
		protected static $_instance = null;



		/**
		 * Constructor.
		 *
		 * @since 2.2.0
		 */
		protected function __construct() {

		}

		/**
		 * Main Customify_Block_Editor Instance
		 *
		 * Ensures only one instance of Customify_Block_Editor is loaded or can be loaded.
		 *
		 * @return Customify_Block_Editor Main Customify_Block_Editor instance
		 * @since  2.2.0
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
		 * @since 2.2.0
		 */
		public function __clone() {

			_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', '__plugin_txtd' ), null );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 2.2.0
		 */
		public function __wakeup() {

			_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', '__plugin_txtd' ), null );
		}
	}
}
