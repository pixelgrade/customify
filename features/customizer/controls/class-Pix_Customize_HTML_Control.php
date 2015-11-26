<?php

/**
 * Class Pix_Customize_HTML_Control
 * A simple HTML Control
 */
class Pix_Customize_HTML_Control extends Pix_Customize_Control {
	public $type    = 'html';
	public $action  = null;
	public $html    = null;

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() {
		if ( ! empty( $this->html ) ) {
			echo ( $this->html );
		}
	}
}