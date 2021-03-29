<?php
/**
 * Upgrade routines.
 *
 * @package PixelgradeLT
 * @license GPL-2.0-or-later
 * @since 3.0.0
 */

declare ( strict_types = 1 );

namespace Pixelgrade\Customify\Provider;

use Pixelgrade\Customify\Options;
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

		$this->options->invalidate_all_caches();

		if ( version_compare( $saved_version, VERSION, '<' ) ) {
			update_option( self::VERSION_OPTION_NAME, VERSION );
		}
	}
}
