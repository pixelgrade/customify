<?php

/**
 * Class Pix_Customize_Background_Control
 */
class Pix_Customize_Background_Control extends Pix_Customize_Control {
	public $type = 'custom_background';

	public $value = array();

	public $field = array(
		'class' => ''
	);

	public $field_defaults = array(
		'background-color'      => true,
		'background-repeat'     => true,
		'background-attachment' => true,
		'background-position'   => true,
		'background-image'      => true,
		'background-gradient'   => false,
		'background-clip'       => false,
		'background-origin'     => false,
		'background-size'       => true,
		'preview_media'         => true,
		'preview'               => true,
		'preview_height'        => '200px',
		'transparent'           => true,
	);

	/**
	 * Render the control's content.
	 */
	public function render_content() {

		// ensure some values
		$this->value = $this->value();
		$this->field = array_merge( $this->field, $this->field_defaults );

		if ( $this->field['background-image'] === true ) {

			if ( empty( $this->value['background-image'] ) && ! empty( $this->value['media']['id'] ) ) {
				$img                             = wp_get_attachment_image_src( $this->value['media']['id'], 'full' );
				$this->value['background-image'] = $img[0];
				$this->value['media']['width']   = $img[1];
				$this->value['media']['height']  = $img[2];
			}

			if ( ! is_array( $this->value ) || ! isset( $this->value['media'] ) || empty( $this->value['media'] ) ) {
				$this->value = array();
				$media_array          = array(
					'id'        => '',
					'height'    => '',
					'width'     => '',
					'thumbnail' => ''
				);
				$this->value['media'] = $media_array;
			}

			if ( ! isset( $this->value['background-image'] ) ) {
				$this->value['background-image'] = '';
			}

			$hide = 'hide ';

			if ( ( isset( $this->field['preview_media'] ) && $this->field['preview_media'] === false ) ) {
				$this->field['class'] .= " noPreview";
			}

			if ( ( ! empty( $this->field['background-image'] ) && $this->field['background-image'] === true ) || isset( $this->field['preview'] ) && $this->field['preview'] === false ) {
				$hide = '';
			}

			$placeholder = isset( $this->field['placeholder'] ) ? $this->field['placeholder'] : __( 'No media selected', 'customify' );

			echo '<input type="text" 
				class="customify_background_input background-image ' . $hide . 'upload ' . $this->field['class'] . '" 
				name="' . esc_attr( $this->setting->id ) . '[background-image]" 
				id="_customize-input-' . esc_attr( $this->setting->id ) . '[background-image]" 
				value="' . $this->value['background-image'] . '"  
				data-select_name="background-image" 
				data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-image]" 
				placeholder="' . esc_attr( $placeholder ) . '"/>';
			echo '<input type="hidden" 
				class="upload-id background-media" 
				name="' . esc_attr( $this->setting->id ) . '[media][id]" 
				id="_customize-input-' . esc_attr( $this->setting->id ) . '[media][id]" 
				value="' . esc_attr( $this->value['media']['id'] ) . '" />';
			echo '<input type="hidden" 
				class="upload-height background-media" 
				name="' . esc_attr( $this->setting->id ) . '[media][height]" 
				id="_customize-input-' . esc_attr( $this->setting->id ) . '[media][height]" 
				value="' . esc_attr( $this->value['media']['height'] ) . '" />';
			echo '<input type="hidden" 
				class="upload-width background-media" 
				name="' . esc_attr( $this->setting->id ) . '[media][width]" 
				id="_customize-input-' . esc_attr( $this->setting->id ) . '[media][width]" 
				value="' . esc_attr( $this->value['media']['width'] ) . '" />';
			echo '<input type="hidden" 
				class="upload-thumbnail background-media" 
				name="' . esc_attr( $this->setting->id ) . '[media][thumbnail]" 
				id="_customize-input-' . esc_attr( $this->setting->id ) . '[media][thumbnail]" 
				value="' . esc_attr( $this->value['media']['thumbnail'] ) . '" />';

			// Preview
			$hide = '';

			if ( ( isset( $this->field['preview_media'] ) && $this->field['preview_media'] === false ) || empty( $this->value['background-image'] ) ) {
				$hide = 'hide ';
			}

			if ( empty( $this->value['media']['thumbnail'] ) && ! empty( $this->value['background-image'] ) ) { // Just in case
				if ( ! empty( $this->value['media']['id'] ) ) {
					$image                             = wp_get_attachment_image_src( $this->value['media']['id'] );
					$this->value['media']['thumbnail'] = $image[0];
				} else {
					$this->value['media']['thumbnail'] = $this->value['background-image'];
				}
			}

			echo '<div class="' . $hide . 'preview_screenshot">';
			echo '<a class="of-uploaded-image" href="' . $this->value['background-image'] . '" target="_blank">';
			echo '<img class="preview_image" id="image_' . $this->value['media']['id'] . '" src="' . $this->value['media']['thumbnail'] . '" alt="" target="_blank" rel="external" />';
			echo '</a>';
			echo '</div>';

			//Upload controls DIV
			echo '<div class="upload_button_div">';

			//If the user has WP3.5+ show upload/remove button
			echo '<span class="button background_upload_button" id="' . esc_attr( $this->id ) . '-media" data-setting_id="' . esc_attr( $this->setting->id ) . '" >' . esc_html__( 'Upload', 'customify' ) . '</span>';

			$hide = '';
			if ( empty( $this->value['background-image'] ) || $this->value['background-image'] == '' ) {
				$hide = ' hide';
			}

			echo '<span class="button remove-image' . $hide . '" id="reset_' . esc_attr( $this->id ) . '" rel="' . esc_attr( $this->id ) . '">' . esc_html__( 'Remove', 'customify' ) . '</span>';

			echo '</div>';
		}

