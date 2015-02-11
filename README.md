Customify
========
**A Theme Customizer Booster**

With Customify, you can easily add Fonts, Colors, Live CSS Editor and other options to your theme.

### How to use it?

First you need to install and activate the stable version. This will always be on [wordpress.org](wordpress.org/support/plugin/pixproof)

Now go to ‘Appearance -> Customize’ menu and have fun with the new fields.

### Make your own customizer

So this plugin adds some fields in customizer, no big deal right? How about adding your own customizer fields?

The Customify [$config](#about_config_var) can be filtered by any theme and this is how you do it, include this filter in your theme(probably in functions.php)

```
 // Advice: change this function's name and make sure it is unique
add_filter('customify_filter_fields', 'make_this_function_name_unique' );
function make_this_function_name_unique( $config ) {
	// usually the sections key will be here, but a check won't hurt
	if ( ! isset($config['sections']) ) { 
		$config['sections'] = array();
	}
	// this means that we add a new entry named "theme_added_settings" in the sections area
	$config['sections']['theme_added_settings'] = array(
		'title' => 'Section added dynamically',
		'settings' => array( 
			// this is the field id and it must be unique
			'field_example' => array(
				'type'  => 'color', // there is a list of types below
				'label' => 'Body color', // the label is optional but is nice to have one
				'css' => array(
					// the CSS key is the one which controls the output of this field
					array(
					 	// a CSS selector
						'selector' => '#logo',
						// the CSS property which should be affected by this field
						'property' => 'background-color',
					)
					// repeat this as long as you need
					array(
						'selector' => 'body',
						'property' => 'color',
					)
				)
			)
		)
	);
	
	// when working with filters always return filtered value
	return $config;
}


```

In this function, we filter the $config variable

### About $config variable<a name="about_config_var"></a> ###

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
