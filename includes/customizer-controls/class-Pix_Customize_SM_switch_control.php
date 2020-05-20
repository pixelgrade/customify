<?php
/**
 * Class Pix_Customize_SM_radio_Control
 */
class Pix_Customize_SM_switch_Control extends Pix_Customize_Control {
	public $type            = 'sm_switch';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$name = '_customize-radio-' . $this->id;
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
