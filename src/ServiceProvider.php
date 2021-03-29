<?php
/**
 * Plugin service definitions.
 *
 * @package PixelgradeLT
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\Provider\I18n;
use Pixelgrade\Customify\Vendor\Pimple\Container as PimpleContainer;
use Pixelgrade\Customify\Vendor\Pimple\ServiceIterator;
use Pixelgrade\Customify\Vendor\Pimple\ServiceProviderInterface;
use Pixelgrade\Customify\Vendor\Pimple\Psr11\ServiceLocator;
use Pixelgrade\Customify\Vendor\Psr\Log\LogLevel;

/**
 * Plugin service provider class.
 *
 * @since 0.1.0
 */
class ServiceProvider implements ServiceProviderInterface {
	/**
	 * Register services.
	 *
	 * @param PimpleContainer $container Container instance.
	 */
	public function register( PimpleContainer $container ) {
		$container['hooks.activation'] = function( $container ) {
			return new Provider\Activation(
				$container['options'],
				$container['logger']
			);
		};

		$container['hooks.admin_assets'] = function() {
			return new Provider\AdminAssets();
		};

		$container['hooks.capabilities'] = function() {
			return new Provider\Capabilities();
		};

		$container['hooks.deactivation'] = function( $container ) {
			return new Provider\Deactivation(
				$container['options'],
				$container['logger']
			);
		};

		$container['hooks.health_check'] = function( $container ) {
			return new Provider\HealthCheck(
				$container['http.request']
			);
		};

		$container['hooks.i18n'] = function() {
			return new I18n();
		};

		$container['hooks.rewrite_rules'] = function() {
			return new Provider\RewriteRules();
		};

		$container['hooks.upgrade'] = function( $container ) {
			return new Provider\Upgrade(
				$container['options'],
				$container['logger']
			);
		};

		$container['logger'] = function( $container ) {
			return new Logger( $container['logger.level'] );
		};

		$container['logger.level'] = function( $container ) {
			// Log warnings and above when WP_DEBUG is enabled.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$level = LogLevel::WARNING;
			}

			return $level ?? '';
		};

		$container['integration.autoptimize'] = function() {
			return new Integration\Autoptimize();
		};

		$container['integration.pixelgrade_assistant'] = function( $container ) {
			return new Integration\PixelgradeAssistant(
				$container['options']
			);
		};

		$container['integration.pixelgrade_care'] = function( $container ) {
			return new Integration\PixelgradeCare(
				$container['options']
			);
		};

		$container['integration.the_events_calendar'] = function() {
			return new Integration\TheEventsCalendar();
		};

		$container['integration.w3_total_cache'] = function() {
			return new Integration\W3TotalCache();
		};

		$container['integration.wp_fastest_cache'] = function() {
			return new Integration\WPFastestCache();
		};

		$container['integration.wp_rocket'] = function() {
			return new Integration\WPRocket();
		};

		$container['options'] = function( $container ) {
			return new Options();
		};

		$container['screen.edit_package'] = function( $container ) {
			return new Screen\EditPackage(
				$container['package.manager'],
				$container['repository.managed'],
				$container['hooks.package_post_type'],
				$container['transformer.composer_package']
			);
		};

		$container['screen.list_packages'] = function( $container ) {
			return new Screen\ListPackages(
				$container['package.manager']
			);
		};

		$container['screen.edit_user'] = function( $container ) {
			return new Screen\EditUser(
				$container['api_key.repository']
			);
		};

		$container['screen.manage_plugins'] = function( $container ) {
			return new Screen\ManagePlugins( $container['repository.installed.managed'] );
		};

		$container['screen.settings'] = function( $container ) {
			return new Screen\Settings(
				$container['repository.managed'],
				$container['api_key.repository'],
				$container['transformer.composer_package']
			);
		};

		$container['storage.packages'] = function( $container ) {
			$path = path_join( $container['storage.working_directory'], 'packages/' );
			return new Storage\Local( $path );
		};

		$container['storage.working_directory'] = function( $container ) {
			if ( \defined( 'PIXELGRADELT_RECORDS_WORKING_DIRECTORY' ) ) {
				return PIXELGRADELT_RECORDS_WORKING_DIRECTORY;
			}

			$upload_config = wp_upload_dir();
			$path          = path_join( $upload_config['basedir'], $container['storage.working_directory_name'] );

			return (string) trailingslashit( apply_filters( 'pixelgradelt_records_working_directory', $path ) );
		};

		$container['storage.working_directory_name'] = function() {
			$directory = get_option( 'pixelgradelt_records_working_directory' );

			if ( ! empty( $directory ) ) {
				return $directory;
			}

			// Append a random string to help hide it from nosey visitors.
			$directory = sprintf( 'pixelgradelt_records-%s', generate_random_string() );

			// Save the working directory so we will always use the same directory.
			update_option( 'pixelgradelt_records_working_directory', $directory );

			return $directory;
		};
	}
}
