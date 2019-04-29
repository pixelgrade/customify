<?php
/**
 * This is the class that handles the overall logic for the Customizer.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Customizer' ) ) :

	class Customify_Customizer {

		/**
		 * Holds the only instance of this class.
		 * @var     null|Customify_Customizer
		 * @access  protected
		 * @since   2.4.0
		 */
		protected static $_instance = null;

		protected $localized = array();

		protected $typo_settings;

		protected $google_fonts = null;

		protected $theme_fonts = null;

		protected $media_queries = array();

		// these properties will get 'px' as a default unit
		public static $pixel_dependent_css_properties = array(
			'width',
			'max-width',
			'min-width',

			'height',
			'max-height',
			'min-height',

			'padding',
			'padding-left',
			'padding-right',
			'padding-top',
			'padding-bottom',

			'margin',
			'margin-right',
			'margin-left',
			'margin-top',
			'margin-bottom',

			'right',
			'left',
			'top',
			'bottom',

			'font-size',
			'letter-spacing',

			'border-size',
			'border-width',
			'border-bottom-width',
			'border-left-width',
			'border-right-width',
			'border-top-width'
		);

		/**
		 * Constructor.
		 *
		 * @since 2.4.0
		 */
		protected function __construct() {
			// We will initialize the Customizer logic after the plugin has finished with it's configuration (at priority 15).
			add_action( 'init', array( $this, 'init' ), 15 );
		}

		/**
		 * Initialize this module.
		 *
		 * @since 2.4.0
		 */
		public function init() {

			$this->localized['options_name'] = PixCustomifyPlugin()->get_options_key();

			if ( $this->import_button_exists() ) {
				$this->localized['import_rest_url']   = get_rest_url( '/customify/1.0/' );
				$this->localized['import_rest_nonce'] = wp_create_nonce( 'wp_rest' );

				$this->register_import_api();
			}

			require_once( PixCustomifyPlugin()->get_base_path() . 'features/class-Font_Selector.php' );
			$this->localized['theme_fonts'] = $this->theme_fonts = Customify_Font_Selector::instance()->get_theme_fonts();

			$this->localized['ajax_url'] = admin_url( 'admin-ajax.php' );
			$this->localized['style_manager_user_feedback_nonce'] = wp_create_nonce( 'customify_style_manager_user_feedback' );
			$this->localized['style_manager_user_feedback_provided'] = get_option( 'style_manager_user_feedback_provided', false );

			// Hook up.
			$this->add_hooks();
		}

		/**
		 * Use this function when you need to know if an import button is used
		 * @return bool
		 */
		protected function import_button_exists() {
			$options_details = PixCustomifyPlugin()->get_options_details(true);

			foreach ( $options_details as $option ) {
				if ( isset( $option['type'] ) && 'import_demo_data' === $option['type'] ) {
					return true;
					break;
				}
			}

			return false;
		}

		protected function register_import_api() {

			include_once( PixCustomifyPlugin()->get_base_path() . 'features/class-Customify_Importer.php' );
			$controller = new Customify_Importer_Controller();
			$controller->init();
		}

		/**
		 * Initiate our hooks
		 *
		 * @since 2.4.0
		 */
		public function add_hooks() {

			// Styles for the Customizer
			add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_styles' ), 10 );
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_styles' ), 10 );
			// Scripts enqueued in the Customizer
			add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_scripts' ), 15 );
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_scripts' ), 15 );

			// Scripts enqueued only in the theme preview
			add_action( 'customize_preview_init', array( $this, 'customizer_live_preview_register_scripts' ), 10 );
			add_action( 'customize_preview_init', array( $this, 'customizer_live_preview_enqueue_scripts' ), 99999 );

			// Add extra settings data to _wpCustomizeSettings.settings of the parent window.
			add_action( 'customize_controls_print_footer_scripts', array( $this, 'customize_pane_settings_additional_data' ), 10000 );

			// The frontend effects of the Customizer controls
			$load_location = PixCustomifyPlugin()->get_plugin_setting( 'style_resources_location', 'wp_head' );

			add_action( $load_location, array( $this, 'output_dynamic_style' ), 99 );
			add_action( 'wp_head', array( $this, 'output_typography_dynamic_script' ), 10 );
			add_action( 'wp_head', array( $this, 'output_typography_dynamic_style' ), 10 );

			add_action( 'customize_register', array( $this, 'remove_default_sections' ), 11 );
			add_action( 'customize_register', array( $this, 'register_customizer' ), 12 );
			// Maybe the theme has instructed us to do things like removing sections or controls.
			add_action( 'customize_register', array( $this, 'maybe_process_config_extras' ), 13 );

			if ( PixCustomifyPlugin()->get_plugin_setting( 'enable_editor_style', true ) ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'script_to_add_customizer_settings_into_wp_editor' ), 10, 1 );
			}

			/*
			 * Development related
			 */
			if ( defined( 'CUSTOMIFY_DEV_FORCE_DEFAULTS' ) && true === CUSTOMIFY_DEV_FORCE_DEFAULTS ) {
				// If the development constant CUSTOMIFY_DEV_FORCE_DEFAULTS has been defined we will not save anything in the database
				// Always go with the default
				add_filter( 'customize_changeset_save_data', array( $this, 'prevent_changeset_save_in_devmode' ), 50, 1 );
				// Add a JS to display a notification
				add_action( 'customize_controls_print_footer_scripts', array( $this, 'prevent_changeset_save_in_devmode_notification' ), 100 );
			}
		}

		/**
		 * Register Customizer admin styles
		 */
		function register_admin_customizer_styles() {
			wp_register_style( 'customify_select2', plugins_url( 'js/select2/css/select2.css', PixCustomifyPlugin()->get_file() ), array(), PixCustomifyPlugin()->get_version() );
			wp_register_style( 'customify_style', plugins_url( 'css/customizer.css', PixCustomifyPlugin()->get_file() ), array( 'customify_select2' ), PixCustomifyPlugin()->get_version() );
		}

		/**
		 * Enqueue Customizer admin styles
		 */
		function enqueue_admin_customizer_styles() {
			wp_enqueue_style( 'customify_style' );
		}

		/**
		 * Register Customizer admin scripts
		 */
		function register_admin_customizer_scripts() {

			wp_register_script( 'customify_select2', plugins_url( 'js/select2/js/select2.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version() );
			wp_register_script( 'jquery-react', plugins_url( 'js/jquery-react.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version() );

			wp_register_script( 'customify-scale', plugins_url( 'js/customizer/scale-iframe.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version() );
			wp_register_script( 'customify-fontselectfields', plugins_url( 'js/customizer/font-select-fields.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version() );

			wp_register_script( PixCustomifyPlugin()->get_slug() . '-customizer-scripts', plugins_url( 'js/customizer.js', PixCustomifyPlugin()->get_file() ), array(
				'jquery',
				'customify_select2',
				'underscore',
				'customize-controls',
				'customify-fontselectfields',

				'customify-scale',
			), PixCustomifyPlugin()->get_version() );
		}

		/**
		 * Enqueue Customizer admin scripts
		 */
		function enqueue_admin_customizer_scripts() {
			wp_enqueue_script( 'jquery-react' );
			wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-customizer-scripts' );

			wp_localize_script( PixCustomifyPlugin()->get_slug() . '-customizer-scripts', 'customify_settings', apply_filters( 'customify_localized_js_settings', $this->localized ) );
		}

		/** Register Customizer scripts loaded only on previewer page */
		function customizer_live_preview_register_scripts() {
			wp_register_script( PixCustomifyPlugin()->get_slug() . 'CSSOM', plugins_url( 'js/CSSOM.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version(), true );
			wp_register_script( PixCustomifyPlugin()->get_slug() . 'cssUpdate', plugins_url( 'js/jquery.cssUpdate.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version(), true );
			wp_register_script( PixCustomifyPlugin()->get_slug() . '-previewer-scripts', plugins_url( 'js/customizer_preview.js', PixCustomifyPlugin()->get_file() ), array(
				'jquery',
				'customize-preview',
				PixCustomifyPlugin()->get_slug() . 'CSSOM',
				PixCustomifyPlugin()->get_slug() . 'cssUpdate'
			), PixCustomifyPlugin()->get_version(), true );
		}

		/** Enqueue Customizer scripts loaded only on previewer page */
		function customizer_live_preview_enqueue_scripts() {
			wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-previewer-scripts' );

			// when a live preview field is in action we need to know which props need 'px' as defaults
			$this->localized['px_dependent_css_props'] = self::$pixel_dependent_css_properties;

			wp_localize_script( PixCustomifyPlugin()->get_slug() . '-previewer-scripts', 'customify_settings', $this->localized );
		}

		/**
		 * Prevent saving of plugin options in the Customizer
		 *
		 * @param array $data The data to save
		 *
		 * @return array
		 */
		public function prevent_changeset_save_in_devmode( $data ) {
			// Get the options key
			$options_key = PixCustomifyPlugin()->get_options_key();
			if ( ! empty( $options_key ) ) {
				// Remove any Customify data thus preventing it from saving
				foreach ( $data as $option_id => $value ) {
					if ( false !== strpos( $option_id, $options_key ) && ! PixCustomifyPlugin()->skip_dev_mode_force_defaults( $option_id ) ) {
						unset( $data[ $option_id ] );
					}
				}
			}

			return $data;
		}

		public function prevent_changeset_save_in_devmode_notification() { ?>
			<script type="application/javascript">
              (function ( $, exports, wp ) {
                'use strict';
                // when the customizer is ready add our notification
                wp.customize.bind('ready', function () {
                  wp.customize.notifications.add( 'customify_force_defaults', new wp.customize.Notification(
                    'customify_force_defaults',
                    {
                      type: 'warning',
                      message: '<strong style="margin-bottom: ">Customify: Development Mode</strong><p>All the options are switched to default. While they are changing in the live preview, they will not be kept when you hit publish.</p>'
                    }
                  ) );
                });
              })(jQuery, window, wp);
			</script>
		<?php }



		/**
		 * Output CSS style generated by customizer
		 */
		function output_dynamic_style() { ?>
			<style id="customify_output_style">
				<?php echo $this->get_dynamic_style(); ?>
			</style>
			<?php

			/**
			 * from now on we output only style tags only for the preview purpose
			 * so don't cry if you see 30+ style tags for each section
			 */
			if ( ! isset( $GLOBALS['wp_customize'] ) ) {
				return;
			}

			foreach ( PixCustomifyPlugin()->get_options_details( false, true) as $option_id => $option_details ) {

				if ( isset( $option_details['type'] ) && $option_details['type'] === 'custom_background' ) {
					$custom_background_output = $this->process_custom_background_field_output( $option_details ); ?>

					<style id="custom_background_output_for_<?php echo sanitize_html_class( $option_id ); ?>">
						<?php
						if ( ! empty( $custom_background_output )) {
							echo $custom_background_output;
						} ?>
					</style>
				<?php }

				if ( ! isset( $option_details['live'] ) || $option_details['live'] !== true ) {
					continue;
				}

				if ( ! empty( $option_details['css'] ) ) {
					foreach ( $option_details['css'] as $key => $properties_set ) {
						// We need to use a class because we may have multiple <style>s with the same "ID" for example
						// when targeting the same property but with different selectors.
						$unique_class = 'dynamic_setting_' .  $option_id . '_property_' . str_replace( '-', '_', $properties_set['property'] ) . '_' . $key;

						$inline_style = '<style class="' . sanitize_html_class( $unique_class ) . '" type="text/css">';

						if ( isset( $properties_set['media'] ) && ! empty( $properties_set['media'] ) ) {
							$inline_style .= '@media '. $properties_set['media'] . ' {';
						}

						if ( isset( $properties_set['selector'] ) && isset( $properties_set['property'] ) ) {
							$css_output = $this->process_css_property($properties_set, $option_details['value']);
							if ( ! empty( $css_output ) ) {
								$inline_style .= $css_output;
							}
						}

						if ( isset( $properties_set['media'] ) && ! empty( $properties_set['media'] ) ) {
							$inline_style .= '}';
						}
						$inline_style .= '</style>';

						echo $inline_style;
					}
				}
			}
		}

		function get_dynamic_style() {
			$custom_css = '';

			foreach ( PixCustomifyPlugin()->get_options_details(true) as $option_id => $option_details ) {

				if ( isset( $option_details['css'] ) && ! empty( $option_details['css'] ) ) {
					// now process each
					$custom_css .= $this->convert_setting_to_css( $option_id, $option_details );
				}

				if ( isset( $option_details['type'] ) && $option_details['type'] === 'custom_background' ) {
					$custom_css .= $this->process_custom_background_field_output( $option_details ) . PHP_EOL;
				}
			}

			if ( ! empty( $this->media_queries ) ) {

				foreach ( $this->media_queries as $media_query => $properties ) {

					if ( empty( $properties ) ) {
						continue;
					}

					$media_query_custom_css = '';

					foreach ( $properties as $key => $property ) {
						$property_settings = $property['property'];
						$property_value    = $property['value'];
						$css_output = $this->process_css_property( $property_settings, $property_value );
						if ( ! empty( $css_output ) ) {
							$media_query_custom_css .= "\t" . $css_output . PHP_EOL;
						}
					}

					if ( ! empty( $media_query_custom_css ) ) {
						$media_query_custom_css = PHP_EOL . '@media ' . $media_query . " { " . PHP_EOL . PHP_EOL . $media_query_custom_css . "}" . PHP_EOL;
					}

					if ( ! empty( $media_query_custom_css ) ) {
						$custom_css .= $media_query_custom_css;
					}

				}
			}

			return apply_filters( 'customify_dynamic_style', $custom_css );
		}

		protected function load_google_fonts() {
			$fonts_path = PixCustomifyPlugin()->get_base_path() . 'features/customizer/controls/resources/google.fonts.php';

			if ( file_exists( $fonts_path ) ) {
				$this->google_fonts = require( $fonts_path );
			}

			if ( ! empty( $this->google_fonts ) ) {
				return $this->google_fonts;
			}

			return false;
		}

		function output_typography_dynamic_style() {
			$style = $this->get_typography_dynamic_style();

			if ( ! empty( $style ) ) { ?>
				<style id="customify_typography_output_style">
					<?php echo $style; ?>
				</style>
			<?php }
		}

		function get_typography_dynamic_style() {
			$output = '';

			$this->get_typography_fields( PixCustomifyPlugin()->get_options_details(true), 'type', 'typography', $this->typo_settings );

			if ( empty( $this->typo_settings ) ) {
				return $output;
			}

			ob_start();
			foreach ( $this->typo_settings as $font ) {
				$selector = apply_filters( 'customify_typography_css_selector', $font['selector'], $font );

				$load_all_weights = false;
				if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
					$load_all_weights = true;
				}

				if ( isset( $selector ) && isset( $font['value'] ) && ! empty( $font['value'] ) ) {
					// Make sure that the value is in the proper format
					$value = PixCustomifyPlugin::decodeURIComponent( $font['value'] );
					if ( is_string( $value ) ) {
						$value = json_decode( $value, true );
					}

					// In case the value is null (most probably because the json_decode failed),
					// try the default value (mostly for google fonts)
					if ( $value === null ) {
						$value = $this->get_font_defaults_value( $font['value'] );
					}

					// Shim the old case when the default was only the font name
					if ( ! empty( $value ) && is_string( $value ) ) {
						$value = array( 'font_family' => $value );
					}

					// Handle special logic for when the $value array is not an associative array
					if ( ! PixCustomifyPlugin()->is_assoc( $value ) ) {
						$value = $this->standardize_non_associative_font_default( $value );
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

						if ( ! $load_all_weights ) {
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
						}

						echo "}\n";
					} elseif ( isset( $value['font_family'] ) ) {
						// The selected font family
						echo $selector . " {\n font-family: " . $value['font_family'] . ";\n";

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

		function output_typography_dynamic_script() {

			$script = $this->get_typography_dynamic_script();
			if ( ! empty ( $script ) ) { ?>
				<script type="text/javascript">
					<?php echo $script; ?>
				</script>
			<?php }
		}

		function get_typography_dynamic_script() {
			$output = '';

			$this->get_typography_fields( PixCustomifyPlugin()->get_options_details(true), 'type', 'typography', $this->typo_settings );

			if ( empty( $this->typo_settings ) ) {
				return $output;
			}

			$families = '';

			foreach ( $this->typo_settings as $id => $font ) {
				if ( isset ( $font['value'] ) ) {

					$load_all_weights = false;
					if ( isset( $font['load_all_weights'] ) && $font['load_all_weights'] == 'true' ) {
						$load_all_weights = true;
					}

					// shim the time when this was an array
					// @todo Is this really needed? Or does it make sense?
					if ( is_array( $font['value'] ) ) {
						$font['value'] = stripslashes_deep( $font['value'] );
						$font['value'] = json_encode( $font['value'] );
					}

					$value = wp_unslash( PixCustomifyPlugin::decodeURIComponent( $font['value'] ) );
					if ( is_string( $value ) ) {
						$value = json_decode( $value, true );
					}

					// In case the value is still null, try default value (mostly for google fonts)
					if ( $value === null || ! is_array( $value ) ) {
						$value = $this->get_font_defaults_value( str_replace( '"', '', $font['value'] ) );
					}

					// Bail if by this time we don't have a value of some sort
					if ( empty( $value ) ) {
						continue;
					}

					// Handle special logic for when the $value array is not an associative array
					if ( ! PixCustomifyPlugin()->is_assoc( $value ) ) {
						$value = $this->standardize_non_associative_font_default( $value );
					}

					// Bail if empty or we don't have an array
					if ( empty( $value ) || ! is_array( $value ) ) {
						continue;
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

			if ( ! empty ( $families ) && PixCustomifyPlugin()->get_plugin_setting( 'typography', '1' )
			     && PixCustomifyPlugin()->get_plugin_setting( 'typography_google_fonts', 1 ) ) {
				ob_start();
				?>
				if (typeof WebFont !== 'undefined') {<?php // if there is a WebFont object, use it ?>
				WebFont.load({
				google: {families: [<?php echo( rtrim( $families, ',' ) ); ?>]},
				classes: false,
				events: false
				});
				} else {<?php // basically when we don't have the WebFont object we create the google script dynamically  ?>

				var tk = document.createElement('script');
				tk.src = '//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
				tk.type = 'text/javascript';

				tk.onload = tk.onreadystatechange = function () {
				WebFont.load({
				google: {families: [<?php echo( rtrim( $families, ',' ) ); ?>]},
				classes: false,
				events: false
				});
				};

				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(tk, s);
				}<?php
				$output = ob_get_clean();
			}

			return $output;
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

		/**
		 *
		 * @param $font_name
		 *
		 * @return null
		 */
		public function get_font_defaults_value( $font_name ) {

			if ( empty( $this->google_fonts ) ) {
				$this->load_google_fonts();
			}

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
			}

			return null;
		}

		/**
		 * Turn css options into a valid CSS output
		 *
		 * @param $option_id
		 * @param array $option_details
		 *
		 * @return string
		 */
		protected function convert_setting_to_css( $option_id, $option_details ) {
			$output = '';

			if ( empty( $option_details['css'] ) ) {
				return $output;
			}

			foreach ( $option_details['css'] as $css_property ) {

				if ( isset( $css_property['media'] ) && ! empty( $css_property['media'] ) ) {
					$this->media_queries[ $css_property['media'] ][ $option_id ] = array(
						'property' => $css_property,
						'value'    => $option_details['value']
					);
					continue;
				}

				if ( isset( $css_property['selector'] ) && isset( $css_property['property'] ) ) {
					$output .= $this->process_css_property( $css_property, $option_details['value'] );
				}
			}

			return $output;
		}

		protected function process_css_property( $css_property, $value ) {
			$unit = '';

			if ( isset( $css_property['unit'] ) ) {
				$unit = $css_property['unit'];
			}

			// if the unit isn't specified but the property should have a unit force 'px' as it
			if ( empty( $unit ) && in_array( $css_property['property'], self::$pixel_dependent_css_properties ) ) {
				$unit = 'px';
			}

			// lose the tons of tabs
			$css_property['selector'] = trim( preg_replace( '/\t+/', '', $css_property['selector'] ) );

			$css_property['selector'] = apply_filters( 'customify_css_selector', $css_property['selector'], $css_property );

			if ( empty( $css_property['selector'] ) ) {
				return '';
			}
			$property_output = $css_property['selector'] . ' { ' . $css_property['property'] . ': ' . $value . $unit . "; }" . PHP_EOL;

			// Handle the value filter callback.
			if ( isset( $css_property['filter_value_cb'] ) ) {
				$value = $this->maybe_apply_filter( $css_property['filter_value_cb'], $value );
			}

			// Handle output callback.
			if ( isset( $css_property['callback_filter'] ) && is_callable( $css_property['callback_filter'] ) ) {
				$property_output = call_user_func( $css_property['callback_filter'], $value, $css_property['selector'], $css_property['property'], $unit );
			}

			return $property_output;
		}

		/**
		 * Apply a filter (config) to a value.
		 *
		 * We currently handle filters like these:
		 *  // Elaborate filter config
		 *  array(
		 *      'callback' => 'is_post_type_archive',
		 *      // The arguments we should pass to the check function.
		 *      // Think post types, taxonomies, or nothing if that is the case.
		 *      // It can be an array of values or a single value.
		 *      'args' => array(
		 *          'jetpack-portfolio',
		 *      ),
		 *  ),
		 *  // Simple filter - just the function name
		 *  'is_404',
		 *
		 * @param array|string $filter
		 * @param mixed $value The value to apply the filter to.
		 *
		 * @return mixed The filtered value.
		 */
		public function maybe_apply_filter( $filter, $value ) {
			// Let's get some obvious things off the table.
			// On invalid data, we just return what we've received.
			if ( empty( $filter ) ) {
				return $value;
			}

			// First, we handle the shorthand version: just a function name
			if ( is_string( $filter ) && is_callable( $filter ) ) {
				$value = call_user_func( $filter );
			} elseif ( is_array( $filter ) && ! empty( $filter['callback'] ) && is_callable( $filter['callback'] ) ) {
				if ( empty( $filter['args'] ) ) {
					$filter['args'] = array();
				}
				// The value is always the first argument.
				$filter['args'] = array( $value ) + $filter['args'];

				$value = call_user_func_array( $filter['callback'], $filter['args'] );
			}

			return $value;
		}

		protected function process_custom_background_field_output( $option_details ) {
			$selector = $output = '';

			if ( ! isset( $option_details['value'] ) ) {
				return false;
			}
			$value = $option_details['value'];

			if ( ! isset( $option_details['output'] ) ) {
				return $selector;
			} elseif ( is_string( $option_details['output'] ) ) {
				$selector = $option_details['output'];
			} elseif ( is_array( $option_details['output'] ) ) {
				$selector = implode( ' ', $option_details['output'] );
			}

			// Loose the ton of tabs.
			$selector = trim( preg_replace( '/\t+/', '', $selector ) );

			$output .= $selector . " {";
			if ( isset( $value['background-image'] ) && ! empty( $value['background-image'] ) ) {
				$output .= "background-image: url( " . $value['background-image'] . ");";
			} else {
				$output .= "background-image: none;";
			}

			if ( isset( $value['background-repeat'] ) && ! empty( $value['background-repeat'] ) ) {
				$output .= "background-repeat:" . $value['background-repeat'] . ";";
			}

			if ( isset( $value['background-position'] ) && ! empty( $value['background-position'] ) ) {
				$output .= "background-position:" . $value['background-position'] . ";";
			}

			if ( isset( $value['background-size'] ) && ! empty( $value['background-size'] ) ) {
				$output .= "background-size:" . $value['background-size'] . ";";
			}

			if ( isset( $value['background-attachment'] ) && ! empty( $value['background-attachment'] ) ) {
				$output .= "background-attachment:" . $value['background-attachment'] . ";";
			}
			$output .= "}\n";

			return $output;
		}

		/**
		 * add our customizer styling edits into the wp_editor
		 */
		function script_to_add_customizer_settings_into_wp_editor() {

			ob_start();
			$this->output_typography_dynamic_script();
			$this->output_typography_dynamic_style();
			$this->output_dynamic_style();

			$custom_css = ob_get_clean();

			ob_start(); ?>
(function ($) {
	$(window).load(function () {
		/**
		* @param iframe_id the id of the frame you want to append the style
		* @param style_element the style element you want to append - boooom
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
<?php
			$script = ob_get_clean();
			wp_add_inline_script( 'editor', $script );

		}

		protected function register_customizer_controls() {

			// first get the base customizer extend class
			require_once( PixCustomifyPlugin()->get_base_path() . 'features/customizer/class-Pix_Customize_Control.php' );

			// now get all the controls
			$path = PixCustomifyPlugin()->get_base_path() . 'features/customizer/controls/';
			pixcustomify::require_all( $path );
		}

		/**
		 * Maybe process certain "commands" from the config.
		 *
		 * Mainly things like removing sections, controls, etc.
		 *
		 * @since 1.9.0
		 *
		 * @param WP_Customize_Manager $wp_customize
		 */
		public function maybe_process_config_extras( $wp_customize ) {
			$customizer_config = PixCustomifyPlugin()->get_customizer_config();

			// Bail if we have no external theme config data.
			if ( empty( $customizer_config ) || ! is_array( $customizer_config ) ) {
				return;
			}

			// Maybe remove panels
			if ( ! empty( $customizer_config['remove_panels'] ) ) {
				// Standardize it.
				if ( is_string( $customizer_config['remove_panels'] ) ) {
					$customizer_config['remove_panels'] = array( $customizer_config['remove_panels'] );
				}

				foreach ( $customizer_config['remove_panels'] as $panel_id ) {
					$wp_customize->remove_panel( $panel_id );
				}
			}

			// Maybe change panel props.
			if ( ! empty( $customizer_config['change_panel_props'] ) ) {
				foreach ( $customizer_config['change_panel_props'] as $panel_id => $panel_props ) {
					if ( ! is_array( $panel_props ) ) {
						continue;
					}

					$panel = $wp_customize->get_panel( $panel_id );
					if ( empty( $panel ) || ! $panel instanceof WP_Customize_Panel ) {
						continue;
					}

					$public_props = get_class_vars( get_class( $panel ) );
					foreach ( $panel_props as $prop_name => $prop_value ) {

						if ( ! in_array( $prop_name, array_keys( $public_props ) ) ) {
							continue;
						}

						$panel->$prop_name = $prop_value;
					}
				}
			}

			// Maybe remove sections
			if ( ! empty( $customizer_config['remove_sections'] ) ) {
				// Standardize it.
				if ( is_string( $customizer_config['remove_sections'] ) ) {
					$customizer_config['remove_sections'] = array( $customizer_config['remove_sections'] );
				}

				foreach ( $customizer_config['remove_sections'] as $section_id ) {

					if ( 'widgets' === $section_id ) {
						global $wp_registered_sidebars;

						foreach ( $wp_registered_sidebars as $widget => $settings ) {
							$wp_customize->remove_section( 'sidebar-widgets-' . $widget );
						}
						continue;
					}

					$wp_customize->remove_section( $section_id );
				}
			}

			// Maybe change section props.
			if ( ! empty( $customizer_config['change_section_props'] ) ) {
				foreach ( $customizer_config['change_section_props'] as $section_id => $section_props ) {
					if ( ! is_array( $section_props ) ) {
						continue;
					}

					$section = $wp_customize->get_section( $section_id );
					if ( empty( $section ) || ! $section instanceof WP_Customize_Section ) {
						continue;
					}

					$public_props = get_class_vars( get_class( $section ) );
					foreach ( $section_props as $prop_name => $prop_value ) {

						if ( ! in_array( $prop_name, array_keys( $public_props ) ) ) {
							continue;
						}

						$section->$prop_name = $prop_value;
					}
				}
			}

			// Maybe remove settings
			if ( ! empty( $customizer_config['remove_settings'] ) ) {
				// Standardize it.
				if ( is_string( $customizer_config['remove_settings'] ) ) {
					$customizer_config['remove_settings'] = array( $customizer_config['remove_settings'] );
				}

				foreach ( $customizer_config['remove_settings'] as $setting_id ) {
					$wp_customize->remove_setting( $setting_id );
				}
			}

			// Maybe change setting props.
			if ( ! empty( $customizer_config['change_setting_props'] ) ) {
				foreach ( $customizer_config['change_setting_props'] as $setting_id => $setting_props ) {
					if ( ! is_array( $setting_props ) ) {
						continue;
					}

					$setting = $wp_customize->get_setting( $setting_id );
					if ( empty( $setting ) || ! $setting instanceof WP_Customize_Setting ) {
						continue;
					}

					$public_props = get_class_vars( get_class( $setting ) );
					foreach ( $setting_props as $prop_name => $prop_value ) {

						if ( ! in_array( $prop_name, array_keys( $public_props ) ) ) {
							continue;
						}

						$setting->$prop_name = $prop_value;
					}
				}
			}

			// Maybe remove controls
			if ( ! empty( $customizer_config['remove_controls'] ) ) {
				// Standardize it.
				if ( is_string( $customizer_config['remove_controls'] ) ) {
					$customizer_config['remove_controls'] = array( $customizer_config['remove_controls'] );
				}

				foreach ( $customizer_config['remove_controls'] as $control_id ) {
					$wp_customize->remove_control( $control_id );
				}
			}

			// Maybe change control props.
			if ( ! empty( $customizer_config['change_control_props'] ) ) {
				foreach ( $customizer_config['change_control_props'] as $control_id => $control_props ) {
					if ( ! is_array( $control_props ) ) {
						continue;
					}

					$control = $wp_customize->get_control( $control_id );
					if ( empty( $control ) || ! $control instanceof WP_Customize_Control ) {
						continue;
					}

					$public_props = get_class_vars( get_class( $control ) );
					foreach ( $control_props as $prop_name => $prop_value ) {

						if ( ! in_array( $prop_name, array_keys( $public_props ) ) ) {
							continue;
						}

						$control->$prop_name = $prop_value;
					}
				}
			}
		}

		/**
		 * @param WP_Customize_Manager $wp_customize
		 */
		function register_customizer( $wp_customize ) {

			$this->register_customizer_controls();

			$customizer_settings = PixCustomifyPlugin()->get_customizer_config();

			if ( ! empty ( $customizer_settings ) ) {

				// first check the very needed options name
				if ( empty( $customizer_settings['opt-name'] ) ) {
					return;
				}
				$options_name              = $customizer_settings['opt-name'];
				$wp_customize->options_key = $options_name;

				// let's check if we have sections or panels
				if ( isset( $customizer_settings['panels'] ) && ! empty( $customizer_settings['panels'] ) ) {

					foreach ( $customizer_settings['panels'] as $panel_id => $panel_config ) {

						if ( ! empty( $panel_id ) && isset( $panel_config['sections'] ) && ! empty( $panel_config['sections'] ) ) {

							// If we have been explicitly given a panel ID we will use that
							if ( ! empty( $panel_config['panel_id'] ) ) {
								$panel_id = $panel_config['panel_id'];
							} else {
								$panel_id   = $options_name . '[' . $panel_id . ']';
							}

							$panel_args = array(
								'priority'                 => 10,
								'capability'               => 'edit_theme_options',
								'title'                    => __( 'Panel title is required', 'customify' ),
								'description'              => __( 'Description of what this panel does.', 'customify' ),
								'auto_expand_sole_section' => false,
							);

							if ( isset( $panel_config['priority'] ) && ! empty( $panel_config['priority'] ) ) {
								$panel_args['priority'] = $panel_config['priority'];
							}

							if ( isset( $panel_config['title'] ) && ! empty( $panel_config['title'] ) ) {
								$panel_args['title'] = $panel_config['title'];
							}

							if ( isset( $panel_config['description'] ) && ! empty( $panel_config['description'] ) ) {
								$panel_args['description'] = $panel_config['description'];
							}

							if ( isset( $panel_config['auto_expand_sole_section'] ) ) {
								$panel_args['auto_expand_sole_section'] = $panel_config['auto_expand_sole_section'];
							}


							$wp_customize->add_panel( $panel_id, $panel_args );

							foreach ( $panel_config['sections'] as $section_id => $section_config ) {
								if ( ! empty( $section_id ) && isset( $section_config['options'] ) && ! empty( $section_config['options'] ) ) {
									$this->register_section( $panel_id, $section_id, $options_name, $section_config, $wp_customize );
								}
							}
						}
					}
				}

				if ( isset( $customizer_settings['sections'] ) && ! empty( $customizer_settings['sections'] ) ) {

					foreach ( $customizer_settings['sections'] as $section_id => $section_config ) {
						if ( ! empty( $section_id ) && isset( $section_config['options'] ) && ! empty( $section_config['options'] ) ) {
							$this->register_section( $panel_id = false, $section_id, $options_name, $section_config, $wp_customize );
						}
					}
				}

				if ( PixCustomifyPlugin()->get_plugin_setting('enable_reset_buttons') ) {
					// create a toolbar section which will be present all the time
					$reset_section_settings = array(
						'title'   => 'Customify Toolbox',
						'capability' => 'manage_options',
						'priority' => 999999999,
						'options' => array(
							'reset_all_button' => array(
								'type'   => 'button',
								'label'  => 'Reset Customify',
								'action' => 'reset_customify',
								'value'  => 'Reset'
							),
						)
					);

					$wp_customize->add_section(
						'customify_toolbar',
						$reset_section_settings
					);

					$wp_customize->add_setting(
						'reset_customify',
						array()
					);
					$wp_customize->add_control( new Pix_Customize_Button_Control(
						$wp_customize,
						'reset_customify',
						array(
							'label'    => __( 'Reset All Customify Options to Default', 'customify' ),
							'section'  => 'customify_toolbar',
							'settings' => 'reset_customify',
							'action'   => 'reset_customify',
						)
					) );
				}
			}

			do_action( 'customify_create_custom_control', $wp_customize );
		}

		/**
		 * @param string $panel_id
		 * @param string $section_key
		 * @param string $options_name
		 * @param array $section_config
		 * @param WP_Customize_Manager $wp_customize
		 */
		protected function register_section( $panel_id, $section_key, $options_name, $section_config, $wp_customize ) {

			if ( isset( $this->plugin_settings['disable_customify_sections'] ) && isset( $this->plugin_settings['disable_customify_sections'][ $section_key ] ) ) {
				return;
			}

			// If we have been explicitly given a section ID we will use that
			if ( ! empty( $section_config['section_id'] ) ) {
				$section_id = $section_config['section_id'];
			} else {
				$section_id = $options_name . '[' . $section_key . ']';
			}

			// Add the new section to the Customizer, but only if it is not already added.
			if ( ! $wp_customize->get_section( $section_id ) ) {
				// Merge the section settings with the defaults
				$section_args = wp_parse_args( $section_config, array(
					'priority'   => 10,
					'panel'      => $panel_id,
					'capability' => 'edit_theme_options',
					'theme_supports' => '',
					'title'      => esc_html__( 'Title Section is required', 'customify' ),
					'description' => '',
					'type' => 'default',
					'description_hidden' => false,
				) );

				$wp_customize->add_section( $section_id, $section_args );
			}

			// Now go through each section option and add the fields
			foreach ( $section_config['options'] as $option_id => $option_config ) {

				if ( empty( $option_id ) || ! isset( $option_config['type'] ) ) {
					continue;
				}

				// If we have been explicitly given a setting ID we will use that
				if ( ! empty( $option_config['setting_id'] ) ) {
					$setting_id = $option_config['setting_id'];
				} else {
					$setting_id = $options_name . '[' . $option_id . ']';
				}

				// Add the option config to the localized array so we can pass the info to JS.
				// @todo Maybe we should ensure that the connected_fields configs passed here follow the same format and logic as the ones in ::customize_pane_settings_additional_data() thus maybe having the data in the same place.
				$this->localized['settings'][ $setting_id ] = $option_config;

				// Generate a safe option ID (not the final setting ID) to us in HTML attributes like ID or class
				$this->localized['settings'][ $setting_id ]['html_safe_option_id'] = sanitize_html_class( $option_id );

				$this->register_field( $section_id, $setting_id, $option_config, $wp_customize );
			}

		}

		/**
		 * Register a Customizer field (setting and control).
		 *
		 * @see WP_Customize_Setting
		 * @see WP_Customize_Control
		 *
		 * @param string $section_id
		 * @param string $setting_id
		 * @param array $field_config
		 * @param WP_Customize_Manager $wp_customize
		 */
		protected function register_field( $section_id, $setting_id, $field_config, $wp_customize ) {

			$add_control = true;
			// defaults
			$setting_args = array(
				'default'    => '',
				'capability' => 'edit_theme_options',
				'transport'  => 'refresh',
			);
			$control_args = array(
				'priority' => 10,
				'label'    => '',
				'section'  => $section_id,
				'settings' => $setting_id,
			);

			// sanitize settings
			if ( ! empty( $field_config['live'] ) || $field_config['type'] === 'font' ) {
				$setting_args['transport'] = 'postMessage';
			}

			if ( isset( $field_config['default'] ) ) {
				$setting_args['default'] = $field_config['default'];
			}

			if ( ! empty( $field_config['capability'] ) ) {
				$setting_args['capability'] = $field_config['capability'];
			}

			// If the setting defines it's own type we will respect that, otherwise we will follow the global plugin setting.
			if ( ! empty( $field_config['setting_type'] ) ) {
				if ( 'option' === $field_config['setting_type'] ) {
					$setting_args['type'] = 'option';
				} else {
					$setting_args['type'] = 'theme_mod';
				}
			} elseif ( PixCustomifyPlugin()->get_plugin_setting('values_store_mod') === 'option' ) {
				$setting_args['type'] = 'option';
			}

			// if we arrive here this means we have a custom field control
			switch ( $field_config['type'] ) {

				case 'checkbox':

					$setting_args['sanitize_callback'] = array( $this, 'setting_sanitize_checkbox' );
					break;

				default:
					break;
			}

			if ( ! empty( $field_config['sanitize_callback'] ) && is_callable( $field_config['sanitize_callback'] ) ) {
				$setting_args['sanitize_callback'] = $field_config['sanitize_callback'];
			}

			// Add the setting
			$wp_customize->add_setting( $setting_id, $setting_args );

			// Stop the control registration, if we are presented with the right type.
			if ( 'hidden_control' === $field_config['type'] ) {
				return;
			}

			$control_args['type'] = $field_config['type'];

			// now sanitize the control
			if ( ! empty( $field_config['label'] ) ) {
				$control_args['label'] = $field_config['label'];
			}

			if ( ! empty( $field_config['priority'] ) ) {
				$control_args['priority'] = $field_config['priority'];
			}

			if ( ! empty( $field_config['desc'] ) ) {
				$control_args['description'] = $field_config['desc'];
			}

			if ( ! empty( $field_config['active_callback'] ) ) {
				$control_args['active_callback'] = $field_config['active_callback'];
			}

			// select the control type
			// but first init a default
			$control_class_name = 'Pix_Customize_Text_Control';

			// If is a standard wp field type call it here and skip the rest.
			if ( in_array( $field_config['type'], array(
				'checkbox',
				'dropdown-pages',
				'url',
				'date',
				'time',
				'datetime',
				'week',
				'search'
			) ) ) {
				$wp_customize->add_control( $setting_id . '_control', $control_args );

				return;
			} elseif ( in_array( $field_config['type'], array(
					'radio',
					'select'
				) ) && ! empty( $field_config['choices'] )
			) {
				$control_args['choices'] = $field_config['choices'];
				$wp_customize->add_control( $setting_id . '_control', $control_args );

				return;
			} elseif ( in_array( $field_config['type'], array( 'range' ) ) && ! empty( $field_config['input_attrs'] ) ) {

				$control_args['input_attrs'] = $field_config['input_attrs'];

				$wp_customize->add_control( $setting_id . '_control', $control_args );
			}

			// If we arrive here this means we have a custom field control.
			switch ( $field_config['type'] ) {

				case 'text':
					if ( isset( $field_config['live'] ) ) {
						$control_args['live'] = $field_config['live'];
					}

					$control_class_name = 'Pix_Customize_Text_Control';
					break;

				case 'textarea':
					if ( isset( $field_config['live'] ) ) {
						$control_args['live'] = $field_config['live'];
					}

					$control_class_name = 'Pix_Customize_Textarea_Control';
					break;

				case 'color':
					$control_class_name = 'WP_Customize_Color_Control';
					break;

				case 'color_drop':
					$control_class_name = 'Pix_Customize_Color_Drop_Control';
					break;

				case 'ace_editor':
					if ( isset( $field_config['live'] ) ) {
						$control_args['live'] = $field_config['live'];
					}

					if ( isset( $field_config['editor_type'] ) ) {
						$control_args['editor_type'] = $field_config['editor_type'];
					}

					$control_class_name = 'Pix_Customize_Ace_Editor_Control';
					break;

				case 'upload':
					$control_class_name = 'WP_Customize_Upload_Control';
					break;

				case 'image':
					$control_class_name = 'WP_Customize_Image_Control';
					break;

				case 'media':
					$control_class_name = 'WP_Customize_Media_Control';
					break;

				case 'custom_background':
					if ( isset( $field_config['field'] ) ) {
						$control_args['field'] = $field_config['field'];
					}

					$control_class_name = 'Pix_Customize_Background_Control';
					break;

				case 'cropped_image':
				case 'cropped_media': // 'cropped_media' no longer works
					if ( isset( $field_config['width'] ) ) {
						$control_args['width'] = $field_config['width'];
					}

					if ( isset( $field_config['height'] ) ) {
						$control_args['height'] = $field_config['height'];
					}

					if ( isset( $field_config['flex_width'] ) ) {
						$control_args['flex_width'] = $field_config['flex_width'];
					}

					if ( isset( $field_config['flex_height'] ) ) {
						$control_args['flex_height'] = $field_config['flex_height'];
					}

					if ( isset( $field_config['button_labels'] ) ) {
						$control_args['button_labels'] = $field_config['button_labels'];
					}

					$control_class_name = 'WP_Customize_Cropped_Image_Control';
					break;

				// Custom types
				case 'typography' :
					$use_typography = PixCustomifyPlugin()->get_plugin_setting( 'typography', '1' );

					if ( $use_typography === false ) {
						$add_control = false;
						continue;
					}

					$control_class_name = 'Pix_Customize_Typography_Control';

					if ( isset( $field_config['backup'] ) ) {
						$control_args['backup'] = $field_config['backup'];
					}

					if ( isset( $field_config['font_weight'] ) ) {
						$control_args['font_weight'] = $field_config['font_weight'];
					}

					if ( isset( $field_config['subsets'] ) ) {
						$control_args['subsets'] = $field_config['subsets'];
					}

					if ( isset( $field_config['recommended'] ) ) {
						$control_args['recommended'] = array_flip( $field_config['recommended'] );
					}

					if ( isset( $field_config['load_all_weights'] ) ) {
						$control_args['load_all_weights'] = $field_config['load_all_weights'];
					}

					if ( isset( $field_config['default'] ) ) {
						$control_args['default'] = $field_config['default'];
					}

					break;

				case 'font' :
					$use_typography = PixCustomifyPlugin()->get_plugin_setting( 'typography', '1' );

					if ( $use_typography === false ) {
						$add_control = false;
						continue;
					}

					$control_class_name = 'Pix_Customize_Font_Control';

					if ( isset( $field_config['backup'] ) ) {
						$control_args['backup'] = $field_config['backup'];
					}

					if ( isset( $field_config['font_weight'] ) ) {
						$control_args['font_weight'] = $field_config['font_weight'];
					}

					if ( isset( $field_config['subsets'] ) ) {
						$control_args['subsets'] = $field_config['subsets'];
					}

					if ( isset( $field_config['recommended'] ) ) {
						$control_args['recommended'] = array_flip( $field_config['recommended'] );
					}

					if ( isset( $field_config['load_all_weights'] ) ) {
						$control_args['load_all_weights'] = $field_config['load_all_weights'];
					}

					if ( isset( $field_config['default'] ) ) {
						$control_args['default'] = $field_config['default'];
					}

					if ( isset( $field_config['fields'] ) ) {
						$control_args['fields'] = $field_config['fields'];
					}
					$control_args['live'] = true;

					break;

				case 'select2' :
					if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
						return;
					}

					$control_args['choices'] = $field_config['choices'];

					$control_class_name = 'Pix_Customize_Select2_Control';
					break;

				case 'sm_radio' :
					if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
						return;
					}

					$control_args['choices'] = $field_config['choices'];

					$control_class_name = 'Pix_Customize_SM_radio_Control';
					break;

				case 'sm_palette_filter' :
					if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
						return;
					}

					$control_args['choices'] = $field_config['choices'];

					$control_class_name = 'Pix_Customize_SM_palette_filter_Control';
					break;

				case 'sm_switch' :
					if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
						return;
					}

					$control_args['choices'] = $field_config['choices'];

					$control_class_name = 'Pix_Customize_SM_switch_Control';
					break;

				case 'preset' :
					if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
						return;
					}

					$control_args['choices'] = $field_config['choices'];

					if ( isset( $field_config['choices_type'] ) || ! empty( $field_config['choices_type'] ) ) {
						$control_args['choices_type'] = $field_config['choices_type'];
					}

					if ( isset( $field_config['desc'] ) || ! empty( $field_config['desc'] ) ) {
						$control_args['description'] = $field_config['desc'];
					}


					$control_class_name = 'Pix_Customize_Preset_Control';
					break;

				case 'radio_image' :
					if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
						return;
					}

					$control_args['choices'] = $field_config['choices'];

					if ( isset( $field_config['choices_type'] ) || ! empty( $field_config['choices_type'] ) ) {
						$control_args['choices_type'] = $field_config['choices_type'];
					}

					if ( isset( $field_config['desc'] ) || ! empty( $field_config['desc'] ) ) {
						$control_args['description'] = $field_config['desc'];
					}


					$control_class_name = 'Pix_Customize_Radio_Image_Control';
					break;

				case 'button' :
					if ( ! isset( $field_config['action'] ) || empty( $field_config['action'] ) ) {
						return;
					}

					$control_args['action'] = $field_config['action'];

					$control_class_name = 'Pix_Customize_Button_Control';

					break;

				case 'html' :
					if ( isset( $field_config['html'] ) || ! empty( $field_config['html'] ) ) {
						$control_args['html'] = $field_config['html'];
					}

					$control_class_name = 'Pix_Customize_HTML_Control';
					break;

				case 'import_demo_data' :
					if ( isset( $field_config['html'] ) || ! empty( $field_config['html'] ) ) {
						$control_args['html'] = $field_config['html'];
					}

					if ( ! isset( $field_config['label'] ) || empty( $field_config['label'] ) ) {
						$control_args['label'] = esc_html__( 'Import', 'customify' );
					} else {
						$control_args['label'] = $field_config['label'];
					}

					if ( isset( $field_config['notices'] ) && ! empty( $field_config['notices'] ) ) {
						$control_args['notices'] = $field_config['notices'];
					}

					$control_class_name = 'Pix_Customize_Import_Demo_Data_Control';
					break;

				default:
					// if we don't have a real control just quit, it doesn't even matter
					return;
					break;
			}

			$this_control = new $control_class_name(
				$wp_customize,
				$setting_id . '_control',
				$control_args
			);

			if ( $add_control ) {
				$wp_customize->add_control( $this_control );
			}
		}

		/**
		 * Remove the sections selected by user
		 *
		 * @param WP_Customize_Manager $wp_customize
		 */
		function remove_default_sections( $wp_customize ) {
			global $wp_registered_sidebars;

			$to_remove = PixCustomifyPlugin()->get_plugin_setting( 'disable_default_sections' );

			if ( ! empty( $to_remove ) ) {
				foreach ( $to_remove as $section => $nothing ) {

					if ( $section === 'widgets' ) {
						foreach ( $wp_registered_sidebars as $widget => $settings ) {
							$wp_customize->remove_section( 'sidebar-widgets-' . $widget );
						}
						continue;
					}

					$wp_customize->remove_section( $section );
				}
			}
		}

		/**
		 * Print JavaScript for adding additional data to _wpCustomizeSettings.settings object of the main window (not the preview window).
		 */
		public function customize_pane_settings_additional_data() {
			/**
			 * @global WP_Customize_Manager $wp_customize
			 */
			global $wp_customize;

			$options_name = PixCustomifyPlugin()->get_options_key();
			// Without an options name we can't do much.
			if ( empty( $options_name ) ) {
				return;
			}

			$customizer_settings = $wp_customize->settings();
			?>
			<script type="text/javascript">
              if ( 'undefined' === typeof _wpCustomizeSettings.settings ) {
                _wpCustomizeSettings.settings = {};
              }

			  <?php
			  echo "(function ( sAdditional ){\n";

			  $options = PixCustomifyPlugin()->get_options_details();
			  foreach ( $options as $option_id => $option_config ) {
				  // If we have been explicitly given a setting ID we will use that
				  if ( ! empty( $option_config['setting_id'] ) ) {
					  $setting_id = $option_config['setting_id'];
				  } else {
					  $setting_id = $options_name . '[' . $option_id . ']';
				  }
				  // @todo Right now we only handle the connected_fields key - make this more dynamic by adding the keys that are not returned by WP_Customize_Setting->json()
				  if ( ! empty( $customizer_settings[ $setting_id ] ) && ! empty( $option_config['connected_fields'] ) ) {
					  // Pass through all the connected fields and make sure the id is in the final format
					  $connected_fields = array();
					  foreach ( $option_config['connected_fields'] as $key => $connected_field_config ) {
						  $connected_field_data = array();

						  if ( is_string( $connected_field_config ) ) {
							  $connected_field_id = $connected_field_config;
						  } elseif ( is_array( $connected_field_config ) ) {
							  // We have a full blown connected field config
							  if ( is_string( $key ) ) {
								  $connected_field_id = $key;
							  } else {
								  continue;
							  }

							  // We will pass to JS all the configured connected field details.
							  $connected_field_data = $connected_field_config;
						  }

						  // Continue if we don't have a connected field ID to work with.
						  if ( empty( $connected_field_id ) ) {
							  continue;
						  }

						  // If the connected setting is not one of our's, we will use it's ID as it is.
						  if ( ! array_key_exists( $connected_field_id, $options ) ) {
							  $connected_field_data['setting_id'] = $connected_field_id;
						  }
						  // If the connected setting specifies a setting ID, we will not prefix it and use it as it is.
						  elseif ( ! empty( $options[ $connected_field_id ] ) && ! empty( $options[ $connected_field_id ]['setting_id'] ) ) {
							  $connected_field_data['setting_id'] = $options[ $connected_field_id ]['setting_id'];
						  } else {
							  $connected_field_data['setting_id'] = $options_name . '[' . $connected_field_id . ']';
						  }

						  $connected_fields[] = $connected_field_data;
					  }

					  printf(
						  "sAdditional[%s].%s = %s;\n",
						  wp_json_encode( $setting_id ),
						  'connected_fields',
						  wp_json_encode( $connected_fields, JSON_FORCE_OBJECT )
					  );
				  }
			  }
			  echo "})( _wpCustomizeSettings.settings );\n";
			  ?>
			</script>
			<?php
		}

		public function get_typography_fields( $fields_config, $key, $value, &$results, $input_key = 0 ) {
			if ( ! is_array( $fields_config ) ) {
				return;
			}

			if ( isset( $fields_config[ $key ] ) && $fields_config[ $key ] == $value ) {
				$results[ $input_key ] = $fields_config;

				$default = null;

				if ( isset( $fields_config['default'] ) && is_array( $fields_config['default'] ) ) {
					$default = json_encode( $fields_config['default'] );
				}

				$results[ $input_key ]['value'] = PixCustomifyPlugin()->get_option( $input_key, $default );
			}

			foreach ( $fields_config as $i => $subarray ) {
				$this->get_typography_fields( $subarray, $key, $value, $results, $i );
			}
		}

		/**
		 * Main Customify_Customizer Instance
		 *
		 * Ensures only one instance of Customify_Customizer is loaded or can be loaded.
		 *
		 * @since  2.4.0
		 * @static
		 *
		 * @return Customify_Customizer Main Customify_Customizer instance
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
		 * @since 2.4.0
		 */
		public function __clone() {

			_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?' ) ), null );
		} // End __clone ()

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 2.4.0
		 */
		public function __wakeup() {

			_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ),  null );
		} // End __wakeup ()
	}

endif;
