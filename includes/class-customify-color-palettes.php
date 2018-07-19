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
	 * Initiate our hooks
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
				'sm_color_palette_variation' => array(
					'type'         => 'radio',
					'setting_type' => 'option',
					'setting_id'   => 'sm_color_palette_variation',
					'label'        => esc_html__( 'Palette Variation', 'customify' ),
					'default'      => 'light',
					'live'         => true,
					'priority'     => 10.5,
					'choices'      => array(
						'light'     => esc_html__( 'light', 'customify' ),
						'light_alt' => esc_html__( 'light_alt', 'customify' ),

						'dark'     => esc_html__( 'dark', 'customify' ),
						'dark_alt' => esc_html__( 'dark_alt', 'customify' ),

						'colorful'     => esc_html__( 'colorful', 'customify' ),
						'colorful_alt' => esc_html__( 'colorful_alt', 'customify' ),
					),
				),
				'sm_color_primary'              => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_color_primary',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'priority'     => 20,
					'label'            => esc_html__( 'Color Primary', 'customify' ),
					'default'          => '#ffeb00',
					'connected_fields' => array(),
				),
				'sm_color_secondary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_secondary',
					'live'             => true,
					'priority'     => 20.1,
					'label'            => esc_html__( 'Color Secondary', 'customify' ),
					'default'          => '#00ecff',
					'connected_fields' => array(),
				),
				'sm_color_tertiary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_tertiary',
					'live'             => true,
					'priority'     => 20.2,
					'label'            => esc_html__( 'Color Tertiary', 'customify' ),
					'default'          => '#00ecff',
					'connected_fields' => array(),
				),
				'sm_dark_primary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_primary',
					'live'             => true,
					'priority'     => 20.3,
					'label'            => esc_html__( 'Dark Primary', 'customify' ),
					'default'          => '#171617',
					'connected_fields' => array(),
				),
				'sm_dark_secondary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_secondary',
					'live'             => true,
					'priority'     => 20.4,
					'label'            => esc_html__( 'Dark Secondary', 'customify' ),
					'default'          => '#383c50',
					'connected_fields' => array(),
				),
				'sm_dark_tertiary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_tertiary',
					'live'             => true,
					'priority'     => 20.5,
					'label'            => esc_html__( 'Dark Tertiary', 'customify' ),
					'default'          => '#65726F',
					'connected_fields' => array(),
				),
				'sm_light_primary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_primary',
					'live'             => true,
					'priority'     => 20.6,
					'label'            => esc_html__( 'Light Primary', 'customify' ),
					'default'          => '#ffffff',
					'connected_fields' => array(),
				),
				'sm_light_secondary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_secondary',
					'live'             => true,
					'priority'     => 20.7,
					'label'            => esc_html__( 'Light Secondary', 'customify' ),
					'default'          => '#ffffff',
					'connected_fields' => array(),
				),
				'sm_light_tertiary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_tertiary',
					'live'             => true,
					'priority'     => 20.8,
					'label'            => esc_html__( 'Light Tertiary', 'customify' ),
					'default'          => '#ffffff',
					'connected_fields' => array(),
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
				'sm_advanced_toggle' => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_toggle_advanced_settings',
					'priority'     => 30.4,
					'label'        => esc_html__( 'Toggle Advanced Settings', 'customify' ),
					'action'       => 'sm_toggle_advanced_settings',
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
		$current_palette_sets = array( 'current', 'next' );

		$master_color_controls_ids = $this->get_all_master_color_controls_ids( $config['sections']['style_manager_section']['options'] );

		foreach ( $current_palette_sets as $set ) {
			$current_palette .= '<div class="colors ' . $set . '">';
			foreach ( $master_color_controls_ids as $setting_id ) {
				$current_palette .=
					'<div class="color ' . $setting_id . '" data-setting="' . $setting_id . '">' . PHP_EOL .
					'<div class="fill"></div>' . PHP_EOL .
					'<div class="picker">' .
					'<div class="disc"></div>'.
					'<i></i>'.
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL;
			}
			$current_palette .= '</div>';
		}

		$current_palette .= '<div class="c-color-palette__fields">';
		$current_palette .= '<div class="c-color-palette__notification  description  hidden  js-altered-notification">' . PHP_EOL .
			__( 'One or more colors connected to your color palette have been modified. By changing or altering the current palette you will lose changes made prior to this action.', '__theme_txtd' ) . PHP_EOL .
		'</div>'  . PHP_EOL;
		foreach ( $master_color_controls_ids as $setting_id ) {
			$current_palette .= '<input id="current-palette-' . $setting_id . '" class="c-color-palette__input ' . $setting_id . '" type="text">';
		}
		$current_palette .= '</div>';

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section']['options'] = array(
              'sm_current_color_palette' => array(
                  'type' => 'html',
                  'setting_id' => 'sm_current_color_palette',
                  'html' =>
                      '<div class="color-palette-container">' . PHP_EOL .
                      '<span class="customize-control-title">Current Color Palette:</span>' . PHP_EOL .
                      '<span class="description customize-control-description">Choose a color palette to start with. Adjust its style using the variation buttons below.</span>' . PHP_EOL .
                      '<div class="c-color-palette">' . PHP_EOL .
                      $current_palette .
                      '<div class="c-color-palette__overlay">' . PHP_EOL .
                      '<div class="c-color-palette__label">' .
                      '<div class="c-color-palette__name">' . 'Original Style' . '</div>' .
                      '<div class="c-color-palette__control variation-light active" data-target="#_customize-input-sm_color_palette_variation_control-radio-light">' .
                      '<span class="dashicons dashicons-image-rotate"></span>' .
                      '<div class="c-color-palette__tooltip">Light</div>' .
                      '</div>' .
                      '<div class="c-color-palette__control variation-dark" data-target="#_customize-input-sm_color_palette_variation_control-radio-dark">' .
                      '<span class="dashicons dashicons-image-filter"></span>'.
                      '<div class="c-color-palette__tooltip">Dark</div>' .
                      '</div>' .
                      '<div class="c-color-palette__control variation-colorful" data-target="#_customize-input-sm_color_palette_variation_control-radio-colorful">' .
                      '<span class="dashicons dashicons-admin-appearance"></span>' .
                      '<div class="c-color-palette__tooltip">Colorful</div>' .
                      '</div>' .
                      '</div>' . PHP_EOL .
                      '</div>' . PHP_EOL .
                      '</div>' . PHP_EOL .
                      '</div>' . PHP_EOL .
                      '<svg class="c-color-palette__blur" width="15em" height="15em" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" version="1.1">' . PHP_EOL .
                      '<defs>' . PHP_EOL .
                      '<filter id="goo">' . PHP_EOL .
                      '<feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />' . PHP_EOL .
                      '<feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 50 -20" result="goo" />' . PHP_EOL .
                      '<feBlend in="SourceGraphic" in2="goo" />' . PHP_EOL .
                      '</filter>' . PHP_EOL .
                      '</defs>' . PHP_EOL .
                      '</svg>',
              ),
              'sm_color_matrix' => array(
                  'type' => 'html',
                  'setting_id' => 'sm_color_matrix',
                  'html' => '<div class="sm_color_matrix"></div>'
              ),
