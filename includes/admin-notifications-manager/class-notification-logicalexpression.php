<?php

/**
 *
 * A class to handle logical expression for notification conditions.
 *
 * Please note that we only support scalar data types and lists of these data types (only when the operator operates on lists)!
 */
class Pixcloud_Notification_LogicalExpression {

	/**
	 * All the supported operators.
	 *
	 * @var array
	 */
	protected static $all_operators = array(
		'equal', 'not_equal', 'is_empty', 'is_not_empty',
		'less', 'less_or_equal', 'greater', 'greater_or_equal',
		'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with',
		'between', 'not_between',
		'in', 'not_in', 'any', 'all',
	);

	/**
	 * All the unary operators (i.e. work with only the left operand).
	 *
	 * @var array
	 */
	protected static $unary_operators = array(
		'is_empty', 'is_not_empty',
	);

	/**
	 * All the binary operators (i.e. work with left and a single right operand).
	 *
	 * @var array
	 */
	protected static $binary_operators = array(
		'equal', 'not_equal',
		'less', 'less_or_equal', 'greater', 'greater_or_equal',
		'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with',
	);

	/**
	 * All the ternary operators (i.e. work with a left operand and two right operands).
	 *
	 * @var array
	 */
	protected static $ternary_operators = array(
		'between', 'not_between',
	);


	/**
	 * All the list operators (i.e. work with a (list) left operand and (list) right operand).
	 *
	 * @var array
	 */
	protected static $list_operators = array(
		'in', 'not_in', 'any', 'all',
	);

	/**
	 * Evaluate a logical expression with one or two operands and an operator.
	 *
	 * @param mixed $left
	 * @param string $operator
	 * @param mixed $right Optional.
	 *
	 * @return bool|null The logical expression result or null on invalid data.
	 */
	public static function evaluate( $left, $operator, $right = null ) {
		// Reject unknown operators.
		if ( ! in_array( $operator, self::$all_operators ) ) {
			return  null;
		}
		// Now, check the number of values a operator can handle and reject on wrong number.
		if ( in_array( $operator, self::$binary_operators ) && is_array( $right ) && count( $right ) > 1 ) {
			return null;
		}
		if ( in_array( $operator, self::$ternary_operators ) && ( ! is_array( $right ) || count( $right ) != 2 ) ) {
			return null;
		}
		if ( in_array( $operator, self::$list_operators ) && ! is_array( $right ) ) {
			return null;
		}

		// Reject missing operator evaluation method.
		if ( ! method_exists( __CLASS__, 'evaluate_' . $operator ) ) {
			return null;
		}

		return (bool) call_user_func( array( __CLASS__, 'evaluate_' . $operator ), $left, $right );
	}

	/* =============================
	 * EVALUATORS FOR EACH OPERATOR.
	 */

	/**
	 * Determine if the left operand is equal to the right operand. The comparison is strict type.
	 *
	 * @param mixed $left
	 * @param mixed $right
	 *
	 * @return bool
	 */
	public static function evaluate_equal( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return $left === $right;
	}

	/**
	 * Determine if the left operand is not equal to the right operand. The comparison is strict type.
	 *
	 * @param mixed $left
	 * @param mixed $right
	 *
	 * @return bool
	 */
	public static function evaluate_not_equal( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return $left !== $right;
	}

	/**
	 * Determine if the left operand is empty (the php empty() function is applied).
	 *
	 * @param mixed $left
	 * @param mixed $right Optional. Not used.
	 *
	 * @return bool
	 */
	public static function evaluate_is_empty( $left, $right = null ) {
		return empty( $left );
	}

	/**
	 * Determine if the left operand is not empty (the php empty() function is applied).
	 *
	 * @param mixed $left
	 * @param mixed $right Optional. Not used.
	 *
	 * @return bool
	 */
	public static function evaluate_is_not_empty( $left, $right = null ) {
		return ! empty( $left );
	}

	/**
	 * Determine if the left (numeric) operand less than the right (numeric) operand.
	 *
	 * @param int|double|float $left
	 * @param int|double|float $right
	 *
	 * @return bool
	 */
	public static function evaluate_less( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return $left < $right;
	}

	/**
	 * Determine if the left (numeric) operand less or equal than the right (numeric) operand.
	 *
	 * @param int|double|float $left
	 * @param int|double|float $right
	 *
	 * @return bool
	 */
	public static function evaluate_less_or_equal( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return $left <= $right;
	}

	/**
	 * Determine if the left (numeric) operand greater than the right (numeric) operand.
	 *
	 * @param int|double|float $left
	 * @param int|double|float $right
	 *
	 * @return bool
	 */
	public static function evaluate_greater( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return $left > $right;
	}

