<?php
/**
 * General admin dashboard screen provider.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen;

use Pixelgrade\Customify\Vendor\Cedaro\WP\Plugin\AbstractHookProvider;
use Pixelgrade\Customify\Vendor\Psr\Log\LoggerInterface;

/**
 * General admin dashboard screen provider class.
 *
 * @since 3.0.0
 */
class GeneralAdmin extends AbstractHookProvider {

	/**
	 * Logger.
	 *
	 * @var LoggerInterface
	 */
	protected LoggerInterface $logger;

	/**
	 * Create the setting screen.
	 *
	 * @since 3.0.0
	 *
	 * @param LoggerInterface $logger       Logger.
	 */
	public function __construct(
		LoggerInterface $logger
	) {
		$this->logger       = $logger;
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		$this->add_action( 'after_switch_theme', 'maybe_show_notice_to_migrate_when_child_theme', 100, 2 );
		$this->add_action( 'wp_ajax_customify_migrate_customizations_from_parent_to_child_theme', 'migrate_customizations_from_parent_to_child_theme' );
		$this->add_action( 'admin_init', 'migrate_to_advanced_dark_mode_control' );
		$this->add_action( 'admin_enqueue_scripts', 'enqueue_assets' );
	}

	/**
	 * Hook up to show notice for customization options migration.
	 *
	 * @since 3.0.0
	 *
	 * @param string    $old_theme_name
	 * @param \WP_Theme $old_theme
	 */
	protected function maybe_show_notice_to_migrate_when_child_theme( string $old_theme_name, \WP_Theme $old_theme ) {
		$current_theme = wp_get_theme();
		// If the current theme is a child theme, show a notice.
		if ( $current_theme->exists()
		     && $old_theme->exists()
		     && $current_theme->get_template() === $old_theme->get_stylesheet() ) {

			$this->add_action( 'admin_notices', 'child_theme_migrate_theme_mods_notice' );
		}
	}

	/**
	 * Output a notice allowing for theme mods migration from the parent theme to the current child theme.
	 *
	 * @since 3.0.0
	 *
	 * @global string $pagenow
	 */
	function child_theme_migrate_theme_mods_notice() {
		global $pagenow;

		// We only show the notice on the themes dashboard, and if we are allowed to.
		if ( 'themes.php' !== $pagenow
		     || ! is_child_theme()
		     || true !== apply_filters( 'customify_allow_child_theme_mod_migrate_notice', true )
		     || ! current_user_can( 'manage_options' ) ) {

			return;
		}

		$parent_theme = wp_get_theme( get_template() );
		if ( ! $parent_theme->exists() ) {
			return;
		}

		ob_start(); ?>
		<div class="customify-notice__container updated notice fade is-dismissible">
			<h3><?php echo sprintf( __( 'You have activated a child theme for "%s". Good for you!', '__plugin_txtd' ), $parent_theme->get('Name') ); ?></h3>
			<p>
				<?php echo wp_kses_post( __( 'If you have already <strong>set up things in the Customizer,</strong> you may want to <strong>keep those customizations</strong> so you don\'t start over.', '__plugin_txtd' ) ); ?>
			</p>
			<p>
				<?php echo wp_kses_post( __( 'So, the question is simple: <strong>would you like to migrate all theme-specific options (theme mods) from the parent theme to the child one?</strong>', '__plugin_txtd' ) ); ?>
			</p>
			<p>
				<?php echo wp_kses_post( __( 'All parent theme customizations will remain in place, while those of the active child theme will be overwritten, if any.', '__plugin_txtd' ) ); ?>
			</p>
			<form class="customify-notice-form" method="post">
				<noscript><input type="hidden" name="customify-notice-no-js" value="1"/></noscript>

				<p>
					<button class="customify-notice-button button button-primary js-handle-customify">
						<span class="customify-notice-button__text"><?php esc_html_e( 'Yes, migrate customizations', '__plugin_txtd' ); ?></span>
					</button>
					<button type="submit" class="customify-dismiss-button button button-secondary js-dismiss-customify"><?php esc_html_e( 'No, thank you', '__plugin_txtd' ); ?></button>
					&nbsp;<span class="message js-plugin-message" style="font-style:italic"></span>
				</p>

				<?php wp_nonce_field( 'customify_migrate_customizations_from_parent_to_child_theme', 'nonce-customify_theme_mods_migrate' ); ?>
			</form>
		</div>
		<script>
			(function ($) {
				$(function () {
					let $noticeContainer = $('.customify-notice__container'),
						$button = $noticeContainer.find('.js-handle-customify'),
						$buttonText = $noticeContainer.find('.customify-notice-button__text'),
						$dismissButton = $noticeContainer.find('.js-dismiss-customify'),
						$statusMessage = $noticeContainer.find('.js-plugin-message')

					$button.on('click', function (e) {
						e.preventDefault();

						$buttonText.html("<?php esc_html_e( 'Migrating customizations..' ,'__plugin_txtd'); ?>")
						$button.attr('disabled', true)
						$dismissButton.hide()

						// Do an AJAX call to migrate the theme_mods.
						$.ajax({
							url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
							type: 'post',
							data: {
								action: 'customify_migrate_customizations_from_parent_to_child_theme',
								nonce_migrate: $noticeContainer.find('#nonce-customify_theme_mods_migrate').val()
							}
						})
							.done(function(response) {
								if (typeof response.success !== 'undefined' && response.success) {
									$statusMessage.html("<?php esc_html_e( 'Successfully migrated the parent customizations! Enjoy crafting your site!', '__plugin_txtd' ); ?>")
									$buttonText.html("<?php esc_html_e( 'Finished migration', '__plugin_txtd' ); ?>")
								} else {
									$statusMessage.html("<?php esc_html_e( 'Something went wrong and we couldn\'t migrate the customizations.' ,'__plugin_txtd'); ?>")
									$buttonText.html("<?php esc_html_e( 'Migration error' ,'__plugin_txtd'); ?>")
								}
							})
							.fail(function() {
								$statusMessage.html("<?php esc_html_e( 'Something went wrong and we couldn\'t migrate the customizations.' ,'__plugin_txtd'); ?>")
								$buttonText.html("<?php esc_html_e( 'Migration error' ,'__plugin_txtd'); ?>")
							})
					})

					// Dismiss the notice.
					$dismissButton.on('click', function (e) {
						e.preventDefault();

						$noticeContainer.slideUp();
					})
				})
			})(jQuery)
		</script>
		<?php
		echo ob_get_clean();
	}

