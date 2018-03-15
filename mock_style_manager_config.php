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
						'julia'  => array(
							'label'   => __( 'Julia', 'customify' ),
							'options' => array(
								'primary_color'              => '#161616',
								'secondary_color'            => '#383c50',
								'tertiary_color'             => '#383c50',
								'background_primary_color'   => '#f7f6f5',
								'background_secondary_color' => '#e7f2f8',
							),
						),
						'royal'  => array(
							'label'   => __( 'Royal', 'customify' ),
							'options' => array(
								'primary_color'              => '#8eb2c5',
								'secondary_color'            => '#725c92',
								'tertiary_color'             => '#6f8089',
								'background_primary_color'   => '#615375',
								'background_secondary_color' => '#715375',
							),
						),
						'lovely' => array(
							'label'   => __( 'Lovely', 'customify' ),
							'options' => array(
								'primary_color'              => '#2E6171',
								'secondary_color'            => '#556F7A',
								'tertiary_color'             => '#798086',
								'background_primary_color'   => '#B79FAD',
								'background_secondary_color' => '#D4AFCD',
							),
						),
						'queen'  => array(
							'label'   => __( 'Queen', 'customify' ),
							'options' => array(
								'primary_color'              => '#7A918D',
								'secondary_color'            => '#93B1A7',
								'tertiary_color'             => '#99C2A2',
								'background_primary_color'   => '#C5EDAC',
								'background_secondary_color' => '#DBFEB8',
							),
						),
						'carrot' => array(
							'label'   => __( 'Carrot', 'customify' ),
							'options' => array(
								'primary_color'              => '#FCECC9',
								'secondary_color'            => '#FCB0B3',
								'tertiary_color'             => '#F93943',
								'background_primary_color'   => '#7EB2DD',
								'background_secondary_color' => '#445E93',
							),
						),
						'adler'  => array(
							'label'   => __( 'Adler', 'customify' ),
							'options' => array(
								'primary_color'              => '#FE4A49',
								'secondary_color'            => '#FED766',
								'tertiary_color'             => '#009FB7',
								'background_primary_color'   => '#E6E6EA',
								'background_secondary_color' => '#F4F4F8',
							),
						),
						'velvet' => array(
							'label'   => __( 'Velvet', 'customify' ),
							'options' => array(
								'primary_color'              => '#65DEF1',
								'secondary_color'            => '#A8DCD1',
								'tertiary_color'             => '#DCE2C8',
								'background_primary_color'   => '#F96900',
								'background_secondary_color' => '#F17F29',
							),
						),

					),
				),
				'primary_color'              => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'pxg_primary_color',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'label'            => esc_html__( 'Primary Color', 'customify' ),
					'default'          => '#8224e3',
					'connected_fields' => array(),
				),
				'secondary_color'            => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'pxg_secondary_color',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'label'            => esc_html__( 'Secondary Color', 'customify' ),
					'default'          => '#81d742',
					'connected_fields' => array(),
				),
				'tertiary_color'             => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'pxg_tertiary_color',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'label'            => esc_html__( 'Tertiary Color', 'customify' ),
					'default'          => '#eeee22',
					'connected_fields' => array(),
				),
				'background_primary_color'   => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'pxg_background_primary_color',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'label'            => esc_html__( 'Background Primary Color', 'customify' ),
					'default'          => '#dd3333',
					'connected_fields' => array(),
				),
				'background_secondary_color' => array(
					'type'             => 'color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'pxg_background_secondary_color',
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
