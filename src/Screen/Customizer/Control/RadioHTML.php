<?php
/**
 * Customizer radio HTML control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer radio HTML control class.
 *
 * This handles the 'radio_html' control type.
 *
 * @since 3.0.0
 */
class RadioHTML extends BaseControl {
	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type = 'radio_html';

	/**
	 * Render the control's content.
	 */
	public function render_content() { ?>

		<?php if ( ! empty( $this->label ) ) { ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php } ?>

		<div class="customify_radio_html">
			<?php
			foreach ( $this->choices as $value => $html ) { ?>
				<div>
					<label>
						<input
							type="radio"
							name="<?php echo esc_attr( $this->setting->id ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
							<?php $this->link(); ?>
							<?php echo selected( $this->value(), $value, false ); ?>
						/>
						<div><?php echo $html; ?></div>
					</label>
				</div>
			<?php } ?>
		</div>

		<?php if ( ! empty( $this->description ) ) { ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php } ?>

	<?php }
}
