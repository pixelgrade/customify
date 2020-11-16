<?php
/**
 * This is the class that handles the logic for Color Palettes.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       1.7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Color_Palettes' ) ) :

class Customify_Color_Palettes {

	/**
	 * Holds the only instance of this class.
	 * @var     null|Customify_Font_Palettes
	 * @access  protected
	 * @since   1.7.4
	 */
	protected static $_instance = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.4
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * Initialize this module.
	 *
	 * @since 1.7.4
	 */
	public function init() {
		// Hook up.
		$this->add_hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 1.7.4
	 */
	public function add_hooks() {
		/*
		 * Handle the Customizer Style Manager section config.
		 */
		add_filter( 'customify_filter_fields', array( $this, 'add_style_manager_section_master_colors_config' ), 12, 1 );
		// This needs to come after the external theme config has been applied
		add_filter( 'customify_filter_fields', array( $this, 'add_current_palette_control' ), 110, 1 );
		add_filter( 'customify_filter_fields', array( $this, 'maybe_enhance_dark_mode_control' ), 120, 1 );

		/*
		 * Scripts enqueued in the Customizer.
		 */
		add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_scripts' ), 10 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_scripts' ), 10 );

		/*
		 * Handle the logic on settings update/save.
		 */
		add_action( 'customize_save_after', array( $this, 'update_custom_palette_in_use' ), 10, 1 );

		/**
		 * Add color palettes usage to site data.
		 */
		add_filter( 'customify_style_manager_get_site_data', array( $this, 'add_palettes_to_site_data' ), 10, 1 );

		// Add configuration data to be passed to JS.
		add_filter( 'customify_localized_js_settings', array( $this, 'add_to_localized_data' ), 10, 1 );

		/**
		 * Reset various Color Palettes options on theme switch to ensure consistency.
		 */
		add_action( 'after_switch_theme', array( $this, 'reset_various_options_on_switch_theme' ), 100 );
	}

	/**
	 * Register Customizer admin scripts
	 */
	public function register_admin_customizer_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script( PixCustomifyPlugin()->get_slug() . '-color-palettes',
			plugins_url( 'js/customizer/color-palettes' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
			array( 'jquery', ), PixCustomifyPlugin()->get_version() );
	}

	/**
	 * Enqueue Customizer admin scripts
	 */
	public function enqueue_admin_customizer_scripts() {
		// If there is no color palettes support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-color-palettes' );
	}

	/**
	 * Get the color palettes configuration.
	 *
	 * @since 1.7.4
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array
	 */
	public function get_palettes( $skip_cache = false ) {
		// Make sure that the Design Assets class is loaded.
		require_once 'lib/class-customify-design-assets.php';

		// Get the design assets data.
		$design_assets = Customify_Design_Assets::instance()->get( $skip_cache );
		if ( false === $design_assets || empty( $design_assets['color_palettes'] ) ) {
			$color_palettes_config = $this->get_default_config();
		} else {
			$color_palettes_config = $design_assets['color_palettes'];
		}

		return apply_filters( 'customify_get_color_palettes', $color_palettes_config );
	}

	/**
	 * Determine if Color Palettes are supported.
	 *
	 * @since 1.7.4
	 *
	 * @return bool
	 */
	public function is_supported() {
		// For now we will only use the fact that Style Manager is supported.
		return apply_filters( 'customify_color_palettes_are_supported', Customify_Style_Manager::instance()->is_supported() );
	}

	/**
	 * Setup the Style Manager Customizer section master colors config.
	 *
	 * This handles the base configuration for the controls in the Style Manager section. We expect other parties (e.g. the theme),
	 * to come and fill up the missing details (e.g. connected fields).
	 *
	 * @since 1.7.4
	 *
	 * @param array $config This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'.
	 *
	 * @return array
	 */
	public function add_style_manager_section_master_colors_config( $config ) {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		if ( ! isset( $config['sections']['style_manager_section'] ) ) {
			$config['sections']['style_manager_section'] = array();
		}

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section'] = Customify_Array::array_merge_recursive_distinct( $config['sections']['style_manager_section'], array(
			'options' => array(
				'sm_color_palette' => array(
					'type'         => 'preset',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type' => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'   => 'sm_color_palette',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'         => true,
					'priority'     => 10,
					'label'        => esc_html__( 'Select a color palette:', 'customify' ),
					'desc'         => esc_html__( 'Conveniently change the design of your site with color palettes. Easy as pie.', 'customify' ),
					'default'      => 'lilac',
					'choices_type' => 'color_palette',
					'choices'      => $this->get_palettes(),
				),
				'sm_palette_filter'  => array(
					'type'         => 'sm_palette_filter',
					'setting_type' => 'option',
					'setting_id'   => 'sm_palette_filter',
					'label'        => esc_html__( 'Filters:', 'customify' ),
					'default'      => 'original',
					'priority'     => 10.1,
					'live'         => true,
					'choices' => array(
						'original'  => esc_html__( 'Original', 'customify' ),
						'clarendon' => esc_html__( 'Clarendon', 'customify' ),
						'vivid'     => esc_html__( 'Vivid', 'customify' ),
						'softer'    => esc_html__( 'Softer', 'customify' ),
						'pastel'    => esc_html__( 'Pastel', 'customify' ),
						'greyish'   => esc_html__( 'Greyish', 'customify' ),
//						 'warm'      => esc_html__( 'Warming', 'customify' ),
//						 'cold'      => esc_html__( 'Cooling', 'customify' ),
//						 'sierra'    => esc_html__( 'Sierra', 'customify' ),
//						 'mayfair'   => esc_html__( 'Mayfair', 'customify' ),
//						 'dumb'      => esc_html__( 'Dumb', 'customify' ),
					),
				),
				'sm_color_primary'           => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_color_primary',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'priority'         => 20,
					'label'            => esc_html__( 'Color Primary', 'customify' ),
					'default'          => '#ffeb00',
					'connected_fields' => array(
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-color-primary',
						),
					),
				),
				'sm_color_primary_final' => array(
					'type'             => 'hidden',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_primary_final',
					'priority'         => 21,
					'label'            => esc_html__( 'Color Primary Final', 'customify' ),
					'live'             => true,
					'default'          => '',
				),
				'sm_color_secondary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_secondary',
					'live'             => true,
					'priority'         => 20.1,
					'label'            => esc_html__( 'Color Secondary', 'customify' ),
					'default'          => '#00ecff',
					'connected_fields' => array(
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-color-secondary',
						),
					),
				),
				'sm_color_secondary_final' => array(
					'type'             => 'hidden',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_secondary_final',
					'priority'         => 21,
					'label'            => esc_html__( 'Color Secondary Final', 'customify' ),
					'live'             => true,
					'default'          => '',
				),
				'sm_color_tertiary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_tertiary',
					'live'             => true,
					'priority'         => 20.2,
					'label'            => esc_html__( 'Color Tertiary', 'customify' ),
					'default'          => '#00ecff',
					'connected_fields' => array(
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-color-tertiary',
						),
					),
				),
				'sm_color_tertiary_final' => array(
					'type'             => 'hidden',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_tertiary_final',
					'priority'         => 21,
					'label'            => esc_html__( 'Color Tertiary Final', 'customify' ),
					'live'             => true,
					'default'          => '',
				),
				'sm_dark_primary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_primary',
					'live'             => true,
					'priority'         => 20.3,
					'label'            => esc_html__( 'Dark Primary', 'customify' ),
					'default'          => '#171617',
					'connected_fields' => array(
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-dark-primary',
						),
					),
				),
				'sm_dark_primary_final' => array(
					'type'             => 'hidden',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_primary_final',
					'priority'         => 21,
					'label'            => esc_html__( 'Dark Primary Final', 'customify' ),
					'live'             => true,
					'default'          => '',
				),
				'sm_dark_secondary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_secondary',
					'live'             => true,
					'priority'         => 20.4,
					'label'            => esc_html__( 'Dark Secondary', 'customify' ),
					'default'          => '#383c50',
					'connected_fields' => array(
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-dark-secondary',
						),
					),
				),
				'sm_dark_secondary_final' => array(
					'type'             => 'hidden',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_secondary_final',
					'priority'         => 21,
					'label'            => esc_html__( 'Dark Secondary Final', 'customify' ),
					'live'             => true,
					'default'          => '',
				),
				'sm_dark_tertiary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_tertiary',
					'live'             => true,
					'priority'         => 20.5,
					'label'            => esc_html__( 'Dark Tertiary', 'customify' ),
					'default'          => '#65726F',
					'connected_fields' => array(
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-dark-tertiary',
						),
					),
				),
				'sm_dark_tertiary_final' => array(
					'type'             => 'hidden',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_tertiary_final',
					'priority'         => 21,
					'label'            => esc_html__( 'Dark Tertiary Final', 'customify' ),
					'live'             => true,
					'default'          => '',
				),
				'sm_light_primary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_primary',
					'live'             => true,
					'priority'         => 20.6,
					'label'            => esc_html__( 'Light Primary', 'customify' ),
					'default'          => '#ffffff',
					'connected_fields' => array(
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-light-primary',
						),
					),
				),
				'sm_light_primary_final' => array(
					'type'             => 'hidden',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_primary_final',
					'priority'         => 21,
					'label'            => esc_html__( 'Light Primary Final', 'customify' ),
					'live'             => true,
					'default'          => '',
				),
				'sm_light_secondary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_secondary',
					'live'             => true,
					'priority'         => 20.7,
					'label'            => esc_html__( 'Light Secondary', 'customify' ),
					'default'          => '#ffffff',
					'connected_fields' => array(
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-light-secondary',
						),
					),
				),
				'sm_light_secondary_final' => array(
					'type'             => 'hidden',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_secondary_final',
					'priority'         => 21,
					'label'            => esc_html__( 'Light Secondary Final', 'customify' ),
					'live'             => true,
					'default'          => '',
				),
				'sm_light_tertiary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_tertiary',
					'live'             => true,
					'priority'         => 20.8,
					'label'            => esc_html__( 'Light Tertiary', 'customify' ),
					'default'          => '#ffffff',
					'connected_fields' => array(
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-light-tertiary',
						),
					),
				),
				'sm_light_tertiary_final' => array(
					'type'             => 'hidden',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_tertiary_final',
					'priority'         => 21,
					'label'            => esc_html__( 'Light Tertiary Final', 'customify' ),
					'live'             => true,
					'default'          => '',
				),
				'sm_swap_colors'                => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_colors',
					'priority'     => 30,
					'label'        => esc_html__( 'Swap Colors', 'customify' ),
					'action'       => 'sm_swap_colors',
				),
				'sm_swap_dark_light'            => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_dark_light',
					'priority'     => 30.1,
					'label'        => esc_html__( 'Swap Dark ⇆ Light', 'customify' ),
					'action'       => 'sm_swap_dark_light',
				),
				'sm_swap_colors_dark'           => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_colors_dark',
					'priority'     => 30.2,
					'label'        => esc_html__( 'Swap Colors ⇆ Dark', 'customify' ),
					'action'       => 'sm_swap_colors_dark',
				),
				'sm_swap_secondary_colors_dark' => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_secondary_colors_dark',
					'priority'     => 30.3,
					'label'        => esc_html__( 'Swap Secondary Color ⇆ Secondary Dark', 'customify' ),
					'action'       => 'sm_swap_secondary_colors_dark',
				),
				'sm_color_palettes_spacing_bottom' => array(
					'type'       => 'html',
					'html'       => '',
					'setting_id' => 'sm_color_palettes_spacing_bottom',
					'priority'   => 31,
				),
			),
		) );

		return $config;
	}

	/**
	 * Add the current color palette control to the Style Manager section.
	 *
	 * @since 1.7.4
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function add_current_palette_control( $config ) {

		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		if ( ! isset( $config['sections']['style_manager_section'] ) ) {
			$config['sections']['style_manager_section'] = array();
		}

		$current_palette = '';

		$master_color_controls_ids = $this->get_all_master_color_controls_ids( $config['sections']['style_manager_section']['options'] );

		$current_palette .= '<div class="sm-color-palette__colors">';
		$color_classes = apply_filters( 'customify_style_manager_color_palettes_colors_classes', array( 'sm-color-palette__color' ) );
		foreach ( $master_color_controls_ids as $setting_id ) {
			$current_palette .=
				'<div class="' . esc_attr( join( ' ', $color_classes ) ) . ' ' . esc_attr( $setting_id ) . '" data-setting="' . esc_attr( $setting_id ) . '">' . "\n" .
				'<div class="sm-color-palette__picker"><div class="sm-color-palette__disc"></div><i></i></div>' . "\n" .
				'</div>' . "\n";
		}
		$current_palette .= '</div><!-- .colors -->' . "\n";

		$current_palette .= '<div class="c-color-palette__fields">';
		$current_palette .= '<div class="c-color-palette__notification  description  hidden  js-altered-notification">' . "\n" .
		                    wp_kses( __( 'One or more colors connected to your color palette have been modified. By changing or altering the current palette you will lose changes made prior to this action.', 'customify' ), array( 'em' => array(), 'b' => array(), 'strong' => array(), 'i' => array() ) ) . "\n" .
		'</div>'  . "\n";
		foreach ( $master_color_controls_ids as $setting_id ) {
			$current_palette .= '<input id="current-palette-' . esc_attr( $setting_id ) . '" class="c-color-palette__input ' . esc_attr( $setting_id ) . '" type="text" value="' . get_option( $setting_id ) . '">';
		}
		$current_palette .= '</div>';

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section']['options'] = array(
              'sm_current_color_palette' => array(
                  'type'       => 'html',
                  'setting_id' => 'sm_current_color_palette',
                  'html'       =>
                      '<div class="c-color-palette">' . "\n" .
                      '<div class="c-color-palette__colors">' . $current_palette . '</div>' . "\n" .
                      '<div class="sm_color_matrix"></div>' . "\n" .
                      '</div>' . "\n" .
                      '<div class="sm-tabs">' . "\n" .
	                      '<div class="sm-tabs__item" data-target="palettes">' . esc_html__( 'Palettes', 'customify' ) . '</div>' . "\n" .
	                      '<div class="sm-tabs__item" data-target="filters">' . esc_html__( 'Filters', 'customify' ) . '</div>' . "\n" .
	                      '<div class="sm-tabs__item" data-target="customize">' . esc_html__( 'Customize', 'customify' ) . '</div>' . "\n" .
                      '</div>',
              ),
              'sm_palettes_description'  => array(
	              'type'       => 'html',
	              'setting_id' => 'sm_palettes_description',
	              'html'       => '<span class="description customize-control-description">' .
                      apply_filters( 'customify_style_manager_sm_palettes_description_html', wp_kses( __( 'Choose your <em>base color palette</em> and go deeper with the <em>Filters</em> and <em>Customize</em> tabs. Make it shine, mate!', 'customify' ), array(
			              'em'     => array(),
			              'b'      => array(),
			              'strong' => array(),
			              'i'      => array(),
		              ) ) ) . '</span>' . "\n",
              ),
              'sm_filters_description'   => array(
	              'type'       => 'html',
	              'setting_id' => 'sm_filters_description',
	              'html'       => '<span class="description customize-control-description">' .
                      apply_filters( 'customify_style_manager_sm_filters_description_html', wp_kses( __( 'Adjust the <i>colors properties</i> by using the filters. Keep the look fresh and engaging!', 'customify' ), array(
			              'em'     => array(),
			              'b'      => array(),
			              'strong' => array(),
			              'i'      => array(),
		              ) ) ) . '</span>' . "\n",
              ),
              'sm_customize_description' => array(
	              'type'       => 'html',
	              'setting_id' => 'sm_customize_description',
	              'html'       => '<span class="description customize-control-description">' .
                      apply_filters( 'customify_style_manager_sm_customize_description_html', wp_kses( __( 'Adjust how the colors are used on your site with ease. Modify their usage level to craft a playful design!', 'customify' ), array(
			              'em'     => array(),
			              'b'      => array(),
			              'strong' => array(),
			              'i'      => array(),
		              ) ) ) . '</span>' . "\n",
              ),
              'sm_coloration_level' => array(
	              'type'         => 'sm_radio',
	              'setting_type' => 'option',
	              'setting_id'   => 'sm_coloration_level',
	              'label'        => esc_html__( 'Coloration Level', 'customify' ),
	              'default'      => $this->get_coloration_level_default_value( $config['sections']['style_manager_section']['options'] ),
	              'live'         => true,
	              'choices'      => $this->get_coloration_level_choices( $config['sections']['style_manager_section']['options'] ),
              ),
              'sm_color_diversity' => array(
	              'type'         => 'sm_radio',
	              'setting_type' => 'option',
	              'setting_id'   => 'sm_color_diversity',
	              'label'        => esc_html__( 'Color Diversity', 'customify' ),
	              'default'      => $this->get_color_diversity_default_value( $config['sections']['style_manager_section']['options'] ),
	              'live'         => true,
	              'choices'      => array(
		              'low'    => esc_html__( 'Low', 'customify' ),
		              'medium' => esc_html__( 'Medium', 'customify' ),
		              'high'   => esc_html__( 'High', 'customify' ),
	              ),
              ),
              'sm_shuffle_colors'  => array(
	              'type'         => 'sm_radio',
	              'setting_type' => 'option',
	              'setting_id'   => 'sm_shuffle_colors',
	              'label'        => esc_html__( 'Shuffle Colors', 'customify' ),
	              'default'      => 'default',
	              'live'         => true,
	              'choices'      => array(
		              'default' => esc_html__( 'Default', 'customify' ),
		              'mixed'   => esc_html__( 'Mixed', 'customify' ),
		              'remix'   => esc_html__( 'Remix', 'customify' ),
	              ),
              ),
              'sm_dark_mode'       => array(
	              'type'         => 'sm_switch',
	              'setting_type' => 'option',
	              'setting_id'   => 'sm_dark_mode',
	              'label'        => esc_html__( 'Dark Mode', 'customify' ),
	              'default'      => 'off',
	              'live'         => true,
	              'choices'      => array(
		              'off' => esc_html__( 'Off', 'customify' ),
		              'on'  => esc_html__( 'On', 'customify' ),
	              ),
              ),
              'sm_dark_color_primary_slider'   => array(
	              'setting_id'  => 'sm_dark_color_primary_slider',
	              'type'        => 'range',
	              'label'       => esc_html__( 'Dark to Color (primary)', 'customify' ),
	              'desc'        => '',
	              'live'        => true,
	              'default'     => $this->get_dark_to_color_slider_default_value( $config['sections']['style_manager_section']['options'], 'sm_dark_primary', 'sm_color_primary' ),
	              'input_attrs' => array(
		              'min'          => 0,
		              'max'          => 100,
		              'step'         => 1,
		              'data-preview' => true,
	              ),
	              'css'         => array(),
              ),
              'sm_dark_color_secondary_slider' => array(
	              'setting_id'  => 'sm_dark_color_secondary_slider',
	              'type'        => 'range',
	              'label'       => esc_html__( 'Dark to Color (secondary)', 'customify' ),
	              'desc'        => '',
	              'live'        => true,
	              'default'     => $this->get_dark_to_color_slider_default_value( $config['sections']['style_manager_section']['options'], 'sm_dark_secondary', 'sm_color_secondary' ),
	              // this should be set by the theme (previously 1300)
	              'input_attrs' => array(
		              'min'          => 0,
		              'max'          => 100,
		              'step'         => 1,
		              'data-preview' => true,
	              ),
	              'css'         => array(),
              ),
              'sm_dark_color_tertiary_slider'  => array(
	              'setting_id'  => 'sm_dark_color_tertiary_slider',
	              'type'        => 'range',
	              'label'       => esc_html__( 'Dark to Color (tertiary)', 'customify' ),
	              'desc'        => '',
	              'live'        => true,
	              'default'     => $this->get_dark_to_color_slider_default_value( $config['sections']['style_manager_section']['options'], 'sm_dark_tertiary', 'sm_color_tertiary' ),
	              // this should be set by the theme (previously 1300)
	              'input_attrs' => array(
		              'min'          => 0,
		              'max'          => 100,
		              'step'         => 1,
		              'data-preview' => true,
	              ),
	              'css'         => array(),
              ),
          ) + $config['sections']['style_manager_section']['options'];

		return $config;
	}

	public function maybe_enhance_dark_mode_control( $config ) {
		$supports_advanced_dark_mode = (bool) current_theme_supports( 'style_manager_advanced_dark_mode' );

		if ( ! $supports_advanced_dark_mode || ! isset( $config['sections']['style_manager_section'] ) ) {
			return $config;
		}

		unset( $config['sections']['style_manager_section']['options']['sm_dark_mode'] );

		$config['sections']['style_manager_section'] = Customify_Array::array_merge_recursive_distinct( $config['sections']['style_manager_section'], array(
			'options' => array(
				'sm_dark_mode_advanced' => array(
					'type'         => 'sm_radio',
					'setting_id'   => 'sm_dark_mode_advanced',
					'setting_type' => 'option',
					'label'        => esc_html__( 'Appearance', 'customify' ),
					'live'         => true,
					'default'      => 'off',
					'desc'         => wp_kses( __( "<strong>Auto</strong> activates dark mode automatically, according to the visitor's system-wide setting", '__plugin_txtd' ), array( 'strong' => array() ) ),
					'choices'      => array(
						'off'  => esc_html__( 'Light', 'customify' ),
						'on'   => esc_html__( 'Dark', 'customify' ),
						'auto' => esc_html__( 'Auto', 'customify' ),
					),
				),
			),
		) );

		return $config;
	}

	private function get_color_diversity_default_value( $options_config ) {
		if ( empty( $options_config ) ) {
			return 'low';
		}
		$optionsArrayObject = new ArrayObject( $options_config );
		$optionsCopy = $optionsArrayObject->getArrayCopy();

		$pos1 = array_search('sm_color_primary_final', $optionsCopy['sm_color_primary']['connected_fields'] );
		if ( false !== $pos1 ) {
			unset( $optionsCopy['sm_color_primary']['connected_fields'][$pos1] );
		}

		$pos2 = array_search('sm_color_secondary_final', $optionsCopy['sm_color_secondary']['connected_fields'] );
		if ( false !== $pos2 ) {
			unset( $optionsCopy['sm_color_secondary']['connected_fields'][$pos2] );
		}

		$pos3 = array_search('sm_color_tertiary_final', $optionsCopy['sm_color_tertiary']['connected_fields'] );
		if ( false !== $pos3 ) {
			unset( $optionsCopy['sm_color_tertiary']['connected_fields'][$pos3] );
		}

		$colors1 = empty( $optionsCopy['sm_color_primary']['connected_fields'] ) ? 0 : 1;
		$colors2 = empty( $optionsCopy['sm_color_secondary']['connected_fields'] ) ? 0 : 1;
		$colors3 = empty( $optionsCopy['sm_color_tertiary']['connected_fields'] ) ? 0 : 1;
		$colors = $colors1 + $colors2 + $colors3;

		if ( $colors > 2 ) {
			return 'high';
		}

		if ( $colors > 1 ) {
			return 'medium';
		}

		return 'low';
	}

	private function get_coloration_level_average( $options_config ) {

		$colors1 = count( $options_config['sm_color_primary']['connected_fields'] );
		$colors2 = count( $options_config['sm_color_secondary']['connected_fields'] );
		$colors3 = count( $options_config['sm_color_tertiary']['connected_fields'] );
		$colors = $colors1 + $colors2 + $colors3;

		$dark1 = count( $options_config['sm_dark_primary']['connected_fields'] );
		$dark2 = count( $options_config['sm_dark_secondary']['connected_fields'] );
		$dark3 = count( $options_config['sm_dark_tertiary']['connected_fields'] );
		$dark = $dark1 + $dark2 + $dark3;

		$total = $colors + $dark;

		// Avoid division by zero.
		if ( 0 === $total ) {
			$total = 1;
		}

		return round( $colors * 100 / $total, 2 );
	}

	private function get_coloration_level_default_value( $options_config ) {
		$label = $this->get_coloration_level_default_label( $options_config );
		return $this->get_coloration_level_point_value( $options_config, $label );
	}

	private function get_coloration_level_choices( $options_config ) {
		return array(
			$this->get_coloration_level_point_value( $options_config, 'low' )      => esc_html__( 'Low', 'customify' ),
			$this->get_coloration_level_point_value( $options_config, 'medium' )   => esc_html__( 'Medium', 'customify' ),
			$this->get_coloration_level_point_value( $options_config, 'high' )     => esc_html__( 'High', 'customify' ),
			$this->get_coloration_level_point_value( $options_config, 'striking' ) => esc_html__( 'Striking', 'customify' ),
		);
	}

	private function get_coloration_level_default_label( $options_config ) {
		if ( empty( $options_config ) ) {
			$average = 0;
		} else {
			$average = $this->get_coloration_level_average( $options_config );
		}

		if ( $average < 25 ) {
			return 'low';
		}

		if ( $average < 50 ) {
			return 'medium';
		}

		if ( $average < 75 ) {
			return 'high';
		}

		return 'striking';
	}

	private function get_coloration_levels( $options_config ) {
		$average = $this->get_coloration_level_average( $options_config );
		$default = $this->get_coloration_level_default_label( $options_config );

		$values = array(
			'low' => $average,
			'medium' => $average,
			'high' => $average,
			'striking' => $average
		);

		if ( 'low' === $default ) {
			$values['medium'] = round( $average + (100 - $average) / 4, 2 );
			$values['high'] = round( $average + (100 - $average) * 2 / 4, 2 );
			$values['striking'] = round( $average + (100 - $average) * 3 / 4, 2 );
		}

		if ( 'medium' === $default ) {
			$values['low'] = round( $average / 2, 2 );
			$values['high'] = round( $average + (100 - $average) / 3, 2 );
			$values['striking'] = round( $average + (100 - $average) * 2 / 3, 2 );
		}

		if ( 'high' === $default ) {
			$values['low'] = round( $average / 3, 2 );
			$values['medium'] = round( $average * 2 / 3, 2 );
			$values['striking'] = round( $average + (100 - $average) / 2, 2 );
		}

		if ( 'striking' === $default ) {
			$values['low'] = round( $average / 4, 2 );
			$values['medium'] = round( $average * 2 / 4, 2 );
			$values['high'] = round( $average * 3 / 4, 2 );
		}

		return $values;
	}

	private function get_coloration_level_point_value( $options_config, $point ) {
		$values = $this->get_coloration_levels( $options_config );
		return $values[$point] . '';
	}

	private function get_dark_to_color_slider_default_value( $options_config, $dark_id, $color_id ) {
		$optionsArrayObject = new ArrayObject( $options_config );
		$optionsCopy = $optionsArrayObject->getArrayCopy();

		$pos1 = array_search($color_id . '_final', $optionsCopy[$color_id]['connected_fields'] );
		if ( false !== $pos1 ) {
			unset( $optionsCopy[$color_id]['connected_fields'][$pos1] );
		}

		$pos2 = array_search($dark_id . '_final', $optionsCopy[$dark_id]['connected_fields'] );
		if ( false !== $pos2 ) {
			unset( $optionsCopy[$dark_id]['connected_fields'][$pos2] );
		}

		$dark_count = count($optionsCopy[$dark_id]['connected_fields']);
		$color_count = count($optionsCopy[$color_id]['connected_fields']);
		$total_count = $dark_count + $color_count;

		if ( $total_count === 0 ) {
			return 0;
		}

		return 100 * $color_count / $total_count;
	}

	public function reset_various_options_on_switch_theme() {
		if ( ! $this->is_supported() ) {
			return;
		}

		// The coloration level and diversity values are calculated dynamically and they are different for each theme (depending on connected fields).
		// That is why we will set it's value to the default value so we don't end up with invalid values (i.e. nothing is selected).
		$config = PixCustomifyPlugin()->get_customizer_config();
		if ( ! empty( $config['sections']['style_manager_section']['options'] ) ) {
			$options_config = $config['sections']['style_manager_section']['options'];
		} elseif ( ! empty( $config['panels']['style_manager_panel']['sections']['sm_color_palettes_section']['options'] ) ) {
			$options_config = $config['panels']['style_manager_panel']['sections']['sm_color_palettes_section']['options'];
		}

		if ( empty( $options_config ) ) {
			return;
		}

		update_option( 'sm_coloration_level', $this->get_coloration_level_default_value( $options_config ) );
		update_option( 'sm_color_diversity', $this->get_color_diversity_default_value( $options_config ) );
		update_option( 'sm_shuffle_colors', 'default' );
	}

	/**
	 * Get the default (hard-coded) color palettes configuration.
	 *
	 * This is only a fallback config in case we can't communicate with the cloud, the first time.
	 *
	 * @since 1.7.4
	 *
	 * @return array
	 */
	protected function get_default_config() {
		$default_config = array(
			'forest_pastel'  => array(
				'label'   => esc_html__( 'Forest Pastel', 'customify' ),
				'preview' => array(
					'background_image_url' => 'https://pxgcdn.com/images/style-manager/color-palettes/lilac-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#a283a7',
					'sm_color_secondary' => '#a283a7',
					'sm_color_tertiary'  => '#a283a7',
					'sm_dark_primary'    => '#9787b3',
					'sm_dark_secondary'  => '#6d5e70',
					'sm_dark_tertiary'   => '#afadaf',
					'sm_light_primary'   => '#f9f8fa',
					'sm_light_secondary' => '#f9f8fa',
					'sm_light_tertiary'  => '#f7f1f8',
				),
			),
			'dawn_lights'  => array(
				'label'   => esc_html__( 'Dawn Lights', 'customify' ),
				'preview' => array(
					'background_image_url' => 'https://pxgcdn.com/images/style-manager/color-palettes/dawn-lights-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#03569a',
					'sm_color_secondary' => '#03569a',
					'sm_color_tertiary'  => '#03569a',
					'sm_dark_primary'    => '#03569a',
					'sm_dark_secondary'  => '#03569a',
					'sm_dark_tertiary'   => '#969b9e',
					'sm_light_primary'   => '#ffffff',
					'sm_light_secondary' => '#ffffff',
					'sm_light_tertiary'  => '#ffffff',
				),
			),
			'royal_shadow'  => array(
				'label'   => esc_html__( 'Royal Shadow', 'customify' ),
				'preview' => array(
					'background_image_url' => 'https://pxgcdn.com/images/style-manager/color-palettes/royal-velvet-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#e6eafe',
					'sm_color_secondary' => '#faeaf0',
					'sm_color_tertiary'  => '#e6eafe',
					'sm_dark_primary'    => '#faeaf0',
					'sm_dark_secondary'  => '#e6eafe',
					'sm_dark_tertiary'   => '#faeaf0',
					'sm_light_primary'   => '#0f0f33',
					'sm_light_secondary' => '#0f0f33',
					'sm_light_tertiary'  => '#0f0f33',
				),
			),
		);

		return apply_filters( 'customify_style_manager_default_color_palettes', $default_config );
	}



	/**
	 * Get the current color palette ID or false if none is selected.
	 *
	 * @since 1.7.4
	 *
	 * @return string|false
	 */
	public function get_current_palette() {
		return get_option( 'sm_color_palette', false );
	}

	/**
	 * Get the current color palette variation ID or false if none is selected.
	 *
	 * @since 1.7.4
	 *
	 * @return string|false
	 */
	public function get_current_palette_variation() {
		return get_option( 'sm_color_palette_variation', false );
	}

	/**
	 * Determine if the selected color palette has been customized and remember this in an option.
	 *
	 * @since 1.7.4
	 *
	 * @return bool
	 */
	public function update_custom_palette_in_use() {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return false;
		}

		$current_palette = $this->get_current_palette();
		if ( empty( $current_palette ) ) {
			return false;
		}

		$color_palettes = $this->get_palettes();
		if ( ! isset( $color_palettes[ $current_palette ] ) || empty( $color_palettes[ $current_palette ]['options'] ) ) {
			return false;
		}

		$is_custom_palette = false;
		// If any of the current master colors has a different value than the one provided by the color palette,
		// it means a custom color palette is in use.
		$current_palette_options = $color_palettes[ $current_palette ]['options'];
		foreach ( $current_palette_options as $setting_id => $value ) {
			if ( $value != get_option( $setting_id ) ) {
				$is_custom_palette = true;
				break;
			}
		}

		update_option( 'sm_is_custom_color_palette', $is_custom_palette, true );

		do_action( 'customify_style_manager_updated_custom_palette_in_use', $is_custom_palette );

		return true;
	}

	/**
	 * Determine if a custom color palette is in use.
	 *
	 * @since 1.7.4
	 *
	 * @return bool
	 */
	public function is_using_custom_palette(){
		return (bool) get_option( 'sm_is_custom_color_palette', false );
	}

	/**
	 * Get all the defined Style Manager master color field ids.
	 *
	 * @param array $options_details Optional.
	 *
	 * @return array
	 *
	 * @since 1.7.4
	 */
	public function get_all_master_color_controls_ids( $options_details = null ) {
		$control_ids = array();

		if ( empty( $options_details ) ) {
			$options_details = PixCustomifyPlugin()->get_options_configs(true);
		}

		if ( empty( $options_details ) ) {
			return $control_ids;
		}

		foreach ( $options_details as $option_id => $option_details ) {
			if ( ! empty( $option_details['type'] ) && 'color' === $option_details['type'] && 0 === strpos( $option_id, 'sm_' ) ) {
				$control_ids[] = $option_id;
			}
		}

		return $control_ids;
	}

	/**
	 * Add color palettes usage data to the site data sent to the cloud.
	 *
	 * @since 1.7.4
	 *
	 * @param array $site_data
	 *
	 * @return array
	 */
	public function add_palettes_to_site_data( $site_data ) {
		if ( empty( $site_data['color_palettes'] ) ) {
			$site_data['color_palettes'] = array();
		}

		// If others have added data before us, we will merge with it.
		$site_data['color_palettes'] = array_merge( $site_data['color_palettes'], array(
			'current' => $this->get_current_palette(),
			'variation' => $this->get_current_palette_variation(),
			'custom' => $this->is_using_custom_palette(),
		) );

		return $site_data;
	}

	/**
	 * Add configuration data to be available to JS.
	 *
	 * @since 2.7.0
	 *
	 * @param array $localized
	 *
	 * @return array
	 */
	public function add_to_localized_data( $localized ) {
		if ( empty( $localized['colorPalettes'] ) ) {
			$localized['colorPalettes'] = array();
		}

		$localized['colorPalettes']['masterSettingIds'] = $this->get_all_master_color_controls_ids();

		$localized['colorPalettes']['variations'] = [
			'color_diversity_low'    => [
				'sm_color_primary'   => [ 'sm_color_primary', 'sm_color_secondary', 'sm_color_tertiary' ],
				'sm_color_secondary' => [],
				'sm_color_tertiary'  => [],
			],
			'color_diversity_medium' => [
				'sm_color_primary'   => [ 'sm_color_primary', 'sm_color_secondary' ],
				'sm_color_secondary' => [ 'sm_color_tertiary' ],
				'sm_color_tertiary'  => [],
			],
			'color_diversity_high'   => [
				'sm_color_primary'   => [ 'sm_color_primary' ],
				'sm_color_secondary' => [ 'sm_color_secondary' ],
				'sm_color_tertiary'  => [ 'sm_color_tertiary' ],
			],
			'shuffle_remix'          => [
				'sm_color_primary'   => [ 'sm_color_secondary' ],
				'sm_color_secondary' => [ 'sm_color_tertiary' ],
				'sm_color_tertiary'  => [ 'sm_color_primary' ],
			],
			'shuffle_mixed'          => [
				'sm_color_primary'   => [ 'sm_color_tertiary' ],
				'sm_color_secondary' => [ 'sm_color_primary' ],
				'sm_color_tertiary'  => [ 'sm_color_secondary' ],
			],
			'light'                  => [
				'sm_color_primary'   => [ 'sm_color_primary' ],
				'sm_color_secondary' => [ 'sm_color_secondary' ],
				'sm_color_tertiary'  => [ 'sm_color_tertiary' ],
				'sm_dark_primary'    => [ 'sm_dark_primary' ],
				'sm_dark_secondary'  => [ 'sm_dark_secondary' ],
				'sm_dark_tertiary'   => [ 'sm_dark_tertiary' ],
				'sm_light_primary'   => [ 'sm_light_primary' ],
				'sm_light_secondary' => [ 'sm_light_secondary' ],
				'sm_light_tertiary'  => [ 'sm_light_tertiary' ],
			],
			'dark'                   => [
				'sm_dark_primary'    => [ 'sm_light_primary' ],
				'sm_dark_secondary'  => [ 'sm_light_secondary' ],
				'sm_dark_tertiary'   => [ 'sm_light_tertiary' ],
				'sm_light_primary'   => [ 'sm_dark_primary' ],
				'sm_light_secondary' => [ 'sm_dark_secondary' ],
				'sm_light_tertiary'  => [ 'sm_dark_tertiary' ],
			],
			'colorful2'              => [
				'sm_color_primary'   => [ 'sm_light_primary' ],
				'sm_color_secondary' => [ 'sm_light_secondary' ],
				'sm_color_tertiary'  => [ 'sm_light_tertiary' ],
				'sm_dark_primary'    => [],
				'sm_dark_secondary'  => [],
				'sm_dark_tertiary'   => [],
				'sm_light_primary'   => [ 'sm_color_primary', 'sm_dark_primary' ],
				'sm_light_secondary' => [ 'sm_color_secondary', 'sm_dark_secondary' ],
				'sm_light_tertiary'  => [ 'sm_color_tertiary', 'sm_dark_tertiary' ],
			],
			'colorful3'              => [
				'sm_color_primary'   => [ 'sm_light_primary' ],
				'sm_color_secondary' => [ 'sm_light_secondary' ],
				'sm_color_tertiary'  => [ 'sm_light_tertiary' ],
				'sm_dark_primary'    => [ 'sm_color_primary', 'sm_color_secondary', 'sm_color_tertiary' ],
				'sm_dark_secondary'  => [],
				'sm_dark_tertiary'   => [],
				'sm_light_primary'   => [ 'sm_dark_primary', 'sm_dark_secondary', 'sm_dark_tertiary' ],
				'sm_light_secondary' => [],
				'sm_light_tertiary'  => [],
			],
			'colorful'               => [
				'sm_color_primary'   => [ 'sm_color_secondary' ],
				'sm_color_secondary' => [ 'sm_color_tertiary' ],
				'sm_color_tertiary'  => [ 'sm_color_primary' ],
				'sm_dark_primary'    => [ 'sm_dark_primary' ],
				'sm_dark_secondary'  => [ 'sm_dark_secondary' ],
				'sm_dark_tertiary'   => [ 'sm_dark_tertiary' ],
				'sm_light_primary'   => [ 'sm_light_primary' ],
				'sm_light_secondary' => [ 'sm_light_secondary' ],
				'sm_light_tertiary'  => [ 'sm_light_tertiary' ],
			],
			'dark_alt'               => [
				'sm_color_primary'   => [ 'sm_light_primary' ],
				'sm_color_secondary' => [ 'sm_light_secondary' ],
				'sm_color_tertiary'  => [ 'sm_light_tertiary' ],
				'sm_dark_primary'    => [ 'sm_color_primary' ],
				'sm_dark_secondary'  => [ 'sm_color_secondary' ],
				'sm_dark_tertiary'   => [ 'sm_color_tertiary' ],
				'sm_light_primary'   => [ 'sm_dark_primary' ],
				'sm_light_secondary' => [ 'sm_dark_secondary' ],
				'sm_light_tertiary'  => [ 'sm_dark_tertiary' ],
			],
			'colorful_alt'           => [
				'sm_color_primary'   => [ 'sm_dark_primary' ],
				'sm_color_secondary' => [ 'sm_dark_secondary' ],
				'sm_color_tertiary'  => [ 'sm_dark_tertiary' ],
				'sm_dark_primary'    => [ 'sm_light_primary' ],
				'sm_dark_secondary'  => [ 'sm_light_secondary' ],
				'sm_dark_tertiary'   => [ 'sm_light_tertiary' ],
				'sm_light_primary'   => [ 'sm_color_primary' ],
				'sm_light_secondary' => [ 'sm_color_secondary' ],
				'sm_light_tertiary'  => [ 'sm_color_tertiary' ],
			],
			'light_alt'              => [
				'sm_color_primary'   => [ 'sm_dark_primary' ],
				'sm_color_secondary' => [ 'sm_dark_secondary' ],
				'sm_color_tertiary'  => [ 'sm_dark_tertiary' ],
				'sm_dark_primary'    => [ 'sm_color_primary' ],
				'sm_dark_secondary'  => [ 'sm_color_secondary' ],
				'sm_dark_tertiary'   => [ 'sm_color_tertiary' ],
				'sm_light_primary'   => [ 'sm_light_primary' ],
				'sm_light_secondary' => [ 'sm_light_secondary' ],
				'sm_light_tertiary'  => [ 'sm_light_tertiary' ],
			],
		];

		return $localized;
	}

	/**
	 * Main Customify_Color_Palettes Instance
	 *
	 * Ensures only one instance of Customify_Color_Palettes is loaded or can be loaded.
	 *
	 * @since  1.7.4
	 * @static
	 *
	 * @return Customify_Color_Palettes Main Customify_Color_Palettes instance
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
	 * @since 1.7.4
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html__( 'You should not do that!', 'customify' ), null );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.7.4
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ),  null );
	}
}

endif;
