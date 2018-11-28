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
	public static $editor_namespace_selector = '.edit-post-visual-editor.editor-styles-wrapper';
	public static $title_namespace_selector = '.editor-styles-wrapper .editor-post-title__block';
	public static $title_input_namespace_selector = '.editor-styles-wrapper .editor-post-title__block .editor-post-title__input';
	public static $block_namespace_selector = '.edit-post-visual-editor.editor-styles-wrapper .editor-block-list__block';

	/**
	 * Regexes
	 */
	public static $gutenbergy_selector_regex = '/^(\.edit-post-visual-editor|\.editor-block-list__block).*$/';
	public static $root_regex = '/^(body|html).*$/';
	public static $title_regex = '/^(h1|h1\s+.*|\.single\s*\.entry-title.*|\.entry-title.*|\.page-title.*|\.article__?title.*)$/';
	/* Regexes based on which we will ignore selectors = do not include them in the selector list for a certain rule. */
	public static $excluded_selectors_regex = array(
		// We don't want to mess with buttons as we have a high likelihood of messing with the Gutenberg toolbar.
		'/^\s*button/',
		'/^\s*\.button/',
		'/^\s*input/',
		'/^\s*select/',
		'/^\s*#/', // ignore all ids
		'/^\s*div#/', // ignore all ids

		'/\.u-/',
		'/\.c-/',
		'/\.o-/',
		'/\.site-/',
		'/\.card/',

		'/^\s*\.archive/',
		'/^\s*\.search/',
		'/^\s*\.no-results/',
		'/^\s*\.home/',
		'/^\s*\.blog/',
		'/^\s*\.site-/',
		'/\.search/',
		'/\.page/',
		'/\.mce-content-body/',
		'/\.attachment/',
		'/\.mobile/',

		'/\.sticky/',
		'/\.custom-logo-link/',

		'/\.entry-meta/',
		'/\.entry-footer/',
		'/\.header-meta/',
		'/\.nav/',
		'/\.main-navigation/',
		'/navbar/',
		'/comment/',
		'/\.dummy/',
		'/\.back-to-top/',
		'/\.page-numbers/',
		'/\.featured/',
		'/\.widget/',
		'/\.edit-link/',
		'/\.posted-on/',
		'/\.cat-links/',
		'/\.posted-by/',
		'/\.more-link/',

		'/jetpack/',
		'/wpforms/',
		'/contact-form/',
		'/sharedaddy/',
	);

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

		add_action( 'enqueue_block_editor_assets', array( $this, 'dynamic_styles' ), 999 );

		// Styles on the front end.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ), 999 );

		add_action( 'init', array( $this, 'editor_color_palettes' ), 20 );
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

	public function get_editor_style_handle() {
		global $wp_styles;
		if ( ! ( $wp_styles instanceof WP_Styles ) ) {
			return '';
		}

		// We need to look into the registered theme stylesheets and get the one most likely to be used for Gutenberg.
		// Thus we can attach inline styles to it.
		$theme_dir_uri = get_stylesheet_directory_uri();
		$theme_slug = get_stylesheet();

		$handle = 'wp-edit-post'; // this is better than nothing as it is the main editor style.
		$reversed = array_reverse( $wp_styles->registered );
		/** @var _WP_Dependency $style */
		foreach ( $reversed as $style ) {
			// This is the most precise.
			if ( 0 === strpos( $style->src, $theme_dir_uri ) ) {
				$handle = $style->handle;
				break;
			}

			// If it is prefixed with the theme slug, it is good also.
			if ( 0 === strpos( $style->handle, $theme_slug . '-' ) || 0 === strpos( $style->handle, $theme_slug . '_' ) ) {
				$handle = $style->handle;
				break;
			}
		}

		return $handle;
	}

	public function get_frontend_style_handle() {
		global $wp_styles;
		if ( ! ( $wp_styles instanceof WP_Styles ) ) {
			return '';
		}

		// We need to look into the registered theme stylesheets and get the one most likely to be used for Gutenberg.
		// Thus we can attach inline styles to it.
		$style_css_uri = get_stylesheet_uri();
		$theme_slug = get_stylesheet();

		$handle = 'wp-edit-post'; // this is better than nothing as it is the main editor style.
		$reversed = array_reverse( $wp_styles->registered );
		/** @var _WP_Dependency $style */
		foreach ( $reversed as $style ) {
			// This is the most precise.
			if ( 0 === strpos( $style->src, $style_css_uri ) ) {
				$handle = $style->handle;
				break;
			}

			// If it is prefixed with the theme slug, it is good also.
			if ( ( 0 === strpos( $style->handle, $theme_slug . '-' ) || 0 === strpos( $style->handle, $theme_slug . '_' ) )
				&& false !== strpos( $style->src, '.css') ) {
				$handle = $style->handle;
				break;
			}
		}

		return $handle;
	}

	/**
	 * Output Customify's dynamic styles in the Gutenberg context.
	 *
	 * @since 2.2.0
	 */
	public function dynamic_styles() {
		$enqueue_parent_handle = $this->get_editor_style_handle();

		if ( PixCustomifyPlugin()->get_plugin_setting( 'enable_editor_style', true ) ) {
			add_filter( 'customify_typography_css_selector', array( $this, 'gutenbergify_font_css_selectors' ), 10, 2 );
			wp_add_inline_script( $enqueue_parent_handle, PixCustomifyPlugin()->get_typography_dynamic_script() );
			wp_add_inline_style( $enqueue_parent_handle, PixCustomifyPlugin()->get_typography_dynamic_style() );
			remove_filter( 'customify_typography_css_selector', array( $this, 'gutenbergify_font_css_selectors' ), 10 );

			add_filter( 'customify_font_css_selector', array( $this, 'gutenbergify_font_css_selectors' ), 10, 2 );
			wp_add_inline_script( $enqueue_parent_handle, Customify_Font_Selector::instance()->get_fonts_dynamic_script() );
			wp_add_inline_style( $enqueue_parent_handle, Customify_Font_Selector::instance()->get_fonts_dynamic_style() );
			remove_filter( 'customify_font_css_selector', array( $this, 'gutenbergify_font_css_selectors' ), 10 );

			add_filter( 'customify_css_selector', array( $this, 'gutenbergify_css_selectors' ), 10, 2 );
			wp_add_inline_style( $enqueue_parent_handle, PixCustomifyPlugin()->get_dynamic_style() );
			remove_filter( 'customify_css_selector', array( $this, 'gutenbergify_css_selectors' ), 10 );

			// Add color palettes classes.
			wp_add_inline_style( $enqueue_parent_handle, $this->editor_color_palettes_css_classes() );
		}
	}

	public function frontend_styles() {
		$enqueue_parent_handle = $this->get_editor_style_handle();

		// Add color palettes classes.
		wp_add_inline_style( $enqueue_parent_handle, $this->editor_color_palettes_css_classes() );
	}

	public function gutenbergify_css_selectors( $selectors, $css_property ) {

		// Treat the selector(s) as an array.
		$selectors = $this->maybeExplodeSelectors( $selectors );

		$new_selectors = array();
		foreach ( $selectors as $selector ) {
			// Clean up
			$selector = trim( $selector );

			// If the selector matches the excluded, skip it.
			if ( $this->preg_match_any( self::$excluded_selectors_regex, $selector ) ) {
				continue;
			}

			// If the selector is already Gutenbergy, we will not do anything to it
			if ( preg_match( self::$gutenbergy_selector_regex, $selector ) ) {
				$new_selectors[] = $selector;
				continue;
			}

			// We will let :root selectors be
			if ( ':root' === $selector ) {
				$new_selectors[] = $selector;
				continue;
			}

			// For root html elements, we will not prefix them, but replace them with the block and title namespace.
			if ( preg_match( self::$root_regex, $selector ) ) {
				// We will ignore pseudo-selectors
				if ( preg_match( '/^(body|html)[\:\+]+.*$/', $selector ) ) {
					continue;
				}

				// When it comes to background properties applied at the body level, we need to scope to the editor namespace
				if ( isset( $css_property['property'] ) && 0 === strpos( $css_property['property'], 'background' ) ) {
					$new_selectors[] = preg_replace( '/^(html body|body|html)/', self::$editor_namespace_selector, $selector );
				} else {
					$new_selectors[] = preg_replace( '/^(html body|body|html)/', self::$block_namespace_selector, $selector );
					$new_selectors[] = preg_replace( '/^(html body|body|html)/', self::$title_namespace_selector, $selector );
				}
				continue;
			}

			// If we encounter selectors that seem that they could target the post title,
			// we will add selectors for the Gutenberg title also.
			if ( preg_match( self::$title_regex, $selector ) ) {
				$new_selectors[] = preg_replace( self::$title_regex, self::$title_input_namespace_selector, $selector );
			}

			$new_selectors[] = self::$block_namespace_selector . ' ' . $selector;
		}

		return implode( ', ', $new_selectors );
	}

	public function gutenbergify_font_css_selectors( $selectors, $font ) {

		// Treat the selector(s) as an array.
		$selectors = $this->maybeExplodeSelectors( $selectors );

		$new_selectors = array();
		foreach ( $selectors as $selector ) {
			// Clean up
			$selector = trim( $selector );

			// If the selector matches the excluded, skip it.
			if ( $this->preg_match_any( self::$excluded_selectors_regex, $selector ) ) {
				continue;
			}

			// If the selector is already Gutenbergy, we will not do anything to it
			if ( preg_match( self::$gutenbergy_selector_regex, $selector ) ) {
				$new_selectors[] = $selector;
				continue;
			}

			// We will let :root selectors be
			if ( ':root' === $selector ) {
				$new_selectors[] = $selector;
				continue;
			}

			// For root html elements, we will not prefix them, but replace them with the block and title namespace.
			if ( preg_match( self::$root_regex, $selector ) ) {
				$new_selectors[] = preg_replace( '/^(html body|body|html|)/', self::$block_namespace_selector, $selector );
				$new_selectors[] = preg_replace( '/^(html body|body|html)/', self::$title_namespace_selector, $selector );
				continue;
			}

			// If we encounter selectors that seem that they could target the post title,
			// we will add selectors for the Gutenberg title also.
			if ( preg_match( self::$title_regex, $selector ) ) {
				$new_selectors[] = preg_replace( self::$title_regex, self::$title_input_namespace_selector, $selector );
			}

			$new_selectors[] = self::$block_namespace_selector . ' ' . $selector;
		}

		return implode( ', ', $new_selectors );
	}

	/**
	 * Preg_match a series of regex against a subject.
	 *
	 * @param string|array $regexes
	 * @param string $subject
	 *
	 * @return bool Returns true if at least one of the regex matches, false otherwise.
	 */
	public function preg_match_any( $regexes, $subject ) {
		if ( is_string( $regexes ) ) {
			$regexes = array( $regexes );
		}

		if ( ! is_array( $regexes ) ) {
			return false;
		}

		foreach ( $regexes as $regex ) {
			if ( preg_match( $regex, $subject ) ) {
				return true;
			}
		}

		return false;
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
	 * Add the SM Color Palettes to the editor sidebar.
	 */
	public function editor_color_palettes() {
		// Bail if Color Palettes are not supported
		if ( ! Customify_Color_Palettes::instance()->is_supported() ) {
			return;
		}

		$options = PixCustomifyPlugin()->get_options_configs();

		$master_color_control_ids = Customify_Color_Palettes::instance()->get_all_master_color_controls_ids();
		if ( empty( $master_color_control_ids ) ) {
			return;
		}

		$editor_color_palettes = array();
		foreach ( $master_color_control_ids as $control_id ) {
			if ( empty( $options[ $control_id ] ) ) {
				continue;
			}

			$value = get_option( $control_id . '_final' );
			if ( empty( $value ) ) {
				continue;
			}

			$editor_color_palettes[] = array(
				'name'  => $options[ $control_id ]['label'],
				'slug'  => $control_id,
				'color' => esc_html( $value ),
			);
		}

		if ( ! empty( $editor_color_palettes ) ) {
			/**
			 * Custom colors for use in the editor.
			 *
			 * @link https://wordpress.org/gutenberg/handbook/reference/theme-support/
			 */
			add_theme_support(
				'editor-color-palette',
				$editor_color_palettes
			);
		}
	}

	/**
	 * Generate the special classes for our colors.
	 */
	public function editor_color_palettes_css_classes() {
		// Bail if Color Palettes are not supported
		if ( ! Customify_Color_Palettes::instance()->is_supported() ) {
			return '';
		}

		$options = PixCustomifyPlugin()->get_options_configs();

		$master_color_control_ids = Customify_Color_Palettes::instance()->get_all_master_color_controls_ids();
		if ( empty( $master_color_control_ids ) ) {
			return '';
		}

		// Build styles.
		$css  = '';
		foreach ( $master_color_control_ids as $control_id ) {
			if ( empty( $options[ $control_id ] ) ) {
				continue;
			}

			$value = get_option( $control_id . '_final' );
			if ( empty( $value ) ) {
				continue;
			}

			$editor_color_palettes[] = array(
				'name'  => $options[ $control_id ]['label'],
				'slug'  => $control_id,
				'color' => esc_html( $value ),
			);

			$color_in_kebab_case = self::to_kebab_case($control_id);

			$css .= '.has-' . $color_in_kebab_case . '-color { color: ' . esc_attr( $value ) . ' !important; }';
			$css .= '.has-' . $color_in_kebab_case . '-background-color { background-color: ' . esc_attr( $value ) . '; }';
		}
		return wp_strip_all_tags( $css );
	}

	public static function to_kebab_case( $string ) {
		return implode('-', array_map('\strtolower', preg_split( "/[\n\r\t -_]+/", preg_replace("/['\x{2019}]/u", '', $string), -1, PREG_SPLIT_NO_EMPTY )));
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
