<?php
/**
 * Capabilities.
 *
 * Meta capabilities are mapped to primitive capabilities in
 * \Pixelgrade\Customify\Provider\Capabilities.
 *
 * @package Pixelgrade Customify
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify;

/**
 * Capabilities.
 *
 * @since 3.0.0
 */
final class Capabilities {

	/**
	 * Primitive capability for managing options.
	 *
	 * @var string
	 */
	const MANAGE_OPTIONS = 'pixelgrade_customify_manage_options';

	/**
	 * Register capabilities.
	 *
	 * @since 3.0.0
	 */
	public static function register() {
		$wp_roles = wp_roles();

		$wp_roles->add_cap( 'administrator', self::MANAGE_OPTIONS );
	}
}
