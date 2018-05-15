<?php

class Customify_Style_Manager {

	/**
	 * Holds the only instance of this class.
	 * @var null|Customify_Style_Manager
	 * @access protected
	 * @since 1.7.0
	 */
	protected static $_instance = null;

	/**
	 * The main plugin object (the parent).
	 * @var     PixCustomifyPlugin
	 * @access  public
	 * @since     1.7.0
	 */
	public $parent = null;

	/**
	 * External REST API endpoints used for communicating with the Pixelgrade Cloud.
	 * @var array
	 * @access public
	 * @since    1.7.0
	 */
	public static $externalApiEndpoints;

	/**
	 * The current design assets config.
	 * @var     array
	 * @access  public
	 * @since   1.7.0
	 */
	public $design_assets = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param $parent
	 */
	protected function __construct( $parent = null ) {
		$this->parent = $parent;

		// Make sure our constants are in place, if not already defined.
		defined( 'PIXELGRADE_CLOUD__API_BASE' ) || define( 'PIXELGRADE_CLOUD__API_BASE', 'https://cloud.pixelgrade.com/' );

		// Save the external API endpoints in a easy to get property.
		self::$externalApiEndpoints = apply_filters( 'customify_style_manager_external_api_endpoints', array(
			'cloud' => array(
				'getDesignAssets' => array(
					'method' => 'GET',
					'url'    => PIXELGRADE_CLOUD__API_BASE . 'wp-json/pixcloud/v1/front/design_assets',
				),
				'stats'           => array(
					'method' => 'POST',
					'url'    => PIXELGRADE_CLOUD__API_BASE . 'wp-json/pixcloud/v1/front/stats',
				),
			),
		) );

		// Hook up.
		$this->add_hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since 1.7.0
	 */
	public function add_hooks() {
		// Handle the Customizer Style Manager config.
		add_filter( 'customify_filter_fields', array( $this, 'style_manager_section_config' ), 12, 1 );
		add_filter( 'customify_filter_fields', array( $this, 'add_current_color_palette_control' ), 20, 1 );

		// Handle the logic on settings update/save.
		add_action( 'customize_save_after', array( $this, 'update_custom_palette_in_use' ), 10, 1 );

		// Handle the logic for user feedback.
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'output_user_feedback_modal' ) );
		add_action( 'wp_ajax_customify_style_manager_user_feedback', array( $this, 'user_feedback_callback' ) );

