<?php

/**
 * Class Pix_Customize_Typography_Control
 * A complex Typography Control
 */
class Pix_Customize_Typography_Control extends Pix_Customize_Control {
	public $type = 'typography';

	public $font_weight = true;
	public $subsets = true;
	public $load_all_weights = false;
	public $recommended = array();
	public $typekit_fonts = array();
	public $current_value;
	public $default;

	/**
	 * The unique CSS ID value to be used throughout this control.
	 *
	 * @var string
	 */
	protected $CSSID;

	/**
	 * Constructor.
	 *
	 * Supplied $args override class property defaults.
	 *
	 * If $args['settings'] is not defined, use the $id as the setting ID.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Customize_Manager $manager
	 * @param string $id
	 * @param array $args
	 */
	public function __construct( $manager, $id, $args = array() ) {
		global $wp_customize;

		parent::__construct( $manager, $id, $args );

		$this->CSSID    = $this->get_CSS_ID();

		$this->typekit_fonts = apply_filters('customify_filter_typekit_fonts_list', get_option( 'typekit_fonts' ) );

		// Since 4.7 all the customizer data is saved in a post type named changeset.
		// This is how we get it.
		if ( method_exists( $wp_customize, 'changeset_data' ) ) {
			$changeset_data = $wp_customize->changeset_data();

			if ( isset( $changeset_data[$this->setting->id] ) ) {
				$this->current_value = $changeset_data[$this->setting->id]['value'];
				return;
			}
		}

		$this->current_value = $this->value();
	}

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		// The value() will consider the defined default value and return that if that is the case.
		$current_value = Customify_Fonts_Global::maybeDecodeValue( $this->value() );
		$current_value = Customify_Fonts_Global::standardizeFontValues( $current_value );

		if ( empty( $current_value ) ) {
			$current_value = $this->get_default_values();
		}

		// Make sure it is an object from here going forward.
		$current_value = (object) $current_value;

		$current_font_family = '';
		if ( isset( $current_value->font_family ) ) {
			$current_font_family = $current_value->font_family;
		}

		$current_font_type = 'std_font';
		$current_font_details = array();
		if ( ! empty( $current_font_family ) ) {
			$current_font_type = Customify_Fonts_Global::instance()->determineFontType( $current_font_family );
			$current_font_details = Customify_Fonts_Global::instance()->getFontDetails( $current_font_family, $current_font_type );
		}

		if ( isset( $current_value->load_all_weights ) ) {
			$this->load_all_weights = $current_value->font_load_all_weights;
		} ?>
		<label class="customify_typography">
			<?php if ( ! empty( $this->label ) ) { ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php }


			$select_data = '';
			if ( $this->load_all_weights ) {
				$select_data .= ' data-load_all_weights="true"';
			}

			/**
			 * This input will hold the values of this typography field
			 */ ?>
			<input class="customify_typography_values"
			       id="<?php echo esc_attr( $this->CSSID ); ?>"
			       type="hidden" <?php $this->link(); ?>
			       value="<?php // The value will be set by the Customizer core logic from the _wpCustomizeSettings.settings data. ?>"
			       data-default="<?php echo esc_attr( PixCustomifyPlugin::encodeURIComponent( json_encode( $current_value ) ) ); ?>"
			/>
			<select class="customify_typography_font_family"<?php echo $select_data; ?>>

				<?php
				// Allow others to add options here
				do_action( 'customify_typography_font_family_before_options', $current_font_family, $current_value ); ?>

				<?php
				if ( ! empty( $this->typekit_fonts ) ) {
					echo '<optgroup label="' . esc_attr__( 'Typekit', 'customify' ) . '">';
					foreach ( $this->typekit_fonts as $key => $font ) {
						self::output_font_option( $font['css_names'][0], $current_font_family, $font, 'typekit' );
					}
					echo "</optgroup>";
				}

				// Allow others to add options here
				do_action( 'customify_typography_font_family_before_recommended_fonts_options', $current_font_family, $current_value );

				if ( ! empty( $this->recommended ) ) {

					echo '<optgroup label="' . esc_attr__( 'Recommended', 'customify' ) . '">';

					foreach ( $this->recommended as $key => $value ) {
						$font_details = array();
						if ( is_numeric( $key ) && is_string( $value ) ) {
							// This means a simple font family string.
							$font_family = $value;
						} elseif ( is_string( $key ) && ! empty( $value ) ) {
							$font_family = $key;
							$font_details = $value;
						} else {
							// We can't use this entry.
							continue;
						}

						if( isset( $this->typekit_fonts[ $font_family ] ) ) {
							$font_type = 'typekit';
						} else {
							$font_type = Customify_Fonts_Global::instance()->determineFontType( $font_family );
							if ( empty( $font_details ) ) {
								$font_details = Customify_Fonts_Global::instance()->getFontDetails( $font_family, $font_type );
							}
						}

						self::output_font_option( $font_family, $current_font_family, $font_details, $font_type );
					}
					echo "</optgroup>";
				}

				// Allow others to add options here
				do_action( 'customify_typography_font_family_before_standard_fonts_options', $current_font_family, $current_value );

				if ( PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_standard_fonts' ) ) {

					echo '<optgroup label="' . esc_attr__( 'Standard fonts', 'customify' ) . '">';
					foreach ( Customify_Fonts_Global::instance()->get_std_fonts() as $key => $font ) {
						self::output_font_option( $key, $current_font_family, $font, 'std_font' );
					}
					echo "</optgroup>";
				}

