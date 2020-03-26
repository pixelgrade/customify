<?php

class Customify_Fonts_Global {

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $_instance = null;

	protected static $typo_settings = null;
	protected static $options_list = null;

	protected $std_fonts = null;
	protected $google_fonts = null;
	protected $theme_fonts = null;
	protected $cloud_fonts = null;


	function __construct() {

		$this->std_fonts = apply_filters( 'customify_filter_standard_fonts_list', array(
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

		$this->maybe_load_google_fonts();
		// This is intentionally commented as it is only used in development to refresh the Google Fonts list
//		$this->generate_google_fonts_json();

		$this->theme_fonts = apply_filters( 'customify_theme_fonts', array() );
		$this->cloud_fonts = apply_filters( 'customify_cloud_fonts', array() );

		$load_location = PixCustomifyPlugin()->settings->get_plugin_setting( 'style_resources_location', 'wp_head' );
		add_action( $load_location, array( $this, 'output_webfont_script' ), 99 );
		add_action( $load_location, array( $this, 'output_fonts_dynamic_style' ), 100 );

		// We want the theme fonts first, then cloud fonts.
		add_action( 'customify_font_family_before_options', array( $this, 'add_customify_cloud_fonts' ), 11, 1 );
		add_action( 'customify_font_family_before_options', array( $this, 'add_customify_theme_fonts' ), 12, 1 );

		if ( PixCustomifyPlugin()->settings->get_plugin_setting( 'enable_editor_style', true ) ) {
			add_action( 'admin_head', array( $this, 'script_to_add_customizer_settings_into_wp_editor' ) );
		}

		add_action( 'customize_controls_print_footer_scripts', array(
			$this,
			'customize_pane_settings_google_fonts_options'
		), 10000 );
	}

	function script_to_add_customizer_settings_into_wp_editor() {

		ob_start();
		$this->output_fonts_dynamic_style();

		$custom_css = ob_get_clean();

		ob_start(); ?>
	(function ($) {
		$(window).load(function () {
			/**
			 * @param iframe_id the id of the frame you want to append the style
			 * @param style_element the style element you want to append
			 */
			var append_script_to_iframe = function (ifrm_id, scriptEl) {
				var myIframe = document.getElementById(ifrm_id);

				var script = myIframe.contentWindow.document.createElement("script");
				script.type = "text/javascript";
				script.innerHTML = scriptEl.innerHTML;

				myIframe.contentWindow.document.head.appendChild(script);
			};

			var append_style_to_iframe = function (ifrm_id, styleElment) {
				var ifrm = window.frames[ifrm_id];
				if ( typeof ifrm === "undefined" ) {
				    return;
				}
				ifrm = ( ifrm.contentDocument || ifrm.document );
				var head = ifrm.getElementsByTagName('head')[0];

				if (typeof styleElment !== "undefined") {
					head.appendChild(styleElment);
				}
			};

			var xmlString = <?php echo json_encode( str_replace( "\n", "", $custom_css ) ); ?>,
				parser = new DOMParser(),
				doc = parser.parseFromString(xmlString, "text/html");

			if (typeof window.frames['content_ifr'] !== 'undefined') {

				$.each(doc.head.childNodes, function (key, el) {
					if (typeof el !== "undefined" && typeof el.tagName !== "undefined") {

						switch (el.tagName) {
							case 'STYLE' :
								append_style_to_iframe('content_ifr', el);
								break;
							case 'SCRIPT' :
								append_script_to_iframe('content_ifr', el);
								break;
							default:
								break;
						}
					}
				});
			}
		});
	})(jQuery);
		<?php
		$script = ob_get_clean();
		wp_add_inline_script( 'editor', $script );
	}

	public function get_theme_fonts() {
		return $this->theme_fonts;
	}

	function add_customify_theme_fonts( $active_font_family ) {
		// First get all the published custom fonts.
		if ( empty( $this->theme_fonts ) ) {
			return;
		}

		echo '<optgroup label="' . esc_html__( 'Theme Fonts', 'customify' ) . '">';
		foreach ( $this->theme_fonts as $font ) {
			if ( ! empty( $font ) ) {
				// Display the select option's HTML.
				Pix_Customize_Font_Control::output_font_option( $font, $active_font_family, 'theme_font' );
			}
		}
		echo "</optgroup>";
	}

	public function get_cloud_fonts() {
		return $this->cloud_fonts;
	}

	function add_customify_cloud_fonts( $active_font_family ) {
		// First get all the published custom fonts.
		if ( empty( $this->cloud_fonts ) ) {
			return;
		}

		echo '<optgroup label="' . esc_html__( 'Cloud Fonts', 'customify' ) . '">';
		foreach ( $this->cloud_fonts as $font ) {
			if ( ! empty( $font ) ) {
				// Display the select option's HTML.
				Pix_Customize_Font_Control::output_font_option( $font, $active_font_family, 'cloud_font' );
			}
		}
		echo "</optgroup>";
	}



	function maybe_decode_value( $value ) {

		$to_return = PixCustomifyPlugin::decodeURIComponent( $value );
		if ( is_string( $value ) ) {
			$to_return = json_decode( wp_unslash( $to_return ), true );
		}

		return $to_return;
	}

	protected function get_fonts_args() {

		$args = array(
			'google_families' => array(),
			'local_families'  => array(),
			'local_srcs'      => array(),
			'cloud_families'  => array(),
			'cloud_srcs'      => array(),
		);

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		self::$options_list = $local_plugin->get_options_details();

		$local_plugin->customizer->get_typography_fields( self::$options_list, 'type', 'font', self::$typo_settings );

		if ( empty( self::$typo_settings ) ) {
			return $args;
		}

		foreach ( self::$typo_settings as $id => $font ) {
			if ( isset ( $font['value'] ) ) {

				$load_all_weights = false;
				if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
					$load_all_weights = true;
				}

				$value = $this->maybe_decode_value( $font['value'] );

				$value = $this->validate_font_values( $value );

				// in case the value is still null, try default value (mostly for google fonts)
				if ( ! is_array( $value ) || $value === null ) {
					$value = $this->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
				}

				//bail if by this time we don't have a value of some sort
				if ( empty( $value ) ) {
					continue;
				}

				// Handle special logic for when the $value array is not an associative array
				if ( ! $local_plugin->is_assoc( $value ) ) {
					$value = $this->standardize_non_associative_font_default( $value );
				}

				// If we have reached this far and we don't have a type, we will assume it's a google font.
				if ( ! isset( $value['type'] ) ) {
					$value['type'] = 'google';
				}

				if ( ! isset( $value['font_family'] ) ) {
					continue;
				}

				// Handle fonts defined by the current theme.
				if ( isset( $this->theme_fonts[ $value['font_family'] ] ) ) {

					if ( false === array_search( $value['font_family'], $args['local_families'] )
					     && false === array_search( $value['font_family'], $args['cloud_families'] ) ) {

						$font_family = $value['font_family'];
						if ( ! empty( $this->theme_fonts[ $value['font_family'] ]['variants'] ) ) {
							$font_family .= join( ',', $this->convert_font_variants_to_fvds( $this->theme_fonts[ $value['font_family'] ]['variants'] ) );
						}
						$args['local_families'][] = "'" . $font_family . "'";
					}

					if ( false === array_search( $this->theme_fonts[ $value['font_family'] ]['src'], $args['local_srcs'] )
						&& false === array_search( $this->theme_fonts[ $value['font_family'] ]['src'], $args['cloud_srcs'] ) ) {

						$args['local_srcs'][] = "'" . $this->theme_fonts[ $value['font_family'] ]['src'] . "'";
					}
				} else
					// Handle fonts from the cloud.
					if ( isset( $this->cloud_fonts[ $value['font_family'] ] ) && isset( $value['type'] ) && $value['type'] === 'cloud_font' ) {

					if ( false === array_search( $value['font_family'], $args['local_families'] )
					     && false === array_search( $value['font_family'], $args['cloud_families'] ) ) {

						$font_family = $value['font_family'];
						if ( ! empty( $this->cloud_fonts[ $value['font_family'] ]['variants'] ) ) {
							$font_family .= join( ',', $this->convert_font_variants_to_fvds( $this->cloud_fonts[ $value['font_family'] ]['variants'] ) );
						}
						$args['cloud_families'][] = "'" . $font_family . "'";
					}

					if ( false === array_search( $this->cloud_fonts[ $value['font_family'] ]['src'], $args['local_srcs'] )
					     && false === array_search( $this->cloud_fonts[ $value['font_family'] ]['src'], $args['cloud_srcs'] ) ) {

						$args['cloud_srcs'][] = "'" . $this->cloud_fonts[ $value['font_family'] ]['src'] . "'";
					}
				} else
					// Handle Google fonts.
					if ( isset( $value['font_family'] ) && isset( $value['type'] ) && $value['type'] === 'google' ) {
					$family = "'" . $value['font_family'];

					if ( $load_all_weights && ! empty( $value['variants'] ) && is_array( $value['variants'] ) ) {
						$family .= ":" . implode( ',', $value['variants'] );
					} elseif ( ! empty( $value['selected_variants'] ) ) {
						if ( is_array( $value['selected_variants'] ) ) {
							$family .= ":" . implode( ',', $value['selected_variants'] );
						} elseif ( is_string( $value['selected_variants'] ) || is_numeric( $value['selected_variants'] ) ) {
							$family .= ":" . $value['selected_variants'];
						}
					} elseif ( ! empty( $value['variants'] ) ) {
						if ( is_array( $value['variants'] ) ) {
							$family .= ":" . implode( ',', $value['variants'] );
						} else {
							$family .= ":" . $value['variants'];
						}
					}

					if ( ! empty( $value['selected_subsets'] ) ) {
						if ( is_array( $value['selected_subsets'] ) ) {
							$family .= ":" . implode( ',', $value['selected_subsets'] );
						} else {
							$family .= ":" . $value['selected_subsets'];
						}
					} elseif ( ! empty( $value['subsets'] ) ) {
						if ( is_array( $value['subsets'] ) ) {
							$family .= ":" . implode( ',', $value['subsets'] );
						} else {
							$family .= ":" . $value['subsets'];
						}
					}

					$family .= "'";

					$args['google_families'][] = $family;
				}
			}
		}

		$args = array(
			'google_families' => array_unique( $args['google_families'] ),
			'local_families'  => array_unique( $args['local_families'] ),
			'local_srcs'      => array_unique( $args['local_srcs'] ),
			'cloud_families'  => array_unique( $args['cloud_families'] ),
			'cloud_srcs'      => array_unique( $args['cloud_srcs'] ),
		);

		return $args;
	}

	/**
	 * Will convert an array of CSS like variants into their FVD equivalents. Web Font Loader expects this format.
	 * @link https://github.com/typekit/fvd
	 *
	 * @param array $variants
	 * @return array
	 */
	function convert_font_variants_to_fvds( $variants ) {
		$fvds = array();
		if ( ! is_array( $variants ) || empty( $variants ) ) {
			return $fvds;
		}

		foreach ( $variants as $variant ) {
			if ( ! is_string( $variant ) ) {
				continue;
			}

			$font_style = 'n'; // normal
			if ( false !== strrpos( 'italic', $variant ) ) {
				$font_style = 'i';
				$variant = str_replace( 'italic', '', $variant );
			} elseif ( false !== strrpos( 'oblique', $variant ) ) {
				$font_style = 'o';
				$variant = str_replace( 'oblique', '', $variant );
			}

//          The equivalence:
//
//			1: 100
//			2: 200
//			3: 300
//			4: 400 (default, also recognized as 'normal')
//			5: 500
//			6: 600
//			7: 700 (also recognized as 'bold')
//			8: 800
//			9: 900

			switch ( $variant ) {
				case 100:
					$font_weight = 1;
					break;
				case 200:
					$font_weight = 2;
					break;
				case 300:
					$font_weight = 3;
					break;
				case 500:
					$font_weight = 5;
					break;
				case 600:
					$font_weight = 6;
					break;
				case 700:
				case 'bold':
					$font_weight = 7;
					break;
				case 800:
					$font_weight = 8;
					break;
				case 900:
					$font_weight = 9;
					break;
				default:
					$font_weight = 4;
					break;
			}

			$fvds[] = $font_style . '' .  $font_weight;
		}

		return $fvds;
	}

	/**
	 *
	 * @param $font_name
	 *
	 * @return null
	 */
	public function get_font_defaults_value( $font_name ) {

		if ( isset( $this->google_fonts[ $font_name ] ) ) {
			$value                = $this->google_fonts[ $font_name ];
			$value['font_family'] = $font_name;
			$value['type']        = 'google';

			return $value;
		} elseif ( isset( $this->theme_fonts[ $font_name ] ) ) {
			$value['type']        = 'theme_font';
			$value['src']         = $this->theme_fonts[ $font_name ]['src'];
			$value['variants']    = $this->theme_fonts[ $font_name ]['variants'];
			$value['font_family'] = $this->theme_fonts[ $font_name ]['family'];

			return $value;
		} elseif ( isset( $this->cloud_fonts[ $font_name ] ) ) {
			$value['type']        = 'cloud_font';
			$value['src']         = $this->cloud_fonts[ $font_name ]['src'];
			$value['variants']    = $this->cloud_fonts[ $font_name ]['variants'];
			$value['font_family'] = $this->cloud_fonts[ $font_name ]['family'];

			return $value;
		}

		return null;
	}

	/**
	 * Handle special logic for when the $value array is not an associative array
	 * Return a new associative array with proper keys
	 */
	public function standardize_non_associative_font_default( $value ) {
		// If the value provided is not array, simply return it
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$new_value = array();

		// Let's determine some type of font
		if ( ! isset( $value[2] ) || 'google' == $value[2] ) {
			$new_value = $this->get_font_defaults_value( $value[0] );
		} else {
			$new_value['type'] = $value[2];
		}

		if ( null == $new_value ) {
			$new_value = array();
		}

		// The first entry is the font-family
		if ( isset( $value[0] ) ) {
			$new_value['font_family'] = $value[0];
		}

		// In case we don't have an associative array
		// The second entry is the variants
		if ( isset( $value[1] ) ) {
			$new_value['selected_variants'] = $value[1];
		}

		return $new_value;
	}


	function output_fonts_dynamic_style() {

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		self::$options_list = $local_plugin->get_options_details();
		$local_plugin->customizer->get_typography_fields( self::$options_list, 'type', 'font', self::$typo_settings );

		if ( empty( self::$typo_settings ) ) {
			return;
		}

		$output = '';

		foreach ( self::$typo_settings as $key => $font ) {
			$font_output = $this->get_font_style( $font );
			if ( empty( $font_output ) ) {
				continue;
			}

			$output .= $font_output . "\n";

			// If we are in a Customizer context we will output CSS rules grouped so we can target them.
			// In the frontend we want a whole bulk.
			if ( isset( $GLOBALS['wp_customize'] ) ) { ?>
			<style id="customify_font_output_for_<?php echo sanitize_html_class( $key ); ?>">
				<?php echo $font_output; ?>
				</style><?php
			}
		}

		// in customizer the CSS is printed per option, in front-end we need to print them in bulk
		if ( ! isset( $GLOBALS['wp_customize'] ) ) { ?>
			<style id="customify_fonts_output">
			<?php echo $output; ?>
			</style><?php
		}
	}

	function get_fonts_dynamic_style() {

		$output = '';

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		self::$options_list = $local_plugin->get_options_details();
		$local_plugin->customizer->get_typography_fields( self::$options_list, 'type', 'font', self::$typo_settings );

		if ( empty( self::$typo_settings ) ) {
			return $output;
		}

		foreach ( self::$typo_settings as $key => $font ) {

			$font_output = $this->get_font_style( $font );
			if ( empty( $font_output ) ) {
				continue;
			}

			$output .= $font_output . "\n";
		}

		return $output;
	}

	function get_font_style( $font ) {

		if ( ! isset( $font['selector'] ) ) {
			return '';
		}

		$font['selector'] = apply_filters( 'customify_font_css_selector', $font['selector'], $font );
		if ( empty( $font['selector'] ) || empty( $font['value'] ) ) {
			return '';
		}

		$properties_prefix = '';
		if ( ! empty ( $font['properties_prefix'] ) ) {
			$properties_prefix = $font['properties_prefix'];
		}

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		$value = $this->maybe_decode_value( $font['value'] );

		if ( $value === null ) {
			$value = $this->get_font_defaults_value( $font['value'] );
		}

		// shim the old case when the default was only the font name
		if ( is_string( $value ) && ! empty( $value ) ) {
			$value = array( 'font_family' => $value );
		}

		// Handle special logic for when the $value array is not an associative array
		if ( ! $local_plugin->is_assoc( $value ) ) {
			$value = $this->standardize_non_associative_font_default( $value );
		}

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
		}

		ob_start();

		if ( isset( $font['callback'] ) && function_exists( $font['callback'] ) ) {
			$output = call_user_func( $font['callback'], $value, $font );
			echo $output;
		} elseif ( isset( $font['selector'] ) ) {
			echo $font['selector'] . " {" . "\n";

			// First handle the case where we have the font-family in the selected variant (usually this means a custom font from our Fonto plugin)
			if ( ! empty( $selected_variant ) && is_array( $selected_variant ) && ! empty( $selected_variant['font-family'] ) ) {
				//the variant's font-family
				$this->display_property( 'font-family', $selected_variant['font-family'], '', $properties_prefix );

				if ( ! $load_all_weights ) {
					// if this is a custom font (like from our plugin Fonto) with individual styles & weights - i.e. the font-family says it all
					// we need to "force" the font-weight and font-style
					if ( ! empty( $value['type'] ) && 'custom_individual' == $value['type'] ) {
						$selected_variant['font-weight'] = '400 !important';
						$selected_variant['font-style'] = 'normal !important';
					}

					$italic_font = false;

					// output the font weight, if available
					if ( ! empty( $selected_variant['font-weight'] ) ) {
						echo ": " . $selected_variant['font-weight'] . ";\n";
						$italic_font = $this->display_weight_property( $selected_variant['font-weight'], $properties_prefix );
					}

					// output the font style, if available and if it wasn't displayed already
					if ( ! $italic_font && ! empty( $selected_variant['font-style'] ) ) {
						$this->display_property( 'font-style', $selected_variant['font-style'], '', $properties_prefix );
					}
				}

			} elseif ( isset( $value['font_family'] ) ) {
				// the selected font family
				$this->display_property( 'font-family', $value['font_family'], '', $properties_prefix );

				if ( ! empty( $selected_variant ) && ! $load_all_weights ) {
					$weight_and_style = strtolower( $selected_variant );
					$italic_font = false;

					//determine if this is an italic font (the $weight_and_style is usually like '400' or '400italic' )
					if ( ! empty( $weight_and_style ) ) {
						//a little bit of sanity check - in case it's not a number
						if( $weight_and_style === 'regular' ) {
							$weight_and_style = 'normal';
						}
						$italic_font = $this->display_weight_property( $weight_and_style, $properties_prefix );
					}

					// output the font style, if available
					if ( ! $italic_font && ! empty( $selected_variant['font-style'] ) ) {
						$this->display_property( 'font-style', $selected_variant['font-style'], '', $properties_prefix );
					}
				}
			} else if (  isset( $value['font-family'] ) ) {
				$this->display_property( 'font-family', $value['font-family'], '', $properties_prefix );
			}

			if ( ! empty( $value['font_weight'] ) ) {
				$this->display_weight_property( $value['font_weight'], $properties_prefix );
			}

			if ( ! empty( $value['font_size'] ) ) {
				// If the value already contains a unit, go with that.
				// We also handle receiving the value in a standardized format ( array with 'value' and 'unit').
				$font_size = $value['font_size'];
				$unit = '';
				if ( is_numeric( $value['font_size'] ) ) {
					$unit = $this->get_field_unit( $font, 'font-size' );
				} elseif ( is_array( $value['font_size'] ) ) {
					if ( isset( $value['font_size']['unit'] ) ) {
						$unit = $value['font_size']['unit'];
					}

					if ( isset( $value['font_size']['value'] ) ) {
						$font_size = $value['font_size']['value'];
					}
				}

				if ( isset( $value['font_size']['unit'] ) && $value['font_size']['unit'] == 'em' && $value['font_size']['value'] >= 9 ) {
					$value['font_size']['unit'] = 'px';
                }

				$this->display_property( 'font-size', $font_size, $unit, $properties_prefix );
			}

			if ( isset( $value['line_height'] ) ) {
				// If the value already contains a unit, go with that.
				// We also handle receiving the value in a standardized format ( array with 'value' and 'unit').
				$line_height = $value['line_height'];
				$unit = '';
				if ( is_numeric( $value['line_height'] ) ) {
					$unit = $this->get_field_unit( $font, 'line-height' );
				} elseif ( is_array( $value['line_height'] ) ) {
					if ( isset( $value['line_height']['unit'] ) ) {
						$unit = $value['line_height']['unit'];
					}

					if ( isset( $value['line_height']['value'] ) ) {
						$line_height = $value['line_height']['value'];
					}
				}

				$this->display_property( 'line-height', $line_height, $unit, $properties_prefix );
			}

			if ( isset( $value['letter_spacing'] ) ) {
				// If the value already contains a unit, go with that.
				// We also handle receiving the value in a standardized format ( array with 'value' and 'unit').
				$letter_spacing = $value['letter_spacing'];
				$unit = '';
				if ( is_numeric( $value['letter_spacing'] ) ) {
					$unit = $this->get_field_unit( $font, 'letter-spacing' );
				} elseif ( is_array( $value['letter_spacing'] ) ) {
					if ( isset( $value['letter_spacing']['unit'] ) ) {
						$unit = $value['letter_spacing']['unit'];
					}

					if ( isset( $value['letter_spacing']['value'] ) ) {
						$letter_spacing = $value['letter_spacing']['value'];
					}
				}

				$this->display_property( 'letter-spacing', $letter_spacing, $unit, $properties_prefix );
			}

			if ( ! empty( $value['text_align'] ) ) {
				$this->display_property( 'text-align', $value['text_align'], '', $properties_prefix );
			}

			if ( ! empty( $value['text_transform'] ) ) {
				$this->display_property( 'text-transform', $value['text_transform'], '', $properties_prefix );
			}

			if ( ! empty( $value['text_decoration'] ) ) {
				$this->display_property( 'text-decoration', $value['text_decoration'], '', $properties_prefix );
			}
			echo "}\n";
		}

		$CSS = ob_get_clean();

		return $CSS;
	}

