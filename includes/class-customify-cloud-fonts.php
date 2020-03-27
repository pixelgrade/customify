<?php
/**
 * This is the class that handles the logic for Cloud Fonts.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Cloud_Fonts' ) ) :

class Customify_Cloud_Fonts {

	/**
	 * Holds the only instance of this class.
	 * @var     null|Customify_Cloud_Fonts
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
		$this->init();
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
		/*
		 * Handle the cloud fonts preprocessing.
		 */
		add_filter( 'customify_get_cloud_fonts', array( $this, 'preprocess_config' ), 5, 1 );

		/*
		 * Add the cloud fonts to the Font Selector
		 */
		add_filter( 'customify_cloud_fonts', array( $this, 'add_fonts_to_font_selector' ), 10, 1 );
	}

	public function add_fonts_to_font_selector( $fonts ) {
		if ( empty( $fonts ) ) {
			$fonts = array();
		}

		if ( ! $this->is_supported() ) {
			return $fonts;
		}

		$fonts = array_merge( $fonts, $this->get_fonts() );

		return $fonts;
	}

	/**
	 * Preprocess the cloud fonts configuration.
	 *
	 * Convert the cloud font list to a format suitable for Customify.
	 *
	 * @since 2.7.0
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function preprocess_config( $config ) {
		if ( empty( $config ) ) {
			return $config;
		}

		$new_config = array();
		foreach ( $config as $font_id => $font_config ) {
			if ( empty( $font_config['font_family'] ) || empty( $font_config['stylesheet'] ) ) {
				continue;
			}


			$new_config[ $font_config['font_family'] ] = $this->preprocess_font_config( $font_config );
		}

		return $new_config;
	}

	/**
	 * Preprocess a cloud font config before using it.
	 *
	 * @since 2.7.0
	 *
	 * @param array $font_config
	 *
	 * @return array
	 */
	private function preprocess_font_config( $font_config ) {
		if ( empty( $font_config ) ) {
			return $font_config;
		}

		// We need to convert the received data structure to the one expected by Customify.
		return array(
			'family' => $font_config['font_family'],
			'src' => $font_config['stylesheet'],
			'variants' => empty( $font_config['variants'] ) ? array() : $font_config['variants'],
		);
	}

	/**
	 * Get the cloud fonts configuration.
	 *
	 * @since 2.7.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array
	 */
	public function get_fonts( $skip_cache = false ) {
		// Make sure that the Design Assets class is loaded.
		require_once 'lib/class-customify-design-assets.php';

		// Get the design assets data.
		$design_assets = Customify_Design_Assets::instance()->get( $skip_cache );
		if ( false === $design_assets || empty( $design_assets['cloud_fonts'] ) ) {
			$config = $this->get_default_config();
		} else {
			$config = $design_assets['cloud_fonts'];
		}

		return apply_filters( 'customify_get_cloud_fonts', $config );
	}

	/**
	 * Get the default (hard-coded) cloud fonts configuration.
	 *
	 * This is only a fallback config in case we can't communicate with the cloud, the first time.
	 *
	 * @since 2.7.0
	 *
	 * @return array
	 */
	protected function get_default_config() {
		$default_config = array(
		);

		return apply_filters( 'customify_style_manager_default_cloud_fonts', $default_config );
	}

	/**
	 * Determine if Cloud Fonts are supported.
	 *
	 * @since 2.7.0
	 *
	 * @return bool
	 */
	public function is_supported() {
		// For now we will only use the fact that Style Manager is supported.
		return apply_filters( 'style_manager_cloud_fonts_are_supported', Customify_Style_Manager::instance()->is_supported() );
	}

	/**
	 * Main Customify_Cloud_Fonts Instance
	 *
	 * Ensures only one instance of Customify_Cloud_Fonts is loaded or can be loaded.
	 *
	 * @since  2.7.0
	 * @static
	 *
	 * @return Customify_Cloud_Fonts Main Customify_Cloud_Fonts instance
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.7.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html__( 'You should not do that!', 'customify' ), null );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.7.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ),  null );
	}
}

endif;
