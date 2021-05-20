<?php
/**
 * Customizer screen provider.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen;

use Pixelgrade\Customify\Provider\Options;
use Pixelgrade\Customify\Provider\PluginSettings;
use Pixelgrade\Customify\Screen\Customizer\Control\Button;
use Pixelgrade\Customify\StyleManager\FontPalettes;
use Pixelgrade\Customify\StyleManager\Fonts;
use Pixelgrade\Customify\Utils\ScriptsEnqueue;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;

/**
 * Customizer screen provider class.
 *
 * @since 3.0.0
 */
class Customizer extends AbstractHookProvider {

	protected array $localized = [];

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
	 * Style Manager Fonts.
	 *
	 * @var Fonts
	 */
	protected Fonts $sm_fonts;

	/**
	 * Style Manager Font Palettes.
	 *
	 * @var FontPalettes
	 */
	protected FontPalettes $sm_font_palettes;


	/**
	 * Logger.
	 *
	 * @var LoggerInterface
	 */
	protected LoggerInterface $logger;

	/**
	 * Create the setting screen.
	 *
	 * @since 3.0.0
	 *
	 * @param Options         $options          Options.
	 * @param PluginSettings  $plugin_settings  Plugin settings.
	 * @param Fonts           $sm_fonts         Style Manager Fonts.
	 * @param FontPalettes    $sm_font_palettes Style Manager Font Palettes.
	 * @param LoggerInterface $logger           Logger.
	 */
	public function __construct(
		Options $options,
		PluginSettings $plugin_settings,
		Fonts $sm_fonts,
		FontPalettes $sm_font_palettes,
		LoggerInterface $logger
	) {
		$this->options          = $options;
		$this->plugin_settings  = $plugin_settings;
		$this->sm_fonts         = $sm_fonts;
		$this->sm_font_palettes = $sm_font_palettes;
		$this->logger           = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		// We will initialize the Customizer logic after the plugin has finished with it's configuration (at priority 15).
		$this->add_action( 'init', 'setup', 15 );

		$this->add_filter( 'customify_filter_fields', 'default_options', 5, 1 );
	}

	/**
	 * Setup the Customizer logic.
	 *
	 * @since 3.0.0
	 */
	protected function setup() {
		// Others will be able to add data here via the 'customify_localized_js_settings' filter.
		// This is a just-in-time filter, triggered as late as possible.
		$this->localized = [
			'config' => [
				'options_name'      => $this->options->get_options_key(),
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'webfontloader_url' => $this->plugin->get_url( 'vendor_js/webfontloader-1-6-28.min.js' ),
			],
			// For localizing strings.
			'l10n'   => [
				'panelResetButton'           => esc_html__( 'Panel\'s defaults', '__plugin_txtd' ),
				'sectionResetButton'         => esc_html__( 'Reset All Options for This Section', '__plugin_txtd' ),
				'resetGlobalConfirmMessage'  => wp_kses_post( __( 'Do you really want to reset to defaults all the fields? Watch out, this will reset all your Customify options and will save them!', '__plugin_txtd' ) ),
				'resetPanelConfirmMessage'   => wp_kses_post( __( 'Do you really want to reset the settings in this panel?', '__plugin_txtd' ) ),
				'resetSectionConfirmMessage' => wp_kses_post( __( 'Do you really want to reset the settings in this section?', '__plugin_txtd' ) ),
			],
		];

		// Hook up.
		$this->add_hooks();

		// $this->maybe_create_colors_page();
	}

