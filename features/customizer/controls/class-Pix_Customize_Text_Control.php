<?php
/**
 * Class Pix_Customize_Text_Control
 * A simple Text Control
 */
class Pix_Customize_Text_Control extends Pix_Customize_Control {
	public $type    = 'text';
	public $live    = false;

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() {
		// here we need an array of classes which should be affected by the live preview
//		if ( ! empty( $this->live ) && is_array( $this->live ) ) {
//			$this->input_attrs['data-live_preview_classes'] = implode( ',', $this->live );
//			$this->input_attrs['class'] = 'customify_text_live_preview';
//		} ?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<input type="<?php echo esc_attr( $this->type ); ?>" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
		</label>
	<?php

	}
}
