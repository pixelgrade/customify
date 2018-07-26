# Color Palettes Integration Guide

Intro cu pasii mari pe care trebuie sa-i faca.

## 1. Define your Color Palette

### 1.1 Find all the Colors from Your Site
The first step is to scan your site and list all the colors that you can find. 

Next you need to organize and reduce the number of all those colors to three groups:
- **Color**: accents elements that give personality to the composition *(e.g., headlines)*
- **Dark**: used for most of the foreground elements *(e.g., body copy)*
- **Light**: usually used for the element's background, as a contrasted version of the two above

From over 7-years experience of building sites, we find out that those three categories could fill up most of the decisions that a designer take in setting up their site’s elements.

### 1.2 Define the Master Colors
Our current color palettes system supports at most 9 colors (3 accent colors, 3 dark shades and 3 light shades). Below are some guidelines and suggestions about the purpose of each color: 

| Section | Type | Description |
|:--|:--|:--|
| **Color** | Primary | The main Accent color use it to draw the highest attention.|
|   | Secondary | An alternative color for Primary |
|   | Tertiary | An alternative color for Secondary |
| **Dark** | Primary | Headings Color – usually the darkest shade. |
| | Secondary | Body text color |
|   | Tertiary | The lightest shade of dark. |
| **Light** | Primary | Main site background color |
|   | Secondary | A complementary color for site background |
|   | Tertiary | A *highlighter* background color for various elements (e.g. post-it notes, brush strokes). Should have a greater intensity than the others two light colors. |


To make things easier define those colors as constants in your `functions.php` file like so: 

```php
// Color
define( 'SM_COLOR_PRIMARY',     '#FF0000' ); // Use it for Accent Elements
define( 'SM_COLOR_SECONDARY',   '#00FF00' ); 
define( 'SM_COLOR_TERTIARY',    '#0000FF' );
 
// Dark
define( 'SM_DARK_PRIMARY',      '#111111' );
define( 'SM_DARK_SECONDARY',    '#222222' ); // Use it for Body Text
define( 'SM_DARK_TERTIARY',     '#333333' );
 
// Light
define( 'SM_LIGHT_PRIMARY',     '#EEEEEE' );
define( 'SM_LIGHT_SECONDARY',   '#DDDDDD' );
define( 'SM_LIGHT_TERTIARY',    '#CCCCCC' );
```

You may want to keep all nine definitions even if you don't need them. If you’re not using one constant in the config you can either copy-paste the value of another one in the same group or event alter it's value a little.

Having all these constants defined will come in handy when using palettes variations and also in defining the default palette for the theme.

Limit yourself to using as few colors as possible

Try to use as few colors as possible in your configuration. 

## 2. Create a Baseline for Color Customizations
To create a baseline for color customizations, we need to set up the system for being capable of changing any color from our website. We will do that by mapping each site element (e.g., page title) to a color option.



### 2.1 Scan all Your Site Elements for Color Rules

The first step is to scan throughout all the stylesheet files (CSS) and list all the elements that have a color-based rule declaration(e.g., color, background, border).

Then we will **group** all those CSS rules and place them under a limited number of color fields. To keep things consistent we suggest to start with the following structure and adjust to the theme needs:

| Section | Fields | Description |
|:--|:--|:--|
| **Header** | Header Text Color |
|  |  Navigation Links Color |
|  |  Links Active Color |
|  |  Header Background |
| **Main Content** | Page Title Color |
| | Body Text Color |
| | Body Link Color |
| | Body Link Active Color |
| ↳ *Headings Color* | Heading 1 |
| | Heading 2 |
| | Heading 3 |
| | Heading 4 |
| | Heading 5 |
| | Heading 6 |
| ↳ *Backgrounds* | Content Background Color |
| **Buttons** | Text Color |
| | Background Color |
| **Footer** | Footer Text Color |
| | Footer Links Color |
| | Footer Headings Color |
| | Footer Background |
| **Miscellaneous** | Other Fields |

### 2.2 The Structure Of a Color Field
Details:
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
### 2.3 Add Color Controls to all Your Site Elements 
Add the code snippet below to `functions.php` in order to..
[un snippet si cu sectiunile de mai sus in care doar sa completeze reguli de CSS?]
```php

```


## 3. Add Style Manager section with master controls
### 3.1. Add Style Manager support to the theme
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

### 3.2. Add a function to filter the Style Manager config
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

### 3.3. Extend Style Manager fields with proper defaults and connected fields
```php
// The section might be already defined, thus we merge, not replace the entire section config.
$options['sections']['style_manager_section'] = array_replace_recursive( $options['sections']['style_manager_section'], array(
    'options' => array(
        'sm_color_primary' => array(
            'default' => SM_COLOR_PRIMARY,
            'connected_fields' => array(
                'accent_color',
            ),
        ),
        ...
    ),
);
        
```

### 3.4. Create a default Color Palette for the current Theme
#### 3.4.1 Upload an image to Pixelgrade Cloud in order to use it as a mood background image for this Palette 
#### 3.4.2 Write the proper configuration and use the `customify_get_color_palettes` hook to add it to the main list 
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

---
## Tips and Tricks / FAQs
### What if I have more colors?
It happens a lot for developers to write unneeded code, or overly specific CSS selectors. This is a good time to give your code a health check.

Things that have a big chance of needed to be improved in your code.

1. Use `opacity` instead of using a new color value when possible.
```css
.container {
    color: #222;
}

.container-child {
    /* color: #444 */
    opacity: 0.9;
}
```

2. Use the `currentColor` value for properties like `border`, `outline`, `box-shadow`, `placeholder` and other properties or pseudo-elements.
```css
.element {
    color: #222;
    /* box-shadow: #222 0 1em 1em; */
    box-shadow: currentColor 0 1em 1em;
}

.element::after {
    content: "";
    /* border: 2px solid #222; */
    border: 2px solid;
}
```
Use `color: inherit` when possible
```
a {
    color: #f00;
}

.container {
    color: #222;
}

.container a {
    /* color: #222; */
    color: inherit;
    text-decoration: underline;
}

```

**Make use of Customify's callback filters**
If your theme uses more than 3 dark or white shades, you can always make use of the callback filters feature that Customify uses.  
