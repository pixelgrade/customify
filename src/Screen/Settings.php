<?php
/**
 * Settings screen provider.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package PixelgradeLT
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container\Container;
use Carbon_Fields\Field;
use Pixelgrade\Customify\Capabilities;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Settings screen provider class.
 *
 * @since 3.0.0
 */
class Settings extends AbstractHookProvider {

	const MENU_SLUG = 'customify';

	/**
	 * User messages to display in the WP admin.
	 *
	 * @var array
	 */
	protected $user_messages = [
		'error'   => [],
		'warning' => [],
		'info'    => [],
	];

	/**
	 * Create the setting screen.
	 */
	public function __construct() {

	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		//		if ( is_multisite() ) {
		//			add_action( 'network_admin_menu', [ $this, 'setup' ] );
		//		} else {
		//			add_action( 'admin_menu', [ $this, 'setup' ] );
		//		}

		$this->add_action( 'plugins_loaded', 'carbonfields_load' );
		$this->add_action( 'carbon_fields_register_fields', 'setup' );

		$this->add_filter( 'carbon_fields_theme_options_container_admin_only_access', 'disable_default_access', 10, 3 );
	}

	/**
	 * Setup the settings page and options.
	 *
	 * @since 3.0.0
	 */
	public function setup() {
		$parent_slug = 'options-general.php';
		if ( is_network_admin() ) {
			$parent_slug = 'settings.php';
		}

		Container::make( 'theme_options', 'customify_options', esc_html__( 'Customify', '__plugin_txtd' ) )
		         ->set_page_parent( $parent_slug )
		         ->set_page_menu_title( esc_html__( 'Pixelgrade Customify', '__plugin_txtd' ) )
		         ->set_page_file( self::MENU_SLUG )
			//->where( 'current_user_capability', '=', Capabilities::MANAGE_OPTIONS )
			     ->add_tab( esc_html__( 'General', '__plugin_txtd' ), array(
				Field::make( 'select', 'values_store_mod', esc_html__( 'Store values as:', '__plugin_txtd' ) )
				     ->set_help_text( esc_html__( 'You can store the values globally so you can use them with other themes or store them as a "theme_mod" which will make an individual set of options only for the current theme', '__plugin_txtd' ) )
				     ->set_options( [
					     'option'    => esc_html__( 'Option (global options)', '__plugin_txtd' ),
					     'theme_mod' => esc_html__( 'Theme Mod (per theme options)', '__plugin_txtd' ),
				     ] )
				     ->set_default_value( 'theme_mod' )
				     ->set_required( true ),
				Field::make( 'set', 'disable_default_sections', esc_html__( 'Disable default sections', '__plugin_txtd' ) )
				     ->set_help_text( esc_html__( 'You can disable default sections', '__plugin_txtd' ) )
				     ->set_options( [
					     'nav'               => esc_html__( 'Navigation', '__plugin_txtd' ),
					     'static_front_page' => esc_html__( 'Front Page', '__plugin_txtd' ),
					     'title_tagline'     => esc_html__( 'Title', '__plugin_txtd' ),
					     'colors'            => esc_html__( 'Colors', '__plugin_txtd' ),
					     'background_image'  => esc_html__( 'Background', '__plugin_txtd' ),
					     'header_image'      => esc_html__( 'Header', '__plugin_txtd' ),
					     'widgets'           => esc_html__( 'Widgets', '__plugin_txtd' ),
				     ] ),
				Field::make( 'checkbox', 'enable_reset_buttons', esc_html__( 'Enable Reset Buttons', '__plugin_txtd' ) )
				     ->set_help_text( esc_html__( 'You can enable "Reset to defaults" buttons for panels / sections or all settings. We have disabled this feature by default to avoid accidental resets. If you are sure that you need it please enable this.', '__plugin_txtd' ) )
				     ->set_option_value( 'yes' ),
				Field::make( 'checkbox', 'enable_editor_style', esc_html__( 'Enable Editor Style', '__plugin_txtd' ) )
				     ->set_help_text( esc_html__( 'The styling added by Customify in front-end can be added in the WordPress editor too by enabling this option', '__plugin_txtd' ) )
				     ->set_option_value( 'yes' )
				     ->set_default_value( 'yes' ),
			) )
		         ->add_tab( esc_html__( 'Output', '__plugin_txtd' ), [
			         Field::make( 'select', 'style_resources_location', esc_html__( 'Styles location:', '__plugin_txtd' ) )
			              ->set_help_text( esc_html__( 'Here you can decide where to put your style output, in header or footer', '__plugin_txtd' ) )
			              ->set_options( [
				              'wp_head'   => esc_html__( 'In header (just before the head tag)', '__plugin_txtd' ),
				              'wp_footer' => esc_html__( 'Footer (just before the end of the body tag)', '__plugin_txtd' ),
			              ] )
			              ->set_default_value( 'wp_footer' )
			              ->set_required( true ),
		         ] )
		         ->add_tab( esc_html__( 'Typography', '__plugin_txtd' ), [
			         Field::make( 'checkbox', 'enable_typography', esc_html__( 'Enable Typography Options', '__plugin_txtd' ) )
			              ->set_option_value( 'yes' )
			              ->set_default_value( 'yes' ),
			         Field::make( 'checkbox', 'typography_system_fonts', esc_html__( 'Use system fonts', '__plugin_txtd' ) )
			              ->set_help_text( esc_html__( 'Would you like to have system fonts available in the font controls?', '__plugin_txtd' ) )
			              ->set_option_value( 'yes' )
			              ->set_default_value( 'yes' )
				         ->set_conditional_logic( [
					         [
						         'field' => 'enable_typography',
						         'value' => true,
					         ],
				         ] ),
			         Field::make( 'checkbox', 'typography_google_fonts', esc_html__( 'Use Google fonts', '__plugin_txtd' ) )
			              ->set_help_text( esc_html__( 'Would you like to have Google fonts available in the font controls?', '__plugin_txtd' ) )
			              ->set_option_value( 'yes' )
			              ->set_default_value( 'yes' )
				         ->set_conditional_logic( [
					         [
						         'field' => 'enable_typography',
						         'value' => true,
					         ],
				         ] ),
			         Field::make( 'checkbox', 'typography_group_google_fonts', esc_html__( 'Group Google fonts', '__plugin_txtd' ) )
			              ->set_help_text( esc_html__( 'You can chose to see the Google fonts in groups', '__plugin_txtd' ) )
			              ->set_option_value( 'yes' )
			              ->set_default_value( 'yes' )
			              ->set_conditional_logic( [
				              [
					              'field' => 'enable_typography',
					              'value' => true,
				              ],
				              [
					              'field' => 'typography_google_fonts',
					              'value' => true,
				              ],
			              ] ),
			         Field::make( 'checkbox', 'typography_cloud_fonts', esc_html__( 'Use cloud fonts', '__plugin_txtd' ) )
			              ->set_help_text( esc_html__( 'Would you to have Cloud fonts available in the font controls?', '__plugin_txtd' ) )
			              ->set_option_value( 'yes' )
			              ->set_default_value( 'yes' )
			              ->set_conditional_logic( [
				              [
					              'field' => 'enable_typography',
					              'value' => true,
				              ],
			              ] ),

		         ] )
		         ->add_tab( esc_html__( 'Tools', '__plugin_txtd' ), [
			         Field::make( 'select', 'reset_theme_mod', esc_html__( 'Reset Theme Mod', '__plugin_txtd' ) )
			              ->set_help_text( esc_html__( 'Here you can decide where to put your style output, in header or footer', '__plugin_txtd' ) )
			              ->set_options( [
				              'wp_head'   => esc_html__( 'In header (just before the head tag)', '__plugin_txtd' ),
				              'wp_footer' => esc_html__( 'Footer (just before the end of the body tag)', '__plugin_txtd' ),
			              ] )
			              ->set_default_value( 'wp_footer' )
			              ->set_required( true ),
		         ] );

		//		$page_hook = add_submenu_page(
		//			$parent_slug,
		//			esc_html__( 'Customify', '__plugin_txtd' ),
		//			esc_html__( 'Pixelgrade Customify', '__plugin_txtd' ),
		//			Capabilities::MANAGE_OPTIONS,
		//			self::MENU_SLUG,
		//			[ $this, 'render_screen' ]
		//		);
		//
		//		add_action( 'load-' . $page_hook, [ $this, 'load_screen' ] );
	}

	/**
	 * Set up the screen.
	 *
	 * @since 3.0.0
	 */
	public function load_screen() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 3.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'pixelgradelt_records-admin' );
		wp_enqueue_style( 'pixelgradelt_records-admin' );
		wp_enqueue_script( 'pixelgradelt_records-package-settings' );
	}

	protected function carbonfields_load() {
		Carbon_Fields::boot();
	}

	protected function disable_default_access( bool $enable, string $container_title, Container $container ): bool {
		// We will define the access ourselves and we don't want the default Carbon Fields behavior.
		if ( 'customify_options' === $container->get_id() ) {
			return false;
		}

		return $enable;
	}

	/**
	 * Retrieve a setting.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key     Setting name.
	 * @param mixed  $default Optional. Default setting value.
	 *
	 * @return mixed
	 */
	protected function get_setting( string $key, $default = null ) {
		$option = get_option( 'pixelgradelt_records' );

		return $option[ $key ] ?? $default;
	}
}
