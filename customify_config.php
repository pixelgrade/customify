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
				'title'    => __( 'Style Presets', 'customify' ),
				'options' => array(
					'theme_style'   => array(
						'type'      => 'preset',
						'label'     => __( 'Select a style:', 'customify' ),
						'desc' => __( 'Conveniently change the design of your site with built-in style presets. Easy as pie.', 'customify' ),
						'default'   => 'royal',
						'choices_type' => 'awesome',
						'choices'  => array(
							'royal' => array(
								'label' => __( 'Royal', 'customify' ),
								'preview' => array(
									'color-text' => '#ffffff',
									'background-card' => '#615375',
									'background-label' => '#46414c',
									'font-main' => 'Abril Fatface',
									'font-alt' => 'PT Serif',
								),
								'options' => array(
									'links_color' => '#8eb2c5',
									'headings_color' => '#725c92',
									'body_color' => '#6f8089',
									'page_background' => '#615375',
									'headings_font' => 'Abril Fatface',
									'body_font' => 'PT Serif',
								)
							),
							'lovely' => array(
								'label' => __( 'Lovely', 'customify' ),
								'preview' => array(
									'color-text' => '#ffffff',
									'background-card' => '#d15c57',
									'background-label' => '#5c374b',
									'font-main' => 'Playfair Display',
									'font-alt' => 'Playfair Display',
								),
								'options' => array(
									'links_color' => '#cc3747',
									'headings_color' => '#d15c57',
									'body_color' => '#5c374b',
									'page_background' => '#d15c57',
									'headings_font' => 'Playfair Display',
									'body_font' => 'Playfair Display',
								)
							),
							'queen' => array(
								'label' => __( 'Queen', 'customify' ),
								'preview' => array(
									'color-text' => '#fbedec',
									'background-card' => '#773347',
									'background-label' => '#41212a',
									'font-main' => 'Cinzel Decorative',
									'font-alt' => 'Gentium Basic',
								),
								'options' => array(
									'links_color' => '#cd8085',
									'headings_color' => '#54323c',
									'body_color' => '#cd8085',
									'page_background' => '#fff',
									'headings_font' => 'Cinzel Decorative',
									'body_font' => 'Gentium Basic',
								)
							),
							'carrot' => array(
								'label' => __( 'Carrot', 'customify' ),
								'preview' => array(
									'color-text' => '#ffffff',
									'background-card' => '#df421d',
									'background-label' => '#85210a',
									'font-main' => 'Oswald',
									'font-alt' => 'PT Sans Narrow',
								),
								'options' => array(
									'links_color' => '#df421d',
									'headings_color' => '#df421d',
									'body_color' => '#7e7e7e',
									'page_background' => '#fff',
									'headings_font' => 'Oswald',
									'body_font' => 'PT Sans Narrow',
								)
							),



							'adler' => array(
								'label' => __( 'Adler', 'customify' ),
								'preview' => array(
									'color-text' => '#fff',
									'background-card' => '#0e364f',
									'background-label' => '#000000',
									'font-main' => 'Permanent Marker',
									'font-alt' => 'Droid Sans Mono',
								),
								'options' => array(
									'links_color' => '#68f3c8',
									'headings_color' => '#0e364f',
									'body_color' => '#45525a',
									'page_background' => '#ffffff',
									'headings_font' => 'Permanent Marker',
									'body_font' => 'Droid Sans Mono'
								)
							),
							'velvet' => array(
								'label' => __( 'Velvet', 'customify' ),
								'preview' => array(
									'color-text' => '#ffffff',
									'background-card' => '#282828',
									'background-label' => '#000000',
									'font-main' => 'Pinyon Script',
									'font-alt' => 'Josefin Sans',
								),
								'options' => array(
									'links_color' => '#000000',
									'headings_color' => '#000000',
									'body_color' => '#000000',
									'page_background' => '#000000',
									'headings_font' => 'Pinyon Script',
									'body_font' => 'Josefin Sans',
								)
							),

						)
					),
				)
			),

			/**
			 * COLORS - This section will handle different elements colors (eg. links, headings)
			 */
			'colors_section' => array(
				'title'    => __( 'Colors', 'customify' ),
				'options' => array(
					'links_color'   => array(
						'type'      => 'color',
						'label'     => __( 'Links Color', 'customify' ),
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
						'label'     => __( 'Headings Color', 'customify' ),
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
						'label'     => __( 'Body Color', 'customify' ),
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
				'title'    => __( 'Fonts', 'customify' ),
				'options' => array(
					'headings_font' => array(
						'type'     => 'typography',
						'label'    => __( 'Headings', 'customify' ),
						'default'  => 'Playfair Display',
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
						'label'   => __( 'Body Text', 'customify' ),
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
				'title'    => __( 'Backgrounds', 'customify' ),
				'options' => array(
					'page_background'   => array(
						'type'      => 'color',
						'label'     => __( 'Page Background', 'customify' ),
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
				'title'    => __( 'Layout', 'customify' ),
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
		 * A self explanatory example of panels **
		 **/
//		$config['panels'] = array(
//			'panel_id' => array(
//				'title'    => __( 'Panel Title', 'customify' ),
//				'sections' => array(
//					'panel_section' => array(
//						'title'    => __( 'Section Title', 'customify' ),
//						'options' => array(
//							'setting_id'   => array(
//								'type'      => 'color',
//								'label'     => __( 'Label', 'customify' ),
//								'live' => true, // or false
//								'default'   => '#6c6e70',
//								'css'  => array(
//									array(
//										'property'     => 'color',
//										'selector' => 'a, .entry-meta a',
//									),
//								)
//							),
//						)
//					)
//				)
//			)
//		);

		return $config;
	}
}


add_filter( 'customify_filter_fields', 'add_customify_base_options' );
