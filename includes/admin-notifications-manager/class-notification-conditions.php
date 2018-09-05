<?php

/**
 *
 * A class to handle the conditions of notifications.
 *
 * These conditions are in the format provided by jQuery QueryBuilder.
 */
class Pixcloud_Notification_Conditions {

	protected static $group_relations = array(
		'AND', 'OR',
	);

	protected static $active_theme_details = null;

	/**
	 * Process a notification's conditions.
	 *
	 * @param array $conditions
	 *
	 * @return bool|mixed
	 */
	public static function process( $conditions ) {
		// First check if the conditions are valid.
		// On invalid conditions we return true.
		if ( empty( $conditions['valid'] ) ) {
			return apply_filters( 'pixcloud_notification_conditions_result', true, $conditions );
		}

		// Process the group. Any top level conditions are expected to be a group, not an individual rule.
		$result = self::process_group( $conditions );

		return apply_filters( 'pixcloud_notification_conditions_result', $result, $conditions );
	}

	/**
	 * Process and evaluate a notification condition group.
	 *
	 * @param array $group_conditions
	 *
	 * @return bool
	 */
	public static function process_group( $group_conditions ) {
		// By default we will use the AND relation among group rules or subgroups.
		$group_relation = 'AND';
		if ( ! empty( $group_conditions['condition'] ) && in_array( $group_conditions['condition'], self::$group_relations ) ) {
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

		$stop = false;
		foreach ( $group_conditions['rules'] as $rule ) {
			// Determine if it is a simple rule or a subgroup.
			if ( ! empty( $rule['rules'] ) ) {
				$result = self::process_group( $rule );
			} else {
				$result = self::process_rule( $rule );
			}

			// Now evaluate the rule result according to the group relation.
			switch ( $group_relation ) {
				case 'AND':
					if ( false === $result ) {
						// Stop the evaluation.
						$stop = true;
					}
					break;
				case 'OR':
					if ( true === $result ) {
						// Stop the evaluation.
						$stop = true;
					}
					break;
				default:
					// We should not reach here but just in case.
					$stop = true;
					break;
			}

			// Stop the rules processing if this is the case.
			if ( true === $stop ) {
				break;
			}
		}

		return apply_filters( 'pixcloud_notification_conditions_group_result', $result, $group_conditions['rules'], $group_relation, $group_conditions );
	}

	/**
	 * Process and evaluate a notification condition rule.
	 *
	 * @param array $rule
	 *
	 * @return bool
	 */
	public static function process_rule( $rule ) {
		$result = true;

		// First validate the rule, just in case. On anything invalid we will return true.
		if ( empty( $rule['id'] ) ) {
			return $result;
		}
		if ( empty( $rule['operator'] ) ) {
			return $result;
		}

		if ( ! isset( $rule['value'] ) ) {
			$rule['value'] = null;
		}

		// Now determine the field value (the dynamic part of the rule).
		if ( ! method_exists( __CLASS__, 'get_' . $rule['id'] ) ) {
			return $result;
		}
		$field_value = call_user_func( array( __CLASS__, 'get_' . $rule['id'] ), $rule );
		// Make sure that we work with the provided field type, regardless if it is a single value or a list.
		$field_value = self::convert_value_to_type( $field_value, $rule['type'] );
		$rule['value'] = self::convert_value_to_type( $rule['value'], $rule['type'] );

		// Before we evaluate the expression, we need to account for the special expressions (e.g. function_exists, class_exists).
		switch ( $rule['id'] ) {
			case 'function_exists':
				// We apply function_exists to each value.
				if ( is_array( $rule['value'] ) ) {
					$rule['value'] = array_map( 'function_exists', $rule['value'] );
				} else {
					$rule['value'] = function_exists( $rule['value'] );
				}

				// Make sure that the field value is true.
				$field_value = true;
				break;
			case 'class_exists':
				// We apply function_exists to each value.
				if ( is_array( $rule['value'] ) ) {
					$rule['value'] = array_map( 'class_exists', $rule['value'] );
				} else {
					$rule['value'] = class_exists( $rule['value'] );
				}

				// Make sure that the field value is true.
				$field_value = true;
				break;
			default:
				break;
		}

		// Now evaluate the expression.
		require_once 'class-notification-logicalexpression.php';
		$result = Pixcloud_Notification_LogicalExpression::evaluate( $field_value, $rule['operator'], $rule['value'] );

		return apply_filters( 'pixcloud_notification_conditions_rule_result', $result, $field_value, $rule['operator'], $rule['value'], $rule );
	}

	public static function evaluate_expression( $left, $operator, $right, $rule ) {

	}

	/* ========================
	 * THE FIELD VALUES GETTERS
	 */

	public static function get_style_manager_is_supported( $rule = null ) {
		if ( class_exists( 'Customify_Style_Manager' ) && Customify_Style_Manager::instance()->is_supported() ) {
			return true;
		}

		return false;
	}

	public static function get_style_manager_user_provided_feedback( $rule = null ) {
		if ( class_exists( 'Customify_Style_Manager' ) && Customify_Style_Manager::instance()->user_provided_feedback() ) {
			return true;
		}

		return false;
	}

	public static function get_style_manager_user_provided_feedback_days_ago( $rule = null ) {
		$user_provided_feedback = get_option( 'style_manager_user_feedback_provided' );
		if ( empty( $user_provided_feedback ) ) {
			return false;
		}

		return round( ( time() - $user_provided_feedback ) / DAY_IN_SECONDS );
	}

	public static function get_current_color_palette_hashid( $rule = null ) {
		if ( class_exists('Customify_Color_Palettes') ) {
			return Customify_Color_Palettes::instance()->get_current_palette();
		}

		return '';
	}

	public static function get_current_color_palette_label( $rule = null ) {
		if ( class_exists('Customify_Color_Palettes') ) {
			$color_palette_hashid = self::get_current_color_palette_hashid( $rule );
			$color_palettes = Customify_Color_Palettes::instance()->get_palettes();
			if ( ! empty( $color_palettes[ $color_palette_hashid ] ) ) {
				return $color_palettes[ $color_palette_hashid ]['label'];
			}
		}

		return '';
	}

	public static function get_current_color_palette_is_custom( $rule = null ) {
		if ( class_exists('Customify_Color_Palettes') ) {
			return Customify_Color_Palettes::instance()->is_using_custom_palette();
		}

		return false;
	}

	public static function get_current_color_palette_is_variation_in_use( $rule = null ) {
		if ( class_exists('Customify_Color_Palettes') ) {
			return Customify_Color_Palettes::instance()->get_current_palette_variation();
		}

		return false;
	}

	public static function get_active_theme_slug( $rule = null ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['slug'] ) ) {
			return $theme_details['slug'];
		}

