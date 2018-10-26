<?php
/**
 * This is the class that handles the overall logic for integration with the new Gutenberg Editor.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Gutenberg' ) ) :

class Customify_Gutenberg {

	/**
	 * Holds the only instance of this class.
	 * @var     null|Customify_Gutenberg
	 * @access  protected
	 * @since   2.2.0
	 */
	protected static $_instance = null;

	/*
	 * Selectors that we will use to constrain CSS rules to certain scopes.
	 */
	public static $editor_namespace_selector = '.edit-post-visual-editor[class]';
	public static $title_namespace_selector = '.editor-post-title__block[class]';
	public static $block_namespace_selector = '.editor-block-list__block[class]';

	/**
	 * Constructor.
	 *
	 * @since 2.2.0
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * Initialize this module.
	 *
	 * @since 2.2.0
	 */
	public function init() {

		// Hook up.
		$this->add_hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since 2.2.0
	 */
	public function add_hooks() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'dynamic_styles' ) );
	}

	/**
	 * Determine if Gutenberg is supported.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	public function is_supported() {
		$gutenberg = false;
		if ( has_filter( 'replace_editor', 'gutenberg_init' ) ) {
			// Gutenberg is installed and activated.
			$gutenberg = true;
		}

		return apply_filters( 'customify_gutenberg_is_supported', $gutenberg );
	}

	/**
	 * Output Customify's dynamic styles in the Gutenberg context.
	 *
	 * @since 2.2.0
	 */
	public function dynamic_styles() {
		if ( PixCustomifyPlugin()->get_plugin_setting( 'enable_editor_style', true ) ) {

			add_filter( 'customify_typography_css_selector', array( $this, 'gutenbergify_font_css_selectors' ), 10, 2 );
			PixCustomifyPlugin()->output_typography_dynamic_style();
			remove_filter( 'customify_typography_css_selector', array( $this, 'gutenbergify_font_css_selectors' ), 10 );

			add_filter( 'customify_font_css_selector', array( $this, 'gutenbergify_font_css_selectors' ), 10, 2 );
			Customify_Font_Selector::instance()->output_font_dynamic_style();
			remove_filter( 'customify_font_css_selector', array( $this, 'gutenbergify_font_css_selectors' ), 10 );

			add_filter( 'customify_css_selector', array( $this, 'gutenbergify_css_selectors' ), 10, 2 );
			PixCustomifyPlugin()->output_dynamic_style();
			remove_filter( 'customify_css_selector', array( $this, 'gutenbergify_css_selectors' ), 10 );

		}
	}

	public function gutenbergify_css_selectors( $selectors, $css_property ) {
		$root_regex = '/^(body|html).*$/';

		// Treat the selector(s) as an array.
		$selectors = $this->maybeExplodeSelectors( $selectors );

		$new_selectors = array();
		foreach ( $selectors as $selector ) {
			// If the selector is already Gutenbergy, we will not do anything to it
			if ( preg_match( '/^(\.edit-post-visual-editor|\.editor-block-list__block).*$/', $selector ) ) {
				$new_selectors[] = $selector;
				continue;
			}

			// We will let :root selectors be
			if ( ':root' === $selector ) {
				$new_selectors[] = $selector;
				continue;
			}

			// For root html elements, we will not prefix them, but replace them with the block and title namespace.
			if ( preg_match( $root_regex, $selector ) ) {
				// We will ignore pseudo-selectors
				if ( preg_match( '/^(body|html)[\:\+]+.*$/', $selector ) ) {
					continue;
				}

				$new_selectors[] = preg_replace( '/^(html body|body|html)/', self::$block_namespace_selector, $selector );
				$new_selectors[] = preg_replace( '/^(html body|body|html)/', self::$title_namespace_selector, $selector );
				continue;
			}

			$new_selectors[] = self::$block_namespace_selector . ' ' . $selector;
		}

		return implode( ', ', $new_selectors );
	}

	public function gutenbergify_font_css_selectors( $selectors, $font ) {
		$root_regex = '/^(body|html).*$/';
		$title_regex = '/^(h1|h1\s+.*|\.entry-title.*|\.page-title.*|\.article__?title.*)$/';

		// Treat the selector(s) as an array.
		$selectors = $this->maybeExplodeSelectors( $selectors );

		$new_selectors = array();
		foreach ( $selectors as $selector ) {
			// If the selector is already Gutenbergy, we will not do anything to it
			if ( preg_match( '/^(\.edit-post-visual-editor|\.editor-block-list__block).*$/', $selector ) ) {
				$new_selectors[] = $selector;
				continue;
			}

			// We will let :root selectors be
			if ( ':root' === $selector ) {
				$new_selectors[] = $selector;
				continue;
			}

			// For root html elements, we will not prefix them, but replace them with the block and title namespace.
			if ( preg_match( $root_regex, $selector ) ) {
				$new_selectors[] = preg_replace( '/^(html body|body|html|)/', self::$block_namespace_selector, $selector );
				$new_selectors[] = preg_replace( '/^(html body|body|html)/', self::$title_namespace_selector, $selector );
				continue;
			}

			// If we encounter selectors that seem that they could target the post title,
			// we will add selectors for the Gutenberg title also.
			if ( preg_match( $title_regex, $selector ) ) {
				$new_selectors[] = self::$title_namespace_selector . ' ' . $selector;
			}

			$new_selectors[] = self::$block_namespace_selector . ' ' . $selector;
		}

		return implode( ', ', $new_selectors );
	}

	/**
	 * Attempt to split a string with selectors and return the parts as an array.
	 * If not a string or no comma present, just returns the value.
	 *
	 * @param mixed $value
	 *
	 * @return array|false|string[]
	 */
	public function maybeExplodeSelectors( $value ) {
		if ( ! is_string( $value ) ) {
			return $value;
		}

		return preg_split( '#[\s]*,[\s]*#', $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
	}

	/**
	 * Main Customify_Gutenberg Instance
	 *
	 * Ensures only one instance of Customify_Gutenberg is loaded or can be loaded.
	 *
	 * @since  2.2.0
	 * @static
	 *
	 * @return Customify_Gutenberg Main Customify_Gutenberg instance
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
	 * @since 2.2.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?' ) ), null );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.2.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ),  null );
	} // End __wakeup ()
}

endif;
