<?php
/**
 * Customizer button control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer button control class.
 *
 * @since 3.0.0
 */
class Button extends BaseControl {
	public string $type = 'button';
	public string $action = '';

	/**
	 * Render the control's content.
	 */
	public function render_content() { ?>
		<button type="button" class="customify_button button" <?php $this->input_attrs(); ?>
		        data-action="<?php echo esc_html( $this->action ); ?>"><?php echo esc_html( $this->label ); ?></button>
		<?php

	}
}
