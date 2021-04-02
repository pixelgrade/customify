<?php
/**
 * Provider for screens when editing posts/pages with the classic editor (TinyMCE).
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen;

use Pixelgrade\Customify\Provider\FrontendOutput;
use Pixelgrade\Customify\Provider\Options;
use Pixelgrade\Customify\Provider\PluginSettings;
use Pixelgrade\Customify\StyleManager\Fonts;
use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;

/**
 * Provider class for screens when editing posts/pages with the classic editor.
 *
 * This is the class that handles the overall logic for integration with the classic editor (TinyMCE).
 *
 * @since 3.0.0
 */
class EditWithClassicEditor extends AbstractHookProvider {

	/**
	 * User messages to display in the WP admin.
	 *
	 * @var array
	 */
	protected array $user_messages = [
		'error'   => [],
		'warning' => [],
		'info'    => [],
	];

	/**
	 * Options.
	 *
	 * @var Options
	 */
	protected Options $options;

	/**
	 * Plugin settings.
	 *
	 * @var PluginSettings
	 */
	protected PluginSettings $plugin_settings;

	/**
	 * Style Manager Fonts.
	 *
	 * @var Fonts
	 */
	protected Fonts $sm_fonts;

	/**
	 * Frontend output provider.
	 *
	 * @var FrontendOutput
	 */
	protected FrontendOutput $frontend_output;

	/**
	 * Logger.
	 *
	 * @var LoggerInterface
	 */
	protected LoggerInterface $logger;

	/**
	 * Create the edit with classic editor screen.
	 *
	 * @since 3.0.0
	 *
	 * @param Options         $options         Options.
	 * @param PluginSettings  $plugin_settings Plugin settings.
	 * @param Fonts           $sm_fonts        Style Manager Fonts.
	 * @param FrontendOutput  $frontend_output Frontend output.
	 * @param LoggerInterface $logger          Logger.
	 */
	public function __construct(
		Options $options,
		PluginSettings $plugin_settings,
		Fonts $sm_fonts,
		FrontendOutput $frontend_output,
		LoggerInterface $logger
	) {
		$this->options      = $options;
		$this->plugin_settings = $plugin_settings;
		$this->sm_fonts        = $sm_fonts;
		$this->frontend_output = $frontend_output;
		$this->logger       = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		$this->add_action( 'admin_enqueue_scripts', 'script_to_add_customizer_settings_into_wp_editor', 10, 1 );
	}

	/**
	 * Add our customizer styling edits into the wp_editor.
	 *
	 * @since 3.0.0
	 */
	protected function script_to_add_customizer_settings_into_wp_editor() {
		$current_screen = get_current_screen();
		// Bail if setting unchecked, if using the block editor,
		// or we are not on an admin page that might have editors (something related to posts, at the moment).
		if ( ! $this->plugin_settings->get( 'enable_editor_style', true )
		     || ! in_array( $current_screen->base, ['post'] )
		     || ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() )
			) {
			return;
		}

		$script = $this->get_fonts_editor_dynamic_script();
		if ( ! empty( $script ) ) {
			// Make sure the the script is enqueued in the footer. We want all the DOM to be loaded and need jQuery.
			wp_deregister_script( 'pixelgrade_customify-web-font-loader' );
			wp_register_script(
				'pixelgrade_customify-web-font-loader',
				$this->plugin->get_url( 'vendor_js/webfontloader-1-6-28.min.js' ),
				['jquery'],
				null,
				true );
			wp_enqueue_script( 'pixelgrade_customify-web-font-loader' );
			wp_add_inline_script( 'pixelgrade_customify-web-font-loader', $script );
		}

		ob_start();

		$this->frontend_output->output_dynamic_style();
		$this->sm_fonts->outputFontsDynamicStyle();

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

	/**
	 * Get the fonts editor dynamic inline script.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	protected function get_fonts_editor_dynamic_script(): string {
		// If typography has been deactivated from the settings, bail.
		if ( ! $this->plugin_settings->get( 'enable_typography', 'yes' ) ) {
			return '';
		}

		$args = $this->sm_fonts->getFontFamiliesDetailsForWebfontloader();

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
		$custom_families = [];
		$custom_urls = [];

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
}
