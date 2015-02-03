Customify
========

A Theme Customizer Booster

How to filter the config?
<pre>
function add_customify_settings( $settings ) {

	if ( !isset($settings['sections']) ) {
		$settings['sections'] = array();
	}

	$settings['sections']['theme_added_settings'] = array(
		'title' => 'General Theme Settings',
		'settings' => array(
			'body_color' => array(
				'type'  => 'color',
				'label' => 'Body color',
				'desc'  => 'ceva ceva pe aici',
				'live' => true, // this means the previewer won't refresh
				'css' => array(
					array(
						'selector' => 'body',
						'property' => 'background-color',
					)
				)
			),
			'left_white_space' => array(
				'type'  => 'range',
				'label' => 'left white space',
				'desc'  => 'ceva ceva pe aici',
				'input_attrs' => array(
					'min'   => 10,
					'max'   => 700,
					'step'  => 1,
					'class' => 'my-custom-class',
					'style' => 'color: #0a0',
				),
				'default' => 430,
				'css' => array(
					array(
						'selector' => 'html body:before',
						'media' => 'screen and (min-width: 700px)',
						'property' => 'width',
						'unit' => 'px'
					)
				)
			)
		)
	);

	return $settings;
}

add_filter('customify_filter_fields', 'add_customify_settings', 10, 1 );

function this_setting_can_call_this_function( $value, $selector, $property, $unit ) {

	$this_property_output = $selector . ' { '. $property .': '. ( $value * 2 ) . $unit . "; } \n";

	return $this_property_output;
}

</pre>