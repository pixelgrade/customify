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
	 * @since    1.3.7
	 */
	public static $externalApiEndpoints;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param $parent
	 */
	protected function __construct( $parent = null ) {
		$this->parent = $parent;

		// Make sure our constants are in place
		defined( 'PIXELGRADE_CLOUD__API_BASE' ) || define( 'PIXELGRADE_CLOUD__API_BASE', 'https://cloud.pixelgrade.com/' );

		// Save the external API endpoints in a easy to get property
		self::$externalApiEndpoints = apply_filters( 'customify_style_manager_external_api_endpoints', array(
			'cloud' => array(
				'getDesignAssets'      => array(
					'method' => 'GET',
					'url' => PIXELGRADE_CLOUD__API_BASE . 'wp-json/pixcloud/v1/front/design_assets',
				),
			),
		) );

		$this->add_hooks();
	}

	/**
	 * Initiate our hooks
	 * @since 1.7.0
	 */
	public function add_hooks() {
		add_filter( 'customify_filter_fields', array( $this, 'style_manager_section_config' ), 12, 1 );
		add_filter( 'customify_filter_fields', array( $this, 'add_current_color_palette_control' ), 20, 1 );
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
		// If the current active theme hasn't declared support for style manager, bail.
		if ( ! current_theme_supports( 'customizer_style_manager' ) ) {
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
					'label'        => __( 'Select a color palette:', 'customify' ),
					'desc'         => __( 'Conveniently change the design of your site with color palettes. Easy as pie.', 'customify' ),
					'default'      => 'lilac',
					'choices_type' => 'color_palette',
					'choices'      => $this->get_color_palettes(),
				),
				'sm_palette_variation' => array(
					'type'         => 'radio',
					'setting_type' => 'option',
					'setting_id'   => 'sm_palette_variation',
					'label'        => __( 'Palette Variation', 'customify' ),
					'default'      => 'color_dark_light',
					'live'         => true,
					'choices'      => array(
						'color_dark_light'  => __( 'Default', 'customify' ),
						'dark_color_light'  => __( 'Alt', 'customify' ),

						'color_light_dark'  => __( 'Dark', 'customify' ),
						'light_color_dark'  => __( 'Dark Alt', 'customify' ),

						'light_dark_color'  => __( 'Color', 'customify' ),
						'dark_light_color'  => __( 'Color Alt', 'customify' ),
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
					'label'        => __( 'Swap Colors', 'customify' ),
					'action'       => 'sm_swap_colors',
				),
				'sm_swap_dark_light'            => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_dark_light',
					'label'        => __( 'Swap Dark ⇆ Light', 'customify' ),
					'action'       => 'sm_swap_dark_light',
				),
				'sm_swap_colors_dark'           => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_colors_dark',
					'label'        => __( 'Swap Colors ⇆ Dark', 'customify' ),
					'action'       => 'sm_swap_colors_dark',
				),
				'sm_swap_secondary_colors_dark' => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_swap_secondary_colors_dark',
					'label'        => __( 'Swap Secondary Color ⇆ Secondary Dark', 'customify' ),
					'action'       => 'sm_swap_secondary_colors_dark',
				),
				'sm_advanced_toggle' => array(
					'type'         => 'button',
					'setting_type' => 'option',
					'setting_id'   => 'sm_toggle_advanced_settings',
					'label'        => __( 'Toggle Advanced Settings', 'customify' ),
					'action'       => 'sm_toggle_advanced_settings',
				),
			),
		) );

		return $config;
	}

	/**
	 * Add the current color palette control to the Style Manager section.
	 * @since 1.7.0
	 *
	 * @param array $config
	 * @return array
	 */
	public function add_current_color_palette_control( $config ) {
		// If the theme hasn't declared support for style manager, bail.
		if ( ! current_theme_supports( 'customizer_style_manager' ) ) {
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
				if ( ! empty( $config["sections"]["style_manager_section"]["options"][$setting_id]['connected_fields'] ) ) {
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
			'sm_current_palette' => array(
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
					'<div class="c-palette__control active" data-target="#_customize-input-sm_palette_variation_control-radio-color_dark_light">' .
					'<span class="dashicons dashicons-image-rotate"></span>' .
					'<div class="c-palette__tooltip">Light</div>' .
					'</div>' .
					'<div class="c-palette__control" data-target="#_customize-input-sm_palette_variation_control-radio-color_light_dark">' .
					'<span class="dashicons dashicons-image-filter"></span>'.
					'<div class="c-palette__tooltip">Dark</div>' .
					'</div>' .
					'<div class="c-palette__control" data-target="#_customize-input-sm_palette_variation_control-radio-light_dark_color">' .
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
	 * Get the design assets data.
	 * @since 1.7.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached data or fetch a new one.
	 * @return array|false
	 */
	protected function get_design_assets( $skip_cache = false ) {
		// First try and get the cached data
		$data = get_option( $this->_get_design_assets_cache_key() );
		$expire_timestamp = get_option( $this->_get_design_assets_cache_key() . '_timestamp' );

		// The data isn't set, is expired or we were instructed to skip the cache; we need to fetch fresh data
		if ( true === $skip_cache || false === $data || false === $expire_timestamp || $expire_timestamp < time() ) {
			$request_args = array(
				'method' => self::$externalApiEndpoints['cloud']['getDesignAssets']['method'],
				'timeout'   => 4,
				'blocking'  => true,
				'body'      => array(
					'site_url' => home_url('/'),
					// We are only interested in data needed to identify the theme and eventually deliver only design assets suitable for it.
					'theme_data' => $this->get_theme_data(),
					// We are only interested in data needed to identify the plugin version and eventually deliver design assets suitable for it.
					'site_data' => $this->get_site_data(),
				),
				'sslverify' => false,
			);
			// Get the user's licenses from the server
			$response = wp_remote_request( self::$externalApiEndpoints['cloud']['getDesignAssets']['url'], $request_args );
			if ( is_wp_error( $response ) ) {
				return false;
			}
			$response_data = json_decode( wp_remote_retrieve_body( $response ), true );
			// Bail in case of decode error or failure to retrieve data
			if ( null === $response_data || empty( $response_data['data'] ) || 'success' !== $response_data['code'] ) {
				return false;
			}

			$data = $response_data['data'];

			// Cache the data in a option for 12 hours
			update_option( $this->_get_design_assets_cache_key() , $data, true );
			update_option( $this->_get_design_assets_cache_key() . '_timestamp' , time() + 12 * HOUR_IN_SECONDS, true );
		}

		return $data;
	}

	protected function _get_design_assets_cache_key() {
		return 'customify_style_manager_design_assets';
	}

	/**
	 * Get the default (hard-coded) color palettes configuration.
	 * @since 1.7.0
	 *
	 * @return array
	 */
	protected function get_default_color_palettes_config() {
		$default_color_palettes = array(
			'vasco'  => array(
				'label'   => __( 'Vasco', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/vasco-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#38C3C8',
					'sm_color_secondary' => '#F59828',
					'sm_color_tertiary'  => '#F59828',
					'sm_dark_primary'    => '#2b2b28',
					'sm_dark_secondary'  => '#2B3D39',
					'sm_dark_tertiary'   => '#65726F',
					'sm_light_primary'   => '#F5F6F1',
					'sm_light_secondary' => '#FFFFFF',
				),
			),
			'felt'  => array(
				'label'   => __( 'Felt', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/felt-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#ff6000',
					'sm_color_secondary' => '#FF9200',
					'sm_color_tertiary'  => '#FF9200',
					'sm_dark_primary'    => '#1C1C1C',
					'sm_dark_secondary'  => '#161616',
					'sm_dark_tertiary'   => '#161616',
					'sm_light_primary'   => '#FFFCFC',
					'sm_light_secondary' => '#fff4e8',
					'sm_light_tertiary'  => '#fff4e8',
				),
			),
			'julia'  => array(
				'label'   => __( 'Julia', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/julia-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#3349B8',
					'sm_color_secondary' => '#3393B8',
					'sm_color_tertiary'  => '#3393B8',
					'sm_dark_primary'    => '#161616',
					'sm_dark_secondary'  => '#383C50',
					'sm_dark_tertiary'   => '#383C50',
					'sm_light_primary'   => '#f7f6f5',
					'sm_light_secondary' => '#e7f2f8',
					'sm_light_tertiary'  => '#e7f2f8',
				),
			),
			'gema'  => array(
				'label'   => __( 'Gema', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/gema-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#E03A3A',
					'sm_color_secondary' => '#E03A3A',
					'sm_color_tertiary'  => '#E03A3A',
					'sm_dark_primary'    => '#000000',
					'sm_dark_secondary'  => '#000000',
					'sm_dark_tertiary'   => '#A3A3A1',
					'sm_light_primary'   => '#FFFFFF',
					'sm_light_secondary' => '#FFFFFF',
					'sm_light_tertiary'  => '#FFFFFF',
				),
			),
			'patch'  => array(
				'label'   => __( 'Patch', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/patch-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#ffeb00',
					'sm_color_secondary' => '#3200B2',
					'sm_color_tertiary'  => '#3200B2',
					'sm_dark_primary'    => '#171617',
					'sm_dark_secondary'  => '#3d3e40',
					'sm_dark_tertiary'   => '#b5b5b5',
					'sm_light_primary'   => '#FFFFFF',
					'sm_light_secondary' => '#FFFFFF',
					'sm_light_tertiary'  => '#FFFFFF',
				),
			),
			'silk'  => array(
				'label'   => __( 'Silk', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/silk-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#A33B61',
					'sm_color_secondary' => '#FCC9B0',
					'sm_color_tertiary'  => '#FCC9B0',
					'sm_dark_primary'    => '#000000',
					'sm_dark_secondary'  => '#000000',
					'sm_dark_tertiary'   => '#A3A3A1',
					'sm_light_primary'   => '#FFFFFF',
					'sm_light_secondary' => '#FFFFFF',
					'sm_light_tertiary'  => '#FFFFFF',
				),
			),
			'hive'  => array(
				'label'   => __( 'Hive', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/hive-theme-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#ffeb00',
					'sm_color_secondary' => '#3200B2',
					'sm_color_tertiary'  => '#3200B2',
					'sm_dark_primary'    => '#171617',
					'sm_dark_secondary'  => '#171617',
					'sm_dark_tertiary'   => '#363636',
					'sm_light_primary'   => '#FFFFFF',
					'sm_light_secondary' => '#FFFFFF',
					'sm_light_tertiary'  => '#FFFFFF',
				),
			),
			'lilac'  => array(
				'label'   => __( 'Lilac', 'customify' ),
				'preview' => array(
					'background_image_url' => 'http://pxgcdn.com/images/style-manager/color-palettes/lilac-palette.jpg',
				),
				'options' => array(
					'sm_color_primary'   => '#DD8CA9',
					'sm_color_secondary' => '#8C9CDE',
					'sm_color_tertiary'  => '#8C9CDE',
					'sm_dark_primary'    => '#303030',
					'sm_dark_secondary'  => '#303030',
					'sm_dark_tertiary'   => '#A3A3A1',
					'sm_light_primary'   => '#ECEEED',
					'sm_light_secondary' => '#FFE9E5',
					'sm_light_tertiary'  => '#FFE9E5',
				),
			),
		);

		return apply_filters( 'customify_style_manager_default_color_palettes', $default_color_palettes );
	}

	protected function get_theme_data() {
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

	protected function get_site_data() {
		$site_data = array(
			'url' => home_url('/'),
			'is_ssl' => is_ssl(),
		);

		$site_data['customify'] = array(
			'version' => PixCustomifyPlugin()->get_version(),
		);

		return apply_filters( 'customify_style_manager_get_site_data', $site_data );
	}

	/**
	 * Get all the defined Style Manager master color field ids.
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
