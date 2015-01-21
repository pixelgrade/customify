<?php defined( 'ABSPATH' ) or die;

$basepath = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

$debug = false;
if ( isset( $_GET['debug'] ) && $_GET['debug'] == 'true' ) {
	$debug = true;
}
$debug   = true;

return array (
	'plugin-name'    => 'pixcustomify',
	'settings-key'   => 'pixcustomify_settings',
	'textdomain'     => 'pixcustomify_txtd',
	'template-paths' => array (
		$basepath . 'core/views/form-partials/',
		$basepath . 'views/form-partials/',
	),
	'fields'         => array (
		'hiddens' => include 'settings/hiddens' . EXT,
		'general' => include 'settings/general' . EXT,
	),
	'processor'      => array (
		// callback signature: (array $input, PixtypesProcessor $processor)
		'preupdate'  => array (
			// callbacks to run before update process
			// cleanup and validation has been performed on data
		),
		'postupdate' => array (
			'save_settings'
		),
	),
	'cleanup'        => array (
		'switch' => array( 'switch_not_available' ),
	),
	'checks'         => array (
		'counter' => array( 'is_numeric', 'not_empty' ),
	),
	'errors'         => array (
		'not_empty' => __( 'Invalid Value.', pixcustomify::textdomain() ),
	),
	'callbacks'      => array (
		'save_settings' => 'save_customizer_plugin_settings'
	),
	// shows exception traces on error
	'debug'          => $debug,

	'pixcustomify_settings' => array(
		'opt-name' => 'customzier_test',
		'panels' => array(
			'panel_1' => array(
				'title' => 'Standard Fields',
				'sections' => array(
					'section_11' => array(
						'title' => 'Texts',
						'settings' => array(
							'the_text' => array(
								'type' => 'text',
								'label' => 'The text option',
								'desc' => 'ceva ceva pe aici'
							),
							'the_textarea' => array(
								'type' => 'textarea',
								'label' => 'Text long',
								'desc' => 'ceva ceva pe aici'
							)
						)
					),
					'section_12' => array(
						'title' => 'Coolors',
						'settings' => array(
							'main_color' => array(
								'type' => 'color',
								'label' => 'Main color',
								'desc' => 'ceva ceva pe aici'
							)
						)
					),
					'section_13' => array(
						'title' => 'Selects',
						'settings' => array(
							'the_select' => array(
								'type' => 'select',
								'label' => 'Un select',
								'desc' => 'Un select are nevoie neaparat de choices',
								'choices' => array(
									'1' => 'Unu',
									'2' => 'Doi'
								)
							),
							'the_radio' => array(
								'type' => 'radio',
								'label' => 'Un select',
								'desc' => 'Un select are nevoie neaparat de choices',
								'choices' => array(
									'1' => 'Unu',
									'2' => 'Doi',
									'3' => 'Tri'
								)
							),
							'the_range' => array(
								'type' => 'range',
								'label' => __( 'Range' ),
								'description' => __( 'This is the range control description.' ),
								'input_attrs' => array(
									'min' => 0,
									'max' => 10,
									'step' => 2,
								),
							)
						)
					),
					'section_14' => array(
						'title' => 'Uploaders',
						'settings' => array(
							'an_upload' => array(
								'type' => 'upload',
								'label' => 'Un upload long',
								'desc' => 'ceva ceva pe aici'
							),
							'an_image' => array(
								'type' => 'image',
								'label' => 'Imagine',
								'desc' => 'ceva ceva pe aici'
							)
						)
					),
					'section_15' => array(
						'title' => 'Surpriza',
						'settings' => array(
							'the_range_is_mine' => array(
								'type' => 'range',
								'label' => __( 'Range' ),
								'description' => __( 'This is the range control description.' ),
								'input_attrs' => array(
									'min' => 1,
									'max' => 30,
									'step' => 1,
									'class' => 'my-custom-class',
									'style' => 'color: #0a0',
								),
							),
							'un_url' => array(
								'type' => 'url',
								'label' => 'Url',
								'desc' => 'ceva ceva pe aici'
							),
							'odata' => array(
								'type' => 'date',
								'label' => 'O data',
								'desc' => 'ceva ceva pe aici'
							),
							'untimp' => array(
								'type' => 'time',
								'label' => 'Un timp',
								'desc' => 'ceva ceva pe aici'
							),
							'odatasiuntimp' => array(
								'type' => 'datetime',
								'label' => 'Odata si Un timp',
								'desc' => 'ceva ceva pe aici'
							),
							'o_saptamana' => array(
								'type' => 'week',
								'label' => 'O saptamana',
								'desc' => 'ceva ceva pe aici'
							),
							'o_cautare' => array(
								'type' => 'search',
								'label' => 'O cautare',
								'desc' => 'ceva ceva pe aici'
							)
						)
					)
				)
			),
			'panel_2' => array(
				'title' => 'Custom made',
				'sections' => array(

				)
			),
		),
		// these settings wont be included inside a panel
		'sections' => array(
			'outside_settings' => array(

			)
		)
	)

); # config
