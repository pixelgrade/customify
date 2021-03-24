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

$config = Customify_Settings::get_plugin_config();

// invoke processor
$processor = pixcustomify::processor( $config );
$status    = $processor->status();
$errors    = $processor->errors(); ?>

<div class="wrap" id="pixcustomify_form">

	<div id="icon-options-general" class="icon32"><br></div>

	<h2><?php esc_html_e( 'Customify', '__plugin_txtd' ); ?></h2>

	<?php if ( $processor->ok() ): ?>

		<?php if ( ! empty( $errors ) ): ?>
			<br/>
			<p class="update-nag">
				<strong><?php esc_html_e( 'Unable to save settings.', '__plugin_txtd' ); ?></strong>
				<?php esc_html_e( 'Please check the fields for errors and typos.', '__plugin_txtd' ); ?>
			</p>
		<?php endif;

		if ( $processor->performed_update() ): ?>
			<br/>
			<p class="update-nag">
				<?php esc_html_e( 'Settings have been updated.', '__plugin_txtd' ); ?>
			</p>
		<?php endif;
		$f = pixcustomify::form( $config, $processor );
		echo $f->startform();

		echo $f->field( 'hiddens' )->render();
		echo $f->field( 'general' )->render();
		echo $f->field( 'output' )->render();
		echo $f->field( 'typography' )->render();
		echo $f->field( 'tools' )->render(); ?>
		<button type="submit" class="button button-primary">
			<?php esc_html_e( 'Save Changes', '__plugin_txtd' ); ?>
		</button>

		<?php echo $f->endform();

	elseif ( $status['state'] == 'error' ): ?>

		<h3><?php esc_html_e( 'Critical Error', '__plugin_txtd' ); ?></h3>

		<p><?php echo $status['message'] ?></p>

	<?php endif; ?>
</div>
