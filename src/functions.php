<?php
/**
 * Helper functions
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify;

/**
 * Retrieve the main plugin instance.
 *
 * @since 3.0.0
 *
 * @return Plugin
 */
function plugin(): Plugin {
	static $instance;
	$instance = $instance ?: new Plugin();

	return $instance;
}

/**
 * Autoload mapped classes.
 *
 * @since 3.0.0
 *
 * @param string $class Class name.
 */
function autoloader_classmap( string $class ) {
	$class_map = [
		'PclZip' => ABSPATH . 'wp-admin/includes/class-pclzip.php',
	];

	if ( isset( $class_map[ $class ] ) ) {
		require_once $class_map[ $class ];
	}
}

/**
 * Generate a random string.
 *
 * @since 3.0.0
 *
 * @param int $length Length of the string to generate.
 *
 * @throws \Exception
 * @return string
 */
function generate_random_string( int $length = 12 ): string {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	$str = '';
	$max = \strlen( $chars ) - 1;
	for ( $i = 0; $i < $length; $i ++ ) {
		$str .= $chars[ random_int( 0, $max ) ];
	}

	return $str;
}

/**
 * Whether a plugin identifier is the main plugin file.
 *
 * Plugins can be identified by their plugin file (relative path to the main
 * plugin file from the root plugin directory) or their slug.
 *
 * This doesn't validate whether or not the plugin actually exists.
 *
 * @since 3.0.0
 *
 * @param string $plugin_file Plugin slug or relative path to the main plugin file.
 *
 * @return bool
 */
function is_plugin_file( string $plugin_file ): bool {
	return '.php' === substr( $plugin_file, - 4 );
}

/**
 * Display a notice about missing dependencies.
 *
 * @since 3.0.0
 */
function display_missing_dependencies_notice() {
	$message = sprintf(
	/* translators: %s: documentation URL */
		__( 'Customify is missing required dependencies. <a href="%s" target="_blank" rel="noopener noreferer">Learn more.</a>', '__plugin_txtd' ),
		'https://github.com/pixelgrade/customify'
	);

	printf(
		'<div class="customify-compatibility-notice notice notice-error"><p>%s</p></div>',
		wp_kses(
			$message,
			[
				'a' => [
					'href'   => true,
					'rel'    => true,
					'target' => true,
				],
			]
		)
	);
}

/**
 * Determine if we are looking at the Customize screen.
 *
 * @return bool
 */
function is_customizer(): bool {
	return ( is_admin() && 'customize.php' === basename( $_SERVER['PHP_SELF'] ) );
}

/**
 * Determine if the Style Manager functionality is supported.
 *
 * @return bool
 */
function is_sm_supported(): bool {
	return apply_filters( 'customify_style_manager_is_supported', current_theme_supports( 'customizer_style_manager' ) );
}

/**
 * Get a Customify option's value, if there is a value, and return it.
 * Otherwise, try to get the default parameter or the default from config.
 *
 * @since 3.0.0
 *
 * @param string     $option_id
 * @param mixed|null $default        Optional.
 * @param array|null $option_details Optional.
 *
 * @return mixed
 */
function get_option( string $option_id, $default = null, $option_details = null ) {
	return plugin()->get_container()->get('options')->get( $option_id, $default, $option_details );
}

/**
 * Get the Customify configuration (and value, hence "details") of a certain option.
 *
 * @since 3.0.0
 *
 * @param string $option_id
 * @param bool   $minimal_details Optional. Whether to return only the minimum amount of details (mainly what is needed on the frontend).
 *                                The advantage is that these details are cached, thus skipping the customizer_config!
 * @param bool   $skip_cache      Optional.
 *
 * @return array|false The option config or false on failure.
 */
function get_option_details( string $option_id, $minimal_details = false, $skip_cache = false ) {
	return plugin()->get_container()->get('options')->get_details( $option_id, $minimal_details, $skip_cache );
}

/**
 * Get all Customify options' details.
 *
 * @since 3.0.0
 *
 * @param bool $only_minimal_details Optional. Whether to return only the minimal details.
 *                                   Defaults to returning all details.
 * @param bool $skip_cache           Optional. Whether to skip the options cache and regenerate.
 *                                   Defaults to using the cache.
 * @return array
 */
function get_option_details_all( $only_minimal_details = false, $skip_cache = false ): array {
	return plugin()->get_container()->get('options')->get_details_all( $only_minimal_details, $skip_cache );
}

/**
 * Determine if a certain option exists.
 *
 * @since 3.0.0
 *
 * @param string $key The option key.
 *
 * @return bool
 */
function has_option( string $key ): bool {
	return plugin()->get_container()->get('options')->has_option( $key );
}

/**
 * Get the key under which all Customify options are saved.
 *
 * @since 3.0.0
 *
 * @param bool $skip_cache Optional. Whether to skip the options cache and regenerate.
 *                         Defaults to using the cache.
 *
 * @return string
 */
function get_options_key( bool $skip_cache = false ): string {
	return plugin()->get_container()->get('options')->get_options_key( $skip_cache );
}

/**
 * Get the entire Customify Customizer fields config or a certain entry key.
 *
 * @since 3.0.0
 *
 * @param bool|string $key
 *
 * @return array|mixed|null
 */
function get_customizer_config( $key = false ) {
	return plugin()->get_container()->get('options')->get_customizer_config( $key );
}