		// Scripts enqueued in the Customizer
		add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_scripts' ), 10 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_scripts' ), 10 );
	}

	/**
	 * Register Customizer admin scripts
	 */
	function register_admin_customizer_scripts() {
		wp_register_script( $this->parent->get_slug() . '-swap-values', plugins_url( 'js/customizer/customify-swap-values.js', $this->parent->file ), array( 'jquery' ), $this->parent->get_version() );
		wp_register_script( $this->parent->get_slug() . '-palette-variations', plugins_url( 'js/customizer/customify-palette-variations.js', $this->parent->file ), array( 'jquery' ), $this->parent->get_version() );
		wp_register_script( $this->parent->get_slug() . '-palettes', plugins_url( 'js/customizer/customify-palettes.js', $this->parent->file ), array( 'jquery', $this->parent->get_slug() . '-palette-variations', $this->parent->get_slug() . '-swap-values' ), $this->parent->get_version() );
	}

	/**
	 * Enqueue Customizer admin scripts
	 */
	function enqueue_admin_customizer_scripts() {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		wp_enqueue_script( $this->parent->get_slug() . '-palettes' );
	}

	/**
	 * Determine if Style Manager is supported.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function is_supported() {
		$has_support = boolval( current_theme_supports( 'customizer_style_manager' ) );

		return apply_filters( 'customify_style_manager_is_supported', $has_support );
	}

	/**
	 * Setup the Style Manager Customizer section config.
	 *
	 * @since 1.7.0
	 *
	 * @param array $config This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'
	 * @return array
	 */
	public function style_manager_section_config( $config ) {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		if ( ! isset( $config['sections']['style_manager_section'] ) ) {
			$config['sections']['style_manager_section'] = array();
		}

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section'] = array_replace_recursive( $config['sections']['style_manager_section'], array(
			'title'   => esc_html__( 'Style Manager', 'customify' ),
			'section_id' => 'style_manager_section', // We will force this section id preventing prefixing and other regular processing.
			'options' => array(
				'sm_color_palette' => array(
					'type'         => 'preset',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type' => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'   => 'sm_color_palette',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'         => true,
					'label'        => esc_html__( 'Select a color palette:', 'customify' ),
					'desc'         => esc_html__( 'Conveniently change the design of your site with color palettes. Easy as pie.', 'customify' ),
					'default'      => 'lilac',
					'choices_type' => 'color_palette',
					'choices'      => $this->get_color_palettes(),
				),
				'sm_color_palette_variation' => array(
					'type'         => 'radio',
					'setting_type' => 'option',
					'setting_id'   => 'sm_color_palette_variation',
					'label'        => esc_html__( 'Palette Variation', 'customify' ),
					'default'      => 'light',
					'live'         => true,
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
					'label'            => esc_html__( 'Color Primary', 'customify' ),
					'default'          => '#ffeb00',
					'connected_fields' => array(),
				),
				'sm_color_secondary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_secondary',
					'live'             => true,
					'label'            => esc_html__( 'Color Secondary', 'customify' ),
					'default'          => '#00ecff',
					'connected_fields' => array(),
				),
				'sm_color_tertiary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_color_tertiary',
					'live'             => true,
					'label'            => esc_html__( 'Color Tertiary', 'customify' ),
					'default'          => '#00ecff',
					'connected_fields' => array(),
				),
				'sm_dark_primary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_primary',
					'live'             => true,
					'label'            => esc_html__( 'Dark Primary', 'customify' ),
					'default'          => '#171617',
					'connected_fields' => array(),
				),
				'sm_dark_secondary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_secondary',
					'live'             => true,
					'label'            => esc_html__( 'Dark Secondary', 'customify' ),
					'default'          => '#383c50',
					'connected_fields' => array(),
				),
				'sm_dark_tertiary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_dark_tertiary',
					'live'             => true,
					'label'            => esc_html__( 'Dark Tertiary', 'customify' ),
					'default'          => '#65726F',
					'connected_fields' => array(),
				),
				'sm_light_primary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_primary',
					'live'             => true,
					'label'            => esc_html__( 'Light Primary', 'customify' ),
					'default'          => '#ffffff',
					'connected_fields' => array(),
				),
				'sm_light_secondary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_secondary',
					'live'             => true,
					'label'            => esc_html__( 'Light Secondary', 'customify' ),
					'default'          => '#ffffff',
					'connected_fields' => array(),
				),
				'sm_light_tertiary'              => array(
					'type'             => 'color',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_light_tertiary',
					'live'             => true,
					'label'            => esc_html__( 'Light Tertiary', 'customify' ),
					'default'          => '#ffffff',
					'connected_fields' => array(),
				),
				'sm_swap_colors'                => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_colors',
					'label'        => esc_html__( 'Swap Colors', 'customify' ),
					'action'       => 'sm_swap_colors',
				),
				'sm_swap_dark_light'            => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_dark_light',
					'label'        => esc_html__( 'Swap Dark ⇆ Light', 'customify' ),
					'action'       => 'sm_swap_dark_light',
				),
				'sm_swap_colors_dark'           => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_colors_dark',
					'label'        => esc_html__( 'Swap Colors ⇆ Dark', 'customify' ),
					'action'       => 'sm_swap_colors_dark',
				),
				'sm_swap_secondary_colors_dark' => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_secondary_colors_dark',
					'label'        => esc_html__( 'Swap Secondary Color ⇆ Secondary Dark', 'customify' ),
					'action'       => 'sm_swap_secondary_colors_dark',
				),
				'sm_advanced_toggle' => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_toggle_advanced_settings',
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
	 * @since 1.7.0
	 *
	 * @param array $config
	 * @return array
	 */
	public function add_current_color_palette_control( $config ) {
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
				if ( ! empty( $config['sections']['style_manager_section']['options'][ $setting_id ]['connected_fields'] ) ) {
					$current_palette .=
						'<div class="color ' . $setting_id . '" data-setting="' . $setting_id . '">' . PHP_EOL .
						'<div class="fill"></div>' . PHP_EOL .
						'<div class="picker"><i></i></div>' . PHP_EOL .
						'</div>' . PHP_EOL;
				}
			}
			$current_palette .= '</div>';
		}

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section']['options'] = array(
              'sm_current_color_palette' => array(
                  'type' => 'html',
                  'html' =>
                      '<div class="palette-container">' . PHP_EOL .
                      '<span class="customize-control-title">Current Color Palette:</span>' . PHP_EOL .
                      '<span class="description customize-control-description">Choose a color palette to start with. Adjust its style using the variation buttons below.</span>' . PHP_EOL .
                      '<div class="c-palette">' . PHP_EOL .
                      $current_palette .
                      '<div class="c-palette__overlay">' . PHP_EOL .
                      '<div class="c-palette__label">' .
                      '<div class="c-palette__name">' . 'Original Style' . '</div>' .
                      '<div class="c-palette__control variation-light active" data-target="#_customize-input-sm_color_palette_variation_control-radio-light">' .
                      '<span class="dashicons dashicons-image-rotate"></span>' .
                      '<div class="c-palette__tooltip">Light</div>' .
                      '</div>' .
                      '<div class="c-palette__control variation-dark" data-target="#_customize-input-sm_color_palette_variation_control-radio-dark">' .
                      '<span class="dashicons dashicons-image-filter"></span>'.
                      '<div class="c-palette__tooltip">Dark</div>' .
                      '</div>' .
                      '<div class="c-palette__control variation-colorful" data-target="#_customize-input-sm_color_palette_variation_control-radio-colorful">' .
                      '<span class="dashicons dashicons-admin-appearance"></span>' .
                      '<div class="c-palette__tooltip">Colorful</div>' .
                      '</div>' .
                      '</div>' . PHP_EOL .
                      '</div>' . PHP_EOL .
                      '</div>' . PHP_EOL .
                      '</div>' . PHP_EOL .
                      '<svg class="c-palette__blur" width="15em" height="15em" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" version="1.1">' . PHP_EOL .
                      '<defs>' . PHP_EOL .
                      '<filter id="goo">' . PHP_EOL .
                      '<feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />' . PHP_EOL .
                      '<feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 50 -20" result="goo" />' . PHP_EOL .
                      '<feBlend in="SourceGraphic" in2="goo" />' . PHP_EOL .
                      '</filter>' . PHP_EOL .
                      '</defs>' . PHP_EOL .
                      '</svg>',
              ),
          ) + $config['sections']['style_manager_section']['options'];

		return $config;
	}

	/**
	 * Get the color palettes configuration.
	 * @since 1.7.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 * @return array
	 */
	protected function get_color_palettes( $skip_cache = false ) {
		// Get the design assets data.
		$design_assets = $this->get_design_assets( $skip_cache );
		if ( false === $design_assets || empty( $design_assets['color_palettes'] ) ) {
			$color_palettes_config = $this->get_default_color_palettes_config();
		} else {
			$color_palettes_config = $design_assets['color_palettes'];
		}

		return apply_filters( 'customify_get_color_palettes', $color_palettes_config );
	}

	/**
	 * Get the design assets configuration.
	 *
	 * @since 1.7.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 * @return array
	 */
	protected function get_design_assets( $skip_cache = false ) {
		if ( ! is_null( $this->design_assets ) ) {
			return $this->design_assets;
		}

		$this->design_assets = apply_filters( 'customify_style_manager_maybe_fetch_design_assets', $this->maybe_fetch_design_assets( $skip_cache ) );

		return $this->design_assets;
	}

	/**
	 * Fetch the design assets data from the Pixelgrade Cloud.
	 *
	 * Caches the data for 12 hours. Use local defaults if not available.
	 *
	 * @since 1.7.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached data or fetch a new one.
	 * @return array|false
	 */
	protected function maybe_fetch_design_assets( $skip_cache = false ) {
		// We don't force skip the cache for AJAX requests for performance reasons.
		if ( ! wp_doing_ajax() && defined('CUSTOMIFY_SM_ALWAYS_FETCH_DESIGN_ASSETS' ) && true === CUSTOMIFY_SM_ALWAYS_FETCH_DESIGN_ASSETS ) {
			$skip_cache = true;
		}

		// First try and get the cached data
		$data = get_option( $this->_get_design_assets_cache_key() );
		$expire_timestamp = get_option( $this->_get_design_assets_cache_key() . '_timestamp' );

		// The data isn't set, is expired or we were instructed to skip the cache; we need to fetch fresh data
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {
			$request_data = apply_filters( 'customify_pixelgrade_cloud_request_data', array(
				'site_url' => home_url('/'),
				// We are only interested in data needed to identify the theme and eventually deliver only design assets suitable for it.
				'theme_data' => $this->get_active_theme_data(),
				// We are only interested in data needed to identify the plugin version and eventually deliver design assets suitable for it.
				'site_data' => $this->get_site_data(),
			), $this );

			$request_args = array(
				'method' => self::$externalApiEndpoints['cloud']['getDesignAssets']['method'],
				'timeout'   => 4,
				'blocking'  => true,
				'body'      => $request_data,
				'sslverify' => false,
			);
			// Get the design assets from the cloud.
			$response = wp_remote_request( self::$externalApiEndpoints['cloud']['getDesignAssets']['url'], $request_args );
			// Bail in case of decode error or failure to retrieve data.
			// We will return the data already available.
			if ( is_wp_error( $response ) ) {
				return $data;
			}
			$response_data = json_decode( wp_remote_retrieve_body( $response ), true );
			// Bail in case of decode error or failure to retrieve data.
			// We will return the data already available.
			if ( null === $response_data || empty( $response_data['data'] ) || empty( $response_data['code'] ) || 'success' !== $response_data['code'] ) {
				return $data;
			}

			$data = apply_filters( 'customify_style_manager_fetch_design_assets', $response_data['data'] );

			// Cache the data in an option for 12 hours
			update_option( $this->_get_design_assets_cache_key() , $data, true );
			update_option( $this->_get_design_assets_cache_key() . '_timestamp' , time() + 12 * HOUR_IN_SECONDS, true );
		}

		return $data;
	}

	/**
	 * Get the design assets cache key.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	protected function _get_design_assets_cache_key() {
		return 'customify_style_manager_design_assets';
	}

	/**
	 * Get the default (hard-coded) color palettes configuration.
	 *
	 * @since 1.7.0
	 *
	 * @return array
	 */
	protected function get_default_color_palettes_config() {
		$default_color_palettes = array(
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
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/lilac-palette.jpg',
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

		return apply_filters( 'customify_style_manager_default_color_palettes', $default_color_palettes );
	}

	/**
	 * Get the active theme data.
	 *
	 * @since 1.7.0
	 *
	 * @return array
	 */
	public function get_active_theme_data() {
		$theme_data = array();

		$slug = basename( get_template_directory() );

		$theme_data['slug'] = $slug;

		// Get the current theme style.css data.
		$current_theme = wp_get_theme( get_template() );
		if ( ! empty( $current_theme ) && ! is_wp_error( $current_theme ) ) {
			$theme_data['name'] = $current_theme->get('Name');
			$theme_data['themeuri'] = $current_theme->get('ThemeURI');
			$theme_data['version'] = $current_theme->get('Version');
			$theme_data['textdomain'] = $current_theme->get('TextDomain');
		}

		// Maybe get the WUpdates theme info if it's a theme delivered from WUpdates.
		$wupdates_ids = apply_filters( 'wupdates_gather_ids', array() );
		if ( ! empty( $wupdates_ids[ $slug ] ) ) {
			$theme_data['wupdates'] = $wupdates_ids[ $slug ];
		}

		return apply_filters( 'customify_style_manager_get_theme_data', $theme_data );
	}

	/**
	 * Get the site data.
	 *
	 * @since 1.7.0
	 *
	 * @return array
	 */
	public function get_site_data() {
		$site_data = array(
			'url' => home_url('/'),
			'is_ssl' => is_ssl(),
		);

		$site_data['wp'] = array(
			'version' => get_bloginfo('version'),
		);

		$site_data['customify'] = array(
			'version' => PixCustomifyPlugin()->get_version(),
		);

		$site_data['color_palettes'] = array(
			'current' => $this->get_current_color_palette(),
			'variation' => $this->get_current_color_palette_variation(),
			'custom' => $this->is_using_custom_color_palette(),
		);

		return apply_filters( 'customify_style_manager_get_site_data', $site_data );
	}

	/**
	 * Get the current color palette ID or false if none is selected.
	 *
	 * @since 1.7.0
	 *
	 * @return string|false
	 */
	protected function get_current_color_palette() {
		return get_option( 'sm_color_palette', false );
	}

	/**
	 * Get the current color palette variation ID or false if none is selected.
	 *
	 * @since 1.7.0
	 *
	 * @return string|false
	 */
	protected function get_current_color_palette_variation() {
		return get_option( 'sm_color_palette_variation', false );
	}

	/**
	 * Determine if the selected color palette has been customized and remember this in an option.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function update_custom_palette_in_use() {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return false;
		}

		$current_palette = $this->get_current_color_palette();
		if ( empty( $current_palette ) ) {
			return false;
		}

		$color_palettes = $this->get_color_palettes();
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

		update_option( 'sm_is_custom_color_palette', $is_custom_palette );

		do_action( 'customify_style_manager_updated_custom_palette_in_use', $is_custom_palette, $this );

		return true;
	}

	/**
	 * Determine if a custom color palette is in use.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	protected function is_using_custom_color_palette(){
		return boolval( get_option( 'sm_is_custom_color_palette', false ) );
	}

	/**
	 * Get all the defined Style Manager master color field ids.
	 *
	 * @since 1.7.0
	 *
	 * @param array $options
	 * @return array
	 */
	public function get_all_master_color_controls_ids( $options ) {
		$master_color_controls = array();

		foreach ( $options as $option_id => $option_settings ) {
			if ( 'color' === $option_settings['type'] ) {
				$master_color_controls[] = $option_id;
			}
		}

		return $master_color_controls;
	}

	/**
	 * Output the user feedback modal markup, if we need to.
	 *
	 * @since 1.7.0
	 */
	public function output_user_feedback_modal() {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		// Only output if the user didn't provide feedback.
		if ( ! $this->user_provided_feedback() ) { ?>
			<div id="style-manager-user-feedback-modal">
				<div class="modal">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<form id="style-manager-user-feedback" action="#" method="post">
								<input type="hidden" name="type" value="1_to_5" />
								<div class="modal-header">
									<button type="button" class="close icon media-modal-close" data-dismiss="modal" aria-label="Close"><span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span></button>
									<!-- <a href="#" class="close button button--naked gray" data-dismiss="modal" aria-label="Close">Close</a> -->
								</div>
								<div class="modal-body full">
									<div class="box box--large">
										<div class="first-step">
											<h2 class="modal-title">How would you rate your experience with using Color Palettes?</h2>
											<div class="scorecard">
												<span>Worst</span>
												<label>
													<input type="radio" name="rating" value="1" required />
													<span>1</span>
												</label>
												<label>
													<input type="radio" name="rating" value="2" required />
													<span>2</span>
												</label>
												<label>
													<input type="radio" name="rating" value="3" required />
													<span>3</span>
												</label>
												<label>
													<input type="radio" name="rating" value="4" required />
													<span>4</span>
												</label>
												<label>
													<input type="radio" name="rating" value="5" required />
													<span>5</span>
												</label>
												<span>Best</span>
											</div>
										</div>
										<div class="second-step hidden">
											<p><strong>What makes you give <span class="rating-placeholder">5</span>*?</strong> I hope you’ll answer and help us do better:</p>
											<div class="not-floating-labels">
												<div class="form-row field">
												<textarea name="message" placeholder="Your message.."
												          id="style-manager-user-feedback-message" rows="4" oninvalid="this.setCustomValidity('May we have a little more info about your experience?')" oninput="setCustomValidity('')" required></textarea>
												</div>
											</div>
											<button id="style-manager-user-feedback_btn" class="button" type="submit"><?php _e( 'Submit my feedback', 'customify' ); ?></button>
										</div>
										<div class="thanks-step hidden">
											<h3 class="modal-title">Thanks for your feedback!</h3>
											<p>This will help us improve the product. Stay awesome! 🤗</p>
										</div>
										<div class="error-step hidden">
											<h3 class="modal-title">We've hit a snag!</h3>
											<p>We couldn't record your feedback and we would truly appreciate it if you would try it again at a latter time. Stay awesome! 🤗</p>
										</div>
									</div>
								</div>
								<div class="modal-footer full">

								</div>
							</form>
						</div>
					</div>
				</div>
				<!-- End Modal -->
				<!-- Modal Backdrop (Shadow) -->
				<div class="modal-backdrop"></div>
			</div>

		<?php }
	}

	/**
	 * @param bool|int $timestamp_limit Optional. Timestamp to compare the time the user provided feedback.
	 *                              If the provided timestamp is earlier than the time the user provided feedback, returns false.
	 *
	 * @return bool
	 */
	public function user_provided_feedback( $timestamp_limit = false ) {
		if ( defined( 'CUSTOMIFY_SM_ALWAYS_ASK_FOR_FEEDBACK' ) && true === CUSTOMIFY_SM_ALWAYS_ASK_FOR_FEEDBACK ) {
			return false;
		}

		$user_provided_feedback = get_option( 'style_manager_user_feedback_provided' );
		if ( empty( $user_provided_feedback ) ) {
			return false;
		}

		if ( ! empty( $timestamp ) && is_int( $timestamp ) && $timestamp_limit > $user_provided_feedback ) {
			return  false;
		}

		return true;
	}

	/**
	 * Callback for the user feedback AJAX call.
	 *
	 * @since 1.7.0
	 */
	public function user_feedback_callback() {
		check_ajax_referer( 'customify_style_manager_user_feedback', 'nonce' );

		if ( empty( $_POST['type'] ) ) {
			wp_send_json_error( esc_html__( 'No type provided', 'customify' ) );
		}

		if ( empty( $_POST['rating'] ) ) {
			wp_send_json_error( esc_html__( 'No rating provided', 'customify' ) );
		}

		$type = sanitize_text_field( $_POST['type'] );
		$rating = intval( $_POST['rating'] );
		$message = '';
		if ( ! empty( $_POST['message'] ) ) {
			$message = wp_kses_post( $_POST['message'] );
		}

		$request_data = apply_filters( 'customify_pixelgrade_cloud_request_data', array(
			'site_url'          => home_url( '/' ),
			'satisfaction_data' => array(
				'type'    => $type,
				'rating'  => $rating,
				'message' => $message,
			),
		), $this );

		$request_args = array(
			'method' => self::$externalApiEndpoints['cloud']['stats']['method'],
			'timeout'   => 5,
			'blocking'  => true,
			'body'      => $request_data,
			'sslverify' => false,
		);

		// Send the feedback.
		$response = wp_remote_request( self::$externalApiEndpoints['cloud']['stats']['url'], $request_args );
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( esc_html__( 'Sorry, something went wrong and we couldn\'t save your feedback.', 'customify' ) );
		}
		$response_data = json_decode( wp_remote_retrieve_body( $response ), true );
		// Bail in case of decode error or failure to retrieve data
		if ( null === $response_data || empty( $response_data['code'] ) || 'success' !== $response_data['code'] ) {
			wp_send_json_error( esc_html__( 'Sorry, something went wrong and we couldn\'t save your feedback.', 'customify' ) );
		}

		// We need to remember that the user provided feedback (and at what timestamp).
		update_option( 'style_manager_user_feedback_provided', time(), true );

		wp_send_json_success( esc_html__( 'Thank you for your feedback.', 'customify' ) );
	}

	/**
	 * Main Customify_Style_Manager Instance
	 *
	 * Ensures only one instance of Customify_Style_Manager is loaded or can be loaded.
	 *
	 * @since  1.7.0
	 * @static
	 * @param  object $parent Main PixCustomifyPlugin instance.
	 *
	 * @return Customify_Style_Manager Main Customify_Style_Manager instance
	 */
	public static function instance( $parent = null ) {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.7.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?' ) ), null );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.7.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ),  null );
	} // End __wakeup ()
}
