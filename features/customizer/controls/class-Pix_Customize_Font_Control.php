<?php

/**
 * Class Pix_Customize_Font_Control
 * A complex Typography Control
 */
class Pix_Customize_Font_Control extends Pix_Customize_Control {
	public $type = 'font';
	public $backup = null;
	public $font_weight = true;
	public $subsets = true;
	public $load_all_weights = false;
	public $recommended = array();
	public $current_value;
	public $default;
	public $fields;

	private $CSSID;

	protected static $google_fonts = null;

	private static $std_fonts = null;

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

		self::$std_fonts = apply_filters( 'customify_filter_standard_fonts_list', array(
			"Arial, Helvetica, sans-serif"                         => "Arial, Helvetica, sans-serif",
			"'Arial Black', Gadget, sans-serif"                    => "'Arial Black', Gadget, sans-serif",
			"'Bookman Old Style', serif"                           => "'Bookman Old Style', serif",
			"'Comic Sans MS', cursive"                             => "'Comic Sans MS', cursive",
			"Courier, monospace"                                   => "Courier, monospace",
			"Garamond, serif"                                      => "Garamond, serif",
			"Georgia, serif"                                       => "Georgia, serif",
			"Impact, Charcoal, sans-serif"                         => "Impact, Charcoal, sans-serif",
			"'Lucida Console', Monaco, monospace"                  => "'Lucida Console', Monaco, monospace",
			"'Lucida Sans Unicode', 'Lucida Grande', sans-serif"   => "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
			"'MS Sans Serif', Geneva, sans-serif"                  => "'MS Sans Serif', Geneva, sans-serif",
			"'MS Serif', 'New York', sans-serif"                   => "'MS Serif', 'New York', sans-serif",
			"'Palatino Linotype', 'Book Antiqua', Palatino, serif" => "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
			"Tahoma,Geneva, sans-serif"                            => "Tahoma, Geneva, sans-serif",
			"'Times New Roman', Times,serif"                       => "'Times New Roman', Times, serif",
			"'Trebuchet MS', Helvetica, sans-serif"                => "'Trebuchet MS', Helvetica, sans-serif",
			"Verdana, Geneva, sans-serif"                          => "Verdana, Geneva, sans-serif",
		) );

		$keys = array_keys( get_object_vars( $this ) );
		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		$this->manager = $manager;
		$this->id      = $id;
		if ( empty( $this->active_callback ) ) {
			$this->active_callback = array( $this, 'active_callback' );
		}
		self::$instance_count += 1;
		$this->instance_number = self::$instance_count;

		// Process settings.
		if ( empty( $this->settings ) ) {
			$this->settings = $id;
		}

		$settings = array();
		if ( is_array( $this->settings ) ) {
			foreach ( $this->settings as $key => $setting ) {
				$settings[ $key ] = $this->manager->get_setting( $setting );
			}
		} else {
			$this->setting       = $this->manager->get_setting( $this->settings );
			$settings['default'] = $this->setting;
		}

		$this->settings = $settings;
		$this->CSSID    = $this->get_CSS_ID();
		$this->load_google_fonts();
		//$this->generate_google_fonts_json();

