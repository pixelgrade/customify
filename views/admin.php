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

$config = include PixCustomifyPlugin()->get_base_path() . 'plugin-config' . EXT;

// invoke processor
$processor = pixcustomify::processor( $config );
$status    = $processor->status();
$errors    = $processor->errors(); ?>

<div class="wrap" id="pixcustomify_form">

	<div id="icon-options-general" class="icon32"><br></div>

	<h2><?php _e( 'PixCustomify', 'customify' ); ?></h2>

	<?php if ( $processor->ok() ): ?>

		<?php if ( ! empty( $errors ) ): ?>
			<br/>
			<p class="update-nag">
				<strong><?php _e( 'Unable to save settings.', 'customify' ); ?></strong>
				<?php _e( 'Please check the fields for errors and typos.', 'customify' ); ?>
			</p>
		<?php endif;

		if ( $processor->performed_update() ): ?>
			<br/>
			<p class="update-nag">
				<?php _e( 'Settings have been updated.', 'customify' ); ?>
			</p>
		<?php endif;
		echo $f = pixcustomify::form( $config, $processor );
		echo $f->field( 'hiddens' )->render();
		echo $f->field( 'general' )->render();
		echo $f->field( 'output' )->render();
		echo $f->field( 'typography' )->render();
		echo $f->field( 'tools' )->render(); ?>
		<button type="submit" class="button button-primary">
			<?php _e( 'Save Changes', 'customify' ); ?>
		</button>

		<?php echo $f->endform();

	elseif ( $status['state'] == 'error' ): ?>

		<h3>Critical Error</h3>

		<p><?php echo $status['message'] ?></p>

	<?php endif; ?>
</div>