	function output_webfont_script() {
		$script = $this->get_fonts_dynamic_script();
		if ( ! empty( $script ) ) { ?>
		<script type="text/javascript">
			<?php echo $script; ?>
		</script>
		<?php }
	}

	function get_fonts_dynamic_script() {
		$args = $this->get_fonts_args();

		if ( ( empty ( $args['local_families'] ) && empty ( $args['cloud_families'] ) && empty ( $args['google_families'] ) )
		     || ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography', '1' )
		     || ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_google_fonts', 1 ) ) {
			return '';
		}

		ob_start(); ?>
function customify_font_loader() {
    var webfontargs = {
        classes: true,
        events: true,
		loading: function() {
			jQuery( window ).trigger( 'wf-loading' );
		},
		active: function() {
			jQuery( window ).trigger( 'wf-active' );
		},
		inactive: function() {
			jQuery( window ).trigger( 'wf-inactive' );
		},
    };
        <?php if ( ! empty( $args['google_families'] ) ) { ?>
    webfontargs.google = {
	        families: [<?php echo join( ',', $args['google_families'] ); ?>]
	    };
        <?php }
        $custom_families = array();
        $custom_urls = array();

		if ( ! empty( $args['local_families'] ) && ! empty( $args['local_srcs'] ) ) {
			$custom_families += $args['local_families'];
			$custom_urls += $args['local_srcs'];
		}

		if ( ! empty( $args['cloud_families'] ) && ! empty( $args['cloud_srcs'] ) ) {
			$custom_families += $args['cloud_families'];
			$custom_urls += $args['cloud_srcs'];
		}

        if ( ! empty( $custom_families ) && ! empty( $custom_urls ) ) { ?>
    webfontargs.custom = {
            families: [<?php echo join( ',', $custom_families ); ?>],
            urls: [<?php echo join( ',', $custom_urls ) ?>]
        };
        <?php } ?>
    WebFont.load(webfontargs);
};

if (typeof WebFont !== 'undefined') {
    customify_font_loader();
} else {
    var tk = document.createElement('script');
    tk.src = '//ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js';
    tk.type = 'text/javascript';

    tk.onload = tk.onreadystatechange = function () {
        customify_font_loader();
    };

    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(tk, s);
}
		<?php
		$output = ob_get_clean();

		return apply_filters( 'customify_fonts_webfont_script', $output );
	}

