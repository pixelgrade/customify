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
use Pixelgrade\Customify\Vendor\Pimple\ServiceProviderInterface;
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

		$container['hooks.customizer_assets'] = function() {
			return new Provider\CustomizerAssets();
		};

		$container['hooks.deactivation'] = function( $container ) {
			return new Provider\Deactivation(
				$container['options'],
				$container['logger']
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
				$container['plugin.settings'],
				$container['logger']
			);
		};

		$container['logger'] = function( $container ) {
			return new Logger( $container['logger.level'] );
		};

		$container['logger.level'] = function() {
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

		$container['options'] = function() {
			return new Provider\Options();
		};

		$container['plugin.settings'] = function() {
			return new Provider\PluginSettings();
		};

		$container['plugin.settings.cfdatastore'] = function() {
			return new Provider\PluginSettingsCFDatastore();
		};

		$container['screen.edit_with_blocks'] = function( $container ) {
			return new Screen\EditWithBlocks(
				$container['options'],
				$container['plugin.settings'],
				$container['logger']
			);
		};

		$container['screen.edit_with_classic_editor'] = function( $container ) {
			return new Screen\EditWithClassicEditor(
				$container['options'],
				$container['plugin.settings'],
				$container['logger']
			);
		};

		$container['screen.settings'] = function( $container ) {
			return new Screen\Settings(
				$container['options'],
				$container['plugin.settings.cfdatastore'],
				$container['logger']
			);
		};
	}
}
