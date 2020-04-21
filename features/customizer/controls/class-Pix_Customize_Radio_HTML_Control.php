<?php

/**
 * Class Pix_Customize_Radio_HTML_Control
 */
class Pix_Customize_Radio_HTML_Control extends Pix_Customize_Control {
	public $type = 'radio_html';
	public $description = null;

	/**
	 * Render the control's content.
	 */
	public function render_content() { ?>

		<label>
			<?php if ( ! empty( $this->label ) ) { ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php } ?>

			<div class="customify_radio_html">
				<?php
				foreach ( $this->choices as $value => $html ) { ?>
					<label>
						<input
							type="radio"
							name="<?php echo esc_attr( $this->setting->id ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
							<?php $this->link(); ?>
							<?php echo selected( $this->value(), $value, false ); ?>
						/>
						<div><?php echo $html; ?></div>
					</label>
				<?php } ?>
			</div>

			<?php if ( ! empty( $this->description ) ) { ?>
				<span class="description customize-control-description"><?php echo $this->description; ?></span>
			<?php } ?>
		</label>

	<?php }
}
