<?php

/**
 * Class Pix_Customize_Font_Control
 *
 * A complex typography control.
 */
class Pix_Customize_Font_Control extends Pix_Customize_Control {

	/**
	 * The field type.
	 *
	 * @var string
	 */
	public $type = 'font';

	/**
	 * The list of recommended fonts to show at the top of the list.
	 *
	 * @var array
	 */
	public $recommended = array();

	/**
	 * The list of sub-fields.
	 *
	 * @var array
	 */
	public $fields;

	/**
	 * The default value for each sub-field.
	 *
	 * @var array
	 */
	public $default;

	/**
	 * The current field value.
	 *
	 * @var mixed
	 */
	public $current_value;

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
	 *
	 * @param WP_Customize_Manager $manager
	 * @param string $id
	 * @param array $args
	 */
	public function __construct( $manager, $id, $args = array() ) {
		global $wp_customize;

		parent::__construct( $manager, $id, $args );

		$this->CSSID    = $this->get_CSS_ID();

		$this->add_hooks();

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

	protected function add_hooks() {
		if ( ! empty( $this->recommended ) ) {
			add_action( 'customify_font_family_select_options', array( $this, 'output_recommended_options_group' ), 10, 3 );
		}

		// Standardize the setting value at a low level so it is consistent everywhere (including in JS).
		add_filter( "customize_sanitize_js_{$this->setting->id}", array( $this, 'standardizeSettingValue'), 100, 1 );
	}

	/**
	 * Given a font value, standardize it (unencoded).
	 *
	 * @param mixed $value
	 * @param WP_Customize_Setting $setting
	 *
	 * @return string
	 */
	public function standardizeSettingValue( $value ) {
		$value = Customify_Fonts_Global::maybeDecodeValue( $value );
		$value = Customify_Fonts_Global::standardizeFontValues( $value );

		return $value;
	}

	public function output_recommended_options_group( $active_font_family, $current_value, $field_id ) {
		// We only want each instance to output it's recommended options group.
		if ( $this->id !== $field_id ) {
			return;
		}

		$this->display_recommended_options_group( $active_font_family, $current_value );
	}

	protected function display_recommended_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_recommended_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->recommended ) ) {

			echo '<optgroup label="' . esc_attr__( 'Recommended', 'customify' ) . '">';

			foreach ( $this->recommended as $font_family ) {
				self::output_font_family_option( $font_family, $active_font_family );
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_recommended_fonts_options', $active_font_family, $current_value );
	}

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		// The self::value() will consider the defined default value and return that if that is the case.
		$current_value = Customify_Fonts_Global::maybeDecodeValue( $this->current_value );
		if ( empty( $current_value ) ) {
			$current_value = $this->get_default_values();
		}

		// Make sure it is an object from here going forward.
		$current_value = (object) $current_value;

		$current_font_family = '';
		if ( isset( $current_value->font_family ) ) {
			$current_font_family = $current_value->font_family;
		}

		$current_font_details = array();
		if ( ! empty( $current_font_family ) ) {
			$current_font_details = Customify_Fonts_Global::instance()->getFontDetails( $current_font_family );
		}

		$select_data = 'data-active_font_family="' . esc_attr( $current_font_family ) . '"'; ?>
		<div class="font-options__wrapper">

			<input type="checkbox" class="font-options__checkbox js-font-option-toggle" id="tooltip_toogle_<?php echo esc_attr( $this->CSSID ); ?>">

			<?php
			$this->display_value_holder( $current_value );
			$this->display_field_title( $current_font_family, $current_font_details ); ?>

			<ul class="font-options__options-list">
				<li class="font-options__option customize-control">
					<select id="select_font_font_family_<?php echo esc_attr( $this->CSSID ); ?>" class="customify_font_family"<?php echo $select_data; ?> data-field="font_family">

						<?php
						// Allow others to add options here. This is mostly for backwards compatibility purposes.
						do_action( 'customify_font_family_before_options', $current_font_family, $current_value, $this->id );

						do_action( 'customify_font_family_select_options', $current_font_family, $current_value, $this->id );

						// Allow others to add options here. This is mostly for backwards compatibility purposes.
						do_action( 'customify_font_family_after_options', $current_font_family, $current_value, $this->id ); ?>

					</select>
				</li>
				<?php
				$this->display_font_variant_field( $current_value, $current_font_details );

				$this->display_font_subsets_field( $current_value, $current_font_details );

				$this->display_font_size_field( $current_value );

				$this->display_line_height_field( $current_value );

				$this->display_letter_spacing_field( $current_value );

				$this->display_text_align_field( $current_value );

				$this->display_text_transform_field( $current_value );

				$this->display_text_decoration_field( $current_value ); ?>
			</ul>
		</div>

		<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php endif;

		?>
	<?php }

	/**
	 * This input will hold the values of this font field
	 *
	 * @param $current_value
	 */
	protected function display_value_holder( $current_value ) { ?>
		<input class="customify_font_values" id="<?php echo esc_attr( $this->CSSID ); ?>"
		       type="hidden" <?php $this->link(); ?>
		       value="<?php // The value will be set by the Customizer core logic from the _wpCustomizeSettings.settings data. ?>"
		/>
	<?php }

	protected function display_field_title( $font_family, $current_font_details ) {
		// Determine if we have a "pretty" display for this font family
		$font_family_display = $font_family;
		if ( ! empty( $current_font_details['family_display'] ) ) {
			$font_family_display = $current_font_details['family_display'];
		}
		?>
		<label class="font-options__head  select" for="tooltip_toogle_<?php echo esc_attr( $this->CSSID ); ?>">
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="font-options__option-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<span class="font-options__font-title" id="font_name_<?php echo esc_attr( $this->CSSID ); ?>"><?php echo $font_family_display; ?></span>
		</label>
	<?php }

	protected function display_font_variant_field( $current_value, $current_font_details ) {
		// If the `font-weight` field entry is falsy, this means we don't want to use the field.
		if ( empty( $this->fields['font-weight'] ) ) {
			return;
		}

		// Display is for the initial state. Depending on the selected fonts, the JS logic will show or hide it.
		$display       = 'none';
		if ( ! empty( $current_font_details['variants'] ) && $current_font_details['variants'] !== ['regular'] ) {
			$display = 'inline-block';
		}

		$selected = false;
		if ( isset( $current_value->font_variant ) ) {
			$selected = $current_value->font_variant;
		}
		?>
		<li class="customify_weights_wrapper customize-control font-options__option" style="display: <?php echo $display; ?>;">
			<label><?php esc_html_e( 'Font Variant', 'customify' ); ?></label>
			<select class="customify_font_weight" data-field="font_variant" <?php echo ( 'none' === $display ) ?  'data-disabled="true"' : ''?>>
				<?php
				if ( ! empty( $current_font_details['variants'] ) ) {
					if ( is_string( $current_font_details['variants'] ) ) {
						$current_font_details['variants'] = array( $current_font_details['variants'] );
					}

					// Output an option with an empty value. Selecting this will NOT force a certain variant in the output.
					echo '<option value="">Auto</option>';

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
	}

	protected function display_font_subsets_field( $current_value, $current_font_details ) {
		// If the `subsets` field entry is falsy, this means we don't want to use the field.
		if ( empty( $this->fields['subsets'] ) ) {
			return;
		}

		// Display is for the initial state. Depending on the selected fonts, the JS logic will show or hide it.
		$display       = 'none';
		if ( ! empty( $current_font_details['subsets'] ) && $current_font_details['subsets'] !== ['latin'] ) {
			$display = 'inline-block';
		} ?>
		<li class="customify_subsets_wrapper customize-control font-options__option" style="display: <?php echo $display; ?>;">
			<label><?php esc_html_e( 'Languages', 'customify' ); ?></label>
			<select multiple class="customify_font_subsets" data-field="selected_subsets" <?php echo ( 'none' === $display ) ?  'data-disabled="true"' : ''?>>
				<?php
				$selected = array();
				if ( isset( $current_value->selected_subsets ) ) {
					$selected = (array) $current_value->selected_subsets;
				}

				if ( ! empty( $current_font_details['subsets'] ) ) {
					foreach ( $current_font_details['subsets'] as $key => $subset ) {
						// The latin subset is always loaded so there is no need to have it as an option.
						if ( $subset === 'latin' ) {
							continue;
						}

						$attrs = '';
						if ( in_array( $subset, $selected ) ) {
							$attrs .= ' selected="selected"';
						}

						echo '<option value="' . esc_attr( $subset ) . '" ' . $attrs . '> ' . $subset . '</option>';
					}
				}?>
			</select>
		</li>

		<?php
	}

	protected function display_font_size_field( $current_value ) {
		// If the `font-size` field entry is falsy, this means we don't want to use the field.
		if ( empty( $this->fields['font-size'] ) ) {
			return;
		}

		$attributes = $this->standardize_range_attributes( $this->fields['font-size'] );

		$value = empty( $current_value->font_size ) ? 0 : $current_value->font_size;
		// Standardize the value.
		$value = Customify_Fonts_Global::standardizeNumericalValue( $value, 'font-size', array( 'fields' => $this->fields ) );

		// We will remember the unit of the value, in case some other system pushed down a value (with an unit)
		// that is different from the field config unit. This way we can retain the unit of the value until
		// the user interacts with the control.
		?>
		<li class="customify_font_size_wrapper customize-control customize-control-range font-options__option">
			<label><?php esc_html_e( 'Font Size', 'customify' ); ?></label>
			<input type="range"
				data-field="font_size"
				<?php $this->range_field_attributes( $attributes ) ?>
				value="<?php echo esc_attr( $value['value'] ); ?>"
				data-value_unit="<?php echo esc_attr( $value['unit'] ); ?>">
		</li>
		<?php
	}

	protected function display_line_height_field( $current_value ) {
		// If the `line-height` field entry is falsy, this means we don't want to use the field.
		if ( empty( $this->fields['line-height'] ) ) {
			return;
		}

		$attributes = $this->standardize_range_attributes( $this->fields['line-height'] );

		$value = empty( $current_value->line_height ) ? 0 : $current_value->line_height;
		// Standardize the value.
		$value = Customify_Fonts_Global::standardizeNumericalValue( $value, 'line-height', array( 'fields' => $this->fields ) );

		// We will remember the unit of the value, in case some other system pushed down a value (with an unit)
		// that is different from the field config unit. This way we can retain the unit of the value until
		// the user interacts with the control.
		?>
		<li class="customify_line_height_wrapper customize-control customize-control-range font-options__option">
			<label><?php esc_html_e( 'Line height', 'customify' ); ?></label>
			<input type="range"
				data-field="line_height"
				<?php $this->range_field_attributes( $attributes ) ?>
				value="<?php echo esc_attr( $value['value'] ); ?>"
				data-value_unit="<?php echo esc_attr( $value['unit'] ); ?>">
		</li>
		<?php
	}

	protected function display_letter_spacing_field( $current_value ) {
		// If the `letter-spacing` field entry is falsy, this means we don't want to use the field.
		if ( empty( $this->fields['letter-spacing'] ) ) {
			return;
		}

		$attributes = $this->standardize_range_attributes( $this->fields['letter-spacing'] );

		$value = empty( $current_value->letter_spacing ) ? 0 : $current_value->letter_spacing;
		// We have some special cases that are valid CSS values but we need to make them compatible with the range control.
		if ( 'normal' === $value ) {
			$value = 0;
		}
		// Standardize the value.
		$value = Customify_Fonts_Global::standardizeNumericalValue( $value, 'letter-spacing', array( 'fields' => $this->fields ) );

		// We will remember the unit of the value, in case some other system pushed down a value (with an unit)
		// that is different from the field config unit. This way we can retain the unit of the value until
		// the user interacts with the control.
		?>
		<li class="customify_letter_spacing_wrapper customize-control customize-control-range font-options__option">
			<label><?php esc_html_e( 'Letter Spacing', 'customify' ); ?></label>
			<input type="range"
				data-field="letter_spacing"
				<?php $this->range_field_attributes( $attributes ) ?>
				value="<?php echo esc_attr( $value['value'] ); ?>"
				data-value_unit="<?php echo esc_attr( $value['unit'] ); ?>">
		</li>
		<?php
	}

	/**
	 * Output the custom attributes for a range sub-field.
	 *
	 * @param array $attributes
	 */
	protected function range_field_attributes( $attributes ) {

		foreach ( $attributes as $attr => $value ) {
			echo $attr . '="' . esc_attr( $value ) . '" ';
		}
	}

	protected function standardize_range_attributes( $attributes ) {
		if ( ! is_array( $attributes ) ) {
			return array(
				'min' => '',
				'max' => '',
				'step' => '',
				'unit' => '',
			);
		}

		// Make sure that if we have a numerical indexed array, we will convert it to an associative one.
		if ( ! $this->isAssocArray( $attributes ) ) {
			$defaults = array(
				'min',
				'max',
				'step',
				'unit',
			);

			$attributes = array_combine( $defaults, array_values( $attributes ) );
		}

		return $attributes;
	}

	protected function display_text_align_field( $current_font_value ) {
		// If the `text-align` field entry is falsy, this means we don't want to use the field.
		if ( empty( $this->fields['text-align'] ) ) {
			return;
		}

		$valid_values = Customify_Fonts_Global::instance()->get_valid_subfield_values( 'text_align', false );
		$value = isset( $current_font_value->text_align ) && ( empty( $valid_values ) || in_array( $current_font_value->text_align, $valid_values ) ) ? $current_font_value->text_align : 'initial'; ?>
		<li class="customify_text_align_wrapper customize-control font-options__option">
			<label><?php esc_html_e( 'Text Align', 'customify' ); ?></label>
			<select data-field="text_align">
				<?php
				foreach ( Customify_Fonts_Global::instance()->get_valid_subfield_values( 'text_align', true ) as $option_value => $option_label ) { ?>
					<option <?php $this->display_option_value( $option_value, $value ); ?>><?php echo $option_label; ?></option>
				<?php } ?>
			</select>
		</li>
		<?php
	}

	protected function display_text_transform_field( $current_font_value ) {
		// If the `text-transform` field entry is falsy, this means we don't want to use the field.
		if ( empty( $this->fields['text-transform'] ) ) {
			return;
		}

		$valid_values = Customify_Fonts_Global::instance()->get_valid_subfield_values( 'text_transform', false );
		$value = isset( $current_font_value->text_transform ) && ( empty( $valid_values ) || in_array( $current_font_value->text_transform, $valid_values ) ) ? $current_font_value->text_transform : 'none'; ?>
		<li class="customify_text_transform_wrapper customize-control font-options__option">
			<label><?php esc_html_e( 'Text Transform', 'customify' ); ?></label>
			<select data-field="text_transform">
				<?php
				foreach ( Customify_Fonts_Global::instance()->get_valid_subfield_values( 'text_transform', true ) as $option_value => $option_label ) { ?>
					<option <?php $this->display_option_value( $option_value, $value ); ?>><?php echo $option_label; ?></option>
				<?php } ?>
			</select>
		</li>
		<?php
	}

	protected function display_text_decoration_field( $current_font_value ) {
		// If the `text-decoration` field entry is falsy, this means we don't want to use the field.
		if ( empty( $this->fields['text-decoration'] ) ) {
			return;
		}

		$valid_values = Customify_Fonts_Global::instance()->get_valid_subfield_values( 'text_decoration', false );
		$value = isset( $current_font_value->text_decoration ) && ( empty( $valid_values ) || in_array( $current_font_value->text_decoration, $valid_values ) ) ? $current_font_value->text_decoration : 'none'; ?>
		<li class="customify_text_decoration_wrapper customize-control font-options__option">
			<label><?php esc_html_e( 'Text Decoration', 'customify' ); ?></label>
			<select data-field="text_decoration">
				<?php
				foreach ( Customify_Fonts_Global::instance()->get_valid_subfield_values( 'text_decoration', true ) as $option_value => $option_label ) { ?>
					<option <?php $this->display_option_value( $option_value, $value ); ?>><?php echo $option_label; ?></option>
				<?php } ?>
			</select>
		</li>
		<?php
	}

	protected function display_option_value( $value, $current_value ) {

		$return = 'value="' . esc_attr( $value ) . '"';

		if ( $value === $current_value ) {
			$return .= ' selected="selected"';
		}

		echo $return;
	}

	/**
	 * This method displays an <option> tag from the given params
	 *
	 * @param string|array $font_family
	 * @param string|false $active_font_family Optional. The active font family to add the selected attribute to the appropriate opt.
	 *                                         False to not mark any opt as selected.
	 */
	public static function output_font_family_option( $font_family, $active_font_family = false ) {
		echo self::get_font_family_option_markup( $font_family, $active_font_family );
	}

	/**
	 * This method returns an <option> tag from the given params
	 *
	 * @param string|array $font_family
	 * @param string|false $active_font_family Optional. The active font family to add the selected attribute to the appropriate opt.
	 *                                         False to not mark any opt as selected.
	 * @return string
	 */
	public static function get_font_family_option_markup( $font_family, $active_font_family = false ) {
		$html = '';

		// Bail if we don't have a font family value.
		if ( empty( $font_family ) ) {
			return apply_filters( 'customify_filter_font_option_markup_no_family', $html, $active_font_family );
		}

		$font_type = Customify_Fonts_Global::instance()->determineFontType( $font_family );
		$font_details = Customify_Fonts_Global::instance()->getFontDetails( $font_family, $font_type );

		// Now determine if we have a "pretty" display for this font family.
		$font_family_display = $font_family;
		if ( is_array( $font_details ) && ! empty( $font_details['family_display'] ) ) {
			$font_family_display = $font_details['family_display'];
		}

		// Determine if the font is selected.
		$selected = ( false !== $active_font_family && $active_font_family === $font_family ) ? ' selected="selected" ' : '';

		// Determine the option class.
		$option_class = ( false !== strpos( $font_type, '_font' ) ) ? $font_type : $font_type . '_font';

		$html .= '<option class="' . esc_attr( $option_class ) . '" value="' . esc_attr( $font_family ) . '" ' . $selected . '>' . $font_family_display . '</option>';

		return apply_filters( 'customify_filter_font_option_markup', $html, $font_family, $active_font_family, $font_type );
	}

	/** ==== Helpers ==== */

	protected function get_default_values() {

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

	/** ==== LEGACY ==== */

	/**
	 * Legacy: This method displays an <option> tag from the given params
	 *
	 * @deprecated Use Pix_Customize_Font_Control::output_font_family_option() instead.
	 *
	 * @param string|array $font
	 * @param string|false $active_font_family Optional. The active font family to add the selected attribute to the appropriate opt.
	 *                                         False to not mark any opt as selected.
	 * @param array $font_settings
	 * @param string $type Optional.
	 */
	public static function output_font_option( $font, $active_font_family = false, $font_settings = array(), $type = 'google_font' ) {
		$font_family = $font;
		if ( is_array( $font_family ) ) {
			if ( ! empty( $font_family['family'] ) ) {
				$font_family = $font_family['family'];
			} else {
				return;
			}
		}
		echo self::get_font_family_option_markup( $font_family, $active_font_family );
	}
}
