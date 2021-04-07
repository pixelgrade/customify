<?php
/**
 * Customizer SM switch control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer SM switch control class.
 *
 * This handles the 'sm_switch' control type.
 *
 * @since 3.0.0
 */
class SMSwitch extends BaseControl {
	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type = 'sm_switch';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$name     = '_customize-radio-' . $this->id;
		?>
		<?php if ( ! empty( $this->label ) ) : ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
		<div class="sm-switch">
			<?php foreach ( array_reverse( $this->choices ) as $value => $label ) { ?>
				<input
					type="radio"
					value="<?php echo esc_attr( $value ) ?>"
					id="<?php echo esc_attr( $input_id . '-radio-' . $value ); ?>"
					name="<?php echo esc_attr( $name ) ?>"
					<?php $this->link(); ?>
					<?php checked( $this->value(), $value, true ); ?>
				>
				<label for="<?php echo esc_attr( $input_id . '-radio-' . $value ); ?>"><?php echo $label ?></label>
			<?php } ?>
		</div>

	<?php }
}
