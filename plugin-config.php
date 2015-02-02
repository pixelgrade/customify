<?php defined( 'ABSPATH' ) or die;

$basepath = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

$debug = false;
if ( isset( $_GET['debug'] ) && $_GET['debug'] == 'true' ) {
	$debug = true;
}
$debug = true;

return array(
	'plugin-name'           => 'pixcustomify',
	'settings-key'          => 'pixcustomify_settings',
	'textdomain'            => 'pixcustomify_txtd',
	'template-paths'        => array(
		$basepath . 'core/views/form-partials/',
		$basepath . 'views/form-partials/',
	),
	'fields'                => array(
		'hiddens' => include 'settings/hiddens' . EXT,
		'general' => include 'settings/general' . EXT,
	),
	'processor'             => array(
		// callback signature: (array $input, PixtypesProcessor $processor)
		'preupdate'  => array(
			// callbacks to run before update process
			// cleanup and validation has been performed on data
		),
		'postupdate' => array(
			'save_settings'
		),
	),
	'cleanup'               => array(
		'switch' => array( 'switch_not_available' ),
	),
	'checks'                => array(
		'counter' => array( 'is_numeric', 'not_empty' ),
	),
	'errors'                => array(
		'not_empty' => __( 'Invalid Value.', pixcustomify::textdomain() ),
	),
	'callbacks'             => array(
		'save_settings' => 'save_customizer_plugin_settings'
	),
	// shows exception traces on error
	'debug'                 => $debug,

	/**
	 * DEFAULTS - The default plugin options
	 */
	'default_options' => array(
		'opt-name' => 'customify_defaults',		
		'sections' => array(

			/**
			 * COLORS - This section will handle different elements colors (eg. links, headings)
			 */
			'colors_section' => array(
				'title'    => __( 'Colors', 'hive_txtd' ),
				'options' => array(
					'links_color'   => array(
						'type'      => 'color',
						'label'     => __( 'Links Color', 'hive_txtd' ),
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
						'label'     => __( 'Headings Color', 'hive_txtd' ),
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
						'label'     => __( 'Body Color', 'hive_txtd' ),
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
				'title'    => __( 'Fonts', 'hive_txtd' ),
				'options' => array(
					'headings_font' => array(
						'type'     => 'typography',
						'label'    => __( 'Headings', 'hive_txtd' ),
						'default'  => 'Playfair Display", serif',
						'selector' => '.site-title a, h1, h2, h3, h4, h5, h6,
										h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
										.widget-title',
						'font_weight' => true,
						'subsets' => true,
						'recommended' => array(
							'ABeeZee',
							'Quattrocento',
							'Yellowtail',
							'Vollkorn',
							'Unkempt'
						)
					),
					'body_font'     => array(
						'type'    => 'typography',
						'label'   => __( 'Body Text', 'hive_txtd' ),
						'default' => '"Droid Serif", serif',
						'selector' => 'html body',
						'load_all_weigts' => true,
					)
				)
			),

			/**
			 * BACKGROUNDS - This section will handle different elements colors (eg. links, headings)
			 */
			'backgrounds_section' => array(
				'title'    => __( 'Backgrounds', 'hive_txtd' ),
				'options' => array(
					'page_background'   => array(
						'type'      => 'color',
						'label'     => __( 'Page Background', 'hive_txtd' ),
						'live' => true,
						'default'   => '#ffffff',
						'css'  => array(
							array(
								'property'     => 'background',
								'selector' => 'body, .site',

							),
						)
					),
				)
			),

		)
	)

); # config
