<?php

class Customify_Fonts_Global {

	/**
	 * Instance of this class.
	 * @since    2.7.0
	 * @var      object
	 */
	protected static $_instance = null;

	/**
	 * The system fonts list.
	 * @since    2.8.0
	 * @var      array
	 */
	protected $system_fonts = [];

	/**
	 * The Google fonts list.
	 * @since    2.7.0
	 * @var      array
	 */
	protected $google_fonts = [];

	/**
	 * The theme defined fonts list.
	 * @since    2.7.0
	 * @var      array
	 */
	protected $theme_fonts = [];

	/**
	 * The cloud fonts list.
	 * @since    2.7.0
	 * @var      array
	 */
	protected $cloud_fonts = [];

	/**
	 * The third-party fonts list.
	 *
	 * These are fonts that can be populated by other plugins (like Fonto).
	 *
	 * @since    2.11.0
	 * @var      array
	 */
	protected $third_party_fonts = [];

	/**
	 * The font categories list.
	 * @since    2.8.0
	 * @var      array
	 */
	protected $categories = [];

	/**
	 * The precision to use when dealing with float values.
	 * @since    2.7.0
	 * @var      int
	 */
	public static $floatPrecision = 2;

	/**
	 * The font subfields that behave as ranges.
	 * @since    2.7.0
	 * @var      array
	 */
	public static $rangeFields = [
		'font-size',
		'line-height',
		'letter-spacing',
	];

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
		 * Grab font categories details.
		 * Used for determining fallback stacks, etc.
		 */
		$this->categories = apply_filters( 'customify_font_categories', [] );

		/*
		 * Gather all fonts, by type.
		 */

		$this->third_party_fonts = self::standardizeFontsList( apply_filters( 'customify_third_party_fonts', [] ) );
		// Add the fonts to selects of the Customizer controls.
		// Since these are quite advanced fonts, we will put them first since the user went through all that trouble for a reason.
		if ( ! empty( $this->third_party_fonts ) ) {
			add_action( 'customify_font_family_select_options', array(
				$this,
				'output_third_party_fonts_select_options_group'
			), 15, 2 );
		}

		if ( PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_cloud_fonts', 1 ) ) {
			$this->cloud_fonts = self::standardizeFontsList( apply_filters( 'customify_cloud_fonts', [] ) );
			// Add the fonts to selects of the Customizer controls.
			if ( ! empty( $this->cloud_fonts ) ) {
				add_action( 'customify_font_family_select_options', array(
					$this,
					'output_cloud_fonts_select_options_group'
				), 20, 2 );
			}
		}

		$this->theme_fonts = self::standardizeFontsList( apply_filters( 'customify_theme_fonts', [] ) );
		// Add the fonts to selects of the Customizer controls.
		if ( ! empty( $this->theme_fonts ) ) {
			add_action( 'customify_font_family_select_options', array(
				$this,
				'output_theme_fonts_select_options_group'
			), 30, 2 );
		}

		if ( PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_system_fonts', 1 ) ) {
			$this->system_fonts = self::standardizeFontsList( apply_filters( 'customify_system_fonts', [] ) );

			// Add the system fonts to selects of the Customizer controls.
			if ( ! empty( $this->system_fonts ) ) {
				add_action( 'customify_font_family_select_options', array(
					$this,
					'output_system_fonts_select_options_group'
				), 40, 2 );
			}
		}

		if ( PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_google_fonts', 1 ) ) {
			if ( ! empty( $this->maybe_load_google_fonts() ) ) {

				// Add the fonts to selects of the Customizer controls.
				// For Google fonts we will first output just an empty option group, and the rest of the options in a JS variable.
				// This way we don't hammer the DOM too much.
				add_action( 'customify_font_family_select_options', array(
					$this,
					'output_google_fonts_select_options_group'
				), 50, 2 );
				add_action( 'customize_controls_print_footer_scripts', array(
					$this,
					'customize_pane_settings_google_fonts_options'
				), 10000 );
			}
		}

