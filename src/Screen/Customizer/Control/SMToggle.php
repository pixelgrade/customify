<?php
/**
 * Customizer SM toggle control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer SM toggle control class.
 *
 * This handles the 'sm_toggle' control type.
 *
 * @since 3.0.0
 */
class SMToggle extends BaseControl {
	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type = 'sm_toggle';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id         = '_customize-input-' . $this->id;
		$description_id   = '_customize-description-' . $this->id;
		$describedby_attr = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';
		?>

		<div class="sm-toggle">
			<input
				id="<?php echo esc_attr( $input_id ); ?>"
				class="sm-toggle__checkbox"
				<?php echo $describedby_attr; ?>
				type="checkbox"
				value="<?php echo esc_attr( $this->value() ); ?>"
				<?php $this->link(); ?>
				<?php checked( $this->value() ); ?>
			/>
			<label class="sm-toggle__label" for="<?php echo esc_attr( $input_id ); ?>">
				<div class="sm-toggle__switch"></div>
				<div class="sm-toggle__label-text"><?php echo esc_html( $this->label ); ?></div>
			</label>
			<?php if ( ! empty( $this->description ) ) : ?>
				<span id="<?php echo esc_attr( $description_id ); ?>" class="sm-toggle__description"><?php echo $this->description; ?></span>
			<?php endif; ?>
		</div>

	<?php }
}
