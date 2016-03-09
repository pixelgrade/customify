<?php

/**
 * Class Pix_Customize_Import_Demo_Data_Control
 *
 */
class Pix_Customize_Import_Demo_Data_Control extends Pix_Customize_Control {
	public $type = 'import_demo_data';
	public $action = null;
	public $notices = array();

	/**
	 * Render the control's content.
	 *
	 * @since 3.4.0
	 */
	public function render_content() {?>

		<a href="#" class="button button-primary customify_import_demo_data_button" data-key="<?php echo $this->setting->id ?>"><?php echo $this->label ?></a>

		<div class="wpGrade-loading-wrap hidden">
			<span class="wpGrade-loading wpGrade-import-loading"></span>
			<div class="wpGrade-import-wait">
				<?php
				if ( isset( $this->notices['wait'] ) ) {

					echo wp_kses( $this->notices['wait'], array(
						'a'      => array(
							'href'  => array(),
							'title' => array()
						),
						'br'     => array(),
						'em'     => array(),
						'strong' => array(),
						'p'      => array(),
						'div'    => array( 'class', 'id' ),
						'span'   => array( 'class', 'id' ),
					) );

				} else {
					esc_html_e( 'Please wait a few minutes (between 1 and 3 minutes usually, but depending on your hosting it can take longer) and ', 'customify' ); ?>
					<strong><?php esc_html_e( 'don\'t reload the page', 'customify' ); ?></strong>
					<?php esc_html__( 'You will be notified as soon as the import has finished!', 'customify' );
				} ?>
			</div>
		</div>

		<?php if ( ! empty( $this->description ) ) { ?>
			<span class="description customize-control-description"><?php echo $this->description; ?></span>
		<?php } ?>

		<div class="wpGrade-import-results hidden"></div>
		<div class="hr">
			<div class="inner"><span>&nbsp;</span></div>
		</div>
	<?php }
}