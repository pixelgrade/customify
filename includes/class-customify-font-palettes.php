<?php
/**
 * This is the class that handles the logic for Font Palettes.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       1.7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Font_Palettes' ) ) :

class Customify_Font_Palettes {

	/**
	 * Holds the only instance of this class.
	 * @var     null|Customify_Font_Palettes
	 * @access  protected
	 * @since   1.7.4
	 */
	protected static $_instance = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.4
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * Initialize this module.
	 *
	 * @since 1.7.4
	 */
	public function init() {
		// Hook up.
		$this->add_hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since 1.7.4
	 */
	public function add_hooks() {
		/*
		 * Handle the font palettes preprocessing.
		 */
		add_filter( 'customify_get_font_palettes', array( $this, 'preprocess_config' ), 5, 1 );

		/*
		 * Handle the Customizer Style Manager section config.
		 */
		add_filter( 'customify_filter_fields', array( $this, 'add_style_manager_section_master_fonts_config' ), 12, 1 );
		// This needs to come after the external theme config has been applied
		add_filter( 'customify_filter_fields', array( $this, 'add_current_palette_control' ), 110, 1 );
		add_filter( 'customify_final_config', array( $this, 'standardize_connected_fields' ), 10, 1 );

		/*
		 * Scripts enqueued in the Customizer.
		 */
		add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_scripts' ), 10 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_scripts' ), 10 );

		/*
		 * Handle the logic on settings update/save.
		 */
		add_action( 'customize_save_after', array( $this, 'update_custom_palette_in_use' ), 10, 1 );

		/**
		 * Add font palettes usage to site data.
		 */
		add_filter( 'customify_style_manager_get_site_data', array( $this, 'add_palettes_to_site_data' ), 10, 1 );

		// Add data to be passed to JS.
		add_filter( 'customify_localized_js_settings', array( $this, 'add_to_localized_data' ), 10, 1 );
	}

	/**
	 * Register Customizer admin scripts
	 */
	public function register_admin_customizer_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script( PixCustomifyPlugin()->get_slug() . '-regression',
			plugins_url( 'js/vendor/regression' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
			array(), PixCustomifyPlugin()->get_version() );
		wp_register_script( PixCustomifyPlugin()->get_slug() . '-font-palettes',
			plugins_url( 'js/customizer/font-palettes' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
			array(
				PixCustomifyPlugin()->get_slug() . '-regression',
				'jquery',
				PixCustomifyPlugin()->get_slug() . '-fontfields',
			),
			PixCustomifyPlugin()->get_version() );
	}

	/**
	 * Enqueue Customizer admin scripts
	 */
	public function enqueue_admin_customizer_scripts() {
		// If there is no font palettes support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-font-palettes' );
	}

	/**
	 * Preprocess the font palettes configuration.
	 *
	 * Things like transforming font_size_line_height_points to a polynomial function for easy use client side,
	 * or processing the styles intervals and making sure that we get to a state where there are no overlaps and the order is right.
	 *
	 * @since 1.7.4
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function preprocess_config( $config ) {
		if ( empty( $config ) ) {
			return $config;
		}

		foreach ( $config as $palette_id => $palette_config ) {
			$config[ $palette_id ] = $this->preprocess_palette_config( $palette_config );
		}

		return $config;
	}

	/**
	 * Preprocess a font palette config before using it.
	 *
	 * @since 1.7.4
	 *
	 * @param array $palette_config
	 *
	 * @return array
	 */
	private function preprocess_palette_config( $palette_config ) {
		if ( empty( $palette_config ) ) {
			return $palette_config;
		}

		global $wp_customize;
		// We only need to do the fonts logic preprocess when we are in the Customizer.
		if ( ! empty( $wp_customize ) && $wp_customize instanceof WP_Customize_Manager && ! empty( $palette_config['fonts_logic'] ) ) {
			$palette_config['fonts_logic'] = $this->preprocess_fonts_logic_config( $palette_config['fonts_logic'] );
		}

		return $palette_config;
	}

	/**
	 * Before using a font logic config, preprocess it to allow for standardization, fill up of missing info, etc.
	 *
	 * @since 1.7.4
	 *
	 * @param array $fonts_logic_config
	 *
	 * @return array
	 */
	private function preprocess_fonts_logic_config( $fonts_logic_config ) {
		if ( empty( $fonts_logic_config ) ) {
			return $fonts_logic_config;
		}

		foreach ( $fonts_logic_config as $font_setting_id => $font_logic ) {

			if ( ! empty( $font_logic['reset'] ) ) {
				$fonts_logic_config[ $font_setting_id ]['reset'] = true;
				continue;
			}

			if ( empty( $font_logic['font_family'] ) ) {
				// If we don't have a font family we can't do much with this config - remove it.
				unset( $fonts_logic_config[ $font_setting_id ] );
				continue;
			}

			// We don't need font types as we will determine them dynamically.
			unset( $fonts_logic_config[ $font_setting_id ]['type'] );
			unset( $fonts_logic_config[ $font_setting_id ]['font_type'] );

			// If we have been given a `font_size_multiplier` value, make sure it is a float.
			if ( isset( $fonts_logic_config[ $font_setting_id ]['font_size_multiplier'] ) ) {
				$fonts_logic_config[ $font_setting_id ]['font_size_multiplier'] = (float) $fonts_logic_config[ $font_setting_id ]['font_size_multiplier'];
				if ( $fonts_logic_config[ $font_setting_id ]['font_size_multiplier'] <= 0 ) {
					// We reject negative or 0 values.
					$fonts_logic_config[ $font_setting_id ]['font_size_multiplier'] = 1.0;
				}
			} else {
				// By default we use 1.
				$fonts_logic_config[ $font_setting_id ]['font_size_multiplier'] = 1.0;
			}

			// Process the font_styles_intervals and make sure that they are in the right order and not overlapping.
			if ( ! empty( $font_logic['font_styles_intervals'] ) && is_array( $font_logic['font_styles_intervals'] ) ) {
				// Initialize the list with the first one found.
				$font_styles_intervals = array( array_shift( $font_logic['font_styles_intervals'] ) );
				// Make sure that this interval has a start
				if ( ! isset( $font_styles_intervals[0]['start'] ) ) {
					$font_styles_intervals[0]['start'] = 0;
				}

				foreach ( $font_logic['font_styles_intervals'] as $font_styles_interval ) {
					// Make sure that the interval has a start
					if ( ! isset( $font_styles_interval['start'] ) ) {
						$font_styles_interval['start'] = 0;
					}
					// Go through the current font_styles and determine the place where this interval should fit in.
					for ( $i = 0; $i < count( $font_styles_intervals ); $i++ ) {
						// Determine if the new interval overlaps with this existing one.
						if ( ! isset( $font_styles_intervals[$i]['end'] ) ) {
							// Since this interval is without end, there is nothing after it.
							// We need to adjust the old interval end.
							if ( $font_styles_intervals[ $i ]['start'] < $font_styles_interval['start'] ) {
								$font_styles_intervals[ $i ]['end'] = $font_styles_interval['start'];
							} else {
								if ( ! isset( $font_styles_interval['end'] ) ) {
									// We need to delete the old interval altogether.
									unset($font_styles_intervals[ $i ]);
									$i--;
									continue;
								} else {
									// Adjust the old interval and insert in front of it.
									$font_styles_intervals[ $i ]['end'] = $font_styles_interval['end'];
									$font_styles_intervals = array_slice( $font_styles_intervals, 0, $i ) + array( $font_styles_interval );
									break;
								}
							}
						} else {
							if ( $font_styles_intervals[ $i ]['end'] > $font_styles_interval['start'] ) {
								// We need to shrink this interval and make room for the new interval.
								$font_styles_intervals[ $i ]['end'] = $font_styles_interval['start'];
							} else {
								// There is not overlap. Move to the next one.
								continue;
							}

							if ( ! isset( $font_styles_interval['end'] ) ) {
								// Everything after the existing interval is gone and the new one takes precedence.
								array_splice( $font_styles_intervals, $i + 1, count( $font_styles_intervals ), array( $font_styles_interval ) );
								break;
							} else {
								// Now go forward and see where the end of the new interval fits in.
								for ( $j = $i + 1; $j < count( $font_styles_intervals ); $j ++ ) {
									if ( $font_styles_intervals[ $j ]['start'] < $font_styles_interval['end'] ) {
										// We have an overlapping after-interval.
										if ( ! isset( $font_styles_intervals[ $j ]['end'] ) ) {
											// Since this interval is without end, there is nothing after it.
											$font_styles_intervals[ $j ]['start'] = $font_styles_interval['end'];
											break;
										} elseif ( $font_styles_intervals[ $j ]['end'] <= $font_styles_interval['end'] ) {
											// We need to delete this interval since it is completely overwritten by the new one.
											unset( $font_styles_intervals[ $j ] );
											$j --;
											continue;
										} else {
											// The new interval partially overlaps with the old one. Adjust.
											$font_styles_intervals[ $j ]['end'] = $font_styles_interval['end'];
											break;
										}
									} else {
										// We can insert the new interval since this interval is after it
										break;
									}
								}

								// Insert the new interval.
								array_splice( $font_styles_intervals, $j, 0, array( $font_styles_interval ) );
								break;
							}
						}
					}

					// If we have reached the end of the list, we will insert it at the end.
					if (  $i === count( $font_styles_intervals ) ) {
						array_push( $font_styles_intervals, $font_styles_interval );
					}
				}

				// We need to do a last pass and ensure no breaks in the intervals. We need them to be continuous.
				// We will extend intervals to their next (right-hand) neighbour to achieve continuity.
				if ( count( $font_styles_intervals ) > 1 ) {
					// The first interval should start at zero, just in case.
					$font_styles_intervals[0]['start'] = 0;
					for( $i = 1; $i < count( $font_styles_intervals ); $i++ ) {
						// Extend the previous interval, just in case.
						$font_styles_intervals[ $i-1 ]['end'] = $font_styles_intervals[ $i ]['start'];
					}
				}

				// The last interval should not have an end.
				unset( $font_styles_intervals[ count( $font_styles_intervals )-1 ]['end'] );

				// Finally, go through each font style and standardize it.
				foreach( $font_styles_intervals as $key => $value ) {

					// Since there is not font value "font_weight", but "font_variant", treat it as such.
					// Font weight is only a CSS value.
					// Font variant "splits" into font-weight and (maybe) "font-style".
					if ( isset( $value['font_weight'] ) && ! isset( $value['font_variant'] ) ) {
						$font_styles_intervals[ $key ]['font_variant'] = $value['font_weight'];
						unset( $font_styles_intervals[ $key ]['font_weight'] );
					}

					// Standardize the font variant.
					if ( isset( $font_styles_intervals[ $key ]['font_variant'] ) ) {
						$font_styles_intervals[ $key ]['font_variant'] = Customify_Fonts_Global::standardizeFontVariant( $font_styles_intervals[ $key ]['font_variant'] );
					}

					if ( isset( $value['letter_spacing'] ) ) {
						// We have some special values for letter-spacing that need to taken care of.
						if ( 'normal' === $value['letter_spacing'] ) {
							$value['letter_spacing'] = 0;
						}
						$font_styles_intervals[ $key ]['letter_spacing'] = Customify_Fonts_Global::standardizeNumericalValue( $value['letter_spacing'] );
					}

					// If we have been given a `font_size_multiplier` value, make sure it is a positive float.
					if ( isset( $font_styles_intervals[ $key ]['font_size_multiplier'] ) ) {
						$font_styles_intervals[ $key ]['font_size_multiplier'] = (float) $font_styles_intervals[ $key ]['font_size_multiplier'];
						if ( $font_styles_intervals[ $key ]['font_size_multiplier'] <= 0 ) {
							// We reject negative or 0 values.
							$font_styles_intervals[ $key ]['font_size_multiplier'] = 1.0;
						}
					} else {
						// By default we use 1, meaning no effect.
						$font_styles_intervals[ $key ]['font_size_multiplier'] = 1.0;
					}

					// We really don't want font_size or line_height in here,
					// since line_height is determined through the curve that matches it to a font_size;
					// and also font_size is the main driving force behind the font palettes logic; so it is absurd to have it here.
					unset( $font_styles_intervals[ $key ]['font_size'] );
					unset( $font_styles_intervals[ $key ]['line_height'] );
				}

				$fonts_logic_config[ $font_setting_id ]['font_styles_intervals'] = $font_styles_intervals;
			}
		}

		return $fonts_logic_config;
	}

	/**
	 * Get the font palettes configuration.
	 *
	 * @since 1.7.4
	 *
	 * @param bool $skip_cache Optional. Whether to use the cached config or fetch a new one.
	 *
	 * @return array
	 */
	public function get_palettes( $skip_cache = false ) {
		// Make sure that the Design Assets class is loaded.
		require_once 'lib/class-customify-design-assets.php';

		// Get the design assets data.
		$design_assets = Customify_Design_Assets::instance()->get( $skip_cache );
		if ( false === $design_assets || empty( $design_assets['font_palettes'] ) ) {
			$config = $this->get_default_config();
		} else {
			$config = $design_assets['font_palettes'];
		}

		return apply_filters( 'customify_get_font_palettes', $config );
	}

	/**
	 * Determine if Font Palettes are supported.
	 *
	 * @since 1.7.4
	 *
	 * @return bool
	 */
	public function is_supported() {
		$has_support = (bool) current_theme_supports( 'style_manager_font_palettes' );
		return apply_filters( 'style_manager_font_palettes_are_supported', $has_support );
	}

	/**
	 * Setup the Style Manager Customizer section master fonts config.
	 *
	 * This handles the base configuration for the controls in the Style Manager section. We expect other parties (e.g. the theme),
	 * to come and fill up the missing details (e.g. connected fields).
	 *
	 * @since 1.7.4
	 *
	 * @param array $config This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'.
	 *
	 * @return array
	 */
	public function add_style_manager_section_master_fonts_config( $config ) {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		if ( ! isset( $config['sections']['style_manager_section'] ) ) {
			$config['sections']['style_manager_section'] = array();
		}

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section'] = Customify_Array::array_merge_recursive_distinct( $config['sections']['style_manager_section'], array(
			'options' => array(
				'sm_font_palette' => array(
					'type'         => 'preset',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type' => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'   => 'sm_font_palette',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'         => true,
					'priority'     => 5,
					'label'        => esc_html__( 'Select a font palette:', 'customify' ),
					'desc'         => esc_html__( 'Conveniently change the design of your site with font palettes. Easy as pie.', 'customify' ),
					'default'      => 'julia',
					'choices_type' => 'font_palette',
					'choices'      => $this->get_palettes(),
				),
				'sm_font_primary'              => array(
					'type'             => 'font',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_font_primary',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'priority'         => 7,
					'label'            => esc_html__( 'Font Primary', 'customify' ),
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => 'regular',
						'font-size'      => 20,
						'line-height'    => 1.25,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),
					// Sub Fields Configuration
					'fields'   => array(
						// These subfields are disabled because they are calculated through the font palette logic.
						'font-size'       => false,
						'font-weight'     => false,
						'line-height'     => false,
						'letter-spacing'  => false,
						'text-align'      => false,
						'text-transform'  => false,
						'text-decoration' => false,
					),
					'connected_fields' => array(),
				),
				'sm_font_secondary'              => array(
					'type'             => 'font',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_font_secondary',
					'live'             => true,
					'priority'         => 7.1,
					'label'            => esc_html__( 'Font Secondary', 'customify' ),
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '300',
						'font-size'      => 10,
						'line-height'    => 1.625,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),
					// Sub Fields Configuration
					'fields'   => array(
						// These subfields are disabled because they are calculated through the font palette logic.
						'font-size'       => false,
						'font-weight'     => false,
						'line-height'     => false,
						'letter-spacing'  => false,
						'text-align'      => false,
						'text-transform'  => false,
						'text-decoration' => false,
					),
					'connected_fields' => array(),
				),
				'sm_font_body'              => array(
					'type'             => 'font',
					'setting_type'     => 'option',
					'setting_id'       => 'sm_font_body',
					'live'             => true,
					'priority'         => 7.2,
					'label'            => esc_html__( 'Font Body', 'customify' ),
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => '300',
						'font-size'      => 14,
						'line-height'    => 1.6,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),
					// Sub Fields Configuration
					'fields'   => array(
						// These subfields are disabled because they are calculated through the font palette logic.
						'font-size'       => false,
						'font-weight'     => false,
						'line-height'     => false,
						'letter-spacing'  => false,
						'text-align'      => false,
						'text-transform'  => false,
						'text-decoration' => false,
					),
					'connected_fields' => array(),
				),
				'sm_font_accent'              => array(
					'type'             => 'font',
					// We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
					'setting_type'     => 'option',
					// We will force this setting id preventing prefixing and other regular processing.
					'setting_id'       => 'sm_font_accent',
					// We don't want to refresh the preview window, even though we have no direct effect on it through this field.
					'live'             => true,
					'priority'         => 7,
					'label'            => esc_html__( 'Font Accent', 'customify' ),
					'default'  => array(
						'font-family'    => 'Montserrat',
						'font-weight'    => 'regular',
						'font-size'      => 20,
						'line-height'    => 1.25,
						'letter-spacing' => 0.029,
						'text-transform' => 'uppercase'
					),
					// Sub Fields Configuration
					'fields'   => array(
						// These subfields are disabled because they are calculated through the font palette logic.
						'font-size'       => false,
						'font-weight'     => false,
						'line-height'     => false,
						'letter-spacing'  => false,
						'text-align'      => false,
						'text-transform'  => false,
						'text-decoration' => false,
					),
					'connected_fields' => array(),
				),
				'sm_font_palettes_spacing_bottom' => array(
					'type'       => 'html',
					'html'       => '',
					'setting_id' => 'sm_font_palettes_spacing_bottom',
					'priority'   => 31,
				),
			),
		) );

		return $config;
	}

	/**
	 * Add the current font palette control to the Style Manager section.
	 *
	 * @since 1.7.4
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function add_current_palette_control( $config ) {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		if ( ! isset( $config['sections']['style_manager_section'] ) ) {
			$config['sections']['style_manager_section'] = array();
		}

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section']['options'] = array(
			'sm_current_font_palette' => array(
				'type'       => 'html',
				'setting_id' => 'sm_current_font_palette',
				'priority'   => 3,
				'html'       =>
					'<div class="sm-tabs">' . "\n" .
						'<div class="sm-tabs__item" data-target="palettes">' . esc_html__( 'Palettes', 'customify' ) . '</div>' . "\n" .
						'<div class="sm-tabs__item" data-target="advanced">' . esc_html__( 'Advanced', 'customify' ) . '</div>' . "\n" .
					'</div>',
				)
            ) + $config['sections']['style_manager_section']['options'];

		return $config;
	}

	/**
	 * Process any configured connected fields that relate to fonts and standardize their config.
	 *
	 * Think things like filling up the default font_size if not present.
	 *
	 * @since 1.7.4
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function standardize_connected_fields( $config ) {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		$style_manager_options = $config['sections']['style_manager_section']['options'];
		$master_font_controls_ids = $this->get_all_master_font_controls_ids( $style_manager_options );
		if ( empty( $master_font_controls_ids ) ) {
			return $config;
		}

		foreach ( $master_font_controls_ids as $id ) {
			if ( ! empty( $style_manager_options[ $id ]['connected_fields'] ) ) {
				$connected_fields_config = array();
				foreach ( $style_manager_options[ $id ]['connected_fields'] as $key => $value ) {
					// If we have a shorthand connected field config, change it to a standard one.
					if ( ! is_array( $value ) ) {
						$key = $value;
						$value = array();
					}

					$option_config = $this->get_option_config( $key, $config );
					if ( empty( $option_config ) ) {
						continue;
					}

					// If we didn't get a font_size we will try and grab the default value for the connected field.
					if ( ! isset( $value['font_size'] ) ) {
						if ( isset( $option_config['default']['font-size'] ) ) {
							$value['font_size'] = $option_config['default']['font-size'];
						} else {
							$value['font_size'] = false;
						}
					}
					// Finally, standardize it.
					$value['font_size'] = Customify_Fonts_Global::standardizeNumericalValue( $value['font_size'], 'font-size', $option_config );

					$connected_fields_config[ $key ] = $value;
				}

				$config['sections']['style_manager_section']['options'][ $id ]['connected_fields'] = $connected_fields_config;
			}
		}

		return $config;
	}

	/**
	 * Get the Customify configuration of a certain option.
	 *
	 * @param string $option_id
	 *
	 * @return array|false The option config or false on failure.
	 */
	private function get_option_config( $option_id, $config ) {
		// We need to search for the option configured under the given id (the array key)
		if ( isset ( $config['panels'] ) ) {
			foreach ( $config['panels'] as $panel_id => $panel_settings ) {
				if ( isset( $panel_settings['sections'] ) ) {
					foreach ( $panel_settings['sections'] as $section_id => $section_settings ) {
						if ( isset( $section_settings['options'] ) ) {
							foreach ( $section_settings['options'] as $id => $option_config ) {
								if ( $id === $option_id ) {
									return $option_config;
								}
							}
						}
					}
				}
			}
		}

		if ( isset ( $config['sections'] ) ) {
			foreach ( $config['sections'] as $section_id => $section_settings ) {
				if ( isset( $section_settings['options'] ) ) {
					foreach ( $section_settings['options'] as $id => $option_config ) {
						if ( $id === $option_id ) {
							return $option_config;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get the default (hard-coded) font palettes configuration.
	 *
	 * This is only a fallback config in case we can't communicate with the cloud, the first time.
	 *
	 * @since 1.7.4
	 *
	 * @return array
	 */
	protected function get_default_config() {
		$default_config = array(
			'gema' => array(
				'label'   => esc_html__( 'Gema', 'customify' ),
				'preview' => array(
					// Font Palette Name
					'title'            => esc_html__( 'Gema', 'customify' ),
					'description'      => esc_html__( 'A graceful nature, truly tasteful and polished.', 'customify' ),
					'background_image_url' => 'https://cloud.pixelgrade.com/wp-content/uploads/2018/09/font-palette-thin.png',

					// Use the following options to style the preview card fonts
					// Including font-family, size, line-height, weight, letter-spacing and text transform
					'title_font'       => array(
						'font' => 'font_primary',
						'size' => 32,
					),
					'description_font' => array(
						'font' => 'font_body',
						'size' => 16,
					),
				),

				'fonts_logic' => array(
					// Primary is used for main headings [Display, H1, H2, H3]
					'sm_font_primary' => array(
						// Font loaded when a palette is selected
						'font_family'      => 'Montserrat',
						// Load all these fonts weights.
						'font_weights'     => array( 100, 300, 700 ),
						// "Generate" the graph to be used for font-size and line-height.
						'font_size_to_line_height_points' => array(
							array( 17, 1.7 ),
							array( 48, 1.2 ),
						),

						// Define how fonts will look based on the font size.
						'font_styles_intervals'      => array(
							array(
								'start'          => 10,
								'font_weight'    => 300,
								'letter_spacing' => '0.03em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 12,
								'font_weight'    => 700,
								'letter_spacing' => '0em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 18,
								'font_weight'    => 100,
								'letter_spacing' => '0.03em',
								'text_transform' => 'uppercase',
							),
						),
					),

					// Secondary font is used for smaller headings [H4, H5, H6], including meta details
					'sm_font_secondary' => array(
						'font_family'      => 'Montserrat',
						'font_weights'     => array( 200, 400 ),
						'font_size_to_line_height_points' => array(
							array( 10, 1.6 ),
							array( 18, 1.5 )
						),
						'font_styles_intervals'      => array(
							array(
								'start'          => 0,
								'font_weight'    => 200,
								'letter_spacing' => '0.03em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 13,
								'font_weight'    => 'regular',
								'letter_spacing' => '0.015em',
								'text_transform' => 'uppercase',
							),
						),
					),

					// Used for Body Font [eg. entry-content]
					'sm_font_body' => array(
						'font_family'      => 'Montserrat',
						'font_weights'     => array( 200, '200italic', 700, '700italic' ),
						'font_size_to_line_height_points' => array(
							array( 15, 1.8 ),
							array( 18, 1.7 ),
						),

						// Define how fonts will look based on their size
						'font_styles_intervals'      => array(
							array(
								'font_weight'    => 200,
								'letter_spacing' => 0,
								'text_transform' => 'none',
							),
						),
					),
				),
			),
			'julia' => array(
				'label'   => esc_html__( 'Julia', 'customify' ),
				'preview' => array(
					// Font Palette Name
					'title'            => esc_html__( 'Julia', 'customify' ),
					'description'      => esc_html__( 'A graceful nature, truly tasteful and polished.', 'customify' ),
					'background_image_url' => 'https://cloud.pixelgrade.com/wp-content/uploads/2018/09/font-palette-serif.png',

					// Use the following options to style the preview card fonts
					// Including font-family, size, line-height, weight, letter-spacing and text transform
					'title_font'       => array(
						'font' => 'font_primary',
						'size' => 30,
					),
					'description_font' => array(
						'font' => 'font_body',
						'size' => 17,
					),
				),

				'fonts_logic' => array(
					// Primary is used for main headings [Display, H1, H2, H3]
					'sm_font_primary' => array(
						// Font loaded when a palette is selected
						'font_family'      => 'Lora',
						// Load all these fonts weights.
						'font_weights'     => array( 700 ),
						// "Generate" the graph to be used for font-size and line-height.
						'font_size_to_line_height_points' => array(
							array( 24, 1.25 ),
							array( 66, 1.15 ),
						),

						// Define how fonts will look based on the font size.
						'font_styles_intervals'      => array(
							array(
								'start'          => 0,
								'font_weight'    => 700,
								'letter_spacing' => '0em',
								'text_transform' => 'none',
							),
						),
					),

					// Secondary font is used for smaller headings [H4, H5, H6], including meta details
					'sm_font_secondary' => array(
						'font_family'      => 'Montserrat',
						'font_weights'     => array( 'regular', 600 ),
						'font_size_to_line_height_points' => array(
							array( 14, 1.3 ),
							array( 16, 1.2 )
						),
						'font_styles_intervals'      => array(
							array(
								'start'          => 0,
								'font_weight'    => 600,
								'letter_spacing' => '0.154em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 13,
								'font_weight'    => 600,
								'letter_spacing' => '0em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 14,
								'font_weight'    => 600,
								'letter_spacing' => '0.1em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 16,
								'font_weight'    => 600,
								'letter_spacing' => '0em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 17,
								'font_weight'    => 600,
								'letter_spacing' => '0em',
								'text_transform' => 'none',
							),
						),
					),

					// Used for Body Font [eg. entry-content]
					'sm_font_body' => array(
						'font_family'      => 'PT Serif',
						'font_weights'     => array( 400, '400italic', 700, '700italic' ),
						'font_size_to_line_height_points' => array(
							array( 15, 1.7 ),
							array( 18, 1.5 ),
						),

						// Define how fonts will look based on their size
						'font_styles_intervals'      => array(
							array(
								'start'          => 0,
								'font_weight'    => 'regular',
								'letter_spacing' => 0,
								'text_transform' => 'none',
							),
						),
					),
				),
			),
			'patch' => array(
				'label'   => esc_html__( 'Patch', 'customify' ),
				'preview' => array(
					// Font Palette Name
					'title'            => esc_html__( 'Patch', 'customify' ),
					'description'      => esc_html__( 'A graceful nature, truly tasteful and polished.', 'customify' ),
					'background_image_url' => 'https://cloud.pixelgrade.com/wp-content/uploads/2018/09/font-palette-lofty.png',

					// Use the following options to style the preview card fonts
					// Including font-family, size, line-height, weight, letter-spacing and text transform
					'title_font'       => array(
						'font' => 'font_primary',
						'size' => 26,
					),
					'description_font' => array(
						'font' => 'font_body',
						'size' => 16,
					),
				),

				'fonts_logic' => array(
					// Primary is used for main headings [Display, H1, H2, H3]
					'sm_font_primary' => array(
						// Font loaded when a palette is selected
						'font_family'      => 'Oswald',
						// Load all these fonts weights.
						'font_weights'     => array( 300, 400, 500 ),
						// "Generate" the graph to be used for font-size and line-height.
						'font_size_to_line_height_points' => array(
							array( 20, 1.55 ),
							array( 56, 1.25 ),
						),

						// Define how fonts will look based on the font size.
						'font_styles_intervals'      => array(
							array(
								'start'          => 0,
								'font_weight'    => 500,
								'letter_spacing' => '0.04em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 24,
								'font_weight'    => 300,
								'letter_spacing' => '0.06em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 25,
								'font_weight'    => 'regular',
								'letter_spacing' => '0.04em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 26,
								'font_weight'    => 500,
								'letter_spacing' => '0.04em',
								'text_transform' => 'uppercase',
							),
						),
					),

					// Secondary font is used for smaller headings [H4, H5, H6], including meta details
					'sm_font_secondary' => array(
						'font_family'      => 'Oswald',
						'font_weights'     => array( 200, '200italic', 500, '500italic' ),
						'font_size_to_line_height_points' => array(
							array( 14, 1.625 ),
							array( 24, 1.5 ),
						),
						'font_styles_intervals'      => array(
							array(
								'start'          => 0,
								'font_weight'    => 500,
								'letter_spacing' => '0.01em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 20,
								'font_weight'    => 500,
								'letter_spacing' => '0em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 24,
								'font_weight'    => 200,
								'letter_spacing' => '0em',
								'text_transform' => 'none',
							),
						),
					),

					// Used for Body Font [eg. entry-content]
					'sm_font_body' => array(
						'font_family'      => 'Roboto',
						'font_weights'     => array( 300, '300italic', 400, '400italic', 500, '500italic' ),
						'font_size_to_line_height_points' => array(
							array( 14, 1.5 ),
							array( 24, 1.45 ),
						),

						// Define how fonts will look based on their size
						'font_styles_intervals'      => array(
							array(
								'start'          => 0,
								'end'            => 10.9,
								'font_weight'    => 500,
								'letter_spacing' => '0.03em',
								'text_transform' => 'none',
							),
							array(
								'start'          => 10.9,
								'end'            => 12,
								'font_weight'    => 500,
								'letter_spacing' => '0.02em',
								'text_transform' => 'uppercase',
							),
							array(
								'start'          => 12,
								'font_weight'    => 300,
								'letter_spacing' => 0,
								'text_transform' => 'none',
							),
						),
					),
				),
			),
			'hive' => array(
				'label'   => esc_html__( 'Hive', 'customify' ),
				'preview' => array(
					// Font Palette Name
					'title'            => esc_html__( 'Hive', 'customify' ),
					'description'      => esc_html__( 'A graceful nature, truly tasteful and polished.', 'customify' ),
					'background_image_url' => 'https://cloud.pixelgrade.com/wp-content/uploads/2018/09/font-palette-classic.png',

					// Use the following options to style the preview card fonts
					// Including font-family, size, line-height, weight, letter-spacing and text transform
					'title_font'       => array(
						'font' => 'font_primary',
						'size' => 36,
					),
					'description_font' => array(
						'font' => 'font_body',
						'size' => 18,
					),
				),

				'fonts_logic' => array(
					// Primary is used for main headings [Display, H1, H2, H3]
					'sm_font_primary' => array(
						// Font loaded when a palette is selected
						'font_family'      => 'Playfair Display',
						// Load all these fonts weights.
						'font_weights'     => array( 400, '400italic', 700, '700italic', 900, '900italic' ),
						// "Generate" the graph to be used for font-size and line-height.
						'font_size_to_line_height_points' => array(
							array( 20, 1.55 ),
							array( 65, 1.15 ),
						),

						// Define how fonts will look based on the font size.
						'font_styles_intervals'      => array(
							array(
								'start'          => 0,
								'font_weight'    => 'regular',
								'letter_spacing' => '0em',
								'text_transform' => 'none',
							),
						),
					),

					// Secondary font is used for smaller headings [H4, H5, H6], including meta details
					'sm_font_secondary' => array(
						'font_family'      => 'Noto Serif',
						'font_weights'     => array( 400, '400italic', 700, '700italic' ),
						'font_size_to_line_height_points' => array(
							array( 13, 1.33 ),
							array( 18, 1.5 ),
						),
						'font_styles_intervals'      => array(
							array(
								'start'          => 0,
								'end'          	 => 15,
								'font_weight'    => 'regular',
								'letter_spacing' => '0em',
								'text_transform' => 'none',
							),
							array(
								'start'          => 15,
								'font_weight'    => 700,
								'letter_spacing' => '0em',
								'text_transform' => 'none',
							),
						),
					),

					// Used for Body Font [eg. entry-content]
					'sm_font_body' => array(
						'font_family'      => 'Noto Serif',
						'font_weights'     => array( 400, '400italic', 700, '700italic' ),
						'font_size_to_line_height_points' => array(
							array( 13, 1.4 ),
							array( 18, 1.5 ),
						),

						// Define how fonts will look based on their size
						'font_styles_intervals'      => array(
							array(
								'start'          => 0,
								'font_weight'    => 'regular',
								'letter_spacing' => 0,
								'text_transform' => 'none',
							),
						),
					),
				),
			),
		);

		return apply_filters( 'customify_style_manager_default_font_palettes', $default_config );
	}

	/**
	 * Get the current font palette ID or false if none is selected.
	 *
	 * @since 1.7.4
	 *
	 * @return string|false
	 */
	public function get_current_palette() {
		return get_option( 'sm_font_palette', false );
	}

	/**
	 * Get the current font palette variation ID or false if none is selected.
	 *
	 * @since 1.7.4
	 *
	 * @return string|false
	 */
	public function get_current_palette_variation() {
		return get_option( 'sm_font_palette_variation', false );
	}

	/**
	 * Determine if the selected font palette has been customized and remember this in an option.
	 *
	 * @since 1.7.4
	 *
	 * @return bool
	 */
	public function update_custom_palette_in_use() {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return false;
		}

		$current_palette = $this->get_current_palette();
		if ( empty( $current_palette ) ) {
			return false;
		}

		$font_palettes = $this->get_palettes();
		if ( ! isset( $font_palettes[ $current_palette ] ) || empty( $font_palettes[ $current_palette ]['options'] ) ) {
			return false;
		}

		$is_custom_palette = false;
		// If any of the current master fonts has a different value than the one provided by the font palette,
		// it means a custom font palette is in use.
		$current_palette_options = $font_palettes[ $current_palette ]['options'];
		foreach ( $current_palette_options as $setting_id => $value ) {
			if ( $value != get_option( $setting_id ) ) {
				$is_custom_palette = true;
				break;
			}
		}

		update_option( 'sm_is_custom_font_palette', $is_custom_palette, true );

		do_action( 'customify_style_manager_updated_custom_palette_in_use', $is_custom_palette );

		return true;
	}

	/**
	 * Determine if a custom font palette is in use.
	 *
	 * @since 1.7.4
	 *
	 * @return bool
	 */
	public function is_using_custom_palette(){
		return (bool) get_option( 'sm_is_custom_font_palette', false );
	}

	/**
	 * Get all the defined Style Manager master font field ids.
	 *
	 * @since 1.7.4
	 *
	 * @param array $options_details Optional.
	 *
	 * @return array
	 */
	public function get_all_master_font_controls_ids( $options_details = null ) {
		$control_ids = array();

		if ( empty( $options_details ) ) {
			$options_details = PixCustomifyPlugin()->get_options_configs(true);
		}

		if ( empty( $options_details ) ) {
			return $control_ids;
		}

		foreach ( $options_details as $option_id => $option_details ) {
			if ( ! empty( $option_details['type'] ) && 'font' === $option_details['type'] && 0 === strpos( $option_id, 'sm_' ) ) {
				$control_ids[] = $option_id;
			}
		}

		return $control_ids;
	}

	/**
	 * Add font palettes usage data to the site data sent to the cloud.
	 *
	 * @since 1.7.4
	 *
	 * @param array $site_data
	 *
	 * @return array
	 */
	public function add_palettes_to_site_data( $site_data ) {
		if ( empty( $site_data['font_palettes'] ) ) {
			$site_data['font_palettes'] = array();
		}

		// If others have added data before us, we will merge with it.
		$site_data['font_palettes'] = array_merge( $site_data['font_palettes'], array(
			'current' => $this->get_current_palette(),
			'variation' => $this->get_current_palette_variation(),
			'custom' => $this->is_using_custom_palette(),
		) );

		return $site_data;
	}

	/**
	 * Add data to be available to JS.
	 *
	 * @since 2.7.0
	 *
	 * @param array $localized
	 *
	 * @return array
	 */
	public function add_to_localized_data( $localized ) {
		if ( empty( $localized['fontPalettes'] ) ) {
			$localized['fontPalettes'] = array();
		}

		$localized['fontPalettes']['masterSettingIds'] = $this->get_all_master_font_controls_ids();

		$localized['fontPalettes']['variations'] = [
			'light'    => [],
			'regular' => [],
			'big'   => [],
		];

		return $localized;
	}

	/**
	 * Main Customify_Font_Palettes Instance
	 *
	 * Ensures only one instance of Customify_Font_Palettes is loaded or can be loaded.
	 *
	 * @since  1.7.4
	 * @static
	 *
	 * @return Customify_Font_Palettes Main Customify_Font_Palettes instance
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
	 * @since 1.7.4
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html__( 'You should not do that!', 'customify' ), null );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.7.4
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ),  null );
	}
}

endif;
