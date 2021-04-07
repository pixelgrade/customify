<?php
/**
 * This is a utility class that groups all our fonts related helper functions.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Utils;

/**
 * Fonts Helper class.
 *
 * @since   3.0.0
 * @package Pixelgrade Customify
 */
class Fonts {

	/**
	 * The precision to use when dealing with float values.
	 * @since    3.0.0
	 */
	const FLOAT_PRECISION = 2;

	/**
	 * Cleanup stuff like tab characters.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function cleanupWhitespace( string $string ): string {

		return normalize_whitespace( $string );
	}

	/**
	 * Determine if a given array is associative.
	 *
	 * @param $array
	 *
	 * @return bool
	 */
	public static function isAssocArray( $array ): bool {
		if ( ! is_array( $array ) ) {
			return false;
		}

		return ( $array !== array_values( $array ) );
	}

	/**
	 * Given an URL, attempt to extract the origin (protocol + domain).
	 *
	 * @param string $url
	 *
	 * @return false|string False if the given string is not a proper URL, the origin otherwise.
	 */
	public static function extractOriginFromUrl( string $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		$regex = '#((?:http|https|ftp|ftps)?\:?\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,})(\/\S*)?#i';
		preg_match( $regex, $url, $matches );

		if ( empty( $matches[1] ) ) {
			return false;
		}

		return $matches[1];
	}

	/**
	 * Given a string, treat it as a (comma separated by default) list and return the array with the items
	 *
	 * @param mixed $str
	 * @param string $delimiter Optional. The delimiter to user.
	 *
	 * @return array
	 */
	public static function maybeExplodeList( $str, string $delimiter = ',' ): array {
		// If by any chance we are given an array, just return it
		if ( is_array( $str ) ) {
			return $str;
		}

		// Anything else we coerce to a string
		if ( ! is_string( $str ) ) {
			$str = (string) $str;
		}

		// Make sure we trim it
		$str = trim( $str );

		// Bail on empty string
		if ( empty( $str ) ) {
			return [];
		}

		// Return the whole string as an element if the delimiter is missing
		if ( false === strpos( $str, $delimiter ) ) {
			return [ $str ];
		}

		// Explode it and return it
		return explode( $delimiter, $str );
	}

