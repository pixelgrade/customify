<?php

/**
 * Class Pix_Customize_Radio_Image_Control
 * A simple Select2 Control
 */
class Pix_Customize_Radio_Image_Control extends Pix_Customize_Control {
	public $type    = 'radio_image';
	public $choices_type    = 'radio';
	public $description    = null;

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() {

		switch ( $this->choices_type ) {

			case 'radio' : { ?>
				<label>
					<?php if ( ! empty( $this->label ) ) { ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php } ?>

					<div class="customify_radio_image">
						<?php
						foreach ( $this->choices as $value => $image_url ){

							if ( empty( $image_url ) ) {
								$image_url = plugins_url() . '/customify/images/default_radio_image.png';
							} ?>
							<label>
								<input <?php $this->link(); echo 'name="' .  $this->setting->id . '" type="radio" value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) .' ></input>';?>
								<img src="<?php echo $image_url; ?>" style="width: 50px; display: block; height: auto;"></span>
							</label>
						<?php } ?>
					</div>

					<?php if ( ! empty( $this->description ) ) { ?>
						<span class="description customize-control-description"><?php echo $this->description; ?></span>
					<?php } ?>
				</label>
			<?php break;
			}

			case 'buttons' : { ?>
				<label>
					<?php if ( ! empty( $this->label ) ) { ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php } ?>

					<div class="customify_radio_image radio_buttons">
						<?php
						foreach ( $this->choices as $value => $setts ){
							if ( ! isset( $setts['options']) || ! isset( $setts['label'] ) ) {
								continue;
							}
							$color = '';
							if ( isset( $setts['color'] ) ) {
								$color .= ' style="border-left-color: ' . $setts['color'] . '; color: ' . $setts['color'] . ';"';
							}

							$label = $setts['label'];
							$options = $setts['options'];
							$data = ' data-options=\'' . json_encode($options) . '\'';?>

							<fieldset class="customify_radio_button">
								<input <?php $this->link(); echo 'name="' .  $this->setting->id . '" type="radio" value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . $data .' />'; ?>
								<label class="button" for="<?php echo $this->setting->id; ?>" <?php echo $color; ?>>
									<?php echo $label; ?>
								</label>
							</fieldset>
						<?php } ?>
					</div>

					<?php if ( ! empty( $this->description ) ) { ?>
						<span class="description customize-control-description"><?php echo $this->description; ?></span>
					<?php } ?>
				</label>
				<?php break;
			}

			default:
				break;
		}
	}
}
