<?php
/**
 * JSON cleaner for json_encode().
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Utils;

/**
 * JSON cleaner class.
 *
 * @since   3.0.0
 * @package Pixelgrade Customify
 */
class JSONCleaner {
	private static array $_objects;
	private static int $_depth;

	/**
	 * Cleans a variable for JSON encoding.
	 *
	 * Does the same thing as _wp_json_sanity_check(), but it does a really important extra thing: removes circular references.
	 *
	 * @see _wp_json_sanity_check()
	 *
	 * @see wp_json_encode()
	 *
	 * @param mixed $var   Variable to be clean for json encoding.
	 * @param int   $depth Maximum depth that the cleaner should allow into the variable. Defaults to 10.
	 *                     Data beyond the maximum depth will be replaces with a string representation (like 'array(...)').
	 *
	 * @return mixed
	 */
	public static function clean( $var, $depth = 10 ) {
		self::$_objects = [];
		self::$_depth   = $depth;

		return self::cleanInternal( $var, 0 );
	}

	private static function cleanInternal( $var, $level ) {
		switch ( gettype( $var ) ) {
			case 'string':
				return _wp_json_convert_string( $var );
			case 'resource':
				return '{resource}';
			case 'unknown type':
				return '{unknown}';
			case 'array':
				// Reached the max depth. Replace with a string representation.
				if ( self::$_depth <= $level ) {
					return 'array(...)';
				}

				if ( empty( $var ) ) {
					return [];
				}

				$output = [];
				foreach ( $var as $key => $value ) {
					// Don't forget to sanitize the $key!
					if ( is_string( $key ) ) {
						$clean_key = _wp_json_convert_string( $key );
					} else {
						$clean_key = $key;
					}

					// Check the element type, so that we're only recursing if we really have to.
					if ( is_array( $value ) || is_object( $value ) ) {
						$output[ $clean_key ] = self::cleanInternal( $value, $level + 1 );
					} elseif ( is_string( $value ) ) {
						$output[ $clean_key ] = _wp_json_convert_string( $value );
					} else {
						$output[ $clean_key ] = $value;
					}
				}

				return $output;
			case 'object':
				// This object reference was seen before. Replace it with a string representation.
				if ( ( $id = array_search( $var, self::$_objects, true ) ) !== false ) {
					return get_class( $var ) . '#' . ( $id + 1 ) . '(...)';
				}

				// Reached the max depth. Replace with a string representation.
				if ( self::$_depth <= $level ) {
					return get_class( $var ) . '(...)';
				}

				$output                        = new \stdClass();
				$output->__original_class_name = get_class( $var );
				array_push( self::$_objects, $var );
				$members = (array) $var;
				foreach ( $members as $key => $value ) {
					if ( is_string( $key ) ) {
						// Since the array cast will prepend an * guarded by null bytes, we need to clean.
						if ( false !== strpos( $key, "\0*\0" ) ) {
							$key = trim( str_replace( "\0*\0", '', $key ) );
						}
						if ( false !== strpos( $key, "\0" ) ) {
							$key = trim( str_replace( "\0", '*', $key ) );
						}
						$clean_key = _wp_json_convert_string( $key );
					} else {
						$clean_key = $key;
					}

					if ( is_array( $value ) || is_object( $value ) ) {
						$output->$clean_key = self::cleanInternal( $value, $level + 1 );
					} elseif ( is_string( $value ) ) {
						$output->$clean_key = _wp_json_convert_string( $value );
					} else {
						$output->$clean_key = $value;
					}
				}

				return $output;
			default:
				break;
		}

		return $var;
	}
}

