<?php

/**
 * Class Pix_Customize_Select_Color_Control
 */
class Pix_Customize_Select_Color_Control extends Pix_Customize_Control {
	public $type = 'select_color';

	/**
	 * Render the control's content.
	 */
	public function render_content() {
		?>
		<label>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
		</label>

		<select <?php $this->link(); ?> class="js-color-select">
			<?php
			foreach ( $this->choices as $value => $label )
				echo '<option value="' . esc_attr( $value ) . '" ' . selected( $this->value(), $value, false ) . '>' . esc_html( $label ) . '</option>';
			?>
		</select>

		<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php endif;
	}
}
