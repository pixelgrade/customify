Customify
========
**A Theme Customizer Booster**

With Customify, you can easily add Fonts, Colors, Live CSS Editor and other options to your theme.

### How to use it?

First you need to install and activate the stable version. This will always be on [wordpress.org](https://wordpress.org/plugins/customify/)

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

The Customify plugin also create's its own defaults this way. You can see that in `customify/customify_config.php`
Personally I like to simply copy this file in my child theme and include it in `functions.php` with

`require 'customify_config.php'`

And after that the sky is the limit, I can style any elements or group of elements in customizer.

The intro is over let's get to some advanced stuff.

# Advanced Things

### The $config variable<a name="about_config_var"></a> ###

This is the array which is processed by the `customify_filter_fields` filter and it contains:
 *  'sections' an array with sections(each section holds an array with fields)
 *  'panels' an array of panels( each panel holds an array with sections)
 *  'opt-name' the option key name which will hold all these options

### Media queries<a name="media_query_config"></a> 

The `css` configuration can also hold a `media` parameter which will make the output of the CSS property, to wrap in the specified media query, for example:

```
'site_title_size' => array(
	'type'  => 'range',
	'label' => 'Site Title Size',
	'default' => 24,
	'css' => array(
		array(
			'property' => 'font-size',
			'selector' => '.site-title',
			'media' => 'screen and (min-width: 1000px)'
		)
	)
)
```

This will make the property take effect only on screens larger than 1000px, because on mobile devices you may not want a bigger logo.

### Field callbacks<a name="callback_example"></a> 

Each field can take a 'callback_filter' parameter.This should be a function name which should be called when a field is changed.

For example let's take this range field :
```
'sidebar_width' => array(
	'type'  => 'range',
	'label' => 'Sidebar width',
	'input_attrs' => array(
		'min'   => 60,
		'max'   => 320,
		'step'  => 60,
	),
	'css' => array(
		array(
			'selector' => 'span.col',
			'property' => 'width',
			'unit' => 'px',
			'callback_filter' => 'this_setting_can_call_this_function'
		)
	)
)

```

Now let's create a callback which multiplies the effect of this css property
Let's say that we want the sidebar to grow faster in length and double its value when the slider is changed

```
function this_setting_can_call_this_function( $value, $selector, $property, $unit ) {

	$this_property_output = $selector . ' { '. $property .': '. ( $value * 2 ) . $unit . "; } \n";

	return $this_property_output;
}

```

Fields  | [Live Preview Support!](#live_preview_support)
------------- | -------------
Text  | No
Textarea | No
Color | Yes
Range | Yes
Typography | No
Select | No
Radio | No
Checkbox | No
Upload | No
Image | No
Date | No
Pages select | No

### Live Preview Support<a name="live_preview_support"></a>

There are a few fields which support this feature for now, but those are awesome.These fields are capable to update the previewer iframe without refreshing the iframe, the preview should be instant.

This is recommended for color fields because you won't need to stop drag-and-dropping the color select to see what color would be better.
