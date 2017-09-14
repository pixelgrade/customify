<?php

class Customify_Font_Selector {

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $_instance = null;

	protected static $typo_settings = null;
	protected static $options_list = null;
	protected $theme_fonts = null;
	protected $customify_CSS_output = array();


	function __construct() {
		$this->theme_fonts = apply_filters( 'customify_theme_fonts', array() );

		$load_location = PixCustomifyPlugin()->get_plugin_setting( 'style_resources_location', 'wp_head' );
		add_action( $load_location, array( $this, 'output_font_dynamic_style' ), 100 );
		add_action( 'customify_font_family_before_options', array( $this, 'add_customify_theme_fonts' ), 11, 2 );

		if ( PixCustomifyPlugin()->get_plugin_setting( 'enable_editor_style', true ) ) {
			add_action( 'admin_head', array( $this, 'add_customizer_settings_into_wp_editor' ) );
		}

	}

	function add_customizer_settings_into_wp_editor() {

		ob_start();
		$this->output_font_dynamic_style();

		$custom_css = ob_get_clean(); ?>
		<script type="text/javascript">
			/* <![CDATA[ */
			(function ($) {
				$(window).load(function () {
					/**
					 * @param iframe_id the id of the frame you whant to append the style
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
						ifrm = ( ifrm.contentDocument || ifrm.contentDocument || ifrm.document );
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
			/* ]]> */
		</script>
	<?php }

	public function get_theme_fonts() {
		return $this->theme_fonts;
	}

	function add_customify_theme_fonts( $active_font_family, $val ) {
		//first get all the published custom fonts
		if ( empty( $this->theme_fonts ) ) {
			return;
		}

		echo '<optgroup label="' . esc_html__( 'Theme Fonts', 'customify' ) . '">';
		foreach ( $this->theme_fonts as $font ) {
			if ( ! empty( $font ) ) {
				//display the select option's HTML
				Pix_Customize_Font_Control::output_font_option( $font['family'], $active_font_family, $font, 'theme_font' );
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

	function output_font_dynamic_style() {

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		self::$options_list = $local_plugin->get_options();

		$local_plugin->get_typography_fields( self::$options_list, 'type', 'font', self::$typo_settings );

		if ( empty( self::$typo_settings ) ) {
			return;
		}

		$google_families = $local_families = '';

		$args = array(
			'google_families' => '',
			'local_families'  => '',
			'local_srcs'      => '',
		);

		foreach ( self::$typo_settings as $id => $font ) {
			if ( isset ( $font['value'] ) ) {

				$load_all_weights = false;
				if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
					$load_all_weights = true;
				}

				$value = $this->maybe_decode_value( $font['value'] );

				$value = $this->validate_font_values( $value );

				// in case the value is still null, try default value(mostly for google fonts)
				if ( ! is_array( $value ) || $value === null ) {
					$value = $local_plugin->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
				}

				//bail if by this time we don't have a value of some sort
				if ( empty( $value ) ) {
					continue;
				}

				//Handle special logic for when the $value array is not an associative array
				if ( ! $local_plugin->is_assoc( $value ) ) {
					$value = $local_plugin->process_a_not_associative_font_default( $value );
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
				} elseif ( isset( $this->theme_fonts[ $value['font_family'] ] ) ) {

//					$value['type']     = 'theme_font';
//					$args['local_srcs'] .= $this->theme_fonts[ $value['font_family'] ]['src'] . ',';
//					$value['variants'] = $this->theme_fonts[ $value['font_family'] ]['variants'];

					if ( false === strpos( $args['local_families'], $value['font_family'] ) ) {
						$args['local_families'] .= "'" . $value['font_family'] . "',";
					}

					if ( false === strpos( $args['local_srcs'], $this->theme_fonts[ $value['font_family'] ]['src'] ) ) {
						$args['local_srcs'] .= "'" . $this->theme_fonts[ $value['font_family'] ]['src'] . "',";
					}
				}
			}
		}

		if ( ( ! empty ( $args['local_families'] ) || ! empty ( $args['google_families'] ) ) && PixCustomifyPlugin()->get_plugin_setting( 'typography', '1' ) && PixCustomifyPlugin()->get_plugin_setting( 'typography_google_fonts', 1 ) ) {
			$this->display_webfont_script( $args );
		}

		foreach ( self::$typo_settings as $key => $font ) {
			if ( empty( $font['selector'] ) || empty( $font['value'] ) ) {
				continue;
			}
			$value = $this->maybe_decode_value( $font['value'] );

			if ( $value === null ) {
				$value = $local_plugin->get_font_defaults_value( $font['value'] );
			}

			// shim the old case when the default was only the font name
			if ( is_string( $value ) && ! empty( $value ) ) {
				$value = array( 'font_family' => $value );
			}

			//Handle special logic for when the $value array is not an associative array
			if ( ! $local_plugin->is_assoc( $value ) ) {
				$value = $local_plugin->process_a_not_associative_font_default( $value );
			}

			$this->output_font_style( $key, $font, $value );
		}

		// in customizer the CSS is printed per option, in front-end we need to print them in bulk
		if ( ! isset( $GLOBALS['wp_customize'] ) ) { ?>
<style id="customify_fonts_output">
<?php echo join( "\n", $this->customify_CSS_output ); ?>
</style><?php
			return;
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
				webfontargs.google = {families: [<?php echo( rtrim( $args['google_families'], ',' ) ); ?>]};
				<?php }
				if ( ! empty( $args['local_families'] ) && ! empty( $args['local_srcs'] ) ) { ?>
				webfontargs.custom = {
					families: [<?php echo( rtrim( $args['local_families'], ',' ) ); ?>],
					urls: [<?php echo rtrim( $args['local_srcs'], ',' ) ?>]
				};
				<?php } ?>
				WebFont.load(webfontargs);
			};

			if (typeof WebFont !== 'undefined') { <?php // if there is a WebFont object, use it ?>
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
		}

		ob_start();

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

					$italic_font = false;

					// output the font weight, if available
					if ( ! empty( $selected_variant['font-weight'] ) ) {
						echo ": " . $selected_variant['font-weight'] . ";\n";
						$italic_font = $this->display_weight_property( $selected_variant['font-weight'] );
					}

					// output the font style, if available and if it wasn't displayed already
					if ( ! $italic_font && ! empty( $selected_variant['font-style'] ) ) {
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
					if ( ! empty( $weight_and_style ) ) {
						//a little bit of sanity check - in case it's not a number
						if( $weight_and_style === 'regular' ) {
							$weight_and_style = 'normal';
						}
						$italic_font = $this->display_weight_property( $weight_and_style );
					}

					// output the font style, if available
					if ( ! $italic_font && ! empty( $selected_variant['font-style'] ) ) {
						$this->display_property( 'font-style', $selected_variant['font-style'] );
					}
				}
			} else if (  isset( $value['font-family'] ) ) {
				$this->display_property( 'font-family', $value['font-family'] );
			}

			if ( ! empty( $value['font_weight'] ) ) {
				$italic_font = $this->display_weight_property( $value['font_weight'] );
			}

			if ( ! empty( $value['font_size'] ) ) {
				$unit = $this->get_field_unit( $font, 'font-size' );
				$this->display_property( 'font-size', $value['font_size'], $unit );
			}

			if ( isset( $value['line_height'] ) ) {
				$unit = $this->get_field_unit( $font, 'line-height' );
				$this->display_property( 'line-height', $value['line_height'], $unit );
			}

			if ( isset( $value['letter_spacing'] ) ) {
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
		}

		$CSS = ob_get_clean();

		if ( isset( $GLOBALS['wp_customize'] ) ) { ?>
			<style id="customify_font_output_for_<?php echo $field; ?>">
				<?php echo $CSS ?>
			</style><?php
			return;
		} else {
			$this->customify_CSS_output[] = $CSS;
		}
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

	// well weight sometimes comes from google as 600italic which in CSS syntax should come in two separate properties
	function display_weight_property( $value ) {
		$has_style = false;

		if ( strpos( $value, 'italic' ) !== false ) {

			$value = str_replace( 'italic', '', $value );
			echo "\n" . 'font-weight' . ": " . $value . ";\n";
			echo "\n" . 'font-style' . ": italic;\n";
			$has_style = true;
		} else {
			echo "\n" . 'font-weight' . ": " . $value . ";\n";
		}


		return $has_style;
	}

	/**
	 * Main Customify_Font_Selector Instance
	 *
	 * Ensures only one instance of Customify_Font_Selector is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @return Customify_Font_Selector Main Customify_Font_Selector instance
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?' ) ), '' );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ), '' );
	} // End __wakeup ()
}
