<?php
/**
 * Customizer HTML pseudo-control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer HTML pseudo-control class.
 *
 * This handles the 'html' control type.
 *
 * @since 3.0.0
 */
class HTML extends BaseControl {
	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type = 'html';

	public string $action = '';
	public string $html = '';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		if ( ! empty( $this->html ) ) {
			echo( $this->html );
		}
	}
}
