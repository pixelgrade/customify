<?php
/**
 * Customify
 *
 * @package Customify
 * @author  Vlad Olaru <vlad@pixelgrade.com>
 * @license GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Customify
 * Plugin URI:  https://wordpress.org/plugins/customify/
 * Description: A Theme Customizer Booster to easily and consistently customize Fonts, Colors, and other options for your site.
 * Version: 3.0.0
 * Author: Pixelgrade
 * Author URI: https://pixelgrade.com
 * Author Email: contact@pixelgrade.com
 * Text Domain: customify
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages/
 * Requires at least: 4.9.16
 * Tested up to: 5.7.1
 * Requires PHP: 5.6.20
 * GitHub Plugin URI: pixelgrade/customify
 * Release Asset: true
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify;

// Exit if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 *
 * @var string
 */
const VERSION = '3.0.0';

/**
 * Plugin required minimal PHP version.
 *
 * @var string
 */
const PHP_VERSION = '5.6.20';

// Load the Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

/**
 * Display admin notice, if the server is using old PHP version.
 *
 * @since 3.0.0
 */
function old_php_version_notice() { ?>

	<div class="notice notice-error">
		<p>
			<?php
			printf(
				wp_kses( /* translators: %1$s - WPBeginner URL for recommended WordPress hosting. */
					__( 'Your site is running an <strong>old version</strong> of PHP that is no longer supported. Please contact your web hosting provider to update your PHP version or switch to a <a href="%1$s" target="_blank" rel="noopener noreferrer">recommended WordPress hosting company</a>.', '__plugin_txtd' ),
					array(
						'a'      => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
						'strong' => array(),
					)
				),
				'https://wordpress.org/hosting/'
			);
			?>
			<br><br>
			<?php
			printf(
				wp_kses( /* translators: %s - WPMailSMTP.com docs URL with more details. */
					__( '<strong>Pixelgrade Customify plugin is disabled</strong> on your site until you fix the issue. <a href="%s" target="_blank" rel="noopener noreferrer">Read more for additional information.</a>', '__plugin_txtd' ),
					array(
						'a'      => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
						'strong' => array(),
					)
				),
				'https://wordpress.org/about/requirements/'
			);
			?>
		</p>
	</div>

	<?php

	// In case this is on plugin activation.
	if ( isset( $_GET['activate'] ) ) { //phpcs:ignore
		unset( $_GET['activate'] ); //phpcs:ignore
	}
}

/**
 * Display admin notice and prevent plugin code execution, if the server is
 * using old PHP version.
 *
 * @since 3.0.0
 */
if ( version_compare( phpversion(), PHP_VERSION, '<' ) ) {
	add_action( 'admin_notices', __NAMESPACE__ . '\old_php_version_notice' );

	return;
}

// Display a notice and bail if dependencies are missing.
if ( ! function_exists( __NAMESPACE__ . '\autoloader_classmap' ) ) {
	require_once __DIR__ . '/src/functions.php';
	add_action( 'admin_notices', __NAMESPACE__ . '\display_missing_dependencies_notice' );

	return;
}

// Autoload mapped classes.
spl_autoload_register( __NAMESPACE__ . '\autoloader_classmap' );

// Load the WordPress plugin administration API.
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Create a container and register a service provider.
$customify_container = new Container();
$customify_container->register( new ServiceProvider() );

// Initialize the plugin and inject the container.
$pixcustomify_plugin = plugin()
	->set_basename( plugin_basename( __FILE__ ) )
	->set_directory( plugin_dir_path( __FILE__ ) )
	->set_file( __DIR__ . '/customify.php' )
	->set_slug( 'customify' )
	->set_url( plugin_dir_url( __FILE__ ) )
	->set_container( $customify_container )
	->register_hooks( $customify_container->get( 'hooks.activation' ) )
	->register_hooks( $customify_container->get( 'hooks.deactivation' ) );

// Composer before the theme is setup; this should give plenty of opportunities to hook.
add_action( 'setup_theme', [ $pixcustomify_plugin, 'compose' ], 15 );