	/**
	 * Determine if the left (numeric) operand greater or equal than the right (numeric) operand.
	 *
	 * @param int|double|float $left
	 * @param int|double|float $right
	 *
	 * @return bool
	 */
	public static function evaluate_greater_or_equal( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return $left >= $right;
	}

	/**
	 * Determine if the right (string) operand is at start of the left (string) operand.
	 *
	 * @param string $left
	 * @param string $right
	 *
	 * @return bool
	 */
	public static function evaluate_begins_with( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return 0 === strpos( $left, $right );
	}

	/**
	 * Determine if the right (string) operand is not at start of the left (string) operand.
	 *
	 * @param string $left
	 * @param string $right
	 *
	 * @return bool
	 */
	public static function evaluate_not_begins_with( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return 0 !== strpos( $left, $right );
	}

	/**
	 * Determine if the right (string) operand is part of the left (string) operand.
	 *
	 * @param string $left
	 * @param string $right
	 *
	 * @return bool
	 */
	public static function evaluate_contains( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return false !== strpos( $left, $right );
	}

	/**
	 * Determine if the right (string) operand is not part of the left (string) operand.
	 *
	 * @param string $left
	 * @param string $right
	 *
	 * @return bool
	 */
	public static function evaluate_not_contains( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return false === strpos( $left, $right );
	}

	/**
	 * Determine if the right (string) operand is at end of the left (string) operand.
	 *
	 * @param string $left
	 * @param string $right
	 *
	 * @return bool
	 */
	public static function evaluate_ends_with( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return ( strlen( $left ) - strlen( $right ) ) === strrpos( $left, $right );
	}

	/**
	 * Determine if the right (string) operand is not at end of the left (string) operand.
	 *
	 * @param string $left
	 * @param string $right
	 *
	 * @return bool
	 */
	public static function evaluate_not_ends_with( $left, $right ) {
		// Sanity check.
		if ( is_array( $right ) ) {
			$right = array_shift( $right );
		}

		return ( strlen( $left ) - strlen( $right ) ) !== strrpos( $left, $right );
	}

	/**
	 * Determine if the left operand is between in the two values in right one.
	 *
	 * @param $left
	 * @param array $right
	 *
	 * @return bool
	 */
	public static function evaluate_between( $left, $right ) {
		// Get the two values
		$small = array_shift( $right );
		$big = array_shift( $right );

		return ( $small <= $left ) && ( $left <= $big );
	}

	/**
	 * Determine if the left operand is not between in the two values in right one.
	 *
	 * @param $left
	 * @param array $right
	 *
	 * @return bool
	 */
	public static function evaluate_not_between( $left, $right ) {
		// Get the two values
		$small = array_shift( $right );
		$big = array_shift( $right );

		return ! ( ( $small <= $left ) && ( $left <= $big ) );
	}

	/**
	 * Determine if the left operand is present in the right (list) one.
	 *
	 * @param $left
	 * @param array $right
	 *
	 * @return bool
	 */
	public static function evaluate_in( $left, $right ) {
		// Sanity check.
		if ( ! is_array( $right ) ) {
			$right = array( $right );
		}

		return ( in_array( $left, $right ) );
	}

	/**
	 * Determine if the left operand is not present in the right (list) one.
	 *
	 * @param $left
	 * @param array $right
	 *
	 * @return bool
	 */
	public static function evaluate_not_in( $left, $right ) {
		// Sanity check.
		if ( ! is_array( $right ) ) {
			$right = array( $right );
		}

		return ! ( in_array( $left, $right ) );
	}

	/**
	 * Determine if any of the values in the left operand are present in the right one.
	 *
	 * @param array $left
	 * @param array $right
	 *
	 * @return bool
	 */
	public static function evaluate_any( $left, $right ) {
		// Sanity check.
		if ( ! is_array( $left ) ) {
			$left = array( $left );
		}
		if ( ! is_array( $right ) ) {
			$right = array( $right );
		}

		$intersect = array_intersect( $left, $right );

		return ! empty( $intersect );
	}

	/**
	 * Determine if all of the values in the left operand are present in the right one.
	 *
	 * @param array $left
	 * @param array $right
	 *
	 * @return bool
	 */
	public static function evaluate_all( $left, $right ) {
		// Sanity check.
		if ( ! is_array( $left ) ) {
			$left = array( $left );
		}
		if ( ! is_array( $right ) ) {
			$right = array( $right );
		}

		$intersect = array_intersect( $left, $right );

		return count( $intersect ) === count( $left );
	}

	/* =======
	 * HELPERS
	 */
}
