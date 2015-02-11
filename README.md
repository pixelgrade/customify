Customify
========
**A Theme Customizer Booster**

If you want to add your own fields you can always filter the fields list like this.

How to filter the config? Include this filter in your theme(probably in functions.php)


function add_customify_settings( [$config](#about_config_var) ) {

	if ( ! isset($config['sections']) ) { // read more about [sections](#about_sections)
		$config['sections'] = array();
	}

	$config['sections']['theme_added_settings'] = array(
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

	return $config;
}
add_filter('customify_filter_fields', 'add_customify_settings', 10, 1 );

### About $config varaible<a name="about_config_var"></a> ###

 The $config array holds 3 important keys:
 *  'sections' an array with sections(each section holds an array with fields)
 *  'panels' an array of panels( each panel holds an array with sections)
 *  'opt-name' the option key name which will hold all these options


### Field callbacks <a name="callback_example"></a> 
```
function this_setting_can_call_this_function( $value, $selector, $property, $unit ) {

	$this_property_output = $selector . ' { '. $property .': '. ( $value * 2 ) . $unit . "; } \n";

	return $this_property_output;
}
```

Fields  | Live Preview Support
------------- | -------------
Text  | No
Textarea  | No
Color | Yes
Range | Yes
Typography | No
