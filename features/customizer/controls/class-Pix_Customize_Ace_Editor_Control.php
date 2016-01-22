<?php

/**
 * Class Pix_Customize_Ace_Editor_Control
 * The ace editor at your feet.
 */
class Pix_Customize_Ace_Editor_Control extends Pix_Customize_Control {
	public $type    = 'ace_editor';

	public $editor_type = 'editor_type';


	public function render_content() { ?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<textarea <?php $this->link(); ?>  id="<?php echo sanitize_html_class( $this->id ) ?>_textarea" class="customify_ace_editor_text"><?php echo esc_textarea( $this->value() ); ?></textarea>
			<div class="customify_ace_editor" id="<?php echo sanitize_html_class( $this->id ); ?>" data-editor_type="<?php echo $this->editor_type; ?>"></div>
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
		</label>
	<?php

	}
}
