<?php
/**
 * This is the class that handles the logic for Color Palettes.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\StyleManager;

use Pixelgrade\Customify\Utils\ArrayHelpers;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;
use function Pixelgrade\Customify\is_sm_supported;

/**
 * Provides the color palettes logic.
 *
 * @since 3.0.0
 */
class ColorPalettes extends AbstractHookProvider {

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
	 * @param LoggerInterface $logger Logger.
	 */
	public function __construct(
		LoggerInterface $logger
	) {
		$this->logger = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		/*
		 * Handle the Customizer Style Manager section config.
		 */
		$this->add_filter( 'customify_filter_fields', 'add_style_manager_new_section_master_colors_config', 13, 1 );
		$this->add_filter( 'style_manager_panel_config', 'reorganize_customizer_controls', 10, 2 );

		// This needs to come after the external theme config has been applied
		$this->add_filter( 'customify_filter_fields', 'maybe_enhance_dark_mode_control', 120, 1 );

		$this->add_filter( 'customify_final_config', 'alter_master_controls_connected_fields', 100, 1 );
		$this->add_filter( 'customify_final_config', 'add_color_usage_section', 110, 1 );

		$this->add_filter( 'novablocks_block_editor_settings', 'add_color_palettes_to_novablocks_settings' );

		/**
		 * Scripts enqueued in the Customizer.
		 */
		$this->add_action( 'customize_controls_enqueue_scripts', 'enqueue_admin_customizer_scripts', 10 );

		/**
		 * Add color palettes usage to site data.
		 */
		$this->add_filter( 'customify_style_manager_get_site_data', 'add_palettes_to_site_data', 10, 1 );

		$this->add_filter( 'language_attributes', 'add_dark_mode_data_attribute', 10, 2 );

		$this->add_action( 'admin_init', 'editor_color_palettes', 20 );
	}

	/**
	 * Determine if Color Palettes are supported.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function is_supported(): bool {
		// For now we will only use the fact that Style Manager is supported.
		return apply_filters( 'customify_color_palettes_are_supported', is_sm_supported() );
	}

	/**
	 * Add the SM Color Palettes to the editor sidebar.
	 *
	 * @since 3.0.0
	 */
	public function editor_color_palettes() {

		// Bail if Color Palettes are not supported
		if ( ! $this->is_supported() ) {
			return;
		}

		$editor_color_palettes = [];

		if ( ! empty( $editor_color_palettes ) ) {
			/**
			 * Custom colors for use in the editor.
			 *
			 * @link https://wordpress.org/gutenberg/handbook/reference/theme-support/
			 */
			add_theme_support(
				'editor-color-palette',
				$editor_color_palettes
			);
		}
	}

	/**
	 * Enqueue Customizer admin scripts
	 */
	protected function enqueue_admin_customizer_scripts() {

		// If there is no color palettes support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		wp_enqueue_script( 'pixelgrade_customify-dark-mode' );
	}

