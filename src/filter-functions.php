<?php
/**
 * Functions that can be used in the Customify config for filtering values.
 *
 * Think modifying colors, etc.
 */

if ( ! function_exists( 'pixcloud_adjust_color_brightness' ) ) {
	/**
	 * Adjust a hex color brightness
	 * Allows us to create hover styles for custom link colors
	 *
	 * Taken from the Storefront theme by Automattic: https://github.com/woocommerce/storefront
	 *
	 * @param string  $hex   hex color e.g. #111111.
	 * @param integer $steps factor by which to brighten/darken ranging from -255 (darken) to 255 (brighten).
	 *
	 * @return string        brightened/darkened hex color
	 */
	function pixcloud_adjust_color_brightness( $hex, $steps ) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter.
		$steps = max( - 255, min( 255, $steps ) );

		// Format the hex color string.
		$hex = str_replace( '#', '', $hex );

		if ( 3 == strlen( $hex ) ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		// Get decimal values.
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		// Adjust number of steps and keep it inside 0 to 255.
		$r = max( 0, min( 255, $r + $steps ) );
		$g = max( 0, min( 255, $g + $steps ) );
		$b = max( 0, min( 255, $b + $steps ) );

		$r_hex = str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT );
		$g_hex = str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT );
		$b_hex = str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );

		return '#' . $r_hex . $g_hex . $b_hex;
	}
}

if ( ! function_exists( 'pixcloud_negate' ) ) {
	/**
	 * Negate the value.
	 *
	 * @param int|float $value
	 *
	 * @return int|float
	 */
	function pixcloud_negate( $value ) {
		if ( ! is_numeric( $value ) ) {
			return $value;
		}

		return - $value;
	}
}

if ( ! function_exists( 'pixcloud_min' ) ) {
	/**
	 * Ensure that value is at least the $min value.
	 *
	 * @param int|float $value
	 * @param int|float $min
	 *
	 * @return int|float
	 */
	function pixcloud_min( $value, $min ) {
		if ( ! is_numeric( $value ) || ! is_numeric( $min ) ) {
			return $value;
		}

		if ( $value < $min ) {
			return $min;
		}

		return $value;
	}
}

if ( ! function_exists( 'pixcloud_max' ) ) {
	/**
	 * Ensure that value is at most the $max value.
	 *
	 * @param int|float $value
	 * @param int|float $max
	 *
	 * @return int|float
	 */
	function pixcloud_max( $value, $max ) {
		if ( ! is_numeric( $value ) || ! is_numeric( $max ) ) {
			return $value;
		}

		if ( $value > $max ) {
			return $max;
		}

		return $value;
	}
}

if ( ! function_exists( 'pixcloud_min_max' ) ) {
	/**
	 * Ensure that value is between $min and $max.
	 *
	 * @param int|float $value
	 * @param int|float $min
	 * @param int|float $max
	 *
	 * @return int|float
	 */
	function pixcloud_min_max( $value, $min, $max ) {
		if ( ! is_numeric( $value ) || ! is_numeric( $min ) || ! is_numeric( $max ) ) {
			return $value;
		}

		if ( $value < $min ) {
			$value = $min;
		}

		if ( $value > $max ) {
			$value = $max;
		}

		return $value;
	}
}

if ( ! function_exists( 'pixcloud_add' ) ) {
	/**
	 * Add something to the value.
	 *
	 * @param int|float $value
	 * @param int|float $add
	 *
	 * @return int|float
	 */
	function pixcloud_add( $value, $add ) {
		if ( ! is_numeric( $value ) || ! is_numeric( $add ) ) {
			return $value;
		}

		return $value + $add;
	}
}

if ( ! function_exists( 'pixcloud_substract' ) ) {
	/**
	 * Substract something from the value.
	 *
	 * @param int|float $value
	 * @param int|float $substract
	 *
	 * @return int|float
	 */
	function pixcloud_substract( $value, $substract ) {
		if ( ! is_numeric( $value ) || ! is_numeric( $substract ) ) {
			return $value;
		}

		return $value - $substract;
	}
}

if ( ! function_exists( 'pixcloud_multiply' ) ) {
	/**
	 * Multiply the value.
	 *
	 * @param int|float $value
	 * @param int|float $multiply
	 *
	 * @return int|float
	 */
	function pixcloud_multiply( $value, $multiply ) {
		if ( ! is_numeric( $value ) || ! is_numeric( $multiply ) ) {
			return $value;
		}

		return $value * $multiply;
	}
}

if ( ! function_exists( 'pixcloud_divide' ) ) {
	/**
	 * Divide the value.
	 *
	 * @param int|float $value
	 * @param int|float $divide
	 *
	 * @return int|float
	 */
	function pixcloud_divide( $value, $divide ) {
		if ( ! is_numeric( $value ) || ! is_numeric( $divide ) || empty( $divide ) ) {
			return $value;
		}

		return $value / $divide;
	}
}

if ( ! function_exists( 'pixcloud_modulo' ) ) {
	/**
	 * Divide the value and get the remainder.
	 *
	 * @param int|float $value
	 * @param int|float $divide
	 *
	 * @return int|float
	 */
	function pixcloud_modulo( $value, $divide ) {
		if ( ! is_numeric( $value ) || ! is_numeric( $divide ) || empty( $divide ) ) {
			return $value;
		}

		return $value % $divide;
	}
}
