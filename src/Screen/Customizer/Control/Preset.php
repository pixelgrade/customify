<?php
/**
 * Customizer preset control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

use Pixelgrade\Customify\StyleManager\FontPalettes;
use function Pixelgrade\Customify\get_customizer_config;
use function Pixelgrade\Customify\get_option_details;
use function Pixelgrade\Customify\plugin;

/**
 * Customizer preset control class.
 *
 * This handles the 'preset' control type.
 *
 * @since 3.0.0
 */
class Preset extends BaseControl {
	/**
	 * Type.
	 *
	 * @var string
	 */
	public $type = 'preset';

	public string $choices_type = 'select';

	/**
	 * Style Manager Font Palettes service.
	 *
	 * @var FontPalettes
	 */
	protected FontPalettes $sm_font_palettes;

	/**
	 * Constructor.
	 *
	 * Supplied $args override class property defaults.
	 *
	 * If $args['settings'] is not defined, use the $id as the setting ID.
	 *
	 *
	 * @param \WP_Customize_Manager $manager
	 * @param string                $id
	 * @param array                 $args
	 */
	public function __construct( $manager, $id, $args = [] ) {
		parent::__construct( $manager, $id, $args );

		$this->sm_font_palettes = $args['sm_font_palettes_service'];
	}

	/**
	 * Render the control's content.
	 */
	public function render_content() {

		do_action( 'customify_before_preset_control', $this );

		switch ( $this->choices_type ) {

			case 'select' :
			{ ?>
				<label>
					<?php if ( ! empty( $this->label ) ) { ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php }
					if ( ! empty( $this->description ) ) { ?>
						<span class="description customize-control-description"><?php echo $this->description; ?></span>
					<?php } ?>

					<select <?php $this->link(); ?> class="js-customify-preset select">
						<?php
						foreach ( $this->choices as $choice_value => $choice_config ) {
							if ( ! isset( $choice_config['options'] ) || ! isset( $choice_config['label'] ) ) {
								continue;
							}
							$label   = $choice_config['label'];
							$options = $this->convertChoiceOptionsIdsToSettingIds( $choice_config['options'] );
							$data    = ' data-options=\'' . json_encode( $options ) . '\'';
							echo '<option value="' . esc_attr( $choice_value ) . '" ' . selected( $this->value(), $choice_value, false ) . $data . ' >' . $label . '</option>';
						} ?>
					</select>
				</label>
				<?php break;
			}

			case 'radio' :
			{ ?>
				<label>
					<?php if ( ! empty( $this->label ) ) { ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php }
					if ( ! empty( $this->description ) ) { ?>
						<span class="description customize-control-description"><?php echo $this->description; ?></span>
					<?php } ?>

					<div class="js-customify-preset radio customize-control-radio">
						<?php
						foreach ( $this->choices as $choice_value => $choice_config ) {
							if ( ! isset( $choice_config['options'] ) || ! isset( $choice_config['label'] ) ) {
								continue;
							}
							$color = '';
							if ( isset( $choice_config['color'] ) ) {
								$color .= ' style="background-color: ' . $choice_config['color'] . '"';
							}

							$label   = $choice_config['label'];
							$options = $this->convertChoiceOptionsIdsToSettingIds( $choice_config['options'] );
							$data    = ' data-options=\'' . json_encode( $options ) . '\''; ?>

							<span class="customize-inside-control-row">
								<input <?php $this->link();
								echo 'name="' . $this->setting->id . '" id="' . esc_attr( $choice_value ) . '" type="radio" value="' . esc_attr( $choice_value ) . '" ' . selected( $this->value(), $choice_value, false ) . $data . $color . ' />'; ?>
								<label for="<?php echo esc_attr( $choice_value ); ?>">
									<?php echo $label; ?>
								</label>
							</span>
						<?php } ?>
					</div>
				</label>
				<?php break;
			}

			case 'buttons' :
			{ ?>
				<label>
					<?php if ( ! empty( $this->label ) ) { ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php }
					if ( ! empty( $this->description ) ) { ?>
						<span class="description customize-control-description"><?php echo $this->description; ?></span>
					<?php } ?>

					<div class="js-customify-preset radio_buttons">
						<?php
						foreach ( $this->choices as $choice_value => $choice_config ) {
							if ( ! isset( $choice_config['options'] ) || ! isset( $choice_config['label'] ) ) {
								continue;
							}
							$color = '';
							if ( isset( $choice_config['color'] ) ) {
								$color .= ' style="border-left-color: ' . $choice_config['color'] . '; color: ' . $choice_config['color'] . ';"';
							}

							$label   = $choice_config['label'];
							$options = $this->convertChoiceOptionsIdsToSettingIds( $choice_config['options'] );
							$data    = ' data-options=\'' . json_encode( $options ) . '\''; ?>

							<fieldset class="customify_radio_button">
								<input <?php $this->link();
								echo 'name="' . $this->setting->id . '" type="radio" value="' . esc_attr( $choice_value ) . '" ' . selected( $this->value(), $choice_value, false ) . $data . ' />'; ?>
								<label class="button" for="<?php echo $this->setting->id; ?>" <?php echo $color; ?>>
									<?php echo $label; ?>
								</label>
							</fieldset>
						<?php } ?>
					</div>
				</label>
				<?php break;
			}

			case 'color_palette' :
			{ ?>
				<?php if ( ! empty( $this->label ) ) { ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php }

				if ( ! empty( $this->description ) ) { ?>
					<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<?php } ?>

				<div class="js-customify-preset js-color-palette customize-control-color-palette">
					<?php
					foreach ( $this->choices as $choice_value => $choice_config ) {
						if ( empty( $choice_config['options'] ) ) {
							continue;
						}

						// Make sure that the defaults are in place
						$choice_config = wp_parse_args( $choice_config, [
							'label'   => '',
							'preview' => [],
						] );

						// Make sure that the preview defaults are in place
						$choice_config['preview'] = wp_parse_args( $choice_config['preview'], [
							'sample_letter'        => 'A',
							'background_image_url' => plugin()->get_url( 'images/color_palette_image.jpg' ),
						] );

						// Determine a (primary) color with fallback for missing options
						$sm_color = '#777777';
						if ( isset( $choice_config['options']['sm_color_primary'] ) ) {
							$sm_color = $choice_config['options']['sm_color_primary'];
						} elseif ( isset( $choice_config['options']['sm_color_secondary'] ) ) {
							$sm_color = $choice_config['options']['sm_color_secondary'];
						} elseif ( isset( $choice_config['options']['sm_color_tertiary'] ) ) {
							$sm_color = $choice_config['options']['sm_color_tertiary'];
						} elseif ( isset( $choice_config['options']['sm_color_quaternary'] ) ) {
							$sm_color = $choice_config['options']['sm_color_quaternary'];
						} elseif ( isset( $choice_config['options']['sm_color_quinary'] ) ) {
							$sm_color = $choice_config['options']['sm_color_quinary'];
						}

						// Determine a (primary) light color with fallback for missing options
						$sm_light = '#FFFFFF';
						if ( isset( $choice_config['options']['sm_light_primary'] ) ) {
							$sm_light = $choice_config['options']['sm_light_primary'];
						} elseif ( isset( $choice_config['options']['sm_light_secondary'] ) ) {
							$sm_light = $choice_config['options']['sm_light_secondary'];
						} elseif ( isset( $choice_config['options']['sm_light_tertiary'] ) ) {
							$sm_light = $choice_config['options']['sm_light_tertiary'];
						} elseif ( isset( $choice_config['options']['sm_light_quaternary'] ) ) {
							$sm_light = $choice_config['options']['sm_light_quaternary'];
						} elseif ( isset( $choice_config['options']['sm_light_quinary'] ) ) {
							$sm_light = $choice_config['options']['sm_light_quinary'];
						}

						$label   = $choice_config['label'];
						$options = $this->convertChoiceOptionsIdsToSettingIds( $choice_config['options'] );
						$data    = ' data-options=\'' . json_encode( $options ) . '\''; ?>

						<span
							class="customize-inside-control-row <?php echo( (string) $this->value() === (string) $choice_value ? 'current-color-palette' : '' ); ?>"
							style="background-image: url( <?php echo esc_url( $choice_config['preview']['background_image_url'] ); ?> );">
                            <input <?php $this->link();
							echo 'name="' . $this->setting->id . '" id="' . esc_attr( $choice_value ) . '-color-palette" type="radio" value="' . esc_attr( $choice_value ) . '" ' . selected( $this->value(), $choice_value, false ) . $data . ' />'; ?>
                            <label for="<?php echo esc_attr( $choice_value ) . '-color-palette'; ?>">
                                <span class="label__inner"
                                      style="color: <?php echo esc_attr( $this->lightOrDark( $sm_light ) ); ?>; background: <?php echo esc_attr( $sm_light ); ?>;">
                                    <i class="preview__letter"
                                       style="background: <?php echo $sm_color; ?>"><?php echo $choice_config['preview']['sample_letter']; ?></i>
                                    <i class="preview__letter--checked"
                                       style="background-color: <?php echo $sm_color; ?>; background-image: url('<?php echo plugin()->get_url( 'images/check.svg' ); ?>')"></i>
                                    <?php echo esc_html( $label ); ?>
                                </span>
                            </label>
                            <div class="palette">
                                <?php foreach ( $choice_config['options'] as $color_setting_id => $color_value ) {
	                                echo '<div class="palette__item ' . esc_attr( $color_setting_id ) . '" style="background: ' . esc_attr( $color_value ) . '"></div>' . "\n";
                                } ?>
                            </div>
                        </span>
					<?php } ?>
				</div>

				<?php break;
			}

			case 'font_palette' :
			{ ?>
				<?php if ( ! empty( $this->label ) ) { ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php }

				if ( ! empty( $this->description ) ) { ?>
					<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<?php } ?>

				<div class="js-customify-preset js-font-palette customize-control-font-palette">
					<?php
					$choices = $this->sm_font_palettes->preprocess_config( $this->choices );
					foreach ( $choices as $choice_value => $choice_config ) {
						if ( empty( $choice_config['options'] ) && empty( $choice_config['fonts_logic'] ) ) {
							continue;
						}

						// Make sure that the defaults are in place
						$choice_config = wp_parse_args( $choice_config, [
							'label'   => '',
							'preview' => [],
						] );

						// Make sure that the preview defaults are in place
						$choice_config['preview'] = wp_parse_args( $choice_config['preview'], [
							'sample_letter'        => 'A',
							'background_image_url' => plugin()->get_url( 'images/color_palette_image.jpg' ),
						] );

						$label = $choice_config['label'];

						if ( empty( $choice_config['options'] ) ) {
							$choice_config['options'] = [];
						}
						$options = $this->convertChoiceOptionsIdsToSettingIds( $choice_config['options'] );
						$data    = ' data-options=\'' . json_encode( $options ) . '\'';

						if ( empty( $choice_config['fonts_logic'] ) ) {
							$choice_config['fonts_logic'] = [];
						}
						$fonts = $this->convertChoiceOptionsIdsToSettingIds( $choice_config['fonts_logic'] );
						$data  .= ' data-fonts_logic=\'' . json_encode( $fonts ) . '\'';
						?>

						<span
							class="customize-inside-control-row <?php echo( (string) $this->value() === (string) $choice_value ? 'current-font-palette' : '' ); ?>"
							style="background-image: url( <?php echo esc_url( $choice_config['preview']['background_image_url'] ); ?> );">
                            <input <?php $this->link();
							echo 'name="' . esc_attr( $this->setting->id ) . '" id="' . esc_attr( $choice_value ) . '-font-palette" type="radio" value="' . esc_attr( $choice_value ) . '" ' . selected( $this->value(), $choice_value, false ) . $data . ' />'; ?>
							<label for="<?php echo esc_attr( $choice_value ) . '-font-palette'; ?>">
								<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
							</label>
                        </span>
					<?php } ?>
				</div>

				<?php break;
			}

			case 'awesome' :
			{ ?>
				<label>
					<?php if ( ! empty( $this->label ) ) { ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php } ?>

					<div class="js-customify-preset awesome_presets">
						<?php

						$google_links = [];

						foreach ( $this->choices as $choice_value => $choice_config ) {
							if ( ! isset( $choice_config['options'] ) || ! isset( $choice_config['label'] ) ) {
								continue;
							}

							$preset_style      = ' style="background-color: #444;';
							$preset_name_style = ' style=" color: #000; background-color: #ccc; border-color: #aaa;';
							$preset_text_color = ' style=" color: #ebebeb';

							$first_font = $second_font = '';
							if ( isset( $choice_config['preview'] ) ) {

								if ( isset( $choice_config['preview']['background-card'] ) ) {
									$preset_style = ' style="';
									$preset_style .= 'background-color: ' . $choice_config['preview']['background-card'] . ';';
								}

								if ( isset( $choice_config['preview']['background-label'] ) ) {

									$this_preset_color = $choice_config['preview']['background-label'];

									if ( $this->isLight( $this_preset_color ) ) {
										$this_preset_color = '#000000';
									} else {
										$this_preset_color = '#ffffff';
									}
									$preset_name_style = ' style="';
									$preset_name_style .= 'color: ' . $this_preset_color . ';background-color: ' . $choice_config['preview']['background-label'] . '; border-color: ' . $choice_config['preview']['background-label'];
								}

								if ( isset( $choice_config['preview']['color-text'] ) ) {
									$preset_text_color = ' style="';
									$preset_text_color .= 'color: ' . $choice_config['preview']['color-text'] . ';"';
								}

								if ( isset( $choice_config['preview']['font-main'] ) ) {
									$first_font     = ' style="font-family: ' . $choice_config['preview']['font-main'] . '"';
									$google_links[] = str_replace( ' ', '+', $choice_config['preview']['font-main'] );
								}

								if ( isset( $choice_config['preview']['font-alt'] ) ) {
									$second_font    = ' style="font-family: ' . $choice_config['preview']['font-alt'] . '"';
									$google_links[] = str_replace( ' ', '+', $choice_config['preview']['font-alt'] );
								}
							}

							$preset_style      .= '"';
							$preset_text_color .= '"';
							$preset_name_style .= '"';

							$label   = $choice_config['label'];
							$options = $this->convertChoiceOptionsIdsToSettingIds( $choice_config['options'] );
							$data    = ' data-options=\'' . json_encode( $options ) . '\''; ?>
							<div class="awesome_preset" <?php echo $preset_text_color; ?>>
								<input <?php $this->link();
								echo 'name="' . $this->setting->id . '" type="radio" value="' . esc_attr( $choice_value ) . '" ' . selected( $this->value(), $choice_value, false ) . $data . ' >' . '</input>'; ?>
								<div class="preset-wrap">
									<div class="preset-color" <?php echo $preset_style; ?>>
										<span
											class="first-font" <?php echo $first_font; ?>><?php echo substr( get_bloginfo( 'name' ), 0, 2 ); ?></span>
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
						echo '<link href="//fonts.googleapis.com/css?family=' . implode( '|', $google_links ) . '&text=AaBbCc' . substr( get_bloginfo( 'name' ), 0, 2 ) . '" rel=\'stylesheet\' type=\'text/css\'>'; ?>
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

		do_action( 'customify_after_preset_control', $this );
	}

	/**
	 * Returns whether or not given color is considered "light"
	 *
	 * @param string|Boolean $color
	 *
	 * @return boolean
	 */
	protected function isLight( $color = false ): bool {
		if ( false === $color ) {
			return false;
		}

		// Make sure that the hex color string is free from whitespace and #
		$color = trim( $color, ' \t\n\r #' );

		// Extract the rbg values.
		$c_r = hexdec( substr( $color, 0, 2 ) );
		$c_g = hexdec( substr( $color, 2, 2 ) );
		$c_b = hexdec( substr( $color, 4, 2 ) );

		return ( ( $c_r * 299 + $c_g * 587 + $c_b * 114 ) / 1000 > 130 );
	}

	/**
	 * Detect if we should use a light or dark color on a background color.
	 *
	 * Taken from WooCommerce: woocommerce/includes/wc-formatting-functions.php
	 * @link http://woocommerce.wp-a2z.org/oik_api/wc_light_or_dark/
	 *
	 * @param mixed  $color Color.
	 * @param string $dark  Darkest reference.
	 *                      Defaults to '#000000'.
	 * @param string $light Lightest reference.
	 *                      Defaults to '#FFFFFF'.
	 *
	 * @return string
	 */
	protected function lightOrDark( $color, $dark = '#000000', $light = '#FFFFFF' ): string {

		// Make sure that the hex color string is free from whitespace and #
		$color = trim( $color, ' \t\n\r #' );

		$c_r = hexdec( substr( $color, 0, 2 ) );
		$c_g = hexdec( substr( $color, 2, 2 ) );
		$c_b = hexdec( substr( $color, 4, 2 ) );

		$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

		return $brightness > 180 ? $dark : $light;
	}

	/**
	 * We will receive the choice options IDs as defined in the Customify config (the options array keys) and we will convert them to actual setting IDs.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	protected function convertChoiceOptionsIdsToSettingIds( $options ): array {
		$settings = [];

		if ( empty( $options ) ) {
			return $settings;
		}

		$customizer_config = get_customizer_config();

		// first check the very needed options name
		if ( empty( $customizer_config['opt-name'] ) ) {
			return $settings;
		}
		$options_name = $customizer_config['opt-name'];

		// Coerce a single string into an array and treat it as a field ID.
		if ( is_string( $options ) ) {
			$options = [ $options => '' ];
		}

		foreach ( $options as $option_id => $option_value ) {
			$option_config = get_option_details( $option_id );
			if ( empty( $option_config ) ) {
				continue;
			}

			// If we have been explicitly given a setting ID we will use that
			if ( ! empty( $option_config['setting_id'] ) ) {
				$setting_id = $option_config['setting_id'];
			} else {
				$setting_id = $options_name . '[' . $option_id . ']';
			}

			$settings[ $setting_id ] = $option_value;
		}

		return $settings;
	}
}
