<?php

$config = apply_filters('customify_filter_fields', array() );

$customify_sections = array();

if ( isset( $config['sections'] ) && ! empty( $config['sections'] ) ) {

	foreach ( $config['sections'] as $id => $section ) {
		$customify_sections[$id] = $section['title'];
	}

}

if ( isset( $config['panels'] ) && ! empty( $config['panels'] ) ) {

	foreach ( $config['panels'] as $panel_id => $panel ) {

		if ( isset( $panel['sections'] ) && ! empty( $panel['sections'] ) ) {
			foreach ( $panel['sections'] as $id => $section ) {
				$customify_sections[$id] = $section['title'];
			}
		}
	}
}

$general_settings = array(
	'type'    => 'postbox',
	'label'   => 'General Settings',
	'options' => array(
		'values_store_mod' => array(
			'name'    => 'values_store_mod',
			'label'   => __( 'Store values as:', 'customify_txtd' ),
			'desc'    => __( 'You can store the values globally so you can use them with other themes or store them as a "theme_mod" which will make an individual set of options only for the current theme', 'customify_txtd' ),
			'default' => 'option',
			'type'    => 'select',
			'options' => array(
				'option'    => __( 'Option (global options)', 'customify_txtd' ),
				'theme_mod' => __( 'Theme Mod (per theme options)', 'customify_txtd' ),
			),
		),

		'disable_default_sections' => array(
			'name'    => 'disable_default_sections',
			'label'   => __( 'Disable default sections', 'customify_txtd' ),
			'desc'    => __( 'You can disable default sections', 'customify_txtd' ),
			'type'    => 'multicheckbox',
			'options' => array(
				'nav'    => __( 'Navigation', 'customify_txtd' ),
				'static_front_page' => __( 'Front Page', 'customify_txtd' ),
				'title_tagline'    => __( 'Title', 'customify_txtd' ),
				'colors' => __( 'Colors', 'customify_txtd' ),
				'background_image'    => __( 'Background', 'customify_txtd' ),
				'header_image' => __( 'Header', 'customify_txtd' ),
				'widgets' => __( 'Widgets', 'customify_txtd' ),
			),
		),

		'enable_reset_buttons' =>  array(
			'name'    => 'enable_reset_buttons',
			'label'   => __( 'Enable Reset Buttons', 'customify_txtd' ),
			'desc'    => __( 'You can enable "Reset to defaults" buttons for panels / sections or all settings. We have disabled this feature by default to avoid accidental resets. If you are sure that you need it please enable this.', 'customify_txtd' ),
			'default'        => false,
			'type'           => 'switch',
		),

		'enable_editor_style' =>  array(
			'name'    => 'enable_editor_style',
			'label'   => __( 'Enable Editor Style', 'customify_txtd' ),
			'desc'    => __( 'The styling added by Customify in front-end can be added in the WordPress editor too by enabling this option', 'customify_txtd' ),
			'default'        => true,
			'type'           => 'switch',
		),
	)
); # config

if ( !empty( $customify_sections ) ) {
	$general_settings['options']['disable_customify_sections'] = array(
		'name'    => 'disable_customify_sections',
		'label'   => __( 'Disable Customify sections', 'customify_txtd' ),
		'desc'    => __( 'You can also disable Customify\'s sections', 'customify_txtd' ),
		'type'    => 'multicheckbox',
		'options' => $customify_sections
	);
}

return $general_settings;