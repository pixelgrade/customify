Customify
========

A Theme Customizer Booster

How to filter the config?

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
//				'transport' => 'postMessage',
				'live_css' => array(
					array(
						'selector' => 'body',
//						'media' => 'screen and (min-width: 1600px)',
						'rule' => 'background-color',
//						'offset' => array(
//							'from_setting' => 'the_select'
//						),
//						'value_template' => array(
//							'webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(50%, ##replace_here##), color-stop(100%, #464a4d))',
//							'-webkit-linear-gradient(##replace_here## 50%, #464a4d );',
//						),
					)
				)
			),
			'left_white_space' => array(
				'type'  => 'range',
				'label' => 'left white space',
				'desc'  => 'ceva ceva pe aici',
//				'transport' => 'postMessage',
				'input_attrs' => array(
					'min'   => 10,
					'max'   => 700,
					'step'  => 1,
					'class' => 'my-custom-class',
					'style' => 'color: #0a0',
				),
				'default' => 430,
				'live_css' => array(
					array(
						'selector' => 'html body:before',
						'media' => 'screen and (min-width: 700px)',
						'rule' => 'width',
//						'offset' => array(
//							'from_setting' => 'the_select'
//						),
						'unit' => 'px',
//						'opposite' => false,
						'callback_filter' => 'this_setting_can_call_this_function'
					)
				)
			)
		)
	);

	return $settings;
}

add_filter('customify_filter_fields', 'add_customify_settings', 10, 1 );

function this_setting_can_call_this_function( $value, $selector, $rule, $unit ) {

	$this_rule_output = $selector . ' { '. $rule .': '. ( $value * 2 ) . $unit . "; } \n";

	return $this_rule_output;
}
