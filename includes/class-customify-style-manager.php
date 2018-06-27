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
	 * @since   1.7.5
	 */
	protected $theme_configs = null;

	/**
	 * The color palettes object.
	 * @var     null|Customify_Color_Palettes
	 * @access  public
	 * @since   1.7.5
	 */
	protected $color_palettes = null;

	/**
	 * The font palettes object.
	 * @var     null|Customify_Font_Palettes
	 * @access  public
	 * @since   1.7.5
	 */
	protected $font_palettes = null;

	/**
	 * The Cloud API object.
	 * @var     null|Customify_Cloud_Api
	 * @access  public
	 * @since   1.7.5
	 */
	protected $cloud_api = null;

	/**
	 * Constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param $parent
	 */
	protected function __construct( $parent = null ) {
		$this->parent = $parent;

		$this->init();
	}

	/**
	 * Initialize this module.
	 *
	 * @since 1.7.5
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
	 * Output the user feedback modal markup, if we need to.
	 *
	 * @since 1.7.0
	 */
	public function output_user_feedback_modal() {
		// If there is no style manager support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		// Only output if the user didn't provide feedback.
		if ( ! $this->user_provided_feedback() ) { ?>
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
											<h2 class="modal-title">How would you rate your experience with using Color Palettes?</h2>
											<div class="scorecard">
												<span>Worst</span>
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
												<span>Best</span>
											</div>
										</div>
										<div class="second-step hidden">
											<p><strong>What makes you give <span class="rating-placeholder">5</span>*?</strong> I hope you’ll answer and help us do better:</p>
											<div class="not-floating-labels">
												<div class="form-row field">
												<textarea name="message" placeholder="Your message.."
												          id="style-manager-user-feedback-message" rows="4" oninvalid="this.setCustomValidity('May we have a little more info about your experience?')" oninput="setCustomValidity('')" required></textarea>
												</div>
											</div>
											<button id="style-manager-user-feedback_btn" class="button" type="submit"><?php _e( 'Submit my feedback', 'customify' ); ?></button>
										</div>
										<div class="thanks-step hidden">
											<h3 class="modal-title">Thanks for your feedback!</h3>
											<p>This will help us improve the product. Stay awesome! 🤗</p>
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
	 * @param bool|int $timestamp_limit Optional. Timestamp to compare the time the user provided feedback.
	 *                              If the provided timestamp is earlier than the time the user provided feedback, returns false.
	 *
	 * @return bool
	 */
	public function user_provided_feedback( $timestamp_limit = false ) {
		if ( defined( 'CUSTOMIFY_SM_ALWAYS_ASK_FOR_FEEDBACK' ) && true === CUSTOMIFY_SM_ALWAYS_ASK_FOR_FEEDBACK ) {
			return false;
		}

		$user_provided_feedback = get_option( 'style_manager_user_feedback_provided' );
		if ( empty( $user_provided_feedback ) ) {
			return false;
		}

		if ( ! empty( $timestamp ) && is_int( $timestamp ) && $timestamp_limit > $user_provided_feedback ) {
			return  false;
		}

		return true;
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
	 * @param  object $parent Main PixCustomifyPlugin instance.
	 *
	 * @return Customify_Style_Manager Main Customify_Style_Manager instance
	 */
	public static function instance( $parent = null ) {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
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
