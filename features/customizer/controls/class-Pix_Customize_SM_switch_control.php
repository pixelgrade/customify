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
        <div class="sm-switch">
		    <?php foreach ( $this->choices as $value => $label ) { ?>
				<input id="_customize-input-sm_palette_filter_control-radio-gingham" type="radio" value="<?php echo $value ?>" name="_customize-radio-sm_palette_filter_control" data-customize-setting-link="sm_palette_filter">
				<label for="_customize-input-sm_palette_filter_control-radio-gingham"><?php echo $label ?></label>
		    <?php } ?>
        </div>

	<?php }
}
