<?php
/**
 * Plugin activation routines.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Provider;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Capabilities;
use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;

/**
 * Class to activate the plugin.
 *
 * @since 3.0.0
 */
class Activation extends AbstractHookProvider {

	/**
	 * Options.
	 *
	 * @var Options
	 */
	protected Options $options;

	/**
	 * Plugin settings.
	 *
	 * @var PluginSettings
	 */
	protected PluginSettings $plugin_settings;

	/**
	 * Logger.
	 *
	 * @var LoggerInterface
	 */
	protected LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Options         $options Options.
	 * @param PluginSettings  $plugin_settings
	 * @param LoggerInterface $logger  Logger.
	 */
	public function __construct(
		Options $options,
		PluginSettings $plugin_settings,
		LoggerInterface $logger
	) {
		$this->options = $options;
		$this->plugin_settings = $plugin_settings;
		$this->logger  = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		register_activation_hook( $this->plugin->get_file(), [ $this, 'activate' ] );
	}

	/**
	 * Activate the plugin.
	 *
	 * - Sets a flag to flush rewrite rules after plugin rewrite rules have been
	 *   registered.
	 * - Registers capabilities for the admin role.
	 *
	 * @since 3.0.0
	 * @see   \Pixelgrade\Customify\Provider\RewriteRules::maybe_flush_rewrite_rules()
	 *
	 */
	public function activate() {
		$this->install();

		$this->options->invalidate_all_caches();

		update_option( 'pixelgrade_customify_flush_rewrite_rules', 'yes' );

		Capabilities::register();
	}

	/*
	 * Install everything needed.
	 */
	private function install() {

		$default_settings = [
			'values_store_mod'              => 'theme_mod',
			'disable_default_sections'      => [],
			'enable_reset_buttons'          => false,
			'enable_editor_style'           => true,
			'style_resources_location'      => 'wp_head',
			'enable_typography'             => true,
			'typography_system_fonts'       => true,
			'typography_google_fonts'       => true,
			'typography_group_google_fonts' => true,
			'typography_cloud_fonts'        => true,
		];

		$current_settings = $this->plugin_settings->get_all();

		if ( empty( $current_settings ) ) {
			// If the settings are empty, set them to the default value.
			$this->plugin_settings->set_all( $default_settings );
		} elseif ( count( array_diff_key( $default_settings, $current_settings ) ) > 0 ) {
			// If we have different keys (possibly new keys).
			$plugin_settings = array_merge( $default_settings, $current_settings );
			$this->plugin_settings->set_all( $plugin_settings );
		}
	}
}
