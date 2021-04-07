<?php
/**
 * Customizer select2 control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer select2 control class.
 *
 * This handles the 'select2' control type.
 *
 * @since 3.0.0
 */
class Select2 extends BaseControl {
	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type = 'select2';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>

			<select <?php $this->link(); ?> class="customify_select2">
				<?php
				foreach ( $this->choices as $value => $label ) {
					echo '<option value="' . esc_attr( $value ) . '" ' . selected( $this->value(), $value, false ) . '>' . $label . '</option>';
				}
				?>
			</select>

			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php endif; ?>
		</label>
		<?php

	}
}
