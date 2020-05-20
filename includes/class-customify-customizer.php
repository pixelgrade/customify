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

if ( ! class_exists( 'PixCustomify_Customizer' ) ) :

	class PixCustomify_Customizer {

		/**
		 * Holds the only instance of this class.
		 * @var     null|PixCustomify_Customizer
		 * @access  protected
		 * @since   2.4.0
		 */
		protected static $_instance = null;

		protected $localized = array();

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
			// Others will be able to add data here via the 'customify_localized_js_settings' filter.
			// This is a just-in-time filter, triggered as late as possible.
			$this->localized  = array(
				'config' => array(
					'options_name' => PixCustomifyPlugin()->get_options_key(),
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'webfontloader_url' => plugins_url( 'js/vendor/webfontloader-1-6-28.min.js', PixCustomifyPlugin()->get_file() ),
					'px_dependent_css_props' => self::$pixel_dependent_css_properties,
				),
				// For localizing strings.
				'l10n' => array(
					'panelResetButton' => esc_html__( 'Panel\'s defaults', 'customify' ),
					'sectionResetButton' => esc_html__( 'Reset All Options for This Section', 'customify' ),
					'resetGlobalConfirmMessage' => wp_kses_post( __( 'Do you really want to reset to defaults all the fields? Watch out, this will reset all your Customify options and will save them!', 'customify' ) ),
					'resetPanelConfirmMessage' => wp_kses_post( __( 'Do you really want to reset the settings in this panel?', 'customify' ) ),
					'resetSectionConfirmMessage' => wp_kses_post( __( 'Do you really want to reset the settings in this section?', 'customify' ) ),
				)
			);

			// Hook up.
			$this->add_hooks();
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
			$load_location = PixCustomifyPlugin()->settings->get_plugin_setting( 'style_resources_location', 'wp_head' );

			add_action( $load_location, array( $this, 'output_dynamic_style' ), 99 );

			add_action( 'customize_register', array( $this, 'remove_default_sections' ), 11 );
			add_action( 'customize_register', array( $this, 'process_customizer_config' ), 12 );
			// Maybe the theme has instructed us to do things like removing sections or controls.
			add_action( 'customize_register', array( $this, 'maybe_process_config_extras' ), 13 );

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
			$rtl_suffix = is_rtl() ? '-rtl' : '';
			wp_register_style( 'customify_style', plugins_url( 'css/customizer' . $rtl_suffix . '.css', PixCustomifyPlugin()->get_file() ), array( 'dashicons' ), PixCustomifyPlugin()->get_version() );
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
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_register_script( PixCustomifyPlugin()->get_slug() . '-select2',
				plugins_url( 'js/vendor/select2-4.0.13/dist/js/select2.full' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
				array( 'jquery' ), PixCustomifyPlugin()->get_version() );
			wp_register_script( 'jquery-react',
				plugins_url( 'js/vendor/jquery-react' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
				array( 'jquery' ), PixCustomifyPlugin()->get_version() );
			wp_register_script( PixCustomifyPlugin()->get_slug() . '-fontfields',
				plugins_url( 'js/customizer/font-fields' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
				array( 'jquery', 'underscore' ), PixCustomifyPlugin()->get_version() );

			wp_register_script( PixCustomifyPlugin()->get_slug() . '-customizer-scripts',
				plugins_url( 'js/customizer' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
				array(
					'jquery',
					PixCustomifyPlugin()->get_slug() . '-select2',
					'underscore',
					'customize-controls',
					PixCustomifyPlugin()->get_slug() . '-fontfields',
				),
				PixCustomifyPlugin()->get_version() );
		}

		/**
		 * Enqueue Customizer admin scripts
		 */
		function enqueue_admin_customizer_scripts() {
			wp_enqueue_script( 'jquery-react' );
			wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-customizer-scripts' );

			wp_add_inline_script( PixCustomifyPlugin()->get_slug() . '-customizer-scripts',
				self::getlocalizeToWindowScript( 'customify',
					apply_filters( 'customify_localized_js_settings', $this->localized )
				), 'before' );
		}

		/** Register Customizer scripts loaded only on previewer page */
		function customizer_live_preview_register_scripts() {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_register_script( PixCustomifyPlugin()->get_slug() . '-CSSOM',
				plugins_url( 'js/vendor/CSSOM' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
				array(),
				PixCustomifyPlugin()->get_version(), true );

			wp_register_script( PixCustomifyPlugin()->get_slug() . '-cssUpdate',
				plugins_url( 'js/jquery.cssUpdate' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
				array( 'jquery' ),
				PixCustomifyPlugin()->get_version(), true );

			wp_register_script( PixCustomifyPlugin()->get_slug() . '-previewer-scripts',
				plugins_url( 'js/customizer_preview' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
				array(
					'jquery',
					'customize-preview',
					'underscore',
					PixCustomifyPlugin()->get_slug() . '-CSSOM',
					PixCustomifyPlugin()->get_slug() . '-cssUpdate'
				),
				PixCustomifyPlugin()->get_version(), true );
		}

		/** Enqueue Customizer scripts loaded only on previewer page */
		function customizer_live_preview_enqueue_scripts() {
			wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-previewer-scripts' );
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
                      message: '<?php echo wp_kses_post( __( '<strong>Customify: Development Mode</strong><p>All options are switched to default. While they are changing in the live preview, they will not be kept when you hit publish.</p>', 'customify' ) ); ?>'
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

			foreach ( PixCustomifyPlugin()->get_options_details( false, true ) as $option_id => $option_details ) {

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

			foreach ( PixCustomifyPlugin()->get_options_details( true ) as $option_id => $option_details ) {

				if ( isset( $option_details['css'] ) && ! empty( $option_details['css'] ) ) {
					// now process each
					$custom_css .= $this->convert_setting_to_css( $option_id, $option_details );
				}

				if ( isset( $option_details['type'] ) && $option_details['type'] === 'custom_background' ) {
					$custom_css .= $this->process_custom_background_field_output( $option_details ) . "\n";
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
							$media_query_custom_css .= "\t" . $css_output . "\n";
						}
					}

					if ( ! empty( $media_query_custom_css ) ) {
						$media_query_custom_css = "\n" . '@media ' . $media_query . " { " . "\n" . "\n" . $media_query_custom_css . "}" . "\n";
					}

					if ( ! empty( $media_query_custom_css ) ) {
						$custom_css .= $media_query_custom_css;
					}

				}
			}

			return apply_filters( 'customify_dynamic_style', $custom_css );
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
			$unit = isset( $css_property['unit'] ) ? $css_property['unit'] : '';
			// If the unit is empty (string, not boolean false) but the property should have a unit force 'px' as it
			if ( '' === $unit && in_array( $css_property['property'], self::$pixel_dependent_css_properties ) ) {
				$unit = 'px';
			}

			$css_property['selector'] = apply_filters( 'customify_css_selector', $this->cleanup_whitespace_css( $css_property['selector'] ), $css_property );
			if ( empty( $css_property['selector'] ) ) {
				return '';
			}

			$property_output = $css_property['selector'] . ' { ' . $css_property['property'] . ': ' . $value . $unit . "; }" . "\n";

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

		protected function load_customizer_controls() {

			// First require the base customizer extend class.
			require_once( trailingslashit( PixCustomifyPlugin()->get_base_path() ) . 'includes/customizer-controls/class-Pix_Customize_Control.php' );

			// Now load all the controls' files.
			$path = apply_filters( 'customify_customizer_controls_path', trailingslashit( PixCustomifyPlugin()->get_base_path() ) . 'includes/customizer-controls/' );
			pixcustomify::require_all( $path );

			do_action( 'customify_loaded_customizer_controls' );
		}

		/**
		 * Register all the panels, sections and controls we receive through the plugin's config.
		 *
		 * @param WP_Customize_Manager $wp_customize
		 */
		function process_customizer_config( $wp_customize ) {

			do_action( 'customify_before_process_customizer_config', $wp_customize );

			// Require all the control classes available.
			$this->load_customizer_controls();

			// Load the customizer config.
			$customizer_config = apply_filters( 'customify_customizer_config_pre_processing', PixCustomifyPlugin()->get_customizer_config(), $wp_customize );

			// Bail if we don't have a config, or we are missing the 'opt-name' entry.
			if ( empty( $customizer_config ) || empty( $customizer_config['opt-name'] ) ) {
				do_action( 'customify_skip_process_customizer_config', $customizer_config, $wp_customize );
				return;
			}

			$options_name              = $customizer_config['opt-name'];
			$wp_customize->options_key = $options_name;

			// Handle panels.
			if ( isset( $customizer_config['panels'] ) && ! empty( $customizer_config['panels'] ) ) {

				foreach ( $customizer_config['panels'] as $panel_id => $panel_config ) {

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
							'title'                    => esc_html__( 'Panel title is required', 'customify' ),
							'description'              => esc_html__( 'Description of what this panel does.', 'customify' ),
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


						$panel = $wp_customize->add_panel( $panel_id, $panel_args );
						// Fire a general added panel hook
						do_action( 'customify_process_customizer_config_added_panel', $panel, $panel_args, $wp_customize );

						foreach ( $panel_config['sections'] as $section_id => $section_config ) {
							if ( ! empty( $section_id ) && isset( $section_config['options'] ) && ! empty( $section_config['options'] ) ) {
								$this->register_section( $panel_id, $section_id, $options_name, $section_config, $wp_customize );
							}
						}

						// Fire a general finished panel hook
						do_action( 'customify_process_customizer_config_finished_panel', $panel, $panel_args, $wp_customize );
					}
				}
			}

			// Handle sections.
			if ( isset( $customizer_config['sections'] ) && ! empty( $customizer_config['sections'] ) ) {

				foreach ( $customizer_config['sections'] as $section_id => $section_config ) {
					if ( ! empty( $section_id ) && isset( $section_config['options'] ) && ! empty( $section_config['options'] ) ) {
						$this->register_section( $panel_id = false, $section_id, $options_name, $section_config, $wp_customize );
					}
				}
			}

			// Handle development helper buttons.
			if ( PixCustomifyPlugin()->settings->get_plugin_setting('enable_reset_buttons') ) {
				// create a toolbar section which will be present all the time
				$reset_section_settings = array(
					'title'   => esc_html__( 'Customify Toolbox', 'customify' ),
					'capability' => 'manage_options',
					'priority' => 999999999,
					'options' => array(
						'reset_all_button' => array(
							'type'   => 'button',
							'label'  => esc_html__( 'Reset Customify', 'customify' ),
							'action' => 'reset_customify',
							'value'  => 'Reset',
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
						'label'    => esc_html__( 'Reset All Customify Options to Default', 'customify' ),
						'section'  => 'customify_toolbar',
						'settings' => 'reset_customify',
						'action'   => 'reset_customify',
					)
				) );
			}

			do_action( 'customify_after_process_customizer_config', $wp_customize );
		}

		/**
		 * @param string $panel_id
		 * @param string $section_key
		 * @param string $options_name
		 * @param array $section_config
		 * @param WP_Customize_Manager $wp_customize
		 */
		protected function register_section( $panel_id, $section_key, $options_name, $section_config, $wp_customize ) {

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

				// Only add the section if it is not of type `hidden`
				if ( 'hidden' !== $section_args['type'] ) {
					$section = $wp_customize->add_section( $section_id, $section_args );
					// Fire a general added section hook
					do_action( 'customify_process_customizer_config_added_section', $section, $section_args, $wp_customize );
				}
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

				// Filter some settings that have purely visual purpose.
				if ( ! empty( $option_config['type'] ) && ! in_array( $option_config['type'], array( 'html', 'button' ) ) ) {
					$this->localized['config']['settings'][ $setting_id ] = $option_config;
				}

				// Generate a safe option ID (not the final setting ID) to us in HTML attributes like ID or class
				$this->localized['config']['settings'][ $setting_id ]['html_safe_option_id'] = sanitize_html_class( $option_id );

				$this->register_field( $section_id, $setting_id, $option_config, $wp_customize );
			}

			// Fire a general finished section hook
			do_action( 'customify_process_customizer_config_finished_section', $section_id, $wp_customize );
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
                'type'       => 'theme_mod',
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
				if ( is_array( $setting_args['default'] ) ) {
					$setting_args['default'] = (object) $setting_args['default'];
				}
			}

			if ( ! empty( $field_config['capability'] ) ) {
				$setting_args['capability'] = $field_config['capability'];
			}

			// If the setting defines it's own type we will respect that, otherwise we will follow the global plugin setting.
			if ( ! empty( $field_config['setting_type'] ) && 'option' === $field_config['setting_type'] ) {
                $setting_args['type'] = 'option';
			} elseif ( PixCustomifyPlugin()->settings->get_plugin_setting('values_store_mod') === 'option' ) {
				$setting_args['type'] = 'option';
			}

			if ( ! empty( $field_config['sanitize_callback'] ) && is_callable( $field_config['sanitize_callback'] ) ) {
				$setting_args['sanitize_callback'] = $field_config['sanitize_callback'];
			} elseif ( 'checkbox' === $field_config['type'] ) {
				$setting_args['sanitize_callback'] = array( $this, 'setting_sanitize_checkbox' );
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

			// If we arrive here this means we have a custom field control (with a corresponding class in includes/customizer-controls).
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

				case 'font' :

					// Only add the control if typography is turned on in the plugin settings.
					if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography', '1' ) ) {
						$add_control = false;
						break;
					}

					$control_class_name = 'Pix_Customize_Font_Control';

					if ( isset( $field_config['recommended'] ) ) {
						$control_args['recommended'] = $field_config['recommended'];
					}

					if ( isset( $field_config['live'] ) ) {
						$control_args['live'] = $field_config['live'];
					}

					// This is used only as an extreme failsafe.
					// Normally, when there is no value, the WP Settings system will fallback on the default given for the setting.
					// See above when registering the setting corresponding to this control.
					if ( isset( $field_config['default'] ) ) {
						$control_args['default'] = $field_config['default'];
					}

					// We should always receive a subfields configuration.
					if ( isset( $field_config['fields'] ) ) {
						$control_args['fields'] = $field_config['fields'];
					} else {
						$control_args['fields'] = array();
					}

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

                case 'radio_html' :
	                if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
		                return;
	                }

	                $control_args['choices'] = $field_config['choices'];

	                if ( isset( $field_config['desc'] ) || ! empty( $field_config['desc'] ) ) {
		                $control_args['description'] = $field_config['desc'];
	                }

	                $control_class_name = 'Pix_Customize_Radio_HTML_Control';
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

			$to_remove = PixCustomifyPlugin()->settings->get_plugin_setting( 'disable_default_sections' );

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

		/* HELPERS */

		/**
		 * Cleanup stuff like tab characters.
		 *
		 * @param string $string
		 *
		 * @return string
		 */
		function cleanup_whitespace_css( $string ) {
			$string = normalize_whitespace( $string );

			return $string;
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

				$results[ $input_key ]['value'] = PixCustomifyPlugin()->get_option( $input_key, $default );
			}

			foreach ( $fields_config as $i => $subarray ) {
				$this->get_fields_by_key( $subarray, $key, $value, $results, $i );
			}
		}

		/**
		 * Return a script for flexibly localizing data to a window property.
		 *
		 * Unlike wp_localize_script() that simply creates a variable and assigns it the value,
		 * thus overwriting anything that may have been in that variable, we will output a script that
		 * will test if the variable exists and only overwrite the first level nodes, not everything.
		 *
		 * @since 2.7.0
		 *
		 * @param string $object_name Name of the variable that will contain the data.
		 * @param array  $l10n        Array of data to localize.
		 *
		 * @return bool True on success, false on failure.
		 */
		public static function getlocalizeToWindowScript( $object_name, $l10n ) {
			$script = "window.$object_name = window.$object_name || parent.$object_name || {};\n";

			foreach ( (array) $l10n as $key => $value ) {
				if ( is_scalar( $value ) ) {
					$value = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
				}

				$script .= "$object_name.$key = " . wp_json_encode( $value ) . ";\n";
			}

			return $script;
		}

		/* SANITIZATION HELPERS */

		/**
		 * Sanitize the checkbox.
		 *
		 * @param boolean $input .
		 *
		 * @return boolean true if is 1 or '1', false if anything else
		 */
		function setting_sanitize_checkbox( $input ) {
			if ( 1 == $input ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Main PixCustomify_Customizer Instance
		 *
		 * Ensures only one instance of PixCustomify_Customizer is loaded or can be loaded.
		 *
		 * @since  2.4.0
		 * @static
		 *
		 * @return PixCustomify_Customizer Main PixCustomify_Customizer instance
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

			_doing_it_wrong( __FUNCTION__,esc_html__( 'You should not do that!', 'customify' ), null );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 2.4.0
		 */
		public function __wakeup() {

			_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ),  null );
		}
	}

endif;
