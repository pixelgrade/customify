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

		add_action( 'wp_footer', array( $this, 'output_dynamic_style' ), 999999999 );

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

		wp_register_script('ace-editor', $dir . '/js/ace/ace.js', array('jquery'), false, true);
		wp_enqueue_script('live-css-editor',  $dir . '/js/live_css_editor.js', array('ace-editor'), false, true);

	}

	function cle_create_custom_control( $wp_customize ) {

		$wp_customize->add_panel( 'live_css_edit_panel', array(
			'priority'    => 10,
			'capability'  => 'edit_theme_options',
			'title' => 'CSS Editor'
		) );

		$wp_customize->add_section( 'live_css_edit_section', array(
			'priority'   => 910,
			'capability' => 'edit_theme_options',
			'title' => 'CSS Edit',
			'panel' => 'live_css_edit_panel'
		) );

		$saving_type = PixCustomifyPlugin::get_plugin_option( 'values_store_mod', 'option' );

		$wp_customize->add_setting( 'live_css_edit', array(
			'type' => $saving_type,
			'label' => 'CSS Edit'
		) );

		$this_control = new Pix_Customize_CSS_Editor_Control(
			$wp_customize,
			'live_css_edit_control',
			array(
				'label'    => 'Edit Live Css',
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
		<style id="css_editor_output">
			<?php echo $output; ?>
		</style>
	<?php
	}
}

$customify_CSS_Editor = Customify_CSS_Live_Editor::get_instance();



