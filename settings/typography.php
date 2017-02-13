<?php
//not used yet - moved them to a per gallery option

return array(
	'type'    => 'postbox',
	'label'   => 'Typography Settings',
	'options' => array(
		'typography'   => array(
			'label'          => __( 'Enable Typography Options', 'customify' ),
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
					'label'   => __( 'Use Standard fonts:', 'customify' ),
					'desc'    => __( 'Would you like them?', 'customify' ),
					'default' => true,
					'type'    => 'switch',
				),
				'typography_google_fonts' => array(
					'name'    => 'typography_google_fonts',
					'label'   => __( 'Use Google fonts:', 'customify' ),
					'desc'    => __( 'Would you like them?', 'customify' ),
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
							'label'   => __( 'Group Google fonts:', 'customify' ),
							'desc'    => __( 'You can chose to see the google fonts in groups', 'customify' ),
							'default' => true,
							'type'    => 'switch',
						),
					)
				)
			)
		)
	)
); # config