		/*
		 * Output the frontend fonts specific scripts and styles.
		 */
		$load_location = PixCustomifyPlugin()->settings->get_plugin_setting( 'style_resources_location', 'wp_head' );
		// Add preconnect links as early as possible for faster external fonts loading.
		add_action('wp_head', array( $this, 'add_preconnect_links' ), 0);
		wp_register_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader',
			plugins_url( 'js/vendor/webfontloader-1-6-28.min.js', PixCustomifyPlugin()->get_file() ), [], null, ( 'wp_head' === $load_location ) ? false : true );
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts_styles' ), 0 );
		add_action( $load_location, array( $this, 'outputFontsDynamicStyle' ), 100 );

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

	public function standardize_font_fields_config( &$item, $key = false ) {
		// We are after fields configs, so not interested in entries that are not arrays.
		if ( ! is_array( $item ) ) {
			return;
		}

		// If we have a `typography` field configuration, we have work to do!!!
		// We will transform it into a equivalent font field (in behavior). Ha!
		// We will not duplicate the logic applied for font fields below.
		if ( isset( $item['type'] ) && 'typography' === $item['type'] ) {
			// Change the field type.
			// Now there is no going back :)
			$item['type'] = 'font';

			// If $item['load_all_weights'] is truthy then that means we allow for a font_variant to be selected,
			// but as far as loading the font files, we will load all variants.
			if ( ! empty( $item['load_all_weights'] ) ) {
				if ( empty( $item['fields'] ) ) {
					$item['fields'] = [];
				}
				$item['fields']['font-weight'] = [ 'load_all_variants' => true ];
				unset( $item['load_all_weights'] );
			}

			// The `variants` entry (consisting of a list of variants) really has no place at a field level.
			// This is information related to each (selected) font.
			if ( isset( $item['variants'] ) ) {
				unset( $item['variants'] );
			}
		}

		// If we have a `font` field configuration, we have work to do.
		if ( isset( $item['type'] ) && 'font' === $item['type'] ) {
			// Standardize the subfields config.
			if ( empty( $item['fields'] ) ) {
				$item['fields'] = [];
			}

			// Some legacy configs specify a couple of fields outside the `fields` entry. We must cleanup.
			if ( isset( $item['font_weight'] ) ) {
				$item['fields']['font-weight'] = $item['font_weight'];
				unset( $item['font_weight'] );
			}
			if ( isset( $item['subsets'] ) ) {
				$item['fields']['subsets'] = $item['subsets'];
				unset( $item['subsets'] );
			}

			// All subfields entries should use dashes not underscores in their keys.
			foreach ( $item['fields'] as $field_type => $value ) {
				if ( strpos( $field_type, '_' ) !== false ) {
					$new_field_type                    = str_replace( '_', '-', $field_type );
					$item['fields'][ $new_field_type ] = $value;
					unset( $item['fields'][ $field_type ] );
				}
			}

			$subfieldsConfig = apply_filters( 'customify_default_font_subfields_config', array(
				'font-family'     => true,
				'font-weight'     => true, // This is actually for the font-variant field (weight and maybe style)
				'font-size'       => false,
				'line-height'     => false,
				'letter-spacing'  => false,
				'text-align'      => false,
				'text-transform'  => false,
				'text-decoration' => false,
			), $item, $key );

			// If we have received a fields configuration, merge it with the default.
			$subfieldsConfig = wp_parse_args( $item['fields'], $subfieldsConfig );

			// Standardize the fields that are ranges.
			foreach ( self::$rangeFields as $rangeField ) {
				if ( isset( $subfieldsConfig[ $rangeField ] ) && false !== $subfieldsConfig[ $rangeField ] ) {
					$subfieldsConfig[ $rangeField ] = self::standardizeRangeFieldAttributes( $subfieldsConfig[ $rangeField ] );
				}
			}

			// Finally save the new subfields config.
			$item['fields'] = $subfieldsConfig;

			// We want to standardize the selector(s), if present.
			if ( ! empty( $item['selector'] ) ) {
				$item['selector'] = self::standardizeFontSelector( $item['selector'] );
			}

			// We want to standardize the default value, if present.
			if ( ! empty( $item['default'] ) ) {
				$item['default'] = self::standardizeFontValue( $item['default'], $item );
			}

			// We have no reason to go recursively further when we have come across a `font` field configuration.
			return;
		}

		foreach ( $item as $key => $subitem ) {
			// We can't use $subitem since that is a copy, and we need to reference the original.
			$this->standardize_font_fields_config( $item[ $key ], $key );
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

		return [];
	}

	public function get_system_fonts() {
		if ( empty( $this->system_fonts ) ) {
			return [];
		}

		return $this->system_fonts;
	}

	public function get_google_fonts() {
		if ( empty( $this->google_fonts ) ) {
			return [];
		}

		return $this->google_fonts;
	}

	public function get_theme_fonts() {
		if ( empty( $this->theme_fonts ) ) {
			return [];
		}

		return $this->theme_fonts;
	}

	public function get_cloud_fonts() {
		if ( empty( $this->cloud_fonts ) ) {
			return [];
		}

		return $this->cloud_fonts;
	}

	public function get_third_party_fonts() {
		if ( empty( $this->third_party_fonts ) ) {
			return [];
		}

		return $this->third_party_fonts;
	}

	public function get_categories() {
		if ( empty( $this->categories ) ) {
			return [];
		}

		return $this->categories;
	}

	public function getFontDetails( $font_family, $font_type = false ) {
		if ( empty( $font_type ) ) {
			// We will determine the font type based on font family.
			$font_type = $this->determineFontType( $font_family );
		}

		switch ( $font_type ) {
			case 'theme_font':
				return $this->theme_fonts[ $font_family ];
				break;
			case 'cloud_font':
				return $this->cloud_fonts[ $font_family ];
				break;
			case 'google_font':
				return $this->google_fonts[ $font_family ];
				break;
			case 'system_font':
				if ( isset( $this->system_fonts[ $font_family ] ) ) {
					return $this->system_fonts[ $font_family ];
				}
				break;
			case 'third_party_font':
				if ( isset( $this->third_party_fonts[ $font_family ] ) ) {
					return $this->third_party_fonts[ $font_family ];
				}
				break;
			default:
				return false;
				break;
		}

		return false;
	}

	function output_third_party_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_third_party_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->third_party_fonts ) ) {
			$group_label = apply_filters( 'customify_third_party_font_group_label', esc_html__( 'Third-Party Fonts', '__plugin_txtd' ), $active_font_family, $current_value );
			echo '<optgroup label="' . esc_attr( $group_label ) . '">';
			foreach ( $this->get_third_party_fonts() as $font ) {
				if ( ! empty( $font['family'] ) ) {
					// Display the select option's HTML.
					Pix_Customize_Font_Control::output_font_family_option( $font['family'], $active_font_family );
				}
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_third_party_fonts_options', $active_font_family, $current_value );
	}

	function output_cloud_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_cloud_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->cloud_fonts ) ) {
			echo '<optgroup label="' . esc_html__( 'Cloud Fonts', 'customify' ) . '">';
			foreach ( $this->get_cloud_fonts() as $font ) {
				if ( ! empty( $font['family'] ) ) {
					// Display the select option's HTML.
					Pix_Customize_Font_Control::output_font_family_option( $font['family'], $active_font_family );
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
				if ( ! empty( $font['family'] ) ) {
					// Display the select option's HTML.
					Pix_Customize_Font_Control::output_font_family_option( $font['family'], $active_font_family );
				}
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_theme_fonts_options', $active_font_family, $current_value );
	}

	function output_system_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_system_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->system_fonts ) ) {

			echo '<optgroup label="' . esc_attr__( 'System fonts', 'customify' ) . '">';
			foreach ( $this->get_system_fonts() as $font ) {
				if ( ! empty( $font['family'] ) ) {
					// Display the select option's HTML.
					Pix_Customize_Font_Control::output_font_family_option( $font['family'], $active_font_family );
				}
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_system_fonts_options', $active_font_family, $current_value );
	}

	function output_google_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_google_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->google_fonts ) ) {
			// The actual options in this optiongroup will be injected via JS from the output of
			// see@ Customify_Fonts_Global::customize_pane_settings_google_fonts_options()
			echo '<optgroup class="google-fonts-opts-placeholder" label="' . esc_attr__( 'Google fonts', 'customify' ) . '"></optgroup>';
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_google_fonts_options', $active_font_family, $current_value );
	}

	public function customize_pane_settings_google_fonts_options() {
		if ( empty( $this->google_fonts ) ) {
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
				wp_json_encode( $this->getGoogleFontsSelectOptions() )
			);
			echo "})( _wpCustomizeSettings );\n";
			?>
		</script>
		<?php
	}

	protected function getGoogleFontsSelectOptions() {

		if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_google_fonts' ) || empty( $this->google_fonts ) ) {
			return '';
		}

		ob_start();
		if ( PixCustomifyPlugin()->settings->get_plugin_setting( 'typography_group_google_fonts', 1 ) ) {

			$grouped_google_fonts = [];
			foreach ( $this->get_google_fonts() as $font_details ) {
				if ( isset( $font_details['category'] ) ) {
					$grouped_google_fonts[ $font_details['category'] ][] = $font_details;
				} else {
					$grouped_google_fonts['uncategorized'][] = $font_details;
				}
			}

			foreach ( $grouped_google_fonts as $group_name => $group_fonts ) {
				/* translators: %s: The font category name */
				echo '<optgroup label="' . sprintf( esc_attr__( 'Google fonts %s', 'customify' ), $group_name ) . '">';
				foreach ( $group_fonts as $font_details ) {
					Pix_Customize_Font_Control::output_font_family_option( $font_details['family'] );
				}
				echo "</optgroup>";
			}

		} else {
			echo '<optgroup label="' . esc_attr__( 'Google fonts', 'customify' ) . '">';
			foreach ( $this->get_google_fonts() as $font_details ) {
				Pix_Customize_Font_Control::output_font_family_option( $font_details['family'] );
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
	public function getFontFamiliesDetailsForWebfontloader() {

		$args = array(
			'google_families' => [],
			'custom_families'  => [],
			'custom_srcs'      => [],
		);

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		$font_fields = [];
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

			$value = $this->standardizeFontValue( self::maybeDecodeValue( $font['value'] ), $font );

			// In case the value is empty, try a default value if the $font['value'] is actually the font family.
			if ( empty( $value ) && is_string( $font['value'] ) ) {
				$value = $this->getFontDefaultsValue( str_replace( '"', '', $font['value'] ) );
			}

			// Bail if we don't have a value or the value isn't an array
			if ( empty( $value ) || ! is_array( $value ) ) {
				continue;
			}

			// We can't do anything without a font family.
			if ( empty( $value['font_family'] ) ) {
				continue;
			}
			$font_family = $value['font_family'];

			$font_type = $this->determineFontType( $value['font_family'] );
			// If this is a standard font, we have nothing to do.
			if ( 'system_font' === $font_type ) {
				continue;
			}

			$font_details = $this->getFontDetails( $value['font_family'], $font_type );

			if ( 'google_font' !== $font_type ) {
				// If there is a selected font variant and we haven't been instructed to load all, load only that,
				// otherwise load all the available variants.
				if ( ! empty( $value['font_variant'] ) && empty( $font['fields']['font-weight']['loadAllVariants'] ) ) {
					$font_family .= ":" . join( ',', self::convertFontVariantsToFvds( $value['font_variant'] ) );
				} elseif ( ! empty( $font_details['variants'] ) ) {
					$font_family .= ':' . join( ',', self::convertFontVariantsToFvds( $font_details['variants'] ) );
				}
				$args['custom_families'][] = "'" . $font_family . "'";
				if ( ! empty( $font_details['src'] ) ) {
					$args['custom_srcs'][] = "'" . $font_details['src'] . "'";
				}
				continue;
			}

			// This is a Google font (if we've reached thus far).
			// If there is a selected font variant and we haven't been instructed to load all, load only that,
			// otherwise load all the available variants.
			if ( ! empty( $value['font_variant'] ) && empty( $font['fields']['font-weight']['loadAllVariants'] ) ) {
				// We need to make sure that we don't load a non-existent variant.
				// In that case we will load all available variants.
				if ( ! empty( $font_details['variants'] ) && is_array( $font_details['variants'] ) && ! in_array( $value['font_variant'], $font_details['variants'] ) ) {
					$font_family .= ":" . self::maybeImplodeList( $font_details['variants'] );
				} else {
					$font_family .= ":" . self::maybeImplodeList( $value['font_variant'] );
				}
			} elseif ( ! empty( $font_details['variants'] ) ) {
				$font_family .= ":" . self::maybeImplodeList( $font_details['variants'] );
			}

			$args['google_families'][] = "'" . $font_family . "'";
		}

		$args = array(
			'google_families' => array_unique( $args['google_families'] ),
			'custom_families' => array_unique( $args['custom_families'] ),
			'custom_srcs'     => array_unique( $args['custom_srcs'] ),
		);

		return $args;
	}

	/**
	 * Gather all the needed web fonts stylesheet URLs (the stylesheets contain the @font-face definition).
	 *
	 * @return array
	 */
	public function getFontsStylesheetUrls() {

		$urls = [];

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		$font_fields = [];
		$local_plugin->customizer->get_fields_by_key( $local_plugin->get_options_details(), 'type', 'font', $font_fields );

		if ( empty( $font_fields ) ) {
			return $urls;
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

		// We will gather Google Fonts and make a single request to the Google Fonts API.
		$google_fonts = [];

		foreach ( $font_fields as $id => $font ) {
			// Bail if this is an excluded field.
			if ( in_array( $id, $excluded_fields ) ) {
				continue;
			}

			// Bail without a value.
			if ( empty( $font['value'] ) ) {
				continue;
			}

			$value = $this->standardizeFontValue( self::maybeDecodeValue( $font['value'] ), $font );

			// In case the value is empty, try a default value if the $font['value'] is actually the font family.
			if ( empty( $value ) && is_string( $font['value'] ) ) {
				$value = $this->getFontDefaultsValue( str_replace( '"', '', $font['value'] ) );
			}

			// Bail if we don't have a value or the value isn't an array
			if ( empty( $value ) || ! is_array( $value ) ) {
				continue;
			}

			// We can't do anything without a font family.
			if ( empty( $value['font_family'] ) ) {
				continue;
			}
			$font_family = $value['font_family'];

			$font_type = $this->determineFontType( $value['font_family'] );
			// If this is a standard font, we have nothing to do.
			if ( 'system_font' === $font_type ) {
				continue;
			}

			$font_details = $this->getFontDetails( $value['font_family'], $font_type );

			if ( 'google_font' !== $font_type ) {
				// When a src is given, we have nothing to do.
				if ( ! empty( $font_details['src'] ) ) {
					$urls[] = $font_details['src'];
				}
				continue;
			}

			// This is a Google font (if we've reached thus far).
			// We request all the available variants.
			if ( ! empty( $font_details['variants'] ) ) {
				$font_family .= ':' . self::convertFontVariantsToGoogleFontsCSS2Styles( $font_details['variants'] );
			}

			$google_fonts[] = $font_family;
		}

		if ( ! empty( $google_fonts ) ) {
			// Avoid duplicate entries.
			$google_fonts = array_unique( $google_fonts );

			$google_url = 'https://fonts.googleapis.com/css2';
			// Add `family=` to each font family.
			$google_fonts = array_map( function( $font_family ) {
				return 'family=' . $font_family;
			}, $google_fonts );
			// We can't use add_query_arg() because it will not allow for multiple `family` args like Google Fonts expects.
			$google_url .= '?' . join('&', $google_fonts );

			// Request @font-face stylesheets with font-display: swap;
			$google_url .= '&display=swap';

			$urls[] = $google_url;
		}

		return $urls;
	}

	/**
	 *
	 * @param string $font_family
	 *
	 * @return array
	 */
	public function getFontDefaultsValue( $font_family ) {
		if ( empty( $font_family ) ) {
			return [];
		}

		return array(
			'type' => $this->determineFontType( $font_family ),
			'font_family' => $font_family
		);
	}

	function outputFontsDynamicStyle() {

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		$font_fields = [];
		$local_plugin->customizer->get_fields_by_key( $local_plugin->get_options_details(), 'type', 'font', $font_fields );

		if ( empty( $font_fields ) ) {
			return;
		}

		$output = '';

		foreach ( $font_fields as $key => $font ) {
			$font_output = $this->getFontStyle( $font );
			// If no output do not print anything, except if we are in the Customizer preview.
			// In the Customizer preview we need the empty <style> since we target it by id.
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

	function getFontsDynamicStyle() {

		$output = '';

		/** @var PixCustomifyPlugin $local_plugin */
		$local_plugin = PixCustomifyPlugin();

		$font_fields = [];
		$local_plugin->customizer->get_fields_by_key( $local_plugin->get_options_details(), 'type', 'font', $font_fields );

		if ( empty( $font_fields ) ) {
			return $output;
		}

		foreach ( $font_fields as $key => $font ) {

			$font_output = $this->getFontStyle( $font );
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
	 * @param array $fontConfig
	 *
	 * @return string The CSS rules.
	 */
	protected function getFontStyle( $fontConfig ) {

		if ( ! isset( $fontConfig['selector'] ) || ! isset( $fontConfig['value'] ) ) {
			return '';
		}

		$value = $this->standardizeFontValue( self::maybeDecodeValue( $fontConfig['value'] ), $fontConfig );

		// In case the value is empty, try a default value if the $font['value'] is actually the font family.
		if ( empty( $value ) && is_string( $fontConfig['value'] ) ) {
			$value = $this->getFontDefaultsValue( str_replace( '"', '', $fontConfig['value'] ) );
		}

		$cssValue = $this->getCSSValue( $value, $fontConfig );
		// Make sure we are dealing with a selector as a list of individual selector,
		// maybe some of them having special details like supported properties.
		$cssSelectors = apply_filters( 'customify_font_css_selector', self::standardizeFontSelector( $fontConfig['selector'] ), $fontConfig );

		// In case we receive a callback, its output will be the final result.
		if ( isset( $fontConfig['callback'] ) && is_callable( $fontConfig['callback'] ) ) {
			// The callbacks expect a string selector right now, not a standardized list.
			// @todo Maybe migrate all callbacks to the new standardized data and remove all this.
			$plainSelectors = [];
			foreach ( $cssSelectors as $selector => $details ) {
				$plainSelectors[] = $selector;
			}
			$fontConfig['selector'] = join( ', ', $plainSelectors );

			// Also, "kill" all fields unit since we pass final CSS values.
			// Except font-size that can be used in Typeline calculations,
			// and letter-spacing that always enforces em if given an empty unit (we will leave it's unit in the field config).
			if ( ! empty( $cssValue['font-size'] ) ) {
				$font_size = self::standardizeNumericalValue( $value['font_size'], 'font-size', $fontConfig );
				$cssValue['font-size'] = $font_size['value'];
			}
			if ( ! empty( $cssValue['letter-spacing'] ) ) {
				$letter_spacing = self::standardizeNumericalValue( $value['letter_spacing'], 'letter-spacing', $fontConfig );
				$cssValue['letter-spacing'] = $letter_spacing['value'];
			}
			foreach ( $fontConfig['fields'] as $fieldKey => $fieldValue ) {
				if ( isset( $fieldValue['unit'] ) && $fieldKey !== 'font-size' && $fieldKey !== 'letter-spacing' ) {
					$fontConfig['fields'][ $fieldKey ]['unit'] = false;
				}
			}

			// font-variant

			// Callbacks want the value keys with underscores, not dashes.
			// We will provide them in both versions for a smoother transition.
			foreach ( $cssValue as $property => $propertyValue ) {
				$new_key = $property;
				if ( strpos( $new_key, '-' ) !== false ) {
					$new_key = str_replace( '-', '_', $new_key );
					$cssValue[ $new_key ] = $propertyValue;
				}
			}

			return call_user_func( $fontConfig['callback'], $cssValue, $fontConfig );
		}

		if ( empty( $cssSelectors ) ) {
			return '';
		}

		$propertiesPrefix = '';
		if ( ! empty ( $fontConfig['properties_prefix'] ) ) {
			$propertiesPrefix = $fontConfig['properties_prefix'];
		}

		// The general CSS allowed properties.
		$subFieldsCSSAllowedProperties = $this->extractAllowedCSSPropertiesFromFontFields( $fontConfig['fields'] );

		// Since we might have simple CSS selectors and complex ones (with special details),
		// for cleanliness we will group the simple ones under a single CSS rule,
		// and output individual CSS rules for complex ones.
		// Right now, for complex CSS selectors we are only interested in the `properties` sub-entry.
		$simple_css_selectors = [];
		$complex_css_selectors = [];
		foreach ( $cssSelectors as $selector => $details ) {
			if ( empty( $details['properties'] ) ) {
				// This is a simple selector.
				$simple_css_selectors[] = $selector;
			} else {
				$complex_css_selectors[ $selector ] = $details;
			}
		}

		$output = '';

		if ( ! empty( $simple_css_selectors ) ) {
			$output .= "\n" . join(', ', $simple_css_selectors ) . " {" . "\n";
			$output .= $this->getCSSProperties( $cssValue, $subFieldsCSSAllowedProperties, $propertiesPrefix );
			$output .= "}\n";
		}

		if ( ! empty( $complex_css_selectors ) ) {
			foreach ( $complex_css_selectors as $selector => $details ) {
				$output .= "\n" . $selector . " {" . "\n";
				$output .= $this->getCSSProperties( $cssValue, $details['properties'], $propertiesPrefix );
				$output .= "}\n";
			}
		}

		return $output;
	}

	/**
	 * Return a list with all the properties values corresponding to a given font value.
	 *
	 * The list has the keys as CSS properties (using dashes, not underscores; the received value uses underscores, not dashes).
	 *
	 * @param array $value
	 * @param array $fontConfig
	 *
	 * @return array
	 */
	protected function getCSSValue( $value, $fontConfig ) {
		$cssValue = [];

		if ( ! empty( $value['font_family'] ) && ! self::isFalsy( $value['font_family'] ) ) {
			$cssValue['font-family'] = $value['font_family'];
			// "Expand" the font family by appending the fallback stack, if any is available.
			// But only do this, if the value is not already a font stack!
			if ( false === strpos( $cssValue['font-family'], ',' ) ) {
				$fallbackStack           = $this->getFontFamilyFallbackStack( $cssValue['font-family'] );
				if ( ! empty( $fallbackStack ) ) {
					$cssValue['font-family'] .= ',' . $fallbackStack;
				}
			}

			$cssValue['font-family'] = self::sanitizeFontFamilyCSSValue( $cssValue['font-family'] );
		}

		// If this is a custom font (like from our plugin Fonto) with individual styles & weights - i.e. the font-family says it all
		// We need to "force" the font-weight and font-style
		if ( ! empty( $value['type'] ) && 'custom_individual' == $value['type'] ) {
			$cssValue['font-weight'] = '400 !important';
			$cssValue['font-style']  = 'normal !important';
		}

		// Handle the case where we have the font_family in the font_variant (usually this means a custom font from our Fonto plugin)
		if ( ! empty( $value['font_variant'] ) && is_array( $value['font_variant'] ) ) {
			// Standardize as value
			$complexVariant = self::standardizeFontValue( $value['font_variant'] );
			// Merge with the received value.
			$value = array_merge( $value, $complexVariant );
			// empty the font_variant going forward.
			unset( $value['font_variant'] );
		}

		// Split the font_variant into font_weight and font_style, it that is the case.
		if ( ! empty( $value['font_variant'] ) && ! self::isFalsy( $value['font_variant'] ) ) {
			// Standardize it.
			$font_variant = self::standardizeFontVariant( $value['font_variant'] );

			if ( strpos( $font_variant, 'italic' ) !== false ) {
				$font_variant        = str_replace( 'italic', '', $font_variant );
				$cssValue['font-style'] = 'italic';
			} elseif ( strpos( $font_variant, 'oblique' ) !== false ) {
				$font_variant        = str_replace( 'oblique', '', $font_variant );
				$cssValue['font-style'] = 'oblique';
			}

			// If we have a remainder like '400', use it as font weight.
			if ( ! empty( $font_variant ) ) {
				$cssValue['font-weight'] = $font_variant;
			}
		}

		if ( ! empty( $value['font_size'] ) && ! self::isFalsy( $value['font_size'] ) ) {
			$font_size = self::standardizeNumericalValue( $value['font_size'], 'font-size', $fontConfig );
			if ( false !== $font_size['value'] ) {
				$cssValue['font-size'] = $font_size['value'];

				// If we use ems or rems, and the value is larger than 9, then something must be wrong; we will use pixels.
				if ( $font_size['value'] >= 9 && in_array( $font_size['unit'], array( 'em', 'rem' ) ) ) {
					$font_size['unit'] = 'px';
				}

				$cssValue['font-size'] .= $font_size['unit'];
			}
		}

		if ( ! empty( $value['letter_spacing'] ) && ! self::isFalsy( $value['letter_spacing'] ) ) {
			$letter_spacing = self::standardizeNumericalValue( $value['letter_spacing'], 'letter-spacing', $fontConfig );

			if ( false !== $letter_spacing['value'] ) {
				$cssValue['letter-spacing'] = $letter_spacing['value'] . $letter_spacing['unit'];
			}
		}

		if ( ! empty( $value['line_height'] ) && ! self::isFalsy( $value['line_height'] ) ) {
			$line_height = self::standardizeNumericalValue( $value['line_height'], 'line-height', $fontConfig );

			if ( false !== $line_height['value'] ) {
				$cssValue['line-height'] = $line_height['value'] . $line_height['unit'];
			}
		}

		if ( ! empty( $value['text_align'] ) && ! self::isFalsy( $value['text_align'] ) ) {
			$cssValue['text-align'] = $value['text_align'];
		}

		if ( ! empty( $value['text_transform'] ) && ! self::isFalsy( $value['text_transform'] ) ) {
			$cssValue['text-transform'] = $value['text_transform'];
		}

		if ( ! empty( $value['text_decoration'] ) && ! self::isFalsy( $value['text_decoration'] ) ) {
			$cssValue['text-decoration'] = $value['text_decoration'];
		}

		return $cssValue;
	}

	protected function getCSSProperties( $cssValue, $allowedProperties = false, $propertiesPrefix = '') {
		$output = '';

		if ( empty( $cssValue ) ) {
			return $output;
		}

		foreach ( $cssValue as $property => $propertyValue ) {
			// We don't want to output empty CSS rules.
			if ( self::isFalsy( $propertyValue ) ) {
				continue;
			}

			// If the property is not allowed, skip it.
			if ( ! $this->isCSSPropertyAllowed( $property, $allowedProperties ) ) {
				continue;
            }

			$output .= $propertiesPrefix . $property . ": " . $propertyValue . ";\n";
		}

		return $output;
	}

	protected function getFontFamilyFallbackStack( $fontFamily ) {
		$fallbackStack = '';

		$fontDetails = $this->getFontDetails( $fontFamily );
		if ( ! empty( $fontDetails['fallback_stack'] ) ) {
			$fallbackStack = $fontDetails['fallback_stack'];
		} elseif ( ! empty( $fontDetails['category'] ) ) {
			$category = $fontDetails['category'];
			// Search in the available categories for a match.
			if ( ! empty( $this->categories[ $category ] ) ) {
				// Matched by category ID/key
				$fallbackStack = ! empty( $this->categories[ $category ]['fallback_stack'] ) ? $this->categories[ $category ]['fallback_stack'] : '';
			} else {
				// We need to search for aliases.
				foreach ( $this->categories as $category_id => $category_details ) {
					if ( ! empty( $category_details['aliases'] ) ) {
						$aliases = self::maybeImplodeList( $category_details['aliases'] );
						if ( false !== strpos( $aliases, $category ) ) {
							// Found it.
							$fallbackStack = ! empty( $category_details['fallback_stack'] ) ? $category_details['fallback_stack'] : '';
							break;
						}
					}
				}
			}
		}

		return $fallbackStack;
	}

	public static function sanitizeFontFamilyCSSValue( $value ) {
		// Since we might get a stack, attempt to treat is a comma-delimited list.
		$fontFamilies = self::maybeExplodeList( $value );
		if ( empty( $fontFamilies ) ) {
			return '';
		}

		foreach ( $fontFamilies as $key => $fontFamily ) {
			// No whitespace at the back or the front.
			$fontFamily = trim( $fontFamily );
			// First, make sure that the font family is free from " or '
			$fontFamily = trim( $fontFamily, "\"\'\‘\’\“\”" );
			// No whitespace at the back or the front, again.
			$fontFamily = trim( $fontFamily );

			if ( '' === $fontFamily ) {
				unset( $fontFamilies[ $key ] );
				continue;
			}

			// Now, if the font family contains spaces, wrap it in ".
			if ( false !== strpos( $fontFamily, ' ' ) ) {
				$fontFamily = '"' . $fontFamily . '"';
			}

			// Finally, put it back.
			$fontFamilies[ $key ] = $fontFamily;
		}

		// Make sure that we have no duplicates.
		$fontFamilies = array_unique( $fontFamilies );

		return self::maybeImplodeList( $fontFamilies, ', ' );
	}

	public static function isFalsy( $value ) {
		return in_array( $value, [ '', 'false', false, ], true );
	}

	/**
	 * Output and enqueue the scripts needed to handle web fonts loading on the frontend (including the Customizer preview).
	 */
	public function enqueue_frontend_scripts_styles() {
		// If we are in the Customizer preview, we will always use the WebFontLoader.
		if ( is_customize_preview() ) {
			// We always enqueue the WebFontLoader script.
			wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader' );

			// Get the inline script to load all the needed fonts via WebFontLoader.
			$script = $this->get_webfontloader_dynamic_script();
			if ( ! empty( $script ) ) {
				wp_add_inline_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader', $script );
			} else {
				$this->handleNoWebFontsEvents();
			}
		} else {
			// In the actual frontend of the site, we rely on more efficient techniques like the FontFace API
			// with fallback to FontFaceObserver library when the browser doesn't support the FontFace API.
			// So we enqueue directly the stylesheet URLs.

			$fontStylesheetUrls = $this->getFontsStylesheetUrls();
			if ( ! empty( $fontStylesheetUrls ) ) {
				foreach ( $fontStylesheetUrls as $key => $fontStylesheetUrl ) {
					wp_enqueue_style( 'customify-font-stylesheet-' . $key, $fontStylesheetUrl, [], null );
				}

				// Now we need to output the JavaScript logic for detecting the fonts loaded event, just like WebFontLoader does.
				add_action( 'wp_footer', function() { ?>
					<script>
						let customifyTriggerFontsLoadedEvents = function() {
							// Trigger the 'wf-active' event, just like Web Font Loader would do.
							window.dispatchEvent(new Event('wf-active'));
							// Add the 'wf-active' class on the html element, just like Web Font Loader would do.
							document.getElementsByTagName('html')[0].classList.add('wf-active');
						}

						// Try to use the modern FontFaceSet browser APIs.
						if ( typeof document.fonts !== 'undefined' && typeof document.fonts.ready !== 'undefined' ) {
							document.fonts.ready.then(customifyTriggerFontsLoadedEvents);
						} else {
							// Fallback to just waiting a little bit and then triggering the events for older browsers.
							window.addEventListener('load', function() {
								setTimeout( customifyTriggerFontsLoadedEvents, 300 );
							});
						}
					</script>
					<?php
				});
			} else {
				$this->handleNoWebFontsEvents();
			}
		}
	}

	protected function handleNoWebFontsEvents() {
		// If there are no web fonts to load, add a script to the footer, on window loaded,
		// to trigger the font loaded event and add the class to the html element.
		// This way the behavior is consistent.
		add_action( 'wp_footer', function() { ?>
			<script>
				window.addEventListener('load', function() {
					// Trigger the 'wf-active' event, just like Web Font Loader would do.
					window.dispatchEvent(new Event('wf-active'));
					// Add the 'wf-active' class on the html element, just like Web Font Loader would do.
					document.getElementsByTagName('html')[0].classList.add('wf-active');
				});
			</script>
			<?php
		});
	}

	function get_webfontloader_dynamic_script() {
		// If typography has been deactivated from the settings, bail.
		if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography', '1' ) ) {
			return '';
		}

		$args = $this->getFontFamiliesDetailsForWebfontloader();

		if ( empty ( $args['custom_families'] ) && empty ( $args['google_families'] ) ) {
			return '';
		}

		ob_start(); ?>
const customifyFontLoader = function() {
    const webfontargs = {
        classes: true,
        events: true,
		loading: function() {
			window.dispatchEvent(new Event('wf-loading'));
		},
		active: function() {
			window.dispatchEvent(new Event('wf-active'));
		},
		inactive: function() {
			window.dispatchEvent(new Event('wf-inactive'));
			// Since we rely on this event to show text, if [all] the webfonts have failed, we still want to let the browser handle it.
			// So we set the .wf-active class on the html element.
			document.getElementByTag('html')[0].classList.add('wf-active');
		}
    };
        <?php if ( ! empty( $args['google_families'] ) ) { ?>
    webfontargs.google = {
	        families: [<?php echo join( ',', $args['google_families'] ); ?>]
	    };
        <?php }
        $custom_families = [];
        $custom_urls = [];

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
	customifyFontLoader();
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

		$args = $this->getFontFamiliesDetailsForWebfontloader();
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
			$own_origin = self::extractOriginFromUrl( get_bloginfo( 'url' ) );
			if ( ! empty( $own_origin ) ) {
				// Remove the protocol
				$own_origin = preg_replace( '#((http|https|ftp|ftps)?\:?)#i', '', $own_origin );

				$external_origins = [];
				foreach ( $args['custom_srcs'] as $src ) {
					$origin = self::extractOriginFromUrl( $src );
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
		if ( empty( $localized['fonts'] ) ) {
			$localized['fonts'] = [];
		}

		$localized['fonts']['floatPrecision'] =self::$floatPrecision;

		$localized['fonts']['third_party_fonts'] = $this->get_third_party_fonts();
		$localized['fonts']['theme_fonts'] = $this->get_theme_fonts();
		$localized['fonts']['cloud_fonts'] = $this->get_cloud_fonts();
		$localized['fonts']['google_fonts'] = $this->get_google_fonts();
		$localized['fonts']['system_fonts'] = $this->get_system_fonts();
		$localized['fonts']['categories'] = $this->get_categories();

		if ( empty( $localized['l10n'] ) ) {
			$localized['l10n'] = [];
		}
		$localized['l10n']['fonts'] = array(
			'familyPlaceholderText' => esc_html__( 'Select a font family', 'customify' ),
			'variantAutoText' => esc_html__( 'Auto', 'customify' ),
		);

		return $localized;
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
				$this->google_fonts = self::standardizeFontsList( $this->google_fonts );
			}
		}

		if ( ! empty( $this->google_fonts ) ) {
			return $this->google_fonts;
		}

		return [];
	}

	/** HELPERS */

	/**
	 * Cleanup stuff like tab characters.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function cleanupWhitespace( $string ) {

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
		if ( ! is_array( $array ) ) {
			return false;
		}

		return ( $array !== array_values( $array ) );
	}

	/**
	 * Given an URL, attempt to extract the origin (protocol + domain).
	 *
	 * @param string $url
	 *
	 * @return false|string False if the given string is not a proper URL, the origin otherwise.
	 */
	public static function extractOriginFromUrl( $url ) {
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
			return [];
		}

		// Return the whole string as an element if the delimiter is missing
		if ( false === strpos( $str, $delimiter ) ) {
			return array( $str );
		}

		// Explode it and return it
		return explode( $delimiter, $str );
	}

	/**
	 * Given a value, attempt to implode it.
	 *
	 * @param mixed $value
	 * @param string $delimiter Optional. The delimiter to user.
	 *
	 * @return string
	 */
	public static function maybeImplodeList( $value, $delimiter = ',' ) {
		// If by any chance we are given a string, just return it
		if ( is_string( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			return implode( $delimiter, $value );
		}

		// For anything else (like objects) we return an empty string.
		return '';
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
			if ( is_numeric( $key ) && is_string( $value ) ) {
				// This means a simple string selector.
				$value = self::cleanupWhitespace( $value );
				$selector_list[ $value ] = [];
				continue;
			}

			// Treat the rest a having the selector in the key and a set of details in the value.
			$key = self::cleanupWhitespace( (string) $key );
			$selector_list[ $key ] = $value;
		}

		return $selector_list;
	}

	/**
	 * Massage an array containing the value (values for subfields) of a `font` field type, into one consistent structure.
	 *
	 * Handle legacy entries.
	 *
	 * @param array $value
	 * @param array $fontConfig Optional.
	 *
	 * @return array
	 */
	public static function standardizeFontValue( $value, $fontConfig = [] ) {
		if ( empty( $value ) ) {
			return [];
		}

		// If we are given a string, we will consider it a font-family definition
		if ( is_string( $value ) ) {
			$value = array( $value );
		}

		// The value may be a stdClass object.
		if ( is_object( $value ) ) {
			// This is a sure way to get multi-dimensional objects as array (converts deep).
			$value = json_decode(json_encode( $value ), true);
		}

		// If by this time we don't have an array, return an empty value.
		if ( ! is_array( $value ) ) {
			return [];
		}

		// Handle special logic for when the $values array is not an associative array.
		if ( ! self::isAssocArray( $value ) ) {
			$value = self::standardizeNonAssociativeFontValues( $value );
		}

		foreach ( $value as $entry => $entryValue ) {
			$newEntry = $entry;
			// First, all entries keys should use underscore not dash.
			if ( strpos( $newEntry, '-' ) !== false ) {
				$newEntry           = str_replace( '-', '_', $newEntry );
				$value[ $newEntry ] = $entryValue;
				unset( $value[ $entry ] );
			}

			if ( 'font_family' === $newEntry ) {
				// The font family may be a comma separated list like "Roboto, sans"
				// We will keep only the first item, but only if the resulting font can be found.
				// Otherwise we will leave the font stack as it is.

				if ( false !== strpos( $entryValue, ',' ) ) {
					$entryValue = trim( substr( $entryValue, 0, strpos( $entryValue, ',' ) ) );
				}
				// Make sure that the font family is free from " or '
				$entryValue = trim( $entryValue, "\"\'\‘\’\“\”" );
				// Search for the font.
				$fontDetails = Customify_Fonts_Global::instance()->getFontDetails( $entryValue );
				if ( false !== $fontDetails ) {
					$value[ $newEntry ] = $entryValue;
				}
			}

			// Standardize numerical fields.
			if ( in_array( $newEntry, ['font_size', 'line_height', 'letter_spacing'] ) ) {
				$value[ $newEntry ] = self::standardizeNumericalValue( $entryValue );
			}
		}

		// We no longer use the `selected_variants` key, but the proper one: `font_variant`.
		if ( isset( $value['selected_variants'] ) ) {
			if ( ! isset( $value['font_variant'] ) ) {
				$value['font_variant'] = $value['selected_variants'];
			}

			unset( $value['selected_variants'] );
		}

		// Convert 'font_weight' entry to 'font_variant'.
		if ( isset( $value['font_weight'] ) ) {
			if ( ! isset( $value['font_variant'] ) ) {
				$value['font_variant'] = $value['font_weight'];
			}

			unset( $value['font_weight'] );
		}

		// Make sure that we have a single value in font_variant.
		if ( ! empty( $value['font_variant'] ) && is_array( $value['font_variant'] ) && ! self::isAssocArray( $value['font_variant'] ) ) {
			$value['font_variant'] = reset( $value['font_variant'] );
		}

		// We no longer hold source font variants and subsets in the value.
		if ( isset( $value['variants'] ) ) {
			unset( $value['variants'] );
		}
		if ( isset( $value['subsets'] ) ) {
			unset( $value['subsets'] );
		}

		// Finally, we need to correlate the subfields values with the fact that they are allowed or not, just to be safe.
		if ( ! empty( $fontConfig['fields'] ) && is_array( $fontConfig['fields'] ) ) {
			foreach ( $fontConfig['fields'] as $field => $fieldDetails ) {
				if ( false === $fieldDetails ) {
					// Need to make sure that there is no entry in the value for this field, since it's disabled.
					// Fields configs use dashes, while the value uses underscores.
					$fieldValueKey = str_replace( '-', '_', $field );
					// We have a special case for the font-weight field; it corresponds to the font_variant value entry.
					if ( 'font_weight' === $fieldValueKey ) {
						$fieldValueKey = 'font_variant';
					}

					// Now remove the value entry, if present.
					if ( isset( $value[ $fieldValueKey ] ) ) {
						unset( $value[ $fieldValueKey ] );
					}
				}
			}
		}

		return apply_filters( 'customify_standardized_font_value', $value, $fontConfig );
	}

	/**
	 * Given a (source) fonts list (like Google fonts list), standardize it (e.g., make sure font variants use the 400 one instead of 'regular' or 'normal').
	 *
	 * @param array $fontList
	 *
	 * @return array|false
	 */
	public static function standardizeFontsList( $fontList ) {
		// Reject anything that is not an array.
		if ( ! is_array( $fontList ) ) {
			return false;
		}

		$newFontsList = [];

		// In case a font is missing any of these entries, these are the safe defaults.
		$defaultFontEntries = [
			'family' => null,
			'family_display' => null,
			'category' => 'other',
			'variants' => [ '400' ],
			'subsets'  => [ 'latin' ],
			'fallback_stack' => '',
		];

		foreach ( $fontList as $key => $font ) {
			$newFont = $font;
			if ( ! is_array( $newFont ) ) {
				$newFont = [];
			}

			if ( ! isset( $newFont['family'] ) && ! is_numeric( $key ) ) {
				$newFont['family'] = (string) $key;
			}

			if ( empty( $newFont['family'] ) ) {
				// We will skip this font if we couldn't get a font family.
				continue;
			}

			$newFont = wp_parse_args( $newFont, $defaultFontEntries );

			// Standardize the font family
			$newFont['family'] = self::standardizeSourceFontFamily( $newFont['family'], $newFont );
			// Standardize the font variants list.
			if ( ! is_bool( $newFont['variants'] ) && empty( $newFont['variants'] ) ) {
				$newFont['variants'] = ['400'];
			}
			$newFont['variants'] = self::standardizeSourceFontVariantsList( $newFont['variants'] );

			// Add the standardized font to the new list, keeping the relative order.
			// We want to have the font family as key for easy searching!
			$newFontsList += [ $newFont['family'] => $newFont ];
		}

		// Allow others to filter this.
		return apply_filters( 'customify_standardized_fonts_list', $newFontsList, $fontList );
	}

	/**
	 * @param string $fontFamily
	 * @param array $font
	 *
	 * @return string
	 */
	public static function standardizeSourceFontFamily( $fontFamily, $font ) {
		// Make sure that the font family is free from " or ', but only if it is missing commas (i.e., it is not a font stack).
		if ( false === strpos( $fontFamily, ',' ) ) {
			$fontFamily = trim( $fontFamily, "\"\'\‘\’\“\”" );
		}

		return $fontFamily;
	}

	/**
	 * @param array|string $variantsList
	 *
	 * @return array
	 */
	public static function standardizeSourceFontVariantsList( $variantsList ) {
		// Make sure we treat comma delimited strings as list.
		$variantsList = self::maybeExplodeList( $variantsList );

		if ( empty( $variantsList ) ) {
			return $variantsList;
		}

		foreach ( $variantsList as $key => $variant ) {
			$variantsList[ $key ] = self::standardizeFontVariant( $variant );
		}

		// Make sure the variants list is ordered ascending, by value.
		sort( $variantsList, SORT_STRING );

		return $variantsList;
	}

	public static function standardizeFontVariant( $variant ) {
		// We want all variants to be strings, since they are not numerical values (even if they may look like it).
		$variant = (string) $variant;

		// Lowercase it.
		$variant = strtolower($variant);

		switch ( $variant ) {
			case 'thin':
				$variant = '100';
				break;
			case 'light':
				$variant = '200';
				break;
			case 'regular':
			case 'normal':
				$variant = '400';
				break;
			case 'italic':
				$variant = '400italic';
				break;
			case 'medium':
				$variant = '500';
				break;
			case 'bold':
				$variant = '700';
				break;
			default:
				break;
		}

		return $variant;
	}

	/**
	 * Handle special logic for when the $value array is not an associative array.
	 *
	 * @param mixed $value
	 * @return array Return a new associative array with proper keys
	 */
	public static function standardizeNonAssociativeFontValues( $value ) {
		// If the value provided is not array or is already an associative array, simply return it
		if ( ! is_array( $value ) || self::isAssocArray( $value ) ) {
			return $value;
		}

		$new_value = [];

		// The first entry is the font-family
		if ( isset( $value[0] ) ) {
			$new_value['font_family'] = $value[0];
		}

		// The second entry is the variant.
		if ( isset( $value[1] ) ) {
			$new_value['font_variant'] = $value[1];
		}

		return $new_value;
	}

	/**
	 * Given a value we will standardize it to an array with 'value' and 'unit'.
	 *
	 * @param mixed $value
	 * @param string|false $field Optional. The subfield name (e.g. `font-size`).
	 * @param array|false $font Optional. The entire font field config.
	 *
	 * @return array
	 */
	public static function standardizeNumericalValue( $value, $field = false, $font = false ) {
		$standard_value = array(
			'value' => false,
			'unit' => false,
		);

		if ( self::isFalsy( $value ) ) {
			return $standard_value;
		}

		if ( is_numeric( $value ) ) {
			$standard_value['value'] = $value;
			// Deduce the unit.
			$standard_value['unit'] = self::getSubFieldUnit( $field, $font );
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
			} else {
				// If we could not extract anything useful we will trust the developer and leave it like that.
				$standard_value['value'] = $value;
			}
		}

		// Make sure that the value number is rounded to 2 decimals.
		if ( is_numeric( $standard_value['value'] ) ) {
			$standard_value['value'] = round( $standard_value['value'], self::$floatPrecision );
		}

		// Make sure that we convert all falsy unit values to the boolean false.
		if ( self::isFalsy( $standard_value['unit'] ) ) {
			$standard_value['unit'] = false;
		}

		return $standard_value;
	}

	public static function standardizeRangeFieldAttributes( $attributes ) {
		if ( false === $attributes ) {
			return $attributes;
		}

		if ( ! is_array( $attributes ) ) {
			return array(
				'min' => '',
				'max' => '',
				'step' => '',
				'unit' => '',
			);
		}

		// Make sure that if we have a numerical indexed array, we will convert it to an associative one.
		if ( ! self::isAssocArray( $attributes ) ) {
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
		$standardizedAllowedProperties = [];
		foreach ( $allowedProperties as $key => $value ) {
			// This means a simple string.
			if ( is_numeric( $key ) && is_string( $value ) ) {
				$standardizedAllowedProperties[ $value ] = true;
				continue;
			}

			$standardizedAllowedProperties[ $key ] = empty( $value ) ? false : true;
		}

		if ( empty( $standardizedAllowedProperties ) ) {
			return true;
		}

		return ! empty( $standardizedAllowedProperties[ $property ] );
	}

	/**
	 * Given a font subfields configuration determine a list of allowed properties.
	 *
	 * The returned list is in the format: `css-property-name`: true|false.
	 *
	 * @param array $subfields
	 *
	 * @return array
	 */
	public static function extractAllowedCSSPropertiesFromFontFields( $subfields ) {
		// Nothing is allowed by default.
		$allowedProperties = array(
			'font-family'     => false,
			'font-weight'     => false,
			'font-style'      => false,
			'font-size'       => false,
			'line-height'     => false,
			'letter-spacing'  => false,
			'text-align'      => false,
			'text-transform'  => false,
			'text-decoration' => false,
		);

		if ( empty( $subfields ) || ! is_array( $subfields ) ) {
			return $allowedProperties;
		}

		// We will match the subfield keys with the CSS properties, but only those that properties that are above.
		// Maybe at some point some more complex matching would be needed here.
		foreach ( $subfields as $key => $value ) {
			if ( isset( $allowedProperties[ $key ] ) ) {
				// Convert values to boolean.
				$allowedProperties[ $key ] = ! empty( $value );

				// For font-weight we want font-style to go the same way,
				// since these two are generated from the same subfield: font-weight (actually holding the font variant value).
				if ( 'font-weight' === $key ) {
					$allowedProperties[ 'font-style' ] = $allowedProperties[ $key ];
				}
			}
		}

		return $allowedProperties;
	}

	/**
	 * @param string $field
	 * @param array $font
	 *
	 * @return bool|string
	 */
	public static function getSubFieldUnit( $field, $font ) {
		if ( false === $field || false === $font ) {
			return false;
		}

		// If the field has no definition.
		if ( empty( $font['fields'][ $field ] ) ) {
			// These fields don't have an unit, by default.
			if ( in_array( $field, ['font-family', 'font-weight', 'font-style', 'line-height', 'text-align', 'text-transform', 'text-decoration'] ) ){
				return false;
			}

			// The rest of the subfields have pixels as default units.
			return 'px';
		}

		if ( isset( $font['fields'][ $field ]['unit'] ) ) {
			// Make sure that we convert all falsy unit values to the boolean false.
			return self::isFalsy( $font['fields'][ $field ]['unit'] ) ? false : $font['fields'][ $field ]['unit'];
		}

		if ( isset( $font['fields'][ $field ][3] ) ) {
			// Make sure that we convert all falsy unit values to the boolean false.
			return self::isFalsy( $font['fields'][ $field ][3] ) ? false : $font['fields'][ $field ][3];
		}

		return 'px';
	}

	/**
	 * Determine a font type based on its font family.
	 *
	 * We will follow a stack in the following order: third-party fonts, cloud fonts, theme fonts, Google fonts, system fonts.
	 *
	 * @param string $fontFamily
	 *
	 * @return string The font type: third_party_font, google_font, theme_font, cloud_font, or system_font.
	 */
	public function determineFontType( $fontFamily ) {
		// The default is a standard font (aka no special loading or processing).
		$fontType = 'system_font';

		if ( ! empty( $this->third_party_fonts[ $fontFamily ] ) ) {
			$fontType = 'third_party_font';
		} elseif ( ! empty( $this->cloud_fonts[ $fontFamily ] ) ) {
			$fontType = 'cloud_font';
		} elseif ( ! empty( $this->theme_fonts[ $fontFamily ] ) ) {
			$fontType = 'theme_font';
		} elseif ( ! empty( $this->google_fonts[ $fontFamily ] ) ) {
			$fontType = 'google_font';
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
	public static function convertFontVariantsToFvds( $variants ) {
		$fvds = [];
		if ( ! is_array( $variants ) || empty( $variants ) ) {
			return $fvds;
		}

		foreach ( $variants as $variant ) {
			// Make sure that we are working with strings.
			$variant = (string) $variant;

			// This is the default font style.
			$font_style = 'n'; // normal
			if ( false !== strrpos( $variant, 'italic'  ) ) {
				$font_style = 'i';
				$variant    = str_replace( 'italic', '', $variant );
			} elseif ( false !== strrpos( $variant, 'oblique' ) ) {
				$font_style = 'o';
				$variant    = str_replace( 'oblique', '', $variant );
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
				case '100':
					$font_weight = 1;
					break;
				case '200':
					$font_weight = 2;
					break;
				case '300':
					$font_weight = 3;
					break;
				case '500':
					$font_weight = 5;
					break;
				case '600':
					$font_weight = 6;
					break;
				case '700':
				case 'bold':
					$font_weight = 7;
					break;
				case '800':
					$font_weight = 8;
					break;
				case '900':
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
	 * Will convert an array of CSS like variants into the appropriate Google Fonts CSS 2 API format.
	 * @link https://developers.google.com/fonts/docs/css2
	 *
	 * @param array $variants
	 * @return string
	 */
	public static function convertFontVariantsToGoogleFontsCSS2Styles( $variants ) {
		$stylesString = '';
		if ( ! is_array( $variants ) || empty( $variants ) ) {
			return $stylesString;
		}

		$styleWeights = [
			'italic' => [],
			'normal' => [],
		];

		foreach ( $variants as $variant ) {
			// Make sure that we are working with strings.
			$variant = (string) $variant;

			// This is the default font style.
			$font_style = 'normal'; // normal
			if ( false !== strrpos( $variant, 'italic'  ) ) {
				$font_style = 'italic';
				$variant    = str_replace( 'italic', '', $variant );
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
				case '100':
					$font_weight = 100;
					break;
				case '200':
					$font_weight = 200;
					break;
				case '300':
					$font_weight = 300;
					break;
				case '500':
					$font_weight = 500;
					break;
				case '600':
					$font_weight = 600;
					break;
				case '700':
				case 'bold':
					$font_weight = 700;
					break;
				case '800':
					$font_weight = 800;
					break;
				case '900':
					$font_weight = 900;
					break;
				default:
					$font_weight = 400;
					break;
			}

			$styleWeights[ $font_style ][] = $font_weight;
		}

		// Now construct the string.

		// All supported weights, ordered numerically.
		$allWeights = [ 100, 200, 300, 400, 500, 600, 700, 800, 900 ];

		$axisTagsList = [];
		// We always have both `ital` and `wght` axis, for a clearer logic.
		$axisTagsList[] = 'ital';
		$axisTagsList[] = 'wght';

		$axisTuplesList = [];
		foreach ( $allWeights as $weight ) {
			// Go through all axes determine the tuple (e.g. italic 400 becomes 1,400; or 700 becomes 0,700)
			// The ital axis can only have the value 0 or 1.
			if ( false !== array_search( $weight, $styleWeights['normal'] ) ) {
				$axisTuplesList[] = '0,' . $weight;
			}
			if ( false !== array_search( $weight, $styleWeights['italic'] ) ) {
				$axisTuplesList[] = '1,' . $weight;
			}
		}

		if ( ! empty( $axisTuplesList ) ) {
			// We must make sure that the axis tags are ordered alphabetically.
			sort( $axisTagsList, SORT_STRING );
			// We also need to sort the tuples, numerically.
			sort( $axisTuplesList, SORT_STRING );

			$stylesString = join( ',', $axisTagsList ) . '@' . join( ';', $axisTuplesList );
		}

		return $stylesString;
	}

	/**
	 * Attempt to JSON decode the provided value.
	 *
	 * @param mixed $value
	 *
	 * @return mixed|string
	 */
	public static function maybeDecodeValue( $value ) {
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
	 * Attempt to JSON encode the provided value.
	 *
	 * @param mixed $value
	 *
	 * @return mixed|string
	 */
	public static function maybeEncodeValue( $value ) {
		// If the value is already a string, nothing to do.
		if ( is_string( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) || is_object( $value ) ) {
			$value = PixCustomifyPlugin::encodeURIComponent( json_encode( $value ) );
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
