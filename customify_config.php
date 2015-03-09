<?php

/**
 * This filter is used to change the Customizer Options
 * You can also copy this function inside your functions.php
 * file but DO NOT FORGET TO CHANGE ITS NAME
 *
 * @param $config array This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'
 * @return $config
 */

if ( ! function_exists('add_customify_base_options') ) {

	function add_customify_base_options( $config ) {

		$config['opt-name'] = 'customify_defaults';

		$config['sections'] = array(
			/**
			 * Presets - This section will handle other options
			 */
			'presets_section' => array(
				'title'    => __( 'Presets', 'customify_txtd' ),
				'options' => array(
					'theme_style'   => array(
						'type'      => 'preset',
						'label'     => __( 'Theme Style', 'customify_txtd' ),
						'default'   => 'default',
						'choices'  => array(
							'default' => array(
								'label' => __( 'Default', 'customify_txtd' ),
								'options' => array(
									'links_color' => '#6c6e70',
									'headings_color' => '#0aa0d9',
									'body_color' => '#2d3033'
								)
							),
							'dark' => array(
								'label' => __( 'Dark', 'customify_txtd' ),
								'options' => array(
									'links_color' => '#000000',
									'headings_color' => '#000000',
									'body_color' => '#000000'
								)
							),
							'light' => array(
								'label' => __( 'Light', 'customify_txtd' ),
								'options' => array(
									'links_color' => '#ffffff',
									'headings_color' => '#ffffff',
									'body_color' => '#ffffff'
								)
							)
						)
					),
					'theme_style2'   => array(
						'type'      => 'preset',
						'label'     => __( 'Theme Style With Colors', 'customify_txtd' ),
						'default'   => 'default',
						'choices_type' => 'radio',
						'choices'  => array(
							'blue' => array(
								'label' => __( 'Blue', 'customify_txtd' ),
								'color' => 'blue',
								'options' => array(
									'links_color' => '#6c6e70',
									'headings_color' => '#0aa0d9',
									'body_color' => '#2d3033',
									'headings_font' => 'Unlock',
									'body_font' => 'Oswald'
								)
							),
							'red' => array(
								'label' => __( 'Red', 'customify_txtd' ),
								'color' => 'red',
								'options' => array(
									'links_color' => 'red',
									'headings_color' => 'red',
									'body_color' => 'red',
									'headings_font' => 'Exo',
									'body_font' => 'Pacifico'
								)
							),
							'pink' => array(
								'label' => __( 'Pink', 'customify_txtd' ),
								'color' => 'pink',
								'options' => array(
									'links_color' => 'pink',
									'headings_color' => 'pink',
									'body_color' => 'pink',
									'headings_font' => 'Pompiere',
									'body_font' => 'Oswald'
								)
							),
							'brown' => array(
								'label' => __( 'Bbrown', 'customify_txtd' ),
								'color' => 'brown',
								'options' => array(
									'links_color' => 'brown',
									'headings_color' => 'brown',
									'body_color' => 'brown',
									'headings_font' => 'Kreon',
									'body_font' => 'Ubuntu'
								)
							)
						)
					),
				)
			),

			/**
			 * COLORS - This section will handle different elements colors (eg. links, headings)
			 */
			'colors_section' => array(
				'title'    => __( 'Colors', 'customify_txtd' ),
				'options' => array(
					'links_color'   => array(
						'type'      => 'color',
						'label'     => __( 'Links Color', 'customify_txtd' ),
						'live' => true,
						'default'   => '#6c6e70',
						'css'  => array(
							array(
								'property'     => 'color',
								'selector' => 'a, .entry-meta a',
							),
						)
					),
					'headings_color' => array(
						'type'      => 'color',
						'label'     => __( 'Headings Color', 'customify_txtd' ),
						'live' => true,
						'default'   => '#0aa0d9',
						'css'  => array(
							array(
								'property'     => 'color',
								'selector' => '.site-title a, h1, h2, h3, h4, h5, h6,
												h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
												.widget-title,
												a:hover, .entry-meta a:hover'
							)
						)
					),
					'body_color'     => array(
						'type'      => 'color',
						'label'     => __( 'Body Color', 'customify_txtd' ),
						'live' => true,
						'default'   => '#2d3033',
						'css'  => array(
							array(
								'selector' => 'body',
								'property'     => 'color'
							)
						)
					)
				)
			),

			/**
			 * FONTS - This section will handle different elements fonts (eg. headings, body)
			 */
			'typography_section' => array(
				'title'    => __( 'Fonts', 'customify_txtd' ),
				'options' => array(
					'headings_font' => array(
						'type'     => 'typography',
						'label'    => __( 'Headings', 'customify_txtd' ),
						'default'  => 'Playfair Display", serif',
						'selector' => '.site-title a, h1, h2, h3, h4, h5, h6,
										h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
										.widget-title',
						'font_weight' => true,
						'subsets' => true,
						'recommended' => array(
							'Playfair Display',
							'Oswald',
							'Lato',
							'Open Sans',
							'Exo',
							'PT Sans',
							'Ubuntu',
							'Vollkorn',
							'Lora',
							'Arvo',
							'Josefin Slab',
							'Crete Round',
							'Kreon',
							'Bubblegum Sans',
							'The Girl Next Door',
							'Pacifico',
							'Handlee',
							'Satify',
							'Pompiere'
						)
					),
					'body_font'     => array(
						'type'    => 'typography',
						'label'   => __( 'Body Text', 'customify_txtd' ),
						'default' => 'Lato',
						'selector' => 'html body',
						'load_all_weights' => true,
						'recommended' => array(
							'Lato',
							'Open Sans',
							'PT Sans',
							'Cabin',
							'Gentium Book Basic',
							'PT Serif',
							'Droid Serif'
						)
					)
				)
			),

			/**
			 * BACKGROUNDS - This section will handle different elements colors (eg. links, headings)
			 */
			'backgrounds_section' => array(
				'title'    => __( 'Backgrounds', 'customify_txtd' ),
				'options' => array(
					'page_background'   => array(
						'type'      => 'color',
						'label'     => __( 'Page Background', 'customify_txtd' ),
						'live' => true,
						'default'   => '#ffffff',
						'css'  => array(
							array(
								'property'     => 'background',
								'selector' => 'body, .site',
							)
						)
					),
				)
			),
			/**
			 * LAYOUTS - This section will handle different elements colors (eg. links, headings)
			 */
			'layout_options' => array(
				'title'    => __( 'Layout', 'customify_txtd' ),
				'options' => array(
					'site_title_size' => array(
						'type'  => 'range',
						'label' => 'Site Title Size',
						'live' => true,
						'input_attrs' => array(
							'min'   => 24,
							'max'   => 100,
							'step'  => 1,
							'data-preview' => true
						),
						'default' => 24,
						'css' => array(
							array(
								'property' => 'font-size',
								'selector' => '.site-title',
								'media' => 'screen and (min-width: 1000px)',
								'unit' => 'px',
							)
						)
					),
					'page_content_spacing' => array(
						'type'  => 'range',
						'label' => 'Page Content Spacing',
						'live' => true,
						'input_attrs' => array(
							'min'   => 0,
							'max'   => 100,
							'step'  => 1,
						),
						'default' => 18,
						'css' => array(
							array(
								'property' => 'padding',
								'selector' => '.site-content',
								'media' => 'screen and (min-width: 1000px)',
								'unit' => 'px',
							)
						)
					)
				)
			)
		);

		/**
		 * A self explanatory example of panels
		$config['panels'] = array(
			'panel_id' => array(
				'title'    => __( 'Panel Title', 'customify_txtd' ),
				'sections' => array(
					'title'    => __( 'Section Title', 'customify_txtd' ),
					'options' => array(
						'setting_id'   => array(
							'type'      => 'color',
							'label'     => __( 'Label', 'customify_txtd' ),
							'live' => true, // or false
							'default'   => '#6c6e70',
							'css'  => array(
								array(
									'property'     => 'color',
									'selector' => 'a, .entry-meta a',
								),
							)
						),
					)
				)
			)
		);
		 *
		 **/

		return $config;
	}
}


add_filter( 'customify_filter_fields', 'add_customify_base_options', 10, 1 );
