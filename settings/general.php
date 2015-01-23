<?php
//not used yet - moved them to a per gallery option
return array(
	'type'    => 'postbox',
	'label'   => 'General Settings',
	'options' => array(

		'values_store_mod' => array(
			'name'    => 'values_store_mod',
			'label'   => __( 'Store values as:', 'pixcustomify_txtd' ),
			'desc'    => __( 'You can store the values globally so you can use them with other themes or store the as a "theme_mod" which will make an individual set of options only for the current theme', 'pixcustomify_txtd' ),
			'default' => 'option',
			'type'    => 'select',
			'options' => array(
				'option'    => __( 'Option (global options)', 'pixcustomify_txtd' ),
				'theme_mod' => __( 'Theme Mod (per theme options)', 'pixcustomify_txtd' ),
			),
		),

//		'enable_archive_zip_download'   => array(
//			'label'          => __( 'Enable Images Download', 'pixcustomify_txtd' ),
//			'default'        => true,
//			'type'           => 'switch',
//			'show_group'     => 'enable_pixcustomify_gallery_group',
//			'display_option' => true
//		), /* ALL THESE PREFIXED WITH PORTFOLIO SHOULD BE KIDS!! **/
//
//		'enable_pixcustomify_gallery_group' => array(
//			'type'    => 'group',
//			'options' => array(
//				'zip_archive_generation' => array(
//					'name'    => 'zip_archive_generation',
//					'label'   => __( 'The ZIP archive should be generated:', 'pixcustomify_txtd' ),
//					'desc'    => __( 'How the archive file should be generated?', 'pixcustomify_txtd' ),
//					'default' => 'manual',
//					'type'    => 'select',
//					'options' => array(
//						'manual'    => __( 'Manually (uploaded by the gallery owner)', 'pixcustomify_txtd' ),
//						'automatic' => __( 'Automatically (from the selected images)', 'pixcustomify_txtd' ),
//					),
//				),
//			)
//		)
	)
); # config