		return '';
	}

	public static function get_active_theme_hashid( $rule = null ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['hashid'] ) ) {
			return $theme_details['hashid'];
		}

		return '';
	}

	public static function get_active_theme_name( $rule = null ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['name'] ) ) {
			return $theme_details['name'];
		}

		return '';
	}

	public static function get_active_theme_author( $rule = null ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['author'] ) ) {
			return $theme_details['author'];
		}

		return '';
	}

	public static function get_active_theme_has_wupdates_valid_code( $rule = null ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['wupdates_code_unchanged'] ) ) {
			return true;
		}

		return false;
	}

	public static function get_active_theme_has_pixelgrade_license( $rule = null ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['license_hash'] ) ) {
			return true;
		}

		return false;
	}

	public static function get_active_theme_pixelgrade_license_status( $rule = null ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['license_status'] ) ) {
			return $theme_details['license_status'];
		}

		return '';
	}

	public static function get_active_theme_version( $rule = null ) {
		$theme_details = self::get_active_theme_details();

		if ( ! empty( $theme_details['version'] ) ) {
			return $theme_details['version'];
		}

		return '0.0.1';
	}

	public static function get_customify_version( $rule = null ) {
		if ( function_exists( 'PixCustomifyPlugin' ) ) {
			return PixCustomifyPlugin()->get_version();
		}

		return false;
	}

	public static function get_style_manager_version( $rule = null ) {
		if ( function_exists( 'StyleManager_Plugin' ) ) {
			return StyleManager_Plugin()->get_version();
		}

		return false;
	}

	public static function get_wp_version( $rule = null ) {
		return get_bloginfo( 'version' );
	}

	public static function get_php_version( $rule = null ) {
		if ( function_exists( 'phpversion' ) ) {
			return phpversion();
		}

		return false;
	}

	public static function get_current_user_role( $rule = null ) {
		$current_user = wp_get_current_user();

		if ( ! empty( $current_user ) && ! is_wp_error( $current_user ) ) {
			return $current_user->roles;
		}

		return false;
	}

	public static function get_current_user_capabilities( $rule = null ) {
		$current_user = wp_get_current_user();

		if ( ! empty( $current_user ) && ! is_wp_error( $current_user ) ) {
			return $current_user->allcaps;
		}

		return false;
	}

	public static function get_site_is_public( $rule = null ) {
		// Local/development url parts to match for
		$devsite_needles = array(
			'localhost',
			':8888',
			'.local',
			'.dev',
			':8082',
			'staging.',
			'.invalid',
			'.test',
			'.example',
		);

		if ( self::string_contains_any( get_bloginfo( 'url'), $devsite_needles ) ) {
			return false;
		}

		return  true;
	}

	public static function get_site_url( $rule = null ) {
		return get_bloginfo( 'url');
	}

	public static function get_site_is_multisite( $rule = null ) {
		return is_multisite();
	}

	public static function get_site_number_of_posts( $rule = null ) {
		// Make sure it is an array.
		$post_count = json_decode( json_encode( wp_count_posts( 'post' ) ), true );
		return ! empty( $post_count['publish'] ) ? $post_count['publish'] : 0;
	}

	public static function get_site_number_of_pages( $rule = null ) {
		// Make sure it is an array.
		$post_count = json_decode( json_encode( wp_count_posts( 'page' ) ), true );
		return ! empty( $post_count['publish'] ) ? $post_count['publish'] : 0;
	}

	public static function get_current_date( $rule = null ) {
		return date('Y/m/d');
	}

	// This is special.
	public static function get_class_exists( $rule = null ) {
		return true;
	}

	// This is special.
	public static function get_function_exists( $rule = null ) {
		return true;
	}

	public static function get_wp_debug_active( $rule = null ) {
		return defined( 'WP_DEBUG') && true === WP_DEBUG;
	}

	public static function get_pixelgrade_dev_mode_active( $rule = null ) {
		return defined( 'PIXELGRADE_CARE__DEV_MODE') && true === PIXELGRADE_CARE__DEV_MODE;
	}

	public static function get_customify_dev_force_defaults_active( $rule = null ) {
		return defined( 'CUSTOMIFY_DEV_FORCE_DEFAULTS') && true === CUSTOMIFY_DEV_FORCE_DEFAULTS;
	}

	public static function get_sm_dev_customizer_force_defaults_active( $rule = null ) {
		return defined( 'SM_DEV_CUSTOMIZER_FORCE_DEFAULTS') && true === SM_DEV_CUSTOMIZER_FORCE_DEFAULTS;
	}

	/* =======
	 * HELPERS
	 */

	/**
	 * @param $value
	 * @param $type
	 *
	 * @return false|float|int|string
	 */
	public static function convert_value_to_type( $value, $type ) {
		if ( null === $value ) {
			return $value;
		}

		// Make sure we are not dealing with stdClass.
		if ( $value instanceof stdClass ) {
			$value = json_decode( json_encode( $value ), true );
		}

		if ( ! empty( $type ) ) {
			switch ( $type ) {
				case 'integer':
					if ( is_array( $value ) ) {
						$value = array_map( 'intval', $value );
					} else {
						$value = intval( $value );
					}
					break;
				case 'string':
					if ( is_array( $value ) ) {
						$value = array_map( 'strval', $value );
					} else {
						$value = strval( $value );
					}
					break;
				case 'double':
					if ( is_array( $value ) ) {
						$value = array_map( 'doubleval', $value );
					} else {
						$value = doubleval( $value );
					}
					break;
				case 'date':
					if ( is_array( $value ) ) {
						$value = array_map( 'strtotime', $value );
						$value = array_map( array( __CLASS__, 'dateval' ), $value );
					} else {
						$value = self::dateval( strtotime( $value ) );
					}
					break;
				case 'time':
					if ( is_array( $value ) ) {
						$value = array_map( 'strtotime', $value );
						$value = array_map( array( __CLASS__, 'timeval' ), $value );
					} else {
						$value = self::timeval( strtotime( $value ) );
					}
					break;
				case 'datetime':
					if ( is_array( $value ) ) {
						$value = array_map( 'strtotime', $value );
						$value = array_map( array( __CLASS__, 'datetimeval' ), $value );
					} else {
						$value = self::datetimeval( strtotime( $value ) );
					}
					break;
				case 'boolean':
					if ( is_array( $value ) ) {
						$value = array_map( 'boolval', $value );
					} else {
						$value = boolval( $value );
					}
					break;
				default:
					break;
			}
		}

		return $value;
	}

	protected static function dateval( $timestamp ) {
		return date('Y/m/d', $timestamp );
	}

	protected static function timeval( $timestamp ) {
		return date('H:i:s', $timestamp );
	}

	protected static function datetimeval( $timestamp ) {
		return date('Y/m/d H:i:s', $timestamp );
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

		return $wupdates_ids[ $slug ]['id'];
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