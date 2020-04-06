<?php

/**
 * Class Pix_Customize_Font_Control
 * A complex Typography Control
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
	}

	public function output_recommended_options_group( $active_font_family, $current_value, $field_id ) {
		// We only want each instance to output it's recommended options group.
		if ( $this->id !== $field_id ) {
			return;
		}

		$this->display_recommended_options_group( $active_font_family, $current_value );
	}

	/**
	 * Render the control's content.
	 */
	public function render_content() {

		$current_value = $this->current_value;

		//maybe we need to decode it
		$current_value = PixCustomifyPlugin::decodeURIComponent( $current_value );

		if ( empty( $current_value ) ) {
			$current_value = $this->get_default_values();
		}

		// if this value was an array, make sure it is ok
		if ( is_string( $current_value ) ) {
			//if we've got a string then it is clear we need to decode it
			$current_value = json_decode( $current_value, true );
		}

		$current_value = Customify_Fonts_Global::standardize_font_values( $current_value );

		//make sure it is an object from here going forward
		$current_value = (object) $current_value;

		$active_font_family = '';
		if ( isset( $current_value->font_family ) ) {
			$active_font_family = $current_value->font_family;
		}

		$select_data = 'data-active_font_family="' . esc_attr( $active_font_family ) . '"'; ?>
		<div class="font-options__wrapper">

			<input type="checkbox" class="font-options__checkbox js-font-option-toggle" id="tooltip_toogle_<?php echo esc_attr( $this->CSSID ); ?>">

			<?php
			$this->display_value_holder( $current_value );
			$this->display_field_title( $active_font_family, esc_attr( $this->CSSID ) ); ?>

			<ul class="font-options__options-list">
				<li class="font-options__option customize-control">
					<select id="select_font_font_family_<?php echo esc_attr( $this->CSSID ); ?>" class="customify_font_family"<?php echo $select_data; ?> data-field="font_family">

						<?php
						// Allow others to add options here. This is mostly for backwards compatibility purposes.
						do_action( 'customify_font_family_before_options', $active_font_family, $current_value, $this->id );

						do_action( 'customify_font_family_select_options', $active_font_family, $current_value, $this->id );

						// Allow others to add options here. This is mostly for backwards compatibility purposes.
						do_action( 'customify_font_family_after_options', $active_font_family, $current_value, $this->id ); ?>

					</select>
				</li>
				<?php
				$this->display_font_weight_field( $current_value );

				$this->display_font_subsets_field( $current_value );

				$this->display_font_size_field( $current_value );

				$this->display_line_height_field( $current_value );

				$this->display_letter_spacing_field( $current_value );

				$this->display_text_align_field( $current_value );

				$this->display_text_transform_field( $current_value );

				$this->display_text_decoration_field( $current_value ); ?>
			</ul>
		</div>
		<script>
			// Update the font name in the font field label
			jQuery( '#select_font_font_family_<?php echo esc_attr( $this->CSSID ); ?>' ).change( function(){
				var newValue = jQuery( '#select_font_font_family_<?php echo esc_attr( $this->CSSID ); ?>' ).val();
				jQuery( '#font_name_<?php echo esc_attr( $this->CSSID ); ?>' ).html( newValue );
			});
		</script>

		<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php endif;

		?>
	<?php }

	/**
	 * This input will hold the values of this font field
	 */
	protected function display_value_holder( $current_value ) { ?>
		<input class="customify_font_values" id="<?php echo esc_attr( $this->CSSID ); ?>"
		       type="hidden" <?php $this->link(); ?>
		       value="<?php echo esc_attr( PixCustomifyPlugin::encodeURIComponent( json_encode( $current_value ) ) ); ?>"
		       data-default="<?php echo esc_attr( PixCustomifyPlugin::encodeURIComponent( json_encode( $current_value ) ) ); ?>"/>
	<?php }

	protected function display_field_title( $font_family, $font_name_id ) { ?>
		<label class="font-options__head  select" for="tooltip_toogle_<?php echo esc_attr( $this->CSSID ); ?>">
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="font-options__option-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<span class="font-options__font-title" id="font_name_<?php echo $font_name_id; ?>"><?php echo $font_family; ?></span>
		</label>
	<?php }

	function display_recommended_options_group( $font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_recommended_fonts_options', $font_family, $current_value );

		if ( ! empty( $this->recommended ) ) {

			echo '<optgroup label="' . esc_attr__( 'Recommended', 'customify' ) . '">';

			$google_fonts = Customify_Fonts_Global::instance()->get_google_fonts();
			foreach ( $this->recommended as $key => $font ) {
				$font_type = 'std';
				if ( isset( $google_fonts[ $key ] ) ) {
					$font      = $google_fonts[ $key ];
					$font_type = 'google';
				} else {
					$font = $key;
				}

				self::output_font_family_option( $font, $font_family, $font_type );
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_recommended_fonts_options', $font_family, $current_value );
	}

	protected function display_font_weight_field( $current_value ) {
		// If the `font-weight` field entry is falsy, this means we don't want to show the font-weight field.
		// @todo Consider if we could simply not output anything.

		// These two are go hand in hand. @todo Maybe simply here.
		$display       = 'none';
		$data_disabled = 'data-disabled';
		if ( ! empty( $this->fields['font-weight'] ) ) {
			$display       = 'inline-block';
			$data_disabled = '';
		}

		// @todo This is very weird! We are only using a single font weight and use that to generate CSS,
		// not just to load font weights/variants via Web Font Loader. This key should actually be font_weight!!!
		// The variants are automatically loaded by Web Font Loader. There is no need to select them.
		$selected = false;
		if ( isset( $current_value->selected_variants ) ) {
			$selected = $current_value->selected_variants;

			if ( is_array( $selected ) ) {
				$selected = reset( $selected );
			}
		}
		?>
		<li class="customify_weights_wrapper customize-control font-options__option"
		    style="display: <?php echo $display; ?>;">
			<label><?php esc_html_e( 'Font Weight', 'customify' ); ?></label>
			<?php
			$data_default = ! empty( $selected ) ? 'data-default="' . $selected . '"' : '';
			?>
			<select class="customify_font_weight"
			        data-field="selected_variants" <?php echo $data_default . ' ' . $data_disabled ?>>
				<?php
				if ( ! empty( $current_value->variants ) ) {
					if ( is_string( $current_value->variants ) ) {
						$current_value->variants = array( $current_value->variants );
					}

					foreach ( $current_value->variants as $weight ) {
						$attrs = '';
						if ( $weight == $selected ) {
							$attrs = ' selected="selected"';
						}

						echo '<option value="' . esc_attr( $weight ) . '" ' . $attrs . '> ' . $weight . '</option>';
					}
				} ?>
			</select>
		</li>
		<?php
	}

	protected function display_font_subsets_field( $current_value ) {
		// If the `subsets` field entry is falsy, this means we don't want to show the subsets field.
		// @todo Consider if we could simply not output anything.

		// These two are go hand in hand. @todo Maybe simply here.
		$display       = 'none';
		$data_disabled = 'data-disabled';
		if ( ! empty( $this->fields['subsets'] ) && ! empty( $current_value->subsets ) ) {
			$display       = 'inline-block';
			$data_disabled = '';
		} ?>
		<li class="customify_subsets_wrapper customize-control font-options__option"
		    style="display: <?php echo $display; ?>;">
			<label><?php esc_html_e( 'Languages', 'customify' ); ?></label>
			<select multiple class="customify_font_subsets" data-field="selected_subsets" <?php echo $data_disabled ?>>
				<?php
				$selected = array();
				if ( isset( $current_value->selected_subsets ) ) {
					$selected = (array) $current_value->selected_subsets;
				}

				if ( ! empty( $current_value->subsets ) ) {
					foreach ( $current_value->subsets as $key => $subset ) {
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
		if ( empty( $this->fields['font-size'] ) ) {
			return;
		}

		$attributes = $this->standardize_range_attributes( $this->fields['font-size'] );

		$value = empty( $current_value->font_size ) ? 0 : $current_value->font_size;
		// Standardize the value.
		$value = Customify_Fonts_Global::standardize_numerical_value( $value, 'font-size', array( 'fields' => $this->fields ) );

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
		if ( empty( $this->fields['line-height'] ) ) {
			return;
		}

		$attributes = $this->standardize_range_attributes( $this->fields['line-height'] );

		$value = empty( $current_value->line_height ) ? 0 : $current_value->line_height;
		// Standardize the value.
		$value = Customify_Fonts_Global::standardize_numerical_value( $value, 'line-height', array( 'fields' => $this->fields ) );

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
		$value = Customify_Fonts_Global::standardize_numerical_value( $value, 'letter-spacing', array( 'fields' => $this->fields ) );

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
	 * @param string|array $font
	 * @param string|false $active_font_family Optional. The active font family to add the selected attribute to the appropriate opt.
	 *                                         False to not mark any opt as selected.
	 * @param string $type Optional.
	 */
	public static function output_font_family_option( $font, $active_font_family = false, $type = 'google' ) {
		echo self::get_font_family_option_markup( $font, $active_font_family, $type );
	}

	/**
	 * This method returns an <option> tag from the given params
	 *
	 * @param string|array $font
	 * @param string|false $active_font_family Optional. The active font family to add the selected attribute to the appropriate opt.
	 *                                         False to not mark any opt as selected.
	 * @param string $type Optional.
	 * @return string
	 */
	public static function get_font_family_option_markup( $font, $active_font_family = false, $type = 'google' ) {

		$html = '';
		$font_family = false;

		if ( empty( $type ) ) {
			$type = 'std';
		}
		$data_attrs = ' data-type="' . esc_attr( $type ) . '"';

		// We will handle Google Fonts separately
		if ( $type === 'google' ) {
			$font_family = $font['family'];

			// Handle the font variants markup, if available
			if ( isset( $font['variants'] ) && ! empty( $font['variants'] ) ) {
				$data_attrs .= ' data-variants="' . esc_attr( PixCustomifyPlugin::encodeURIComponent( json_encode( (object) $font['variants'] ) ) ) . '"';
			}

			if ( isset( $font['subsets'] ) && ! empty( $font['subsets'] ) ) {
				$data_attrs .= ' data-subsets="' . esc_attr( PixCustomifyPlugin::encodeURIComponent( json_encode( (object) $font['subsets'] ) ) ) . '"';
			}


		} elseif ( $type === 'theme_font' || $type === 'cloud_font' ) {
			$font_family = $font['family'];

			// Handle the font variants markup, if available
			if ( isset( $font['variants'] ) && ! empty( $font['variants'] ) ) {
				$data_attrs .= ' data-variants="' . esc_attr( PixCustomifyPlugin::encodeURIComponent( json_encode( (object) $font['variants'] ) ) ) . '"';
			}

			$data_attrs .= ' data-src="' . esc_attr( $font['src'] ) . '"';
		} else {
			// Handle the font variants markup, if available
			if ( is_array( $font ) && ! empty( $font['variants'] ) ) {
				$data_attrs .= ' data-variants="' . esc_attr( PixCustomifyPlugin::encodeURIComponent( json_encode( (object) $font['variants'] ) ) ) . '"';
			}

			// By default, we assume we only get a font family string
			$font_family = $font;
			// when we get an array we expect to get a font_family entry
			if ( is_array( $font ) && isset( $font['font_family'] ) ) {
				$font_family = $font['font_family'];
			}
		}

		// Now determine if we have a "pretty" display for this font family
		$font_family_display = $font_family;
		if ( is_array( $font ) && isset( $font['font_family_display'] ) ) {
			$font_family_display = $font['font_family_display'];
		}

		// Bail if we don't have a font family value.
		if ( empty( $font_family ) ) {
			return apply_filters( 'customify_filter_font_option_markup_no_family', $html, $font, $active_font_family, $type );
		}

		// Determine if the font is selected
		$selected = ( false !== $active_font_family && $active_font_family === $font_family ) ? ' selected="selected" ' : '';

		// Determine the option class
		$option_class = ( false !== strpos( $type, '_font' ) ) ? $type : $type . '_font';

		$html .= '<option class="' . esc_attr( $option_class ) . '" value="' . esc_attr( $font_family ) . '" ' . $selected . $data_attrs . '>' . $font_family_display . '</option>';

		return apply_filters( 'customify_filter_font_option_markup', $html, $font, $active_font_family, $type );
	}

	/** ==== Helpers ==== */

	function get_default_values() {

		$to_return = array();

		if ( isset( $this->default ) && is_array( $this->default ) ) {

			// Handle special logic for when the $value array is not an associative array.
			if ( ! $this->isAssocArray( $this->default ) ) {

				// Let's determine some type of font.
				if ( ! isset( $this->default[2] ) || ( isset( $this->default[2] ) && 'google' == $this->default[2] ) ) {
					$google_fonts = Customify_Fonts_Global::instance()->get_google_fonts();
					if ( isset( $google_fonts[ $this->default[0] ] ) ) {
						$to_return                = $google_fonts[ $this->default[0] ];
						$to_return['font_family'] = $this->default[0];
						$to_return['type']        = 'google';
					}
				} else {
					$to_return['type'] = $this->default[2];
				}

				// The first entry is the font-family.
				if ( isset( $this->default[0] ) ) {
					$to_return['font_family'] = $this->default[0];
				}

				// In case we don't have an associative array.
				// The second entry is the variants.
				if ( isset( $this->default[1] ) ) {
					$to_return['selected_variants'] = $this->default[1];
				}
			} else {

				if ( isset( $this->default['font_family'] ) ) {
					$to_return['font_family'] = $this->default['font_family'];
				} elseif ( isset( $this->default['font-family'] ) ) {
					// Handle the case with dash instead of underscore.
					$to_return['font_family'] = $this->default['font-family'];
				}

				if ( isset( $this->default['selected_variants'] ) ) {
					$to_return['selected_variants'] = $this->default['selected_variants'];
				} elseif ( isset( $this->default['font-weight'] ) ) {
					$to_return['selected_variants'] = $this->default['font-weight'];
				}

				if ( isset( $this->default['font-size'] ) ) {
					$to_return['font-size'] = $this->default['font-size'];
				}

				if ( isset( $this->default['line-height'] ) ) {
					$to_return['line-height'] = $this->default['line-height'];
				}

				if ( isset( $this->default['letter-spacing'] ) ) {
					$to_return['letter-spacing'] = $this->default['letter-spacing'];
				}

				if ( isset( $this->default['text-transform'] ) ) {
					$to_return['text-transform'] = $this->default['text-transform'];
				}

				if ( isset( $this->default['text-align'] ) ) {
					$to_return['text-align'] = $this->default['text-align'];
				}

				if ( isset( $this->default['text-decoration'] ) ) {
					$to_return['text_decoration'] = $this->default['text-decoration'];
				}
			}
		}

		// Rare case when there is a standard font we need to get the custom variants if there are some.
		$std_fonts = Customify_Fonts_Global::instance()->get_std_fonts();
		if ( ! isset( $to_return['variants'] )
		     && isset( $to_return['font_family'] )
		     && isset( $std_fonts[ $to_return['font_family'] ] )
		     && isset( $std_fonts[ $to_return['font_family'] ]['variants'] ) ) {
			$to_return['variants'] = $std_fonts[ $to_return['font_family'] ]['variants'];
		}

		return $to_return;
	}

	protected function get_CSS_ID() {
		$id = $this->id;

		$id = str_replace( '[', '_', $id );
		$id = str_replace( ']', '_', $id );

		return $id;
	}

	protected function isAssocArray( $array ) {
		return ( $array !== array_values( $array ) );
	}

	/** ==== LEGACY ==== */

	/**
	 * Legacy: This method displays an <option> tag from the given params
	 *
	 * @deprecated Use Pix_Customize_Font_Control::output_font_option() instead.
	 *
	 * @param string|array $font
	 * @param string|false $active_font_family Optional. The active font family to add the selected attribute to the appropriate opt.
	 *                                         False to not mark any opt as selected.
	 * @param array $font_settings
	 * @param string $type Optional.
	 */
	public static function output_font_option( $font, $active_font_family = false, $font_settings = array(), $type = 'google' ) {
		echo self::get_font_family_option_markup( $font, $active_font_family, $type );
	}
}
