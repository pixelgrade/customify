<?php
/**
 * Customizer select color control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package PixelgradeLT
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer select color control class.
 *
 * @since 3.0.0
 */
class SelectColor extends BaseControl {
	public $type = 'select_color';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
		</label>

		<select <?php $this->link(); ?> class="js-color-select">
			<?php
			foreach ( $this->choices as $value => $label ) {
				echo '<option value="' . esc_attr( $value ) . '" ' . selected( $this->value(), $value, false ) . '>' . esc_html( $label ) . '</option>';
			}
			?>
		</select>

		<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php endif;
	}
}
