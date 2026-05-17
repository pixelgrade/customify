<?php
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.Security.EscapeOutput.ExceptionNotEscaped,WordPress.Security.EscapeOutput.UnsafePrintingFunction -- Legacy Customify view output contains trusted config HTML, dynamic CSS/JS, or WordPress Customizer binding attributes.

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
