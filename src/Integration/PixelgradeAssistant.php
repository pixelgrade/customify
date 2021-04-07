<?php
/**
 * Pixelgrade Assistant plugin integration.
 *
 * @link    https://wordpress.org/plugins/pixelgrade-assistant/
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Integration;

use Pixelgrade\Customify\Provider\Options;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Pixelgrade Assistant plugin integration provider class.
 *
 * @since 3.0.0
 */
class PixelgradeAssistant extends AbstractHookProvider {

	/**
	 * Options.
	 *
	 * @var Options
	 */
	protected Options $options;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Options $options Options.
	 */
	public function __construct(
		Options $options
	) {
		$this->options = $options;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		$this->add_filter( 'pre_set_theme_mod_pixassist_license', 'invalidate_all_caches', 10, 1 );
	}

	/**
	 * Invalidate all caches on license update.
	 *
	 * @since 3.0.0
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function invalidate_all_caches( $value ) {
		$this->options->invalidate_all_caches();

		return $value;
	}
}
