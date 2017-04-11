<?php defined( 'ABSPATH' ) or die;
/* @var PixCustomifyFormField $field */
/* @var PixCustomifyForm $form */
/* @var mixed $default */
/* @var string $name */
/* @var string $idname */
/* @var string $label */
/* @var string $desc */
/* @var string $rendering */

// [!!] the counter field needs to be able to work inside other fields; if
// the field is in another field it will have a null label

$selected = $form->autovalue( $name, $default );

$config = apply_filters('customify_filter_fields', array() );

$key = $config[ 'opt-name' ];

$mods = get_theme_mods();

$option = get_option( 'pixcustomify_settings' );

$attrs = array(
	'type' => 'checkbox',
); ?>
<div class="reset_customify_theme_mod">
	<div class="button" id="reset_theme_mods"><?php esc_html_e( 'Reset theme mod', 'customify' ); ?></div>
	<script>
		(function ($) {
			$(document).ready(function () {
				$('#reset_theme_mods').on('click', function () {
					var confirm = window.confirm('Are you sure?');

					if ( ! confirm ) {
						return false;
					}

					$.ajax({
						url: customify_settings.wp_rest.root + 'customfiy/v1/delete_theme_mod',
						method: 'POST',
						beforeSend: function (xhr) {
							xhr.setRequestHeader('X-WP-Nonce', customify_settings.wp_rest.nonce);
						},
						data: {
							'customify_settings_nonce': customify_settings.wp_rest.customify_settings_nonce
						}
					}).done(function (response) {
						if ( response.success ) {
							alert( response.data );
						}
					}).error(function (e) {
						console.log(e);
					});
				});
			});

		})(jQuery)
	</script>
</div>
