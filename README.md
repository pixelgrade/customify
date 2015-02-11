Customify
========
**A Theme Customizer Booster**

With Customify you can easily add Fonts, Colors, Live CSS Editor and other options to your theme.

### How to use it?

First you need to install and activate the stable version. This will always be on [wordpress.org](wordpress.org/support/plugin/pixproof)

Now go to ‘Appearance -> Customize’ menu and have fun with the new fields.

### Make your own customizer

So this plugin adds some fields in customizer, no big deal right? How about adding your own customizer fields?

The Customify [$config](#about_config_var) can be filtered by any theme and this is how you do it, include this filter in your theme(probably in functions.php)

```

function add_customify_settings( $config ) { // change this function's name and make sure it is unique

	if ( ! isset($config['sections']) ) { // usually the sections key will be there,but a check won't hurt
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

```

In this function we filter the $config variable

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
