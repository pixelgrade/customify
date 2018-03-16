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
				'color_palette'              => array(
					'type'         => 'preset',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type' => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'   => 'pxg_color_palette',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'         => true,
					'label'        => __( 'Select a color palette:', 'customify' ),
					'desc'         => __( 'Conveniently change the design of your site with color palettes. Easy as pie.', 'customify' ),
					'default'      => 'royal',
					'choices_type' => 'radio',
					'choices'      => array(
						'hive'  => array(
							'label'   => __( 'Hive', 'customify' ),
							'options' => array(
								'master_primary_color'              => '#171617',
								'master_secondary_color'            => '#afafaf',
								'master_tertiary_color'             => '#ffeb00',
								'master_quaternary_color'           => '#81d742',
								'master_background_primary_color'   => '#FFFFFF',
								'master_background_secondary_color' => '#FFFFFF',
							),
						),
						'julia'  => array(
							'label'   => __( 'Julia', 'customify' ),
							'options' => array(
								'master_primary_color'              => '#161616',
								'master_secondary_color'            => '#383c50',
								'master_tertiary_color'             => '#383c50',
								'master_quaternary_color'           => '#81d742',
								'master_background_primary_color'   => '#f7f6f5',
								'master_background_secondary_color' => '#e7f2f8',
							),
						),
						'vasco'  => array(
							'label'   => __( 'Vasco', 'customify' ),
							'options' => array(
								'master_primary_color'              => '#F59828',
								'master_secondary_color'            => '#38C3C8',
								'master_tertiary_color'             => '#2B3D39',
								'master_quaternary_color'           => '#81d742',
								'master_background_primary_color'   => '#F5F6F1',
								'master_background_secondary_color' => '#FFFFFF',
							),
						),
						'royal'  => array(
							'label'   => __( 'Royal', 'customify' ),
							'options' => array(
								'master_primary_color'              => '#8eb2c5',
								'master_secondary_color'            => '#725c92',
								'master_tertiary_color'             => '#6f8089',
								'master_quaternary_color'           => '#81d742',
								'master_background_primary_color'   => '#615375',
								'master_background_secondary_color' => '#715375',
							),
						),
						'lovely' => array(
							'label'   => __( 'Lovely', 'customify' ),
							'options' => array(
								'master_primary_color'              => '#2E6171',
								'master_secondary_color'            => '#556F7A',
								'master_tertiary_color'             => '#798086',
								'master_quaternary_color'           => '#81d742',
								'master_background_primary_color'   => '#B79FAD',
								'master_background_secondary_color' => '#D4AFCD',
							),
						),
						'queen'  => array(
							'label'   => __( 'Queen', 'customify' ),
							'options' => array(
								'master_primary_color'              => '#7A918D',
								'master_secondary_color'            => '#93B1A7',
								'master_tertiary_color'             => '#99C2A2',
								'master_quaternary_color'           => '#81d742',
								'master_background_primary_color'   => '#C5EDAC',
								'master_background_secondary_color' => '#DBFEB8',
							),
						),
						'carrot' => array(
							'label'   => __( 'Carrot', 'customify' ),
							'options' => array(
								'master_primary_color'              => '#FCECC9',
								'master_secondary_color'            => '#FCB0B3',
								'master_tertiary_color'             => '#F93943',
								'master_quaternary_color'           => '#81d742',
								'master_background_primary_color'   => '#7EB2DD',
								'master_background_secondary_color' => '#445E93',
							),
						),
						'adler'  => array(
							'label'   => __( 'Adler', 'customify' ),
							'options' => array(
								'master_primary_color'              => '#FE4A49',
								'master_secondary_color'            => '#FED766',
								'master_tertiary_color'             => '#009FB7',
								'master_quaternary_color'           => '#81d742',
								'master_background_primary_color'   => '#E6E6EA',
								'master_background_secondary_color' => '#F4F4F8',
							),
						),
						'velvet' => array(
							'label'   => __( 'Velvet', 'customify' ),
							'options' => array(
								'master_primary_color'              => '#65DEF1',
								'master_secondary_color'            => '#A8DCD1',
								'master_tertiary_color'             => '#DCE2C8',
								'master_quaternary_color'           => '#81d742',
								'master_background_primary_color'   => '#F96900',
								'master_background_secondary_color' => '#F17F29',
							),
						),
						'chroma' => array(
							'label'   => __( 'Chroma', 'customify' ),
							'options' => array(
								'master_primary_color'              => '#81d742',
								'master_secondary_color'            => '#81d742',
								'master_tertiary_color'             => '#81d742',
								'master_quaternary_color'           => '#81d742',
								'master_background_primary_color'   => '#81d742',
								'master_background_secondary_color' => '#81d742',
							),
						),
					),
				),
				'master_primary_color'              => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'master_primary_color',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'label'            => esc_html__( 'Primary Color', 'customify' ),
					'default'          => '#8224e3',
					'connected_fields' => array(),
				),
				'master_secondary_color'            => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'master_secondary_color',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'label'            => esc_html__( 'Secondary Color', 'customify' ),
					'default'          => '#81d742',
					'connected_fields' => array(),
				),
				'master_tertiary_color'             => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'master_tertiary_color',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'label'            => esc_html__( 'Tertiary Color', 'customify' ),
					'default'          => '#eeee22',
					'connected_fields' => array(),
				),
				'master_quaternary_color'             => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'master_quaternary_color',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'label'            => esc_html__( 'Quaternary Color', 'customify' ),
					'default'          => '#eeee22',
					'connected_fields' => array(),
				),
				'master_background_primary_color'   => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'master_background_primary_color',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'label'            => esc_html__( 'Background Primary Color', 'customify' ),
					'default'          => '#dd3333',
					'connected_fields' => array(),
				),
				'master_background_secondary_color' => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'master_background_secondary_color',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'label'            => esc_html__( 'Background Secondary Color', 'customify' ),
					'default'          => '#ffffff',
					'connected_fields' => array(),
				),
			),
		) );

		return $config;
	}
}
add_filter( 'customify_filter_fields', 'mock_style_manager_section', 12, 1 );
