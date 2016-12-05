<?php

class Customify_Font_Selector extends PixCustomifyPlugin {

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;
	protected $parent = null;
	protected static $typo_settings = null;
	protected static $options_list = null;
	protected static $theme_fonts = null;

	function __construct( $parent ) {
		add_action( 'customize_preview_init', array( $this, 'enqueue_admin_customizer_preview_assets' ), 10 );
		$load_location = PixCustomifyPlugin::get_plugin_option( 'style_resources_location', 'wp_head' );
		add_action( $load_location, array( $this, 'output_font_dynamic_style' ), 999999999 );

		$this->parent      = $parent;
		self::$theme_fonts = apply_filters( 'customify_theme_fonts', array() );

		add_action( 'customify_font_family_before_options', array( $this, 'add_customify_theme_fonts' ), 11, 2 );
	}

	function add_customify_theme_fonts( $active_font_family, $val ) {
		//first get all the published custom fonts
		if ( empty( self::$theme_fonts ) ) {
			return;
		}

		echo '<optgroup label="' . esc_html__( 'Theme Fonts', 'customify' ) . '">';
		foreach ( self::$theme_fonts as $font ) {
			if ( ! empty( $font ) ) {
				//display the select option's HTML
				Pix_Customize_Font_Control::output_font_option( $font['family'], $active_font_family, $font, 'theme_font' );
			}
		}
		echo "</optgroup>";
	}

	function output_font_dynamic_style() {

		self::$options_list = $this->get_options();

		self::get_typography_fields( self::$options_list, 'type', 'font', self::$typo_settings );

		if ( empty( self::$typo_settings ) ) {
			return;
		}

		$google_families = $local_families = '';

		$args = array(
			'google_families' => '',
			'local_families' => '',
			'local_srcs' => '',
		);

		foreach ( self::$typo_settings as $id => $font ) {
			if ( isset ( $font['value'] ) ) {

				$load_all_weights = false;
				if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
					$load_all_weights = true;
				}
				$value = json_decode( wp_unslash( PixCustomifyPlugin::decodeURIComponent( $font['value'] ) ), true );

				$value = $this->validate_font_values( $value );

				// in case the value is still null, try default value(mostly for google fonts)
				if ( ! is_array( $value ) || $value === null ) {
					$value = $this->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
				}

				//bail if by this time we don't have a value of some sort
				if ( empty( $value ) ) {
					continue;
				}

				//Handle special logic for when the $value array is not an associative array
				if ( ! self::is_assoc( $value ) ) {
					$value = $this->process_a_not_associative_font_default( $value );
				}

				if ( isset( $value['font_family'] ) && isset( $value['type'] ) && $value['type'] == 'google' ) {
					$args['google_families'] .= "'" . $value['font_family'];

					if ( $load_all_weights && is_array( $value['variants'] ) ) {
						$args['google_families'] .= ":" . implode( ',', $value['variants'] );
					} elseif ( isset( $value['selected_variants'] ) && ! empty( $value['selected_variants'] ) ) {
						if ( is_array( $value['selected_variants'] ) ) {
							$args['google_families'] .= ":" . implode( ',', $value['selected_variants'] );
						} elseif ( is_string( $value['selected_variants'] ) || is_numeric( $value['selected_variants'] ) ) {
							$args['google_families'] .= ":" . $value['selected_variants'];
						}
					} elseif ( isset( $value['variants'] ) && ! empty( $value['variants'] ) ) {
						if ( is_array( $value['variants'] ) ) {
							$args['google_families'] .= ":" . implode( ',', $value['variants'] );
						} else {
							$args['google_families'] .= ":" . $value['variants'];
						}
					}

					if ( isset( $value['selected_subsets'] ) && ! empty( $value['selected_subsets'] ) ) {
						if ( is_array( $value['selected_subsets'] ) ) {
							$args['google_families'] .= ":" . implode( ',', $value['selected_subsets'] );
						} else {
							$args['google_families'] .= ":" . $value['selected_subsets'];
						}
					} elseif ( isset( $value['subsets'] ) && ! empty( $value['subsets'] ) ) {
						if ( is_array( $value['subsets'] ) ) {
							$args['google_families'] .= ":" . implode( ',', $value['subsets'] );
						} else {
							$args['google_families'] .= ":" . $value['subsets'];
						}
					}

					$args['google_families'] .= '\',';
				} elseif ( isset( self::$theme_fonts[ $value['font_family'] ] ) ) {

//					$value['type']     = 'theme_font';
//					$args['local_srcs'] .= self::$theme_fonts[ $value['font_family'] ]['src'] . ',';
//					$value['variants'] = self::$theme_fonts[ $value['font_family'] ]['variants'];

					if ( false === strpos( $args['local_families'], $value['font_family'] ) ) {
						$args['local_families'] .= $value['font_family'];
					}

					if ( false === strpos( $args['local_srcs'], self::$theme_fonts[ $value['font_family'] ]['src'] ) ) {
						$args['local_srcs'] .= self::$theme_fonts[ $value['font_family'] ]['src'] . ',';
					}
				}
			}
		}

