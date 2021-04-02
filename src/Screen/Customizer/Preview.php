<?php
/**
 * Customizer screen preview functionality provider.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;

/**
 * Customizer screen preview provider class.
 *
 * @since 3.0.0
 */
class Preview extends AbstractHookProvider {

	/**
	 * Create the Customizer screen preview.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		$this->add_action( 'customize_preview_init', 'enqueue_assets', 99999 );

		$this->add_action( 'wp_footer', 'output_color_palettes_preview_overlay' );

		// Register hooks related to Style Manager controls callbacks in sm-functions.php
		$this->add_action( 'customize_preview_init', 'sm_color_select_dark_cb_customizer_preview', 20 );
		$this->add_action( 'customize_preview_init', 'sm_color_select_darker_cb_customizer_preview', 20 );
		$this->add_action( 'customize_preview_init', 'sm_color_switch_dark_cb_customizer_preview', 20 );
		$this->add_action( 'customize_preview_init', 'sm_color_switch_darker_cb_customizer_preview', 20 );
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 3.0.0
	 */
	protected function enqueue_assets() {
		wp_enqueue_script( 'pixelgrade_customify-previewer' );
	}

	/**
	 * Output a wrapper for the color palettes preview overlay.
	 *
	 * @since 3.0.0
	 */
	protected function output_color_palettes_preview_overlay() {
		if ( is_customize_preview() ) {
			echo '<div id="sm-color-palettes-preview"></div>';
		}
	}

	protected function sm_color_select_dark_cb_customizer_preview() {
		$js = "
function sm_color_select_dark_cb(value, selector, property) {
    return selector + ' {' + property + ': var(--sm-current-' + value + '-color);' + '}';
}" . PHP_EOL;

		wp_add_inline_script( 'customify-previewer-scripts', $js );
	}

	protected function sm_color_select_darker_cb_customizer_preview() {
		$js = "
function sm_color_select_darker_cb(value, selector, property) {
    return selector + ' {' + property + ': var(--sm-current-' + value + '-color);' + '}';
}" . PHP_EOL;

		wp_add_inline_script( 'customify-previewer-scripts', $js );
	}

	protected function sm_color_switch_dark_cb_customizer_preview() {
		$js = "
function sm_color_switch_dark_cb(value, selector, property) {
    var color = value === 'on' ? 'accent' : 'fg1';
    return selector + ' { ' + property + ': var(--sm-current-' + color + '-color); }';
}" . PHP_EOL;

		wp_add_inline_script( 'customify-previewer-scripts', $js );
	}

	protected function sm_color_switch_darker_cb_customizer_preview() {
		$js = "
function sm_color_switch_darker_cb(value, selector, property) {
	var color = value === 'on' ? 'accent' : 'fg2';
	return selector + ' { ' + property + ': var(--sm-current-' + color + '-color); }';
}" . PHP_EOL;

		wp_add_inline_script( 'customify-previewer-scripts', $js );
	}
}
