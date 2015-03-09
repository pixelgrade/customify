<?php

/**
 * Class Pix_Customize_Select2_Control
 * A simple Select2 Control
 */
class Pix_Customize_Preset_Control extends Pix_Customize_Control {
	public $type    = 'preset';
	public $choices_type    = 'select';

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() {

		switch ( $this->choices_type ) {

			case 'select' : { ?>
			<label>
				<?php if ( ! empty( $this->label ) ) { ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php }
				if ( ! empty( $this->description ) ) { ?>
					<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<?php } ?>

				<select <?php $this->link(); ?> class="customify_preset select">
					<?php
					foreach ( $this->choices as $value => $setts ){
						if ( ! isset( $setts['options']) || ! isset( $setts['label'] ) ) {
							continue;
						}
						$label = $setts['label'];
						$options = $setts['options'];
						$data = ' data-options=\'' . json_encode($options) . '\'';
						echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . $data . ' >' . $label . '</option>';
					} ?>
				</select>
			</label>
			<?php break;
			}

			case 'radio' : { ?>
				<label>
					<?php if ( ! empty( $this->label ) ) { ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php }
					if ( ! empty( $this->description ) ) { ?>
						<span class="description customize-control-description"><?php echo $this->description; ?></span>
					<?php } ?>

					<div class="customify_preset radio">
						<?php
						foreach ( $this->choices as $value => $setts ){
							if ( ! isset( $setts['options']) || ! isset( $setts['label'] ) ) {
								continue;
							}
							$color = '';
							if ( isset( $setts['color'] ) ) {
								$color .= ' style="background-color: ' . $setts['color'] . '"';
							}

							$label = $setts['label'];
							$options = $setts['options'];
							$data = ' data-options=\'' . json_encode($options) . '\'';?>
							<input <?php $this->link(); echo 'name="' .  $this->setting->id . '" type="radio" value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . $data . $color .' >' . $label . '</input>';
						} ?>
					</div>
				</label>
			<?php break;
			}

			case 'buttons' : { ?>
				<label>
					<?php if ( ! empty( $this->label ) ) { ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php }
					if ( ! empty( $this->description ) ) { ?>
						<span class="description customize-control-description"><?php echo $this->description; ?></span>
					<?php } ?>

					<div class="customify_preset radio_buttons">
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
				</label>
				<?php break;
			}

			default:
				break;
		}
	}
}