	/**
	 *
	 * @since 3.0.0
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	protected function alter_master_controls_connected_fields( array $config ): array {

		$switch_foreground_connected_fields = [];
		$switch_accent_connected_fields     = [];

		$select_foreground_connected_fields = [];
		$select_accent_connected_fields     = [];

		if ( ! isset( $config['panels']['theme_options_panel']['sections']['colors_section']['options'] ) ) {
			return $config;
		}

		foreach ( $config['panels']['theme_options_panel']['sections']['colors_section']['options'] as $id => $option_config ) {

			if ( $option_config['type'] === 'sm_toggle' ) {
				if ( $option_config['default'] === 'on' ) {
					$switch_accent_connected_fields[] = $id;
				} else {
					$switch_foreground_connected_fields[] = $id;
				}
			}

			if ( $option_config['type'] === 'select_color' ) {
				if ( $option_config['default'] === 'accent' ) {
					$select_accent_connected_fields[] = $id;
				} else {
					$select_foreground_connected_fields[] = $id;
				}
			}
		}

		if ( ! isset( $config['panels']['style_manager_panel']['sections']['sm_color_palettes_section']['options'] ) ) {
			return $config;
		}

		$options = $config['panels']['style_manager_panel']['sections']['sm_color_palettes_section']['options'];

		if ( isset( $options['sm_text_color_switch_master'] ) ) {
			$options['sm_text_color_switch_master']['connected_fields'] = $switch_foreground_connected_fields;
		}

		if ( isset( $options['sm_accent_color_switch_master'] ) ) {
			$options['sm_accent_color_switch_master']['connected_fields'] = $switch_accent_connected_fields;
		}

		if ( isset( $options['sm_text_color_select_master'] ) ) {
			$options['sm_text_color_select_master']['connected_fields'] = $select_foreground_connected_fields;
		}

		if ( isset( $options['sm_accent_color_select_master'] ) ) {
			$options['sm_accent_color_select_master']['connected_fields'] = $select_accent_connected_fields;
		}

		if ( isset( $options['sm_dark_color_switch_slider'] ) ) {
			$switch_dark_count = count( $switch_foreground_connected_fields );
			// Avoid division by zero.
			if ( empty( $switch_dark_count ) ) {
				$switch_dark_count = 1;
			}
			$switch_accent_count                               = count( $switch_accent_connected_fields );
			$options['sm_dark_color_switch_slider']['default'] = round( $switch_accent_count * 100 / $switch_dark_count );
		}

		if ( isset( $options['sm_dark_color_select_slider'] ) ) {
			$select_dark_count = count( $select_foreground_connected_fields );
			// Avoid division by zero.
			if ( empty( $select_dark_count ) ) {
				$select_dark_count = 1;
			}
			$select_accent_count                               = count( $select_accent_connected_fields );
			$options['sm_dark_color_select_slider']['default'] = round( $select_accent_count * 100 / $select_dark_count );
		}

		if ( isset( $options['sm_dark_color_switch_slider'] ) &&
		     isset( $options['sm_dark_color_select_slider'] ) &&
		     isset( $options['sm_coloration_level'] ) ) {

			$average                                   = ( $options['sm_dark_color_switch_slider']['default'] + $options['sm_dark_color_select_slider']['default'] ) * 0.5;
			$default                                   = $average > 87.5 ? '100' : ( $average > 62.5 ? '75' : ( $average > 25 ? '50' : '0' ) );
			$options['sm_coloration_level']['default'] = $default;
		}

		$config['panels']['style_manager_panel']['sections']['sm_color_palettes_section']['options'] = $options;

		return $config;
	}

	/**
	 *
	 * @since 3.0.0
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	protected function add_color_usage_section( array $config ): array {

		$color_usage_fields = [
			'sm_dark_color_switch_slider',
			'sm_dark_color_select_slider',
			'sm_text_color_switch_master',
			'sm_text_color_select_master',
			'sm_accent_color_switch_master',
			'sm_accent_color_select_master',
			'sm_site_color_variation',
			'sm_coloration_level',
			'sm_colorize_elements_button',
			'sm_dark_mode',
			'sm_dark_mode_advanced',
		];

		$color_usage_section = [
			'title'      => esc_html__( 'Color Usage', '__plugin_txtd' ),
			'section_id' => 'sm_color_usage_section',
			'priority'   => 10,
			'options'    => [],
		];

		if ( ! isset( $config['panels']['style_manager_panel']['sections']['sm_color_palettes_section']['options'] ) ) {
			return $config;
		}

		$sm_colors_options = $config['panels']['style_manager_panel']['sections']['sm_color_palettes_section']['options'];

		foreach ( $color_usage_fields as $field_id ) {

			if ( ! isset( $sm_colors_options[ $field_id ] ) ) {
				continue;
			}

			if ( empty( $color_usage_section['options'] ) ) {
				$color_usage_section['options'] = [ $field_id => $sm_colors_options[ $field_id ] ];
			} else {
				$color_usage_section['options'] = array_merge( $color_usage_section['options'], [ $field_id => $sm_colors_options[ $field_id ] ] );
			}
		}

		$config['panels']['theme_options_panel']['sections']['sm_color_usage_section'] = $color_usage_section;

		return $config;
	}

	protected function add_color_palettes_to_novablocks_settings( array $settings ): array {
		$palette_output_value = PixCustomifyPlugin()->get_option( 'sm_advanced_palette_output' );
		$palettes = [];

		if ( ! empty( $palette_output_value ) ) {
			$palettes = json_decode( $palette_output_value );
		}

		$settings[ 'palettes' ] = $palettes;
		return $settings;
	}

	/**
	 *
	 * @since 3.0.0
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	protected function add_style_manager_new_section_master_colors_config( array $config ): array {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		if ( ! isset( $config['sections']['style_manager_section'] ) ) {
			$config['sections']['style_manager_section'] = [];
		}

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section']['options'] =
			[
				'sm_advanced_palette_source'    => [
					'type'         => 'text',
					'live'         => true,
					'default'      => '[
					{
						"uid": "color_group_1",
						"sources": [
							{ 
								"uid": "color_11", 
								"showPicker": true,
								"label": "Color",
								"value": "#ddaa61"
							}
						]
					},
					{
						"uid": "color_group_2",
						"sources": [
							{ 
								"uid": "color_21", 
								"showPicker": true,
								"label": "Color",
								"value": "#39497C"
							}
						]
					},
					{
						"uid": "color_group_3",
						"sources": [
							{ 
								"uid": "color_31", 
								"showPicker": true,
								"label": "Color",
								"value": "#B12C4A"
							}
						]
					}
					]',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type' => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'   => 'sm_advanced_palette_source',
					'label'        => esc_html__( 'Palette Source', '__plugin_txtd' ),
				],
				'sm_advanced_palette_output' => [
					'type'    => 'text',
					'live'    => true,
					'default' => '[
					  {
					    "sourceIndex": 5,
					    "id": 1,
					    "lightColorsCount": 5,
					    "label": "Color",
					    "source": {
					      "0": "#DDAB5D"
					    },
					    "colors": [
					      { "value": "#FFFFFF" },
					      { "value": "#EEEFF2" },
					      { "value": "#EEEFF2" },
					      { "value": "#EEEFF2" },
					      { "value": "#EEEFF2" },
					      { "value": "#DDAB5D", "isSource": true },
					      { "value": "#DDAB5D" },
					      { "value": "#DDAB5D" },
					      { "value": "#212B49" },
					      { "value": "#212B49" },
					      { "value": "#141928" },
					      { "value": "#141928" }
					    ],
					    "textColors": [
					      { "value": "#34394B" },
					      { "value": "#34394B" }
					    ]
					  },
					  {
					    "sourceIndex": 5,
					    "id": 2,
					    "lightColorsCount": 5,
					    "label": "Color",
					    "source": {
					      "0": "#39497C"
					    },
					    "colors": [
					      { "value": "#FFFFFF" },
					      { "value": "#EEEFF2" },
					      { "value": "#EEEFF2" },
					      { "value": "#EEEFF2" },
					      { "value": "#EEEFF2" },
					      { "value": "#39497C", "isSource": true },
					      { "value": "#39497C" },
					      { "value": "#39497C" },
					      { "value": "#212B49" },
					      { "value": "#212B49" },
					      { "value": "#141928" },
					      { "value": "#141928" }
					    ],
					    "textColors": [
					      { "value": "#34394B" },
					      { "value": "#34394B" }
					    ]
					  },
					  {
					    "sourceIndex": 5,
					    "id": 3,
					    "lightColorsCount": 5,
					    "label": "Color",
					    "source": {
					      "0": "#B12C4A"
					    },
					    "colors": [
					      { "value": "#FFFFFF" },
					      { "value": "#EEEFF2" },
					      { "value": "#EEEFF2" },
					      { "value": "#EEEFF2" },
					      { "value": "#EEEFF2" },
					      { "value": "#B12C4A", "isSource": true },
					      { "value": "#B12C4A" },
					      { "value": "#B12C4A" },
					      { "value": "#212B49" },
					      { "value": "#212B49" },
					      { "value": "#141928" },
					      { "value": "#141928" }
					    ],
					    "textColors": [
					      { "value": "#34394B" },
					      { "value": "#34394B" }
					    ]
					  }
					]',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type' => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'   => 'sm_advanced_palette_output',
					'label'        => esc_html__( 'Palette Output', '__plugin_txtd' ),
					'css'          => [
						[
							'selector'        => ':root',
							'property'        => 'dummy-property',
							'callback_filter' => 'sm_advanced_palette_output_cb',
						],
					],
				],
				'sm_site_color_variation'       => [
					'type'         => 'range',
					'desc'         => wp_kses( __( 'Shift the <strong>start position</strong> of the color palette. Use 0 for white, 1-2 for subtle shades, 3-6 for colorful, above 7 for darker shades.', '__plugin_txtd' ), [ 'strong' => [] ] ),
					'live'         => true,
					'setting_type' => 'option',
					'setting_id'   => 'sm_site_color_variation',
					'label'        => esc_html__( 'Palette Basis Offset', '__plugin_txtd' ),
					'default'      => 1,
					'input_attrs'  => [
						'min'  => 1,
						'max'  => 12,
						'step' => 1,
					],
					'css'          => [
						[
							'selector'        => ':root',
							'property'        => 'dummy-property',
							'callback_filter' => 'sm_variation_range_cb',
						],
					],
				],
				'sm_text_color_switch_master'   => [
					'type'             => 'sm_toggle',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_text_color_switch_master',
					'label'            => esc_html__( 'Text Master', '__plugin_txtd' ),
					'live'             => true,
					'default'          => false,
					'connected_fields' => [],
					'css'              => [],
				],
				'sm_text_color_select_master'   => [
					'type'             => 'select_color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_text_color_select_master',
					'label'            => esc_html__( 'Text Select Master', '__plugin_txtd' ),
					'live'             => true,
					'default'          => 'dark',
					'connected_fields' => [],
					'css'              => [],
					'choices'          => [
						'text' => esc_html__( 'Text', '__plugin_txtd' ),
					],
				],
				'sm_accent_color_switch_master' => [
					'type'             => 'sm_toggle',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_accent_color_switch_master',
					'label'            => esc_html__( 'Accent Master', '__plugin_txtd' ),
					'live'             => true,
					'default'          => true,
					'connected_fields' => [],
					'css'              => [],
				],
				'sm_accent_color_select_master' => [
					'type'             => 'select_color',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_accent_color_select_master',
					'label'            => esc_html__( 'Accent Select Master', '__plugin_txtd' ),
					'live'             => true,
					'default'          => 'accent',
					'connected_fields' => [],
					'css'              => [],
					'choices'          => [
						'accent' => esc_html__( 'Accent', '__plugin_txtd' ),
					],
				],
				'sm_coloration_level'           => [
					'type'         => 'sm_radio',
					'desc'         => wp_kses( __( 'Adjust <strong>how much color</strong> you want to add to your site. For more control over elements, you can edit them individually.', '__plugin_txtd' ), [ 'strong' => [] ] ),
					'setting_type' => 'option',
					'setting_id'   => 'sm_coloration_level',
					'label'        => esc_html__( 'Coloration Level', '__plugin_txtd' ),
					'default'      => 0,
					'live'         => true,
					'choices'      => [
						'0'   => esc_html__( 'Low', '__plugin_txtd' ),
						'50'  => esc_html__( 'Medium', '__plugin_txtd' ),
						'75'  => esc_html__( 'High', '__plugin_txtd' ),
						'100' => esc_html__( 'Striking', '__plugin_txtd' ),
					],
				],
				'sm_dark_color_switch_slider'   => [
					'setting_id'  => 'sm_dark_color_switch_slider',
					'type'        => 'range',
					'label'       => esc_html__( 'Dark to Color (switch)', '__plugin_txtd' ),
					'desc'        => '',
					'live'        => true,
					'default'     => 0,
					'input_attrs' => [
						'min'          => 0,
						'max'          => 100,
						'step'         => 1,
						'data-preview' => true,
					],
					'css'         => [],
				],
				'sm_dark_color_select_slider'   => [
					'setting_id'  => 'sm_dark_color_select_slider',
					'type'        => 'range',
					'label'       => esc_html__( 'Dark to Color (select)', '__plugin_txtd' ),
					'desc'        => '',
					'live'        => true,
					'default'     => 0,
					'input_attrs' => [
						'min'          => 0,
						'max'          => 100,
						'step'         => 1,
						'data-preview' => true,
					],
					'css'         => [],
				],
			] + $config['sections']['style_manager_section']['options'];

		return $config;
	}

	/**
	 * Reorganize the Customizer controls.
	 *
	 * @since 3.0.0
	 *
	 * @param array $sm_panel_config
	 * @param array $sm_section_config
	 *
	 * @return array
	 */
	protected function reorganize_customizer_controls( array $sm_panel_config, array $sm_section_config ): array {
		// We need to split the fields in the Style Manager section into two: color palettes and fonts.
		$color_palettes_fields = [
			'sm_advanced_palette_source',
			'sm_advanced_palette_output',

			'sm_dark_color_switch_slider',
			'sm_dark_color_select_slider',
			'sm_text_color_switch_master',
			'sm_text_color_select_master',
			'sm_accent_color_switch_master',
			'sm_accent_color_select_master',
			'sm_site_color_variation',
			'sm_coloration_level',
			'sm_colorize_elements_button',
			'sm_dark_mode',
			'sm_dark_mode_advanced',
		];

		$color_palettes_section_config = [
			'title'      => esc_html__( 'Colors', '__plugin_txtd' ),
			'section_id' => 'sm_color_palettes_section',
			'priority'   => 10,
			'options'    => [],
		];

		foreach ( $color_palettes_fields as $field_id ) {
			if ( ! isset( $sm_section_config['options'][ $field_id ] ) ) {
				continue;
			}

			if ( empty( $color_palettes_section_config['options'] ) ) {
				$color_palettes_section_config['options'] = [ $field_id => $sm_section_config['options'][ $field_id ] ];
			} else {
				$color_palettes_section_config['options'] = array_merge( $color_palettes_section_config['options'], [ $field_id => $sm_section_config['options'][ $field_id ] ] );
			}
		}

		$sm_panel_config['sections']['sm_color_palettes_section'] = $color_palettes_section_config;

		return $sm_panel_config;
	}

