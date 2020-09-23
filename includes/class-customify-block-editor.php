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

		/*
		 * Selectors that we will use to constrain CSS rules to certain scopes.
		 */
		public static $editor_namespace_selector = '.edit-post-visual-editor.editor-styles-wrapper';
		public static $title_namespace_selector = '.editor-styles-wrapper .editor-post-title__block';
		public static $title_input_namespace_selector = '.editor-styles-wrapper .editor-post-title__block .editor-post-title__input';
		public static function get_block_namespace_selector() {
			global $wp_version;

			$is_old_wp_version = version_compare($wp_version, '5.4', '<');

			if( $is_old_wp_version ) {
				return '.edit-post-visual-editor.editor-styles-wrapper .editor-block-list__block';
			}

			return '.edit-post-visual-editor.editor-styles-wrapper .block-editor-block-list__block';
		}

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

			// Styles and scripts when editing.
			add_action( 'enqueue_block_editor_assets', array( $this, 'dynamic_styles_scripts' ), 999 );

			// Styles on the front end.
			add_action( 'enqueue_block_assets', array( $this, 'frontend_styles' ), 999 );

			add_action( 'admin_init', array( $this, 'editor_color_palettes' ), 20 );
		}

		/**
		 * Determine if Gutenberg is supported.
		 *
		 * @return bool
		 * @since 2.2.0
		 *
		 */
		public function is_supported() {
			$gutenberg = false;

			// Determine if the block editor is active for the frontend.
			if ( has_action( 'enqueue_block_assets' ) ) {
				// Gutenberg is installed and activated.
				$gutenberg = true;
			}

			// Determine if the block editor is being used in the WP admin.
			$current_screen = get_current_screen();
			if ( is_admin() && method_exists( $current_screen, 'is_block_editor' ) && get_current_screen()->is_block_editor() ) {
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
			$theme_dir_uri = get_template_directory_uri();
			$theme_slug    = get_template();

			$handle   = 'wp-edit-post'; // this is better than nothing as it is the main editor style.
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
			$style_css_uri = get_template_directory_uri() . '/style.css';
			$theme_slug    = get_template();

			$handle   = 'wp-block-library'; // this is better than nothing as it is the main editor frontend style.
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
				     && false !== strpos( $style->src, '.css' ) ) {
					$handle = $style->handle;
					break;
				}
			}

			return $handle;
		}

		/**
		 * Output Customify's dynamic styles and scripts in the Gutenberg context.
		 *
		 * @since 2.2.0
		 */
		public function dynamic_styles_scripts() {
			if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'enable_editor_style', true ) ) {
				return;
			}

			require_once( PixCustomifyPlugin()->get_base_path() . 'includes/class-customify-fonts-global.php' );

			$enqueue_parent_handle = $this->get_editor_style_handle();
			if ( empty( $enqueue_parent_handle ) ) {
				return;
			}

			wp_register_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader',
				plugins_url( 'js/vendor/webfontloader-1-6-28.min.js', PixCustomifyPlugin()->get_file() ), array('wp-editor'), null );

			add_filter( 'customify_font_css_selector', array( $this, 'gutenbergify_font_css_selectors' ), 10, 2 );
			Customify_Fonts_Global::instance()->enqueue_frontend_scripts_styles();
			wp_add_inline_style( $enqueue_parent_handle, Customify_Fonts_Global::instance()->getFontsDynamicStyle() );
			remove_filter( 'customify_font_css_selector', array( $this, 'gutenbergify_font_css_selectors' ), 10 );

			add_filter( 'customify_css_selector', array( $this, 'gutenbergify_css_selectors' ), 10, 2 );
			wp_add_inline_style( $enqueue_parent_handle, PixCustomifyPlugin()->customizer->get_dynamic_style() );
			remove_filter( 'customify_css_selector', array( $this, 'gutenbergify_css_selectors' ), 10 );

			// Add color palettes classes.
			wp_add_inline_style( $enqueue_parent_handle, $this->editor_color_palettes_css_classes() );
		}

		public function frontend_styles() {
			$enqueue_parent_handle = $this->get_frontend_style_handle();
			if ( empty( $enqueue_parent_handle ) ) {
				return;
			}

			// Add color palettes classes.
			wp_add_inline_style( $enqueue_parent_handle, $this->editor_color_palettes_css_classes() );
		}

		/**
		 * @param string $selectors
		 * @param array $css_property
		 *
		 * @return string
		 */
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
						$new_selectors[] = preg_replace( '/^(html body|body|html)/', self::get_block_namespace_selector(), $selector );
						$new_selectors[] = preg_replace( '/^(html body|body|html)/', self::$title_namespace_selector, $selector );
					}
					continue;
				}

				// If we encounter selectors that seem that they could target the post title,
				// we will add selectors for the Gutenberg title also.
				if ( preg_match( self::$title_regex, $selector ) ) {
					$new_selectors[] = preg_replace( self::$title_regex, self::$title_input_namespace_selector, $selector );
				}

				$new_selectors[] = self::get_block_namespace_selector() . ' ' . $selector;
			}

			return implode( ', ', $new_selectors );
		}

		/**
		 * @param array $selectors An array of standardized, cleaned selectors where the key is the selector and the value is possible details array.
		 *
		 * @return array
		 */
		public function gutenbergify_font_css_selectors( $selectors ) {

			$new_selectors = array();
			foreach ( $selectors as $selector => $selector_details ) {
				// If the selector matches the excluded, skip it.
				if ( $this->preg_match_any( self::$excluded_selectors_regex, $selector ) ) {
					continue;
				}

				// If the selector is already Gutenbergy, we will not do anything to it
				if ( preg_match( self::$gutenbergy_selector_regex, $selector ) ) {
					$new_selectors[ $selector ] = $selector_details;
					continue;
				}

				// We will let :root selectors be
				if ( ':root' === $selector ) {
					$new_selectors[ $selector ] = $selector_details;
					continue;
				}

				// For root html elements, we will not prefix them, but replace them with the block and title namespace.
				if ( preg_match( self::$root_regex, $selector ) ) {
					$new_selector = preg_replace( '/^(html body|body|html|)/', self::get_block_namespace_selector(), $selector );
					$new_selectors[ $new_selector ] = $selector_details;
					$new_selector = preg_replace( '/^(html body|body|html)/', self::$title_namespace_selector, $selector );
					$new_selectors[ $new_selector ] = $selector_details;
					continue;
				}

				// If we encounter selectors that seem that they could target the post title,
				// we will add selectors for the Gutenberg title also.
				if ( preg_match( self::$title_regex, $selector ) ) {
					$new_selector = preg_replace( self::$title_regex, self::$title_input_namespace_selector, $selector );
					$new_selectors[ $new_selector ] = $selector_details;
				}

				$selector = self::get_block_namespace_selector() . ' ' . $selector;
				$new_selectors[ $selector ] = $selector_details;
			}

			return $new_selectors;
		}

		/**
		 * Preg_match a series of regex against a subject.
		 *
		 * @param string|array $regexes
		 * @param string       $subject
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

			return preg_split( '#[\s]*,[\s]*#', $value, - 1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		}

		/**
		 * Add the SM Color Palettes to the editor sidebar.
		 */
		public function editor_color_palettes() {
			// Bail if Color Palettes are not supported
			if ( ! Customify_Color_Palettes::instance()->is_supported() ) {
				return;
			}

			$options_details = PixCustomifyPlugin()->get_options_configs();

			$master_color_control_ids = Customify_Color_Palettes::instance()->get_all_master_color_controls_ids();
			if ( empty( $master_color_control_ids ) ) {
				return;
			}

			$editor_color_palettes = array();
			foreach ( $master_color_control_ids as $control_id ) {
				if ( empty( $options_details[ $control_id ] ) ) {
					continue;
				}

				$value = get_option( $control_id . '_final' );
				if ( empty( $value ) ) {
					$value = $options_details[ $control_id ][ 'default' ];
				}

				if ( empty( $value ) ) {
					continue;
				}

				$editor_color_palettes[] = array(
					'name'  => $options_details[ $control_id ]['label'],
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

			$options_details = PixCustomifyPlugin()->get_options_configs();

			$master_color_control_ids = Customify_Color_Palettes::instance()->get_all_master_color_controls_ids();
			if ( empty( $master_color_control_ids ) ) {
				return '';
			}

			// Build styles.
			$css = '';
			foreach ( $master_color_control_ids as $control_id ) {
				if ( empty( $options_details[ $control_id ] ) ) {
					continue;
				}

				$value = get_option( $control_id . '_final' );
				if ( empty( $value ) ) {
					continue;
				}

				$editor_color_palettes[] = array(
					'name'  => $options_details[ $control_id ]['label'],
					'slug'  => $control_id,
					'color' => esc_html( $value ),
				);

				$color_in_kebab_case = self::to_kebab_case( $control_id );

				$css .= '.has-' . $color_in_kebab_case . '-color { color: ' . esc_attr( $value ) . ' !important; }';
				$css .= '.has-' . $color_in_kebab_case . '-background-color { background-color: ' . esc_attr( $value ) . '; }';
			}

			return wp_strip_all_tags( $css );
		}

		public static function to_kebab_case( $string ) {
			return implode( '-', array_map( '\strtolower', preg_split( "/[\n\r\t -_]+/", preg_replace( "/['\x{2019}]/u", '', $string ), - 1, PREG_SPLIT_NO_EMPTY ) ) );
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

			_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ), null );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 2.2.0
		 */
		public function __wakeup() {

			_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ), null );
		}
	}
}
