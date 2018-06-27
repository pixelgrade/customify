# Color Palettes Integration Guide

## 1. Add Style Manager support to the theme
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

## 2. Add a function to filter the Style Manager config
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

### Extend Style Manager fields with proper defaults and connected fields
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

### Create a default Color Palette for the current Theme
Color values listed in the options attribute should match the ones that we've just set for the options in the Style Manager section (or rather the other way around)
```php
function themename_add_default_color_palette( $color_palettes ) {

    $color_palettes = array_merge(array(
        'default' => array(
            'label' => 'Default',
            'background_image_url' => '',
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
