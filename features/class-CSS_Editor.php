<?php

class Customify_CSS_Live_Editor {

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	protected function __construct() {

		add_action( 'customify_create_custom_control', array( $this,'cle_create_custom_control'), 10, 1 );

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_styles' ), 10);

		$load_location = PixCustomifyPlugin::get_plugin_option( 'style_resources_location', 'wp_head' );
		add_action( $load_location, array( $this, 'output_dynamic_style' ), 999999999 );
	}

	/**
	 * Return an instance of this class.
	 * @since     1.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	function enqueue_admin_customizer_styles(){
		$dir = plugin_dir_url( __FILE__ );

		$dir = rtrim( $dir, 'features/' );

		wp_register_script('customify-ace-editor', $dir . '/js/ace/ace.js', array('jquery'), false, true);
		wp_enqueue_script('live-css-editor',  $dir . '/js/live_css_editor.js', array('customify-ace-editor'), false, true);

	}

	function cle_create_custom_control( $wp_customize ) {

		$wp_customize->add_section( 'live_css_edit_section', array(
			'priority'   => 11,
			'capability' => 'edit_theme_options',
			'title' => __('CSS Editor', 'customify_txtd'),
		) );

		$saving_type = PixCustomifyPlugin::get_plugin_option( 'values_store_mod', 'option' );

		$wp_customize->add_setting( 'live_css_edit', array(
			'type' => $saving_type,
			'label' => __('CSS Editor', 'customify_txtd'),
			'capability' => 'edit_theme_options',
			'transport' => 'postMessage',
			'default' => __("/*
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
}", 'customify_txtd')
		) );

		$this_control = new Pix_Customize_CSS_Editor_Control(
			$wp_customize,
			'live_css_edit_control',
			array(
				'label'    => __('Edit Live Css', 'customify_txtd'),
				'section'  => 'live_css_edit_section',
				'settings' => 'live_css_edit',
			)
		);
		$wp_customize->add_control( $this_control );
	}

	function output_dynamic_style() {
		$store_type = PixCustomifyPlugin::get_plugin_option( 'values_store_mod', 'option' );
		if ( $store_type === 'option' ) {
			$output = get_option( 'live_css_edit' );
		} elseif ( $store_type === 'theme_mod' ) {
			$output = get_theme_mod(  'live_css_edit' );
		}

		if ( empty( $output ) ) {
			return;
		} ?>
		<style id="customify_css_editor_output">
			<?php echo $output; ?>
		</style>
		<?php
	}
}

$customify_CSS_Editor = Customify_CSS_Live_Editor::get_instance();