		if ( ( ! empty ( $args['local_families'] ) || ! empty ( $args['google_families'] ) ) && self::get_plugin_option( 'typography', '1' ) && self::get_plugin_option( 'typography_google_fonts', 1 ) ) {
			$this->display_webfont_script( $args );
		}

		foreach ( self::$typo_settings as $key => $font ) {
			if ( empty( $font['selector'] ) || empty( $font['value'] ) ) {
				continue;
			}

			$value = json_decode( PixCustomifyPlugin::decodeURIComponent( $font['value'] ), true );
			// in case the value is still null, try default value(mostly for google fonts)
			if ( $value === null ) {
				$value = $this->get_font_defaults_value( $font['value'] );
			}

			// shim the old case when the default was only the font name
			if ( is_string( $value ) && ! empty( $value ) ) {
				$value = array( 'font_family' => $value );
			}

			//Handle special logic for when the $value array is not an associative array
			if ( ! self::is_assoc( $value ) ) {
				$value = $this->process_a_not_associative_font_default( $value );
			}

			$this->output_font_style( $key, $font, $value );
		}
	}

	function display_webfont_script( $args ) { ?>
		<script type="text/javascript">
			var customify_font_loader = function () {
				var webfontargs = {
					classes: false,
					events: false
				};
				<?php if ( ! empty( $args['google_families'] ) ) { ?>
				webfontargs.google = { families: [<?php echo( rtrim( $args['google_families'], ',' ) ); ?>] };
				<?php }
				if ( ! empty( $args['local_families'] ) && ! empty( $args['local_srcs'] ) ) { ?>
				webfontargs.custom = {
					families: ['<?php echo( rtrim( $args['local_families'], ',' ) ); ?>'],
					urls: ['<?php echo rtrim( $args['local_srcs'], ',' ) ?>']
				};
				<?php } ?>
				WebFont.load(webfontargs);
			}

			if ( typeof WebFont !== 'undefined' ) { <?php // if there is a WebFont object, use it ?>
				customify_font_loader();
			} else { <?php // basically when we don't have the WebFont object we create the google script dynamically  ?>
				var tk = document.createElement('script');
				tk.src = '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
				tk.type = 'text/javascript';

				tk.onload = tk.onreadystatechange = function () {
					customify_font_loader();
				};
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(tk, s);
			}
		</script>
		<?php
	}

	function output_font_style( $field, $font, $value ) {
		$value = $this->validate_font_values( $value );
		// some sanitizing
		$load_all_weights = false;
		if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
			$load_all_weights = true;
		}

		$selected_variant = '';
		if ( ! empty( $value['selected_variants'] ) ) {
			if ( is_array( $value['selected_variants'] ) ) {
				$selected_variant = $value['selected_variants'][0];
			} else {
				$selected_variant = $value['selected_variants'];
			}
		} ?>
		<style id="customify_font_output_for_<?php echo $field; ?>">
			<?php
			if ( isset( $font['callback'] ) && function_exists( $font['callback'] ) ) {
				$output = call_user_func( $font['callback'], $value, $font );
				echo $output;
			} else {
				echo $font['selector'] . " {";

				// First handle the case where we have the font-family in the selected variant (usually this means a custom font from our Fonto plugin)
				if ( ! empty( $selected_variant ) && is_array( $selected_variant ) && ! empty( $selected_variant['font-family'] ) ) {
					//the variant's font-family
					$this->display_property( 'font-family', $selected_variant['font-family'] );

					if ( ! $load_all_weights ) {
						// if this is a custom font (like from our plugin Fonto) with individual styles & weights - i.e. the font-family says it all
						// we need to "force" the font-weight and font-style
						if ( ! empty( $value['type'] ) && 'custom_individual' == $value['type'] ) {
							$selected_variant['font-weight'] = '400 !important';
							$selected_variant['font-style'] = 'normal !important';
						}

						// output the font weight, if available
						if ( ! empty( $selected_variant['font-weight'] ) ) {
							echo ": " . $selected_variant['font-weight'] . ";\n";
							$this->display_property( 'font-weight', $selected_variant['font-weight'] );
						}

						// output the font style, if available
						if ( ! empty( $selected_variant['font-style'] ) ) {
							$this->display_property( 'font-style', $selected_variant['font-style'] );
						}
					}

				} elseif ( isset( $value['font_family'] ) ) {
					// the selected font family
					$this->display_property( 'font-family', $value['font_family'] );

					if ( ! empty( $selected_variant ) && ! $load_all_weights ) {
						$weight_and_style = strtolower( $selected_variant );
						$italic_font = false;

						//determine if this is an italic font (the $weight_and_style is usually like '400' or '400italic' )
						if ( strpos( $weight_and_style, 'italic' ) !== false ) {
							$weight_and_style = str_replace( 'italic', '', $weight_and_style);
							$italic_font = true;
						}

						if ( ! empty( $weight_and_style ) ) {
							//a little bit of sanity check - in case it's not a number
							if( $weight_and_style === 'regular' ) {
								$weight_and_style = 'normal';
							}
							$this->display_property( 'font-weight', $weight_and_style );
						}

						if ( $italic_font ) {
							$this->display_property( 'font-style', 'italic' );
						}
					}
				} else if (  isset( $value['font-family'] ) ) {
					$this->display_property( 'font-family', $value['font-family'] );
				}

				if ( ! empty( $value['font_weight'] ) ) {
					$this->display_property( 'font-weight', $value['font_weight'] );
				}

				if ( ! empty( $value['font_size'] ) ) {
					$unit = $this->get_field_unit( $font, 'font-size' );
					$this->display_property( 'font-size', $value['font_size'], $unit );
				}

				if ( ! empty( $value['line_height'] ) ) {
					$unit = $this->get_field_unit( $font, 'line-height' );
					$this->display_property( 'line-height', $value['line_height'], $unit );
				}

				if ( ! empty( $value['letter_spacing'] ) ) {
					$unit = $this->get_field_unit( $font, 'letter-spacing' );
					$this->display_property( 'letter-spacing', $value['letter_spacing'], $unit );
				}

				if ( ! empty( $value['text_align'] ) ) {
					$this->display_property( 'text-align', $value['text_align'] );
				}

				if ( ! empty( $value['text_transform'] ) ) {
					$this->display_property( 'text-transform', $value['text_transform'] );
				}

				if ( ! empty( $value['text_decoration'] ) ) {
					$this->display_property( 'text-decoration', $value['text_decoration'] );
				}
				echo "}\n";
			} ?>
		</style>
	<?php
	}

	function get_field_unit( $font, $field ) {

		if ( empty( $font ) || empty( $font['fields'] ) || empty( $font['fields'][ $field ] ) ) {
			return 'px';
		}

		if ( isset( $font['fields'][ $field ]['unit'] ) ) {
			return $font['fields'][ $field ]['unit'];
		}

		if ( isset( $font['fields'][ $field ][3] ) ) {
			return $font['fields'][ $field ][3];
		}

		return 'px';
	}

	function validate_font_values( $values ) {

		if ( empty( $values ) ) {
			return array();
		}

		foreach ( $values as $key => $value ) {

			if ( strpos( $key, '-' ) !== false ) {
				$new_key = str_replace( '-', '_', $key );

				$values[ $new_key ] = $value;

				unset( $values[ $key ] );
			}
		}

		return $values;
	}

	function display_selector( $selector ) {

	}

	function display_property( $property, $value, $unit = '' ) {
		echo "\n" . $property . ": " . $value . $unit . ";\n";
	}

	function enqueue_admin_customizer_preview_assets() {
		$dir = plugin_dir_url( __FILE__ );
		$dir = rtrim( $dir, 'features/' );
		wp_enqueue_script( 'font_selector_preview', $dir . '/js/font_selector_preview.js', array( 'jquery' ), false, true );
	}
}
