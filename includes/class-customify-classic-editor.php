<?php
/**
 * This is the class that handles the overall logic for integration with the classic editor (TinyMCE).
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @since       2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Customify_Classic_Editor' ) ) {

	class Customify_Classic_Editor {

		/**
		 * Holds the only instance of this class.
		 * @var     null|Customify_Classic_Editor
		 * @access  protected
		 * @since   2.7.0
		 */
		protected static $_instance = null;

		/**
		 * Constructor.
		 *
		 * @since 2.7.0
		 */
		protected function __construct() {
			// We will initialize the logic after the plugin has finished with it's configuration (at priority 15).
			add_action( 'init', array( $this, 'init' ), 15 );
		}

		/**
		 * Initialize this module.
		 *
		 * @since 2.7.0
		 */
		public function init() {

			// Hook up.
			$this->add_hooks();
		}

		/**
		 * Initiate our hooks
		 *
		 * @since 2.7.0
		 */
		public function add_hooks() {

			add_action( 'admin_enqueue_scripts', array( $this, 'script_to_add_customizer_settings_into_wp_editor' ), 10, 1 );
		}

		/**
		 * Add our customizer styling edits into the wp_editor
		 */
		function script_to_add_customizer_settings_into_wp_editor() {
			$current_screen = get_current_screen();
			// Bail if setting unchecked, if using the block editor,
			// or we are not on an admin page that might have editors (something related to posts, at the moment).
			if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'enable_editor_style', true )
			     || ! in_array( $current_screen->base, ['post'] )
			     || ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() )
				) {
				return;
			}

			$script = $this->get_fonts_editor_dynamic_script();
			if ( ! empty( $script ) ) {
				// Make sure the the script is enqueued in the footer. We want all the DOM to be loaded and need jQuery.
				wp_deregister_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader' );
				wp_register_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader',
					plugins_url( 'js/vendor/webfontloader-1-6-28.min.js', PixCustomifyPlugin()->get_file() ), array('jquery'), null, true );
				wp_enqueue_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader' );
				wp_add_inline_script( PixCustomifyPlugin()->get_slug() . '-web-font-loader', $script );
			}

			ob_start();

			PixCustomify_Customizer::instance()->output_dynamic_style();
			Customify_Fonts_Global::instance()->outputFontsDynamicStyle();

			$custom_output = ob_get_clean();

			ob_start(); ?>
(function ($) {
	$(window).on('load',function () {
		/**
		* @param iframe_id the id of the frame you want to append the style
		* @param style_element the style element you want to append - boooom
		*/
		const append_script_to_iframe = function (ifrm_id, scriptEl) {
			var myIframe = document.getElementById(ifrm_id);

			var script = myIframe.contentWindow.document.createElement("script");
			script.type = "text/javascript";
			if (scriptEl.getAttribute("src")) { script.src = scriptEl.getAttribute("src"); }
			script.innerHTML = scriptEl.innerHTML;

			myIframe.contentWindow.document.head.appendChild(script);
		};

		const append_style_to_iframe = function (ifrm_id, styleElement) {
			var ifrm = window.frames[ifrm_id];
			if ( typeof ifrm === "undefined" ) {
				return;
			}
			ifrm = ( ifrm.contentDocument || ifrm.contentDocument || ifrm.document );
			var head = ifrm.getElementsByTagName('head')[0];

			if (typeof styleElement !== "undefined") {
				head.appendChild(styleElement);
			}
		};

		const xmlString = <?php echo json_encode( str_replace( "\n", "", $custom_output ) ); ?>,
			parser = new DOMParser();

		$('.mce-edit-area iframe').each(function(idx, iframe) {
			if (typeof iframe.id !== 'undefined' ) {
				const doc = parser.parseFromString(xmlString, "text/html");
				$.each(doc.head.childNodes, function (key, el) {
					if (typeof el !== "undefined" && typeof el.tagName !== "undefined") {

						switch (el.tagName) {
							case 'STYLE' :
								append_style_to_iframe(iframe.id, el);
								break;
							case 'SCRIPT' :
								append_script_to_iframe(iframe.id, el);
								break;
							default:
								break;
						}
					}
				});
			}
		})
	});
})(jQuery);
<?php
			$script = ob_get_clean();
			wp_add_inline_script( 'editor', $script );

		}

		protected function get_fonts_editor_dynamic_script() {
		// If typography has been deactivated from the settings, bail.
		if ( ! PixCustomifyPlugin()->settings->get_plugin_setting( 'typography', '1' ) ) {
			return '';
		}

		$args = Customify_Fonts_Global::instance()->getFontFamiliesDetailsForWebfontloader();

		if ( empty ( $args['custom_families'] ) && empty ( $args['google_families'] ) ) {
			return '';
		}

		ob_start(); ?>
(function ($) { $(window).on('load',function () {
const customifyIframeFontLoader = function(context) {
	const webfontargs = {
		classes: true,
		events: true,
		loading: function() {
			$( window ).trigger( 'wf-loading' );
		},
		active: function() {
			$( window ).trigger( 'wf-active' );
		},
		inactive: function() {
			$( window ).trigger( 'wf-inactive' );
		},
		context: context
	};
		<?php if ( ! empty( $args['google_families'] ) ) { ?>
	webfontargs.google = {
		families: [<?php echo join( ',', $args['google_families'] ); ?>]
	};
		<?php }
		$custom_families = array();
		$custom_urls = array();

		if ( ! empty( $args['custom_families'] ) && ! empty( $args['custom_srcs'] ) ) {
			$custom_families += $args['custom_families'];
			$custom_urls += $args['custom_srcs'];
		}

		if ( ! empty( $custom_families ) && ! empty( $custom_urls ) ) { ?>
	webfontargs.custom = {
		families: [<?php echo join( ',', $custom_families ); ?>],
		urls: [<?php echo join( ',', $custom_urls ) ?>]
	};
		<?php } ?>
	WebFont.load(webfontargs);
};
if (typeof WebFont !== 'undefined') {
	$('.mce-edit-area iframe').each(function(idx, el) {
		if (typeof el.id !== 'undefined' ) {
			customifyIframeFontLoader(frames[el.id].contentWindow)
		}
	})
}
}); })(jQuery);<?php
		$output = ob_get_clean();

		return apply_filters( 'customify_fonts_editor_webfont_script', $output );
	}

		/**
		 * Main Customify_Classic_Editor Instance
		 *
		 * Ensures only one instance of Customify_Classic_Editor is loaded or can be loaded.
		 *
		 * @return Customify_Classic_Editor Main Customify_Classic_Editor instance
		 * @since  2.7.0
		 * @static
		 *
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
		 * @since 2.7.0
		 */
		public function __clone() {

			_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ), null );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 2.7.0
		 */
		public function __wakeup() {

			_doing_it_wrong( __FUNCTION__, esc_html__( 'You should not do that!', 'customify' ), null );
		}
	}
}
