<?php

/**
 * Class Pix_Customize_Select2_Control
 * A simple Select2 Control
 */
class Pix_Customize_Preset_Control extends Pix_Customize_Control {
	public $type    = 'preset';
	public $choices_type    = 'select';
	public $description    = null;

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

			case 'awesome' : { ?>
				<label>
					<?php if ( ! empty( $this->label ) ) { ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php } ?>

					<div class="customify_preset awesome_presets">
						<?php

						$google_links = array();

						foreach ( $this->choices as $value => $setts ){
							if ( ! isset( $setts['options']) || ! isset( $setts['label'] ) ) {
								continue;
							}

							$preset_style = ' style="';
							$preset_name_style = ' style="';
							$preset_text_color = ' style="';

							if ( isset( $setts['colors'] ) ) {

								if ( isset( $setts['colors']['main'] ) ) {
									$preset_style .= 'background-color: ' .  $setts['colors']['main'] . ';';
								}

								if ( isset( $setts['colors']['second'] ) ) {

									$this_preset_color = $setts['colors']['second'];

									if ( $this->isLight($this_preset_color) ) {
										$this_preset_color = '#000000';
									} else {
										$this_preset_color = '#ffffff';
									}

									$preset_name_style .= 'color: ' .$this_preset_color . ';background-color: ' .  $setts['colors']['second'] . '; border-color: ' .  $setts['colors']['second'];
								}

								if ( isset( $setts['colors']['text'] ) ) {
									$preset_text_color .= 'color: ' .  $setts['colors']['text'] . ';"';
								}
							}

							$preset_style .= '"';
							$preset_name_style .= '"';
							$first_font = $second_font = '';
							if ( isset( $setts['fonts'] ) ) {

								if ( isset( $setts['fonts']['main'] ) ) {
									$first_font = ' style="font-family: ' . $setts['fonts']['main'] . '"' ;
									$google_links[] = str_replace( ' ', '+', $setts['fonts']['main'] );
								}

								if ( isset( $setts['fonts']['second'] ) ) {
									$second_font = ' style="font-family: ' . $setts['fonts']['second'] . '"' ;
									$google_links[] = str_replace( ' ', '+', $setts['fonts']['second'] );
								}
							}

							$label = $setts['label'];
							$options = $setts['options'];
							$data = ' data-options=\'' . json_encode($options) . '\'';?>
							<div class="awesome_preset" <?php echo $preset_text_color; ?>>
								<input <?php $this->link(); echo 'name="' .  $this->setting->id . '" type="radio" value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . $data .' >'  . '</input>'; ?>
								<div class="preset-wrap">
                                    <div class="preset-color" <?php echo $preset_style; ?>>
                                        <span class="first-font" <?php echo $first_font; ?>><?php echo substr( get_bloginfo('name'), 0, 2); ?></span>
                                        <span class="secondary-font" <?php echo $second_font; ?>>AaBbCc</span>
                                    </div>
                                    <div class="preset-name" <?php echo $preset_name_style; ?>>
                                        <?php echo $label; ?>
                                    </div>
                                </div>
							</div>
						<?php }

						// ok now we have our preview fonts, let's ask them from google
						// note that we request only these chars "AaBbCc" so it should be a small request
						echo '<link href="http://fonts.googleapis.com/css?family=' . implode('|', $google_links ) . '&text=AaBbCc' . substr( get_bloginfo('name'), 0, 2) . '" rel=\'stylesheet\' type=\'text/css\'>';?>
					</div>

					<?php
					if ( ! empty( $this->description ) ) { ?>
						<span class="description customize-control-description"><?php echo $this->description; ?></span>
					<?php } ?>
				</label>
				<?php break;
			}

			default:
				break;
		}
	}

	/**
	 * Returns whether or not given color is considered "light"
	 * @param string|Boolean $color
	 * @return boolean
	 */
	public function isLight( $color = FALSE ){
		// Get our color
		$color = ($color) ? $color : $this->_hex;
		// Calculate straight from rbg
		$r = hexdec($color[0].$color[1]);
		$g = hexdec($color[2].$color[3]);
		$b = hexdec($color[4].$color[5]);
		return (( $r*299 + $g*587 + $b*114 )/1000 > 130);
	}
}
