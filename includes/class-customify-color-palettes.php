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
	}

	/**
	 * Register Customizer admin scripts
	 */
	public function register_admin_customizer_scripts() {
		wp_register_script( PixCustomifyPlugin()->get_slug() . '-swap-values', plugins_url( 'js/customizer/swap-values.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version() );
		wp_register_script( PixCustomifyPlugin()->get_slug() . '-color-palettes-variations', plugins_url( 'js/customizer/color-palettes-variations.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version() );
		wp_register_script( PixCustomifyPlugin()->get_slug() . '-color-palettes', plugins_url( 'js/customizer/color-palettes.js', PixCustomifyPlugin()->get_file() ), array( 'jquery', PixCustomifyPlugin()->get_slug() . '-color-palettes-variations', PixCustomifyPlugin()->get_slug() . '-swap-values' ), PixCustomifyPlugin()->get_version() );
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
		$config['sections']['style_manager_section'] = array_replace_recursive( $config['sections']['style_manager_section'], array(
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
//						'sm_color_primary_connected'
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-color-primary',
						),
					),
				),
				'sm_color_primary_connected' => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_primary_connected',
					'priority'         => 21,
					'label'            => esc_html__( 'Color Primary Connected', 'customify' ),
					'live'             => true,
					'default'          => '#ffeb00',
					'css' => array(
						array(
							'selector' => ':root',
							'property' => '--sm-color-primary-connected',
						),
					),
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
//						'sm_color_secondary_connected'
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-color-secondary',
						),
					),
				),
				'sm_color_secondary_connected' => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_secondary_connected',
					'priority'         => 21,
					'label'            => esc_html__( 'Color Secondary Connected', 'customify' ),
					'live'             => true,
					'default'          => '#00ecff',
					'css' => array(
						array(
							'selector' => ':root',
							'property' => '--sm-color-secondary-connected',
						),
					),
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
//						'sm_color_tertiary_connected'
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-color-tertiary',
						),
					),
				),
				'sm_color_tertiary_connected' => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_tertiary_connected',
					'priority'         => 21,
					'label'            => esc_html__( 'Color Tertiary Connected', 'customify' ),
					'live'             => true,
					'default'          => '#00ecff',
					'css' => array(
						array(
							'selector' => ':root',
							'property' => '--sm-color-tertiary-connected',
						),
					),
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
//						'sm_dark_primary_connected'
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-dark-primary',
						),
					),
				),
				'sm_dark_primary_connected' => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_primary_connected',
					'priority'         => 21,
					'label'            => esc_html__( 'Dark Primary Connected', 'customify' ),
					'live'             => true,
					'default'          => '#171617',
					'css' => array(
						array(
							'selector' => ':root',
							'property' => '--sm-dark-primary-connected',
						),
					),
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
//						'sm_dark_secondary_connected'
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-dark-secondary',
						),
					),
				),
				'sm_dark_secondary_connected' => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_secondary_connected',
					'priority'         => 21,
					'label'            => esc_html__( 'Color Primary Connected', 'customify' ),
					'live'             => true,
					'default'          => '#ffeb00',
					'css' => array(
						array(
							'selector' => ':root',
							'property' => '--sm-dark-secondary-connected',
						),
					),
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
//						'sm_dark_tertiary_connected'
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-dark-tertiary',
						),
					),
				),
				'sm_dark_tertiary_connected' => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_tertiary_connected',
					'priority'         => 21,
					'label'            => esc_html__( 'Dark Tertiary Connected', 'customify' ),
					'live'             => true,
					'default'          => '#ffeb00',
					'css' => array(
						array(
							'selector' => ':root',
							'property' => '--sm-dark-tertiary-connected',
						),
					),
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
//						'sm_light_primary_connected'
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-light-primary',
						),
					),
				),
				'sm_light_primary_connected' => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_primary_connected',
					'priority'         => 21,
					'label'            => esc_html__( 'Light Primary Connected', 'customify' ),
					'live'             => true,
					'default'          => '#ffffff',
					'css' => array(
						array(
							'selector' => ':root',
							'property' => '--sm-light-primary-connected',
						),
					),
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
//						'sm_light_secondary_connected'
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-light-secondary',
						),
					),
				),
				'sm_light_secondary_connected' => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_secondary_connected',
					'priority'         => 21,
					'label'            => esc_html__( 'Light Secondary Connected', 'customify' ),
					'live'             => true,
					'default'          => '#ffffff',
					'css' => array(
						array(
							'selector' => ':root',
							'property' => '--sm-light-secondary-connected',
						),
					),
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
//						'sm_light_tertiary_connected'
					),
					'css'              => array(
						array(
							'selector' => ':root',
							'property' => '--sm-light-tertiary',
						),
					),
				),
				'sm_light_tertiary_connected' => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_tertiary_connected',
					'priority'         => 21,
					'label'            => esc_html__( 'Light Tertiary Connected', 'customify' ),
					'live'             => true,
					'default'          => '#ffffff',
					'css' => array(
						array(
							'selector' => ':root',
							'property' => '--sm-light-tertiary-connected',
						),
					),
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
				'sm_spacing_bottom' => array(
					'type'       => 'html',
					'html'       => '',
					'setting_id' => 'sm_spacing_bottom',
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

		$current_palette .= '<div class="colors">';
		foreach ( $master_color_controls_ids as $setting_id ) {
			$current_palette .=
				'<div class="color ' . $setting_id . '" data-setting="' . $setting_id . '">' . PHP_EOL .
				'<div class="picker">' .
				'<div class="disc"></div>'.
				'<i></i>'.
				'</div>' . PHP_EOL .
				'</div>' . PHP_EOL;
		}
		$current_palette .= '</div>';

		$current_palette .= '<div class="c-color-palette__fields">';
		$current_palette .= '<div class="c-color-palette__notification  description  hidden  js-altered-notification">' . PHP_EOL .
		                    wp_kses( __( 'One or more colors connected to your color palette have been modified. By changing or altering the current palette you will lose changes made prior to this action.', 'customify' ), array( 'em' => array(), 'b' => array(), 'strong' => array(), 'i' => array() ) ) . PHP_EOL .
		'</div>'  . PHP_EOL;
		foreach ( $master_color_controls_ids as $setting_id ) {
			$current_palette .= '<input id="current-palette-' . $setting_id . '" class="c-color-palette__input ' . $setting_id . '" type="text" value="' . get_option( $setting_id ) . '">';
		}
		$current_palette .= '</div>';

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section']['options'] = array(
              'sm_current_color_palette' => array(
                  'type'       => 'html',
                  'setting_id' => 'sm_current_color_palette',
                  'html'       =>
                      '<div class="c-color-palette">' . PHP_EOL .
                      '<div class="c-color-palette__colors">' . $current_palette . '</div>' . PHP_EOL .
                      '<div class="sm_color_matrix"></div>' . PHP_EOL .
                      '</div>' . PHP_EOL .
                      '<div class="sm-tabs">' . PHP_EOL .
	                      '<div class="sm-tabs__item" data-target="palettes">' . esc_html__( 'Palettes', 'customify' ) . '</div>' . PHP_EOL .
	                      '<div class="sm-tabs__item" data-target="filters">' . esc_html__( 'Filters', 'customify' ) . '</div>' . PHP_EOL .
	                      '<div class="sm-tabs__item" data-target="customize">' . esc_html__( 'Customize', 'customify' ) . '</div>' . PHP_EOL .
                      '</div>',
              ),
              'sm_palettes_description'  => array(
	              'type'       => 'html',
	              'setting_id' => 'sm_palettes_description',
	              'html'       => '<span class="description customize-control-description">' . wp_kses( __( 'Choose your <em>base color palette</em> and go deeper with the <em>Filters</em> and <em>Customize</em> tabs. Make it shine, mate!', 'customify' ), array(
			              'em'     => array(),
			              'b'      => array(),
			              'strong' => array(),
			              'i'      => array(),
		              ) ) . '</span>' . PHP_EOL,
              ),
              'sm_filters_description'   => array(
	              'type'       => 'html',
	              'setting_id' => 'sm_filters_description',
	              'html'       => '<span class="description customize-control-description">' . wp_kses( __( 'Adjust the <i>colors properties</i> by using the filters. Keep the look fresh and engaging!', 'customify' ), array(
			              'em'     => array(),
			              'b'      => array(),
			              'strong' => array(),
			              'i'      => array(),
		              ) ) . '</span>' . PHP_EOL,
              ),
              'sm_customize_description' => array(
	              'type'       => 'html',
	              'setting_id' => 'sm_customize_description',
	              'html'       => '<span class="description customize-control-description">' . wp_kses( __( 'Adjust how the colors are used on your site with ease. Modify their usage level to craft a playful design!', 'customify' ), array( 'em'     => array(),
	                                                                                                                                                                                                                                            'b'      => array(),
	                                                                                                                                                                                                                                            'strong' => array(),
	                                                                                                                                                                                                                                            'i'      => array(),
		              ) ) . '</span>' . PHP_EOL,
              ),
              'sm_coloration_level' => array(
	              'type'         => 'sm_radio',
	              'setting_type' => 'option',
	              'setting_id'   => 'sm_coloration_level',
	              'label'        => esc_html__( 'Coloration Level', 'customify' ),
	              'default'      => $this->get_coloration_level_default_value( $config ),
	              'live'         => true,
	              'choices'      => $this->get_coloration_level_choices( $config ),
              ),
              'sm_color_diversity' => array(
	              'type'         => 'sm_radio',
	              'setting_type' => 'option',
	              'setting_id'   => 'sm_color_diversity',
	              'label'        => esc_html__( 'Color Diversity', 'customify' ),
	              'default'      => $this->get_color_diversity_default_value( $config ),
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

	private function get_color_diversity_default_value( $config ) {
		$optionsArrayObject = new ArrayObject( $config['sections']['style_manager_section']['options'] );
		$optionsCopy = $optionsArrayObject->getArrayCopy();

		$pos1 = array_search('sm_color_primary_connected', $optionsCopy['sm_color_primary']['connected_fields'] );
		if ( false !== $pos1 ) {
			unset( $optionsCopy['sm_color_primary']['connected_fields'][$pos1] );
		}

		$pos2 = array_search('sm_color_secondary_connected', $optionsCopy['sm_color_secondary']['connected_fields'] );
		if ( false !== $pos2 ) {
			unset( $optionsCopy['sm_color_secondary']['connected_fields'][$pos2] );
		}

		$pos3 = array_search('sm_color_tertiary_connected', $optionsCopy['sm_color_tertiary']['connected_fields'] );
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

	private function get_coloration_level_average( $config ) {
		$options = $config['sections']['style_manager_section']['options'];

		$colors1 = count( $options['sm_color_primary']['connected_fields'] );
		$colors2 = count( $options['sm_color_secondary']['connected_fields'] );
		$colors3 = count( $options['sm_color_tertiary']['connected_fields'] );
		$colors = $colors1 + $colors2 + $colors3;

		$dark1 = count( $options['sm_dark_primary']['connected_fields'] );
		$dark2 = count( $options['sm_dark_secondary']['connected_fields'] );
		$dark3 = count( $options['sm_dark_tertiary']['connected_fields'] );
		$dark = $dark1 + $dark2 + $dark3;

		$total = $colors + $dark;

		// Avoid division by zero.
		if ( 0 === $total ) {
			$total = 1;
		}

		return round( $colors * 100 / $total, 2 );
	}

	private function get_coloration_level_default_value( $config ) {
		$label = $this->get_coloration_level_default_label( $config );
		return $this->get_coloration_level_point_value( $config, $label );
	}

	private function get_coloration_level_choices( $config ) {
		return array(
			$this->get_coloration_level_point_value( $config, 'low' )      => esc_html__( 'Low', 'customify' ),
			$this->get_coloration_level_point_value( $config, 'medium' )   => esc_html__( 'Medium', 'customify' ),
			$this->get_coloration_level_point_value( $config, 'high' )     => esc_html__( 'High', 'customify' ),
			$this->get_coloration_level_point_value( $config, 'striking' ) => esc_html__( 'Striking', 'customify' ),
		);
	}

	private function get_coloration_level_default_label( $config ) {
		$average = $this->get_coloration_level_average( $config );

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

	private function get_coloration_levels( $config ) {
		$average = $this->get_coloration_level_average( $config );
		$default = $this->get_coloration_level_default_label( $config );

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

	private function get_coloration_level_point_value( $config, $point ) {
		$values = $this->get_coloration_levels( $config );
		return $values[$point] . '';
	}

	private function get_dark_to_color_slider_default_value( $options, $dark_id, $color_id ) {
		$optionsArrayObject = new ArrayObject( $options );
		$optionsCopy = $optionsArrayObject->getArrayCopy();

		$pos1 = array_search($color_id . '_connected', $optionsCopy[$color_id]['connected_fields'] );
		if ( false !== $pos1 ) {
			unset( $optionsCopy[$color_id]['connected_fields'][$pos1] );
		}

		$pos2 = array_search($dark_id . '_connected', $optionsCopy[$dark_id]['connected_fields'] );
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
			'vasco'  => array(
				'label'   => esc_html__( 'Restful Beach', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/vasco-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#38C3C8',
					'sm_color_secondary' => '#F59828',
					'sm_color_tertiary'  => '#FB551C',
					'sm_dark_primary'    => '#2b2b28',
					'sm_dark_secondary'  => '#2B3D39',
					'sm_dark_tertiary'   => '#65726F',
					'sm_light_primary'   => '#F5F6F1',
					'sm_light_secondary' => '#E6F7F7',
					'sm_light_tertiary'  => '#FAEDE8',
				),
			),
			'felt'  => array(
				'label'   => esc_html__( 'Warm Summer', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/felt-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#ff6000',
					'sm_color_secondary' => '#FF9200',
					'sm_color_tertiary'  => '#FF7019',
					'sm_dark_primary'    => '#1C1C1C',
					'sm_dark_secondary'  => '#161616',
					'sm_dark_tertiary'   => '#161616',
					'sm_light_primary'   => '#FFFCFC',
					'sm_light_secondary' => '#FFF4E8',
					'sm_light_tertiary'  => '#F7F3F0',
				),
			),
			'julia'  => array(
				'label'   => esc_html__( 'Serenity', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/julia-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#3349B8',
					'sm_color_secondary' => '#3393B8',
					'sm_color_tertiary'  => '#C18866',
					'sm_dark_primary'    => '#161616',
					'sm_dark_secondary'  => '#383C50',
					'sm_dark_tertiary'   => '#383C50',
					'sm_light_primary'   => '#f7f6f5',
					'sm_light_secondary' => '#E7F2F8',
					'sm_light_tertiary'  => '#F7ECE6',
				),
			),
			'gema'  => array(
				'label'   => esc_html__( 'Burning Red', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/gema-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#E03A3A',
					'sm_color_secondary' => '#F75034',
					'sm_color_tertiary'  => '#AD2D2D',
					'sm_dark_primary'    => '#000000',
					'sm_dark_secondary'  => '#000000',
					'sm_dark_tertiary'   => '#A3A3A1',
					'sm_light_primary'   => '#FFFFFF',
					'sm_light_secondary' => '#F7F5F5',
					'sm_light_tertiary'  => '#F7F2F2',
				),
			),
			'patch'  => array(
				'label'   => esc_html__( 'Fresh Lemon', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/patch-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#ffeb00',
					'sm_color_secondary' => '#19CDFF',
					'sm_color_tertiary'  => '#0BE8DD',
					'sm_dark_primary'    => '#171617',
					'sm_dark_secondary'  => '#3d3e40',
					'sm_dark_tertiary'   => '#b5b5b5',
					'sm_light_primary'   => '#FFFFFF',
					'sm_light_secondary' => '#E8FAFF',
					'sm_light_tertiary'  => '#F2FFFE',
				),
			),
			'silk'  => array(
				'label'   => esc_html__( 'Floral Bloom', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/silk-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#A33B61',
					'sm_color_secondary' => '#FCC9B0',
					'sm_color_tertiary'  => '#C9648A',
					'sm_dark_primary'    => '#000000',
					'sm_dark_secondary'  => '#000000',
					'sm_dark_tertiary'   => '#A3A3A1',
					'sm_light_primary'   => '#FFFFFF',
					'sm_light_secondary' => '#F7F5F6',
					'sm_light_tertiary'  => '#F7F0F3',
				),
			),
			'hive'  => array(
				'label'   => esc_html__( 'Powerful', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/hive-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#ffeb00',
					'sm_color_secondary' => '#3200B2',
					'sm_color_tertiary'  => '#740AC9',
					'sm_dark_primary'    => '#171617',
					'sm_dark_secondary'  => '#171617',
					'sm_dark_tertiary'   => '#363636',
					'sm_light_primary'   => '#FFFFFF',
					'sm_light_secondary' => '#F2F5F7',
					'sm_light_tertiary'  => '#F5F2F7',
				),
			),
			'lilac'  => array(
				'label'   => esc_html__( 'Soft Lilac', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/lilac-color-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#DD8CA9',
					'sm_color_secondary' => '#8C9CDE',
					'sm_color_tertiary'  => '#E3B4A6',
					'sm_dark_primary'    => '#1A1A1A',
					'sm_dark_secondary'  => '#303030',
					'sm_dark_tertiary'   => '#A3A3A1',
					'sm_light_primary'   => '#F0F2F1',
					'sm_light_secondary' => '#CED5F2',
					'sm_light_tertiary'  => '#F7E1DA',
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
	 * @since 1.7.4
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function get_all_master_color_controls_ids( $options ) {
		$master_color_controls = array();

		if ( empty( $options ) ) {
			return $master_color_controls;
		}

		foreach ( $options as $option_id => $option_settings ) {
			if ( ! empty( $option_settings['type'] ) && 'color' === $option_settings['type'] ) {
				$master_color_controls[] = $option_id;
			}
		}

		return $master_color_controls;
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

		_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?' ) ), null );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.7.4
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ),  null );
	} // End __wakeup ()
}

endif;