		// since 4.7 all the customizer data is saved in a post type named changeset this is how we get it.
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
	 *
	 * @since 3.4.0
	 */
	public function render_content() {

		$current_value = $this->current_value;

		//maybe we need to decode it
		$current_value = PixCustomifyPlugin::decodeURIComponent( $current_value );

		if ( empty( $current_value ) ) {
			$current_value = $this->get_default_values();
		}

		// if this value was an array, make sure it is ok
		if ( is_array( $current_value ) ) {
			if ( isset( $current_value['font-family'] ) ) {
				$current_value['font_family'] = $current_value['font-family'];
				unset( $current_value['font-family'] );
			}
		} else {
			//if we've got a string then it is clear we need to decode it
			$current_value = json_decode( $current_value );
		}

		$current_value = $this->validate_font_values( $current_value );

		//make sure it is an object from here going forward
		$current_value = (object) $current_value;

		$font_family = '';
		if ( isset( $current_value->font_family ) ) {
			$font_family = $current_value->font_family;
		}

		$select_data = '';
		if ( isset( $current_value->load_all_weights ) ) {
			$this->load_all_weights = $current_value->font_load_all_weights;

			$select_data .= ' data-load_all_weights="true"';
		} ?>
		<div class="font-options__wrapper">
			<?php
			$this->display_value_holder( $current_value );
			$this->display_field_title( $font_family, esc_attr( $this->CSSID ) ); ?>

			<input type="checkbox" class="customify_font_tooltip"
			       id="tooltip_toogle_<?php echo esc_attr( $this->CSSID ); ?>">

			<ul class="font-options__options-list">
				<li class="font-options__option customize-control">
					<select id="select_font_font_family_<?php echo esc_attr( $this->CSSID ); ?>" class="customify_font_family"<?php echo $select_data; ?> data-field="font_family">
						<?php
						// Allow others to add options here
						do_action( 'customify_font_family_before_options', $font_family, $current_value );

						$this->display_recommended_options_group( $font_family, $current_value );

						$this->display_standard_options_group( $font_family, $current_value );

						do_action( 'customify_font_family_before_google_fonts_options' );

						if ( PixCustomifyPlugin()->get_plugin_setting( 'typography_google_fonts' ) ) {

							if ( PixCustomifyPlugin()->get_plugin_setting( 'typography_group_google_fonts' ) ) {

								$grouped_google_fonts = array();
								foreach ( self::$google_fonts as $key => $font ) {
									if ( isset( $font['category'] ) ) {
										$grouped_google_fonts[ $font['category'] ][] = $font;
									}
								}

								foreach ( $grouped_google_fonts as $group_name => $group ) {
									echo '<optgroup label="' . __( 'Google fonts', 'customify' ) . ' ' . $group_name . '">';
									foreach ( $group as $key => $font ) {
										self::output_font_option( $key, $font_family, $font );
									}
									echo "</optgroup>";
								}

							} else {
								echo '<optgroup label="' . __( 'Google fonts', 'customify' ) . '">';
								foreach ( self::$google_fonts as $key => $font ) {
									self::output_font_option( $key, $font_family, $font );
								}
								echo "</optgroup>";
							}
						} ?>
					</select>
				</li>
				<?php
				$this->display_font_weight_field( $current_value );

				$this->display_font_subset_field( $current_value );

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
	 * This input will hold the values of this typography field
	 */
	function display_value_holder( $current_value ) { ?>
		<input class="customify_font_values" id="<?php echo esc_attr( $this->CSSID ); ?>"
		       type="hidden" <?php $this->link(); ?>
		       value="<?php echo esc_attr( PixCustomifyPlugin::encodeURIComponent( json_encode( $current_value ) ) ); ?>"
		       data-default="<?php echo esc_attr( PixCustomifyPlugin::encodeURIComponent( json_encode( $current_value ) ) ); ?>"/>
	<?php }

	function display_field_title( $font_family, $font_name_id ) { ?>
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

			echo '<optgroup label="' . __( 'Recommended', 'customify' ) . '">';

			foreach ( $this->recommended as $key => $font ) {
				$font_type = 'std';
				if ( isset( self::$google_fonts[ $key ] ) ) {
					$font      = self::$google_fonts[ $key ];
					$font_type = 'google';
				} else {
					$font = $key;
				}

				self::output_font_option( $key, $font_family, $font, $font_type );
			}
			echo "</optgroup>";
		}
	}

