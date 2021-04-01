<?php
/**
 * Customizer CSS editor control.
 *
 * @since   3.0.0
 * @license GPL-2.0-or-later
 * @package Pixelgrade Customify
 */

declare ( strict_types=1 );

namespace Pixelgrade\Customify\Screen\Customizer\Control;

/**
 * Customizer CSS editor control class.
 *
 * @since 3.0.0
 */
class CSSEditor extends BaseControl {
	public string $type = 'css_editor';

	/**
	 * Render the control's content.
	 */
	public function render_content() { ?>
		<style type="text/css" media="screen">
			#css_editor {
				display: inline-block;
				height: 700px;
				position: absolute;
				top: 130px;
				right: 0;
				bottom: 0;
				left: 0;
				z-index: 99999999;
			}
		</style>

		<textarea <?php $this->link(); ?> id="css_editor_textarea"><?php echo esc_textarea( $this->value() ); ?></textarea>
		<div id="css_editor"></div>
	<?php }
}
