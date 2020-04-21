<?php

/**
 * Class Pix_Customize_Button_Control
 */
class Pix_Customize_Button_Control extends Pix_Customize_Control {
	public $type    = 'button';
	public $action  = null;

	/**
	 * Render the control's content.
	 */
	public function render_content() { ?>
		<button type="button" class="customify_button button" <?php $this->input_attrs(); ?> data-action="<?php echo esc_html( $this->action ); ?>" ><?php echo esc_html( $this->label ); ?></button>
	<?php

	}
}
