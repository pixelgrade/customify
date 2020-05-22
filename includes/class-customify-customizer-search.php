<?php
/**
 * This is the class that handles the logic for Customizer controls search.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Customizer_Search' ) ) :

class Customify_Customizer_Search {

	/**
	 * Holds the only instance of this class.
	 * @var     null|Customify_Customizer_Search
	 * @access  protected
	 * @since   2.9.0
	 */
	protected static $_instance = null;

	/**
	 * Constructor.
	 *
	 * @since 2.9.0
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * Initialize this module.
	 *
	 * @since 2.9.0
	 */
	public function init() {
		// Hook up.
		$this->add_hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since 2.9.0
	 */
	public function add_hooks() {

		/*
		 * Enqueue the needed scripts and styles.
		 */
		add_action( 'customize_controls_init', array( $this, 'register_admin_customizer_scripts' ), 10 );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_admin_customizer_scripts' ), 10 );

		/*
		 * Print the needed JavaScript templates.
		 */
		add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_js_template' ) );
	}

	/**
	 * Register Customizer admin scripts.
	 *
	 * @since 2.9.0
	 */
	public function register_admin_customizer_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script( PixCustomifyPlugin()->get_slug() . '-customizer-search',
			plugins_url( 'js/customizer/search' . $suffix . '.js', PixCustomifyPlugin()->get_file() ),
			array( 'jquery', ), PixCustomifyPlugin()->get_version() );
	}

	/**
	 * Enqueue Customizer admin scripts.
	 *
	 * @since 2.9.0
	 */
	public function enqueue_admin_customizer_scripts() {
		// If there is no customizer search support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}

		wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-customizer-search' );
	}

	public function print_js_template() {
		// If there is no customizer search support, bail early.
		if ( ! $this->is_supported() ) {
			return;
		}
		?>
		<script type="text/html" id="tmpl-customify-search-button">
			<button type="button" class="customize-search-toggle dashicons dashicons-search" aria-expanded="false"><span class="screen-reader-text"><?php esc_html_e( 'Search', 'customify' ); ?></span></button>
		</script>

		<script type="text/html" id="tmpl-customify-search-form">
			<div id="accordion-section-customify-customizer-search" style="display: none;">
				<h4 class="customify-customizer-search-section accordion-section-title">
					<span class="search-input-label"><?php esc_html_e( 'Search through all available controls', 'customify' ); ?></span>
					<span class="search-field-wrapper">
						<input type="text" placeholder="<?php esc_html_e( 'Start typing...', 'customify' ); ?>" name="customify-customizer-search-input" autofocus="autofocus" id="customify-customizer-search-input" class="customizer-search-input" />
						<span class="search-field-button-wrapper">
							<button type="button" class="clear-search button button-primary has-next-sibling" tabindex="0" aria-label="<?php esc_html_e( 'Clear current search', 'customify' ); ?>"><?php esc_html_e( 'Clear', 'customify' ); ?></button>
							<button type="button" class="close-search button-primary button dashicons dashicons-no" aria-label="<?php esc_html_e( 'Close search', 'customify' ); ?>"></button>
						</span>
					</span>
				</h4>
			</div>
		</script>
	<?php }

	/**
	 * Determine if the Customizer search is supported.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function is_supported() {
		// Determine if the controls search functionality is supported.
		return apply_filters( 'customify_customizer_search_is_supported', true );
	}

	/**
	 * Main Customify_Customizer_Search Instance
	 *
	 * Ensures only one instance of Customify_Customizer_Search is loaded or can be loaded.
	 *
	 * @since  2.9.0
	 * @static
	 *
	 * @return Customify_Customizer_Search Main Customify_Customizer_Search instance
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.9.0
	 */
	public function __clone() {

		_doing_it_wrong( __FUNCTION__,esc_html__( 'You should not do that!', 'customify' ), null );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.9.0
	 */
	public function __wakeup() {

		_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ),  null );
	}
}

endif;
