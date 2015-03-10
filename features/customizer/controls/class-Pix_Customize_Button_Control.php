<?php

/**
 * Class Pix_Customize_Color_Control
 * A simple Color Control
 */
class Pix_Customize_Button_Control extends Pix_Customize_Control {
	public $type    = 'button';
	public $action  = null;

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() { ?>
		<button type="button" class="customify_button button" <?php $this->input_attrs(); ?> data-action="<?php echo esc_html( $this->action ); ?>" ><?php echo esc_html( $this->label ); ?></button>
	<?php

	}
}