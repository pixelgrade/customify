<?php
/**
 * Customizer ACE Editor control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer ACE Editor control class.
 *
 * This handles the 'ace_editor' control type.
 *
 * @since 3.0.0
 */
class AceEditor extends BaseControl {
	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type = 'ace_editor';

	public string $editor_type = 'editor_type';

	public function render_content() { ?>
		<label>
			<?php if ( ! empty( $this->label ) ) { ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php } ?>
			<textarea <?php $this->link(); ?>  id="<?php echo sanitize_html_class( $this->id ) ?>_textarea"
			                                   class="customify_ace_editor_text"><?php echo esc_textarea( $this->value() ); ?></textarea>
			<div class="customify_ace_editor" id="<?php echo sanitize_html_class( $this->id ); ?>"
			     data-editor_type="<?php echo $this->editor_type; ?>"></div>
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
		</label>
		<?php

	}

	public function enqueue() {
		wp_enqueue_script( 'pixelgrade_customify-ace-editor' );
	}
}
