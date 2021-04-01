<?php
/**
 * This is the class that handles the overall logic for the Style Manager.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\StyleManager;

use Pixelgrade\Customify\Client\CloudInterface;
use Pixelgrade\Customify\Utils\ArrayHelpers;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;
use function Pixelgrade\Customify\is_sm_supported;

/**
 * Provides the style manager general logic.
 *
 * @since 3.0.0
 */
class StyleManager extends AbstractHookProvider {

	const USER_PROVIDED_FEEDBACK_OPTION_KEY = 'style_manager_user_feedback_provided';

	/**
	 * Cache for the wupdates identification data to avoid firing the filter multiple times.
	 * @var array
	 */
	protected static array $wupdates_ids = [];

	/**
	 * Cloud client.
	 *
	 * @var CloudInterface
	 */
	protected CloudInterface $cloud_client;

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
	 * @param CloudInterface  $cloud_client Cloud client.
	 * @param LoggerInterface $logger Logger.
	 */
	public function __construct(
		CloudInterface $cloud_client,
		LoggerInterface $logger
	) {
		$this->cloud_client = $cloud_client;
		$this->logger = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		/**
		 * Handle the Customizer Style Manager base config.
		 */
		$this->add_filter( 'customify_filter_fields', 'setup_sm_section_base_config', 12, 1 );

		/**
		 * Handle the grouping and reorganization of the Customizer theme sections when Style Manager is active.
		 */
		$this->add_filter( 'customify_final_config', 'reorganize_customify_sections', 10, 1 );
		// Remove the switch theme panel from the Customizer.
		$this->add_action( 'customize_register', 'remove_switch_theme_panel', 12 );
		// Add the logic that handles sections and controls registered directly to WP_Customizer, not through the Customify config.
		$this->add_action( 'customize_register', 'reorganize_direct_sections_and_controls', 998 );

		/**
		 * Handle other, more general reorganization in the Customizer, independent of Style Manager.
		 */
		$this->add_action( 'customize_register', 'general_reorganization_of_customize_sections', 999, 1 );

		/**
		 * Handle the customization of controls based on theme type.
		 */
		$this->add_filter( 'customify_filter_fields', 'pre_filter_based_on_theme_type', 20, 1 );
		$this->add_filter( 'customify_final_config', 'filter_based_on_theme_type', 20, 1 );

		/**
		 * Handle the logic for the user giving us feedback.
		 */
		$this->add_action( 'customize_controls_print_footer_scripts', 'output_user_feedback_modal' );
		$this->add_action( 'wp_ajax_customify_style_manager_user_feedback', 'user_feedback_callback' );

		$this->add_filter( 'customify_localized_js_settings', 'add_to_localized_data', 10, 1 );
	}

	/**
	 * Determine if Style Manager is supported.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function is_supported(): bool {
		return is_sm_supported();
	}

	/**
	 * Setup the Style Manager Customizer section base config.
	 *
	 * This handles the base configuration for the controls in the Style Manager section. We expect other parties (e.g. the theme),
	 * to come and fill up the missing details (e.g. connected fields).
	 *
	 * @since 3.0.0
	 *
	 * @param array $config This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'
	 *
	 * @return array
	 */
	protected function setup_sm_section_base_config( array $config ): array {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		if ( ! isset( $config['sections']['style_manager_section'] ) ) {
			$config['sections']['style_manager_section'] = [];
		}

		// The section might be already defined, thus we merge, not replace the entire section config.
		$config['sections']['style_manager_section'] =
			ArrayHelpers::array_merge_recursive_distinct( $config['sections']['style_manager_section'], [
			'title'      => esc_html__( 'Style Manager', '__plugin_txtd' ),
			// We will force this section id preventing prefixing and other regular processing.
			'section_id' => 'style_manager_section',
			'priority'   => 1,
			'options'    => [],
		] );

		return $config;
	}

