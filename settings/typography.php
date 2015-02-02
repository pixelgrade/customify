<?php
//not used yet - moved them to a per gallery option

return array(
	'type'    => 'postbox',
	'label'   => 'Typography Settings',
	'options' => array(
		'typography'   => array(
			'label'          => __( 'Enable Typography Options', 'pixcustomify_txtd' ),
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
					'label'   => __( 'Use Standard fonts:', 'pixcustomify_txtd' ),
					'desc'    => __( 'Would you like them?', 'pixcustomify_txtd' ),
					'default' => true,
					'type'    => 'switch',
				),
				'typography_google_fonts' => array(
					'name'    => 'typography_google_fonts',
					'label'   => __( 'Use Google fonts:', 'pixcustomify_txtd' ),
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
							'desc'    => __( 'You can chose to see the google fonts in groups', 'pixcustomify_txtd' ),
							'default' => true,
							'type'    => 'switch',
						),
					)
				)
			)
		)
	)
); # config