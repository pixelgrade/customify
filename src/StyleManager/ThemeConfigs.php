<?php
/**
 * Theme configurations class.
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
 * Provides the theme configurations logic.
 *
 * @since 3.0.0
 */
class ThemeConfigs extends AbstractHookProvider {

	/**
	 * The external theme config for the current active theme.
	 *
	 * @since   3.0.0
	 *
	 * @var     array
	 */
	public array $external_theme_config = [];

	/**
	 * Design assets.
	 *
	 * @var DesignAssets
	 */
	protected DesignAssets $design_assets;

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
	 * @param DesignAssets    $design_assets Design assets.
	 * @param LoggerInterface $logger Logger.
	 */
	public function __construct(
		DesignAssets $design_assets,
		LoggerInterface $logger
	) {
		$this->design_assets = $design_assets;
		$this->logger = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		/**
		 * Handle the external theme configuration logic. We use a late priority to be able to overwrite if we have to.
		 */
		$this->add_filter( 'customify_filter_fields', 'maybe_activate_external_theme_config', 10, 1 );
		$this->add_filter( 'customify_filter_fields', 'maybe_apply_external_theme_config', 100, 1 );
		// Maybe the theme has instructed us to do things like removing sections or controls.
		$this->add_action( 'customize_register', 'maybe_process_external_theme_config_extras', 11 );

		/**
		 * Determine if we should output the theme root JSON in the Customizer for easier copy&paste to cloud.
		 */
		if ( defined( 'CUSTOMIFY_SM_LOAD_THEME_ROOT_CONFIG' ) && true === CUSTOMIFY_SM_LOAD_THEME_ROOT_CONFIG ) {
			$this->add_filter( 'customize_controls_print_styles', 'maybe_output_json_external_config', 0 );
		}
	}

	/**
	 * Determine if Style Manager is supported.
	 *
	 * @since 1.7.4
	 *
	 * @return bool
	 */
	public function is_supported(): bool {
		// For now we will only use the fact that Style Manager is supported.
		return apply_filters( 'customify_theme_configs_are_supported', is_sm_supported() );
	}

	/**
	 * Get the themes configuration.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array
	 */
	protected function get_theme_configs( $skip_cache = false ): array {
		$theme_configs = $this->design_assets->get_entry( 'theme_configs', $skip_cache );
		if ( is_null( $theme_configs ) ) {
			$theme_configs = [];
		}

		return apply_filters( 'customify_get_theme_configs', $theme_configs );
	}

	/**
	 * Maybe activate an external theme config.
	 *
	 * If the conditions are met, activate an external theme config by declaring support for the style manager and saving the config.
	 *
	 * @since 3.0.0
	 *
	 * @param array $config This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'
	 *
	 * @return array
	 */
	protected function maybe_activate_external_theme_config( array $config ): array {
		// If somebody else already declared support for the Style Manager, we stop and let them have it.
		if ( $this->is_supported() ) {
			return $config;
		}

		// First gather details about the current (parent) theme.
		$theme = wp_get_theme( get_template() );
		// Bail if for some strange reason we couldn't find the theme.
		if ( ! $theme->exists() ) {
			return $config;
		}

		// Now determine if we have a theme config for the current theme.
		$theme_configs = $this->get_theme_configs();

		// We will go through every theme config and determine it's match score
		foreach ( $theme_configs as $hashid => $theme_config ) {
			// Loose matching means that the theme doesn't have to match all the conditions.
			$loose_match = false;
			if ( ! empty( $theme_config['loose_match'] ) ) {
				$loose_match = true;
			}

			$matches = 0;
			$total   = 0;
			if ( ! empty( $theme_config['name'] ) && $theme_config['name'] == $theme->get( 'Name' ) ) {
				$matches ++;
				$total ++;
			}
			if ( ! empty( $theme_config['slug'] ) && $theme_config['slug'] == $theme->get_stylesheet() ) {
				$matches ++;
				$total ++;
			}
			if ( ! empty( $theme_config['txtd'] ) && $theme_config['txtd'] == $theme->get( 'TextDomain' ) ) {
				$matches ++;
				$total ++;
			}

			$theme_configs[ $hashid ]['match_score'] = 0;
			if ( true === $loose_match ) {
				$theme_configs[ $hashid ]['match_score'] = $matches;
			} elseif ( $matches === $total ) {
				$theme_configs[ $hashid ]['match_score'] = $matches;
			}
		}

		// Now we will order the theme configs by match scores, descending and get the highest matching candidate, if any.
		$theme_configs         = ArrayHelpers::array_orderby( $theme_configs, 'match_score', SORT_DESC );
		$external_theme_config = array_shift( $theme_configs );
		// If we've ended up with a theme config with a zero match score, bail.
		if ( empty( $external_theme_config['match_score'] ) || empty( $external_theme_config['config']['sections'] ) ) {
			return $config;
		}

		// Now we have a theme config to work with. Save it for later use.
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->external_theme_config = $external_theme_config;

		// Declare support for the Style Manager if there is such a section in the config
		if ( isset( $external_theme_config['config']['sections']['style_manager_section'] ) ) {
			add_theme_support( 'customizer_style_manager' );
		}

		return $config;
	}

