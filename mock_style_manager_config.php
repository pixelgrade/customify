<?php

/**
 * Setup the Style Manager Customizer section and mock some color palettes.
 *
 * @param $config array This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'
 * @return $config
 */

if ( ! function_exists('mock_style_manager_section') ) {

	function mock_style_manager_section( $config ) {
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
					'default'      => 'royal',
					'choices_type' => 'radio',
					'choices'      => array(
						'vasco'  => array(
							'label'   => __( 'Vasco', 'customify' ),
							'options' => array(
								'sm_color_primary'   => '#38C3C8',
								'sm_color_secondary' => '#F59828',
								'sm_dark_primary'    => '#2b2b28',
								'sm_dark_secondary'  => '#2B3D39',
								'sm_dark_tertiary'   => '#65726F',
								'sm_light_primary'   => '#F5F6F1',
								'sm_light_secondary' => '#E6F7F7',
							),
						),
						'felt'  => array(
							'label'   => __( 'Felt', 'customify' ),
							'options' => array(
								'sm_color_primary'   => '#ff6000',
								'sm_color_secondary' => '#FF9200',
								'sm_dark_primary'    => '#1C1C1C',
								'sm_dark_secondary'  => '#161616',
								'sm_light_primary'   => '#FFFCFC',
								'sm_light_secondary' => '#fff4e8',
							),
						),
						'julia'  => array(
							'label'   => __( 'Julia', 'customify' ),
							'options' => array(
								'sm_color_primary'   => '#3349B8',
								'sm_color_secondary' => '#3393B8',
								'sm_dark_primary'    => '#161616',
								'sm_dark_secondary'  => '#383C50',
								'sm_light_primary'   => '#f7f6f5',
								'sm_light_secondary' => '#e7f2f8',
							),
						),
						'julia_colored'  => array(
							'label'   => __( 'Julia Colored', 'customify' ),
							'options' => array(
								'sm_dark_primary'   => '#3349B8',
								'sm_dark_secondary' => '#3393B8',
								'sm_color_primary'    => '#161616',
								'sm_color_secondary'  => '#383C50',
								'sm_light_primary'   => '#f7f6f5',
								'sm_light_secondary' => '#e7f2f8',
							),
						),
						'julia_inversed'  => array(
							'label'   => __( 'Julia Inversed', 'customify' ),
							'options' => array(
								'sm_color_primary'   => '#3349B8',
								'sm_color_secondary' => '#3393B8',
								'sm_light_primary'    => '#161616',
								'sm_light_secondary'  => '#383C50',
								'sm_dark_primary'   => '#f7f6f5',
								'sm_dark_secondary' => '#e7f2f8',
							),
						),
						'gema'  => array(
							'label'   => __( 'Gema Theme', 'customify' ),
							'options' => array(
								'sm_color_primary'   => '#E03A3A',
								'sm_color_secondary'   => '#E03A3A',
								'sm_dark_primary'    => '#000000',
								'sm_dark_secondary'  => '#000000',
								'sm_dark_tertiary'  => '#A3A3A1',
								'sm_light_primary'   => '#FFFFFF',
								'sm_light_secondary' => '#FFFFFF',
							),
						),
						'patch'  => array(
							'label'   => __( 'Patch Theme', 'customify' ),
							'options' => array(
								'sm_color_primary'   => '#ffeb00',
								'sm_color_secondary'   => '#3200B2',
								'sm_dark_primary'    => '#171617',
								'sm_dark_secondary'  => '#3d3e40',
								'sm_dark_tertiary'  => '#afafaf',
								'sm_light_primary'   => '#FFFFFF',
								'sm_light_secondary' => '#FFFFFF',
							),
						),
						'hive'  => array(
							'label'   => __( 'Hive Theme', 'customify' ),
							'options' => array(
								'sm_color_primary'   => '#ffeb00',
								'sm_color_secondary'   => '#3200B2',
								'sm_dark_primary'    => '#171617',
								'sm_dark_secondary'  => '#171617',
								'sm_dark_tertiary'  => '#363636',
								'sm_light_primary'   => '#FFFFFF',
								'sm_light_secondary' => '#FFFFFF',
							),
						),
						'hive_inversed'  => array(
							'label'   => __( 'Hive Inversed', 'customify' ),
							'options' => array(
								'sm_color_primary'   => '#ffeb00',
								'sm_color_secondary'   => '#3200B2',
								'sm_light_primary'    => '#171617',
								'sm_light_secondary'  => '#363636',
								'light_tertiary'  => '#afafaf',
								'sm_dark_primary'   => '#FFFFFF',
								'sm_dark_secondary' => '#FFFFFF',
								'sm_dark_tertiary' => '#FFFFFF',
							),
						),
						'hive_rotate'  => array(
							'label'   => __( 'Hive Swap Colors', 'customify' ),
							'options' => array(
								'sm_color_primary'   => '#3200B2',
								'sm_color_secondary'   => '#ffeb00',
								'sm_light_primary'    => '#171617',
								'sm_light_secondary'  => '#363636',
								'light_tertiary'  => '#afafaf',
								'sm_dark_primary'   => '#FFFFFF',
								'sm_dark_secondary' => '#FFFFFF',
								'sm_dark_tertiary' => '#FFFFFF',
							),
						),
						'silk'  => array(
							'label'   => __( 'Silk Theme', 'customify' ),
							'options' => array(
								'sm_color_primary'   => '#A33B61',
								'sm_color_secondary'   => '#FCC9B0',
								'sm_dark_primary'    => '#000000',
								'sm_dark_secondary'  => '#000000',
								'sm_dark_tertiary'  => '#A3A3A1',
								'sm_light_primary'   => '#FFFFFF',
								'sm_light_secondary' => '#FFFFFF',
							),
						),
						

						
						'royal'  => array(
							'label'   => __( 'Royal', 'customify' ),
							'options' => array(
								'sm_color_primary'              => '#8eb2c5',
								'sm_dark_primary'            => '#725c92',
								'sm_dark_secondary'             => '#6f8089',
								'sm_light_primary'   => '#615375',
								'sm_light_secondary' => '#715375',
							),
						),
						'lovely' => array(
							'label'   => __( 'Lovely', 'customify' ),
							'options' => array(
								'sm_color_primary'              => '#2E6171',
								'sm_dark_primary'            => '#556F7A',
								'sm_dark_secondary'             => '#798086',
								'sm_light_primary'   => '#B79FAD',
								'sm_light_secondary' => '#D4AFCD',
							),
						),
						'queen'  => array(
							'label'   => __( 'Queen', 'customify' ),
							'options' => array(
								'sm_color_primary'              => '#7A918D',
								'sm_dark_primary'            => '#93B1A7',
								'sm_dark_secondary'             => '#99C2A2',
								'sm_light_primary'   => '#C5EDAC',
								'sm_light_secondary' => '#DBFEB8',
							),
						),
						'carrot' => array(
							'label'   => __( 'Carrot', 'customify' ),
							'options' => array(
								'sm_color_primary'              => '#FCECC9',
								'sm_dark_primary'            => '#FCB0B3',
								'sm_dark_secondary'             => '#F93943',
								'sm_light_primary'   => '#7EB2DD',
								'sm_light_secondary' => '#445E93',
							),
						),
						'adler'  => array(
							'label'   => __( 'Adler', 'customify' ),
							'options' => array(
								'sm_color_primary'              => '#FE4A49',
								'sm_dark_primary'            => '#FED766',
								'sm_dark_secondary'             => '#009FB7',
								'sm_light_primary'   => '#faf9ff',
								'sm_light_secondary' => '#F4F4F8',
							),
						),
						'velvet' => array(
							'label'   => __( 'Velvet', 'customify' ),
							'options' => array(
								'sm_color_primary'              => '#65DEF1',
								'sm_dark_primary'            => '#A8DCD1',
								'sm_dark_secondary'             => '#DCE2C8',
								'sm_light_primary'   => '#F96900',
								'sm_light_secondary' => '#F17F29',
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
			),
		) );

		return $config;
	}
}
add_filter( 'customify_filter_fields', 'mock_style_manager_section', 12, 1 );
