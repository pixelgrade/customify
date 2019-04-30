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

$option = get_option( 'pixcustomify_settings' );
$attrs = array(
	'type' => 'checkbox',
);
?>
<div class="multicheckbox">
	<?php
	foreach ( $this->getmeta( 'options', array() ) as $value => $label ) {
		$attrs['name'] = $name . '[' . $value . ']';

		if ( is_array($selected) && array_key_exists( $value, $selected) ) {
			$attrs['checked'] = 'checked';
		} else {
			unset($attrs['checked']);
		} ?>
		<fieldset class="multicheckbox_option">
			<input <?php echo $field->htmlattributes( $attrs ) ?>>
			<label id="<?php echo $value ?>"><?php echo $label ?></label>
		</fieldset>
	<?php } ?>
</div>
