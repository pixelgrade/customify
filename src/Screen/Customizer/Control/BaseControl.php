<?php
/**
 * Customizer base control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer base control class.
 *
 * With this class we will overwrite the default markup which WordPress comes with.
 *
 * @since 3.0.0
 */
class BaseControl extends \WP_Customize_Control {

	/**
	 * Renders the control wrapper and calls $this->render_content() for the internals.
	 */
	protected function render() {
		$id    = 'customize-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) );
		$class = 'pix_customizer_setting customize-control customize-control-' . $this->type;

		?><li id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
		<?php $this->render_content(); ?>
		</li><?php
	}
}