	/**
	 * Hook up.
	 *
	 * @since 3.0.0
	 */
	public function add_hooks() {

		// Scripts enqueued in the Customizer
		$this->add_action( 'customize_controls_enqueue_scripts', 'enqueue_assets', 15 );

		// Add extra settings data to _wpCustomizeSettings.settings of the parent window.
		$this->add_action( 'customize_controls_print_footer_scripts', 'customize_pane_settings_additional_data', 10000 );

		$this->add_action( 'customize_register', 'maybe_remove_default_sections', 11 );
		$this->add_action( 'customize_register', 'process_customizer_config', 12 );
		// Maybe the theme has instructed us to do things like removing sections or controls.
		$this->add_action( 'customize_register', 'maybe_process_config_extras', 13 );

		/**
		 * DEVELOPMENT RELATED
		 */
		if ( defined( 'CUSTOMIFY_DEV_FORCE_DEFAULTS' ) && true === CUSTOMIFY_DEV_FORCE_DEFAULTS ) {
			// If the development constant CUSTOMIFY_DEV_FORCE_DEFAULTS has been defined we will not save anything in the database
			// Always go with the default
			$this->add_filter( 'customize_changeset_save_data', 'prevent_changeset_save_in_devmode', 50, 1 );
			// Add a JS to display a notification
			$this->add_action( 'customize_controls_print_footer_scripts', 'prevent_changeset_save_in_devmode_notification', 100 );
		}
	}

	/**
	 * Enqueue Customizer scripts and styles.
	 *
	 * @since 3.0.0
	 * @see   CustomizerAssets
	 *
	 */
	protected function enqueue_assets() {
		wp_enqueue_script( 'pixelgrade_customify-customizer' );
		wp_add_inline_script( 'pixelgrade_customify-customizer',
			ScriptsEnqueue::getlocalizeToWindowScript( 'customify',
				apply_filters( 'customify_localized_js_settings', $this->localized )
			), 'before' );

		wp_enqueue_style( 'pixelgrade_customify-sm-colors-custom-properties' );
		wp_enqueue_style( 'pixelgrade_customify-customizer' );
	}

	/**
	 * Prevent saving of plugin options in the Customizer
	 *
	 * @since 3.0.0
	 *
	 * @param array $data The data to save
	 *
	 * @return array
	 */
	protected function prevent_changeset_save_in_devmode( array $data ): array {
		// Get the options key
		$options_key = $this->options->get_options_key();
		if ( ! empty( $options_key ) ) {
			// Remove any Customify data thus preventing it from saving
			foreach ( $data as $option_id => $value ) {
				if ( false !== strpos( $option_id, $options_key ) && ! $this->options->skip_dev_mode_force_defaults( $option_id ) ) {
					unset( $data[ $option_id ] );
				}
			}
		}

		return $data;
	}

	/**
	 * Display a notification regarding the fact that we are in development mode and no changes will be saved.
	 *
	 * @since 3.0.0
	 */
	protected function prevent_changeset_save_in_devmode_notification() { ?>
		<script type="application/javascript">
			(function ($, exports, wp) {
				'use strict'
				// when the customizer is ready add our notification
				wp.customize.bind('ready', function () {
					wp.customize.notifications.add('customify_force_defaults', new wp.customize.Notification(
						'customify_force_defaults',
						{
							type: 'warning',
							message: '<?php echo wp_kses_post( __( '<strong>Customify: Development Mode</strong><p>All options are switched to default. While they are changing in the live preview, they will not be kept when you hit publish.</p>', '__plugin_txtd' ) ); ?>'
						}
					))
				})
			})(jQuery, window, wp)
		</script>
	<?php }

