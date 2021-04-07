<?php
/**
 * Customizer SM radio control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer SM radio control class.
 *
 * This handles the 'sm_radio' control type.
 *
 * @since 3.0.0
 */
class SMRadio extends BaseControl {
	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type = 'sm_radio';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$name     = '_customize-radio-' . $this->id;

		do_action( 'customify_before_sm_radio_control', $this );

		if ( ! empty( $this->label ) ) { ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php } ?>
		<div class="sm-radio-group">
			<?php foreach ( $this->choices as $value => $label ) { ?>
				<input
					type="radio"
					value="<?php echo esc_attr( $value ) ?>"
					id="<?php echo esc_attr( $input_id . '-radio-' . $value ); ?>"
					name="<?php echo esc_attr( $name ) ?>"
					<?php $this->link(); ?>
					<?php if ( $value == $this->settings['default']->default ) {
						echo 'data-default="true"';
					}; ?>
					<?php checked( $this->value(), $value, true ); ?>
				>
				<label
					for="<?php echo esc_attr( $input_id . '-radio-' . $value ); ?>"><?php echo esc_html( $label ) ?></label>
			<?php } ?>
		</div>

		<?php if ( ! empty( $this->description ) ) { ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php } ?>

		<?php
		do_action( 'customify_after_sm_radio_control', $this );
	}
}
