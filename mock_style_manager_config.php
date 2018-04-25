<?php
if ( ! function_exists('mock_style_manager_section') ) {

	/**
	 * Setup the Style Manager Customizer section and mock some color palettes.
	 *
	 * @param $config array This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'
	 * @return $config
	 */
	function mock_style_manager_section( $config ) {
		// If the theme hasn't declared support for style manager, bail.
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
				'sm_color_palette'              => array(
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
					'choices'      => array(
						'vasco'  => array(
							'label'   => __( 'Vasco Theme', 'customify' ),
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
								'sm_light_tertiary'  => '#FFFFFF',
							),
						),
						'felt'  => array(
							'label'   => __( 'Felt Theme', 'customify' ),
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
							'label'   => __( 'Julia Theme', 'customify' ),
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
							'label'   => __( 'Gema Theme', 'customify' ),
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
							'label'   => __( 'Patch Theme', 'customify' ),
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
							'label'   => __( 'Silk Theme', 'customify' ),
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
							'label'   => __( 'Hive Theme', 'customify' ),
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

				'sm_palette_variation' => array(
					'type'         => 'radio',
					'setting_type' => 'option',
					'setting_id'   => 'sm_palette_variation',
					'label'        => __( 'Palette Variation', 'customify' ),
					'default'      => 'default',
					'live'         => true,
					'choices'      => array(
						'default' => __( 'Default', 'customify' ),
						'dark' => __( 'Dark', 'customify' ),
						'colorful' => __( 'Colorful', 'customify' ),
					),
				),

			),
		) );

		return $config;
	}

}
add_filter( 'customify_filter_fields', 'mock_style_manager_section', 12, 1 );

function alter_color_palette( $config ) {
	$variation = get_option( 'sm_palette_variation' );
	$new_config = $config;

//	$options['sections']['style_manager_section'] = array_replace_recursive( $options['sections']['style_manager_section'], array(
//		'options' => array(
//			'sm_color_primary' => array(
//				'connected_fields' => array(

	switch ( $variation ) {
		case 'dark':
			$new_config['sections']['style_manager_section']['options']['sm_dark_primary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_light_primary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_dark_secondary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_light_secondary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_dark_tertiary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_light_tertiary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_light_primary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_dark_primary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_light_secondary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_dark_secondary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_light_tertiary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_dark_tertiary']['connected_fields'];
			break;
		case 'colorful':
			$new_config['sections']['style_manager_section']['options']['sm_color_primary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_light_primary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_color_secondary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_light_secondary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_color_tertiary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_light_tertiary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_dark_primary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_color_primary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_dark_secondary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_color_secondary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_dark_tertiary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_color_tertiary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_light_primary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_dark_primary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_light_secondary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_dark_secondary']['connected_fields'];
			$new_config['sections']['style_manager_section']['options']['sm_light_tertiary']['connected_fields'] = $config['sections']['style_manager_section']['options']['sm_dark_tertiary']['connected_fields'];
			break;
		default:
			break;
	}

	return $new_config;
}
//add_filter( 'customify_filter_fields', 'alter_color_palette', 1000, 1 );
