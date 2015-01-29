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

		'disable_default_sections' => array(
			'name'    => 'disable_default_sections',
			'label'   => __( 'Disable sections', 'pixcustomify_txtd' ),
			'desc'    => __( 'You can disable sections', 'pixcustomify_txtd' ),
			'type'    => 'multicheckbox',
			'options' => array(
				'nav'    => __( 'Navigation', 'pixcustomify_txtd' ),
				'static_front_page' => __( 'Front Page', 'pixcustomify_txtd' ),
				'title_tagline'    => __( 'Title', 'pixcustomify_txtd' ),
				'colors' => __( 'Colors', 'pixcustomify_txtd' ),
				'background_image'    => __( 'Background', 'pixcustomify_txtd' ),
				'header_image' => __( 'Header', 'pixcustomify_txtd' ),
				'widgets' => __( 'Widgets', 'pixcustomify_txtd' ),
			),
		),

		'typography'   => array(
			'label'          => __( 'Typography Options', 'pixcustomify_txtd' ),
			'default'        => true,
			'type'           => 'switch',
			'show_group'     => 'typography_group',
			'display_option' => true
		),

		'typography_group' => array(
			'type'    => 'group',
			'options' => array(
				'typography_standard_fonts' => array(
					'name'    => 'typography_standard_fonts',
					'label'   => __( 'Standard fonts:', 'pixcustomify_txtd' ),
					'desc'    => __( 'Would you like them?', 'pixcustomify_txtd' ),
					'default' => true,
					'type'    => 'switch',
				),
				'typography_google_fonts' => array(
					'name'    => 'typography_google_fonts',
					'label'   => __( 'Google fonts:', 'pixcustomify_txtd' ),
					'desc'    => __( 'Would you like them?', 'pixcustomify_txtd' ),
					'default' => true,
					'type'    => 'switch',
					'show_group'     => 'typography_google_fonts_group',
					'display_option' => true
				),
				'typography_google_fonts_group' => array(
					'type'    => 'group',
					'options' => array(
						'typography_group_google_fonts' => array(
							'name'    => 'typography_standard_fonts',
							'label'   => __( 'Group Google fonts:', 'pixcustomify_txtd' ),
							'desc'    => __( 'Would you like them?', 'pixcustomify_txtd' ),
							'default' => true,
							'type'    => 'switch',
						),
					)
				)
			)
		)
	)
); # config