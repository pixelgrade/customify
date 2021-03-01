<?php
/**
 * This is the class that handles the logic for Color Palettes.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       1.7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Color_Palettes' ) ) :

class Customify_Color_Palettes {

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
	 * Initiate our hooks.
	 *
	 * @since 1.7.4
	 */
	public function add_hooks() {
		/*
		 * Handle the Customizer Style Manager section config.
		 */
		add_filter( 'customify_filter_fields', array( $this, 'add_style_manager_new_section_master_colors_config' ), 13, 1 );

		// This needs to come after the external theme config has been applied
		add_filter( 'customify_filter_fields', array( $this, 'maybe_enhance_dark_mode_control' ), 120, 1 );

		/*
		 * Scripts enqueued in the Customizer.
		 */
		add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_scripts' ), 10 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_scripts' ), 10 );

		/**
		 * Add color palettes usage to site data.
		 */
		add_filter( 'customify_style_manager_get_site_data', array( $this, 'add_palettes_to_site_data' ), 10, 1 );

		add_filter( 'language_attributes', array( $this, 'add_dark_mode_data_attribute' ), 10, 2 );
	}

	/**
	 * Register Customizer admin scripts
	 */
	public function register_admin_customizer_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script(
			PixCustomifyPlugin()->get_slug() . '-dark-mode',
			plugins_url( 'dist/js/dark-mode' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
			array( 'jquery' ),
			PixCustomifyPlugin()->get_version()
		);
	}

	/**
	 * Enqueue Customizer admin scripts
	 */
	public function enqueue_admin_customizer_scripts() {

		// If there is no color palettes support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-dark-mode' );
	}

	/**
	 * Determine if Color Palettes are supported.
	 *
	 * @return bool
	 * @since 1.7.4
	 *
	 */
	public function is_supported() {
		// For now we will only use the fact that Style Manager is supported.
		return apply_filters( 'customify_color_palettes_are_supported', Customify_Style_Manager::instance()->is_supported() );
	}

	public function add_style_manager_new_section_master_colors_config( $config ) {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		if ( ! isset( $config['sections']['style_manager_section'] ) ) {
			$config['sections']['style_manager_section'] = array();
		}

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section']['options'] = array(
		      'sm_advanced_palette_source'    => array(
		          'type'         => 'text',
		          'live'         => true,
		          // We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
		          'setting_type' => 'option',
		          // We will force this setting id preventing prefixing and other regular processing.
		          'setting_id'   => 'sm_advanced_palette_source',
		          'label'        => esc_html__( 'Palette Source', '__theme_txtd' ),
		      ),
		      'sm_advanced_palette_output'    => array(
		          'type'         => 'text',
		          'live'         => true,
		          'default'      => '[]',
		          // We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
		          'setting_type' => 'option',
		          // We will force this setting id preventing prefixing and other regular processing.
		          'setting_id'   => 'sm_advanced_palette_output',
		          'label'        => esc_html__( 'Palette Output', '__theme_txtd' ),
		          'css'          => array(
		              array(
		                  'selector'        => ':root',
		                  'property'        => 'dummy-property',
		                  'callback_filter' => 'sm_palette_output_cb'
		              )
		          )
		      ),
		      'sm_site_color_variation' => array(
			      'type'         => 'range',
			      'live'         => true,
			      'setting_type' => 'option',
			      'setting_id'   => 'sm_site_color_variation',
			      'label'        => esc_html__( 'Variation', '__theme_txtd' ),
			      'default'      => 1,
			      'input_attrs'  => array(
				      'min'  => 1,
				      'max'  => 12,
				      'step' => 1,
			      ),
			      'css'          => array(
				      array(
					      'property'        => '--sm-property',
					      'selector'        => ':root',
					      'unit'            => '',
					      'callback_filter' => 'sm_variation_range_cb'
				      ),
			      )
		      ),
		      'sm_text_color_switch_master'   => array(
		          'type'             => 'sm_switch',
		          // We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
		          'setting_type'     => 'option',
		          // We will force this setting id preventing prefixing and other regular processing.
		          'setting_id'       => 'sm_text_color_switch_master',
		          'label'            => esc_html__( 'Text Master', '__theme_txtd' ),
		          'live'             => true,
		          'default'          => 'off',
		          'connected_fields' => array(),
		          'css'              => array(),
		          'choices'          => array(
		              'off' => esc_html__( 'Off', 'customify' ),
		              'on'  => esc_html__( 'On', 'customify' ),
		          ),
		      ),
		      'sm_text_color_select_master'   => array(
		          'type'             => 'select_color',
		          // We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
		          'setting_type'     => 'option',
		          // We will force this setting id preventing prefixing and other regular processing.
		          'setting_id'       => 'sm_text_color_select_master',
		          'label'            => esc_html__( 'Text Select Master', '__theme_txtd' ),
		          'live'             => true,
		          'default'          => 'text',
		          'connected_fields' => array(),
		          'css'              => array(),
		          'choices'          => array(
		              'text' => esc_html__( 'Text', '__theme_txtd' ),
		          ),
		      ),
		      'sm_accent_color_switch_master' => array(
		          'type'             => 'sm_switch',
		          // We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
		          'setting_type'     => 'option',
		          // We will force this setting id preventing prefixing and other regular processing.
		          'setting_id'       => 'sm_accent_color_switch_master',
		          'label'            => esc_html__( 'Accent Master', '__theme_txtd' ),
		          'live'             => true,
		          'default'          => 'on',
		          'connected_fields' => array(),
		          'css'              => array(),
		          'choices'          => array(
		              'off' => esc_html__( 'Off', 'customify' ),
		              'on'  => esc_html__( 'On', 'customify' ),
		          ),
		      ),
		      'sm_accent_color_select_master' => array(
		          'type'             => 'select_color',
		          // We will bypass the plugin setting regarding where to store - we will store it cross-theme in wp_options
		          'setting_type'     => 'option',
		          // We will force this setting id preventing prefixing and other regular processing.
		          'setting_id'       => 'sm_accent_color_select_master',
		          'label'            => esc_html__( 'Accent Select Master', '__theme_txtd' ),
		          'live'             => true,
		          'default'          => 'accent',
		          'connected_fields' => array(),
		          'css'              => array(),
		          'choices'          => array(
		              'accent' => esc_html__( 'Accent', '__theme_txtd' ),
		          ),
		      ),
		      'sm_coloration_level'            => array(
			      'type'         => 'sm_radio',
			      'setting_type' => 'option',
			      'setting_id'   => 'sm_coloration_level',
			      'label'        => esc_html__( 'Coloration Level', 'customify' ),
			      'default'      => 0,
			      'live'         => true,
			      'choices'      => array(
			      	'0' => 'Low',
			      	'50' => 'Medium',
			      	'75' => 'High',
			      	'100' => 'Striking',
			      ),
		      ),
		      'sm_dark_color_switch_slider'    => array(
			      'setting_id'  => 'sm_dark_color_switch_slider',
			      'type'        => 'range',
			      'label'       => esc_html__( 'Dark to Color (switch)', 'customify' ),
			      'desc'        => '',
			      'live'        => true,
			      'default'     => 0,
			      'input_attrs' => array(
				      'min'          => 0,
				      'max'          => 100,
				      'step'         => 1,
				      'data-preview' => true,
			      ),
			      'css'         => array(),
		      ),
		      'sm_dark_color_select_slider'    => array(
			      'setting_id'  => 'sm_dark_color_select_slider',
			      'type'        => 'range',
			      'label'       => esc_html__( 'Dark to Color (select)', 'customify' ),
			      'desc'        => '',
			      'live'        => true,
			      'default'     => 0,
			      'input_attrs' => array(
				      'min'          => 0,
				      'max'          => 100,
				      'step'         => 1,
				      'data-preview' => true,
			      ),
			      'css'         => array(),
		      ),
		  ) + $config['sections']['style_manager_section']['options'];

		return $config;
	}

	public function maybe_enhance_dark_mode_control( $config ) {
		$supports_advanced_dark_mode = (bool) current_theme_supports( 'style_manager_advanced_dark_mode' );

		if ( ! $supports_advanced_dark_mode || ! isset( $config['sections']['style_manager_section'] ) ) {
			return $config;
		}

		unset( $config['sections']['style_manager_section']['options']['sm_dark_mode'] );

		$config['sections']['style_manager_section'] = Customify_Array::array_merge_recursive_distinct( $config['sections']['style_manager_section'], array(
			'options' => array(
				'sm_dark_mode_advanced' => array(
					'type'         => 'sm_radio',
					'setting_id'   => 'sm_dark_mode_advanced',
					'setting_type' => 'option',
					'label'        => esc_html__( 'Appearance', 'customify' ),
					'live'         => true,
					'default'      => 'off',
					'desc'         => wp_kses( __( "<strong>Auto</strong> activates dark mode automatically, according to the visitor's system-wide setting", '__plugin_txtd' ), array( 'strong' => array() ) ),
					'choices'      => array(
						'off'  => esc_html__( 'Light', 'customify' ),
						'on'   => esc_html__( 'Dark', 'customify' ),
						'auto' => esc_html__( 'Auto', 'customify' ),
					),
				),
			),
		) );

		return $config;
	}

	/**
	 * Get the default (hard-coded) color palettes configuration.
	 *
	 * This is only a fallback config in case we can't communicate with the cloud, the first time.
	 *
	 * @return array
	 * @since 1.7.4
	 *
	 */
	protected function get_default_config() {
		$default_config = array(

		);

		return apply_filters( 'customify_style_manager_default_color_palettes', $default_config );
	}

	/**
	 * Add Color Scheme attribute to <html> tag.
	 *
	 * @since 1.6.0
	 *
	 * @param string $output A space-separated list of language attributes.
	 * @param string $doctype The type of html document (xhtml|html).
	 *
	 * @return string $output A space-separated list of language attributes.
	 */
	public function add_dark_mode_data_attribute( $output, $doctype ) {

		if ( is_admin() ) {
			return null;
		}

		if ( 'html' !== $doctype ) {
			return $output;
		}

		$output .= ' data-dark-mode-advanced=' . pixelgrade_option( 'sm_dark_mode_advanced', 'off' );

		return $output;
	}


	/**
	 * Add color palettes usage data to the site data sent to the cloud.
	 *
	 * @param array $site_data
	 *
	 * @return array
	 * @since 1.7.4
	 *
	 */
	public function add_palettes_to_site_data( $site_data ) {

		if ( empty( $site_data['color_palettes'] ) ) {
			$site_data['color_palettes'] = array();
		}

		// If others have added data before us, we will merge with it.
		$site_data['color_palettes'] = array_merge( $site_data['color_palettes'], array(

		) );

		return $site_data;
	}

	/**
	 * Main Customify_Color_Palettes Instance
	 *
	 * Ensures only one instance of Customify_Color_Palettes is loaded or can be loaded.
	 *
	 * @return Customify_Color_Palettes Main Customify_Color_Palettes instance
	 * @since  1.7.4
	 * @static
	 *
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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ), null );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.7.4
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ), null );
	}

}

	function get_fallback_palettes() {
		$alphabet = range( 'A', 'Z' );

		$options_details = PixCustomifyPlugin()->get_options_configs();
		$color_control_ids = array(
			'sm_color_primary',
			'sm_color_secondary',
			'sm_color_tertiary',
		);

		$lighter = PixCustomifyPlugin()->get_option( 'sm_light_primary_final' );
		if ( empty( $lighter ) ) {
			$lighter = PixCustomifyPlugin()->get_option( 'sm_light_primary' );
		}

		$light = PixCustomifyPlugin()->get_option( 'sm_light_tertiary_final' );
		if ( empty( $light ) ) {
			$light = PixCustomifyPlugin()->get_option( 'sm_light_tertiary' );
		}

		$text_color = PixCustomifyPlugin()->get_option( 'sm_dark_secondary_final' );
		if ( empty( $text_color ) ) {
			$text_color = PixCustomifyPlugin()->get_option( 'sm_dark_secondary' );
		}

		$dark = PixCustomifyPlugin()->get_option( 'sm_dark_primary_final' );
		if ( empty( $dark ) ) {
			$dark = PixCustomifyPlugin()->get_option( 'sm_dark_primary' );
		}

		$darker = PixCustomifyPlugin()->get_option( 'sm_dark_tertiary_final' );
		if ( empty( $darker ) ) {
			$darker = PixCustomifyPlugin()->get_option( 'sm_dark_tertiary' );
		}

		$palettes = array();

		foreach ( $color_control_ids as $index => $control_id ) {

			if ( empty( $options_details[ $control_id ] ) ) {
				continue;
			}

			$value = get_option( $control_id . '_final' );

			if ( empty( $value ) ) {
				$value = $options_details[ $control_id ][ 'default' ];
			}

			$colors = array(
				$lighter,
				$light,
				$light,
				$light,
				$value,
				$value,
				$value,
				$dark,
				$dark,
				$dark,
				$darker,
				'#000000',
			);

			$color_objects = array();

			foreach ( $colors as $color ) {
				$obj = ( object ) array(
					'value' => $color
				);

				$color_objects[] = $obj;
			}

			$textColors = array(
				$text_color,
				$text_color,
			);

			$textColor_objects = array();

			foreach ( $textColors as $color ) {
				$obj = ( object ) array(
					'value' => $color
				);

				$textColor_objects[] = $obj;
			}

			$palettes[] = ( object ) array(
				'colors'      => $color_objects,
				'textColors'  => $textColor_objects,
				'source'      => $value,
				'sourceIndex' => 6,
				'label'       => 'Color ' . $alphabet[ $index + 1 ],
				'id'          => $index + 1
			);
		}

		return $palettes;
	}

	function palettes_output( $palettes ) {
		$output = '';
		$variation = intval( get_option( 'sm_site_color_variation', 1 ) );

		foreach ( $palettes as $palette_index => $palette ) {
			$sourceIndex = $palette->sourceIndex;

			$output .= 'html { ' . PHP_EOL;
			$output .= get_initial_color_variables( $palette );
			$output .= get_variables_css( $palette, $variation - 1 );
			$output .= get_variables_css( $palette, $sourceIndex, false, true );
			$output .= '}' . PHP_EOL;

			$output .= '.is-dark { ' . PHP_EOL;
			$output .= get_variables_css( $palette, $variation - 1, true );
			$output .= get_variables_css( $palette, $sourceIndex, true, true );
			$output .= '}' . PHP_EOL;
		}

		return $output;
	}

	function get_initial_color_variables( $palette ) {
		$colors = $palette->colors;
		$textColors = $palette->textColors;
		$id = $palette->id;
		$prefix = '--sm-color-palette-';

		$output = '';

		foreach ( $colors as $index => $color ) {
			$output .= $prefix . $id . '-color-' . ( $index + 1 ) . ': ' . $color->value . ';' . PHP_EOL;
		}

		foreach ( $textColors as $index => $color ) {
			$output .= $prefix . $id . '-text-color-' . ( $index + 1 ) . ': ' . $color->value . ';' . PHP_EOL;
		}

		return $output;
	}

	function get_variables_css( $palette, $offset = 0, $isDark = false, $isShifted = false ) {
		$colors = $palette->colors;
		$count = count( $colors );

		$output = '';

		foreach ( $colors as $index => $color ) {
			$oldColorIndex = ( $index + $offset ) % $count;

			if ( $isDark ) {
				if ( $oldColorIndex < $count / 2 ) {
					$oldColorIndex = 11 - $oldColorIndex;
				} else {
					continue;
				}
			}

			$output .= get_color_variables( $palette, $index, $oldColorIndex, $isShifted );
		}

		return $output;
	}

	function get_color_variables( $palette, $newColorIndex, $oldColorIndex, $isShifted ) {
		$colors = $palette->colors;
		$id = $palette->id;
		$count = count( $colors );
		$accentColorIndex = ( $oldColorIndex + $count / 2 ) % $count;
		$prefix = '--sm-color-palette-';
		$suffix = $isShifted ? '-shifted' : '';

		$output = '';

		$output .= $prefix . $id . '-bg-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-color-' . ( $oldColorIndex + 1 ) . ');' . PHP_EOL;
		$output .= $prefix . $id . '-accent-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-color-' . ( $accentColorIndex + 1 ) . ');' . PHP_EOL;

		if ( $oldColorIndex < $count / 2 ) {
			$output .= $prefix . $id . '-fg1-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-text-color-1);' . PHP_EOL;
			$output .= $prefix . $id . '-fg2-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-text-color-2);' . PHP_EOL;
		} else {
			$output .= $prefix . $id . '-fg1-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-color-1);' . PHP_EOL;
			$output .= $prefix . $id . '-fg2-color-' . ( $newColorIndex + 1 ) . $suffix . ': var(' . $prefix . $id . '-color-2);' . PHP_EOL;
		}

		return $output;
	}

	function sm_palette_output_cb( $value ) {
		$output = '';

		$palettes = json_decode( $value );

		if ( empty( $palettes ) ) {
			$palettes = get_fallback_palettes();
		}

		$output .= palettes_output( $palettes );

		return $output;
	}

	function sm_palette_output_cb_customizer_preview() {
		$palettes = get_fallback_palettes();
		$variation = intval( get_option( 'sm_site_color_variation', 1 ) );

		$js = "";

		$js .= "
function sm_palette_output_cb( value, selector, property ) {
    var palettes = JSON.parse( value ),
        variation = ' . $variation . ',
        fallbackPalettes = JSON.parse('" . json_encode( $palettes ) . "');
        
    if ( ! palettes.length ) {
        palettes = fallbackPalettes;
    }
    
    return window.parent.sm.customizer.getCSSFromPalettes( palettes, variation );
}" . PHP_EOL;

		wp_add_inline_script( 'customify-previewer-scripts', $js );
	}
	add_action( 'customize_preview_init', 'sm_palette_output_cb_customizer_preview', 20 );

	function sm_variation_range_cb( $value, $selector, $property ) {
		return '';
	}

	function sm_variation_range_cb_customizer_preview() {
		$palettes = get_fallback_palettes();

		$js = "";

		$js .= "
function sm_variation_range_cb(value, selector, property) {
    var paletteOutputSetting = wp.customize( 'sm_advanced_palette_output' ),
        palettes = !! paletteOutputSetting ? JSON.parse( paletteOutputSetting() ) : [],
        fallbackPalettes = JSON.parse('" . json_encode( $palettes ) . "');
        
    if ( ! palettes.length ) {
        palettes = fallbackPalettes;
    }
        
    return window.parent.sm.customizer.getCSSFromPalettes( palettes, value );
}" . PHP_EOL;

		wp_add_inline_script( 'customify-previewer-scripts', $js );
	}
	add_action( 'customize_preview_init', 'sm_variation_range_cb_customizer_preview', 20 );

endif;
