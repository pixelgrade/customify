<?php
/**
 * This is the class that handles the logic for Color Palettes.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package PixelgradeLT
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

		// This needs to come after the external theme config has been applied
		$this->add_filter( 'customify_filter_fields', 'maybe_enhance_dark_mode_control', 120, 1 );

		$this->add_filter( 'customify_final_config', 'alter_master_controls_connected_fields', 100, 1 );

		/**
		 * Scripts enqueued in the Customizer.
		 */
		$this->add_action( 'customize_controls_enqueue_scripts', 'enqueue_admin_customizer_scripts', 10 );

		/**
		 * Add color palettes usage to site data.
		 */
		$this->add_filter( 'customify_style_manager_get_site_data', 'add_palettes_to_site_data', 10, 1 );

		$this->add_filter( 'language_attributes', 'add_dark_mode_data_attribute', 10, 2 );
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

			if ( $option_config['type'] === 'sm_switch' ) {
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
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type' => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'   => 'sm_advanced_palette_source',
					'label'        => esc_html__( 'Palette Source', '__plugin_txtd' ),
				],
				'sm_advanced_palette_output'    => [
					'type'         => 'text',
					'live'         => true,
					'default'      => '[]',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type' => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'   => 'sm_advanced_palette_output',
					'label'        => esc_html__( 'Palette Output', '__plugin_txtd' ),
					'css'          => [
						[
							'selector'        => ':root',
							'property'        => 'dummy-property',
							'callback_filter' => 'sm_palette_output_cb',
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
							'property'        => '--sm-property',
							'selector'        => ':root',
							'unit'            => '',
							'callback_filter' => 'sm_variation_range_cb',
						],
					],
				],
				'sm_text_color_switch_master'   => [
					'type'             => 'sm_switch',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_text_color_switch_master',
					'label'            => esc_html__( 'Text Master', '__plugin_txtd' ),
					'live'             => true,
					'default'          => 'off',
					'connected_fields' => [],
					'css'              => [],
					'choices'          => [
						'off' => esc_html__( 'Off', '__plugin_txtd' ),
						'on'  => esc_html__( 'On', '__plugin_txtd' ),
					],
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
					'type'             => 'sm_switch',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_accent_color_switch_master',
					'label'            => esc_html__( 'Accent Master', '__plugin_txtd' ),
					'live'             => true,
					'default'          => 'on',
					'connected_fields' => [],
					'css'              => [],
					'choices'          => [
						'off' => esc_html__( 'Off', '__plugin_txtd' ),
						'on'  => esc_html__( 'On', '__plugin_txtd' ),
					],
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
					'desc'         => wp_kses( __( 'Adjust <strong>how much color</strong> you want to add to your site. For more control over elements, you can edit them individually.', '__plugin_txtd' ), array( 'strong' => [] ) ),
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
