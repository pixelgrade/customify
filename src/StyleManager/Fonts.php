<?php
/**
 * This is the class that handles the overall logic for fonts.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\StyleManager;

use Pixelgrade\Customify\Provider\Options;
use Pixelgrade\Customify\Provider\PluginSettings;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;
use \Pixelgrade\Customify\Utils\Fonts as FontsHelper;

/**
 * Provides the fonts logic.
 *
 * @since 3.0.0
 */
class Fonts extends AbstractHookProvider {

	/**
	 * The system fonts list.
	 * @since    3.0.0
	 * @var      array
	 */
	protected array $system_fonts = [];

	/**
	 * The Google fonts list.
	 * @since    3.0.0
	 * @var      array
	 */
	protected array $google_fonts = [];

	/**
	 * The theme defined fonts list.
	 * @since    3.0.0
	 * @var      array
	 */
	protected array $theme_fonts = [];

	/**
	 * The cloud fonts list.
	 * @since    3.0.0
	 * @var      array
	 */
	protected array $cloud_fonts = [];

	/**
	 * The font categories list.
	 * @since    3.0.0
	 * @var      array
	 */
	protected array $categories = [];

	/**
	 * The font subfields that behave as ranges.
	 * @since    3.0.0
	 * @var      array
	 */
	public static array $rangeFields = [
		'font-size',
		'line-height',
		'letter-spacing',
	];

	/**
	 * Options.
	 *
	 * @var Options
	 */
	protected Options $options;

	/**
	 * Plugin settings.
	 *
	 * @var PluginSettings
	 */
	protected PluginSettings $plugin_settings;

	/**
	 * Logger.
	 *
	 * @var LoggerInterface
	 */
	protected LoggerInterface $logger;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Options         $options         Options.
	 * @param PluginSettings  $plugin_settings Plugin settings.
	 * @param LoggerInterface $logger          Logger.
	 */
	public function __construct(
		Options $options,
		PluginSettings $plugin_settings,
		LoggerInterface $logger
	) {
		$this->options         = $options;
		$this->plugin_settings = $plugin_settings;
		$this->logger          = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		/*
		 * Standardize the customify_config for field types we can handle.
		 */
		add_filter( 'customify_final_config', [ $this, 'standardize_global_customify_config' ], 99999, 1 );

		// We will initialize the logic after the plugin has finished with it's configuration (at priority 15).
		$this->add_action( 'init', 'init', 20 );
	}