	/**
	 * Maybe apply an external theme config.
	 *
	 * If the conditions are met, apply an external theme config. Right now we are only handling sections and their controls.
	 *
	 * @since 3.0.0
	 *
	 * @param array $config This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'
	 *
	 * @return array
	 */
	protected function maybe_apply_external_theme_config( array $config ): array {
		// Bail if we have no external theme config data.
		if ( empty( $this->external_theme_config ) ) {
			return $config;
		}

		// Apply the theme config.
		// If we are dealing with the Customify default config, we need a clean slate, sort of.
		if ( 'customify_defaults' === $config['opt-name'] ) {
			// We will save the Style Manager config so we can merge with it. But the rest goes away.
			$style_manager_section = [];
			if ( isset( $config['sections']['style_manager_section'] ) ) {
				$style_manager_section = $config['sections']['style_manager_section'];
			}

			$config['opt-name'] = get_template() . '_options';
			if ( ! empty( $this->external_theme_config['config']['opt-name'] ) ) {
				$config['opt-name'] = $this->external_theme_config['config']['opt-name'];
			}

			$config['sections'] = [
				'style_manager_section' => $style_manager_section,
			];
		}

		// Now merge things.
		$config['sections'] = ArrayHelpers::array_merge_recursive_distinct( $config['sections'], $this->external_theme_config['config']['sections'] );

		return $config;
	}

	/**
	 * Maybe process certain "commands" from the external theme config.
	 *
	 * Mainly things like removing sections, controls, etc.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 *
	 */
	protected function maybe_process_external_theme_config_extras( \WP_Customize_Manager $wp_customize ) {
		// Bail if we have no external theme config data.
		if ( empty( $this->external_theme_config ) ) {
			return;
		}

		// Maybe remove panels
		if ( ! empty( $this->external_theme_config['config']['remove_panels'] ) ) {
			// Standardize it.
			if ( is_string( $this->external_theme_config['config']['remove_panels'] ) ) {
				$this->external_theme_config['config']['remove_panels'] = [ $this->external_theme_config['config']['remove_panels'] ];
			}

			foreach ( $this->external_theme_config['config']['remove_panels'] as $panel_id ) {
				$wp_customize->remove_panel( $panel_id );
			}
		}

		// Maybe remove sections
		if ( ! empty( $this->external_theme_config['config']['remove_sections'] ) ) {
			// Standardize it.
			if ( is_string( $this->external_theme_config['config']['remove_sections'] ) ) {
				$this->external_theme_config['config']['remove_sections'] = [ $this->external_theme_config['config']['remove_sections'] ];
			}

			foreach ( $this->external_theme_config['config']['remove_sections'] as $section_id ) {

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

		// Maybe remove settings
		if ( ! empty( $this->external_theme_config['config']['remove_settings'] ) ) {
			// Standardize it.
			if ( is_string( $this->external_theme_config['config']['remove_settings'] ) ) {
				$this->external_theme_config['config']['remove_settings'] = [ $this->external_theme_config['config']['remove_settings'] ];
			}

			foreach ( $this->external_theme_config['config']['remove_settings'] as $setting_id ) {
				$wp_customize->remove_setting( $setting_id );
			}
		}

		// Maybe remove controls
		if ( ! empty( $this->external_theme_config['config']['remove_controls'] ) ) {
			// Standardize it.
			if ( is_string( $this->external_theme_config['config']['remove_controls'] ) ) {
				$this->external_theme_config['config']['remove_controls'] = [ $this->external_theme_config['config']['remove_controls'] ];
			}

			foreach ( $this->external_theme_config['config']['remove_controls'] as $control_id ) {
				$wp_customize->remove_control( $control_id );
			}
		}
	}

	/**
	 * Output the JSON in the Customizer page source.
	 */
	protected function maybe_output_json_external_config() {
		if ( ! empty( $this->external_theme_config['config'] ) ) {
			// Also output the JSON in a special hidden div for easy copy pasting.
			// Also remove any multiple tabs.
			echo "\n" . '<!--' . "\n" . 'Just copy&paste this:' . "\n" . "\n" . trim( str_replace( '\t\t', '', json_encode( $this->external_theme_config['config'] ) ) ) . "\n" . "\n" . '-->' . "\n";
		}
	}
}
