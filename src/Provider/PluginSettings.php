<?php
/**
 * Plugin settings class.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Provider;

/**
 * Provides the plugin settings values.
 *
 * @since 3.0.0
 */
class PluginSettings {

	const OPTION_NAME = 'pixelgrade_customify_settings';

	/**
	 * Retrieve a setting.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key     Setting name.
	 * @param mixed  $default Optional. Default setting value.
	 *
	 * @return mixed
	 */
	public function get( string $key, $default = null ) {
		$options = get_option( self::OPTION_NAME, [] );

		return $options[ $key ] ?? $default;
	}

	/**
	 * Retrieve all settings.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_all(): array {
		return get_option( self::OPTION_NAME, [] );
	}

	/**
	 * Set a setting value.
	 *
	 * @since 3.0.0
	 *
	 * @param string $key     Setting name.
	 * @param mixed  $value Optional. If given null, will delete the setting entry from the array.
	 *
	 * @return bool
	 */
	public function set( string $key, $value = null ): bool {
		$settings = get_option( self::OPTION_NAME, [] );

		if ( null === $value ) {
			if ( isset( $settings[ $key ] ) ) {
				unset( $settings[ $key ] );
			}
		} else {
			$settings[ $key ] = $value;
		}

		return $this->set_all( $settings );
	}

	/**
	 * Set all settings in one go.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $settings
	 *
	 * @return bool
	 */
	public function set_all( array $settings ): bool {
		return update_option( self::OPTION_NAME, $settings, true );
	}
}