	/**
	 * Initialize this provider.
	 *
	 * @since 3.0.0
	 */
	protected function init() {

		/*
		 * Grab font categories details.
		 * Used for determining fallback stacks, etc.
		 */
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->categories = apply_filters( 'customify_font_categories', [] );

		/*
		 * Gather all fonts, by type.
		 */

		if ( $this->plugin_settings->get( 'typography_cloud_fonts', 'yes' ) ) {
			$this->cloud_fonts = FontsHelper::standardizeFontsList( apply_filters( 'customify_cloud_fonts', [] ) );
			// Add the fonts to selects of the Customizer controls.
			if ( ! empty( $this->cloud_fonts ) ) {
				add_action( 'customify_font_family_select_options', [
					$this,
					'output_cloud_fonts_select_options_group',
				], 20, 2 );
			}
		}

		$this->theme_fonts = FontsHelper::standardizeFontsList( apply_filters( 'customify_theme_fonts', [] ) );
		// Add the fonts to selects of the Customizer controls.
		if ( ! empty( $this->theme_fonts ) ) {
			add_action( 'customify_font_family_select_options', [
				$this,
				'output_theme_fonts_select_options_group',
			], 30, 2 );
		}

		if ( $this->plugin_settings->get( 'typography_system_fonts', 'yes' ) ) {
			$this->system_fonts = FontsHelper::standardizeFontsList( apply_filters( 'customify_system_fonts', [] ) );

			// Add the system fonts to selects of the Customizer controls.
			if ( ! empty( $this->system_fonts ) ) {
				add_action( 'customify_font_family_select_options', [
					$this,
					'output_system_fonts_select_options_group',
				], 40, 2 );
			}
		}

		if ( $this->plugin_settings->get( 'typography_google_fonts', 'yes' ) ) {
			if ( ! empty( $this->maybe_load_google_fonts() ) ) {

				// Add the fonts to selects of the Customizer controls.
				// For Google fonts we will first output just an empty option group, and the rest of the options in a JS variable.
				// This way we don't hammer the DOM too much.
				add_action( 'customify_font_family_select_options', [
					$this,
					'output_google_fonts_select_options_group',
				], 50, 2 );
				add_action( 'customize_controls_print_footer_scripts', [
					$this,
					'customize_pane_settings_google_fonts_options',
				], 10000 );
			}
		}

		/*
		 * Output the frontend fonts specific scripts and styles.
		 */
		$load_location = $this->plugin_settings->get( 'style_resources_location', 'wp_head' );
		// Add preconnect links as early as possible for faster external fonts loading.
		add_action( 'wp_head', [ $this, 'add_preconnect_links' ], 0 );
		wp_register_script( 'pixelgrade_customify-web-font-loader',
			$this->plugin->get_url( 'vendor_js/webfontloader-1-6-28.min.js' ), [], null, ! ( 'wp_head' === $load_location ) );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts_styles' ], 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_frontend_scripts_styles' ], 0 );
		add_action( $load_location, [ $this, 'outputFontsDynamicStyle' ], 100 );

		// Add data to be passed to JS.
		add_filter( 'customify_localized_js_settings', [ $this, 'add_to_localized_data' ], 10, 1 );
	}

	/**
	 * Go deep and identify all the fields we are interested in and standardize their entries.
	 *
	 * @since 3.0.0
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function standardize_global_customify_config( array $config ): array {
		// We will go recursively and search for fonts fields.
		$this->standardize_font_fields_config( $config );

		return $config;
	}

	/**
	 *
	 * @since 3.0.0
	 *
	 * @param       $item
	 * @param false $key
	 */
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

			$subfieldsConfig = apply_filters( 'customify_default_font_subfields_config', [
				'font-family'     => true,
				'font-weight'     => true, // This is actually for the font-variant field (weight and maybe style)
				'font-size'       => false,
				'line-height'     => false,
				'letter-spacing'  => false,
				'text-align'      => false,
				'text-transform'  => false,
				'text-decoration' => false,
			], $item, $key );

			// If we have received a fields configuration, merge it with the default.
			$subfieldsConfig = wp_parse_args( $item['fields'], $subfieldsConfig );

			// Standardize the fields that are ranges.
			foreach ( self::$rangeFields as $rangeField ) {
				if ( isset( $subfieldsConfig[ $rangeField ] ) && false !== $subfieldsConfig[ $rangeField ] ) {
					$subfieldsConfig[ $rangeField ] = FontsHelper::standardizeRangeFieldAttributes( $subfieldsConfig[ $rangeField ] );
				}
			}

			// Finally save the new subfields config.
			$item['fields'] = $subfieldsConfig;

			// We want to standardize the selector(s), if present.
			if ( ! empty( $item['selector'] ) ) {
				$item['selector'] = FontsHelper::standardizeFontSelector( $item['selector'] );
			}

			// We want to standardize the default value, if present.
			if ( ! empty( $item['default'] ) ) {
				$item['default'] = $this->standardizeFontValue( $item['default'], $item );
			}

			// We have no reason to go recursively further when we have come across a `font` field configuration.
			return;
		}

		foreach ( $item as $key => $subitem ) {
			// We can't use $subitem since that is a copy, and we need to reference the original.
			$this->standardize_font_fields_config( $item[ $key ], $key );
		}
	}

	/**
	 * Massage an array containing the value (values for subfields) of a `font` field type, into one consistent structure.
	 *
	 * Handle legacy entries.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $value
	 * @param array $fontConfig Optional.
	 *
	 * @return array
	 */
	public function standardizeFontValue( $value, array $fontConfig = [] ): array {
		if ( empty( $value ) ) {
			return [];
		}

		// If we are given a string, we will consider it a font-family definition
		if ( is_string( $value ) ) {
			$value = [ $value ];
		}

		// The value may be a stdClass object.
		if ( is_object( $value ) ) {
			// This is a sure way to get multi-dimensional objects as array (converts deep).
			$value = json_decode( json_encode( $value ), true );
		}

		// If by this time we don't have an array, return an empty value.
		if ( ! is_array( $value ) ) {
			return [];
		}

		// Handle special logic for when the $values array is not an associative array.
		if ( ! FontsHelper::isAssocArray( $value ) ) {
			$value = FontsHelper::standardizeNonAssociativeFontValues( $value );
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
				$fontDetails = $this->getFontDetails( $entryValue );
				if ( false !== $fontDetails ) {
					$value[ $newEntry ] = $entryValue;
				}
			}

			// Standardize numerical fields.
			if ( in_array( $newEntry, [ 'font_size', 'line_height', 'letter_spacing' ] ) ) {
				$value[ $newEntry ] = FontsHelper::standardizeNumericalValue( $entryValue );
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
		if ( ! empty( $value['font_variant'] ) && is_array( $value['font_variant'] ) && ! FontsHelper::isAssocArray( $value['font_variant'] ) ) {
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

	public function get_system_fonts(): array {
		return $this->system_fonts;
	}

	public function get_google_fonts(): array {
		return $this->google_fonts;
	}

	public function get_theme_fonts(): array {
		return $this->theme_fonts;
	}

	public function get_cloud_fonts(): array {
		return $this->cloud_fonts;
	}

	public function get_categories(): array {
		return $this->categories;
	}

	public function getFontDetails( string $font_family, string $font_type = '' ): array {
		if ( empty( $font_type ) ) {
			// We will determine the font type based on font family.
			$font_type = $this->determineFontType( $font_family );
		}

		switch ( $font_type ) {
			case 'theme_font':
				return $this->theme_fonts[ $font_family ];
			case 'cloud_font':
				return $this->cloud_fonts[ $font_family ];
			case 'google_font':
				return $this->google_fonts[ $font_family ];
			case 'system_font':
				if ( isset( $this->system_fonts[ $font_family ] ) ) {
					return $this->system_fonts[ $font_family ];
				}
				break;
			default:
				return [];
		}

		return [];
	}

	public function output_cloud_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_cloud_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->cloud_fonts ) ) {
			echo '<optgroup label="' . esc_html__( 'Cloud Fonts', '__plugin_txtd' ) . '">';
			foreach ( $this->get_cloud_fonts() as $font ) {
				if ( ! empty( $font['family'] ) ) {
					// Display the select option's HTML.
					$this->output_font_family_option( $font['family'], $active_font_family );
				}
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_cloud_fonts_options', $active_font_family, $current_value );
	}

	public function output_theme_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_theme_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->theme_fonts ) ) {
			echo '<optgroup label="' . esc_html__( 'Theme Fonts', '__plugin_txtd' ) . '">';
			foreach ( $this->get_theme_fonts() as $font ) {
				if ( ! empty( $font['family'] ) ) {
					// Display the select option's HTML.
					$this->output_font_family_option( $font['family'], $active_font_family );
				}
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_theme_fonts_options', $active_font_family, $current_value );
	}

	public function output_system_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_system_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->system_fonts ) ) {

			echo '<optgroup label="' . esc_attr__( 'System fonts', '__plugin_txtd' ) . '">';
			foreach ( $this->get_system_fonts() as $font ) {
				if ( ! empty( $font['family'] ) ) {
					// Display the select option's HTML.
					$this->output_font_family_option( $font['family'], $active_font_family );
				}
			}
			echo "</optgroup>";
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_system_fonts_options', $active_font_family, $current_value );
	}

	public function output_google_fonts_select_options_group( $active_font_family, $current_value ) {
		// Allow others to add options here
		do_action( 'customify_font_family_before_google_fonts_options', $active_font_family, $current_value );

		if ( ! empty( $this->google_fonts ) ) {
			// The actual options in this optiongroup will be injected via JS from the output of
			// see@ Fonts::customize_pane_settings_google_fonts_options()
			echo '<optgroup class="google-fonts-opts-placeholder" label="' . esc_attr__( 'Google fonts', '__plugin_txtd' ) . '"></optgroup>';
		}

		// Allow others to add options here
		do_action( 'customify_font_family_after_google_fonts_options', $active_font_family, $current_value );
	}

	/**
	 * This method displays an <option> tag from the given params
	 *
	 * @param string|array $font_family
	 * @param string|false $active_font_family Optional. The active font family to add the selected attribute to the appropriate opt.
	 *                                         False to not mark any opt as selected.
	 */
	protected function output_font_family_option( $font_family, $active_font_family = false ) {
		echo $this->get_font_family_option_markup( $font_family, $active_font_family );
	}

	/**
	 * This method returns an <option> tag from the given params
	 *
	 * @param string|array $font_family
	 * @param string|false $active_font_family Optional. The active font family to add the selected attribute to the appropriate opt.
	 *                                         False to not mark any opt as selected.
	 *
	 * @return string
	 */
	protected function get_font_family_option_markup( $font_family, $active_font_family = false ): string {
		$html = '';

		// Bail if we don't have a font family value.
		if ( empty( $font_family ) ) {
			return apply_filters( 'customify_filter_font_option_markup_no_family', $html, $active_font_family );
		}

		$font_type    = $this->determineFontType( $font_family );
		$font_details = $this->getFontDetails( $font_family, $font_type );

		// Now determine if we have a "pretty" display for this font family.
		$font_family_display = $font_family;
		if ( ! empty( $font_details['family_display'] ) ) {
			$font_family_display = $font_details['family_display'];
		}

		// Determine if the font is selected.
		$selected = ( false !== $active_font_family && $active_font_family === $font_family ) ? ' selected="selected" ' : '';

		// Determine the option class.
		$option_class = ( false !== strpos( $font_type, '_font' ) ) ? $font_type : $font_type . '_font';

		$html .= '<option class="' . esc_attr( $option_class ) . '" value="' . esc_attr( $font_family ) . '" ' . $selected . '>' . $font_family_display . '</option>';

		return apply_filters( 'customify_filter_font_option_markup', $html, $font_family, $active_font_family, $font_type );
	}

	public function customize_pane_settings_google_fonts_options() {
		if ( empty( $this->google_fonts ) ) {
			return;
		}

		?>
		<script type="text/javascript">
			if ('undefined' === typeof _wpCustomizeSettings.settings) {
				_wpCustomizeSettings.settings = {}
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

	/**
	 * @return false|string
	 */
	protected function getGoogleFontsSelectOptions() {

		if ( ! $this->plugin_settings->get( 'typography_google_fonts', 'yes' ) || empty( $this->google_fonts ) ) {
			return '';
		}

		ob_start();
		if ( $this->plugin_settings->get( 'typography_group_google_fonts', 'yes' ) ) {

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
				echo '<optgroup label="' . sprintf( esc_attr__( 'Google fonts %s', '__plugin_txtd' ), $group_name ) . '">';
				foreach ( $group_fonts as $font_details ) {
					$this->output_font_family_option( $font_details['family'] );
				}
				echo "</optgroup>";
			}

		} else {
			echo '<optgroup label="' . esc_attr__( 'Google fonts', '__plugin_txtd' ) . '">';
			foreach ( $this->get_google_fonts() as $font_details ) {
				$this->output_font_family_option( $font_details['family'] );
			}
			echo "</optgroup>";
		}

		return ob_get_clean();
	}

	/**
	 * Gather all the font families that need to be loaded via Web Font Loader.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function getFontFamiliesDetailsForWebfontloader(): array {

		$args = [
			'google_families' => [],
			'custom_families' => [],
			'custom_srcs'     => [],
		];

		$font_fields = [];
		$this->get_fields_by_key( $this->options->get_details_all(), 'type', 'font', $font_fields );

		if ( empty( $font_fields ) ) {
			return $args;
		}

		// These are fields that should have no frontend impact.
		$excluded_fields = [
			'sm_font_palette',
			'sm_font_palette_variation',
			'sm_font_primary',
			'sm_font_secondary',
			'sm_font_body',
			'sm_font_accent',
			'sm_swap_fonts',
			'sm_swap_primary_secondary_fonts',
		];

		foreach ( $font_fields as $id => $font ) {
			// Bail if this is an excluded field.
			if ( in_array( $id, $excluded_fields ) ) {
				continue;
			}

			// Bail without a value.
			if ( empty( $font['value'] ) ) {
				continue;
			}

			$value = $this->standardizeFontValue( FontsHelper::maybeDecodeValue( $font['value'] ), $font );

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
					$font_family .= ":" . join( ',', FontsHelper::convertFontVariantsToFvds( $value['font_variant'] ) );
				} elseif ( ! empty( $font_details['variants'] ) ) {
					$font_family .= ':' . join( ',', FontsHelper::convertFontVariantsToFvds( $font_details['variants'] ) );
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
					$font_family .= ":" . FontsHelper::maybeImplodeList( $font_details['variants'] );
				} else {
					$font_family .= ":" . FontsHelper::maybeImplodeList( $value['font_variant'] );
				}
			} elseif ( ! empty( $font_details['variants'] ) ) {
				$font_family .= ":" . FontsHelper::maybeImplodeList( $font_details['variants'] );
			}

			$args['google_families'][] = "'" . $font_family . "'";
		}

		$args = [
			'google_families' => array_unique( $args['google_families'] ),
			'custom_families' => array_unique( $args['custom_families'] ),
			'custom_srcs'     => array_unique( $args['custom_srcs'] ),
		];

		return $args;
	}

	/**
	 * Gather all the needed web fonts stylesheet URLs (the stylesheets contain the @font-face definition).
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function getFontsStylesheetUrls(): array {
		$urls = [];

		$font_fields = [];
		$this->get_fields_by_key( $this->options->get_details_all(), 'type', 'font', $font_fields );

		if ( empty( $font_fields ) ) {
			return $urls;
		}

		// These are fields that should have no frontend impact.
		$excluded_fields = [
			'sm_font_palette',
			'sm_font_palette_variation',
			'sm_font_primary',
			'sm_font_secondary',
			'sm_font_body',
			'sm_font_accent',
			'sm_swap_fonts',
			'sm_swap_primary_secondary_fonts',
		];

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

			$value = $this->standardizeFontValue( FontsHelper::maybeDecodeValue( $font['value'] ), $font );

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
				$font_family .= ':' . FontsHelper::convertFontVariantsToGoogleFontsCSS2Styles( $font_details['variants'] );
			}

			$google_fonts[] = $font_family;
		}

		if ( ! empty( $google_fonts ) ) {
			$google_url = 'https://fonts.googleapis.com/css2';
			// Add `family=` to each font family.
			$google_fonts = array_map( function ( $font_family ) {
				return 'family=' . $font_family;
			}, $google_fonts );
			// We can't use add_query_arg() because it will not allow for multiple `family` args like Google Fonts expects.
			$google_url .= '?' . join( '&', $google_fonts );

			// Request @font-face stylesheets with font-display: swap;
			$google_url .= '&display=swap';

			$urls[] = $google_url;
		}

		return $urls;
	}

	/**
	 *
	 * @since 3.0.0
	 *
	 * @param string $font_family
	 *
	 * @return array
	 */
	public function getFontDefaultsValue( string $font_family ): array {
		if ( empty( $font_family ) ) {
			return [];
		}

		return [
			'type'        => $this->determineFontType( $font_family ),
			'font_family' => $font_family,
		];
	}

	/**
	 * @since 3.0.0
	 */
	function outputFontsDynamicStyle() {

		$font_fields = [];
		$this->get_fields_by_key( $this->options->get_details_all(), 'type', 'font', $font_fields );

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

	/**
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	function getFontsDynamicStyle(): string {
		$output = '';

		$font_fields = [];
		$this->get_fields_by_key( $this->options->get_details_all(), 'type', 'font', $font_fields );

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
	 * @since 3.0.0
	 *
	 * @param array $fontConfig
	 *
	 * @return string The CSS rules.
	 */
	protected function getFontStyle( array $fontConfig ): string {

		if ( ! isset( $fontConfig['selector'] ) || ! isset( $fontConfig['value'] ) ) {
			return '';
		}

		$value = $this->standardizeFontValue( FontsHelper::maybeDecodeValue( $fontConfig['value'] ), $fontConfig );

		// In case the value is empty, try a default value if the $font['value'] is actually the font family.
		if ( empty( $value ) && is_string( $fontConfig['value'] ) ) {
			$value = $this->getFontDefaultsValue( str_replace( '"', '', $fontConfig['value'] ) );
		}

		$cssValue = $this->getCSSValue( $value, $fontConfig );
		// Make sure we are dealing with a selector as a list of individual selector,
		// maybe some of them having special details like supported properties.
		$cssSelectors = apply_filters( 'customify_font_css_selector', FontsHelper::standardizeFontSelector( $fontConfig['selector'] ), $fontConfig );

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
				$font_size             = FontsHelper::standardizeNumericalValue( $value['font_size'], 'font-size', $fontConfig );
				$cssValue['font-size'] = $font_size['value'];
			}
			if ( ! empty( $cssValue['letter-spacing'] ) ) {
				$letter_spacing             = FontsHelper::standardizeNumericalValue( $value['letter_spacing'], 'letter-spacing', $fontConfig );
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
					$new_key              = str_replace( '-', '_', $new_key );
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
		$subFieldsCSSAllowedProperties = FontsHelper::extractAllowedCSSPropertiesFromFontFields( $fontConfig['fields'] );

		// Since we might have simple CSS selectors and complex ones (with special details),
		// for cleanliness we will group the simple ones under a single CSS rule,
		// and output individual CSS rules for complex ones.
		// Right now, for complex CSS selectors we are only interested in the `properties` sub-entry.
		$simple_css_selectors  = [];
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
			$output .= "\n" . join( ', ', $simple_css_selectors ) . " {" . "\n";
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
	 * @since 3.0.0
	 *
	 * @param array $value
	 * @param array $fontConfig
	 *
	 * @return array
	 */
	protected function getCSSValue( array $value, array $fontConfig ): array {
		$cssValue = [];

		if ( ! empty( $value['font_family'] ) && ! FontsHelper::isFalsy( $value['font_family'] ) ) {
			$cssValue['font-family'] = $value['font_family'];
			// "Expand" the font family by appending the fallback stack, if any is available.
			// But only do this, if the value is not already a font stack!
			if ( false === strpos( $cssValue['font-family'], ',' ) ) {
				$fallbackStack = $this->getFontFamilyFallbackStack( $cssValue['font-family'] );
				if ( ! empty( $fallbackStack ) ) {
					$cssValue['font-family'] .= ',' . $fallbackStack;
				}
			}

			$cssValue['font-family'] = FontsHelper::sanitizeFontFamilyCSSValue( $cssValue['font-family'] );
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
		if ( ! empty( $value['font_variant'] ) && ! FontsHelper::isFalsy( $value['font_variant'] ) ) {
			// Standardize it.
			$font_variant = \Pixelgrade\Customify\Utils\Fonts::standardizeFontVariant( $value['font_variant'] );

			if ( strpos( $font_variant, 'italic' ) !== false ) {
				$font_variant           = str_replace( 'italic', '', $font_variant );
				$cssValue['font-style'] = 'italic';
			} elseif ( strpos( $font_variant, 'oblique' ) !== false ) {
				$font_variant           = str_replace( 'oblique', '', $font_variant );
				$cssValue['font-style'] = 'oblique';
			}

			// If we have a remainder like '400', use it as font weight.
			if ( ! empty( $font_variant ) ) {
				$cssValue['font-weight'] = $font_variant;
			}
		}

		if ( ! empty( $value['font_size'] ) && ! FontsHelper::isFalsy( $value['font_size'] ) ) {
			$font_size = FontsHelper::standardizeNumericalValue( $value['font_size'], 'font-size', $fontConfig );
			if ( false !== $font_size['value'] ) {
				$cssValue['font-size'] = $font_size['value'];

				// If we use ems or rems, and the value is larger than 9, then something must be wrong; we will use pixels.
				if ( $font_size['value'] >= 9 && in_array( $font_size['unit'], [ 'em', 'rem' ] ) ) {
					$font_size['unit'] = 'px';
				}

				$cssValue['font-size'] .= $font_size['unit'];
			}
		}

		if ( ! empty( $value['letter_spacing'] ) && ! FontsHelper::isFalsy( $value['letter_spacing'] ) ) {
			$letter_spacing = FontsHelper::standardizeNumericalValue( $value['letter_spacing'], 'letter-spacing', $fontConfig );

			if ( false !== $letter_spacing['value'] ) {
				$cssValue['letter-spacing'] = $letter_spacing['value'] . $letter_spacing['unit'];
			}
		}

		if ( ! empty( $value['line_height'] ) && ! FontsHelper::isFalsy( $value['line_height'] ) ) {
			$line_height = FontsHelper::standardizeNumericalValue( $value['line_height'], 'line-height', $fontConfig );

			if ( false !== $line_height['value'] ) {
				$cssValue['line-height'] = $line_height['value'] . $line_height['unit'];
			}
		}

		if ( ! empty( $value['text_align'] ) && ! FontsHelper::isFalsy( $value['text_align'] ) ) {
			$cssValue['text-align'] = $value['text_align'];
		}

		if ( ! empty( $value['text_transform'] ) && ! FontsHelper::isFalsy( $value['text_transform'] ) ) {
			$cssValue['text-transform'] = $value['text_transform'];
		}

		if ( ! empty( $value['text_decoration'] ) && ! FontsHelper::isFalsy( $value['text_decoration'] ) ) {
			$cssValue['text-decoration'] = $value['text_decoration'];
		}

		return $cssValue;
	}

	/**
	 *
	 * @since 3.0.0
	 *
	 * @param        $cssValue
	 * @param false  $allowedProperties
	 * @param string $propertiesPrefix
	 *
	 * @return string
	 */
	protected function getCSSProperties( $cssValue, $allowedProperties = false, $propertiesPrefix = '' ): string {
		$output = '';

		if ( empty( $cssValue ) ) {
			return $output;
		}

		foreach ( $cssValue as $property => $propertyValue ) {
			// We don't want to output empty CSS rules.
			if ( FontsHelper::isFalsy( $propertyValue ) ) {
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

	/**
	 *
	 * @since 3.0.0
	 *
	 * @param $fontFamily
	 *
	 * @return string
	 */
	protected function getFontFamilyFallbackStack( $fontFamily ): string {
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
						$aliases = FontsHelper::maybeImplodeList( $category_details['aliases'] );
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

	/**
	 * Output and enqueue the scripts needed to handle web fonts loading on the frontend (including the Customizer preview).
	 *
	 * @since 3.0.0
	 */
	public function enqueue_frontend_scripts_styles() {
		// If we are in the Customizer preview, we will always use the WebFontLoader.
		if ( is_customize_preview() ) {
			// We always enqueue the WebFontLoader script.
			wp_enqueue_script( 'pixelgrade_customify-web-font-loader' );

			// Get the inline script to load all the needed fonts via WebFontLoader.
			$script = $this->get_webfontloader_dynamic_script();
			if ( ! empty( $script ) ) {
				wp_add_inline_script( 'pixelgrade_customify-web-font-loader', $script );
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
				add_action( 'wp_footer', function () { ?>
					<script>
						let customifyTriggerFontsLoadedEvents = function () {
							// Trigger the 'wf-active' event, just like Web Font Loader would do.
							window.dispatchEvent(new Event('wf-active'))
							// Add the 'wf-active' class on the html element, just like Web Font Loader would do.
							document.getElementsByTagName('html')[0].classList.add('wf-active')
						}

						// Try to use the modern FontFaceSet browser APIs.
						if (typeof document.fonts !== 'undefined' && typeof document.fonts.ready !== 'undefined') {
							document.fonts.ready.then(customifyTriggerFontsLoadedEvents)
						} else {
							// Fallback to just waiting a little bit and then triggering the events for older browsers.
							window.addEventListener('load', function () {
								setTimeout(customifyTriggerFontsLoadedEvents, 300)
							})
						}
					</script>
					<?php
				} );
			} else {
				$this->handleNoWebFontsEvents();
			}
		}
	}

	/**
	 * @since 3.0.0
	 */
	protected function handleNoWebFontsEvents() {
		// If there are no web fonts to load, add a script to the footer, on window loaded,
		// to trigger the font loaded event and add the class to the html element.
		// This way the behavior is consistent.
		add_action( 'wp_footer', function () { ?>
			<script>
				window.addEventListener('load', function () {
					// Trigger the 'wf-active' event, just like Web Font Loader would do.
					window.dispatchEvent(new Event('wf-active'))
					// Add the 'wf-active' class on the html element, just like Web Font Loader would do.
					document.getElementsByTagName('html')[0].classList.add('wf-active')
				})
			</script>
			<?php
		} );
	}

	/**
	 * @since 3.0.0
	 *
	 * @return string
	 */
	function get_webfontloader_dynamic_script(): string {
		// If typography has been deactivated from the settings, bail.
		if ( ! $this->plugin_settings->get( 'enable_typography', 'yes' ) ) {
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
$custom_urls     = [];

if ( ! empty( $args['custom_families'] ) && ! empty( $args['custom_srcs'] ) ) {
	$custom_families += $args['custom_families'];
	$custom_urls     += $args['custom_srcs'];
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
	 *
	 * @since 3.0.0
	 */
	public function add_preconnect_links() {
		// If typography has been deactivated from the settings, bail.
		if ( ! $this->plugin_settings->get( 'enable_typography', 'yes' )
		     || ! $this->plugin_settings->get( 'typography_google_fonts', 'yes' ) ) {
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
			$own_origin = FontsHelper::extractOriginFromUrl( get_bloginfo( 'url' ) );
			if ( ! empty( $own_origin ) ) {
				// Remove the protocol
				$own_origin = preg_replace( '#((http|https|ftp|ftps)?\:?)#i', '', $own_origin );

				$external_origins = [];
				foreach ( $args['custom_srcs'] as $src ) {
					$origin = FontsHelper::extractOriginFromUrl( $src );
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
	 * @since 3.0.0
	 *
	 * @param $localized
	 *
	 * @return mixed
	 */
	public function add_to_localized_data( $localized ) {
		if ( empty( $localized['fonts'] ) ) {
			$localized['fonts'] = [];
		}

		$localized['fonts']['floatPrecision'] = FontsHelper::FLOAT_PRECISION;

		$localized['fonts']['theme_fonts']  = $this->get_theme_fonts();
		$localized['fonts']['cloud_fonts']  = $this->get_cloud_fonts();
		$localized['fonts']['google_fonts'] = $this->get_google_fonts();
		$localized['fonts']['system_fonts'] = $this->get_system_fonts();
		$localized['fonts']['categories']   = $this->get_categories();

		if ( empty( $localized['l10n'] ) ) {
			$localized['l10n'] = [];
		}
		$localized['l10n']['fonts'] = [
			'familyPlaceholderText' => esc_html__( 'Select a font family', '__plugin_txtd' ),
			'variantAutoText'       => esc_html__( 'Auto', '__plugin_txtd' ),
		];

		return $localized;
	}

	/**
	 * Load the google fonts list from the local file, if not already loaded.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	protected function maybe_load_google_fonts() {

		if ( empty( $this->google_fonts ) ) {
			$fonts_path = $this->plugin->get_path( 'resources/google.fonts.php' );

			if ( file_exists( $fonts_path ) ) {
				$this->google_fonts = apply_filters( 'customify_filter_google_fonts_list', require( $fonts_path ) );
				$this->google_fonts = FontsHelper::standardizeFontsList( $this->google_fonts );
			}
		}

		if ( ! empty( $this->google_fonts ) ) {
			return $this->google_fonts;
		}

		return [];
	}

	/**
	 * Given a property and a list of allowed properties, determine if it is allowed.
	 *
	 * @since 3.0.0
	 *
	 * @param string      $property
	 * @param false|array $allowedProperties
	 *
	 * @return bool
	 */
	protected function isCSSPropertyAllowed( string $property, $allowedProperties = false ): bool {
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

			$standardizedAllowedProperties[ $key ] = ! empty( $value );
		}

		if ( empty( $standardizedAllowedProperties ) ) {
			return true;
		}

		return ! empty( $standardizedAllowedProperties[ $property ] );
	}

	/**
	 * Determine a font type based on its font family.
	 *
	 * We will follow a stack in the following order: cloud fonts, theme fonts, Google fonts, system fonts.
	 *
	 * @since 3.0.0
	 *
	 * @param string $fontFamily
	 *
	 * @return string The font type: google_font, theme_font, cloud_font, or system_font.
	 */
	public function determineFontType( string $fontFamily ): string {
		// The default is a standard font (aka no special loading or processing).
		$fontType = 'system_font';

		if ( ! empty( $this->cloud_fonts[ $fontFamily ] ) ) {
			$fontType = 'cloud_font';
		} elseif ( ! empty( $this->theme_fonts[ $fontFamily ] ) ) {
			$fontType = 'theme_font';
		} elseif ( ! empty( $this->google_fonts[ $fontFamily ] ) ) {
			$fontType = 'google_font';
		}

		return $fontType;
	}

	public function get_fields_by_key( $fields_config, $key, $value, &$results, $input_key = 0 ) {
		if ( ! is_array( $fields_config ) ) {
			return;
		}

		if ( isset( $fields_config[ $key ] ) && $fields_config[ $key ] == $value ) {
			$results[ $input_key ] = $fields_config;

			$default = null;
			if ( isset( $fields_config['default'] ) ) {
				$default = $fields_config['default'];
			}

			$results[ $input_key ]['value'] = $this->options->get( $input_key, $default );
		}

		foreach ( $fields_config as $i => $subarray ) {
			$this->get_fields_by_key( $subarray, $key, $value, $results, $i );
		}
	}
}
