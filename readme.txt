=== Customify ===
Contributors: euthelup, babbardel
Tags: customizer, css, editor, live, preview, customise
Requires at least: 3.8.0
Tested up to: 4.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Customify is a Theme Customizer Booster that you can easily use to add Fonts, Colors, Live CSS Editor and other options to your theme.

== Description ==

With Customify, developers can easily create advanced theme-specific options inside the WordPress Customizer. Using those options, a user can make presentational changes without having to know or edit the theme code.

=Types of Fields=

*Color*
A color picker used to control any text or background color of an element.

*Typography*
A series of typographic options that allow you to access the massive **Google Fonts library** and make them available inside your theme customizer.

*CSS Editor*
A powerful **Live CSS Editor** directly into your customizer! Useful for better control over the appearance of your theme without the need to create a child theme or worry about theme updates overwriting your customizations.

*Text Field*
A simple text field that allows you to customize elements like Site Title or Footer Credits.

*Select Dropdown*
A drop-down menu selector to be used when you have to choose from multiple options.

== Installation ==

1. Upload `pixcustomify.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to ‘Appearance -> Customize’ menu and have fun with the new fields

*Note*

If you want to add your own fields you can always filter the fields list config like this
`
/**
 * The $ config array holds 3 important keys:
 *  - 'sections' an array with sections(each section holds an array with fields)
 *  - 'panels' an array of panels( each panel holds an array with sections)
 *  - 'opt-name' the option key name which will hold all these options
 */

function make_this_function_name_unique( $config ) {

	$new_sections = array(
		'colors_sssection' => array( // this is the id of this section, it must be unique
			'title'    => __( 'Colorsss', 'customify_txtd' ),
			'options' => array( //this is the list of fields
				'links_colorsss'   => array( // each field must have an unique id
					'type'      => 'color', // the type key is required
					'label'     => __( 'Links Cosslor', 'customify_txtd' ),
					'live' => true,
					'default'   => '#6c6e70',
					'css'  => array( // the CSS key is the one which controls the output of this field, it contains an array of CSS selectors
						array( // each CSS selector must be an array with two important keys: the "selector" and the "property" and voila now when you will change the color of this field the CSS property of this selector will change.
							'selector' => 'a, .entry-meta a',
							'property'     => 'color',
						),
					)
				)
			)
		)
	);


	// Note: this example will overwrite the default sections created by this plugin.
	$config['sections'] = $new_sections;

	// if you still want to keep the default sections, try
	$config['sections'] = array_merge( $config['sections'], $new_sections );

	return $config; // when working with filters always return filtered value
}
add_filter( 'customify_filter_fields', 'make_this_function_name_unique', 10, 1 );
`