	/**
	 * Process ajax call to migrate customizations from parent to current child theme.
	 *
	 * @since 3.0.0
	 */
	function migrate_customizations_from_parent_to_child_theme() {
		// Check nonce.
		check_ajax_referer( 'customify_migrate_customizations_from_parent_to_child_theme', 'nonce_migrate' );

		$parent_theme = wp_get_theme( get_template() );
		if ( ! $parent_theme->exists() ) {
			wp_send_json_error();
		}

		// Migrate theme mods
		$parent_theme_mods = get_option( "theme_mods_" . $parent_theme->get_stylesheet() );
		// We need to exclude certain theme_mods since they are not needed by the child theme.
		$excluded = [
			'pixcare_license',
			'pixcare_new_theme_version',
			'pixcare_install_notice_dismissed',
		];
		foreach ( $excluded as $exclude ) {
			unset( $parent_theme_mods[ $exclude ] );
		}
		// Finally, write the new theme mods for the active child theme.
		if ( ! update_option( "theme_mods_" . get_option( 'stylesheet' ), $parent_theme_mods ) ) {
			wp_send_json_error( esc_html__( 'Could not update the child theme theme_mods.', '__plugin_txtd' ) );
		}

		// Redirect if this is not an ajax request.
		if ( isset( $_POST['pixcare-notice-no-js'] ) ) {

			// Go back to where we came from.
			wp_safe_redirect( wp_get_referer() );
			exit();
		}

		wp_send_json_success();
	}

	/**
	 * Migrate data from the simple Dark Mode control to Advanced Dark Mode Control, if the current theme supports it.
	 *
	 * @since 3.0.0
	 */
	function migrate_to_advanced_dark_mode_control() {
		// Bail if the current theme doesn't support the advanced control.
		if ( ! current_theme_supports( 'style_manager_advanced_dark_mode' ) ) {
			return;
		}

		$advanced_dark_mode = get_option( 'sm_dark_mode_advanced', null );
		// Bail if we already have advanced control data saved.
		if ( ! is_null( $advanced_dark_mode ) ) {
			return;
		}

		// Bail if there isn't a simple dark mode option saved.
		$simple_dark_mode = get_option( 'sm_dark_mode', null );
		if ( is_null( $simple_dark_mode ) ) {
			return;
		}

		// If the simple control value was on, we have work to do.
		if ( 'on' === $simple_dark_mode ) {
			$old_sm_dark_primary_final    = get_option( 'sm_dark_primary_final' );
			$old_sm_dark_secondary_final  = get_option( 'sm_dark_secondary_final' );
			$old_sm_dark_tertiary_final   = get_option( 'sm_dark_tertiary_final' );
			$old_sm_light_primary_final   = get_option( 'sm_light_primary_final' );
			$old_sm_light_secondary_final = get_option( 'sm_light_secondary_final' );
			$old_sm_light_tertiary_final  = get_option( 'sm_light_tertiary_final' );

			update_option( 'sm_dark_mode_advanced', 'on' );
			update_option( 'sm_dark_mode', 'off' );
			update_option( 'sm_dark_primary_final', $old_sm_light_primary_final );
			update_option( 'sm_dark_secondary_final', $old_sm_light_secondary_final );
			update_option( 'sm_dark_tertiary_final', $old_sm_light_tertiary_final );
			update_option( 'sm_light_primary_final', $old_sm_dark_primary_final );
			update_option( 'sm_light_secondary_final', $old_sm_dark_secondary_final );
			update_option( 'sm_light_tertiary_final', $old_sm_dark_tertiary_final );
		} else {
			update_option( 'sm_dark_mode_advanced', 'off' );
		}
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 3.0.0
	 */
	protected function enqueue_assets() {
		wp_enqueue_style( 'pixelgrade_customify-sm-colors-custom-properties' );
	}
}
