<?php
/**
 * Plugin activation routines.
 *
 * @package PixelgradeLT
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify\Provider;

use Pixelgrade\Customify\Options;
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
	 * @param LoggerInterface $logger  Logger.
	 */
	public function __construct(
		Options $options,
		LoggerInterface $logger
	) {
		$this->options          = $options;
		$this->logger          = $logger;
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
	 * @see \PixelgradeLT\Records\Provider\RewriteRules::maybe_flush_rewrite_rules()
	 *
	 * @since 3.0.0
	 */
	public function activate() {
		$this->install();

		$this->options->invalidate_all_caches();

		update_option( 'pixelgrade_customify_flush_rewrite_rules', 'yes' );

		Capabilities::register();
	}

	/*
	 * Install everything needed
	 */
	private function install() {
		$config = Customify_Settings::get_plugin_config();

		$defaults = array(

			# Hidden fields
			'settings_saved_once'                   => '0',
			# General
			'values_store_mod'                => 'theme_mod',

			'typography' => true,
			'typography_system_fonts' => true,
			'typography_google_fonts' => true,
			'typography_group_google_fonts' => true,
			'typography_cloud_fonts' => true,
			'disable_default_sections' => array(),
			'disable_customify_sections' => array(),
			'enable_reset_buttons' => false,
			'enable_editor_style' => true,
			'style_resources_location' => 'wp_head'
		);

		$current_data = get_option( $config['settings-key'] );

		if ( $current_data === false ) {
			add_option( $config['settings-key'], $defaults );
		} elseif ( count( array_diff_key( $defaults, $current_data ) ) != 0)  {
			$plugin_data = array_merge( $defaults, $current_data );
			update_option( $config['settings-key'], $plugin_data );
		}
	}
}
