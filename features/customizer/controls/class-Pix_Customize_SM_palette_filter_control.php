<?php
/**
 * Class Pix_Customize_SM_palette_filter_Control
 */
class Pix_Customize_SM_palette_filter_Control extends Pix_Customize_Control {
	public $type            = 'sm_palette_filter';

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() {
		$input_id = '_customize-input-' . $this->id;
		$name = '_customize-radio-' . $this->id;
	    ?>
		<?php if ( ! empty( $this->label ) ) : ?>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
        <div class="sm-palette-filter">
		    <?php foreach ( $this->choices as $value => $label ) { ?>
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

                    <?php
                    $master_color_controls_ids = array(
	                    "sm_color_primary",
	                    "sm_color_secondary",
	                    "sm_color_tertiary",
	                    "sm_dark_primary",
	                    "sm_dark_secondary",
	                    "sm_dark_tertiary",
	                    "sm_light_primary",
	                    "sm_light_secondary",
	                    "sm_light_tertiary"
                    );

                    $current_palette = '<div class="colors">';
                        foreach ( $master_color_controls_ids as $setting_id ) {
                        $current_palette .=
                        '<div class="color ' . $setting_id . '" data-setting="' . $setting_id . '">' . PHP_EOL .
                            '<div class="picker"></div>' . PHP_EOL .
                        '</div>' . PHP_EOL;
                        }
                    $current_palette .= '</div>';

                    echo $current_palette;
                    ?>
                </label>
		    <?php } ?>
        </div>

	<?php }
}
