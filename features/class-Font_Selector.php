<?php

class Customify_Font_Selector extends PixCustomifyPlugin{

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;
	protected $parent = null;
	protected static $typo_settings = null;
	protected static $options_list = null;

	function __construct( $parent ) {

		add_action( 'customize_preview_init', array( $this, 'enqueue_admin_customizer_preview_assets' ), 10);
		$load_location = PixCustomifyPlugin::get_plugin_option( 'style_resources_location', 'wp_head' );
		add_action( $load_location, array( $this, 'output_font_dynamic_style' ), 999999999 );

		$this->parent = $parent;
	}

	function output_font_dynamic_style() {

		self::$options_list = $this->get_options();

		self::get_typography_fields( self::$options_list, 'type', 'font', self::$typo_settings );

		if ( empty( self::$typo_settings ) ) {
			return;
		}

		$families = '';

		foreach ( self::$typo_settings as $id => $font ) {
			if ( isset ( $font['value'] ) ) {

				$load_all_weights = false;
				if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
					$load_all_weights = true;
				}

				$value = json_decode( wp_unslash( PixCustomifyPlugin::decodeURIComponent( $font['value'] ) ), true );

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
					$families .= "'" . $value['font_family'];

					if ( $load_all_weights && is_array( $value['variants'] ) ) {
						$families .= ":" . implode( ',', $value['variants'] );
					} elseif ( isset( $value['selected_variants'] ) && ! empty( $value['selected_variants'] ) ) {
						if ( is_array( $value['selected_variants'] ) ) {
							$families .= ":" . implode( ',', $value['selected_variants'] );
						} elseif ( is_string( $value['selected_variants'] ) || is_numeric( $value['selected_variants'] ) ) {
							$families .= ":" . $value['selected_variants'];
						}
					} elseif ( isset( $value['variants'] ) && ! empty( $value['variants'] ) ) {
						if ( is_array( $value['variants'] ) ) {
							$families .= ":" . implode( ',', $value['variants'] );
						} else {
							$families .= ":" . $value['variants'];
						}
					}

					if ( isset( $value['selected_subsets'] ) && ! empty( $value['selected_subsets'] ) ) {
						if ( is_array( $value['selected_subsets'] ) ) {
							$families .= ":" . implode( ',', $value['selected_subsets'] );
						} else {
							$families .= ":" . $value['selected_subsets'];
						}
					} elseif ( isset( $value['subsets'] ) && ! empty( $value['subsets'] ) ) {
						if ( is_array( $value['subsets'] ) ) {
							$families .= ":" . implode( ',', $value['subsets'] );
						} else {
							$families .= ":" . $value['subsets'];
						}
					}

					$families .= '\',';
				}
			}
		}


		if ( ! empty ( $families ) && self::get_plugin_option( 'typography', '1' ) && self::get_plugin_option( 'typography_google_fonts', 1 ) ) { ?>
			<script type="text/javascript">
				if ( typeof WebFont !== 'undefined' ) {<?php // if there is a WebFont object, use it ?>
					WebFont.load( {
						google: {families: [<?php echo( rtrim( $families, ',' ) ); ?>]},
						classes: false,
						events: false
					} );
				} else {<?php // basically when we don't have the WebFont object we create the google script dynamically  ?>

					var tk = document.createElement( 'script' );
					tk.src = '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
					tk.type = 'text/javascript';

					tk.onload = tk.onreadystatechange = function() {
						WebFont.load( {
							google: {families: [<?php echo( rtrim( $families, ',' ) ); ?>]},
							classes: false,
							events: false
						} );
					};

					var s = document.getElementsByTagName( 'script' )[0];
					s.parentNode.insertBefore( tk, s );
				}
			</script>
		<?php }

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

	function output_font_style( $field, $font, $value ) {

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
			}

			if ( ! empty( $value['font_size'] ) ) {
				$this->display_property( 'font-size', $value['font_size'], 'px' );
			}

			if ( ! empty( $value['line_height'] ) ) {
				$this->display_property( 'line-height', $value['line_height'], 'px' );
			}

			if ( ! empty( $value['letter_spacing'] ) ) {
				$this->display_property( 'letter-spacing', $value['letter_spacing'], 'px' );
			}

			if ( ! empty( $value['text-align'] ) ) {
				$this->display_property( 'text-align', $value['text-align'] );
			}

			if ( ! empty( $value['text-transform'] ) ) {
				$this->display_property( 'text-transform', $value['text-transform'] );
			}

			if ( ! empty( $value['text-decoration'] ) ) {
				$this->display_property( 'text-decoration', $value['text-decoration'] );
			}
			echo "}\n"; ?>

		</style>
	<?php }

	function display_selector ( $selector ) {

	}

	function display_property( $property, $value, $unit = '' ) {
		echo "\n" . $property . ": " . $value . $unit . ";\n";
	}

	function enqueue_admin_customizer_preview_assets(){
		$dir = plugin_dir_url( __FILE__ );
		$dir = rtrim( $dir, 'features/' );
		wp_enqueue_script('font_selector_preview',  $dir . '/js/font_selector_preview.js', array( 'jquery' ), false, true);
	}
}



