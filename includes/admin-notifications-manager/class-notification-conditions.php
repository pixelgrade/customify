<?php

/**
 *
 * A class to handle the conditions of notifications.
 *
 * These conditions are in the format provided by jQuery QueryBuilder.
 */
class Pixcloud_Notification_Conditions {

	protected static $all_operators = array(
		'equal', 'not_equal', 'is_empty', 'is_not_empty',
		'less', 'less_or_equal', 'greater', 'greater_or_equal',
		'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with',
		'in', 'not_in', 'any', 'all',
		'between', 'not_between',
	);

	protected static $unary_operators = array(
		'is_empty', 'is_not_empty',
	);

	protected static $binary_operators = array(
		'equal', 'not_equal',
		'less', 'less_or_equal', 'greater', 'greater_or_equal',
		'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with',
	);

	protected static $ternary_operators = array(
		'between', 'not_between',
	);


	protected static $list_operators = array(
		'in', 'not_in', 'any', 'all',
	);

	protected static $active_theme_details = null;

	/**
	 * @param $conditions
	 *
	 * @return bool|mixed|void
	 */
	public static function process( $conditions ) {
		// First check if the conditions are valid.
		// On invalid conditions we return true.
		if ( empty( $conditions['valid'] ) ) {
			return apply_filters( 'pixcloud_processed_conditions', true, $conditions );
		}

		return self::process_group( $conditions );
	}

	/**
	 * @param $group_conditions
	 *
	 * @return bool
	 */
	public static function process_group( $group_conditions ) {
		// By default we will use the AND relation among group rules or subgroups.
		$group_relation = 'AND';
		if ( ! empty( $group_conditions['condition'] ) && in_array( $group_conditions['condition'], array( 'AND', 'OR', ) ) ) {
			$group_relation = $group_conditions['condition'];
		}

		if ( empty( $group_conditions['rules'] ) || ! is_array( $group_conditions['rules'] ) ) {
			return true;
		}

		switch ( $group_relation ) {
			case 'AND':
				// By default we assure that the conditions evaluate to true.
				$result = true;
				break;
			case 'OR':
				// By default we assure that the conditions evaluate to false.
				$result = false;
				break;
			default:
				$result = false;
				break;
		}

		foreach ( $group_conditions['rules'] as $rule ) {
			// Determine if it is a simple rule or a subgroup.
			if ( ! empty( $rule['rules'] ) ) {
				$rule_result = self::process_group( $rule );
			} else {
				$rule_result = self::process_rule( $rule );
			}

			// Now evaluate the rule result according to the group relation.
			switch ( $group_relation ) {
				case 'AND':
					if ( false === $rule_result ) {
						// Stop the evaluation.
						return $rule_result;
					}
					break;
				case 'OR':
					if ( true === $rule_result ) {
						// Stop the evaluation.
						return $rule_result;
					}
					break;
				default:
					// We should not reach here but just in case.
					return $result;
					break;
			}
		}

		return $result;
	}

	/**
	 * @param $rule
	 *
	 * @return bool
	 */
	public static function process_rule( $rule ) {
		$result = true;

		// First validate the rule, just in case. On anything invalid we will return true.
		if ( empty( $rule['id'] ) ) {
			return $result;
		}
		if ( empty( $rule['operator'] ) || ! in_array( $rule['operator'], self::$all_operators ) ) {
			return $result;
		}

		// Check the number of values a operator can handle.
		if ( in_array( $rule['operator'], self::$unary_operators ) && is_array( $rule['value'] ) && count( $rule['value'] ) > 1 ) {
			return $result;
		}
		if ( in_array( $rule['operator'], self::$binary_operators ) && is_array( $rule['value'] ) && count( $rule['value'] ) > 1 ) {
			return $result;
		}
		if ( in_array( $rule['operator'], self::$ternary_operators ) && ( ! is_array( $rule['value'] ) || count( $rule['value'] ) != 2 ) ) {
			return $result;
		}
		if ( in_array( $rule['operator'], self::$list_operators ) && ! is_array( $rule['value'] ) ) {
			return $result;
		}

		// Now determine the field value (the dynamic part of the rule).
		if ( ! method_exists( __CLASS__, 'get_' . $rule['id'] ) ) {
			return $result;
		}
		$field_value = call_user_func( array( __CLASS__, 'get_' . $rule['id'] ), $rule );
		// Make sure that we work with the provided field type.
		$field_value = self::convert_field_value_to_type( $field_value, $rule );

		return $result;
	}