	function display_standard_options_group( $font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_standard_fonts_options', $font_family, $current_value );

		if ( PixCustomifyPlugin()->get_plugin_setting( 'typography_standard_fonts' ) ) {

			echo '<optgroup label="' . __( 'Standard fonts', 'customify' ) . '">';
			foreach ( self::$std_fonts as $key => $font ) {
				self::output_font_option( $key, $font_family, $font, 'std' );
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_standard_fonts_options' );
	}

	function display_font_weight_field( $current_value ) {

		$display = 'none';

		if ( ! $this->load_all_weights && $this->font_weight ) {
			$display = 'inline-block';
		} ?>
		<li class="customify_weights_wrapper customize-control font-options__option"
		    style="display: <?php echo $display; ?>">
			<select class="customify_font_weight" data-field="selected_variants" <?php echo ! empty( $current_value->selected_variants ) ? 'data-default="' . $current_value->selected_variants . '"' : null; ?>>
				<?php
				$selected = array();
				if ( isset( $current_value->selected_variants ) ) {
					$selected = $current_value->selected_variants;
				}

				if ( isset( $current_value->variants ) && ! empty( $current_value->variants ) && is_object( $current_value->variants ) ) {
					foreach ( $current_value->variants as $weight ) {
						$attrs = '';
						if ( in_array( $weight, (array) $selected ) ) {
							$attrs = ' selected="selected"';
						}

						echo '<option value="' . $weight . '" ' . $attrs . '> ' . $weight . '</option>';
					}
				} else if ( ! empty( $current_value->variants ) && is_string( $current_value->variants ) ) {
					echo '<option value="' . $current_value->variants . '" selected="selected"> ' . $current_value->variants . '</option>';
				} ?>
			</select>
		</li>
		<?php
	}

	function display_font_subset_field( $current_value ) {
		$display = 'none';
		if ( $this->subsets && ! empty( $current_value->subsets ) ) {
			$display = 'inline-block';
		} ?>
		<li class="customify_subsets_wrapper customize-control font-options__option"
		    style="display: <?php echo $display; ?>">
			<select multiple class="customify_font_subsets" data-field="selected_subsets">
				<?php
				$selected = array();
				if ( isset( $current_value->selected_subsets ) ) {
					$selected = $current_value->selected_subsets;
				}

				if ( isset( $current_value->subsets ) && ! empty( $current_value->subsets ) && is_object( $current_value->variants ) ) {
					foreach ( $current_value->subsets as $key => $subset ) {

						if ( $subset === 'latin' ) {
							continue;
						}

						$attrs = '';
						if ( in_array( $subset, (array) $selected ) ) {
							$attrs .= ' selected="selected"';
						}

						echo '<option value="' . $subset . '"' . $attrs . '> ' . $subset . '</option>';
					}
				} ?>
			</select>
		</li>

		<?php
	}

	function display_font_size_field( $current_value ) {
		if ( ! empty( $this->fields['font-size'] ) ) {
			$fs_val = empty( $current_value->font_size ) ? 0 : $current_value->font_size; ?>
			<li class="customify_font_size_wrapper customize-control font-options__option">
				<label><?php esc_html_e( 'Font Size', 'customify' ); ?></label>
				<input type="range"
				       data-field="font_size" <?php $this->input_field_atts( $this->fields['font-size'] ) ?>
				       value="<?php echo $fs_val; ?>">
			</li>
		<?php }
	}

	function display_line_height_field( $current_value ) {
		if ( ! empty( $this->fields['line-height'] ) ) {
			$lh_val = isset( $current_value->line_height ) ? $current_value->line_height : 0 ; ?>
			<li class="customify_line_height_wrapper customize-control font-options__option">
				<label><?php esc_html_e( 'Line height', 'customify' ); ?></label>
				<input type="range"
				       data-field="line_height" <?php $this->input_field_atts( $this->fields['line-height'] ) ?>
				       value="<?php echo $lh_val ?>">
			</li>
		<?php }
	}

	function display_letter_spacing_field( $current_value ) {

		if ( ! empty( $this->fields['letter-spacing'] ) ) {
			$ls_val = isset( $current_value->letter_spacing ) ? $current_value->letter_spacing : 0; ?>
			<li class="customify_letter_spacing_wrapper customize-control font-options__option">
				<label><?php esc_html_e( 'Letter Spacing', 'customify' ); ?></label>
				<input type="range"
				       data-field="letter_spacing" <?php $this->input_field_atts( $this->fields['letter-spacing'] ) ?>
				       value="<?php echo $ls_val ?>">
			</li>
		<?php }
	}

	function display_text_align_field( $current_value ) {
		if ( ! empty( $this->fields['text-align'] ) && $this->fields['text-align'] ) {
			$ta_val = isset( $current_value->text_align ) ? $current_value->text_align : 'initial'; ?>
			<li class="customify_text_align_wrapper customize-control font-options__option">
				<label><?php esc_html_e( 'Text Align', 'customify' ); ?></label>
				<select data-field="text_align">
					<option <?php $this->display_option_value( 'initial', $ta_val ); ?>><?php esc_html_e( 'Initial', 'customify' ); ?></option>
					<option  <?php $this->display_option_value( 'center', $ta_val ); ?>><?php esc_html_e( 'Center', 'customify' ); ?></option>
					<option <?php $this->display_option_value( 'left', $ta_val ); ?>><?php esc_html_e( 'Left', 'customify' ); ?></option>
					<option <?php $this->display_option_value( 'right', $ta_val ); ?>><?php esc_html_e( 'Right', 'customify' ); ?></option>
				</select>
			</li>
		<?php }
	}

	function display_text_transform_field( $current_value ) {
		if ( ! empty( $this->fields['text-transform'] ) && $this->fields['text-transform'] ) {
			$tt_val = isset( $current_value->text_transform ) ? $current_value->text_transform : 'none'; ?>
			<li class="customify_text_transform_wrapper customize-control font-options__option">
				<label><?php esc_html_e( 'Text Transform', 'customify' ); ?></label>
				<select data-field="text_transform">
					<option <?php $this->display_option_value( 'none', $tt_val ); ?>><?php esc_html_e( 'None', 'customify' ); ?></option>
					<option <?php $this->display_option_value( 'capitalize', $tt_val ); ?>><?php esc_html_e( 'Capitalize', 'customify' ); ?></option>
					<option <?php $this->display_option_value( 'uppercase', $tt_val ); ?>><?php esc_html_e( 'Uppercase', 'customify' ); ?></option>
					<option <?php $this->display_option_value( 'lowercase', $tt_val ); ?>><?php esc_html_e( 'Lowercase', 'customify' ); ?></option>
				</select>
			</li>
		<?php }
	}

	function display_text_decoration_field( $current_value ) {
		if ( ! empty( $this->fields['text-decoration'] ) && $this->fields['text-decoration'] ) {
			$td_val = isset( $current_value->text_decoration ) ? $current_value->text_decoration : 'none';?>
			<li class="customify_text_decoration_wrapper customize-control font-options__option">
				<label><?php esc_html_e( 'Text Decoration', 'customify' ); ?></label>
				<select data-field="text_decoration">
					<option <?php $this->display_option_value( 'none', $td_val ); ?>><?php esc_html_e( 'None', 'customify' ); ?></option>
					<option <?php $this->display_option_value( 'underline', $td_val ); ?>><?php esc_html_e( 'Underline', 'customify' ); ?></option>
					<option <?php $this->display_option_value( 'overline', $td_val ); ?>><?php esc_html_e( 'Overline', 'customify' ); ?></option>
					<option <?php $this->display_option_value( 'line-through', $td_val ); ?>><?php esc_html_e( 'Line Through', 'customify' ); ?></option>
				</select>
			</li>
		<?php }
	}

	function display_option_value( $value, $current_value ) {

		$return = 'value="' . $value . '"';

		if ( $value === $current_value ) {
			$return .= ' selected="selected"';
		}

		echo $return;
	}

	/**
	 * This method displays an <option> tag from the given params
	 *
	 * @param string $key
	 * @param string $active_font_family
	 * @param string|array $font
	 * @param string string $type
	 */
	public static function output_font_option( $key, $active_font_family, $font, $type = 'google' ) {
		//initialize data attributes
		$data = '';

		$data .= ' data-type="' . esc_attr( $type ) . '"';

		//we will handle Google Fonts separately
		if ( $type === 'google' ) {
			// Handle the font variants markup, if available
			if ( isset( $font['variants'] ) && ! empty( $font['variants'] ) ) {
				$data .= ' data-variants="' . PixCustomifyPlugin::encodeURIComponent( json_encode( (object) $font['variants'] ) ) . '"';
			}

			if ( isset( $font['subsets'] ) && ! empty( $font['subsets'] ) ) {
				$data .= ' data-subsets="' . PixCustomifyPlugin::encodeURIComponent( json_encode( (object) $font['subsets'] ) ) . '"';
			}

			//determine if it's selected
			$selected = ( $active_font_family === $font['family'] ) ? ' selected="selected" ' : '';

			//output the markup
			echo '<option value="' . $font['family'] . '"' . $selected . $data . '>' . $font['family'] . '</option>';
		} elseif ( $type === 'theme_font' ) {
			$data = '';

			// Handle the font variants markup, if available
			if ( isset( $font['variants'] ) && ! empty( $font['variants'] ) ) {
				$data .= ' data-variants="' . PixCustomifyPlugin::encodeURIComponent( json_encode( (object) $font['variants'] ) ) . '"';
			}

			$selected = ( $active_font_family === $font['family'] ) ? ' selected="selected" ' : '';
			$data .= ' data-src="' . $font['src'] . '" data-type="theme_font"';
			//output the markup
			echo '<option value="' . $font['family'] . '"' . $selected . $data . '>' . $font['family'] . '</option>';
		} else {
			// Handle the font variants markup, if available
			if ( is_array( $font ) && isset( $font['variants'] ) && ! empty( $font['variants'] ) ) {
				$data .= ' data-variants="' . PixCustomifyPlugin::encodeURIComponent( json_encode( (object) $font['variants'] ) ) . '"';
			}

			// by default, we assume we only get a font family string
			$font_family = $font;
			// when we get an array we expect to get a font_family entry
			if ( is_array( $font ) && isset( $font['font_family'] ) ) {
				$font_family = $font['font_family'];
			}

			//determine if it's selected
			$selected = ( $active_font_family === $font_family ) ? ' selected="selected" ' : '';

			//now determine if we have a "pretty" display for this font family
			$font_family_display = $font_family;
			if ( is_array( $font ) && isset( $font['font_family_display'] ) ) {
				$font_family_display = $font['font_family_display'];
			}

			//determine the option class
			if ( empty( $type ) ) {
				$type = 'std';
			}
			$option_class = $type . '_font';

			//output the markup
			echo '<option class="' . esc_attr( $option_class ) . '" value="' . esc_attr( $font_family ) . '" ' . $selected . $data . '>' . $font_family_display . '</option>';
		}
	}

	/** ==== Helpers ==== */

	/**
	 * Load the google fonts list from the local file
	 * @return bool|mixed|null
	 */
	protected function load_google_fonts() {

		$fonts_path = plugin_dir_path( __FILE__ ) . 'resources/google.fonts.php';

		if ( file_exists( $fonts_path ) ) {
			self::$google_fonts = require( $fonts_path );
		}

		if ( ! empty( self::$google_fonts ) ) {
			return apply_filters( 'customify_filter_google_fonts_list', self::$google_fonts );
		}

		return false;
	}

	/**
	 * This method is used only to update the google fonts json file
	 * Even if it sounds weird you need to go to https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyB7Yj842mK5ogSiDa3eRrZUIPTzgiGopls
	 * copy all the content and put it into google.fonts.php
	 * call this
	 * the result needs to be put back into google.fonts.php
	 * remove this call
	 * before rage about file_get_content + json file think about the security avoided with this simple call
	 */
	protected function generate_google_fonts_json() {

		$fonts_path = plugin_dir_path( __FILE__ ) . 'resources/google.fonts.php';

		$new_array = array();
		foreach ( self::$google_fonts as $key => $font ) {
			// unset unused data
			unset( $font['kind'] );
			unset( $font['version'] );
			unset( $font['lastModified'] );
			unset( $font['files'] );
			$new_array[ $font['family'] ] = $font;
		}

		file_put_contents( plugin_dir_path( __FILE__ ) . 'resources/google.fonts.json', json_encode( $new_array ) );
	}

	function get_default_values() {

		$to_return = array();

		if ( isset( $this->default ) && is_array( $this->default ) ) {

			//Handle special logic for when the $value array is not an associative array
			if ( ! PixCustomifyPlugin()->is_assoc( $this->default ) ) {

				//Let's determine some type of font
				if ( ! isset( $this->default[2] ) || ( isset( $this->default[2] ) && 'google' == $this->default[2] ) ) {
					if ( isset( self::$google_fonts[ $this->default[0] ] ) ) {
						$to_return                = self::$google_fonts[ $this->default[0] ];
						$to_return['font_family'] = $this->default[0];
						$to_return['type']        = 'google';
					}
				} else {
					$to_return['type'] = $this->default[2];
				}

				//The first entry is the font-family
				if ( isset( $this->default[0] ) ) {
					$to_return['font_family'] = $this->default[0];
				}

				//In case we don't have an associative array
				//The second entry is the variants
				if ( isset( $this->default[1] ) ) {
					$to_return['selected_variants'] = $this->default[1];
				}
			} else {

				if ( isset( $this->default['font_family'] ) ) {
					$to_return['font-family'] = $this->default['font_family'];
				}

				if ( isset( $this->default['font-family'] ) ) {
					$to_return['font-family'] = $this->default['font-family'];
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

		// rare case when there is a standard font we need to get the custom variants if there are some
		if ( ! isset( $to_return['variants'] ) && isset( $to_return['font_family'] ) && isset( self::$std_fonts[ $to_return['font_family'] ] ) && isset( self::$std_fonts[ $to_return['font_family'] ]['variants'] ) ) {
			$to_return['variants'] = self::$std_fonts[ $to_return['font_family'] ]['variants'];
		}

		return $to_return;
	}

	function validate_font_values( $values ) {

		if ( empty( $values ) ) {
			return array();
		}

		foreach ( $values as $key => $value ) {

			if ( strpos( $key, '-' ) !== false ) {
				$new_key = str_replace( '-', '_', $key );

				if ( $new_key === 'font_weight' ) {
					$values[ 'selected_variants' ] = $value;
					unset( $values[ 'font_weight' ] );
				} else {
					$values[ $new_key ] = $value;
					unset( $values[ $key ] );
				}
			}
		}

		return $values;
	}

	private function get_CSS_ID( $id = null ) {

		if ( empty( $id ) ) {
			$id = $this->id;
		}

		$id = str_replace( '[', '_', $id );
		$id = str_replace( ']', '_', $id );

		return $id;
	}

	/**
	 * Render the custom attributes for the control's input element.
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public function input_field_atts( $atts ) {

		if ( ! PixCustomifyPlugin()->is_assoc( $atts ) ) {
			$defaults = array(
				'min',
				'max',
				'step',
				'unit',
			);

			$atts = array_combine( $defaults, array_values( $atts ) );
		}

		foreach ( $atts as $attr => $value ) {
			echo $attr . '="' . esc_attr( $value ) . '" ';
		}
	}
}
