<?php
/**
 * Class Pix_Customize_SM_palette_filter_Control
 */
class Pix_Customize_SM_palette_filter_Control extends Pix_Customize_Control {
	public $type            = 'sm_palette_filter';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$name = '_customize-radio-' . $this->id;

		do_action( 'customify_before_sm_palette_filter_control', $this );

		if ( ! empty( $this->label ) ) { ?>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php } ?>
        <div class="sm-palette-filter">
		    <?php
		    $master_color_controls_ids = Customify_Color_Palettes::instance()->get_all_master_color_controls_ids();

		    foreach ( $this->choices as $value => $label ) { ?>
				<input
                    type="radio"
                    value="<?php echo esc_attr( $value ) ?>"
                    id="<?php echo esc_attr( $input_id . '-radio-' . $value ); ?>"
                    name="<?php echo esc_attr( $name ) ?>"
					<?php $this->link(); ?>
                    <?php if ( $value === $this->settings['default']->default ) { echo 'data-default="true"'; }; ?>
                    <?php checked( $this->value(), $value, true ); ?>
                >
				<label for="<?php echo esc_attr( $input_id . '-radio-' . $value ); ?>">
                    <div class="filter-label">
                        <span><?php echo esc_html( $label ); ?></span>
                    </div>

					<div class="sm-color-palette__colors">
                    <?php foreach ( $master_color_controls_ids as $setting_id ) { ?>
                        <div class="sm-color-palette__color <?php echo esc_attr( $setting_id ) ?>" data-setting="<?php echo esc_attr( $setting_id ) ?>">
                            <div class="sm-color-palette__picker"></div>
                        </div>
                    <?php } ?>
                    </div>
                </label>
		    <?php } ?>
        </div>

		<?php
		do_action( 'customify_after_sm_palette_filter_control', $this );
	}
}
