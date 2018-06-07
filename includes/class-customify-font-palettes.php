<?php
/**
 * This is the class that handles the logic for Font Palettes.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       1.7.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Font_Palettes' ) ) :

class Customify_Font_Palettes {

	/**
	 * Holds the only instance of this class.
	 * @var     null|Customify_Font_Palettes
	 * @access  protected
	 * @since   1.7.5
	 */
	protected static $_instance = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.5
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * Initialize this module.
	 *
	 * @since 1.7.5
	 */
	public function init() {
		// Hook up.
		$this->add_hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since 1.7.5
	 */
	public function add_hooks() {
		/*
		 * Handle the Customizer Style Manager section config.
		 */
		add_filter( 'customify_filter_fields', array( $this, 'add_style_manager_section_master_fonts_config' ), 12, 1 );
		// This needs to come after the external theme config has been applied
//		add_filter( 'customify_filter_fields', array( $this, 'add_current_palette_control' ), 110, 1 );

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
		 * Add font palettes usage to site data.
		 */
		add_filter( 'customify_style_manager_get_site_data', array( $this, 'add_palettes_to_site_data' ), 10, 1 );
	}

	/**
	 * Register Customizer admin scripts
	 */
	public function register_admin_customizer_scripts() {
		wp_register_script( PixCustomifyPlugin()->get_slug() . '-font-swap-values', plugins_url( 'js/customizer/font-swap-values.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version() );
		wp_register_script( PixCustomifyPlugin()->get_slug() . '-font-palettes-variations', plugins_url( 'js/customizer/font-palettes-variations.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version() );
		wp_register_script( PixCustomifyPlugin()->get_slug() . '-font-palettes', plugins_url( 'js/customizer/font-palettes.js', PixCustomifyPlugin()->get_file() ), array( 'jquery', PixCustomifyPlugin()->get_slug() . '-font-palettes-variations', PixCustomifyPlugin()->get_slug() . '-swap-values' ), PixCustomifyPlugin()->get_version() );
	}

	/**
	 * Enqueue Customizer admin scripts
	 */
	public function enqueue_admin_customizer_scripts() {
		// If there is no font palettes support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-font-palettes' );
	}

	/**
	 * Get the font palettes configuration.
	 *
	 * @since 1.7.5
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
		if ( false === $design_assets || empty( $design_assets['font_palettes'] ) ) {
			$config = $this->get_default_config();
		} else {
			$config = $design_assets['font_palettes'];
		}

		return apply_filters( 'customify_get_font_palettes', $config );
	}

	/**
	 * Determine if Font Palettes are supported.
	 *
	 * @since 1.7.5
	 *
	 * @return bool
	 */
	public function is_supported() {
		// For now we will only use the fact that Style Manager is supported.
		return apply_filters( 'customify_font_palettes_are_supported', Customify_Style_Manager::instance()->is_supported() );
	}

	/**
	 * Setup the Style Manager Customizer section master fonts config.
	 *
	 * This handles the base configuration for the controls in the Style Manager section. We expect other parties (e.g. the theme),
	 * to come and fill up the missing details (e.g. connected fields).
	 *
	 * @since 1.7.5
	 *
	 * @param array $config This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'.
	 *
	 * @return array
	 */
	public function add_style_manager_section_master_fonts_config( $config ) {
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
				'sm_font_palette' => array(
					'type'         => 'preset',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type' => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'   => 'sm_font_palette',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'         => true,
					'priority'     => 5,
					'label'        => esc_html__( 'Select a font palette:', 'customify' ),
					'desc'         => esc_html__( 'Conveniently change the design of your site with font palettes. Easy as pie.', 'customify' ),
					'default'      => 'julia',
					'choices_type' => 'font_palette',
					'choices'      => $this->get_palettes(),
				),
				'sm_font_palette_variation' => array(
					'type'         => 'radio',
					'setting_type' => 'option',
					'setting_id'   => 'sm_font_palette_variation',
					'label'        => esc_html__( 'Palette Variation', 'customify' ),
					'default'      => 'regular',
					'live'         => true,
					'priority'     => 5.5,
					'choices'      => array(
						'light'     => esc_html__( 'light', 'customify' ),
						'regular'     => esc_html__( 'regular', 'customify' ),
						'big' => esc_html__( 'big', 'customify' ),
					),
				),
				'sm_font_primary'              => array(
					'type'             => 'font',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_font_primary',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'priority'         => 7,
					'label'            => esc_html__( 'Font Primary', 'customify' ),
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '400',
						'font-size'      => 20,
						'line-height'    => 1.25,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),
					// Sub Fields Configuration
					'fields'   => array(
						// This is the configuration for the range slider.
						'font-size'       => array(
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						// These subfields are disabled because they are calculated through the font palette logic.
						'font-weight'     => false,
						'line-height'     => false,
						'letter-spacing'  => false,
						'text-align'      => false,
						'text-transform'  => false,
						'text-decoration' => false,
					),
					'connected_fields' => array(),
				),
				'sm_font_secondary'              => array(
					'type'             => 'font',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_font_secondary',
					'live'             => true,
					'priority'         => 7.1,
					'label'            => esc_html__( 'Font Secondary', 'customify' ),
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '300',
						'font-size'      => 10,
						'line-height'    => 1.625,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),
					// Sub Fields Configuration
					'fields'   => array(
						// This is the configuration for the range slider.
						'font-size'       => array(
							'min'  => 8,
							'max'  => 90,
							'step' => 1,
							'unit' => 'px',
						),
						// These subfields are disabled because they are calculated through the font palette logic.
						'font-weight'     => false,
						'line-height'     => false,
						'letter-spacing'  => false,
						'text-align'      => false,
						'text-transform'  => false,
						'text-decoration' => false,
					),
					'connected_fields' => array(),
				),
				'sm_font_body'              => array(
					'type'             => 'font',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_font_body',
					'live'             => true,
					'priority'         => 7.2,
					'label'            => esc_html__( 'Font Body', 'customify' ),
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '300',
						'font-size'      => 14,
						'line-height'    => 1.6,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),
					// Sub Fields Configuration
					'fields'   => array(
						// This is the configuration for the range slider.
						'font-size'       => array(
							'min'  => 8,
							'max'  => 100,
							'step' => 1,
							'unit' => 'px',
						),
						// These subfields are disabled because they are calculated through the font palette logic.
						'font-weight'     => false,
						'line-height'     => false,
						'letter-spacing'  => false,
						'text-align'      => false,
						'text-transform'  => false,
						'text-decoration' => false,
					),
					'connected_fields' => array(),
				),
				'sm_swap_fonts'                => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_fonts',
					'priority'     => 9,
					'label'        => esc_html__( 'Swap Fonts', 'customify' ),
					'action'       => 'sm_swap_fonts',
				),
				'sm_swap_primary_secondary'            => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_primary_secondary',
					'priority'     => 9.1,
					'label'        => esc_html__( 'Swap Primary ⇆ Secondary', 'customify' ),
					'action'       => 'sm_swap_dark_light',
				),
			),
		) );

		return $config;
	}

	/**
	 * Add the current font palette control to the Style Manager section.
	 *
	 * @since 1.7.5
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

		$master_font_controls_ids = $this->get_all_master_font_controls_ids( $config['sections']['style_manager_section']['options'] );

		foreach ( $current_palette_sets as $set ) {
			$current_palette .= '<div class="fonts ' . $set . '">';
			foreach ( $master_font_controls_ids as $setting_id ) {
				if ( ! empty( $config['sections']['style_manager_section']['options'][ $setting_id ]['connected_fields'] ) ) {
					$current_palette .=
						'<div class="font ' . $setting_id . '" data-setting="' . $setting_id . '">' . PHP_EOL .
						'<div class="fill"></div>' . PHP_EOL .
						'<div class="picker"><i></i></div>' . PHP_EOL .
						'</div>' . PHP_EOL;
				}
			}
			$current_palette .= '</div>';
		}

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section']['options'] = array(
              'sm_current_font_palette' => array(
                  'type' => 'html',
                  'html' =>
                      '<div class="font-palette-container">' . PHP_EOL .
                      '<span class="customize-control-title">Current Font Palette:</span>' . PHP_EOL .
                      '<span class="description customize-control-description">Choose a font palette to start with. Adjust its style using the variation buttons below.</span>' . PHP_EOL .
                      '<div class="c-font-palette">' . PHP_EOL .
                      $current_palette .
                      '<div class="c-font-palette__overlay">' . PHP_EOL .
                      '<div class="c-font-palette__label">' .
                      '<div class="c-font-palette__name">' . 'Original Style' . '</div>' .
                      '<div class="c-font-palette__control variation-light active" data-target="#_customize-input-sm_font_palette_variation_control-radio-light">' .
                      '<span class="dashicons dashicons-image-rotate"></span>' .
                      '<div class="c-font-palette__tooltip">Light</div>' .
                      '</div>' .
                      '<div class="c-font-palette__control variation-dark" data-target="#_customize-input-sm_font_palette_variation_control-radio-dark">' .
                      '<span class="dashicons dashicons-image-filter"></span>'.
                      '<div class="c-font-palette__tooltip">Dark</div>' .
                      '</div>' .
                      '<div class="c-font-palette__control variation-fontful" data-target="#_customize-input-sm_font_palette_variation_control-radio-fontful">' .
                      '<span class="dashicons dashicons-admin-appearance"></span>' .
                      '<div class="c-font-palette__tooltip">Fontful</div>' .
                      '</div>' .
                      '</div>' . PHP_EOL .
                      '</div>' . PHP_EOL .
                      '</div>' . PHP_EOL .
                      '</div>' . PHP_EOL .
                      '<svg class="c-font-palette__blur" width="15em" height="15em" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" version="1.1">' . PHP_EOL .
                      '<defs>' . PHP_EOL .
                      '<filter id="goo">' . PHP_EOL .
                      '<feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />' . PHP_EOL .
                      '<feFontMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 50 -20" result="goo" />' . PHP_EOL .
                      '<feBlend in="SourceGraphic" in2="goo" />' . PHP_EOL .
                      '</filter>' . PHP_EOL .
                      '</defs>' . PHP_EOL .
                      '</svg>',
              ),
          ) + $config['sections']['style_manager_section']['options'];

		return $config;
	}

	/**
	 * Get the default (hard-coded) font palettes configuration.
	 *
	 * This is only a fallback config in case we can't communicate with the cloud, the first time.
	 *
	 * @since 1.7.5
	 *
	 * @return array
	 */
	protected function get_default_config() {
		$default_config = array(
			'julia' => array(
				'label'   => esc_html__( 'Julia', 'customify' ),
				'preview' => array(
					// Font Palette Name
					'title'            => esc_html__( 'Julia', 'customify' ),
					'description'      => esc_html__( 'A graceful nature, truly tasteful and polished.', 'customify' ),

					// Use the following options to style the preview card fonts
					// Including font-family, size, line-height, weight, letter-spacing and text transform
					'title_font'       => array(
						'font' => 'font_primary',
						'size' => 30,
					),
					'description_font' => array(
						'font' => 'font_body',
						'size' => 17,
					),
				),

				'fonts_logic' => array(
					// Primary is used for main headings [Display, H1, H2, H3]
					'sm_font_primary' => array(
						// Font loaded when a palette is selected
						'font-family'      => 'Young Serif',
						// Load all these fonts weights.
						'font-weights'     => array( 400 ),
						// "Generate" the graph to be used for font-size and line-height.
						'font-size_line-height_points' => array(
							array( 14, 1.7 ),
							array( 50, 1.3 ),
							array( 80, 1 ),
						),

						// Define how fonts will look based on their size
						'font-styles'      => array(
							'min' => 30,
							'max' => 100,

							array(
								'start'          => 0,
								'end'            => 31,
								'font-weight'    => 400,
								'letter-spacing' => '0em',
								'text-transform' => 'none',
							),
							array(
								'start'          => 32,
								'end'            => 43,
								'weight'         => 400,
								'letter-spacing' => '0em',
								'text-transform' => 'none',
							),
							array(
								'start'          => 44,
								'weight'         => 400,
								'letter-spacing' => '0em',
								'text-transform' => 'none',
							),
						),
					),

					// Secondary font is used for smaller headings [H4, H5, H6], including meta details
					'sm_font_secondary' => array(
						'font-family'      => 'HK Grotesk',
						'font-weights'     => array( 400, 500, 700 ),
						'font-size_line-height_points' => array(
							array( 14, 1.7 ),
							array( 50, 1.3 ),
							array( 80, 1 ),
						),
						'font-styles'      => array(
							array(
								'end'            => 14,
								'weight'         => 400,
								'letter-spacing' => '0.08em',
								'text-transform' => 'uppercase',
							),
							array(
								'start'          => 14,
								'end'            => 19,
								'weight'         => 700,
								'letter-spacing' => '0.07em',
								'text-transform' => 'uppercase',
							),
							array(
								'start'          => 19,
								'weight'         => 500,
								'letter-spacing' => 0,
								'text-transform' => 'none',
							),
						),
					),

					// Used for Body Font [eg. entry-content]
					'sm_font_body' => array(
						'font-family'      => 'PT Serif',
						'font-weights'     => array( 400, '400italic', 700, '700italic' ),
						'font-size_line-height_points' => array(
							array( 15, 1.7 ),
							array( 17, 1.6 ),
							array( 18, 1.5 ),
						),

						// Define how fonts will look based on their size
						'font-styles'      => array(
							array(
								'start'          => 0,
								'weight'         => 400,
								'letter-spacing' => 0,
								'text-transform' => 'none',
							),
						),
					),
				),
			),
		);

		return apply_filters( 'customify_style_manager_default_font_palettes', $default_config );
	}



	/**
	 * Get the current font palette ID or false if none is selected.
	 *
	 * @since 1.7.5
	 *
	 * @return string|false
	 */
	protected function get_current_palette() {
		return get_option( 'sm_font_palette', false );
	}

	/**
	 * Get the current font palette variation ID or false if none is selected.
	 *
	 * @since 1.7.5
	 *
	 * @return string|false
	 */
	protected function get_current_palette_variation() {
		return get_option( 'sm_font_palette_variation', false );
	}

	/**
	 * Determine if the selected font palette has been customized and remember this in an option.
	 *
	 * @since 1.7.5
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

		$font_palettes = $this->get_palettes();
		if ( ! isset( $font_palettes[ $current_palette ] ) || empty( $font_palettes[ $current_palette ]['options'] ) ) {
			return false;
		}

		$is_custom_palette = false;
		// If any of the current master fonts has a different value than the one provided by the font palette,
		// it means a custom font palette is in use.
		$current_palette_options = $font_palettes[ $current_palette ]['options'];
		foreach ( $current_palette_options as $setting_id => $value ) {
			if ( $value != get_option( $setting_id ) ) {
				$is_custom_palette = true;
				break;
			}
		}

		update_option( 'sm_is_custom_font_palette', $is_custom_palette, true );

		do_action( 'customify_style_manager_updated_custom_palette_in_use', $is_custom_palette );

		return true;
	}

	/**
	 * Determine if a custom font palette is in use.
	 *
	 * @since 1.7.5
	 *
	 * @return bool
	 */
	protected function is_using_custom_palette(){
		return (bool) get_option( 'sm_is_custom_font_palette', false );
	}

	/**
	 * Get all the defined Style Manager master font field ids.
	 *
	 * @since 1.7.5
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function get_all_master_font_controls_ids( $options ) {
		$master_font_controls = array();

		foreach ( $options as $option_id => $option_settings ) {
			if ( 'font' === $option_settings['type'] ) {
				$master_font_controls[] = $option_id;
			}
		}

		return $master_font_controls;
	}

	/**
	 * Add font palettes usage data to the site data sent to the cloud.
	 *
	 * @since 1.7.5
	 *
	 * @param array $site_data
	 *
	 * @return array
	 */
	public function add_palettes_to_site_data( $site_data ) {
		if ( empty( $site_data['font_palettes'] ) ) {
			$site_data['font_palettes'] = array();
		}

		// If others have added data before us, we will merge with it.
		$site_data['font_palettes'] = array_merge( $site_data['font_palettes'], array(
			'current' => $this->get_current_palette(),
			'variation' => $this->get_current_palette_variation(),
			'custom' => $this->is_using_custom_palette(),
		) );

		return $site_data;
	}

	/**
	 * Main Customify_Font_Palettes Instance
	 *
	 * Ensures only one instance of Customify_Font_Palettes is loaded or can be loaded.
	 *
	 * @since  1.7.5
	 * @static
	 *
	 * @return Customify_Font_Palettes Main Customify_Font_Palettes instance
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
	 * @since 1.7.5
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?' ) ), null );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.7.5
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ),  null );
	} // End __wakeup ()
}

endif;
