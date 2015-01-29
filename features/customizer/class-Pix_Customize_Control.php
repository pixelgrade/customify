<?php

/**
 * Class Pix_Customize_Control
 *
 * with this class we will overwrite the default markup which WordPress comes with
 */
class Pix_Customize_Control extends WP_Customize_Control {

	/**
	 * Renders the control wrapper and calls $this->render_content() for the internals.
	 *
	 * @since 3.4.0
	 */
	protected function render() {
		$group = '';
		$id    = 'customize-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) );
		$class = 'pix_customizer_setting customize-control customize-control-' . $this->type;

		?><li id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
		<?php $this->render_content(); ?>
		</li><?php
	}
}