	/**
	 * Reorganize the Customizer sections.
	 *
	 * @since 3.0.0
	 *
	 * @param array $config This holds required keys for the plugin config like 'opt-name', 'panels', 'settings'.
	 *
	 * @return array
	 */
	protected function reorganize_customify_sections( array $config ): array {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return $config;
		}

		// If there is no Style Manager section or panel, bail.
		if ( ! isset( $config['sections']['style_manager_section'] ) &&
		     ! isset( $config['panels']['style_manager_panel'] ) ) {

			return $config;
		}

		$sm_section_config = [];
		if ( isset( $config['sections']['style_manager_section'] ) ) {
			$sm_section_config = $config['sections']['style_manager_section'];
			unset( $config['sections']['style_manager_section'] );
		}

		// All the other sections.
		$other_theme_sections_config = $config['sections'];
		unset( $config['sections'] );

		// The Style Manager panel.
		if ( empty( $config['panels']['style_manager_panel'] ) ) {
			$sm_panel_config = [
				'priority'                 => 22, // after the Site Identity panel
				'capability'               => 'edit_theme_options',
				'panel_id'                 => 'style_manager_panel',
				'title'                    => esc_html__( 'Style Manager', '__plugin_txtd' ),
				'description'              => wp_kses_post( __( '<strong>Style Manager</strong> is an intuitive system to help you change the look of your website and make an excellent impression.', '__plugin_txtd' ) ),
				'sections'                 => [],
				'auto_expand_sole_section' => true, // If there is only one section in the panel, auto-expand it.
			];
		} else {
			$sm_panel_config = $config['panels']['style_manager_panel'];
			unset( $config['panels']['style_manager_panel'] );
		}

		$other_panels_config = [];
		if ( ! empty( $config['panels'] ) ) {
			$other_panels_config = $config['panels'];
			unset( $config['panels'] );
		}

		// Start fresh and add the Style Manager panel config
		if ( empty( $config['panels'] ) ) {
			$config['panels'] = [];
		}
		// Allow others to have a say in this (like Color Palettes, or Font Palettes).
		$config['panels']['style_manager_panel'] = apply_filters( 'style_manager_panel_config', $sm_panel_config, $sm_section_config );

		// The Theme Options panel.
		$theme_options_panel_config = [
			'priority'    => 24, // after the Style Manager panel.
			'capability'  => 'edit_theme_options',
			'panel_id'    => 'theme_options_panel',
			'title'       => esc_html__( 'Theme Options', '__plugin_txtd' ),
			'description' => esc_html__( 'Advanced options to change your site look-and-feel on a detailed level.', '__plugin_txtd' ),
			'sections'    => [],
		];

