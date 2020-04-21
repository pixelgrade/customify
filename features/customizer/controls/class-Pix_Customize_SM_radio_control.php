<?php
/**
 * Class Pix_Customize_SM_radio_Control
 */
class Pix_Customize_SM_radio_Control extends Pix_Customize_Control {
	public $type            = 'sm_radio';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$name = '_customize-radio-' . $this->id;

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
                    <?php if ( $value == $this->settings['default']->default ) { echo 'data-default="true"'; }; ?>
                    <?php checked( $this->value(), $value, true ); ?>
                >
				<label for="<?php echo esc_attr( $input_id . '-radio-' . $value ); ?>"><?php echo esc_html( $label ) ?></label>
		    <?php } ?>
        </div>

		<?php
		do_action( 'customify_after_sm_radio_control', $this );
	}
}
