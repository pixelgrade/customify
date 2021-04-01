<?php
/**
 * Upgrade routines.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Provider;

use Pixelgrade\Customify\Capabilities;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;

use const Pixelgrade\Customify\VERSION;

/**
 * Class for upgrade routines.
 *
 * @since 3.0.0
 */
class Upgrade extends AbstractHookProvider {
	/**
	 * Version option name.
	 *
	 * @var string
	 */
	const VERSION_OPTION_NAME = 'customify_dbversion';

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
	 * @param Options         $options         Options.
	 * @param PluginSettings  $plugin_settings Plugin settings.
	 * @param LoggerInterface $logger          Logger.
	 */
	public function __construct(
		Options $options,
		PluginSettings $plugin_settings,
		LoggerInterface $logger
	) {
		$this->options         = $options;
		$this->plugin_settings = $plugin_settings;
		$this->logger          = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_init', [ $this, 'maybe_upgrade' ] );
	}

	/**
	 * Upgrade when the database version is outdated.
	 *
	 * @since 3.0.0
	 */
	public function maybe_upgrade() {
		$saved_version = get_option( self::VERSION_OPTION_NAME, '0' );

		// For versions, previous of version 2.0.0 (the Color Palettes v2.0 release).
		if ( version_compare( $saved_version, '2.0.0', '<' ) ) {
			// Delete the option holding the fact that the user offered feedback.
			delete_option( 'style_manager_user_feedback_provided' );
		}

		// For versions, previous of version 3.0.0 (the rewrite release).
		if ( version_compare( $saved_version, '3.0.0', '<' ) ) {
			// Make sure that admins have the required capability to access the plugin settings page.
			Capabilities::register();

			// Migrate the old plugin settings to the new ones.
			$existing_settings = get_option( 'pixcustomify_settings' );
			if ( ! empty( $existing_settings ) && is_array( $existing_settings ) ) {
				$new_settings = [];
				if ( isset( $existing_settings['values_store_mod'] ) ) {
					$new_settings['values_store_mod'] = $existing_settings['values_store_mod'];
				}
				if ( isset( $existing_settings['disable_default_sections'] ) && is_array( $existing_settings['disable_default_sections'] ) ) {
					$new_settings['disable_default_sections'] = [];
					foreach ( $existing_settings['disable_default_sections'] as $section => $disabled ) {
						if ( 'on' === $disabled ) {
							$new_settings['disable_default_sections'][] = $section;
						}
					}
				}
				if ( isset( $existing_settings['enable_reset_buttons'] ) ) {
					$new_settings['enable_reset_buttons'] = ! empty ( $existing_settings['enable_reset_buttons'] ) ? 'yes' : '';
				}
				if ( isset( $existing_settings['enable_editor_style'] ) ) {
					$new_settings['enable_editor_style'] = ! empty ( $existing_settings['enable_editor_style'] ) ? 'yes' : '';
				}
				if ( isset( $existing_settings['style_resources_location'] ) ) {
					$new_settings['style_resources_location'] = $existing_settings['style_resources_location'];
				}
				if ( isset( $existing_settings['typography'] ) ) {
					$new_settings['enable_typography'] = ! empty ( $existing_settings['typography'] ) ? 'yes' : '';
				}
				if ( isset( $existing_settings['typography_system_fonts'] ) ) {
					$new_settings['typography_system_fonts'] = ! empty ( $existing_settings['typography_system_fonts'] ) ? 'yes' : '';
				}
				if ( isset( $existing_settings['typography_google_fonts'] ) ) {
					$new_settings['typography_google_fonts'] = ! empty ( $existing_settings['typography_google_fonts'] ) ? 'yes' : '';
				}
				if ( isset( $existing_settings['typography_group_google_fonts'] ) ) {
					$new_settings['typography_group_google_fonts'] = ! empty ( $existing_settings['typography_group_google_fonts'] ) ? 'yes' : '';
				}
				if ( isset( $existing_settings['typography_cloud_fonts'] ) ) {
					$new_settings['typography_cloud_fonts'] = ! empty ( $existing_settings['typography_cloud_fonts'] ) ? 'yes' : '';
				}

				if ( ! empty( $new_settings ) ) {
					$this->plugin_settings->set_all( $new_settings );
					delete_option( 'pixcustomify_settings' );
				}
			}

			// Cleanup old options since we use new keys.
			delete_option( 'customify_options_minimal_details' );
			delete_option( 'customify_options_extra_details' );
			delete_option( 'customify_options_details_timestamp' );
			delete_option( 'customify_customizer_config' );
			delete_option( 'customify_customizer_config_timestamp' );
			delete_option( 'customify_customizer_opt_name' );
			delete_option( 'customify_customizer_opt_name_timestamp' );
		}

		if ( version_compare( $saved_version, VERSION, '<' ) ) {
			// Always invalidate caches on upgrade, just to be sure.
			$this->options->invalidate_all_caches();

			update_option( self::VERSION_OPTION_NAME, VERSION );
		}
	}
}