		if ( $this->field['background-repeat'] === true ) {
			$array = array(
				'no-repeat' => 'No Repeat',
				'repeat'    => 'Repeat All',
				'repeat-x'  => 'Repeat Horizontally',
				'repeat-y'  => 'Repeat Vertically',
				'inherit'   => 'Inherit',
			);

			if ( ! isset( $this->value['background-repeat'] ) ) {
				$this->value['background-repeat'] = '';
			}

			echo '<select id="' . $this->id . '-repeat-select" 
				name="_customize-input-' . esc_attr( $this->setting->id ) . '[background-repeat]" 
				class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" 
				data-select_name="background-repeat" 
				data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-repeat]">';
			echo '<option disabled ' . selected( $this->value['background-repeat'], '', false ) . '>' . esc_html__( 'Background repeat..', 'customify' ) . '</option>';
			foreach ( $array as $k => $v ) {
				echo '<option value="' . $k . '"' . selected( $this->value['background-repeat'], $k, false ) . '>' . $v . '</option>';
			}
			echo '</select>';
		}

		if ( $this->field['background-clip'] === true ) {
			$array = array(
				'inherit'     => 'Inherit',
				'border-box'  => 'Border Box',
				'content-box' => 'Content Box',
				'padding-box' => 'Padding Box',
			);

			if ( ! isset( $this->value['background-clip'] ) ) {
				$this->value['background-clip'] = '';
			}

			echo '<select id="' . $this->id . '-clip-select" 
				name="_customize-input-' . esc_attr( $this->setting->id ) . '[background-clip]" 
				class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" 
				data-select_name="background-clip" 
				data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-clip]">';
			echo '<option disabled ' . selected( $this->value['background-clip'], '', false ) . '>' . esc_html__( 'Background clip..', 'customify' ) . '</option>';
			foreach ( $array as $k => $v ) {
				echo '<option value="' . $k . '"' . selected( $this->value['background-clip'], $k, false ) . '>' . $v . '</option>';
			}
			echo '</select>';
		}

		if ( $this->field['background-origin'] === true ) {
			$array = array(
				'inherit'     => 'Inherit',
				'border-box'  => 'Border Box',
				'content-box' => 'Content Box',
				'padding-box' => 'Padding Box',
			);

			if ( ! isset( $this->value['background-origin'] ) ) {
				$this->value['background-origin'] = '';
			}

			echo '<select id="' . $this->id . '-origin-select" 
				name="_customize-input-' . esc_attr( $this->setting->id ) . '[background-origin]" 
				class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" 
				data-select_name="background-origin" 
				data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-origin]">';
			echo '<option disabled ' . selected( $this->value['background-origin'], '', false ) . '>' . esc_html__( 'Background origin..', 'customify' ) . '</option>';
			foreach ( $array as $k => $v ) {
				echo '<option value="' . $k . '"' . selected( $this->value['background-origin'], $k, false ) . '>' . $v . '</option>';
			}
			echo '</select>';
		}

		if ( $this->field['background-size'] === true ) {
			$array = array(
				'inherit' => 'Inherit',
				'cover'   => 'Cover',
				'contain' => 'Contain',
			);

			if ( ! isset( $this->value['background-size'] ) ) {
				$this->value['background-size'] = '';
			}

			echo '<select id="' . $this->id . '-size-select" 
				name="_customize-input-' . esc_attr( $this->setting->id ) . '[background-size]" 
				class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" 
				data-select_name="background-size" 
				data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-size]">';
			echo '<option disabled ' . selected( $this->value['background-size'], '', false ) . '>' . esc_html__( 'Background size..', 'customify' ) . '</option>';
			foreach ( $array as $k => $v ) {
				echo '<option value="' . $k . '"' . selected( $this->value['background-size'], $k, false ) . '>' . $v . '</option>';
			}
			echo '</select>';
		}

		if ( $this->field['background-attachment'] === true ) {
			$array = array(
				'fixed'   => 'Fixed',
				'scroll'  => 'Scroll',
				'inherit' => 'Inherit',
			);

			if ( ! isset( $this->value['background-attachment'] ) ) {
				$this->value['background-attachment'] = '';
			}

			echo '<select id="' . $this->id . '-attachment-select" name="_customize-input-' . esc_attr( $this->setting->id ) . '[background-attachment]" class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" data-select_name="background-attachment" data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-attachment]">';
			echo '<option disabled ' . selected( $this->value['background-attachment'], '', false ) . '>' . esc_html__( 'Background attachment..', 'customify' ) . '</option>';
			foreach ( $array as $k => $v ) {
				echo '<option value="' . $k . '"' . selected( $this->value['background-attachment'], $k, false ) . '>' . $v . '</option>';
			}
			echo '</select>';
		}

		if ( $this->field['background-position'] === true ) {
			$array = array(
				'left top'      => 'Left Top',
				'left center'   => 'Left center',
				'left bottom'   => 'Left Bottom',
				'center top'    => 'Center Top',
				'center center' => 'Center Center',
				'center bottom' => 'Center Bottom',
				'right top'     => 'Right Top',
				'right center'  => 'Right center',
				'right bottom'  => 'Right Bottom',
			);

			if ( ! isset( $this->value['background-position'] ) ) {
				$this->value['background-position'] = '';
			}

			echo '<select id="' . $this->id . '-position-select" 
				name="_customize-input-' . esc_attr( $this->setting->id ) . '[background-position]" 
				class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" 
				placeholder="Background position?"
				data-select_name="background-position" 
				data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-position]">';
			echo '<option disabled ' . selected( $this->value['background-position'], '', false ) . '>' . esc_html__( 'Background position..', 'customify' ) . '</option>';
			foreach ( $array as $k => $v ) {
				echo '<option value="' . $k . '"' . selected( $this->value['background-position'], $k, false ) . '>' . $v . '</option>';
			}
			echo '</select>';
		}
	}
}
