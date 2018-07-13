# Color Palettes Integration Guide

## 1. Add color controls for all elements on the page
```php
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
add_filter('customify_filter_fields', 'make_this_function_name_unique' );
```

## 2. Add Style Manager section with master controls
### 2.1. Add Style Manager support to the theme
In your function.php file add the following line of code to add support for the Style Manager section.
This is usually done 
```php
if ( ! function_exists( 'themename_setup' ) ) :
    function listable_setup() {
        ...
        add_theme_support('customizer_style_manager');
        ...
endif;

add_action( 'after_setup_theme', 'themename_setup' );
``` 

### 2.2. Add a function to filter the Style Manager config
```php
/**
 * Add the Style Manager cross-theme Customizer section.
 *
 * @param array $options
 *
 * @return array
 */
function pixelgrade_add_customify_style_manager_section( $options ) {
	// If the theme hasn't declared support for style manager, bail.
	if ( ! current_theme_supports( 'customizer_style_manager' ) ) {
		return $options;
	}

	if ( ! isset( $options['sections']['style_manager_section'] ) ) {
		$options['sections']['style_manager_section'] = array();
	}
}
```
```php
add_filter( 'customify_filter_fields', 'pixelgrade_add_customify_style_manager_section', 12, 1 );
```

### 2.3. Extend Style Manager fields with proper defaults and connected fields
```php
// The section might be already defined, thus we merge, not replace the entire section config.
$options['sections']['style_manager_section'] = array_replace_recursive( $options['sections']['style_manager_section'], array(
    'options' => array(
        'sm_color_primary' => array(
            'default' => '#FF0000',
            'connected_fields' => array(
                'accent_color',
            ),
        ),
        ...
    ),
);
        
```

### 2.4. Create a default Color Palette for the current Theme
#### 2.4.1 Upload an image to Pixelgrade Cloud in order to use it as a mood background image for this Palette 
#### 2.4.2 Write the proper configuration and use the `customify_get_color_palettes` hook to add it to the main list 
Color values listed in the options attribute should match the ones that we've just set for the options in the Style Manager section (or rather the other way around)
```php
function themename_add_default_color_palette( $color_palettes ) {

    $color_palettes = array_merge(array(
        'default' => array(
            'label' => 'Default',
			'preview' => array(
				'background_image_url' => '',
			),
            'options' => array(
                'sm_color_primary' => '#FF4D58',
                'sm_color_secondary' => '#F53C48',
                'sm_color_tertiary' => '#FF4D58',
                'sm_dark_primary' => '#484848',
                'sm_dark_secondary' => '#2F2929',
                'sm_dark_tertiary' => '#919191',
                'sm_light_primary' => '#FFFFFF',
                'sm_light_secondary' => '#F9F9F9',
                'sm_light_tertiary' => '#F9F9F9',
            ),
        ),
    ), $color_palettes);
    
    return $color_palettes;
}
add_filter( 'customify_get_color_palettes', 'themename_add_default_color_palette' );
```
darkest shades should go in dark_primary
body text color should go in dark_secondary

Pairs of options that control the foreground / background for the same element should not stay in the same group (color, dark or light). One should stay in one of the light groups, and the other one can stay either in the color or the dark groups.