	/**
	 *
	 * @since 3.0.0
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	protected function maybe_enhance_dark_mode_control( array $config ): array {

		if ( ! current_theme_supports( 'style_manager_advanced_dark_mode' )
		     || ! isset( $config['sections']['style_manager_section'] ) ) {

			return $config;
		}

		unset( $config['sections']['style_manager_section']['options']['sm_dark_mode'] );

		$config['sections']['style_manager_section'] = ArrayHelpers::array_merge_recursive_distinct( $config['sections']['style_manager_section'], [
			'options' => [
				'sm_dark_mode_advanced' => [
					'type'         => 'sm_radio',
					'setting_id'   => 'sm_dark_mode_advanced',
					'setting_type' => 'option',
					'label'        => esc_html__( 'Appearance', '__plugin_txtd' ),
					'live'         => true,
					'default'      => 'off',
					'desc'         => wp_kses( __( "<strong>Auto</strong> activates dark mode automatically, according to the visitor's system-wide setting", '__plugin_txtd' ), [ 'strong' => [] ] ),
					'choices'      => [
						'off'  => esc_html__( 'Light', '__plugin_txtd' ),
						'on'   => esc_html__( 'Dark', '__plugin_txtd' ),
						'auto' => esc_html__( 'Auto', '__plugin_txtd' ),
					],
				],
			],
		] );

		return $config;
	}

	/**
	 * Get the default (hard-coded) color palettes configuration.
	 *
	 * This is only a fallback config in case we can't communicate with the cloud, the first time.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	protected function get_default_config(): array {
		return apply_filters( 'customify_style_manager_default_color_palettes', [] );
	}

	/**
	 * Add color palettes usage data to the site data sent to the cloud.
	 *
	 * @since 3.0.0
	 *
	 * @param array $site_data
	 *
	 * @return array
	 */
	protected function add_palettes_to_site_data( array $site_data ): array {

		if ( empty( $site_data['color_palettes'] ) ) {
			$site_data['color_palettes'] = [];
		}

		// If others have added data before us, we will merge with it.
		$site_data['color_palettes'] = array_merge( $site_data['color_palettes'], [] );

		return $site_data;
	}

	/**
	 * Add Color Scheme attribute to <html> tag.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output  A space-separated list of language attributes.
	 * @param string $doctype The type of html document (xhtml|html).
	 *
	 * @return string $output A space-separated list of language attributes.
	 */
	protected function add_dark_mode_data_attribute( string $output, string $doctype ): string {

		if ( is_admin() || 'html' !== $doctype ) {
			return $output;
		}

		$output .= ' data-dark-mode-advanced=' . pixelgrade_option( 'sm_dark_mode_advanced', 'off' );

		return $output;
	}
}
