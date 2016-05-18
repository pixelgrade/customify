<?php

/**
 * Class Pix_Customize_Typography_Control
 * A complex Typography Control
 */
class Pix_Customize_Typography_Control extends Pix_Customize_Control {
	public $type = 'typography';
	public $backup = null;
	public $font_weight = true;
	public $subsets = true;
	public $load_all_weights = false;
	public $recommended = array();
	public $typekit_fonts = array();
	public $current_value;
	public $default;

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

		$this->load_google_fonts();

		$this->typekit_fonts = apply_filters('customify_filter_typekit_fonts_list', get_option( 'typekit_fonts' ) );


		$this->current_value = $this->value();
//		$this->generate_google_fonts_json();
	}

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() {

		$current_value = $this->value();
		if ( empty( $current_value ) || ( is_array( $current_value ) && ! isset( $current_value['font_family'] ) ) ) {
			$current_value = $this->get_default_values();
		}
		// if this value was an array, well it was wrong
		if ( is_array( $current_value ) ) {
			if ( isset( $current_value['font-family'] ) ) {
				$current_value['font_family'] = $current_value['font-family'];
				unset( $current_value['font-family'] );
			}
			$current_value = json_encode( $current_value );
		}
		$values = json_decode( $current_value );

		$font_family = '';
		if ( isset( $values->font_family ) ) {
			$font_family = $values->font_family;
		}

		if ( isset( $values->load_all_weights ) ) {
			$this->load_all_weights = $values->font_load_all_weights;
		} ?>
		<label class="customify_typography">
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; 
			
			$this_id     = str_replace( '[', '_', $this->id );
			$this_id     = str_replace( ']', '_', $this_id );
			$select_data = '';
			if ( $this->load_all_weights ) {
				$select_data .= ' data-load_all_weights="true"';
			}

			/**
			 * This input will hold the values of this typography field
			 */ ?>
			<input class="customify_typography_values" id="<?php echo $this_id; ?>" type="hidden" <?php $this->link(); ?> value='<?php echo $current_value; ?>' data-default='<?php echo $current_value; ?>'/>
			<select class="customify_typography_font_family"<?php echo $select_data; ?>>
				<?php

				if ( ! empty( $this->typekit_fonts ) ) {
					echo '<optgroup label="' . __( 'Typekit', 'customify_txtd' ) . '">';
					foreach ( $this->typekit_fonts as $key => $font ) {
						self::output_font_option( $font['css_names'][0], $font_family, $font, 'typekit' );
					}
					echo "</optgroup>";
				}

				if ( ! empty( $this->recommended ) ) {

					echo '<optgroup label="' . __( 'Recommended', 'customify_txtd' ) . '">';

					foreach ( $this->recommended as $key => $font ) {
						$font_type = 'std';
						if ( isset( self::$google_fonts[ $key ] ) ) {
							$font = self::$google_fonts[ $key ];
							$font_type = 'google';
						} elseif( isset( $this->typekit_fonts[ $key ] ) ) {
							$font_type = 'typekit';
							$font = $key;
						} else {
							$font = $key;
						}

						self::output_font_option( $key, $font_family, $font, $font_type );
					}
					echo "</optgroup>";
				}

				if ( PixCustomifyPlugin::get_plugin_option( 'typography_standard_fonts' ) ) {

					echo '<optgroup label="' . __( 'Standard fonts', 'customify_txtd' ) . '">';
					foreach ( self::$std_fonts as $key => $font ) {
						self::output_font_option( $key, $font_family, $font, 'std' );
					}
					echo "</optgroup>";
				}

				if ( PixCustomifyPlugin::get_plugin_option( 'typography_google_fonts' ) ) {

					if ( PixCustomifyPlugin::get_plugin_option( 'typography_group_google_fonts' ) ) {

						$grouped_google_fonts = array();
						foreach ( self::$google_fonts as $key => $font ) {
							if ( isset( $font['category'] ) ) {
								$grouped_google_fonts[ $font['category'] ][] = $font;
							}
						}

						foreach ( $grouped_google_fonts as $group_name => $group ) {
							echo '<optgroup label="' . __( 'Google fonts', 'customify_txtd' ) . ' ' . $group_name . '">';
							foreach ( $group as $key => $font ) {
								self::output_font_option( $key, $font_family, $font );
							}
							echo "</optgroup>";
						}

					} else {
						echo '<optgroup label="' . __( 'Google fonts', 'customify_txtd' ) . '">';
						foreach ( self::$google_fonts as $key => $font ) {
							self::output_font_option( $key, $font_family, $font );
						}
						echo "</optgroup>";
					}
				} ?>
			</select>
		</label>
		<ul class="options">
			<?php
			$display = 'none';

			if ( ! $this->load_all_weights && $this->font_weight ) {
				$display = 'inline-block';
			} ?>
			<li class="customify_weights_wrapper" style="display: <?php echo $display; ?>">
				<select class="customify_typography_font_weight">
					<?php
					$selected = array();
					if ( isset( $values->selected_variants ) ) {
						$selected = $values->selected_variants;
					}

					if ( isset( $values->variants ) && ! empty( $values->variants ) && is_array( $values->variants ) ) {
						foreach ( $values->variants as $weight ) {
							$attrs = '';
							if ( in_array( $weight, (array) $selected ) ) {
								$attrs = ' selected="selected"';
							}

							echo '<option value="' . $weight . '" ' . $attrs . '> ' . $weight . '</option>';
						}
					} ?>
				</select>
			</li>
			<?php
			$display = 'none';
			if ( $this->subsets && ! empty( $values->subsets ) ) {
				$display = 'inline-block';
			}?>
			<li class="customify_subsets_wrapper" style="display: <?php echo $display; ?>">
				<select multiple class="customify_typography_font_subsets">
					<?php
					$selected = array();
					if ( isset( $values->selected_subsets ) ) {
						$selected = $values->selected_subsets;
					}

					if ( isset( $values->subsets ) && ! empty( $values->subsets ) && is_array( $values->variants ) ) {
						foreach ( $values->subsets as $key => $subset ) {
							$attrs = '';
							if ( in_array( $subset, (array) $selected ) ) {
								$attrs .= ' selected="selected"';
							}

							echo '<option value="' . $subset . '"' . $attrs . '> ' . $subset . '</option>';
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
	 * This method makes an <option> tag from the given params
	 *
	 * @param $key
	 * @param $font_family
	 * @param $font
	 * @param string $type
	 */
	protected static function output_font_option( $key, $font_family, $font, $type = 'google' ) {
		$data = '';

		$data .= ' data-type="' . $type . '"';

		if ( $type === 'google' ) {

			if ( isset( $font['variants'] ) && ! empty( $font['variants'] ) ) {
				$data .= ' data-variants=\'' . json_encode( (object) $font['variants'] ) . '\'';
			}

			if ( isset( $font['subsets'] ) && ! empty( $font['subsets'] ) ) {
				$data .= ' data-subsets=\'' . json_encode( (object) $font['subsets'] ) . '\'';
			}

			$selected = ( $font_family === $font['family'] ) ? ' selected="selected" ' : '';
			echo '<option value="' . $font['family'] . '"' . $selected . $data . '>' . $font['family'] . '</option>';
		} elseif ( $type === 'typekit' ) {

			$selected = ( $font_family === $key ) ? ' selected="selected" ' : '';
			echo '<option class="typekit_font" value="' . $key . '"' . $selected . $data . '>' . $font['name'] . '</option>';
		} else {

			if ( is_array($font) && isset( $font['variants'] ) && ! empty( $font['variants'] ) ) {
				$data .= ' data-variants=\'' . json_encode( (object) $font['variants'] ) . '\'';
			}

			if ( is_array( $font ) && isset( $font['font_family'] ) ) {
				$selected = ( $font_family === $font['font_family'] ) ? ' selected="selected" ' : '';
				$font = $font['font_family'];
			} else {
				$selected = ( $font_family === $font ) ? ' selected="selected" ' : '';
			}
			echo '<option class="std_font" value="' . $font . '"' . $selected . $data . '>' . $font . '</option>';
		}
	}

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

	function get_default_values( ) {

		$to_return = array();
		if (isset( $this->default ) && is_array( $this->default ) ) {

			//Handle special logic for when the $value array is not an associative array
			if ( ! PixCustomifyPlugin::is_assoc( $this->default ) ) {

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
					$to_return['font_family'] = $this->default['font_family'];
				}

				if ( isset( $this->default['selected_variants'] ) ) {
					$to_return['selected_variants'] = $this->default['selected_variants'];
				}
			}
		}

		// rare case when there is a standard font we need to get the custom variants if there are some
		if ( ! isset( $to_return['variants'] ) && isset( $to_return['font_family'] ) && isset( self::$std_fonts[ $to_return['font_family'] ] ) && isset( self::$std_fonts[ $to_return['font_family'] ]['variants'] ) )  {
			$to_return['variants'] = self::$std_fonts[ $to_return['font_family'] ]['variants'];
		}

		return $to_return;
	}
}
