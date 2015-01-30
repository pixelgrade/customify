<?php

class Pix_Customize_CSS_Editor_Control extends Pix_Customize_Control {
	public $type = 'css_editor';

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() { ?>

		<style type="text/css" media="screen">
			#css_editor {
				display: inline-block;
				height: 700px;
				position: absolute;
				top: 130px;
				right: 0;
				bottom: 0;
				left: 0;
				z-index: 99999999;
			}
		</style>

		<textarea <?php $this->link(); ?> id="css_editor_textarea"><?php echo esc_textarea( $this->value() ); ?></textarea>
		<div id="css_editor"></div>

		<?php

		/**
		<label>
		<?php if ( ! empty( $this->label ) ) : ?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif;
		if ( ! empty( $this->description ) ) : ?>
		<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php endif; ?>
		<textarea rows="5" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
		</label> */

	}
}