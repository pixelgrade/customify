<?php

/**
 * Class Pix_Customize_HTML_Control
 */
class Pix_Customize_HTML_Control extends Pix_Customize_Control {
	public $type    = 'html';
	public $action  = null;
	public $html    = null;

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		if ( ! empty( $this->html ) ) {
			echo ( $this->html );
		}
	}
}