		// If we have other panels we will make their sections parts of the Theme Options panel.
		if ( ! empty( $other_panels_config ) ) {
			// If we have another panel that is called Theme Options we will extract it's sections and put them directly in the Theme Options panel.
			$second_theme_options_sections = [];
			foreach ( $other_panels_config as $panel_id => $panel_config ) {
				$found = false;
				// First try the panel ID.
				if ( false !== strpos( strtolower( str_replace( '-', '_', $panel_id ) ), 'theme_options' ) ) {
					$found = true;
				}

				// Second, try the panel title.
				if ( ! $found && ! empty( $panel_config['title'] ) && false !== strpos( strtolower( str_replace( [
						'-',
						'_',
					], ' ', $panel_config['title'] ) ), ' theme options' ) ) {
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
			$prefixed_sections = [];
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

		return $config;
	}

	/**
	 * Reorganizes sections and controls added directly to WP_Customizer, not through the config.
	 *
	 * @since 3.0.0
	 *
	 * @todo  Please note that this is house cleaning and it is only necessary due to the lack of complete standardization on the theme side. We should not need this forever!
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	protected function reorganize_direct_sections_and_controls( \WP_Customize_Manager $wp_customize ) {
		// If there is no style manager support, bail.
		if ( ! $this->is_supported() ) {
			return;
		}

		$theme_options_panel = $wp_customize->get_panel( 'theme_options_panel' );
		// Bail if we don't have a Theme Options panel since all that we do at the moment is related to it.
		if ( empty( $theme_options_panel ) ) {
			return;
		}

		/** @var \WP_Customize_Section $section */
		foreach ( $wp_customize->sections() as $section ) {
			// These are general theme options sections that need to have their controls moved to the Theme Options > General section.
			if ( false !== strpos( $section->id, 'theme_options' ) ) {
				// Find the General theme options section, if any.
				$general_section = false;
				foreach ( $theme_options_panel->sections as $theme_options_section ) {
					if ( false !== strpos( $theme_options_section->id, 'general' ) ) {
						$general_section = $section;
					}
				}

				if ( false === $general_section ) {
					// We need to add a general section in the Theme Options panel.
					$general_section = $wp_customize->add_section( 'theme_options[general]', [
						'title'    => esc_html__( 'General', '__plugin_txtd' ),
						'panel'    => $theme_options_panel->id,
						'priority' => 2,
					] );
				}

				// Move all the controls in the identified theme options section to the general one.
				/** @var \WP_Customize_Control $control */
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
	 * @since 3.0.0
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	protected function remove_switch_theme_panel( \WP_Customize_Manager $wp_customize ) {
		// If there is no style manager support, bail.
		if ( ! $this->is_supported() ) {
			return;
		}

		$wp_customize->remove_panel( 'themes' );
	}

	/**
	 * Reorganize the Customizer sections and panels, without accounting for Customify's configured ones.
	 *
	 * @param \WP_Customize_Manager $wp_customize WP_Customize_Manager instance.
	 */
	protected function general_reorganization_of_customize_sections( \WP_Customize_Manager $wp_customize ) {
		$sections = $wp_customize->sections();
		if ( ! empty( $sections['pro__section'] ) ) {
			$sections['pro__section']->priority = 24; // After the Style Manager panel.
		}

		// Add a pretty icon to Site Identity
		$wp_customize->get_section( 'title_tagline' )->title = esc_html__( 'Site Identity', '__plugin_txtd' );
	}

	/**
	 * Filter the config during the build up.
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	protected function pre_filter_based_on_theme_type( array $config ): array {
		if ( in_array( self::get_theme_type(), [ 'theme_wporg', 'theme_modular_wporg' ] ) ) {

			add_filter( 'customify_style_manager_color_palettes_colors_classes', function ( $classes ) {
				$classes[] = 'js-no-picker';

				return $classes;
			} );
		}

		return $config;
	}

	/**
	 * Filter the final config.
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	protected function filter_based_on_theme_type( array $config ): array {
		if ( ! empty( $config['panels']['style_manager_panel']['sections']['sm_color_palettes_section']['options'] ) && in_array( self::get_theme_type(), [
				'theme_wporg',
				'theme_modular_wporg',
			] ) ) {
			$color_palettes_options = $config['panels']['style_manager_panel']['sections']['sm_color_palettes_section']['options'];

			$options_to_remove = [
				'sm_color_diversity',
				'sm_shuffle_colors',
				'sm_dark_mode',
			];
			foreach ( $options_to_remove as $option_key ) {
				if ( isset( $color_palettes_options[ $option_key ] ) ) {
					$color_palettes_options[ $option_key ]['type'] = 'hidden_control';
				}
			}

			if ( ! empty( $color_palettes_options['sm_palette_filter']['choices'] ) ) {
				unset( $color_palettes_options['sm_palette_filter']['choices']['clarendon'] );
				unset( $color_palettes_options['sm_palette_filter']['choices']['pastel'] );
				unset( $color_palettes_options['sm_palette_filter']['choices']['greyish'] );
			}

			$config['panels']['style_manager_panel']['sections']['sm_color_palettes_section']['options'] = $color_palettes_options;
		}

		return $config;
	}

	/**
	 * Get the current theme type from the WUpdates code.
	 *
	 * Generally, this is a 'theme', but it could also be 'plugin', 'theme_modular', 'theme_wporg' or other markers we wish to use.
	 *
	 * @return string
	 */
	public static function get_theme_type(): string {
		$wupdates_identification = self::get_wupdates_identification_data();
		if ( empty( $wupdates_identification['type'] ) ) {
			return 'theme_wporg';
		}

		return sanitize_title( $wupdates_identification['type'] );
	}

	public static function get_wupdates_identification_data( $slug = '' ) {
		if ( empty( $slug ) ) {
			$slug = basename( get_template_directory() );
		}

		$wupdates_ids = self::get_all_wupdates_identification_data();

		// We really want an id (hash_id) and a type.
		if ( empty( $slug ) || empty( $wupdates_ids[ $slug ] ) || ! isset( $wupdates_ids[ $slug ]['id'] ) || ! isset( $wupdates_ids[ $slug ]['type'] ) ) {
			return false;
		}

		return $wupdates_ids[ $slug ];
	}

	public static function get_all_wupdates_identification_data(): array {
		if ( empty( self::$wupdates_ids ) ) {
			/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
			self::$wupdates_ids = apply_filters( 'wupdates_gather_ids', [] );
		}

		return self::$wupdates_ids;
	}

	/**
	 *  Add data to be available in JS.
	 *
	 * @since 3.0.0
	 *
	 * @param $localized
	 *
	 * @return mixed
	 */
	protected function add_to_localized_data( $localized ) {
		if ( empty( $localized['styleManager'] ) ) {
			$localized['styleManager'] = [];
		}

		$localized['styleManager']['userFeedback'] = [
			'nonce'    => wp_create_nonce( 'customify_style_manager_user_feedback' ),
			'provided' => get_option( self::USER_PROVIDED_FEEDBACK_OPTION_KEY, false ),
		];

		return $localized;
	}

	/**
	 * Output the user feedback modal markup, if we need to.
	 *
	 * @since 3.0.0
	 */
	protected function output_user_feedback_modal() {
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
								<input type="hidden" name="type" value="1_to_5"/>
								<div class="modal-header">
									<button type="button" class="close icon media-modal-close" data-dismiss="modal"
									        aria-label="Close"><span class="media-modal-icon"><span
												class="screen-reader-text">Close media panel</span></span></button>
									<!-- <a href="#" class="close button button--naked gray" data-dismiss="modal" aria-label="Close">Close</a> -->
								</div>
								<div class="modal-body full">
									<div class="box box--large">
										<div class="first-step">
											<h2 class="modal-title">How would you rate your experience in finding the
												right colors for your site?</h2>
											<div class="scorecard">
												<span>Poor</span>
												<label>
													<input type="radio" name="rating" value="1" required/>
													<span>1</span>
												</label>
												<label>
													<input type="radio" name="rating" value="2" required/>
													<span>2</span>
												</label>
												<label>
													<input type="radio" name="rating" value="3" required/>
													<span>3</span>
												</label>
												<label>
													<input type="radio" name="rating" value="4" required/>
													<span>4</span>
												</label>
												<label>
													<input type="radio" name="rating" value="5" required/>
													<span>5</span>
												</label>
												<span>Great</span>
											</div>
										</div>
										<div class="second-step hidden">
											<p><strong>What points along the way made this a <span
														class="rating-placeholder">5</span>* experience for
													you?</strong><br>We are counting on your insights to guide us in
												doing better 🙏</p>
											<div class="not-floating-labels">
												<div class="form-row field">
												<textarea name="message"
												          placeholder="Describe your experience in customizing your site colors.."
												          id="style-manager-user-feedback-message" rows="6"
												          oninvalid="this.setCustomValidity('May we have a little more info about your experience?')"
												          oninput="setCustomValidity('')" required></textarea>
												</div>
											</div>
											<button id="style-manager-user-feedback_btn" class="button"
											        type="submit"><?php _e( 'Send us your insights', '__plugin_txtd' ); ?></button>
										</div>
										<div class="thanks-step hidden">
											<h3 class="modal-title">Thank you so much for your feedback!</h3>
											<p>It means the world to us as we strive to constantly push the limits
												and aim higher. Stay awesome! 🤗</p>
											<p><em>The Pixelgrade Team</em></p>
										</div>
										<div class="error-step hidden">
											<h3 class="modal-title">We've hit a snag!</h3>
											<p>We couldn't record your feedback and we would truly appreciate it if
												you would try it again at a latter time. Stay awesome! 🤗</p>
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
	 * Check whether the user provided feedback, and, if so, return the timestamp.
	 *
	 * @return bool|int
	 */
	protected function user_provided_feedback() {
		$user_provided_feedback = get_option( self::USER_PROVIDED_FEEDBACK_OPTION_KEY );
		if ( empty( $user_provided_feedback ) ) {
			return false;
		}

		return $user_provided_feedback;
	}

	/**
	 * Determine if we should ask for user feedback.
	 *
	 * @param bool|int $timestamp_limit Optional. Timestamp to compare the time the user provided feedback.
	 *                                  If the provided timestamp is earlier than the time the user provided feedback, should ask again.
	 *
	 * @return bool
	 */
	protected function should_ask_for_feedback( $timestamp_limit = false ): bool {
		if ( defined( 'CUSTOMIFY_SM_ALWAYS_ASK_FOR_FEEDBACK' ) && true === CUSTOMIFY_SM_ALWAYS_ASK_FOR_FEEDBACK ) {
			return true;
		}

		$feedback_timestamp = $this->user_provided_feedback();
		if ( empty( $feedback_timestamp ) ) {
			return true;
		}

		if ( ! empty( $timestamp_limit ) && intval( $timestamp_limit ) > intval( $feedback_timestamp ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Callback for the user feedback AJAX call.
	 *
	 * @since 3.0.0
	 */
	protected function user_feedback_callback() {
		check_ajax_referer( 'customify_style_manager_user_feedback', 'nonce' );

		if ( empty( $_POST['type'] ) ) {
			wp_send_json_error( esc_html__( 'No type provided', '__plugin_txtd' ) );
		}

		if ( empty( $_POST['rating'] ) ) {
			wp_send_json_error( esc_html__( 'No rating provided', '__plugin_txtd' ) );
		}

		$type    = sanitize_text_field( $_POST['type'] );
		$rating  = intval( $_POST['rating'] );
		$message = '';
		if ( ! empty( $_POST['message'] ) ) {
			$message = wp_kses_post( $_POST['message'] );
		}

		$request_data = [
			'site_url'          => home_url( '/' ),
			'satisfaction_data' => [
				'type'    => $type,
				'rating'  => $rating,
				'message' => $message,
			],
		];

		// Send the feedback.
		$response = $this->cloud_client->send_stats( $request_data, true );
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( esc_html__( 'Sorry, something went wrong and we couldn\'t save your feedback.', '__plugin_txtd' ) );
		}
		$response_data = json_decode( wp_remote_retrieve_body( $response ), true );
		// Bail in case of decode error or failure to retrieve data
		if ( null === $response_data || empty( $response_data['code'] ) || 'success' !== $response_data['code'] ) {
			wp_send_json_error( esc_html__( 'Sorry, something went wrong and we couldn\'t save your feedback.', '__plugin_txtd' ) );
		}

		// We need to remember that the user provided feedback (and at what timestamp).
		update_option( self::USER_PROVIDED_FEEDBACK_OPTION_KEY, time(), true );

		wp_send_json_success( esc_html__( 'Thank you for your feedback.', '__plugin_txtd' ) );
	}
}
