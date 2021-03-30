<?php
/**
 * Helper functions
 *
 * @package PixelgradeLT
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify;

/**
 * Retrieve the main plugin instance.
 *
 * @since 0.1.0
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
 * @since 0.1.0
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
 * @since 0.1.0
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
	for ( $i = 0; $i < $length; $i++ ) {
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
 * @since 0.1.0
 *
 * @param string $plugin_file Plugin slug or relative path to the main plugin file.
 *
 * @return bool
 */
function is_plugin_file( string $plugin_file ): bool {
	return '.php' === substr( $plugin_file, -4 );
}

/**
 * Display a notice about missing dependencies.
 *
 * @since 0.1.0
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