	/*
	 * THE FIELD VALUES GETTERS
	 */

	public static function get_style_manager_is_supported( $rule ) {
		if ( class_exists( 'Customify_Style_Manager' ) && Customify_Style_Manager::instance()->is_supported() ) {
			return true;
		}

		return false;
	}

	public static function get_style_manager_user_provided_feedback( $rule ) {
		if ( class_exists( 'Customify_Style_Manager' ) && Customify_Style_Manager::instance()->user_provided_feedback() ) {
			return true;
		}

		return false;
	}

	public static function get_style_manager_user_provided_feedback_days_ago( $rule ) {
		$user_provided_feedback = get_option( 'style_manager_user_feedback_provided' );
		if ( empty( $user_provided_feedback ) ) {
			return false;
		}

		return round( ( time() - $user_provided_feedback ) / DAY_IN_SECONDS );
	}

	public static function get_current_color_palette_hashid( $rule ) {
		if ( class_exists('Customify_Color_Palettes') ) {
			return Customify_Color_Palettes::instance()->get_current_palette();
		}

		return '';
	}

	public static function get_current_color_palette_is_custom( $rule ) {
		if ( class_exists('Customify_Color_Palettes') ) {
			return Customify_Color_Palettes::instance()->is_using_custom_palette();
		}

		return false;
	}

	public static function get_current_color_palette_is_variation_in_use( $rule ) {
		if ( class_exists('Customify_Color_Palettes') ) {
			return Customify_Color_Palettes::instance()->get_current_palette_variation();
		}

		return false;
	}