//              'sm_dark_color_master_slider' => array(
//	              'setting_id'  => 'sm_dark_color_master_slider',
//	              'type'        => 'range',
//	              'label'       => esc_html__( 'Dark to Color (master)', 'customify' ),
//	              'desc'        => '',
//	              'live'        => true,
//	              'default'     => 50, // this should be set by the theme (previously 1300)
//	              'input_attrs' => array(
//		              'min'          => 0,
//		              'max'          => 100,
//		              'step'         => 1,
//		              'data-preview' => true,
//	              ),
//	              'css'         => array(),
//              ),
//              'sm_dark_color_primary_slider' => array(
//	              'setting_id'  => 'sm_dark_color_primary_slider',
//                  'type'        => 'range',
//                  'label'       => esc_html__( 'Dark to Color (primary)', 'customify' ),
//                  'desc'        => '',
//                  'live'        => true,
//                  'default'     => $this->get_dark_to_color_slider_default_value( $config['sections']['style_manager_section']['options'], 'sm_dark_primary', 'sm_color_primary' ),
//                  'input_attrs' => array(
//                      'min'          => 0,
//                      'max'          => 100,
//                      'step'         => 1,
//                      'data-preview' => true,
//                  ),
//                  'css'         => array(),
//              ),
//              'sm_dark_color_secondary_slider' => array(
//	              'setting_id'  => 'sm_dark_color_secondary_slider',
//                  'type'        => 'range',
//                  'label'       => esc_html__( 'Dark to Color (secondary)', 'customify' ),
//                  'desc'        => '',
//                  'live'        => true,
//	              'default'     => $this->get_dark_to_color_slider_default_value( $config['sections']['style_manager_section']['options'], 'sm_dark_secondary', 'sm_color_secondary' ), // this should be set by the theme (previously 1300)
//                  'input_attrs' => array(
//                      'min'          => 0,
//                      'max'          => 100,
//                      'step'         => 1,
//                      'data-preview' => true,
//                  ),
//                  'css'         => array(),
//              ),
//              'sm_dark_color_tertiary_slider' => array(
//	              'setting_id'  => 'sm_dark_color_tertiary_slider',
//                  'type'        => 'range',
//                  'label'       => esc_html__( 'Dark to Color (tertiary)', 'customify' ),
//                  'desc'        => '',
//                  'live'        => true,
//	              'default'     => $this->get_dark_to_color_slider_default_value( $config['sections']['style_manager_section']['options'], 'sm_dark_tertiary', 'sm_color_tertiary' ), // this should be set by the theme (previously 1300)
//                  'input_attrs' => array(
//                      'min'          => 0,
//                      'max'          => 100,
//                      'step'         => 1,
//                      'data-preview' => true,
//                  ),
//                  'css'         => array(),
//              ),
//              'sm_colors_dispersion' => array(
//	              'setting_id'  => 'sm_colors_dispersion',
//                  'type'        => 'range',
//                  'label'       => esc_html__( 'Colors dispersion range', 'customify' ),
//                  'desc'        => '',
//                  'live'        => true,
//                  'default'     => $this->get_color_dispersion_slider_default_value( $config['sections']['style_manager_section']['options'] ),
//                  'input_attrs' => array(
//                      'min'          => 1,
//                      'max'          => 100,
//                      'step'         => 1,
//                      'data-preview' => true,
//                  ),
//                  'css'         => array(),
//              ),
//              'sm_colors_focus_point' => array(
//	              'setting_id'  => 'sm_colors_focus_point',
//                  'type'        => 'range',
//                  'label'       => esc_html__( 'Colors focus point', 'customify' ),
//                  'desc'        => '',
//                  'live'        => true,
//                  'default'     => $this->get_color_focus_slider_default_value( $config['sections']['style_manager_section']['options'] ),
//                  'input_attrs' => array(
//                      'min'          => 0,
//                      'max'          => 100,
//                      'step'         => 1,
//                      'data-preview' => true,
//                  ),
//                  'css'         => array(),
//              ),
          ) + $config['sections']['style_manager_section']['options'];

		return $config;
	}

	private function get_dark_to_color_slider_default_value( $options, $dark_id, $color_id ) {
		$dark_count = count($options[$dark_id]['connected_fields']);
		$color_count = count($options[$color_id]['connected_fields']);
		$total_count = $dark_count + $color_count;

		if ( $total_count === 0 ) {
			return 0;
		}

		return 100 * $color_count / $total_count;
	}


	private function get_color_dispersion_slider_default_value( $options ) {
		$primary_count = count($options['sm_color_primary']['connected_fields']);
		$secondary_count = count($options['sm_color_secondary']['connected_fields']);
		$tertiary_count = count($options['sm_color_tertiary']['connected_fields']);
		$total_count = $primary_count + $secondary_count + $tertiary_count;
		$n = 3;

		$average = ( $primary_count + $secondary_count + $tertiary_count ) / $n;

		$diff_primary = pow( $primary_count - $average, 2 );
		$diff_secondary = pow( $secondary_count - $average, 2 );
		$diff_tertiary = pow( $tertiary_count - $average, 2 );

		$diff_average = ( $diff_primary + $diff_secondary + $diff_tertiary ) / $n; // presupun ca e intre 0 si total * 2 / 3

		$diff1 = pow( $total_count - $average, 2);
		$diff2 = pow( $average, 2);
		$diff3 = $diff2;

		$min = 0; // dispersion = 1
		$max = ($diff1 + $diff2 + $diff3) / 3;
		// $max = 2 * ($n - 1) * $average / $n; // dispersion = 0;

		// avoid division by zero
		if ( $max === 0 ) {
			return 100;
		}

		return 100 * ($diff_average / max($primary_count, $secondary_count, $tertiary_count));
	}

	private function get_color_focus_slider_default_value( $options ) {
		$primary_count = count($options['sm_color_primary']['connected_fields']);
		$secondary_count = count($options['sm_color_secondary']['connected_fields']);
		$tertiary_count = count($options['sm_color_tertiary']['connected_fields']);
		$total_count = $primary_count + $secondary_count + $tertiary_count;

		// avoid division by zero
		if ( $total_count === 0 ) {
			return 50;
		}

		$focus_point = (0 * $primary_count + 0.5 * $secondary_count + 1 * $tertiary_count ) / $total_count;

		return $focus_point * 100;
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
	protected function get_current_palette() {
		return get_option( 'sm_color_palette', false );
	}

	/**
	 * Get the current color palette variation ID or false if none is selected.
	 *
	 * @since 1.7.4
	 *
	 * @return string|false
	 */
	protected function get_current_palette_variation() {
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
	protected function is_using_custom_palette(){
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
	 * @return Customify_Font_Palettes Main Customify_Color_Palettes instance
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
