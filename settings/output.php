<?php


$output_settings = array(
	'type'    => 'postbox',
	'label'   => 'Output Settings',
	'options' => array(
		'style_resources_location' => array(
			'name'    => 'style_resources_location',
			'label'   => __( 'Styles location:', 'customify_txtd' ),
			'desc'    => __( 'Here you can decide where to put your style output, in header or footer', 'customify_txtd' ),
			'default' => 'wp_footer',
			'type'    => 'select',
			'options' => array(
				'wp_head'    => __( "In header (just before the head tag)", 'customify_txtd' ),
				'wp_footer' => __( "Footer (just before the end of the body tag)", 'customify_txtd' ),
			),
		),
//		'script_resources_location' => array(
//			'name'    => 'script_resources_location',
//			'label'   => __( 'Script location:', 'customify_txtd' ),
//			'desc'    => __( 'Here you can decide where to put your scripts output, in header or footer', 'customify_txtd' ),
//			'default' => 'wp_footer',
//			'type'    => 'select',
//			'options' => array(
//				'wp_head'    => __( 'In <head> (just before </head>', 'customify_txtd' ),
//				'wp_footer' => __( 'Footer (just before </body>)', 'customify_txtd' ),
//			)
//		)
	)
); # config

return $output_settings;