				// Allow others to add options here
				do_action( 'customify_typography_font_family_before_google_fonts_options' );

				if ( PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_google_fonts' ) ) {

					if ( PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_group_google_fonts' ) ) {

						$grouped_google_fonts = array();
						foreach ( Customify_Fonts_Global::instance()->get_google_fonts() as $key => $font ) {
							if ( isset( $font['category'] ) ) {
								$grouped_google_fonts[ $font['category'] ][] = $font;
							}
						}

						foreach ( $grouped_google_fonts as $group_name => $group ) {
							echo '<optgroup label="' . esc_attr__( 'Google fonts', 'customify' ) . ' ' . $group_name . '">';
							foreach ( $group as $key => $font ) {
								self::output_font_option( $key, $current_font_family, $font );
							}
							echo "</optgroup>";
						}

					} else {
						echo '<optgroup label="' . esc_attr__( 'Google fonts', 'customify' ) . '">';
						foreach ( Customify_Fonts_Global::instance()->get_google_fonts() as $key => $font ) {
							self::output_font_option( $key, $current_font_family, $font );
						}
						echo "</optgroup>";
					}
				} ?>
			</select>
		</label>
		<ul class="options">
			<?php
			$display = 'none';
			if ( ! $this->load_all_weights && $this->font_weight && ! empty( $current_font_details['variants'] ) ) {
				$display = 'inline-block';
			} ?>
			<li class="customify_weights_wrapper" style="display: <?php echo $display; ?>">
				<select class="customify_typography_font_weight">
					<?php
					$selected = false;
					if ( isset( $current_value->font_variant ) ) {
						$selected = $current_value->font_variant;
					}

					if ( ! empty( $current_font_details['variants'] ) ) {
						foreach ( $current_font_details['variants'] as $variant ) {
							$attrs = '';
							if ( $variant == $selected ) {
								$attrs = ' selected="selected"';
							}

							echo '<option value="' . esc_attr( $variant ) . '" ' . $attrs . '> ' . $variant . '</option>';
						}
					} ?>
				</select>
			</li>
			<?php
			$display = 'none';
			if ( $this->subsets && ! empty( $current_font_details['subsets'] ) ) {
				$display = 'inline-block';
			}?>
			<li class="customify_subsets_wrapper" style="display: <?php echo $display; ?>">
				<select multiple class="customify_typography_font_subsets">
					<?php
					$selected = array();
					if ( isset( $current_value->selected_subsets ) ) {
						$selected = $current_value->selected_subsets;
					}

					if ( ! empty( $current_font_details['subsets'] ) ) {
						foreach ( $current_font_details['subsets'] as $key => $subset ) {
							$attrs = '';
							if ( in_array( $subset, (array) $selected ) ) {
								$attrs .= ' selected="selected"';
							}

							echo '<option value="' . esc_attr( $subset ) . '" ' . $attrs . '> ' . $subset . '</option>';
						}
					} ?>
				</select>
			</li>
			<?php ?>
		</ul>
		<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php endif; ?>
	<?php }

	/**
	 * This method displays an <option> tag from the given params
	 *
	 * @param string       $font_family
	 * @param string       $active_font_family
	 * @param string|array $font_details
	 * @param string       $font_type
	 */
	public static function output_font_option( $font_family, $active_font_family, $font_details, $font_type = 'google_font' ) {
		if ( empty( $font_type ) ) {
			$font_type = 'std_font';
		}

		// Initialize data attributes.
		$data = 'data-type="' . esc_attr( $font_type ) . '"';

		// Determine if the font is selected
		$selected = ( false !== $active_font_family && $active_font_family === $font_family ) ? ' selected="selected" ' : '';

		// Now determine if we have a "pretty" display for this font family.
		$font_family_display = $font_family;
		if ( ! empty( $font_details['font_family_display'] ) ) {
			$font_family_display = $font_details['font_family_display'];
		} elseif ( ! empty( $font_details['name'] ) ) {
			$font_family_display = $font_details['name'];
		}

		// Determine the option class.
		$option_class = ( false !== strpos( $font_type, '_font' ) ) ? $font_type : $font_type . '_font';

		// Output the markup.
		echo '<option class="' . esc_attr( $option_class ) . '" value="' . esc_attr( $font_family ) . '" ' . $selected . $data . '>' . $font_family_display . '</option>';
	}

	function get_default_values() {

		$defaults = array();

		if ( isset( $this->default ) && is_array( $this->default ) ) {

			// Handle special logic for when the $value array is not an associative array.
			if ( ! $this->isAssocArray( $this->default ) ) {

				// The first entry is the font-family.
				if ( isset( $this->default[0] ) ) {
					$defaults['font_family'] = $this->default[0];
				}

				// The second entry is the variant.
				if ( isset( $this->default[1] ) ) {
					$defaults['font_variant'] = $this->default[1];
				}
			} else {
				$defaults = $this->default;
			}
		}

		return Customify_Fonts_Global::standardizeFontValues( $defaults );
	}

	protected function get_CSS_ID() {
		return str_replace( array( '[', ']' ), '_', $this->id );
	}

	protected function isAssocArray( $array ) {
		return ( $array !== array_values( $array ) );
	}
}
