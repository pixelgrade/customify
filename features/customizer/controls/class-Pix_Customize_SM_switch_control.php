<?php
/**
 * Class Pix_Customize_SM_radio_Control
 */
class Pix_Customize_SM_switch_Control extends Pix_Customize_Control {
	public $type            = 'sm_switch';

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() { ?>
		<?php if ( ! empty( $this->label ) ) : ?>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
        <div class="sm-switch">
		    <?php foreach ( $this->choices as $value => $label ) { ?>
                <input
                    type="radio"
                    value="<?php echo $value ?>"
                    id="_customize-input-<?php echo $this->id ?>_control-radio-<?php echo $value ?>"
                    name="_customize-radio-<?php echo $this->id ?>_control"
                    data-customize-setting-link="<?php echo $this->id ?>"
	                <?php checked( $this->value(), $value, true ); ?>
                >
                <label for="_customize-input-<?php echo $this->id ?>_control-radio-<?php echo $value ?>"><?php echo $label ?></label>
		    <?php } ?>
        </div>

	<?php }
}
