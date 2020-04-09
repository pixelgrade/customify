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
	<div class="button" id="reset_theme_mods"><?php esc_html_e( 'Reset Customizer Settings', 'customify' ); ?></div>
</div>
<br>
<div class="field-desc"><?php esc_html_e('Resets all the Customizer settings introduced by this plugin. It will NOT reset core Customizer settings or plugin settings.'); ?></div>
