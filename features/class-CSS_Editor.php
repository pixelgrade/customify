<?php

class Customify_CSS_Live_Editor {

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	protected function __construct() {

		add_action( 'customify_create_custom_control', array( $this, 'cle_create_custom_control' ), 10, 1 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_styles' ), 10 );
		$load_location = PixCustomifyPlugin()->get_plugin_setting( 'style_resources_location', 'wp_head' );

		if ( function_exists( 'wp_custom_css_cb' ) ) {
//			remove_action( 'wp_head', 'wp_custom_css_cb', 101 );
//			add_action( $load_location, 'wp_custom_css_cb', 999999999 );
		} else {
			// keep this for wordpress versions lower than 4.7
			add_action( $load_location, array( $this, 'output_dynamic_style' ), 99 );
		}

		//Check the WordPress version and if there are known problems disable it
		add_filter( 'customify_css_live_editor_enabled', array( $this, 'disable_if_wp_incompatible' ), 10, 1 );
	}

	/**
	 * Return an instance of this class.
	 * @since     1.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	function enqueue_admin_customizer_styles() {

		$dir = plugin_dir_url( __FILE__ );
		$dir = rtrim( $dir, 'features/' );

		wp_enqueue_script( 'customify-ace-editor', $dir . '/js/ace/ace.js', array( 'jquery' ), false, true );

		if ( ! apply_filters( 'customify_css_live_editor_enabled', true ) ) {
			return ;
		}

		wp_enqueue_script( 'live-css-editor', $dir . '/js/live_css_editor.js', array( 'customify-ace-editor' ), false, true );
	}

	function cle_create_custom_control( $wp_customize ) {
		// Allow others to short-circuit us
		if ( ! apply_filters( 'customify_css_live_editor_enabled', true ) ) {

			// port CSS if
			if ( function_exists( 'wp_update_custom_css_post' ) ) {
				// Migrate any existing theme CSS to the core option added in WordPress 4.7.
				$store_type = PixCustomifyPlugin()->get_plugin_setting( 'values_store_mod', 'option' );

				$default_css = __( "/*
 * Welcome to the Custom CSS Editor
 *
 * CSS (Cascading Style Sheets) is a language that helps
 * the browser render your website. You may remove these
 * lines and get started with your own customizations.
 *
 * The generated code will be placed after the theme
 * stylesheets, which means that your rules can take
 * precedence and override the theme CSS rules. Just
 * write here what you want to change, you don't need
 * to copy all your theme's stylesheet content.
 *
 * Getting started with CSS (tutorial):
 * http://bit.ly/css-getting-started
 */

/* An example of a Custom CSS Snippet */
selector {
	color: green;
}", 'customify' );

				if ( $store_type === 'option' ) {
					$CSS = get_option( 'live_css_edit' );
				} elseif ( $store_type === 'theme_mod' ) {
					$CSS = get_theme_mod( 'live_css_edit' );
				}

				if ( ! empty( $CSS ) && $default_css !== $CSS ) {

					$CSS = str_replace( $default_css, '', $CSS );

					$core_css = wp_get_custom_css(); // Preserve any CSS already added to the core option.
					$return   = wp_update_custom_css_post( $core_css . $CSS );
					if ( ! is_wp_error( $return ) ) {
						// Remove the old theme_mod, so that the CSS is stored in only one place moving forward.
						remove_theme_mod( 'custom_theme_css' );
						if ( $store_type === 'option' ) {
							$CSS = delete_option( 'live_css_edit' );
						} elseif ( $store_type === 'theme_mod' ) {
							$CSS = remove_theme_mod( 'live_css_edit' );
						}
					}
				}
			}

			return;
		}

		$wp_customize->add_section( 'live_css_edit_section', array(
			'priority'   => 11,
			'capability' => 'edit_theme_options',
			'title'      => __( 'CSS Editor', 'customify' ),
		) );

		$saving_type = PixCustomifyPlugin()->get_plugin_setting( 'values_store_mod', 'option' );

		$wp_customize->add_setting( 'live_css_edit', array(
			'type'       => $saving_type,
			'label'      => __( 'CSS Editor', 'customify' ),
			'capability' => 'edit_theme_options',
			'transport'  => 'postMessage',
			'default'    => __( "/*
 * Welcome to the Custom CSS Editor
 *
 * CSS (Cascading Style Sheets) is a language that helps
 * the browser render your website. You may remove these
 * lines and get started with your own customizations.
 *
 * The generated code will be placed after the theme
 * stylesheets, which means that your rules can take
 * precedence and override the theme CSS rules. Just
 * write here what you want to change, you don't need
 * to copy all your theme's stylesheet content.
 *
 * Getting started with CSS (tutorial):
 * http://bit.ly/css-getting-started
 */

/* An example of a Custom CSS Snippet */
selector {
	color: green;
}", 'customify' )
		) );

		$this_control = new Pix_Customize_CSS_Editor_Control(
			$wp_customize,
			'live_css_edit_control',
			array(
				'label'    => __( 'Edit Live Css', 'customify' ),
				'section'  => 'live_css_edit_section',
				'settings' => 'live_css_edit',
			)
		);
		$wp_customize->add_control( $this_control );
	}

	function output_dynamic_style() {
		// Allow others to short-circuit us
		if ( ! apply_filters( 'customify_css_live_editor_enabled', true ) ) {
			return;
		}

		$store_type = PixCustomifyPlugin()->get_plugin_setting( 'values_store_mod', 'option' );
		if ( $store_type === 'option' ) {
			$output = get_option( 'live_css_edit' );
		} elseif ( $store_type === 'theme_mod' ) {
			$output = get_theme_mod( 'live_css_edit' );
		}

		if ( empty( $output ) ) {
			return;
		} ?>
		<style id="customify_css_editor_output">
			<?php echo $output; ?>
		</style>
		<?php
	}

	function disable_if_wp_incompatible( $enabled ) {
		global $wp_version;

		// WordPress 4.7 introduced a Customizer CSS editor that conflicts with our own
		// It's best to leave only the one in core
		// So only load our CSS editor for WP version smaller than 4.7
		// We use 4.6.9 to catch the release candidates also
		if ( version_compare( $wp_version, '4.6.9', '>=' ) ) {
			return false;
		}

		return $enabled;
	}
}

$customify_CSS_Editor = Customify_CSS_Live_Editor::get_instance();



