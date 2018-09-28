<?php
/**
 * This is the class that handles the overall logic for the Style Manager.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Style_Manager' ) ) :

class Customify_Style_Manager {

	/**
	 * Holds the only instance of this class.
	 * @var     null|Customify_Style_Manager
	 * @access  protected
	 * @since   1.7.0
	 */
	protected static $_instance = null;

	/**
	 * The main plugin object (the parent).
	 * @var     null|PixCustomifyPlugin
	 * @access  public
	 * @since   1.7.0
	 */
	public $parent = null;

	/**
	 * The external theme configs object.
	 * @var     null|Customify_Theme_Configs
	 * @access  public
	 * @since   1.7.4
	 */
	protected $theme_configs = null;

	/**
	 * The color palettes object.
	 * @var     null|Customify_Color_Palettes
	 * @access  public
	 * @since   1.7.4
	 */
	protected $color_palettes = null;

	/**
	 * The font palettes object.
	 * @var     null|Customify_Font_Palettes
	 * @access  public
	 * @since   1.7.4
	 */
	protected $font_palettes = null;

	/**
	 * The notifications object.
	 * @var     null|Pixcloud_Admin_Notifications_Manager
	 * @access  public
	 * @since   1.9.0
	 */
	protected $notifications = null;

	/**
	 * The Cloud API object.
	 * @var     null|Customify_Cloud_Api
	 * @access  public
	 * @since   1.7.4
	 */
	protected $cloud_api = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
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
		/**
		 * Initialize the Themes Config logic.
		 */
		require_once 'class-customify-theme-configs.php';
		$this->theme_configs = Customify_Theme_Configs::instance();

		/**
		 * Initialize the Color Palettes logic.
		 */
		require_once 'class-customify-color-palettes.php';
		$this->color_palettes = Customify_Color_Palettes::instance();

		/**
		 * Initialize the Font Palettes logic.
		 */
		require_once 'class-customify-font-palettes.php';
		$this->font_palettes = Customify_Font_Palettes::instance();

		/**
		 * Initialize the Notifications logic.
		 */
		require_once 'admin-notifications-manager/class-admin-notifications-manager.php';
		$this->notifications = Pixcloud_Admin_Notifications_Manager::instance(
			array(
				'plugin_name'       => 'Customify',
				'text_domain'       => 'customify',
				'version'           => '',
			)
		);

		/**
		 * Initialize the Cloud API logic.
		 */
		require_once 'lib/class-customify-cloud-api.php';
		$this->cloud_api = new Customify_Cloud_Api();

		// Hook up.
		$this->add_hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since 1.7.0
	 */
	public function add_hooks() {
		/*
		 * Handle the Customizer Style Manager base config.
		 */
		add_filter( 'customify_filter_fields', array( $this, 'style_manager_section_base_config' ), 12, 1 );

		/*
		 * Handle the grouping and reorganization of the Customizer theme sections when the Style Manager is active.
		 */
		add_filter( 'customify_final_config', array( $this, 'reorganize_sections' ), 10, 1 );

		/*
		 * Handle the logic for user feedback.
		 */
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'output_user_feedback_modal' ) );
		add_action( 'wp_ajax_customify_style_manager_user_feedback', array( $this, 'user_feedback_callback' ) );

		/*
		 * Scripts enqueued in the Customizer.
		 */
		add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_scripts' ), 10 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_scripts' ), 10 );
	}

	/**
	 * Register Customizer admin scripts.
	 */
	function register_admin_customizer_scripts() {
		wp_register_script( PixCustomifyPlugin()->get_slug() . '-style-manager', plugins_url( 'js/customizer/style-manager.js', PixCustomifyPlugin()->get_file() ), array( 'jquery' ), PixCustomifyPlugin()->get_version() );
	}

	/**
	 * Enqueue Customizer admin scripts
	 */
	function enqueue_admin_customizer_scripts() {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		// Enqueue the needed scripts, already registered.
		wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-style-manager' );
	}

	/**
	 * Determine if Style Manager is supported.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function is_supported() {
		$has_support = (bool) current_theme_supports( 'customizer_style_manager' );

		return apply_filters( 'customify_style_manager_is_supported', $has_support );
	}

	/**
	 * Setup the Style Manager Customizer section base config.
	 *
	 * This handles the base configuration for the controls in the Style Manager section. We expect other parties (e.g. the theme),
	 * to come and fill up the missing details (e.g. connected fields).
	 *
	 * @since 1.7.0
	 *
	 * @param array $config This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'
	 * @return array
	 */
	public function style_manager_section_base_config( $config ) {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		if ( ! isset( $config['sections']['style_manager_section'] ) ) {
			$config['sections']['style_manager_section'] = array();
		}

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section'] = array_replace_recursive( $config['sections']['style_manager_section'], array(
			'title'   => esc_html__( 'Style Manager', 'customify' ),
			'section_id' => 'style_manager_section', // We will force this section id preventing prefixing and other regular processing.
			'priority' => 1,
			'options' => array(),
		) );

		return $config;
	}

	/**
	 * Reorganize the Customizer sections.
	 *
	 * @since 1.7.4
	 *
	 * @param array $config This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'.
	 * @return array
	 */
	public function reorganize_sections( $config ) {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		// If there is no Style Manager section or panel, bail.
		if ( ! isset( $config['sections']['style_manager_section'] ) &&
			! isset( $config['panels']['style_manager_panel'] ) ) {

			return $config;
		}

		$style_manager_section_config = false;
		if ( isset( $config['sections']['style_manager_section'] ) ) {
			$style_manager_section_config = $config['sections']['style_manager_section'];
			unset( $config['sections']['style_manager_section'] );
		}

		// All the other sections.
		$other_theme_sections_config = $config['sections'];
		unset( $config['sections'] );

		// The Style Manager panel.
		if ( empty( $config['panels']['style_manager_panel'] ) ) {
			$style_manager_panel_config = array(
				'priority'                 => 22,
				'capability'               => 'edit_theme_options',
				'panel_id'                 => 'style_manager_panel',
				'title'                    => esc_html__( 'Style Manager', 'customify' ),
				'description'              => __( '<strong>Style Manager</strong> is an intuitive system to help you change the look of your website and make an excellent impression.', 'customify' ),
				'sections'                 => array(),
				'auto_expand_sole_section' => true, // If there is only one section in the panel, auto-expand it.
			);
		} else {
			$style_manager_panel_config = $config['panels']['style_manager_panel'];
			unset( $config['panels']['style_manager_panel'] );
		}

		$other_panels_config = false;
		if ( ! empty( $config['panels'] ) ) {
			$other_panels_config = $config['panels'];
			unset( $config['panels'] );
		}

		// Maybe handle the color palettes.
		if ( is_array( $style_manager_section_config ) && class_exists( 'Customify_Color_Palettes' ) && Customify_Color_Palettes::instance()->is_supported() ) {

			// We need to split the fields in the Style Manager section into two: color palettes and fonts.
			$color_palettes_fields = array(
				'sm_current_color_palette',
				'sm_palettes_description',
				'sm_filters_description',
				'sm_customize_description',
				'sm_color_matrix',
				'sm_palette_filter',
				'sm_coloration_level',
				'sm_color_diversity',
				'sm_shuffle_colors',
                'sm_dark_mode',
				'sm_dark_color_master_slider',
				'sm_dark_color_primary_slider',
				'sm_dark_color_secondary_slider',
				'sm_dark_color_tertiary_slider',
				'sm_colors_dispersion',
				'sm_colors_focus_point',
				'sm_color_palette',
				'sm_color_palette_variation',
				'sm_color_primary',
				'sm_color_primary_connected',
				'sm_color_secondary',
				'sm_color_secondary_connected',
				'sm_color_tertiary',
				'sm_color_tertiary_connected',
				'sm_dark_primary',
				'sm_dark_primary_connected',
				'sm_dark_secondary',
				'sm_dark_secondary_connected',
				'sm_dark_tertiary',
				'sm_dark_tertiary_connected',
				'sm_light_primary',
				'sm_light_primary_connected',
				'sm_light_secondary',
				'sm_light_secondary_connected',
				'sm_light_tertiary',
				'sm_light_tertiary_connected',
				'sm_swap_colors',
				'sm_swap_dark_light',
				'sm_swap_colors_dark',
				'sm_swap_secondary_colors_dark',
				'sm_advanced_toggle',
				'sm_spacing_bottom',
			);

			$color_palettes_section_config = array(
				'title'      => esc_html__( 'Colors', 'customify' ),
				'section_id' => 'sm_color_palettes_section',
				'priority'   => 10,
				'options'    => array(),
			);
			foreach ( $color_palettes_fields as $field_id ) {
				if ( ! isset( $style_manager_section_config['options'][ $field_id ] ) ) {
					continue;
				}

				if ( empty( $color_palettes_section_config['options'] ) ) {
					$color_palettes_section_config['options'] = array( $field_id => $style_manager_section_config['options'][ $field_id ] );
				} else {
					$color_palettes_section_config['options'] = array_merge( $color_palettes_section_config['options'], array( $field_id => $style_manager_section_config['options'][ $field_id ] ) );
				}
			}

			$style_manager_panel_config['sections']['sm_color_palettes_section'] = $color_palettes_section_config;
		}

		// Maybe handle the font palettes.
		if ( is_array( $style_manager_section_config ) && class_exists( 'Customify_Font_Palettes' ) && Customify_Font_Palettes::instance()->is_supported() ) {

			$font_palettes_fields = array(
				'sm_font_palette',
				'sm_font_palette_variation',
				'sm_font_primary',
				'sm_font_secondary',
				'sm_font_body',
				'sm_swap_fonts',
				'sm_swap_primary_secondary_fonts',
			);

			$font_palettes_section_config = array(
				'title'      => esc_html__( 'Fonts', 'customify' ),
				'section_id' => 'sm_font_palettes_section',
				'priority'   => 20,
				'options'    => array(),
			);
			foreach ( $font_palettes_fields as $field_id ) {
				if ( ! isset( $style_manager_section_config['options'][ $field_id ] ) ) {
					continue;
				}

				if ( empty( $font_palettes_section_config['options'] ) ) {
					$font_palettes_section_config['options'] = array( $field_id => $style_manager_section_config['options'][ $field_id ] );
				} else {
					$font_palettes_section_config['options'] = array_merge( $font_palettes_section_config['options'], array( $field_id => $style_manager_section_config['options'][ $field_id ] ) );
				}
			}

			$style_manager_panel_config['sections']['sm_font_palettes_section'] = $font_palettes_section_config;
		}

		// Start fresh and add the Style Manager panel config
		if ( empty( $config['panels'] ) ) {
			$config['panels'] = array();
		}
		$config['panels']['style_manager_panel'] = $style_manager_panel_config;

		// The Theme Options panel.
		$theme_options_panel_config = array(
			'priority'    => 23,
			'capability'  => 'edit_theme_options',
			'panel_id'    => 'theme_options_panel',
			'title'       => esc_html__( 'Theme Options', 'customify' ),
			'description' => esc_html__( 'Advanced options to change your site look-and-feel on a detailed level.', 'customify' ),
			'sections'    => array(),
		);

		// If we have other panels we will make their sections parts of the Theme Options panel.
		if ( ! empty( $other_panels_config ) ) {
			// If we have another panel that is called Theme Options we will extract it's sections and put them directly in the Theme Options panel.
			$second_theme_options_sections = array();
			foreach ( $other_panels_config as $panel_id => $panel_config ) {
				$found = false;
				// First try the panel ID.
				if ( false !== strpos( strtolower( str_replace( '-', '_', $panel_id ) ), 'theme_options' ) ) {
					$found = true;
				}

				// Second, try the panel title.
				if ( ! $found &&  ! empty( $panel_config['title'] ) && false !== strpos( strtolower( str_replace( array( '-', '_'), ' ', $panel_config['title'] ) ), ' theme options' ) ) {
					$found = true;
				}

				if ( $found && ! empty( $panel_config['sections'] ) ) {
					$second_theme_options_sections = array_merge( $second_theme_options_sections, $panel_config['sections'] );
					unset( $other_panels_config[ $panel_id ] );
				}
			}
			if ( ! empty( $second_theme_options_sections ) ) {
				$theme_options_panel_config['sections'] = array_merge( $theme_options_panel_config['sections'], $second_theme_options_sections );
			}

			// For the remaining panels, we will put their section into the Theme Options panel, but prefix their title with their respective panel title.
			$prefixed_sections = array();
			foreach ( $other_panels_config as $panel_id => $panel_config ) {
				if ( ! empty( $panel_config['sections'] ) ) {
					foreach ( $panel_config['sections'] as $section_id => $section_config ) {
						if ( ! empty( $section_config['title'] ) && ! empty( $panel_config['title'] ) ) {
							$section_config['title'] = $panel_config['title'] . ' - ' . $section_config['title'];
						}
						$prefixed_sections[ $panel_id . '_' . $section_id ] = $section_config;
					}
				}
			}
			$theme_options_panel_config['sections'] = array_merge( $theme_options_panel_config['sections'], $prefixed_sections );
		}

		// If we have other sections we will add them to the Theme Options panel.
		if ( ! empty( $other_theme_sections_config ) ) {
			$theme_options_panel_config['sections'] = array_merge( $theme_options_panel_config['sections'], $other_theme_sections_config );
		}

		if ( empty( $config['panels']['theme_options_panel'] ) ) {
			$config['panels']['theme_options_panel'] = $theme_options_panel_config;
		} else {
			$config['panels']['theme_options_panel'] = array_merge( $config['panels']['theme_options_panel'], $theme_options_panel_config );
		}

		// Add the logic that handles sections and controls added directly to WP_Customizer, not through the config.
		add_action( 'customize_register', array( $this, 'reorganize_direct_sections_and_controls' ), 100 );

		// Remove the switch theme panel from the Customizer.
		add_action( 'customize_register', array( $this, 'remove_switch_theme_panel' ), 12 );

		return $config;
	}

	/**
	 * Reorganizes sections and controls added directly to WP_Customizer, not through the config.
	 *
	 * @todo Please note that this is house cleaning and it is only necessary due to the lack of complete standardization on the theme side. We should not need this forever!
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function reorganize_direct_sections_and_controls( $wp_customize ) {
		// We will do out best to identify direct sections and move their controls to the appropriate place.
		/** @var WP_Customize_Section $section */
		foreach ( $wp_customize->sections() as $section ) {
			// These are general theme options sections that need to have their controls moved to the Theme Options > General section.
			if ( false !== strpos( $section->id, 'theme_options') ) {
				$theme_options_panel = $wp_customize->get_panel( 'theme_options_panel' );
				$general_section = false;
				foreach ( $theme_options_panel->sections as $theme_options_section ) {
					if ( false !== strpos( $theme_options_section->id, 'general' ) ) {
						$general_section = $section;
					}
				}

				if ( false === $general_section ) {
					// We need to add a general section in the Theme Options panel.
					$general_section = $wp_customize->add_section( 'theme_options[general]', array(
						'title'             => esc_html__( 'General', 'customify' ),
						'panel'             => $theme_options_panel->id,
						'priority'          => 2,
					) );
				}

				// Move all the controls in the identified theme options section to the general one.
				/** @var WP_Customize_Control $control */
				foreach ( $wp_customize->controls() as $control ) {
					if ( $control->section !== $section->id ) {
						continue;
					}

					$control->section = $general_section->id;
				}

				// Finally remove the now empty section.
				$wp_customize->remove_section( $section->id );

				break;
			}
		}
	}

	/**
	 * Remove the switch/preview theme panel.
	 *
	 * @since 1.7.4
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function remove_switch_theme_panel( $wp_customize ) {
		$wp_customize->remove_panel( 'themes' );
	}

	/**
	 * Output the user feedback modal markup, if we need to.
	 *
	 * @since 1.7.0
	 */
	public function output_user_feedback_modal() {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		// We want to ask for feedback once a month.
		$a_month_back = time() - MONTH_IN_SECONDS;

		// Only output if we should ask for feedback.
		if ( $this->should_ask_for_feedback( $a_month_back ) ) { ?>
			<div id="style-manager-user-feedback-modal">
				<div class="modal">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<form id="style-manager-user-feedback" action="#" method="post">
								<input type="hidden" name="type" value="1_to_5" />
								<div class="modal-header">
									<button type="button" class="close icon media-modal-close" data-dismiss="modal" aria-label="Close"><span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span></button>
									<!-- <a href="#" class="close button button--naked gray" data-dismiss="modal" aria-label="Close">Close</a> -->
								</div>
								<div class="modal-body full">
									<div class="box box--large">
										<div class="first-step">
											<h2 class="modal-title">How would you rate your experience in finding the right colors for your site?</h2>
											<div class="scorecard">
												<span>Poor</span>
												<label>
													<input type="radio" name="rating" value="1" required />
													<span>1</span>
												</label>
												<label>
													<input type="radio" name="rating" value="2" required />
													<span>2</span>
												</label>
												<label>
													<input type="radio" name="rating" value="3" required />
													<span>3</span>
												</label>
												<label>
													<input type="radio" name="rating" value="4" required />
													<span>4</span>
												</label>
												<label>
													<input type="radio" name="rating" value="5" required />
													<span>5</span>
												</label>
												<span>Great</span>
											</div>
										</div>
										<div class="second-step hidden">
											<p><strong>What points along the way made this a <span class="rating-placeholder">5</span>* experience for you?</strong><br>We are counting on your insights to guide us in doing better 🙏</p>
											<div class="not-floating-labels">
												<div class="form-row field">
												<textarea name="message" placeholder="Describe your experience in customizing your site colors.."
												          id="style-manager-user-feedback-message" rows="6" oninvalid="this.setCustomValidity('May we have a little more info about your experience?')" oninput="setCustomValidity('')" required></textarea>
												</div>
											</div>
											<button id="style-manager-user-feedback_btn" class="button" type="submit"><?php _e( 'Send us your insights', 'customify' ); ?></button>
										</div>
										<div class="thanks-step hidden">
											<h3 class="modal-title">Thank you so much for your feedback!</h3>
											<p>It means the world to us as we strive to constantly push the limits and aim higher. Stay awesome! 🤗</p>
											<p><em>The Pixelgrade Team</em></p>
										</div>
										<div class="error-step hidden">
											<h3 class="modal-title">We've hit a snag!</h3>
											<p>We couldn't record your feedback and we would truly appreciate it if you would try it again at a latter time. Stay awesome! 🤗</p>
										</div>
									</div>
								</div>
								<div class="modal-footer full">

								</div>
							</form>
						</div>
					</div>
				</div>
				<!-- End Modal -->
				<!-- Modal Backdrop (Shadow) -->
				<div class="modal-backdrop"></div>
			</div>

		<?php }
	}

	/**
	 * Return whether user provided feedback, and if so, return the timestamp.
	 *
	 * @return bool|int
	 */
	public function user_provided_feedback() {
		$user_provided_feedback = get_option( 'style_manager_user_feedback_provided' );
		if ( empty( $user_provided_feedback ) ) {
			return false;
		}

		return $user_provided_feedback;
	}

	/**
	 * Determine if we should ask for user feedback.
	 *
	 * @param bool|int $timestamp_limit Optional. Timestamp to compare the time the user provided feedback.
	 *                              If the provided timestamp is earlier than the time the user provided feedback, should ask again.
	 *
	 * @return bool
	 */
	public function should_ask_for_feedback( $timestamp_limit = false ) {
		if ( defined( 'CUSTOMIFY_SM_ALWAYS_ASK_FOR_FEEDBACK' ) && true === CUSTOMIFY_SM_ALWAYS_ASK_FOR_FEEDBACK ) {
			return true;
		}

		$feedback_timestamp = $this->user_provided_feedback();
		if ( empty( $feedback_timestamp ) ) {
			return true;
		}

		if ( ! empty( $timestamp_limit ) && intval( $timestamp_limit ) > intval( $feedback_timestamp ) ) {
			return  true;
		}

		return false;
	}

	/**
	 * Callback for the user feedback AJAX call.
	 *
	 * @since 1.7.0
	 */
	public function user_feedback_callback() {
		check_ajax_referer( 'customify_style_manager_user_feedback', 'nonce' );

		if ( empty( $_POST['type'] ) ) {
			wp_send_json_error( esc_html__( 'No type provided', 'customify' ) );
		}

		if ( empty( $_POST['rating'] ) ) {
			wp_send_json_error( esc_html__( 'No rating provided', 'customify' ) );
		}

		$type = sanitize_text_field( $_POST['type'] );
		$rating = intval( $_POST['rating'] );
		$message = '';
		if ( ! empty( $_POST['message'] ) ) {
			$message = wp_kses_post( $_POST['message'] );
		}

		$request_data = array(
			'site_url'          => home_url( '/' ),
			'satisfaction_data' => array(
				'type'    => $type,
				'rating'  => $rating,
				'message' => $message,
			),
		);

		// Send the feedback.
		$response = $this->cloud_api->send_stats( $request_data, true );
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( esc_html__( 'Sorry, something went wrong and we couldn\'t save your feedback.', 'customify' ) );
		}
		$response_data = json_decode( wp_remote_retrieve_body( $response ), true );
		// Bail in case of decode error or failure to retrieve data
		if ( null === $response_data || empty( $response_data['code'] ) || 'success' !== $response_data['code'] ) {
			wp_send_json_error( esc_html__( 'Sorry, something went wrong and we couldn\'t save your feedback.', 'customify' ) );
		}

		// We need to remember that the user provided feedback (and at what timestamp).
		update_option( 'style_manager_user_feedback_provided', time(), true );

		wp_send_json_success( esc_html__( 'Thank you for your feedback.', 'customify' ) );
	}

	/**
	 * Main Customify_Style_Manager Instance
	 *
	 * Ensures only one instance of Customify_Style_Manager is loaded or can be loaded.
	 *
	 * @since  1.7.0
	 * @static
	 *
	 * @return Customify_Style_Manager Main Customify_Style_Manager instance
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
	 * @since 1.7.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?' ) ), null );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.7.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ),  null );
	} // End __wakeup ()
}

endif;