	function get_field_unit( $font, $field ) {

		if ( empty( $font ) || empty( $font['fields'] ) || empty( $font['fields'][ $field ] ) ) {

			if ( 'line-height' == $field ){
				return '';
			}

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

	function display_property( $property, $value, $unit = '', $prefix = '' ) {
		echo $prefix . $property . ": " . $value . $unit . ";\n";
	}

	// well weight sometimes comes from google as 600italic which in CSS syntax should come in two separate properties
	function display_weight_property( $value, $prefix = '' ) {
		$has_style = false;

		if ( strpos( $value, 'italic' ) !== false ) {

			$value = str_replace( 'italic', '', $value );
			echo $prefix . 'font-weight' . ": " . $value . ";\n";
			echo $prefix . 'font-style' . ": italic;\n";
			$has_style = true;
		} else {
			echo $prefix . 'font-weight' . ": " . $value . ";\n";
		}


		return $has_style;
	}

	public function customize_pane_settings_google_fonts_options() {
		if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_google_fonts' ) || empty( $this->google_fonts ) ) {
			return;
		}

		?>
		<script type="text/javascript">
			if ( 'undefined' === typeof _wpCustomizeSettings.settings ) {
				_wpCustomizeSettings.settings = {};
			}

			<?php
			echo "(function ( sAdditional ){\n";

			printf(
				"sAdditional['google_fonts_opts'] = %s;\n",
				wp_json_encode( Pix_Customize_Font_Control::get_google_fonts_opts_html( $this->google_fonts ) )
			);
			echo "})( _wpCustomizeSettings );\n";
			?>
		</script>
		<?php
	}

	/** HELPERS */

	/**
	 * Load the google fonts list from the local file, if not already loaded.
	 *
	 * @return bool|mixed|null
	 */
	protected function maybe_load_google_fonts() {

		if ( empty( $this->google_fonts ) ) {
			$fonts_path = plugin_dir_path( __FILE__ ) . 'resources/google.fonts.php';

			if ( file_exists( $fonts_path ) ) {
				$this->google_fonts = apply_filters( 'customify_filter_google_fonts_list', require( $fonts_path ) );
			}
		}

		if ( ! empty( $this->google_fonts ) ) {
			return $this->google_fonts;
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
		$new_array = array();
		foreach ( $this->google_fonts as $key => $font ) {
			// unset unused data
			unset( $font['kind'] );
			unset( $font['version'] );
			unset( $font['lastModified'] );
			unset( $font['files'] );
			$new_array[ $font['family'] ] = $font;
		}

		file_put_contents( plugin_dir_path( __FILE__ ) . 'resources/google.fonts.json', json_encode( $new_array ) );
	}

	/**
	 * Main Customify_Fonts_Global Instance
	 *
	 * Ensures only one instance of Customify_Fonts_Global is loaded or can be loaded.
	 *
	 * @since  2.7.0
	 * @static
	 *
	 * @return Customify_Fonts_Global Main Customify_Fonts_Global instance
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html__( 'You should not do that!', 'customify' ), '' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ), '' );
	}
}
