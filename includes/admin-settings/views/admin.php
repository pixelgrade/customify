<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should
 * provide the user interface to the end user.
 *
 * @package   customify
 * @author    Pixelgrade <contact@pixelgrade.com>
 * @license   GPL-2.0+
 * @link      http://pixelgrade.com
 * @copyright 2013 Pixel Grade Media
 */

/**
 * @var $config
 * @var $processor
 * @var $status
 * @var $errors
 */
 ?>

<div class="wrap" id="pixcustomify_form">

	<div id="icon-options-general" class="icon32"><br></div>

	<h2><?php esc_html_e( 'Customify', 'customify' ); ?></h2>

	<?php if ( $processor->ok() ): ?>

		<?php if ( ! empty( $errors ) ): ?>
			<br/>
			<p class="update-nag">
				<strong><?php esc_html_e( 'Unable to save settings.', 'customify' ); ?></strong>
				<?php esc_html_e( 'Please check the fields for errors and typos.', 'customify' ); ?>
			</p>
		<?php endif;

		if ( $processor->performed_update() ): ?>
			<br/>
			<p class="update-nag">
				<?php esc_html_e( 'Settings have been updated.', 'customify' ); ?>
			</p>
		<?php endif;
		$f = pixcustomify::form( $config, $processor );
		echo $f->startform();

		echo $f->field( 'hiddens' )->render();
		echo $f->field( 'general' )->render();
		echo $f->field( 'output' )->render();
		echo $f->field( 'typography' )->render();
		echo $f->field( 'tools' )->render(); ?>
		<?php wp_nonce_field( 'customify_settings_save', '_wpnonce-customify-settings' ); ?>
		<button type="submit" class="button button-primary">
			<?php esc_html_e( 'Save Changes', 'customify' ); ?>
		</button>

		<?php echo $f->endform();

	elseif ( $status['state'] == 'error' ): ?>

		<h3><?php esc_html_e( 'Critical Error', 'customify' ); ?></h3>

		<p><?php echo $status['message'] ?></p>

	<?php endif; ?>
</div>
