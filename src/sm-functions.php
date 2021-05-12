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
		'type'    => 'sm_toggle',
		'label'   => $label,
		'live'    => true,
		'default' => $default,
		'css'     => $css,
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
function sm_color_switch_dark_cb( bool $value, string $selector, string $property ): string {
	$color = 'fg1';

	if ( $value === true ) {
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
function sm_color_switch_darker_cb( bool $value, string $selector, string $property ): string {
	$color = 'fg2';

	if ( $value === true ) {
		$color = 'accent';
	}

	return $selector . ' {' . $property . ': var(--sm-current-' . $color . '-color); }' . PHP_EOL;
}

/**
 * @since   3.0.0
 *
 * @param string $value
 *
 * @return string
 */
function sm_advanced_palette_output_cb( string $value ) {
	return '';
}

/**
 * @since   3.0.0
 *
 * @param string $value
 *
 * @return string
 */
function sm_variation_range_cb( string $value ) {
	return '';
}

function sm_get_palette_output_from_color_config( string $value ) {
	$output = '';

	$palettes = json_decode( $value );

	if ( empty( $palettes ) ) {
		$palettes = get_fallback_palettes();
	}

	$output .= palettes_output( $palettes );

	return $output;
}

/**
 * @since   3.0.0
 *
 * @param array $palettes
 *
 * @return string
 */
function palettes_output( array $palettes ) {
	$output = '';
	$variation = intval( get_option( 'sm_site_color_variation', 1 ) );

	foreach ( $palettes as $palette_index => $palette ) {
		$sourceIndex = $palette->sourceIndex;

		$output .= 'html { ' . PHP_EOL;
		$output .= get_initial_color_variables( $palette );
		$output .= get_variables_css( $palette, $variation - 1 );
		$output .= get_variables_css( $palette, $sourceIndex, false, true );
		$output .= '}' . PHP_EOL;

		$output .= '.is-dark { ' . PHP_EOL;
		$output .= get_variables_css( $palette, $variation - 1, true );
		$output .= get_variables_css( $palette, $sourceIndex, true, true );
		$output .= '}' . PHP_EOL;
	}

	return $output;
}

/**
 * @since   3.0.0
 *
 * @param object $palette
 *
 * @return string
 */
function get_initial_color_variables( object $palette ) {
	$colors = $palette->colors;
	$textColors = $palette->textColors;
	$id = $palette->id;
	$prefix = '--sm-color-palette-';

	$output = '';

	foreach ( $colors as $index => $color ) {
		$output .= $prefix . $id . '-color-' . ( $index + 1 ) . ': ' . $color->value . ';' . PHP_EOL;
	}

	foreach ( $textColors as $index => $color ) {
		$output .= $prefix . $id . '-text-color-' . ( $index + 1 ) . ': ' . $color->value . ';' . PHP_EOL;
	}

	return $output;
}

/**
 * @since   3.0.0
 *
 * @param object $palette
 * @param int    $offset
 * @param bool   $isDark
 * @param bool   $isShifted
 *
 * @return string
 */
function get_variables_css( object $palette, int $offset = 0, bool $isDark = false, bool $isShifted = false ) {
	$colors = $palette->colors;
	$count = count( $colors );

	$output = '';

	foreach ( $colors as $index => $color ) {
		$oldColorIndex = ( $index + $offset ) % $count;

		if ( $isDark ) {
			if ( $oldColorIndex < $count / 2 ) {
				$oldColorIndex = 11 - $oldColorIndex;
			} else {
				continue;
			}
		}

		$output .= get_color_variables( $palette, $index, $oldColorIndex, $isShifted );
	}

	return $output;
}

/**
 * @since   3.0.0
 *
 * @param object $palette
 * @param int    $newColorIndex
 * @param int    $oldColorIndex
 * @param bool   $isShifted
 *
 * @return string
 */
function get_color_variables( object $palette, int $newColorIndex, int $oldColorIndex, bool $isShifted ) {
	$colors = $palette->colors;
	$id = $palette->id;
	$count = count( $colors );
	$lightColorsCount = isset( $palette->lightColorsCount ) ? $palette->lightColorsCount : $count / 2;

	$accentColorIndex = ( $oldColorIndex + $count / 2 ) % $count;
	$prefix = '--sm-color-palette-';
	$suffix = $isShifted ? '-shifted' : '';

	$output = '';

	$output .= $prefix . $id . '-bg-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-color-' . ( $oldColorIndex + 1 ) . ');' . PHP_EOL;
	$output .= $prefix . $id . '-accent-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-color-' . ( $accentColorIndex + 1 ) . ');' . PHP_EOL;

	if ( $oldColorIndex < $lightColorsCount ) {
		$output .= $prefix . $id . '-fg1-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-text-color-1);' . PHP_EOL;
		$output .= $prefix . $id . '-fg2-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-text-color-2);' . PHP_EOL;
	} else {
		$output .= $prefix . $id . '-fg1-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-color-1);' . PHP_EOL;
		$output .= $prefix . $id . '-fg2-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-color-1);' . PHP_EOL;
	}

	return $output;
}

/**
 * @since   3.0.0
 *
 * @return array
 */
function get_fallback_palettes() {
	$alphabet = range( 'A', 'Z' );

	$options_details = PixCustomifyPlugin()->get_options_configs();

	$color_control_ids = array(
		'sm_color_primary',
		'sm_color_secondary',
		'sm_color_tertiary',
	);

	$lighter = get_fallback_color_value( 'sm_light_primary' );
	$light = get_fallback_color_value( 'sm_light_tertiary' );
	$text_color = get_fallback_color_value( 'sm_dark_secondary' );
	$dark = get_fallback_color_value( 'sm_dark_primary' );
	$darker = get_fallback_color_value( 'sm_dark_tertiary' );

	$palettes = array();

	foreach ( $color_control_ids as $index => $control_id ) {

		if ( empty( $options_details[ $control_id ] ) ) {
			continue;
		}

		$color = get_fallback_color_value( $control_id );

		$colors = array(
			$lighter,
			$light,
			$light,
			$light,
			$color,
			$color,
			$color,
			$dark,
			$dark,
			$dark,
			$darker,
			'#000000',
		);

		$color_objects = array();

		foreach ( $colors as $color ) {
			$obj = ( object ) array(
				'value' => $color
			);

			$color_objects[] = $obj;
		}

		$textColors = array(
			$text_color,
			$text_color,
		);

		$textColor_objects = array();

		foreach ( $textColors as $color ) {
			$obj = ( object ) array(
				'value' => $color
			);

			$textColor_objects[] = $obj;
		}

		$palettes[] = ( object ) array(
			'colors'      => $color_objects,
			'textColors'  => $textColor_objects,
			'source'      => $color,
			'sourceIndex' => 6,
			'label'       => 'Color ' . $alphabet[ $index + 1 ],
			'id'          => $index + 1
		);
	}

	return $palettes;
}

function get_fallback_color_value( $id ) {

	$color = PixCustomifyPlugin()->get_option( $id . '_final' );

//	if ( empty( $color ) ) {
//		$color = PixCustomifyPlugin()->get_option( $id );
//	}
//
//	if ( empty( $color ) ) {
//		$config = PixCustomifyPlugin()->get_option_details( $id );
//
//		if ( isset( $config['default'] ) ) {
//			$color = $config['default'];
//		}
//	}

	return $color;
}
