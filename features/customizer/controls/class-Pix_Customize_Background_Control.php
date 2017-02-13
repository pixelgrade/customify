<?php

/**
 * Class Pix_Customize_Background_Control
 * A simple Color Control
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
	 *
	 * @since 3.4.0
	 */
	public function render_content() {

		// ensure some values
		$this->value = $this->value();
		$this->field = array_merge( $this->field, $this->field_defaults );

		if ( $this->field['background-image'] === true ) {

			// NO defaults for now
//			if ( empty( $this->value ) && ! empty( $this->field['default'] ) ) { // If there are standard values and value is empty
//				if ( is_array( $this->field['default'] ) ) {
//					if ( ! empty( $this->field['default']['media']['id'] ) ) {
//						$this->value['media']['id'] = $this->field['default']['media']['id'];
//					} else if ( ! empty( $this->field['default']['id'] ) ) {
//						$this->value['media']['id'] = $this->field['default']['id'];
//					}
//
//					if ( ! empty( $this->field['default']['url'] ) ) {
//						$this->value['background-image'] = $this->field['default']['url'];
//					} else if ( ! empty( $this->field['default']['media']['url'] ) ) {
//						$this->value['background-image'] = $this->field['default']['media']['url'];
//					} else if ( ! empty( $this->field['default']['background-image'] ) ) {
//						$this->value['background-image'] = $this->field['default']['background-image'];
//					}
//
//				} else {
//					if ( is_numeric( $this->field['default'] ) ) { // Check if it's an attachment ID
//						$this->value['media']['id'] = $this->field['default'];
//					} else { // Must be a URL
//						$this->value['background-image'] = $this->field['default'];
//					}
//				}
//			}

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

			$hide = 'hide ';

			if ( ( isset( $this->field['preview_media'] ) && $this->field['preview_media'] === false ) ) {
				$this->field['class'] .= " noPreview";
			}

			if ( ( ! empty( $this->field['background-image'] ) && $this->field['background-image'] === true ) || isset( $this->field['preview'] ) && $this->field['preview'] === false ) {
				$hide = '';
			}

			$placeholder = isset( $this->field['placeholder'] ) ? $this->field['placeholder'] : __( 'No media selected', 'customify' );


			if ( ! isset( $this->value['background-image'] ) ) {
				$this->value['background-image'] = '';
			}

			echo '<input placeholder="' . $placeholder . '" type="text" class="customify_background_input background-image ' . $hide . 'upload ' . $this->field['class'] . '" name="' . $this->label . '[background-image]" id="' . $this->manager->options_key . '[' . $this->id . '][background-image]" value="' . $this->value['background-image'] . '"  data-select_name="background-image" data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-image]"/>';
			echo '<input type="hidden" class="upload-id ' . $this->field['class'] . '" name="' . $this->manager->options_key . '[media][id]" id="' . $this->manager->options_key . '[' . $this->id . '][media][id]" value="' . $this->value['media']['id'] . '" />';
			echo '<input type="hidden" class="upload-height" name="' . $this->manager->options_key . '[media][height]" id="' . $this->manager->options_key . '[' . $this->id . '][media][height]" value="' . $this->value['media']['height'] . '" />';
			echo '<input type="hidden" class="upload-width" name="' . $this->manager->options_key . '[media][width]" id="' . $this->manager->options_key . '[' . $this->id . '][media][width]" value="' . $this->value['media']['width'] . '" />';
			echo '<input type="hidden" class="upload-thumbnail" name="' . $this->manager->options_key . '[media][thumbnail]" id="' . $this->manager->options_key . '[media][thumbnail]" value="' . $this->value['media']['thumbnail'] . '" />';

			//Preview
			$hide = '';

			if ( ( isset( $this->field['preview_media'] ) && $this->field['preview_media'] === false ) || empty( $this->value['background-image'] ) ) {
				$hide = 'hide ';
			}

			if ( empty( $this->value['media']['thumbnail'] ) && ! empty( $this->value['background-image'] ) ) { // Just in case
				if ( ! empty( $this->value['media']['id'] ) ) {
					$image                             = wp_get_attachment_image_src( $this->value['media']['id'], array( 150 ) );
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
			echo '<span class="button background_upload_button" id="' . $this->id . '-media" data-setting_id="' . $this->setting->id . '" >' . __( 'Upload', 'customify' ) . '</span>';

			$hide = '';
			if ( empty( $this->value['background-image'] ) || $this->value['background-image'] == '' ) {
				$hide = ' hide';
			}

			echo '<span class="button remove-image' . $hide . '" id="reset_' . $this->id . '" rel="' . $this->id . '">' . __( 'Remove', 'customify' ) . '</span>';

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

			echo '<select id="' . $this->id . '-repeat-select" name="' . $this->setting->id . '[background-repeat]" class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" data-select_name="background-repeat" data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-repeat]">';
			echo '<option></option>';
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
			echo '<select id="' . $this->id . '-repeat-select" name="' . $this->setting->id . '[background-clip]" class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" data-select_name="background-clip" data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-clip]">';
			echo '<option></option>';

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
			echo '<select id="' . $this->id . '-repeat-select" name="' . $this->setting->id . '[background-origin]" class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" data-select_name="background-origin" data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-origin]">';
			echo '<option></option>';

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
			echo '<select id="' . $this->id . '-repeat-select" name="' . $this->label . '[background-size]" class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" data-select_name="background-size" data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-size]">';
			echo '<option></option>';

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
			echo '<select id="' . $this->id . '-attachment-select" name="' . $this->setting->id . '[background-attachment]" class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" data-select_name="background-attachment" data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-attachment]">';
			echo '<option></option>';
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
			echo '<select id="' . $this->id . '-position-select" name="' . $this->setting->id . '[background-position]" class="customify_background_select ' . $this->field['class'] . ' ' . $hide . '" data-select_name="background-position" data-customize-setting-link="' . esc_attr( $this->setting->id ) . '[background-position]">';
			echo '<option></option>';

			foreach ( $array as $k => $v ) {
				echo '<option value="' . $k . '"' . selected( $this->value['background-position'], $k, false ) . '>' . $v . '</option>';
			}
			echo '</select>';
		}
	}

	static function css_output() {

	}
}