<?php
/**
 * This is a utility class that groups all our scripts enqueue related helpers.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Utils;

/**
 * Our scripts enqueue related helpers class.
 *
 * @since   3.0.0
 * @package Pixelgrade Customify
 */
class ScriptsEnqueue {
	/**
	 * Return a script for flexibly localizing data to a window property.
	 *
	 * Unlike wp_localize_script() that simply creates a variable and assigns it the value,
	 * thus overwriting anything that may have been in that variable, we will output a script that
	 * will test if the variable exists and only overwrite the first level nodes, not everything.
	 *
	 * @since 2.7.0
	 *
	 * @param string $object_name Name of the variable that will contain the data.
	 * @param array  $l10n        Array of data to localize.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function getlocalizeToWindowScript( string $object_name, array $l10n ) {
		$script = "window.$object_name = window.$object_name || parent.$object_name || {};\n";

		foreach ( (array) $l10n as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$value = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}

			$script .= "$object_name.$key = " . wp_json_encode( $value ) . ";\n";
		}

		return $script;
	}
}

