<?php
//not used yet - moved them to a per gallery option

return array(
	'type'    => 'postbox',
	'label'   => 'CSS Editor',
	'options' => array(
		'css_editor'   => array(
			'label'          => __( 'Enable CSS Editor', 'customify' ),
			'default'        => true,
			'type'           => 'switch',
			'show_group'     => 'css_editor_group',
			'display_option' => true
		),

		'css_editor_group' => array(
			'type'    => 'group',
			'options' => array(
				'css_editor_use_ace' => array(
					'name'    => 'typography_standard_fonts',
					'label'   => __( 'Use Ace Editor?', 'customify' ),
					'default' => true,
					'type'    => 'switch',
				)
			)
		)
	)
); # config