	/**
	 * Register all the panels, sections and controls we receive through the plugin's config.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	protected function process_customizer_config( \WP_Customize_Manager $wp_customize ) {

		do_action( 'customify_before_process_customizer_config', $wp_customize );

		// Load the customizer config.
		$customizer_config = apply_filters( 'customify_customizer_config_pre_processing', $this->options->get_customizer_config(), $wp_customize );

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
						$panel_id = $options_name . '[' . $panel_id . ']';
					}

					$panel_args = [
						'priority'                 => 10,
						'capability'               => 'edit_theme_options',
						'title'                    => esc_html__( 'Panel title is required', '__plugin_txtd' ),
						'description'              => esc_html__( 'Description of what this panel does.', '__plugin_txtd' ),
						'auto_expand_sole_section' => false,
					];

					if ( ! empty( $panel_config['priority'] ) ) {
						$panel_args['priority'] = $panel_config['priority'];
					}

					if ( ! empty( $panel_config['title'] ) ) {
						$panel_args['title'] = $panel_config['title'];
					}

					if ( ! empty( $panel_config['description'] ) ) {
						$panel_args['description'] = $panel_config['description'];
					}

					if ( isset( $panel_config['auto_expand_sole_section'] ) ) {
						$panel_args['auto_expand_sole_section'] = $panel_config['auto_expand_sole_section'];
					}


					$panel = $wp_customize->add_panel( $panel_id, $panel_args );
					// Fire a general added panel hook
					do_action( 'customify_process_customizer_config_added_panel', $panel, $panel_args, $wp_customize );

					foreach ( $panel_config['sections'] as $section_id => $section_config ) {
						if ( ! empty( $section_id ) && ! empty( $section_config['options'] ) ) {
							$this->register_section( $panel_id, $section_id, $options_name, $section_config, $wp_customize );
						}
					}

					// Fire a general finished panel hook
					do_action( 'customify_process_customizer_config_finished_panel', $panel, $panel_args, $wp_customize );
				}
			}
		}

		// Handle sections.
		if ( ! empty( $customizer_config['sections'] ) ) {

			foreach ( $customizer_config['sections'] as $section_id => $section_config ) {
				if ( ! empty( $section_id ) && ! empty( $section_config['options'] ) ) {
					$this->register_section( '', $section_id, $options_name, $section_config, $wp_customize );
				}
			}
		}

		// Handle development helper buttons.
		if ( $this->plugin_settings->get( 'enable_reset_buttons' ) ) {
			// create a toolbar section which will be present all the time
			$reset_section_settings = [
				'title'      => esc_html__( 'Customify Toolbox', '__plugin_txtd' ),
				'capability' => 'manage_options',
				'priority'   => 999999999,
				'options'    => [
					'reset_all_button' => [
						'type'   => 'button',
						'label'  => esc_html__( 'Reset Customify', '__plugin_txtd' ),
						'action' => 'reset_customify',
						'value'  => 'Reset',
					],
				],
			];

			$wp_customize->add_section(
				'customify_toolbar',
				$reset_section_settings
			);

			$wp_customize->add_setting(
				'reset_customify',
				[]
			);
			$wp_customize->add_control( new Button(
				$wp_customize,
				'reset_customify',
				[
					'label'    => esc_html__( 'Reset All Customify Options to Default', '__plugin_txtd' ),
					'section'  => 'customify_toolbar',
					'settings' => 'reset_customify',
					'action'   => 'reset_customify',
				]
			) );
		}

		do_action( 'customify_after_process_customizer_config', $wp_customize );
	}

	/**
	 *
	 * @since 3.0.0
	 *
	 * @param string                $panel_id
	 * @param string                $section_key
	 * @param string                $options_name
	 * @param array                 $section_config
	 * @param \WP_Customize_Manager $wp_customize
	 */
	protected function register_section( string $panel_id, string $section_key, string $options_name, array $section_config, \WP_Customize_Manager $wp_customize ) {

		// If we have been explicitly given a section ID we will use that
		if ( ! empty( $section_config['section_id'] ) ) {
			$section_id = $section_config['section_id'];
		} else {
			$section_id = $options_name . '[' . $section_key . ']';
		}

		// Add the new section to the Customizer, but only if it is not already added.
		if ( ! $wp_customize->get_section( $section_id ) ) {
			// Merge the section settings with the defaults
			$section_args = wp_parse_args( $section_config, [
				'priority'           => 10,
				'panel'              => $panel_id,
				'capability'         => 'edit_theme_options',
				'theme_supports'     => '',
				'title'              => esc_html__( 'Title Section is required', '__plugin_txtd' ),
				'description'        => '',
				'type'               => 'default',
				'description_hidden' => false,
			] );

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
			if ( ! empty( $option_config['type'] ) && ! in_array( $option_config['type'], [
					'html',
					'button',
				] ) ) {
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
	 * @since 3.0.0
	 *
	 * @see   \WP_Customize_Setting
	 * @see   \WP_Customize_Control
	 *
	 * @param string                $section_id
	 * @param string                $setting_id
	 * @param array                 $field_config
	 * @param \WP_Customize_Manager $wp_customize
	 */
	protected function register_field( string $section_id, string $setting_id, array $field_config, \WP_Customize_Manager $wp_customize ) {

		$add_control = true;
		// defaults
		$setting_args = [
			'type'       => 'theme_mod',
			'default'    => '',
			'capability' => 'edit_theme_options',
			'transport'  => 'refresh',
		];
		$control_args = [
			'priority' => 10,
			'label'    => '',
			'section'  => $section_id,
			'settings' => $setting_id,
		];

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
		} elseif ( $this->plugin_settings->get( 'values_store_mod' ) === 'option' ) {
			$setting_args['type'] = 'option';
		}

		if ( ! empty( $field_config['sanitize_callback'] ) && is_callable( $field_config['sanitize_callback'] ) ) {
			$setting_args['sanitize_callback'] = $field_config['sanitize_callback'];
		} elseif ( 'checkbox' === $field_config['type'] ) {
			$setting_args['sanitize_callback'] = [ $this, 'setting_sanitize_checkbox' ];
		}

		// Add the setting.
		$wp_customize->add_setting( $setting_id, $setting_args );

		// Stop the control registration, if we are presented with the right type.
		if ( 'hidden_control' === $field_config['type'] ) {
			return;
		}

		$control_args['type'] = $field_config['type'];

		// Now sanitize the control config.
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

		// Select the control type, but first initialize a default.
		$control_class_name = __NAMESPACE__ . '\Customizer\Control\Text';

		// If is a standard wp field type call it here and skip the rest.
		if ( in_array( $field_config['type'], [
			'checkbox',
			'dropdown-pages',
			'url',
			'date',
			'time',
			'datetime',
			'week',
			'search',
		] ) ) {
			$wp_customize->add_control( $setting_id . '_control', $control_args );

			return;
		} elseif ( in_array( $field_config['type'], [
				'radio',
				'select',
			] ) && ! empty( $field_config['choices'] )
		) {
			$control_args['choices'] = $field_config['choices'];
			$wp_customize->add_control( $setting_id . '_control', $control_args );

			return;
		} elseif ( in_array( $field_config['type'], [ 'range' ] ) && ! empty( $field_config['input_attrs'] ) ) {

			$control_args['input_attrs'] = $field_config['input_attrs'];

			$wp_customize->add_control( $setting_id . '_control', $control_args );
		}

		// If we arrive here this means we have a custom field control (with a corresponding class in includes/customizer-controls).
		switch ( $field_config['type'] ) {

			case 'text':
				if ( isset( $field_config['live'] ) ) {
					$control_args['live'] = $field_config['live'];
				}

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\Text';
				break;

			case 'textarea':
				if ( isset( $field_config['live'] ) ) {
					$control_args['live'] = $field_config['live'];
				}

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\Textarea';
				break;

			case 'color':
				$control_class_name = 'WP_Customize_Color_Control';
				break;

			case 'ace_editor':
				if ( isset( $field_config['live'] ) ) {
					$control_args['live'] = $field_config['live'];
				}

				if ( isset( $field_config['editor_type'] ) ) {
					$control_args['editor_type'] = $field_config['editor_type'];
				}

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\AceEditor';
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

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\Background';
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
				if ( ! $this->plugin_settings->get( 'enable_typography', 'yes' ) ) {
					$add_control = false;
					break;
				}

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\Font';

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
					$control_args['fields'] = [];
				}

				// We need to pass the Style Manager Fonts service to the control so it can fetch font details.
				$control_args['sm_fonts_service'] = $this->sm_fonts;

				break;

			case 'select2' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\Select2';
				break;

			case 'select_color' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\SelectColor';
				break;

			case 'sm_radio' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\SMRadio';
				break;

			case 'sm_switch' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\SMSwitch';
				break;

			case 'sm_toggle' :
				$control_class_name = __NAMESPACE__ . '\Customizer\Control\SMToggle';
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

				// We need to pass the Style Manager Fonts service to the control so it can fetch font details.
				$control_args['sm_font_palettes_service'] = $this->sm_font_palettes;

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\Preset';
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


				$control_class_name = __NAMESPACE__ . '\Customizer\Control\RadioImage';
				break;

			case 'radio_html' :
				if ( ! isset( $field_config['choices'] ) || empty( $field_config['choices'] ) ) {
					return;
				}

				$control_args['choices'] = $field_config['choices'];

				if ( isset( $field_config['desc'] ) || ! empty( $field_config['desc'] ) ) {
					$control_args['description'] = $field_config['desc'];
				}

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\RadioHTML';
				break;

			case 'button' :
				if ( ! isset( $field_config['action'] ) || empty( $field_config['action'] ) ) {
					return;
				}

				$control_args['action'] = $field_config['action'];

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\Button';

				break;

			case 'html' :
				if ( isset( $field_config['html'] ) || ! empty( $field_config['html'] ) ) {
					$control_args['html'] = $field_config['html'];
				}

				$control_class_name = __NAMESPACE__ . '\Customizer\Control\HTML';
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
	 * Remove the core Customizer sections selected by user in the plugin settings.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 * @global                      $wp_registered_sidebars
	 *
	 */
	protected function maybe_remove_default_sections( \WP_Customize_Manager $wp_customize ) {
		global $wp_registered_sidebars;

		$to_remove = $this->plugin_settings->get( 'disable_default_sections' );
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
	 *
	 * @since 3.0.0
	 * @global \WP_Customize_Manager $wp_customize
	 */
	protected function customize_pane_settings_additional_data() {
		/**
		 * @global \WP_Customize_Manager $wp_customize
		 */
		global $wp_customize;

		$options_name = $this->options->get_options_key();
		// Without an options name we can't do much.
		if ( empty( $options_name ) ) {
			return;
		}

		$customizer_settings = $wp_customize->settings(); ?>

		<script type="text/javascript">
			if ('undefined' === typeof _wpCustomizeSettings.settings) {
				_wpCustomizeSettings.settings = {}
			}

			<?php
			echo "(function ( sAdditional ){\n";

			$options = $this->options->get_details_all();
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
					$connected_fields = [];
					foreach ( $option_config['connected_fields'] as $key => $connected_field_config ) {
						$connected_field_data = [];

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
						} // If the connected setting specifies a setting ID, we will not prefix it and use it as it is.
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
	 * @since 3.0.0
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	protected function maybe_process_config_extras( \WP_Customize_Manager $wp_customize ) {
		$customizer_config = $this->options->get_customizer_config();

		// Bail if we have no external theme config data.
		if ( empty( $customizer_config ) || ! is_array( $customizer_config ) ) {
			return;
		}

		// Maybe remove panels
		if ( ! empty( $customizer_config['remove_panels'] ) ) {
			// Standardize it.
			if ( is_string( $customizer_config['remove_panels'] ) ) {
				$customizer_config['remove_panels'] = [ $customizer_config['remove_panels'] ];
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
				if ( empty( $panel ) || ! $panel instanceof \WP_Customize_Panel ) {
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
				$customizer_config['remove_sections'] = [ $customizer_config['remove_sections'] ];
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
				if ( empty( $section ) || ! $section instanceof \WP_Customize_Section ) {
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
				$customizer_config['remove_settings'] = [ $customizer_config['remove_settings'] ];
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
				if ( empty( $setting ) || ! $setting instanceof \WP_Customize_Setting ) {
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
				$customizer_config['remove_controls'] = [ $customizer_config['remove_controls'] ];
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
				if ( empty( $control ) || ! $control instanceof \WP_Customize_Control ) {
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
	 * @param array $config
	 *
	 * @return array
	 */
	protected function default_options( array $config ): array {

		$config['opt-name'] = 'customify_defaults';

		$config['sections'] = [
			/**
			 * Presets - This section will handle other options
			 */
			'presets_section'     => [
				'title'   => esc_html__( 'Style Presets', '__plugin_txtd' ),
				'options' => [
					'theme_style' => [
						'type'         => 'preset',
						'label'        => esc_html__( 'Select a style:', '__plugin_txtd' ),
						'desc'         => esc_html__( 'Conveniently change the design of your site with built-in style presets. Easy as pie.', '__plugin_txtd' ),
						'default'      => 'royal',
						'choices_type' => 'awesome',
						'choices'      => [
							'royal'  => [
								'label'   => esc_html__( 'Royal', '__plugin_txtd' ),
								'preview' => [
									'color-text'       => '#ffffff',
									'background-card'  => '#615375',
									'background-label' => '#46414c',
									'font-main'        => 'Abril Fatface',
									'font-alt'         => 'PT Serif',
								],
								'options' => [
									'links_color'     => '#8eb2c5',
									'headings_color'  => '#725c92',
									'body_color'      => '#6f8089',
									'page_background' => '#615375',
									'headings_font'   => 'Abril Fatface',
									'body_font'       => 'PT Serif',
								],
							],
							'lovely' => [
								'label'   => esc_html__( 'Lovely', '__plugin_txtd' ),
								'preview' => [
									'color-text'       => '#ffffff',
									'background-card'  => '#d15c57',
									'background-label' => '#5c374b',
									'font-main'        => 'Playfair Display',
									'font-alt'         => 'Playfair Display',
								],
								'options' => [
									'links_color'     => '#cc3747',
									'headings_color'  => '#d15c57',
									'body_color'      => '#5c374b',
									'page_background' => '#d15c57',
									'headings_font'   => 'Playfair Display',
									'body_font'       => 'Playfair Display',
								],
							],
							'queen'  => [
								'label'   => esc_html__( 'Queen', '__plugin_txtd' ),
								'preview' => [
									'color-text'       => '#fbedec',
									'background-card'  => '#773347',
									'background-label' => '#41212a',
									'font-main'        => 'Cinzel Decorative',
									'font-alt'         => 'Gentium Basic',
								],
								'options' => [
									'links_color'     => '#cd8085',
									'headings_color'  => '#54323c',
									'body_color'      => '#cd8085',
									'page_background' => '#fff',
									'headings_font'   => 'Cinzel Decorative',
									'body_font'       => 'Gentium Basic',
								],
							],
							'carrot' => [
								'label'   => esc_html__( 'Carrot', '__plugin_txtd' ),
								'preview' => [
									'color-text'       => '#ffffff',
									'background-card'  => '#df421d',
									'background-label' => '#85210a',
									'font-main'        => 'Oswald',
									'font-alt'         => 'PT Sans Narrow',
								],
								'options' => [
									'links_color'     => '#df421d',
									'headings_color'  => '#df421d',
									'body_color'      => '#7e7e7e',
									'page_background' => '#fff',
									'headings_font'   => 'Oswald',
									'body_font'       => 'PT Sans Narrow',
								],
							],


							'adler'  => [
								'label'   => esc_html__( 'Adler', '__plugin_txtd' ),
								'preview' => [
									'color-text'       => '#fff',
									'background-card'  => '#0e364f',
									'background-label' => '#000000',
									'font-main'        => 'Permanent Marker',
									'font-alt'         => 'Droid Sans Mono',
								],
								'options' => [
									'links_color'     => '#68f3c8',
									'headings_color'  => '#0e364f',
									'body_color'      => '#45525a',
									'page_background' => '#ffffff',
									'headings_font'   => 'Permanent Marker',
									'body_font'       => 'Droid Sans Mono',
								],
							],
							'velvet' => [
								'label'   => esc_html__( 'Velvet', '__plugin_txtd' ),
								'preview' => [
									'color-text'       => '#ffffff',
									'background-card'  => '#282828',
									'background-label' => '#000000',
									'font-main'        => 'Pinyon Script',
									'font-alt'         => 'Josefin Sans',
								],
								'options' => [
									'links_color'     => '#000000',
									'headings_color'  => '#000000',
									'body_color'      => '#000000',
									'page_background' => '#000000',
									'headings_font'   => 'Pinyon Script',
									'body_font'       => 'Josefin Sans',
								],
							],

						],
					],
				],
			],

			/**
			 * COLORS - This section will handle different elements colors (eg. links, headings)
			 */
			'colors_section'      => [
				'title'   => esc_html__( 'Colors', '__plugin_txtd' ),
				'options' => [
					'links_color'    => [
						'type'    => 'color',
						'label'   => esc_html__( 'Links Color', '__plugin_txtd' ),
						'live'    => true,
						'default' => '#6c6e70',
						'css'     => [
							[
								'property' => 'color',
								'selector' => 'a, .entry-meta a',
							],
						],
					],
					'headings_color' => [
						'type'    => 'color',
						'label'   => esc_html__( 'Headings Color', '__plugin_txtd' ),
						'live'    => true,
						'default' => '#0aa0d9',
						'css'     => [
							[
								'property' => 'color',
								'selector' => '.site-title a, h1, h2, h3, h4, h5, h6,
												h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
												.widget-title,
												a:hover, .entry-meta a:hover',
							],
						],
					],
					'body_color'     => [
						'type'    => 'color',
						'label'   => esc_html__( 'Body Color', '__plugin_txtd' ),
						'live'    => true,
						'default' => '#2d3033',
						'css'     => [
							[
								'selector' => 'body',
								'property' => 'color',
							],
						],
					],
				],
			],

			/**
			 * FONTS - This section will handle different elements fonts (eg. headings, body)
			 */
			'typography_section'  => [
				'title'   => esc_html__( 'Fonts', '__plugin_txtd' ),
				'options' => [
					'headings_font' => [
						'type'        => 'font',
						'label'       => esc_html__( 'Headings', '__plugin_txtd' ),
						'default'     => 'Playfair Display',
						'selector'    => '.site-title a, h1, h2, h3, h4, h5, h6,
										h1 a, h2 a, h3 a, h4 a, h5 a, h6 a,
										.widget-title',
						'font_weight' => true,
						'recommended' => [
							'Playfair Display',
							'Oswald',
							'Lato',
							'Open Sans',
							'Exo',
							'PT Sans',
							'Ubuntu',
							'Vollkorn',
							'Lora',
							'Arvo',
							'Josefin Slab',
							'Crete Round',
							'Kreon',
							'Bubblegum Sans',
							'The Girl Next Door',
							'Pacifico',
							'Handlee',
							'Satify',
							'Pompiere',
						],
					],
					'body_font'     => [
						'type'        => 'font',
						'label'       => esc_html__( 'Body Text', '__plugin_txtd' ),
						'default'     => 'Lato',
						'selector'    => 'html body',
						'recommended' => [
							'Lato',
							'Open Sans',
							'PT Sans',
							'Cabin',
							'Gentium Book Basic',
							'PT Serif',
							'Droid Serif',
						],
					],
				],
			],

			/**
			 * BACKGROUNDS - This section will handle different elements colors (eg. links, headings)
			 */
			'backgrounds_section' => [
				'title'   => esc_html__( 'Backgrounds', '__plugin_txtd' ),
				'options' => [
					'page_background' => [
						'type'    => 'color',
						'label'   => esc_html__( 'Page Background', '__plugin_txtd' ),
						'live'    => true,
						'default' => '#ffffff',
						'css'     => [
							[
								'property' => 'background',
								'selector' => 'body, .site',
							],
						],
					],
				],
			],
			/**
			 * LAYOUTS - This section will handle different elements colors (eg. links, headings)
			 */
			'layout_options'      => [
				'title'   => esc_html__( 'Layout', '__plugin_txtd' ),
				'options' => [
					'site_title_size'      => [
						'type'        => 'range',
						'label'       => esc_html__( 'Site Title Size', '__plugin_txtd' ),
						'live'        => true,
						'input_attrs' => [
							'min'          => 24,
							'max'          => 100,
							'step'         => 1,
							'data-preview' => true,
						],
						'default'     => 24,
						'css'         => [
							[
								'property' => 'font-size',
								'selector' => '.site-title',
								'media'    => 'screen and (min-width: 1000px)',
								'unit'     => 'px',
							],
						],
					],
					'page_content_spacing' => [
						'type'        => 'range',
						'label'       => 'Page Content Spacing',
						'live'        => true,
						'input_attrs' => [
							'min'  => 0,
							'max'  => 100,
							'step' => 1,
						],
						'default'     => 18,
						'css'         => [
							[
								'property' => 'padding',
								'selector' => '.site-content',
								'media'    => 'screen and (min-width: 1000px)',
								'unit'     => 'px',
							],
						],
					],
				],
			],
		];

		return $config;
	}

	/* SANITIZATION HELPERS */

	/**
	 * Sanitize the checkbox.
	 *
	 * @param mixed $input .
	 *
	 * @return boolean true if is 1, '1', or 'yes', false if anything else
	 */
	function setting_sanitize_checkbox( $input ): bool {
		if ( 1 == $input || 'yes' === $input ) {
			return true;
		} else {
			return false;
		}
	}
}
