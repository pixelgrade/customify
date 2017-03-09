Customify [![Build Status](https://travis-ci.org/pixelgrade/customify.svg?branch=wporg)](https://travis-ci.org/pixelgrade/customify) [![Code Climate](https://lima.codeclimate.com/github/pixelgrade/customify/badges/gpa.svg)](https://lima.codeclimate.com/github/pixelgrade/customify) [![Issue Count](https://lima.codeclimate.com/github/pixelgrade/customify/badges/issue_count.svg)](https://lima.codeclimate.com/github/pixelgrade/customify)
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

# List of fields

Fields<a name="list_of_fields"></a>  | [Live Preview Support!](#live_preview_support) | Description
------------- | ------------- | -------------
Text  | Yes [with classes](#live_preview_with_classes) | A simple text input
Textarea | Yes [with classes](#live_preview_with_classes) | A simple text area input
Ace Editor | Yes [with classes](#live_preview_with_classes) | An ace editor that supports plain_text / css / html / javascript / json / markdown
Color | Yes | A simple color picker
Range | Yes | The default html5 range input
Font | Custom Live Preview | A complete font selector with Live Preview, google fonts, filtrable theme fonts and custom callbacks 
Typography | No | This is a font selector but it will be soon depricated, use Font instead
Select | No | The standard HTML select
Radio | No |
Checkbox | No | 
Upload | No | This field allows you to upload a file which you can use it later in front-end
Image | No | This is like the upload field, but it accepts only images 
Date | No | 
Pages select | No | The standard WordPress Page Select
[Select2](https://select2.github.io/) | No | An awesome select 
[Presets](#presets_title) | No | An radio input option to select a group of options (inception style ^^)

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

For example let's take this range field:
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

HTML field | No | A field which allows you to add custom HTML in customizer and hook into it with javascript later ;) 

### Live Preview Support<a name="live_preview_support"></a>

There are a few fields which support this feature for now, but those are awesome.These fields are capable to update the previewer iframe without refreshing the iframe, the preview should be instant.

This is recommended for color fields because you won't need to stop drag-and-dropping the color select to see what color would be better.

**Note<a name="live_preview_with_classes"></a>** 
All the text fields have support for a **live preview** but they require an array of classes instead of the boolean `true` for the `live` parameter.

For example a fields which would provide the copyright text from footer whould be like this:
```
'footer_copyright' => array(
	'type'     => 'text',
	'label'    => 'Footer Copyright'
	'default'  => 'All contents &copy; Pixelgrade 2011-2015'
	'sanitize_callback' => 'wp_kses_post',
	'live' => array( '.copyright-class' )
)
```

For this example the element with the `.copyright-class` class will get the text replaced as soon as the user types a
new text. I bet this is awesome.


### Conditional fields<a name="conditional_fields"></a>

Once with 1.2.3 version we've added support for conditional fields. This means that you can use the `show_if` argument
to display a field only when another field has a certain value.

This [gist](https://gist.github.com/andreilupu/3a71618fb6d2ea2c2b1429544c667cd1) shows how this can be done.

### Presets<a name="presets_title"></a>

Since version 1.1.0 we added support for presets options. With this fields, you can pre-define other options.
Here is and example of how to config this.

```
'theme_style'   => array(
	'type'      => 'preset',
	'label'     => __( 'Select a style:', 'customify' ),
	'desc' => __( 'Conveniently change the design of your site with built-in style presets. Easy as pie.', 'customify' ),
	'default'   => 'silk',
	'choices_type' => 'select',
	'choices'  => array(
		'silk' => array(
			'label' => __( 'Silk', 'customify' ),
			'options' => array(
				'links_color' => '#FAC2A8', //second
				'headings_color' => '#A84469', //main
				'body_color' => '#ffffff', // -
				'headings_font' => 'Playfair Display', //main
				'body_font' => 'Merriweather'
			)
		),
		'red' => array(
			'label' => __( 'Urban', 'customify' ),
			'options' => array(
				'links_color' => 'red',
				'headings_color' => 'red',
				'body_color' => 'red',
				'headings_font' => 'Exo',
				'body_font' => 'Pacifico'
			)
		),
		'black' => array(
			'label' => __( 'Black', 'customify' ),
			'options' => array(
				'links_color' => '#ebebeb',
				'headings_color' => '#333',
				'body_color' => '#989898',
				'headings_font' => 'Arvo',
				'body_font' => 'Lora'
			)
		),
	)
)
```

The upper example will output a select which will change all the fields setted up in the `options` array.

If you don't like the select type, at `choices_type` you can choose between `select`, `button` and an `awesome` radio select which allows you not only change de font-end options but also the preview button style.

Wanna have a preset like this?

![img](https://cloud.githubusercontent.com/assets/1893980/6652930/86b7a1aa-ca88-11e4-8997-ba63be1598d8.png)

Just add this section in your config

```
'presets_section' => array(
	'title'    => __( 'Style Presets', 'customify' ),
	'options' => array(
		'theme_style'   => array(
			'type'      => 'preset',
			'label'     => __( 'Select a style:', 'customify' ),
			'desc' => __( 'Conveniently change the design of your site with built-in style presets. Easy as pie.', 'customify' ),
			'default'   => 'royal',
			'choices_type' => 'awesome',
			'choices'  => array(
				'royal' => array(
					'label' => __( 'Royal', 'customify' ),
					'preview' => array(
						'color-text' => '#ffffff',
						'background-card' => '#615375',
						'background-label' => '#46414c',
						'font-main' => 'Abril Fatface',
						'font-alt' => 'PT Serif',
					),
					'options' => array(
						'links_color' => '#8eb2c5',
						'headings_color' => '#725c92',
						'body_color' => '#6f8089',
						'page_background' => '#615375',
						'headings_font' => 'Abril Fatface',
						'body_font' => 'PT Serif',
					)
				),
				'lovely' => array(
					'label' => __( 'Lovely', 'customify' ),
					'preview' => array(
						'color-text' => '#ffffff',
						'background-card' => '#d15c57',
						'background-label' => '#5c374b',
						'font-main' => 'Playfair Display',
						'font-alt' => 'Playfair Display',
					),
					'options' => array(
						'links_color' => '#cc3747',
						'headings_color' => '#d15c57',
						'body_color' => '#5c374b',
						'page_background' => '#d15c57',
						'headings_font' => 'Playfair Display',
						'body_font' => 'Playfair Display',
					)
				),
				'queen' => array(
					'label' => __( 'Queen', 'customify' ),
					'preview' => array(
						'color-text' => '#fbedec',
						'background-card' => '#773347',
						'background-label' => '#41212a',
						'font-main' => 'Cinzel Decorative',
						'font-alt' => 'Gentium Basic',
					),
					'options' => array(
						'links_color' => '#cd8085',
						'headings_color' => '#54323c',
						'body_color' => '#cd8085',
						'page_background' => '#fff',
						'headings_font' => 'Cinzel Decorative',
						'body_font' => 'Gentium Basic',
					)
				),
				'carrot' => array(
					'label' => __( 'Carrot', 'customify' ),
					'preview' => array(
						'color-text' => '#ffffff',
						'background-card' => '#df421d',
						'background-label' => '#85210a',
						'font-main' => 'Oswald',
						'font-alt' => 'PT Sans Narrow',
					),
					'options' => array(
						'links_color' => '#df421d',
						'headings_color' => '#df421d',
						'body_color' => '#7e7e7e',
						'page_background' => '#fff',
						'headings_font' => 'Oswald',
						'body_font' => 'PT Sans Narrow',
					)
				),
				'adler' => array(
					'label' => __( 'Adler', 'customify' ),
					'preview' => array(
						'color-text' => '#fff',
						'background-card' => '#0e364f',
						'background-label' => '#000000',
						'font-main' => 'Permanent Marker',
						'font-alt' => 'Droid Sans Mono',
					),
					'options' => array(
						'links_color' => '#68f3c8',
						'headings_color' => '#0e364f',
						'body_color' => '#45525a',
						'page_background' => '#ffffff',
						'headings_font' => 'Permanent Marker',
						'body_font' => 'Droid Sans Mono'
					)
				),
				'velvet' => array(
					'label' => __( 'Velvet', 'customify' ),
					'preview' => array(
						'color-text' => '#ffffff',
						'background-card' => '#282828',
						'background-label' => '#000000',
						'font-main' => 'Pinyon Script',
						'font-alt' => 'Josefin Sans',
					),
					'options' => array(
						'links_color' => '#000000',
						'headings_color' => '#000000',
						'body_color' => '#000000',
						'page_background' => '#000000',
						'headings_font' => 'Pinyon Script',
						'body_font' => 'Josefin Sans',
					)
				),
			)
		),
	)
)
```


### Font Selector<a name="font_selector"></a>

In 1.3.0 We introduced the new font selector, it works with live preview only and it has this possible configs:

```
'headings_font' => array(
    'type'        => 'font',
    'label'       => esc_html__( 'Headings', 'customify' ),
    'desc'        => esc_html__( 'o descriere', 'customify' ),
    'selector'    => 'h1, h2, h3, h4, .title, .sub-title',

    // Set the defaults
    'default'   => array(
        'font-family' => 'Open Sans',
        'font-weight' => '400',
        'font-size'   => 24,
        'line-height' => 1.5,
        'letter-spacing' => 1,
        'text-align' => 'initial',
        'text-transform' => 'uppercase',
        'text-decoration' => 'underline'
    ),

    //'load_all_weights' => true,

    'recommended' => $recommended_headings_fonts,   // List of recommended fonts defined by theme
    
    // Sub Fields Configuration (optional)
    'fields'    => array(
        'font-size'     => array(                           // Set custom values for a range slider
            'min'          => 10,
            'max'          => 40,
            'step'         => 1,
            'unit'         => 'px',
        ),
        'line-height'    => array(0, 2, 0.1, ''),           // Short-hand version
        'letter-spacing' => array(-1, 4, 0.1, 'em'),
        'text-align'     => true,                           // Disable sub-field (False by default)
        'text-transform' => true,
        'text-decoration'=> true,
    ),
    'callback' => 'your_custom_callback_function'
),
```

In the above example you can see the callback parameter, it supports a PHP or Javascript function
which should replace the current output of the font

```
function your_custom_callback_function( $value, $font ) {
	return $combined_css';
}

function add_javascript_callback_function() { ?>
	<script>
		function your_custom_callback_function( $values, field ) {
			return $combined_css;
		}

	</script>
<?php }
add_action( 'customize_preview_init', 'add_javascript_callback_function' );
```


### Local Fonts

Also this font selector comes with the ability to add custom fonts from a theme.
If a theme comes with the name of a font and a stylesheet with its fontface it
will be added as the first option of the font selector
```
function theme_add_customify_theme_fonts ( $fonts ) {
	$fonts['Custom Font'] = array(
		'family' => 'Custom Font',
		'src' => get_template_directory_uri() . '/assets/fonts/custom_font/stylesheet.css',
		'variants' => array( '400', '700' )
	);
	return $fonts;
}
add_filter( 'customify_theme_fonts', 'theme_add_customify_theme_fonts' );
```

## License

## Thanks!
This plugin also includes the following libraries:

* Select 2 - https://select2.github.io/
* Ace Editor - https://ace.c9.io/
* React jQuery Plugin - https://github.com/natedavisolds/jquery-react