	/**
	 * Given a value, attempt to implode it.
	 *
	 * @param mixed $value
	 * @param string $delimiter Optional. The delimiter to user.
	 *
	 * @return string
	 */
	public static function maybeImplodeList( $value, string $delimiter = ',' ): string {
		// If by any chance we are given a string, just return it
		if ( is_string( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			return implode( $delimiter, $value );
		}

		// For anything else (like objects) we return an empty string.
		return '';
	}

	/**
	 * Given a selector standardize it to a list.
	 *
	 * @param mixed $selector
	 *
	 * @return array
	 */
	public static function standardizeFontSelector( $selector ): array {
		$selector_list = [];

		// Attempt to split it by coma.
		$list = self::maybeExplodeList( $selector );

		// Make sure that we have an associative array with the key being the individual selector
		foreach ( $list as $key => $value ) {
			if ( is_numeric( $key ) && is_string( $value ) ) {
				// This means a simple string selector.
				$value = self::cleanupWhitespace( $value );
				$selector_list[ $value ] = [];
				continue;
			}

			// Treat the rest a having the selector in the key and a set of details in the value.
			$key = self::cleanupWhitespace( (string) $key );
			$selector_list[ $key ] = $value;
		}

		return $selector_list;
	}

	/**
	 * Given a (source) fonts list (like Google fonts list), standardize it (e.g., make sure font variants use the 400 one instead of 'regular' or 'normal').
	 *
	 * @param array $fontList
	 *
	 * @return array|false
	 */
	public static function standardizeFontsList( array $fontList ) {
		// Reject anything that is not an array.
		if ( ! is_array( $fontList ) ) {
			return false;
		}

		$newFontsList = [];

		// In case a font is missing any of these entries, these are the safe defaults.
		$defaultFontEntries = [
			'family' => null,
			'family_display' => null,
			'category' => 'other',
			'variants' => [ '400' ],
			'subsets'  => [ 'latin' ],
			'fallback_stack' => '',
		];

		foreach ( $fontList as $key => $font ) {
			$newFont = $font;
			if ( ! is_array( $newFont ) ) {
				$newFont = [];
			}

			if ( ! isset( $newFont['family'] ) && ! is_numeric( $key ) ) {
				$newFont['family'] = (string) $key;
			}

			if ( empty( $newFont['family'] ) ) {
				// We will skip this font if we couldn't get a font family.
				continue;
			}

			$newFont = wp_parse_args( $newFont, $defaultFontEntries );

			// Standardize the font family
			$newFont['family'] = self::standardizeSourceFontFamily( $newFont['family'] );
			// Standardize the font variants list.
			if ( ! is_bool( $newFont['variants'] ) && empty( $newFont['variants'] ) ) {
				$newFont['variants'] = ['400'];
			}
			$newFont['variants'] = self::standardizeSourceFontVariantsList( $newFont['variants'] );

			// Add the standardized font to the new list, keeping the relative order.
			// We want to have the font family as key for easy searching!
			$newFontsList += [ $newFont['family'] => $newFont ];
		}

		// Allow others to filter this.
		return apply_filters( 'customify_standardized_fonts_list', $newFontsList, $fontList );
	}

	/**
	 * @param string $fontFamily
	 *
	 * @return string
	 */
	public static function standardizeSourceFontFamily( string $fontFamily ): string {
		// Make sure that the font family is free from " or ', but only if it is missing commas (i.e., it is not a font stack).
		if ( false === strpos( $fontFamily, ',' ) ) {
			$fontFamily = trim( $fontFamily, "\"\'\‘\’\“\”" );
		}

		return $fontFamily;
	}

	public static function standardizeFontVariant( $variant ): string {
		// We want all variants to be strings, since they are not numerical values (even if they may look like it).
		$variant = (string) $variant;

		// Lowercase it.
		$variant = strtolower($variant);

		switch ( $variant ) {
			case 'thin':
				$variant = '100';
				break;
			case 'light':
				$variant = '200';
				break;
			case 'regular':
			case 'normal':
				$variant = '400';
				break;
			case 'italic':
				$variant = '400italic';
				break;
			case 'medium':
				$variant = '500';
				break;
			case 'bold':
				$variant = '700';
				break;
			default:
				break;
		}

		return $variant;
	}

	/**
	 * @param array|string $variantsList
	 *
	 * @return array
	 */
	public static function standardizeSourceFontVariantsList( $variantsList ): array {
		// Make sure we treat comma delimited strings as list.
		$variantsList = self::maybeExplodeList( $variantsList );

		if ( empty( $variantsList ) ) {
			return $variantsList;
		}

		foreach ( $variantsList as $key => $variant ) {
			$variantsList[ $key ] = \Pixelgrade\Customify\Utils\Fonts::standardizeFontVariant( $variant );
		}

		// Make sure the variants list is ordered ascending, by value.
		sort( $variantsList, SORT_STRING );

		return $variantsList;
	}



	/**
	 * Handle special logic for when the $value array is not an associative array.
	 *
	 * @param mixed $value
	 * @return array Return a new associative array with proper keys
	 */
	public static function standardizeNonAssociativeFontValues( $value ): array {
		// If the value provided is not array or is already an associative array, simply return it
		if ( ! is_array( $value ) || self::isAssocArray( $value ) ) {
			return $value;
		}

		$new_value = [];

		// The first entry is the font-family
		if ( isset( $value[0] ) ) {
			$new_value['font_family'] = $value[0];
		}

		// The second entry is the variant.
		if ( isset( $value[1] ) ) {
			$new_value['font_variant'] = $value[1];
		}

		return $new_value;
	}

	/**
	 * Given a value we will standardize it to an array with 'value' and 'unit'.
	 *
	 * @param mixed $value
	 * @param string $field Optional. The subfield name (e.g. `font-size`).
	 * @param array $font Optional. The entire font field config.
	 *
	 * @return array
	 */
	public static function standardizeNumericalValue( $value, $field = '', $font = [] ): array {
		$standard_value = [
			'value' => false,
			'unit' => false,
		];

		if ( self::isFalsy( $value ) ) {
			return $standard_value;
		}

		if ( is_numeric( $value ) ) {
			$standard_value['value'] = $value;
			// Deduce the unit.
			$standard_value['unit'] = self::getSubFieldUnit( $field, $font );
		} elseif ( is_array( $value ) ) {
			// The value may be an associative array or a numerical keyed one.
			if ( isset( $value['value'] ) ) {
				$standard_value['value'] = $value['value'];
			} elseif ( isset( $value[0] ) ) {
				$standard_value['value'] = $value[0];
			}

			if ( isset( $value['unit'] ) ) {
				$standard_value['unit'] = $value['unit'];
			} elseif ( isset( $value[1] ) ) {
				$standard_value['unit'] = $value[1];
			}
		} elseif ( is_string( $value ) ) {
			// We will get everything in front that is a valid part of a number (float including).
			preg_match( "/^([\d.\-+]+)/i", $value, $match );

			if ( ! empty( $match ) && isset( $match[0] ) ) {
				$standard_value['value'] = $match[0];
				$standard_value['unit'] = substr( $value, strlen( $match[0] ) );
			} else {
				// If we could not extract anything useful we will trust the developer and leave it like that.
				$standard_value['value'] = $value;
			}
		}

		// Make sure that the value number is rounded to 2 decimals.
		if ( is_numeric( $standard_value['value'] ) ) {
			$standard_value['value'] = round( $standard_value['value'], self::FLOAT_PRECISION );
		}

		// Make sure that we convert all falsy unit values to the boolean false.
		if ( self::isFalsy( $standard_value['unit'] ) ) {
			$standard_value['unit'] = false;
		}

		return $standard_value;
	}

	public static function standardizeRangeFieldAttributes( $attributes ) {
		if ( false === $attributes ) {
			return $attributes;
		}

		if ( ! is_array( $attributes ) ) {
			return [
				'min' => '',
				'max' => '',
				'step' => '',
				'unit' => '',
			];
		}

		// Make sure that if we have a numerical indexed array, we will convert it to an associative one.
		if ( ! self::isAssocArray( $attributes ) ) {
			$defaults = [
				'min',
				'max',
				'step',
				'unit',
			];

			$attributes = array_combine( $defaults, array_values( $attributes ) );
		}

		return $attributes;
	}

	/**
	 * Given a font subfields configuration determine a list of allowed properties.
	 *
	 * The returned list is in the format: `css-property-name`: true|false.
	 *
	 * @param array $subfields
	 *
	 * @return array
	 */
	public static function extractAllowedCSSPropertiesFromFontFields( array $subfields ): array {
		// Nothing is allowed by default.
		$allowedProperties = [
			'font-family'     => false,
			'font-weight'     => false,
			'font-style'      => false,
			'font-size'       => false,
			'line-height'     => false,
			'letter-spacing'  => false,
			'text-align'      => false,
			'text-transform'  => false,
			'text-decoration' => false,
		];

		if ( empty( $subfields ) || ! is_array( $subfields ) ) {
			return $allowedProperties;
		}

		// We will match the subfield keys with the CSS properties, but only those that properties that are above.
		// Maybe at some point some more complex matching would be needed here.
		foreach ( $subfields as $key => $value ) {
			if ( isset( $allowedProperties[ $key ] ) ) {
				// Convert values to boolean.
				$allowedProperties[ $key ] = ! empty( $value );

				// For font-weight we want font-style to go the same way,
				// since these two are generated from the same subfield: font-weight (actually holding the font variant value).
				if ( 'font-weight' === $key ) {
					$allowedProperties[ 'font-style' ] = $allowedProperties[ $key ];
				}
			}
		}

		return $allowedProperties;
	}

	public static function getValidSubfieldValues( $subfield, $labels = false ) {
		$valid_values = apply_filters( 'customify_fonts_valid_subfield_values', [
			'text_align'      => [
				'initial' => esc_html__( 'Initial', '__plugin_txtd' ),
				'center'  => esc_html__( 'Center', '__plugin_txtd' ),
				'left'    => esc_html__( 'Left', '__plugin_txtd' ),
				'right'   => esc_html__( 'Right', '__plugin_txtd' ),
			],
			'text_transform'  => [
				'none'       => esc_html__( 'None', '__plugin_txtd' ),
				'capitalize' => esc_html__( 'Capitalize', '__plugin_txtd' ),
				'uppercase'  => esc_html__( 'Uppercase', '__plugin_txtd' ),
				'lowercase'  => esc_html__( 'Lowercase', '__plugin_txtd' ),
			],
			'text_decoration' => [
				'none'         => esc_html__( 'None', '__plugin_txtd' ),
				'underline'    => esc_html__( 'Underline', '__plugin_txtd' ),
				'overline'     => esc_html__( 'Overline', '__plugin_txtd' ),
				'line-through' => esc_html__( 'Line Through', '__plugin_txtd' ),
			],
		] );

		if ( ! empty( $valid_values[ $subfield ] ) ) {
			// Return only the keys if we've been instructed to do so.
			if ( false === $labels && self::isAssocArray( $valid_values[ $subfield ] ) ) {
				return array_keys( $valid_values[ $subfield ] );
			}

			return $valid_values[ $subfield ];
		}

		return [];
	}

	/**
	 * @param string $field
	 * @param array  $font
	 *
	 * @return bool|string
	 */
	public static function getSubFieldUnit( string $field, array $font ) {
		if ( empty( $field ) || empty( $font ) ) {
			return false;
		}

		// If the field has no definition.
		if ( empty( $font['fields'][ $field ] ) ) {
			// These fields don't have an unit, by default.
			if ( in_array( $field, ['font-family', 'font-weight', 'font-style', 'line-height', 'text-align', 'text-transform', 'text-decoration'] ) ){
				return false;
			}

			// The rest of the subfields have pixels as default units.
			return 'px';
		}

		if ( isset( $font['fields'][ $field ]['unit'] ) ) {
			// Make sure that we convert all falsy unit values to the boolean false.
			return self::isFalsy( $font['fields'][ $field ]['unit'] ) ? false : $font['fields'][ $field ]['unit'];
		}

		if ( isset( $font['fields'][ $field ][3] ) ) {
			// Make sure that we convert all falsy unit values to the boolean false.
			return self::isFalsy( $font['fields'][ $field ][3] ) ? false : $font['fields'][ $field ][3];
		}

		return 'px';
	}

	/**
	 *
	 * @since 3.0.0
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function sanitizeFontFamilyCSSValue( $value ): string {
		// Since we might get a stack, attempt to treat is a comma-delimited list.
		$fontFamilies = self::maybeExplodeList( $value );
		if ( empty( $fontFamilies ) ) {
			return '';
		}

		foreach ( $fontFamilies as $key => $fontFamily ) {
			// No whitespace at the back or the front.
			$fontFamily = trim( $fontFamily );
			// First, make sure that the font family is free from " or '
			$fontFamily = trim( $fontFamily, "\"\'\‘\’\“\”" );
			// No whitespace at the back or the front, again.
			$fontFamily = trim( $fontFamily );

			if ( '' === $fontFamily ) {
				unset( $fontFamilies[ $key ] );
				continue;
			}

			// Now, if the font family contains spaces, wrap it in ".
			if ( false !== strpos( $fontFamily, ' ' ) ) {
				$fontFamily = '"' . $fontFamily . '"';
			}

			// Finally, put it back.
			$fontFamilies[ $key ] = $fontFamily;
		}

		// Make sure that we have no duplicates.
		$fontFamilies = array_unique( $fontFamilies );

		return self::maybeImplodeList( $fontFamilies, ', ' );
	}

	/**
	 * Will convert an array of CSS like variants into their FVD equivalents. Web Font Loader expects this format.
	 * @link https://github.com/typekit/fvd
	 *
	 * @param array|int|string $variants
	 *
	 * @return array
	 */
	public static function convertFontVariantsToFvds( $variants ): array {
		$fvds = [];
		if ( ! is_array( $variants ) || empty( $variants ) ) {
			return $fvds;
		}

		foreach ( $variants as $variant ) {
			// Make sure that we are working with strings.
			$variant = (string) $variant;

			// This is the default font style.
			$font_style = 'n'; // normal
			if ( false !== strrpos( $variant, 'italic'  ) ) {
				$font_style = 'i';
				$variant    = str_replace( 'italic', '', $variant );
			} elseif ( false !== strrpos( $variant, 'oblique' ) ) {
				$font_style = 'o';
				$variant    = str_replace( 'oblique', '', $variant );
			}

			//          The equivalence:
			//
			//			1: 100
			//			2: 200
			//			3: 300
			//			4: 400 (default, also recognized as 'normal')
			//			5: 500
			//			6: 600
			//			7: 700 (also recognized as 'bold')
			//			8: 800
			//			9: 900

			switch ( $variant ) {
				case '100':
					$font_weight = 1;
					break;
				case '200':
					$font_weight = 2;
					break;
				case '300':
					$font_weight = 3;
					break;
				case '500':
					$font_weight = 5;
					break;
				case '600':
					$font_weight = 6;
					break;
				case '700':
				case 'bold':
					$font_weight = 7;
					break;
				case '800':
					$font_weight = 8;
					break;
				case '900':
					$font_weight = 9;
					break;
				default:
					$font_weight = 4;
					break;
			}

			$fvds[] = $font_style . '' .  $font_weight;
		}

		return $fvds;
	}

	/**
	 * Will convert an array of CSS like variants into the appropriate Google Fonts CSS 2 API format.
	 * @link https://developers.google.com/fonts/docs/css2
	 *
	 * @param array $variants
	 *
	 * @return string
	 */
	public static function convertFontVariantsToGoogleFontsCSS2Styles( array $variants ): string {
		$stylesString = '';
		if ( ! is_array( $variants ) || empty( $variants ) ) {
			return $stylesString;
		}

		$styleWeights = [
			'italic' => [],
			'normal' => [],
		];

		foreach ( $variants as $variant ) {
			// Make sure that we are working with strings.
			$variant = (string) $variant;

			// This is the default font style.
			$font_style = 'normal'; // normal
			if ( false !== strrpos( $variant, 'italic'  ) ) {
				$font_style = 'italic';
				$variant    = str_replace( 'italic', '', $variant );
			}

			//          The equivalence:
			//
			//			1: 100
			//			2: 200
			//			3: 300
			//			4: 400 (default, also recognized as 'normal')
			//			5: 500
			//			6: 600
			//			7: 700 (also recognized as 'bold')
			//			8: 800
			//			9: 900

			switch ( $variant ) {
				case '100':
					$font_weight = 100;
					break;
				case '200':
					$font_weight = 200;
					break;
				case '300':
					$font_weight = 300;
					break;
				case '500':
					$font_weight = 500;
					break;
				case '600':
					$font_weight = 600;
					break;
				case '700':
				case 'bold':
					$font_weight = 700;
					break;
				case '800':
					$font_weight = 800;
					break;
				case '900':
					$font_weight = 900;
					break;
				default:
					$font_weight = 400;
					break;
			}

			$styleWeights[ $font_style ][] = $font_weight;
		}

		// Now construct the string.

		// All supported weights, ordered numerically.
		$allWeights = [ 100, 200, 300, 400, 500, 600, 700, 800, 900 ];

		$axisTagsList = [];
		// We always have both `ital` and `wght` axis, for a clearer logic.
		$axisTagsList[] = 'ital';
		$axisTagsList[] = 'wght';

		$axisTuplesList = [];
		foreach ( $allWeights as $weight ) {
			// Go through all axes determine the tuple (e.g. italic 400 becomes 1,400; or 700 becomes 0,700)
			// The ital axis can only have the value 0 or 1.
			if ( false !== array_search( $weight, $styleWeights['normal'] ) ) {
				$axisTuplesList[] = '0,' . $weight;
			}
			if ( false !== array_search( $weight, $styleWeights['italic'] ) ) {
				$axisTuplesList[] = '1,' . $weight;
			}
		}

		if ( ! empty( $axisTuplesList ) ) {
			// We must make sure that the axis tags are ordered alphabetically.
			sort( $axisTagsList, SORT_STRING );
			// We also need to sort the tuples, numerically.
			sort( $axisTuplesList, SORT_STRING );

			$stylesString = join( ',', $axisTagsList ) . '@' . join( ';', $axisTuplesList );
		}

		return $stylesString;
	}

	/**
	 * Attempt to JSON decode the provided value.
	 *
	 * @param mixed $value
	 *
	 * @return mixed|string
	 */
	public static function maybeDecodeValue( $value ) {
		// If the value is already an array, nothing to do.
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( is_string( $value ) ) {
			$value = self::decodeURIComponent( $value );
			$value = wp_unslash( $value );
			$value = json_decode( $value, true );
		}

		return $value;
	}

	/**
	 * Attempt to JSON encode the provided value.
	 *
	 * @param mixed $value
	 *
	 * @return mixed|string
	 */
	public static function maybeEncodeValue( $value ) {
		// If the value is already a string, nothing to do.
		if ( is_string( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) || is_object( $value ) ) {
			$value = self::encodeURIComponent( json_encode( $value ) );
		}

		return $value;
	}

	public static function isFalsy( $value ): bool {
		return in_array( $value, [ '', 'false', false, ], true );
	}

	/**
	 * Does the same thing the JS encodeURIComponent() does
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public static function encodeURIComponent( string $str ): string {
		//if we get an array we just let it be
		if ( is_string( $str ) ) {
			$revert = [ '%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')' ];
			$str = strtr( rawurlencode( $str ), $revert );
		}

		return $str;
	}

	/**
	 * Does the same thing the JS decodeURIComponent() does
	 *
	 * @param mixed $str
	 *
	 * @return mixed
	 */
	public static function decodeURIComponent( $str ) {
		// Nothing to do if we receive an array.
		if ( is_array( $str ) ) {
			return $str;
		}

		if ( is_string( $str ) ) {
			$revert = [ '!' => '%21', '*' => '%2A', "'" => '%27', '(' => '%28', ')' => '%29' ];
			$str    = rawurldecode( strtr( $str, $revert ) );
		}

		return $str;
	}
}
