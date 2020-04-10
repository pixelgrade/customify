<?php

class Customify_Fonts_Global {

	/**
	 * Instance of this class.
	 * @since    2.7.0
	 * @var      object
	 */
	protected static $_instance = null;

	/**
	 * The standard fonts list.
	 * @since    2.7.0
	 * @var      array
	 */
	protected $std_fonts = null;

	/**
	 * The Google fonts list.
	 * @since    2.7.0
	 * @var      array
	 */
	protected $google_fonts = null;

	/**
	 * The theme defined fonts list.
	 * @since    2.7.0
	 * @var      array
	 */
	protected $theme_fonts = null;

	/**
	 * The cloud fonts list.
	 * @since    2.7.0
	 * @var      array
	 */
	protected $cloud_fonts = null;

	/**
	 * Constructor.
	 *
	 * @since 2.7.0
	 */
	protected function __construct() {
		/*
		 * Standardize the customify_config for field types we can handle.
		 */
		add_filter( 'customify_final_config', array( $this, 'standardize_global_customify_config' ), 99999, 1 );

		// We will initialize the logic after the plugin has finished with it's configuration (at priority 15).
		add_action( 'init', array( $this, 'init' ), 20 );
	}

	/**
	 * Initialize this module.
	 *
	 * @since 2.7.0
	 */
	public function init() {

		/*
		 * Gather all fonts, by type.
		 */
		$this->std_fonts = apply_filters( 'customify_standard_fonts_list', array(
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
			"Tahoma, Geneva, sans-serif"                            => "Tahoma, Geneva, sans-serif",
			"'Times New Roman', Times,serif"                       => "'Times New Roman', Times, serif",
			"'Trebuchet MS', Helvetica, sans-serif"                => "'Trebuchet MS', Helvetica, sans-serif",
			"Verdana, Geneva, sans-serif"                          => "Verdana, Geneva, sans-serif",
		) );

		$this->maybe_load_google_fonts();

		$this->theme_fonts = apply_filters( 'customify_theme_fonts', array() );
		$this->cloud_fonts = apply_filters( 'customify_cloud_fonts', array() );

		/*
		 * Add the fonts to selects of the Customizer controls.
		 */
		add_action( 'customify_font_family_select_options', array( $this, 'output_cloud_fonts_select_options_group' ), 20, 2 );
		add_action( 'customify_font_family_select_options', array( $this, 'output_theme_fonts_select_options_group' ), 30, 2 );
		add_action( 'customify_font_family_select_options', array( $this, 'output_standard_fonts_select_options_group' ), 40, 2 );
		// For Google fonts we will first output just an empty option group, and the rest of the options in a JS variable.
		// This way we don't hammer the DOM too much.
		add_action( 'customify_font_family_select_options', array( $this, 'output_google_fonts_select_options_group' ), 50, 2 );
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'customize_pane_settings_google_fonts_options' ), 10000 );

		/*
		 * Output the frontend fonts specific scripts and styles.
		 */
		$load_location = PixCustomifyPlugin()->settings->get_plugin_setting( 'style_resources_location', 'wp_head' );
		// Add a preconnect links as early as possible for faster external fonts loading.
		add_action('wp_head', array( $this, 'add_preconnect_links' ), 0);
		wp_register_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader',
			plugins_url( 'js/vendor/webfontloader-1-6-28.js', PixCustomifyPlugin()->get_file() ), array(), null, ( 'wp_head' === $load_location ) ? false : true );
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( $load_location, array( $this, 'output_fonts_dynamic_style' ), 100 );

		/*
		 * These are only useful for older Typography fields, not the new Font fields.
		 * @deprecated
		 */
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_typography_frontend_scripts' ) );
		add_action( $load_location, array( $this, 'output_typography_dynamic_style' ), 10 );

		/*
		 * Add integration with the Classic editor.
		 */
		if ( PixCustomifyPlugin()->settings->get_plugin_setting( 'enable_editor_style', true ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'script_to_add_customizer_output_into_wp_editor' ), 10, 1 );
		}

		// Add data to be passed to JS.
		add_filter( 'customify_localized_js_settings', array( $this, 'add_to_localized_data' ), 10, 1 );
	}

	/**
	 * Go deep and identify all the fields we are interested in and standardize their entries.
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function standardize_global_customify_config( $config ) {
		// We will go recursively and search for fonts fields.
		$this->standardize_font_fields_config( $config );

		return $config;
	}

	public function standardize_font_fields_config( &$item ) {
		// We are after fields configs, so not interested in entries that are not arrays.
		if ( ! is_array( $item ) ) {
			return;
		}

		// If we have a `font` field configuration, we have work to do.
		if ( isset( $item['type'] ) && 'font' === $item['type'] ) {
			// We want to standardize the default value, if present.
			if ( ! empty( $item['default'] ) ) {
				$item['default'] = self::standardize_font_values( $item['default'] );
			}

			// We want to standardize the selector(s), if present.
			if ( ! empty( $item['selector'] ) ) {
				$item['selector'] = self::standardizeFontSelector( $item['selector'] );
			}

			// We do a little bit of fields cleanup.
			if ( ! empty( $item['fields'] ) ) {
				// All entries should use dashes not underscores in their keys.
				foreach ( $item['fields'] as $field_type => $value ) {
					if ( strpos( $field_type, '_' ) !== false ) {
						$new_field_type = str_replace( '_', '-', $field_type );
						$item['fields'][ $new_field_type ] = $value;
						unset( $item['fields'][ $field_type ] );
					}
				}
			}

			// Some legacy configs specify a couple of fields outside the `fields` entry. We must cleanup.
			if ( isset( $item['font_weight'] ) && ! isset( $item['fields']['font-weight'] ) ) {
				$item['fields']['font-weight'] = $item['font_weight'];
			}
			if ( isset( $item['subsets'] ) && ! isset( $item['fields']['subsets'] ) ) {
				$item['fields']['subsets'] = $item['subsets'];
			}

			// We have no reason to go recursively further when we have come across a `font` field configuration.
			return;
		}

		foreach ( $item as $key => $subitem ) {
			// We can't use $subitem since that is a copy, and we need to reference the original.
			$this->standardize_font_fields_config( $item[ $key ] );
		}
	}

	public function get_valid_subfield_values( $subfield, $labels = false ) {
		$valid_values = apply_filters( 'customify_fonts_valid_subfield_values', array(
			'text_align'      => array(
				'initial' => esc_html__( 'Initial', 'customify' ),
				'center'  => esc_html__( 'Center', 'customify' ),
				'left'    => esc_html__( 'Left', 'customify' ),
				'right'   => esc_html__( 'Right', 'customify' ),
			),
			'text_transform'  => array(
				'none'       => esc_html__( 'None', 'customify' ),
				'capitalize' => esc_html__( 'Capitalize', 'customify' ),
				'uppercase'  => esc_html__( 'Uppercase', 'customify' ),
				'lowercase'  => esc_html__( 'Lowercase', 'customify' ),
			),
			'text_decoration' => array(
				'none'         => esc_html__( 'None', 'customify' ),
				'underline'    => esc_html__( 'Underline', 'customify' ),
				'overline'     => esc_html__( 'Overline', 'customify' ),
				'line-through' => esc_html__( 'Line Through', 'customify' ),
			),
		) );

		if ( ! empty( $valid_values[ $subfield ] ) ) {
			// Return only the keys if we've been instructed to do so.
			if ( false === $labels && self::isAssocArray( $valid_values[ $subfield ] ) ) {
				return array_keys( $valid_values[ $subfield ] );
			}

			return $valid_values[ $subfield ];
		}

		return array();
	}

	public function get_std_fonts() {
		if ( empty( $this->std_fonts ) ) {
			return array();
		}

		return $this->std_fonts;
	}

	public function get_google_fonts() {
		if ( empty( $this->google_fonts ) ) {
			return array();
		}

		return $this->google_fonts;
	}

	public function get_theme_fonts() {
		if ( empty( $this->theme_fonts ) ) {
			return array();
		}

		return $this->theme_fonts;
	}

	public function get_cloud_fonts() {
		if ( empty( $this->cloud_fonts ) ) {
			return array();
		}

		return $this->cloud_fonts;
	}

	function output_cloud_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_cloud_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->cloud_fonts ) ) {
			echo '<optgroup label="' . esc_html__( 'Cloud Fonts', 'customify' ) . '">';
			foreach ( $this->get_cloud_fonts() as $font ) {
				if ( ! empty( $font ) ) {
					// Display the select option's HTML.
					Pix_Customize_Font_Control::output_font_family_option( $font, $active_font_family, 'cloud_font' );
				}
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_cloud_fonts_options', $active_font_family, $current_value );
	}

	function output_theme_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_theme_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->theme_fonts ) ) {
			echo '<optgroup label="' . esc_html__( 'Theme Fonts', 'customify' ) . '">';
			foreach ( $this->get_theme_fonts() as $font ) {
				if ( ! empty( $font ) ) {
					// Display the select option's HTML.
					Pix_Customize_Font_Control::output_font_family_option( $font, $active_font_family, 'theme_font' );
				}
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_theme_fonts_options', $active_font_family, $current_value );
	}

	function output_standard_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_standard_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->std_fonts ) && PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_standard_fonts' ) ) {

			echo '<optgroup label="' . esc_attr__( 'Standard fonts', 'customify' ) . '">';
			foreach ( $this->get_std_fonts() as $key => $font ) {
				Pix_Customize_Font_Control::output_font_family_option( $font, $active_font_family, 'std' );
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_standard_fonts_options', $active_font_family, $current_value );
	}

	function output_google_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_google_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->google_fonts ) && PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_google_fonts' ) ) {
			// The actual options in this optiongroup will be injected via JS from the output of
			// see@ Customify_Fonts_Global::customize_pane_settings_google_fonts_options()
			echo '<optgroup class="google-fonts-opts-placeholder" label="' . esc_attr__( 'Google fonts', 'customify' ) . '"></optgroup>';
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_google_fonts_options', $active_font_family, $current_value );
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
				wp_json_encode( $this->get_google_fonts_select_options() )
			);
			echo "})( _wpCustomizeSettings );\n";
			?>
		</script>
		<?php
	}

	protected function get_google_fonts_select_options() {

		if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_google_fonts' ) || empty( $this->google_fonts ) ) {
			return '';
		}

		ob_start();
		if ( PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_group_google_fonts' ) ) {

			$grouped_google_fonts = array();
			foreach ( $this->get_google_fonts() as $key => $font ) {
				if ( isset( $font['category'] ) ) {
					$grouped_google_fonts[ $font['category'] ][] = $font;
				}
			}

			foreach ( $grouped_google_fonts as $group_name => $group ) {
				echo '<optgroup label="' . esc_attr__( 'Google fonts', 'customify' ) . ' ' . $group_name . '">';
				foreach ( $group as $key => $font ) {
					Pix_Customize_Font_Control::output_font_family_option( $font );
				}
				echo "</optgroup>";
			}

		} else {
			echo '<optgroup label="' . esc_attr__( 'Google fonts', 'customify' ) . '">';
			foreach ( $this->get_google_fonts() as $key => $font ) {
				Pix_Customize_Font_Control::output_font_family_option( $font );
			}
			echo "</optgroup>";
		}

		return ob_get_clean();
	}

	/**
	 * Gather all the font families that need to be loaded via Web Font Loader.
	 *
	 * @return array
	 */
	protected function get_font_families_details_for_webfontloader() {

		$args = array(
			'google_families' => array(),
			'custom_families'  => array(),
			'custom_srcs'      => array(),
		);

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		$font_fields = array();
		$local_plugin->customizer->get_fields_by_key( $local_plugin->get_options_details(), 'type', 'font', $font_fields );

		if ( empty( $font_fields ) ) {
			return $args;
		}

		// These are fields that should have no frontend impact.
		$excluded_fields = array(
			'sm_font_palette',
			'sm_font_palette_variation',
			'sm_font_primary',
			'sm_font_secondary',
			'sm_font_body',
			'sm_font_accent',
			'sm_swap_fonts',
			'sm_swap_primary_secondary_fonts',
		);

		foreach ( $font_fields as $id => $font ) {
			// Bail if this is an excluded field.
			if ( in_array( $id, $excluded_fields ) ) {
				continue;
			}

			// Bail without a value.
			if ( empty( $font['value'] ) ) {
				continue;
			}

			$value = $this->standardize_font_values( $this->maybe_decode_value( $font['value'] ) );

			// In case the value is empty, try a default value if the $font['value'] is actually the font family.
			if ( empty( $value ) && is_string( $font['value'] ) ) {
				$value = $this->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
			}

			// Bail if we don't have a value or the value isn't an array
			if ( empty( $value ) || ! is_array( $value ) ) {
				continue;
			}

			// We can't do anything without a font family.
			if ( empty( $value['font_family'] ) ) {
				continue;
			}

			// If this matches a standard font, we have nothing to do.
			if ( ! empty( $this->std_fonts[ $value['font_family'] ] ) ) {
				continue;
			}

			// For each font family we will follow a stack: theme fonts, cloud fonts, google fonts.
			if ( ! empty( $this->theme_fonts[ $value['font_family'] ] ) ) {
				$font_details = $this->theme_fonts[ $value['font_family'] ];
				$font_family = $value['font_family'];
				if ( ! empty( $font_details['variants'] ) ) {
					$font_family .= ':' . join( ',', self::convert_font_variants_to_fvds( $font_details['variants'] ) );
				}
				$args['custom_families'][] = "'" . $font_family . "'";
				if ( ! empty( $font_details['src'] ) ) {
					$args['custom_srcs'][] = "'" . $font_details['src'] . "'";
				}
				continue;
			}

			if ( ! empty( $this->cloud_fonts[ $value['font_family'] ] ) ) {
				$font_details = $this->cloud_fonts[ $value['font_family'] ];
				$font_family = $value['font_family'];
				if ( ! empty( $font_details['variants'] ) ) {
					$font_family .= join( ',', self::convert_font_variants_to_fvds( $font_details['variants'] ) );
				}
				$args['custom_families'][] = "'" . $font_family . "'";
				if ( ! empty( $font_details['src'] ) ) {
					$args['custom_srcs'][] = "'" . $font_details['src'] . "'";
				}
				continue;
			}

			// Treat this as a Google font, if we have reached this far.
			$font_family = $value['font_family'];

			if ( ! empty( $value['variants'] ) && is_array( $value['variants'] ) ) {
				$font_family .= ":" . implode( ',', $value['variants'] );
			} elseif ( ! empty( $value['selected_variants'] ) ) {
				if ( is_array( $value['selected_variants'] ) ) {
					$font_family .= ":" . implode( ',', $value['selected_variants'] );
				} elseif ( is_string( $value['selected_variants'] ) || is_numeric( $value['selected_variants'] ) ) {
					$font_family .= ":" . $value['selected_variants'];
				}
			} elseif ( ! empty( $value['variants'] ) ) {
				if ( is_array( $value['variants'] ) ) {
					$font_family .= ":" . implode( ',', $value['variants'] );
				} else {
					$font_family .= ":" . $value['variants'];
				}
			}

			if ( ! empty( $value['selected_subsets'] ) ) {
				if ( is_array( $value['selected_subsets'] ) ) {
					$font_family .= ":" . implode( ',', $value['selected_subsets'] );
				} else {
					$font_family .= ":" . $value['selected_subsets'];
				}
			} elseif ( ! empty( $value['subsets'] ) ) {
				if ( is_array( $value['subsets'] ) ) {
					$font_family .= ":" . implode( ',', $value['subsets'] );
				} else {
					$font_family .= ":" . $value['subsets'];
				}
			}

			// Wrap it.
			$font_family = "'" . $font_family . "'";

			$args['google_families'][] = $font_family;
		}

		$args = array(
			'google_families' => array_unique( $args['google_families'] ),
			'custom_families'  => array_unique( $args['custom_families'] ),
			'custom_srcs'      => array_unique( $args['custom_srcs'] ),
		);

		return $args;
	}

	/**
	 *
	 * @param string $font_family
	 *
	 * @return array
	 */
	public function get_font_defaults_value( $font_family ) {
		if ( empty( $font_family ) ) {
			return array();
		}

		// This is a safe default.
		$value = array( 'font_family' => $font_family );

		if ( isset( $this->google_fonts[ $font_family ] ) ) {
			$value                = $this->google_fonts[ $font_family ];
			$value['font_family'] = $font_family;
			$value['type']        = 'google';
		} elseif ( isset( $this->theme_fonts[ $font_family ] ) ) {
			$value['type']        = 'theme_font';
			$value['src']         = $this->theme_fonts[ $font_family ]['src'];
			$value['variants']    = $this->theme_fonts[ $font_family ]['variants'];
			$value['font_family'] = $this->theme_fonts[ $font_family ]['family'];
		} elseif ( isset( $this->cloud_fonts[ $font_family ] ) ) {
			$value['type']        = 'cloud_font';
			$value['src']         = $this->cloud_fonts[ $font_family ]['src'];
			$value['variants']    = $this->cloud_fonts[ $font_family ]['variants'];
			$value['font_family'] = $this->cloud_fonts[ $font_family ]['family'];
		}

		return $value;
	}

	function output_fonts_dynamic_style() {

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		$font_fields = array();
		$local_plugin->customizer->get_fields_by_key( $local_plugin->get_options_details(), 'type', 'font', $font_fields );

		if ( empty( $font_fields ) ) {
			return;
		}

		$output = '';

		foreach ( $font_fields as $key => $font ) {
			$font_output = $this->get_font_style( $font );
			// Do not print anything, but only if we are not in the Customizer.
			// There we need even the empty <style> since we target it by id.
			if ( empty( $font_output ) && ! is_customize_preview() ) {
				continue;
			}

			$output .= $font_output . "\n";

			// If we are in a Customizer context we will output CSS rules grouped so we can target them individually.
			if ( is_customize_preview() ) { ?>
<style id="customify_font_output_for_<?php echo sanitize_html_class( $key ); ?>">
	<?php echo $font_output; ?>
</style>
				<?php
			}
		}

		// In the front-end we need to print CSS rules in bulk.
		if ( ! empty( $output ) && ! is_customize_preview() ) { ?>
<style id="customify_fonts_output">
	<?php echo $output; ?>
</style>
			<?php
		}
	}

	function get_fonts_dynamic_style() {

		$output = '';

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		$font_fields = array();
		$local_plugin->customizer->get_fields_by_key( $local_plugin->get_options_details(), 'type', 'font', $font_fields );

		if ( empty( $font_fields ) ) {
			return $output;
		}

		foreach ( $font_fields as $key => $font ) {

			$font_output = $this->get_font_style( $font );
			if ( empty( $font_output ) ) {
				continue;
			}

			$output .= $font_output . "\n";
		}

		return $output;
	}

	/**
	 * Get the CSS rules for a given font (with `selector` and `value` sub-entries at least).
	 *
	 * @param array $font
	 *
	 * @return string The CSS rules.
	 */
	protected function get_font_style( $font ) {

		if ( ! isset( $font['selector'] ) || ! isset( $font['value'] ) ) {
			return '';
		}

		$value = $this->standardize_font_values( $this->maybe_decode_value( $font['value'] ) );

		// In case the value is empty, try a default value if the $font['value'] is actually the font family.
		if ( empty( $value ) && is_string( $font['value'] ) ) {
			$value = $this->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
		}

		// In case we receive a callback, its output will be the final result.
		if ( isset( $font['callback'] ) && is_callable( $font['callback'] ) ) {
			return call_user_func( $font['callback'], $value, $font );
		}

		// Make sure we are dealing with a selector as a list of individual selector,
		// maybe some of them having special details like supported properties.
		$css_selectors = apply_filters( 'customify_font_css_selector', self::standardizeFontSelector( $font['selector'] ), $font );
		if ( empty( $css_selectors ) ) {
			return '';
		}

		$properties_prefix = '';
		if ( ! empty ( $font['properties_prefix'] ) ) {
			$properties_prefix = $font['properties_prefix'];
		}

		$selected_variant = '';
		if ( ! empty( $value['selected_variants'] ) ) {
			if ( is_array( $value['selected_variants'] ) ) {
				$selected_variant = $value['selected_variants'][0];
			} else {
				$selected_variant = $value['selected_variants'];
			}
		}

		// Since we might have simple CSS selectors and complex ones (with special details),
		// for cleanliness we will group the simple ones under a single CSS rule,
		// and output individual CSS rules for complex ones.
		// Right now, for complex CSS selectors we are only interested in the `properties` sub-entry.
		$simple_css_selectors = [];
		$complex_css_selectors = [];
		foreach ( $css_selectors as $selector => $details ) {
			if ( empty( $details['properties'] ) ) {
				// This is a simple selector.
				$simple_css_selectors[] = $selector;
			} else {
				$complex_css_selectors[ $selector ] = $details;
			}
		}

		$output = '';

		if ( ! empty( $simple_css_selectors ) ) {
			ob_start();

			echo "\n" . join(', ', $simple_css_selectors ) . " {" . "\n";
			$this->output_font_style_properties( $font, $value, false, $properties_prefix, $selected_variant );
			echo "}\n";

			$output .= ob_get_clean();
		}

		if ( ! empty( $complex_css_selectors ) ) {
			foreach ( $complex_css_selectors as $selector => $details ) {
				ob_start();

				echo "\n" . $selector . " {" . "\n";
				$this->output_font_style_properties( $font, $value, $details['properties'], $properties_prefix, $selected_variant );
				echo "}\n";

				$output .= ob_get_clean();
			}
		}

		return $output;
	}

	protected function output_font_style_properties( $font, $value, $properties = false, $properties_prefix = '', $selected_variant = '') {
		// First handle the case where we have the font-family in the selected variant (usually this means a custom font from our Fonto plugin)
		if ( is_array( $selected_variant ) && ! empty( $selected_variant['font-family'] ) ) {
			// The variant's font-family.
			if ( $this->isCSSPropertyAllowed( 'font-family', $properties ) ) {
				$this->display_property( 'font-family', $selected_variant['font-family'], '', $properties_prefix );
			}

			// If this is a custom font (like from our plugin Fonto) with individual styles & weights - i.e. the font-family says it all
			// We need to "force" the font-weight and font-style
			if ( ! empty( $value['type'] ) && 'custom_individual' == $value['type'] ) {
				$selected_variant['font-weight'] = '400 !important';
				$selected_variant['font-style']  = 'normal !important';
			}

			$italic_style = false;

			// Output the font weight, if available
			if ( ! empty( $selected_variant['font-weight'] ) && $this->isCSSPropertyAllowed( 'font-weight', $properties ) ) {
				$italic_style = $this->display_weight_property( $selected_variant['font-weight'], $properties_prefix );
			}

			// Output the font style, if available and if it wasn't displayed already
			if ( ! $italic_style && ! empty( $selected_variant['font-style'] ) && $this->isCSSPropertyAllowed( 'font-style', $properties ) ) {
				$this->display_property( 'font-style', $selected_variant['font-style'], '', $properties_prefix );
			}

		} elseif ( isset( $value['font_family'] ) ) {
			if ( $this->isCSSPropertyAllowed( 'font-family', $properties ) ) {
				$this->display_property( 'font-family', $value['font_family'], '', $properties_prefix );
			}

			if ( ! empty( $selected_variant ) ) {
				$weight_and_style = strtolower( $selected_variant );

				// Determine if this is an italic font (the $weight_and_style is usually like '400' or '400italic' )
				$italic_style      = false;

				// A little bit of sanity check - in case it's not a number
				if ( $weight_and_style === 'regular' ) {
					$weight_and_style = 'normal';
				}
				if ( $this->isCSSPropertyAllowed( 'font-weight', $properties ) ) {
					$italic_style = $this->display_weight_property( $weight_and_style, $properties_prefix );
				}

				// Output the font style, if available
				if ( ! $italic_style && ! empty( $selected_variant['font-style'] ) && $this->isCSSPropertyAllowed( 'font-style', $properties ) ) {
					$this->display_property( 'font-style', $selected_variant['font-style'], '', $properties_prefix );
				}
			}
		}

		if ( ! empty( $value['font_weight'] ) && $this->isCSSPropertyAllowed( 'font-weight', $properties ) ) {
			$this->display_weight_property( $value['font_weight'], $properties_prefix );
		}

		if ( ! empty( $value['font_size'] ) && $this->isCSSPropertyAllowed( 'font-size', $properties ) ) {

			$font_size = self::standardize_numerical_value( $value['font_size'], 'font-size', $font );
			if ( false !== $font_size['value'] ) {

				// If we use ems or rems, and the value is larger than 9, then something must be wrong; we will use pixels.
				if ( $font_size['value'] >= 9 && in_array( $font_size['unit'], array( 'em', 'rem' ) ) ) {
					$font_size['unit'] = 'px';
				}

				$this->display_property( 'font-size', $font_size['value'], $font_size['unit'], $properties_prefix );
			}
		}

		if ( ! empty( $value['line_height'] ) && $this->isCSSPropertyAllowed( 'line-height', $properties ) ) {

			$line_height = self::standardize_numerical_value( $value['line_height'], 'line-height', $font );
			if ( false !== $line_height['value'] ) {
				$this->display_property( 'line-height', $line_height['value'], $line_height['unit'], $properties_prefix );
			}
		}

		if ( isset( $value['letter_spacing'] ) && false !== $value['letter_spacing'] && $this->isCSSPropertyAllowed( 'letter-spacing', $properties ) ) {

			$letter_spacing = self::standardize_numerical_value( $value['letter_spacing'], 'letter-spacing', $font );
			if ( false !== $letter_spacing['value'] ) {
				$this->display_property( 'letter-spacing', $letter_spacing['value'], $letter_spacing['unit'], $properties_prefix );
			}
		}

		if ( ! empty( $value['text_align'] ) && $this->isCSSPropertyAllowed( 'text-align', $properties ) ) {
			$this->display_property( 'text-align', $value['text_align'], '', $properties_prefix );
		}

		if ( ! empty( $value['text_transform'] ) && $this->isCSSPropertyAllowed( 'text-transform', $properties ) ) {
			$this->display_property( 'text-transform', $value['text_transform'], '', $properties_prefix );
		}

		if ( ! empty( $value['text_decoration'] ) && $this->isCSSPropertyAllowed( 'text-decoration', $properties ) ) {
			$this->display_property( 'text-decoration', $value['text_decoration'], '', $properties_prefix );
		}
	}

	protected function display_property( $property, $value, $unit = '', $prefix = '' ) {
		// We don't want to output empty CSS rules.
		if ( '' === $value ) {
			return;
		}
		echo $prefix . $property . ": " . $value . $unit . ";\n";
	}

	// well weight sometimes comes from google as 600italic which in CSS syntax should come in two separate properties
	protected function display_weight_property( $value, $prefix = '' ) {
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

	public function enqueue_frontend_scripts() {
		$script = $this->get_fonts_dynamic_script();
		if ( ! empty( $script ) ) {
			wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader' );
			wp_add_inline_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader', $script );
		} elseif ( is_customize_preview() ) {
			// If we are in the Customizer preview, we still need the Web Font Loader.
			wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader' );
		}
	}

	function get_fonts_dynamic_script() {
		// If typography has been deactivated from the settings, bail.
		if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography', '1' )
		     || ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_google_fonts', 1 ) ) {
			return '';
		}

		$args = $this->get_font_families_details_for_webfontloader();

		if ( empty ( $args['custom_families'] ) && empty ( $args['google_families'] ) ) {
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

		if ( ! empty( $args['custom_families'] ) && ! empty( $args['custom_srcs'] ) ) {
			$custom_families += $args['custom_families'];
			$custom_urls += $args['custom_srcs'];
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
}<?php
		$output = ob_get_clean();

		return apply_filters( 'customify_fonts_webfont_script', $output );
	}

	/**
	 * Output preconnect links to speed up fonts download and avoid FOUT as much as possible.
	 */
	public function add_preconnect_links() {
		// If typography has been deactivated from the settings, bail.
		if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography', '1' )
		     || ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_google_fonts', 1 ) ) {
			return;
		}

		$args = $this->get_font_families_details_for_webfontloader();
		// If we are not using external fonts, bail.
		if ( empty ( $args['custom_families'] ) && empty ( $args['google_families'] ) ) {
			return;
		}

		// If we are using Google fonts, add the known origins.
		// Google uses two different origins, one for the CSS and another for the actual fonts.
		if ( ! empty ( $args['google_families'] ) ) {
			echo '<link href="https://fonts.googleapis.com" rel="preconnect" crossorigin>';
			echo '<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>';
		}

		// Now deal with custom external fonts.
		if ( ! empty( $args['custom_srcs'] ) ) {
			// Get the site's origin (without the protocol) so we can exclude it.
			$own_origin = self::extract_origin_from_url( get_bloginfo( 'url' ) );
			// Remove the protocol
			$own_origin = preg_replace( '#((http|https|ftp|ftps)?\:?)#i', '', $own_origin );

			$external_origins = array();
			foreach ( $args['custom_srcs'] as $src ) {
				$origin = self::extract_origin_from_url( $src );
				if ( ! empty( $origin ) && false === strpos( $origin, $own_origin ) ) {
					$external_origins[] = $origin;
				}
			}

			$external_origins = array_unique( $external_origins );
			if ( ! empty( $external_origins ) ) {
				foreach ( $external_origins as $external_origin ) {
					echo '<link href="' . esc_url( $external_origin ) . '" rel="preconnect" crossorigin>';
				}
			}
		}
	}

	/**
	 * Add data to be available in JS.
	 *
	 * @since 2.7.0
	 *
	 * @param $localized
	 *
	 * @return mixed
	 */
	public function add_to_localized_data( $localized ) {
		$localized['theme_fonts'] = $this->get_theme_fonts();
		$localized['cloud_fonts'] = $this->get_cloud_fonts();
		$localized['std_fonts'] = $this->get_std_fonts();

		return $localized;
	}

	function script_to_add_customizer_output_into_wp_editor() {

		ob_start(); ?>
<script type="text/javascript" src="<?php echo plugins_url( 'js/vendor/webfontloader-1-6-28.js', PixCustomifyPlugin()->get_file() ); ?>"></script>
<?php
	$fonts_dynamic_script = $this->get_fonts_dynamic_script();
	if ( ! empty( $fonts_dynamic_script ) ) {
		?>
<script type="text/javascript"><?php echo $fonts_dynamic_script ?></script>
		<?php
	}

	$this->output_fonts_dynamic_style();

	$typography_dynamic_script = $this->get_typography_dynamic_script();
	if ( ! empty( $typography_dynamic_script ) ) {
		?>
<script type="text/javascript"><?php echo $typography_dynamic_script ?></script>
		<?php
	}

	$this->output_typography_dynamic_style();

	$custom_output = ob_get_clean();

		ob_start(); ?>
(function ($) {
	$(window).on('load', function () {
		/**
		 * @param iframe_id the id of the frame you want to append the style
		 * @param style_element the style element you want to append
		 */
		var append_script_to_iframe = function (ifrm_id, scriptEl) {
			var myIframe = document.getElementById(ifrm_id);

			var script = myIframe.contentWindow.document.createElement("script");
			script.type = "text/javascript";
			if (scriptEl.getAttribute("src")) { script.src = scriptEl.getAttribute("src"); }
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

		var xmlString = <?php echo json_encode( str_replace( "\n", "", $custom_output ) ); ?>,
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
})(jQuery);<?php
		$script = ob_get_clean();
		wp_add_inline_script( 'editor', $script );
	}

	/**
	 * This is only useful for older Typography fields, not the new Font fields.
	 * @deprecated
	 */
	function output_typography_dynamic_style() {
		$style = $this->get_typography_dynamic_style();

		if ( ! empty( $style ) ) { ?>
<style id="customify_typography_output_style">
	<?php echo $style; ?>
</style>
		<?php }
	}

	/**
	 * This is only useful for older Typography fields, not the new Font fields.
	 * @deprecated
	 */
	function get_typography_dynamic_style() {
		$output = '';

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		$typography_fields = array();
		$local_plugin->customizer->get_fields_by_key( $local_plugin->get_options_details( true ), 'type', 'typography', $typography_fields );

		if ( empty( $typography_fields ) ) {
			return $output;
		}

		ob_start();
		foreach ( $typography_fields as $font ) {
			$selector = apply_filters( 'customify_typography_css_selector', $this->cleanup_whitespace( $font['selector'] ), $font );

			if ( isset( $selector ) && ! empty( $font['value'] ) ) {

				$value = $this->standardize_font_values( $this->maybe_decode_value( $font['value'] ) );

				// In case the value is empty, try a default value if the $font['value'] is actually the font family.
				if ( empty( $value ) && is_string( $font['value'] ) ) {
					$value = $this->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
				}

				// Bail if empty or we don't have an array
				if ( empty( $value ) || ! is_array( $value ) ) {
					continue;
				}

				$selected_variant = '';
				if ( ! empty( $value['selected_variants'] ) ) {
					if ( is_array( $value['selected_variants'] ) ) {
						$selected_variant = $value['selected_variants'][0];
					} else {
						$selected_variant = $value['selected_variants'];
					}
				}

				// First handle the case where we have the font-family in the selected variant (usually this means a custom font from our Fonto plugin)
				if ( ! empty( $selected_variant ) && is_array( $selected_variant ) && ! empty( $selected_variant['font-family'] ) ) {
					// The variant's font-family
					echo $selector . " {\nfont-family: " . $selected_variant['font-family'] . ";\n";

					// If this is a custom font (like from our plugin Fonto) with individual styles & weights - i.e. the font-family says it all
					// we need to "force" the font-weight and font-style
					if ( ! empty( $value['type'] ) && 'custom_individual' == $value['type'] ) {
						$selected_variant['font-weight'] = '400 !important';
						$selected_variant['font-style'] = 'normal !important';
					}

					// Output the font weight, if available
					if ( ! empty( $selected_variant['font-weight'] ) ) {
						echo "font-weight: " . $selected_variant['font-weight'] . ";\n";
					}

					// Output the font style, if available
					if ( ! empty( $selected_variant['font-style'] ) ) {
						echo "font-style: " . $selected_variant['font-style'] . ";\n";
					}

					echo "}\n";
				} elseif ( isset( $value['font_family'] ) ) {
					// The selected font family
					echo $selector . " {\n font-family: " . $value['font_family'] . ";\n";

					if ( ! empty( $selected_variant ) ) {
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
							echo "font-weight: " . $weight_and_style . ";\n";
						}

						if ( $italic_font ) {
							echo "font-style: italic;\n";
						}
					}

					echo "}\n";
				}
			}
		}

		$output = ob_get_clean();

		return $output;
	}

	/**
	 * This is only useful for older Typography fields, not the new Font fields.
	 * @deprecated
	 */
	public function enqueue_typography_frontend_scripts() {
		$script = $this->get_typography_dynamic_script();
		if ( ! empty( $script ) ) {
			wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader' );
			wp_add_inline_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader', $script );
		} elseif ( is_customize_preview() ) {
			// If we are in the Customizer preview, we still need the Web Font Loader.
			wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader' );
		}
	}

	/**
	 * This is only useful for older Typography fields, not the new Font fields.
	 * @deprecated
	 */
	function get_typography_dynamic_script() {

		// If typography has been deactivated from the settings, bail.
		if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography', '1' )
		     || ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_google_fonts', 1 ) ) {
			return '';
		}

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		$typography_fields = array();
		$local_plugin->customizer->get_fields_by_key( $local_plugin->get_options_details( true ), 'type', 'typography', $typography_fields );

		if ( empty( $typography_fields ) ) {
			return '';
		}

		$output = '';
		$families = array();

		foreach ( $typography_fields as $id => $font ) {
			// Bail without a value.
			if ( empty( $font['value'] ) ) {
				continue;
			}

			$value = $this->standardize_font_values( $this->maybe_decode_value( $font['value'] ) );

			// In case the value is empty, try a default value if the $font['value'] is actually the font family.
			if ( empty( $value ) && is_string( $font['value'] ) ) {
				$value = $this->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
			}

			// Bail if we don't have a value or the value isn't an array
			if ( empty( $value ) || ! is_array( $value ) ) {
				continue;
			}

			// We can't do anything without a font family.
			if ( empty( $value['font_family'] ) ) {
				continue;
			}

			$font_type = self::determineFontType( $value['font_family'] );

			if ( $font_type == 'google' ) {
				$family =  $value['font_family'];

				if ( is_array( $value['variants'] ) ) {
					$family .= ":" . implode( ',', $value['variants'] );
				} elseif ( isset( $value['selected_variants'] ) && ! empty( $value['selected_variants'] ) ) {
					if ( is_array( $value['selected_variants'] ) ) {
						$family .= ":" . implode( ',', $value['selected_variants'] );
					} elseif ( is_string( $value['selected_variants'] ) || is_numeric( $value['selected_variants'] ) ) {
						$family .= ":" . $value['selected_variants'];
					}
				} elseif ( isset( $value['variants'] ) && ! empty( $value['variants'] ) ) {
					if ( is_array( $value['variants'] ) ) {
						$family .= ":" . implode( ',', $value['variants'] );
					} else {
						$family .= ":" . $value['variants'];
					}
				}

				if ( isset( $value['selected_subsets'] ) && ! empty( $value['selected_subsets'] ) ) {
					if ( is_array( $value['selected_subsets'] ) ) {
						$family .= ":" . implode( ',', $value['selected_subsets'] );
					} else {
						$family .= ":" . $value['selected_subsets'];
					}
				} elseif ( isset( $value['subsets'] ) && ! empty( $value['subsets'] ) ) {
					if ( is_array( $value['subsets'] ) ) {
						$family .= ":" . implode( ',', $value['subsets'] );
					} else {
						$family .= ":" . $value['subsets'];
					}
				}

				$families[] = "'" . $family . "'";
			}
		}

		if ( ! empty( $families ) ) {
			ob_start();
			?>
if (typeof WebFont !== 'undefined') {<?php // if there is a WebFont object, use it ?>
	WebFont.load({
		google: {families: [<?php echo join( ',', $families ); ?>]},
		classes: false,
		events: false
	});
}<?php
			$output = ob_get_clean();
		}

		return $output;
	}

	/**
	 * Load the google fonts list from the local file, if not already loaded.
	 *
	 * @return array
	 */
	protected function maybe_load_google_fonts() {

		if ( empty( $this->google_fonts ) ) {
			$fonts_path = PixCustomifyPlugin()->get_base_path() . 'includes/resources/google.fonts.php';

			if ( file_exists( $fonts_path ) ) {
				$this->google_fonts = apply_filters( 'customify_filter_google_fonts_list', require( $fonts_path ) );
			}
		}

		if ( ! empty( $this->google_fonts ) ) {
			return $this->google_fonts;
		}

		return array();
	}

	/** HELPERS */

	/**
	 * Cleanup stuff like tab characters.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function cleanup_whitespace( $string ) {

		return normalize_whitespace( $string );
	}

	/**
	 * Determine if a given array is associative.
	 *
	 * @param $array
	 *
	 * @return bool
	 */
	public static function isAssocArray( $array ) {
		return ( $array !== array_values( $array ) );
	}

	/**
	 * Given an URL, attempt to extract the origin (protocol + domain).
	 *
	 * @param string $url
	 *
	 * @return false|string False if the given string is not a proper URL, the origin otherwise.
	 */
	public static function extract_origin_from_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		$regex = '#((?:http|https|ftp|ftps)?\:?\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,})(\/\S*)?#i';
		preg_match( $regex, $url, $matches );

		if ( empty( $matches[1] ) ) {
			return false;
		}

		return $matches[1];
	}

	/**
	 * Given a string, treat it as a (comma separated by default) list and return the array with the items
	 *
	 * @param mixed $str
	 * @param string $delimiter Optional. The delimiter to user.
	 *
	 * @return array
	 */
	public static function maybeExplodeList( $str, $delimiter = ',' ) {
		// If by any chance we are given an array, just return it
		if ( is_array( $str ) ) {
			return $str;
		}

		// Anything else we coerce to a string
		if ( ! is_string( $str ) ) {
			$str = (string) $str;
		}

		// Make sure we trim it
		$str = trim( $str );

		// Bail on empty string
		if ( empty( $str ) ) {
			return array();
		}

		// Return the whole string as an element if the delimiter is missing
		if ( false === strpos( $str, $delimiter ) ) {
			return array( $str );
		}

		// Explode it and return it
		return explode( $delimiter, $str );
	}

	/**
	 * Given a selector standardize it to a list.
	 *
	 * @param mixed $selector
	 *
	 * @return array
	 */
	protected function standardizeFontSelector( $selector ) {
		$selector_list = [];

		// Attempt to split it by coma.
		$list = self::maybeExplodeList( $selector );

		// Make sure that we have an associative array with the key being the individual selector
		foreach ( $list as $key => $value ) {
			// This means a simple string selector.
			if ( is_numeric( $key ) && is_string( $value ) ) {
				$value = self::cleanup_whitespace( $value );
				$selector_list[ $value ] = [];
				continue;
			}

			// Treat the rest a having the selector in the key and a set of details in the value.
			$key = self::cleanup_whitespace( (string) $key );
			$selector_list[ $key ] = $value;
		}

		return $selector_list;
	}

	/**
	 * Massage an array containing values (values for subfields) of a `font` field type, into one consistent structure.
	 *
	 * Handle legacy entries.
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	public static function standardize_font_values( $values ) {

		if ( empty( $values ) || ! is_array( $values ) ) {
			return array();
		}

		// Handle special logic for when the $values array is not an associative array.
		if ( ! self::isAssocArray( $values ) ) {
			$values = self::standardize_non_associative_font_default( $values );
		}

		foreach ( $values as $key => $value ) {
			$new_key = $key;
			// First, all entries keys should use underscore not dash.
			if ( strpos( $new_key, '-' ) !== false ) {
				$new_key = str_replace( '-', '_', $new_key );
				$values[ $new_key ] = $value;
				unset( $values[ $key ] );
			}

			if ( 'font_family' === $new_key ) {
				// The font family may be a comma separated list like "Roboto, sans"
				// We will keep only the first item.
				if ( false !== strpos( $value, ',' ) ) {
					$value = trim( substr( $value, 0, strpos( $value, ',' ) ) );
				}

				// Make sure that the font family is free from " or '
				$value = trim( $value, "\"\'" );

				$values[ $new_key ] = $value;
			}

			// @todo This is very weird! We are only using a single font weight and use that to generate CSS,
			// not just to load font weights/variants via Web Font Loader. This key should actually be font_weight!!!
			// The variants are automatically loaded by Web Font Loader. There is no need to select them.
			if ( 'font_weight' === $new_key ) {
				$values[ 'selected_variants' ] = $values[ $new_key ];
				unset( $values[ $new_key ] );
			}
		}

		return $values;
	}

	/**
	 * Handle special logic for when the $value array is not an associative array.
	 *
	 * @param mixed $value
	 * @return array Return a new associative array with proper keys
	 */
	public static function standardize_non_associative_font_default( $value ) {
		// If the value provided is not array, simply return it
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$new_value = array();

		// The first entry is the font-family
		if ( empty( $new_value['font_family'] ) && isset( $value[0] ) ) {
			$new_value['font_family'] = $value[0];
		}

		// In case we don't have an associative array
		// The second entry is the variants
		if ( empty( $new_value['selected_variants'] ) && isset( $value[1] ) ) {
			$new_value['selected_variants'] = $value[1];
		}

		return $new_value;
	}

	/**
	 * Given a value we will standardize it to an array with 'value' and 'unit'.
	 *
	 * @param $value
	 * @param $field
	 * @param $font
	 *
	 * @return array
	 */
	public static function standardize_numerical_value( $value, $field, $font ) {
		$standard_value = array(
			'value' => false,
			'unit' => '',
		);

		if ( is_numeric( $value ) ) {
			$standard_value['value'] = $value;
			// Deduce the unit.
			$standard_value['unit'] = self::get_field_unit( $field, $font );
		} elseif ( is_array( $value ) ) {
			// The value may be an associative array or a numerical keyed one.
			if ( isset( $value['value'] ) ) {
				$standard_value['value'] = $value['value'];
			} elseif ( isset( $value[0] ) ) {
				$standard_value['value'] = $value[0];
			}

			if ( isset( $value['unit'] ) ) {
				$standard_value['unit'] = $value['unit'];
			} elseif ( isset( $value[1] ) ) {
				$standard_value['unit'] = $value[1];
			}
		} elseif ( is_string( $value ) ) {
			// We will get everything in front that is a valid part of a number (float including).
			preg_match( "/^([\d.\-+]+)/i", $value, $match );

			if ( ! empty( $match ) && isset( $match[0] ) ) {
				$standard_value['value'] = $match[0];
				$standard_value['unit'] = substr( $value, strlen( $match[0] ) );
			}
		}

		return $standard_value;
	}

	/**
	 * Given a property and a list of allowed properties, determine if it is allowed.
	 *
	 * @param string      $property
	 * @param false|array $allowedProperties
	 *
	 * @return bool
	 */
	protected function isCSSPropertyAllowed( $property, $allowedProperties = false ) {
		// Empty properties are not allowed.
		if ( empty( $property ) ) {
			return false;
		}

		// Everything is allowed if nothing is specified.
		if ( ! is_array( $allowedProperties ) ) {
			return true;
		}

		// If we have received an array, it may come in two flavors:
		// - non-associative: meaning that if a property is part of the list, it is allowed
		// - associative: with the key as the property and a value; if the value is not empty() then it is allowed.
		// Standardize the $allowed to a "property" => true or false format.
		$stdAllowedProperties = [];
		foreach ( $allowedProperties as $key => $value ) {
			// This means a simple string.
			if ( is_numeric( $key ) && is_string( $value ) ) {
				$stdAllowedProperties[ $value ] = true;
				continue;
			}

			$stdAllowedProperties[ $key ] = empty( $value ) ? false : true;
		}

		if ( empty( $stdAllowedProperties ) ) {
			return true;
		}

		return ! empty( $stdAllowedProperties[ $property ] );
	}

	public static function get_field_unit( $field, $font ) {

		if ( empty( $font['fields'][ $field ] ) ) {

			if ( 'line-height' === $field ){
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

	/**
	 * Determine a font type based on its font family.
	 *
	 * We will follow a stack in the following order: theme fonts, cloud fonts, standard fonts, Google fonts.
	 *
	 * @param string $fontFamily
	 *
	 * @return string The font type: google, theme_font, cloud_font, or std_font.
	 */
	public function determineFontType( $fontFamily ) {
		// The default is Google since it's the most forgiving.
		$fontType = 'google';

		if ( ! empty( $this->theme_fonts[ $fontFamily ] ) ) {
			$fontType = 'theme_font';
		} elseif ( ! empty( $this->cloud_fonts[ $fontFamily ] ) ) {
			$fontType = 'cloud_font';
		} else if ( ! empty( $this->std_fonts[ $fontFamily ] ) ) {
			$fontType = 'std_font';
		}

		return $fontType;
	}

	/**
	 * Will convert an array of CSS like variants into their FVD equivalents. Web Font Loader expects this format.
	 * @link https://github.com/typekit/fvd
	 *
	 * @param array $variants
	 * @return array
	 */
	public static function convert_font_variants_to_fvds( $variants ) {
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
	 * Attempt to JSON decode the provided value.
	 *
	 * @param mixed $value
	 *
	 * @return mixed|string
	 */
	protected function maybe_decode_value( $value ) {
		// If the value is already an array, nothing to do.
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( is_string( $value ) ) {
			$value = PixCustomifyPlugin::decodeURIComponent( $value );
			$value = wp_unslash( $value );
			$value = json_decode( $value, true );
		}

		return $value;
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