	public static function get_active_theme_slug( $rule ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['slug'] ) ) {
			return $theme_details['slug'];
		}

		return '';
	}

	public static function get_active_theme_hashid( $rule ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['hashid'] ) ) {
			return $theme_details['hashid'];
		}

		return '';
	}

	public static function get_active_theme_name( $rule ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['name'] ) ) {
			return $theme_details['name'];
		}

		return '';
	}

	public static function get_active_theme_author( $rule ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['author'] ) ) {
			return $theme_details['author'];
		}

		return '';
	}

	public static function get_active_theme_has_wupdates_valid_code( $rule ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['wupdates_code_unchanged'] ) ) {
			return true;
		}

		return false;
	}

	public static function get_active_theme_has_pixelgrade_license( $rule ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['license_hash'] ) ) {
			return true;
		}

		return false;
	}

	public static function get_active_theme_pixelgrade_license_status( $rule ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['license_status'] ) ) {
			return $theme_details['license_status'];
		}

		return '';
	}

	public static function get_active_theme_version( $rule ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['version'] ) ) {
			return $theme_details['version'];
		}

		return '0.0.1';
	}

	public static function get_customify_version( $rule ) {
		if ( function_exists( 'PixCustomifyPlugin' ) ) {
			return PixCustomifyPlugin()->get_version();
		}

		return false;
	}

	public static function get_style_manager_version( $rule ) {
		if ( function_exists( 'StyleManager_Plugin' ) ) {
			return StyleManager_Plugin()->get_version();
		}

		return false;
	}

	public static function get_wp_version( $rule ) {
		return get_bloginfo( 'version' );
	}

	public static function get_php_version( $rule ) {
		if ( function_exists( 'phpversion' ) ) {
			return phpversion();
		}

		return false;
	}

	public static function get_current_user_role( $rule ) {
		$current_user = wp_get_current_user();

		if ( ! empty( $current_user ) && ! is_wp_error( $current_user ) ) {
			return $current_user->roles;
		}

		return false;
	}

	public static function get_current_user_capabilities( $rule ) {
		$current_user = wp_get_current_user();

		if ( ! empty( $current_user ) && ! is_wp_error( $current_user ) ) {
			return $current_user->allcaps;
		}

		return false;
	}

	public static function get_site_is_public( $rule ) {
		// Local/development url parts to match for
		$devsite_needles = array(
			'localhost',
			':8888',
			'.local',
			'pixelgrade.dev',
			'.dev',
			':8082',
			'staging.',
		);

		if ( self::string_contains_any( get_bloginfo( 'url'), $devsite_needles ) ) {
			return false;
		}

		return  true;
	}

	public static function get_site_url( $rule ) {
		return get_bloginfo( 'url');
	}

	public static function get_site_is_multisite( $rule ) {
		return is_multisite();
	}

	public static function get_site_number_of_posts( $rule ) {
		return wp_count_posts( 'post' );
	}

	public static function get_site_number_of_pages( $rule ) {
		return wp_count_posts( 'page' );
	}

	public static function get_current_date( $rule ) {
		return date('Y/m/d');
	}
	// There are special.
	public static function get_class_exists( $rule ) {
		return null;
	}

	public static function get_function_exists( $rule ) {
		return null;
	}

	/*
	 * HELPERS
	 */

	/**
	 * @param $value
	 * @param $rule
	 *
	 * @return false|float|int|string
	 */
	public static function convert_field_value_to_type( $value, $rule ) {
		if ( ! empty( $rule['type'] ) ) {
			switch ( $rule['type'] ) {
				case 'integer':
					$value = (int) $value;
					break;
				case 'string':
					$value = (string) $value;
					break;
				case 'double':
					$value = (double) $value;
					break;
				case 'date':
					$value = date('Y/m/d', strtotime( $value ) );
					break;
				case 'time':
					$value = date('H:i:s', strtotime( $value ) );
					break;
				case 'datetime':
					$value = date('Y/m/d H:i:s', strtotime( $value ) );
					break;
				case 'boolean':
					$value = (bool) $value;
					break;
				default:
					break;
			}
		}

		return $value;
	}

	/**
	 * Grab all the details about the current active theme.
	 *
	 * @return array
	 */
	public static function get_active_theme_details() {
		if ( self::$active_theme_details !== null ) {
			return self::$active_theme_details;
		}

		$theme_details = array();

		// Gather Pixelgrade and WUpdates theme details.
		$theme_details['is_pixelgrade_theme'] = self::is_pixelgrade_theme();
		$theme_details['hashid'] = self::get_wupdates_theme_hashid();
		$theme_details['wupdates_code_unchanged'] = self::is_wupdates_code_unchanged();
		$theme_details['license_hash'] = get_theme_mod( 'pixcare_license_hash', false );
		$theme_details['license_status'] = get_theme_mod( 'pixcare_license_status', false );

		// Gather the rest of the theme details.
		/** @var WP_Theme $theme */
		$theme = wp_get_theme();
		$parent = $theme->parent();
		if ( is_child_theme() && ! empty( $parent ) ) {
			$theme = $parent;
		}

		// The theme name should be the one from the wupdates array.
		$wupdates_theme_name = self::get_original_theme_name();
		if ( ! empty( $wupdates_theme_name ) ) {
			$theme_details['name'] = $wupdates_theme_name;
		}
		// If for some reason we couldn't get the theme name from the WUpdates code, use the standard theme name.
		if ( empty( $theme_details['name'] ) ) {
			$theme_details['name'] = $theme->get( 'Name' );
		}

		// The theme slug should be the one from the wupdates array
		$wupdates_theme_slug = self::get_original_theme_slug();
		if ( ! empty( $wupdates_theme_slug ) ) {
			$theme_details['slug'] = $wupdates_theme_slug;
		}
		// If for some reason we couldn't get the theme slug from the WUpdates code, use the standard theme slug.
		if ( empty( $theme_details['slug'] ) ) {
			$theme_details['slug'] = basename( get_template_directory() );
		}

		$theme_details['uri'] = $theme->get( 'ThemeURI' );
		$theme_details['desc'] = $theme->get( 'Description' );
		$theme_details['author'] = $theme->get( 'Author' );
		$theme_details['version'] = $theme->get( 'Version' );

		$theme_details['is_child'] = is_child_theme();
		$theme_details['template'] = $theme->get_template();

		self::$active_theme_details = $theme_details;

		return $theme_details;
	}

	/**
	 * Determine if the current theme is one of ours.
	 *
	 * @return bool
	 */
	public static function is_pixelgrade_theme() {
		// Get the id of the current theme
		$wupdates_ids = apply_filters( 'wupdates_gather_ids', array() );
		$slug         = basename( get_template_directory() );
		// If we have the WUpdates information tied to the current theme slug, then we are good
		if ( isset( $wupdates_ids[ $slug ] ) ) {
			return true;
		}

		// Next we will test for the author in the theme header
		$theme = wp_get_theme();
		$theme_author = $theme->get( 'Author' );
		if ( ! empty( $theme_author ) && strtolower( $theme_author ) == 'pixelgrade' ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the wupdates_gather_ids code has been tempered with.
	 *
	 * @return bool
	 */
	public static function is_wupdates_code_unchanged() {
		// Get the id of the current theme
		$wupdates_ids = apply_filters( 'wupdates_gather_ids', array() );
		$slug         = basename( get_template_directory() );
		// If the user hasn't got any pixelgrade themes - return true. They don't need this filter
		if ( ! self::has_pixelgrade_theme() ) {
			return true;
		}

		// Check if the wupdates_ids array is missing either of this properties
		if ( ! isset( $wupdates_ids[ $slug ] ) || ! isset( $wupdates_ids[ $slug ]['name'] ) || ! isset( $wupdates_ids[ $slug ]['slug'] ) || ! isset( $wupdates_ids[ $slug ]['id'] ) || ! isset( $wupdates_ids[ $slug ]['type'] ) || ! isset( $wupdates_ids[ $slug ]['digest'] ) ) {
			return false;
		}
		// Create the md5 hash from the properties of wupdates_ids and compare it to the digest from that array
		$md5 = md5( 'name-' . $wupdates_ids[ $slug ]['name'] . ';slug-' . $wupdates_ids[ $slug ]['slug'] . ';id-' . $wupdates_ids[ $slug ]['id'] . ';type-' . $wupdates_ids[ $slug ]['type'] );
		// the md5 hash should be the same one as the digest hash
		if ( $md5 !== $wupdates_ids[ $slug ]['digest'] ) {
			return false;
		}
		return true;
	}

	/**
	 * Determine if there are any Pixelgrade themes currently installed.
	 *
	 * @return bool
	 */
	public static function has_pixelgrade_theme() {
		$themes = wp_get_themes();
		// Loop through the themes.
		// If we find a theme from pixelgrade return true.
		/** @var WP_Theme $theme */
		foreach ( $themes as $theme ) {
			$theme_author = $theme->get( 'Author' );

			if ( ! empty( $theme_author ) && strtolower( $theme_author ) == 'pixelgrade' ) {
				return true;
			}
		}

		// No themes from pixelgrade found, return false.
		return false;
	}

	/**
	 * Get the current theme original name from the WUpdates code.
	 *
	 * @return string
	 */
	public static function get_original_theme_name() {
		// Get the id of the current theme
		$wupdates_ids = apply_filters( 'wupdates_gather_ids', array() );
		$slug         = basename( get_template_directory() );
		if ( ! isset( $wupdates_ids[ $slug ] ) || ! isset( $wupdates_ids[ $slug ]['name'] ) ) {
			return ucfirst( $slug );
		}
		return $wupdates_ids[ $slug ]['name'];
	}

	/**
	 * Get the current theme original slug from the WUpdates code.
	 *
	 * @return string
	 */
	public static function get_original_theme_slug() {
		// Get the id of the current theme
		$wupdates_ids = apply_filters( 'wupdates_gather_ids', array() );
		$slug         = basename( get_template_directory() );
		if ( ! isset( $wupdates_ids[ $slug ] ) || ! isset( $wupdates_ids[ $slug ]['slug'] ) ) {
			return $slug;
		}

		return sanitize_title( $wupdates_ids[ $slug ]['slug'] );
	}

	/**
	 * Get the current theme hashid from the WUpdates code.
	 *
	 * @return string
	 */
	public static function get_wupdates_theme_hashid() {
		// Get the id of the current theme
		$wupdates_ids = apply_filters( 'wupdates_gather_ids', array() );
		$slug         = basename( get_template_directory() );
		if ( ! isset( $wupdates_ids[ $slug ] ) || ! isset( $wupdates_ids[ $slug ]['id'] ) ) {
			return false;
		}

		return sanitize_title( $wupdates_ids[ $slug ]['id'] );
	}

	/**
	 * Check if the $haystack contains any of the needles.
	 *
	 * @param string $haystack
	 * @param array $needles
	 *
	 * @return bool
	 */
	public static function string_contains_any( $haystack, $needles ) {
		foreach ( $needles as $needle ) {
			if ( false !== strpos( $haystack, $needle ) ) {
				return true;
			}
		}

		return false;
	}
}