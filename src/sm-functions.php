<?php
/**
 * Style Manager functions to be used by themes mainly.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

/**
 * @since   3.0.0
 *
 * @param          $label
 * @param          $selector
 * @param          $default
 * @param string[] $properties
 *
 * @return array
 */
function sm_get_color_select_darker_config( $label, $selector, $default, $properties = [ 'color' ] ): array {
	return sm_get_color_select_dark_config( $label, $selector, $default, $properties, true );
}

/**
 * @since   3.0.0
 *
 * @param          $label
 * @param          $selector
 * @param          $default
 * @param string[] $properties
 * @param false    $isDarker
 *
 * @return array
 */
function sm_get_color_select_dark_config( $label, $selector, $default, $properties = [ 'color' ], $isDarker = false ): array {

	$callback = 'sm_color_select_dark_cb';
	$choices  = [
		'background' => esc_html__( 'Background', '__plugin_txtd' ),
		'dark'       => esc_html__( 'Dark', '__plugin_txtd' ),
		'accent'     => esc_html__( 'Accent', '__plugin_txtd' ),
	];

	if ( $isDarker ) {
		$callback = 'sm_color_select_darker_cb';
		$choices  = [
			'background' => esc_html__( 'Background', '__plugin_txtd' ),
			'darker'     => esc_html__( 'Dark', '__plugin_txtd' ),
			'accent'     => esc_html__( 'Accent', '__plugin_txtd' ),
		];
	}

	$css = [];

	if ( ! is_array( $properties ) ) {
		$properties = [ $properties ];
	}

	foreach ( $properties as $property ) {
		$css[] = [
			'property'        => $property,
			'selector'        => $selector,
			'callback_filter' => $callback,
		];
	}

	return [
		'type'    => 'select_color',
		'label'   => $label,
		'live'    => true,
		'default' => $default,
		'css'     => $css,
		'choices' => $choices,
	];
}

/**
 * @since   3.0.0
 *
 * @param string $value
 * @param string $selector
 * @param string $property
 *
 * @return string
 */
function sm_color_select_dark_cb( string $value, string $selector, string $property ): string {
	return $selector . ' { ' . $property . ': var(--sm-current-' . $value . '-color); }' . PHP_EOL;
}

/**
 * @since   3.0.0
 *
 * @param string $value
 * @param string $selector
 * @param string $property
 *
 * @return string
 */
function sm_color_select_darker_cb( string $value, string $selector, string $property ): string {
	return $selector . ' { ' . $property . ': var(--sm-current-' . $value . '-color); }' . PHP_EOL;
}

/**
 * @since   3.0.0
 *
 * @param          $label
 * @param          $selector
 * @param          $default
 * @param string[] $properties
 *
 * @return array
 */
function sm_get_color_switch_darker_config( $label, $selector, $default, $properties = [ 'color' ] ): array {
	return sm_get_color_switch_dark_config( $label, $selector, $default, $properties, true );
}

/**
 * @since   3.0.0
 *
 * @param          $label
 * @param          $selector
 * @param          $default
 * @param string[] $properties
 * @param false    $isDarker
 *
 * @return array
 */
function sm_get_color_switch_dark_config( $label, $selector, $default, $properties = [ 'color' ], $isDarker = false ): array {

	$css      = [];
	$callback = 'sm_color_switch_dark_cb';
	if ( $isDarker ) {
		$callback = 'sm_color_switch_darker_cb';
	}

	if ( ! is_array( $properties ) ) {
		$properties = [ $properties ];
	}

	foreach ( $properties as $property ) {
		$css[] = [
			'property'        => $property,
			'selector'        => $selector,
			'callback_filter' => $callback,
		];
	}

	return [
		'type'    => 'sm_switch',
		'label'   => $label,
		'live'    => true,
		'default' => $default,
		'css'     => $css,
		'choices' => [
			'off' => esc_html__( 'Off', '__plugin_txtd' ),
			'on'  => esc_html__( 'On', '__plugin_txtd' ),
		],
	];
}

/**
 * @since   3.0.0
 *
 * @param string $value
 * @param string $selector
 * @param string $property
 *
 * @return string
 */
function sm_color_switch_dark_cb( string $value, string $selector, string $property ): string {
	$color = 'fg1';
	if ( $value === 'on' ) {
		$color = 'accent';
	}

	return $selector . ' {' . $property . ': var(--sm-current-' . $color . '-color); }' . PHP_EOL;
}

/**
 * @since   3.0.0
 *
 * @param string $value
 * @param string $selector
 * @param string $property
 *
 * @return string
 */
function sm_color_switch_darker_cb( string $value, string $selector, string $property ): string {
	$color = 'fg2';
	if ( $value === 'on' ) {
		$color = 'accent';
	}

	return $selector . ' {' . $property . ': var(--sm-current-' . $color . '-color); }' . PHP_EOL;
}
