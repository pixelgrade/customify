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

$attrs = array
(
	'name' => $name,
	'id'   => $idname,
);

// group show

if ($field->hasmeta('show_group')) {
	$attrs['data-show_group'] =  $field->getmeta('show_group');
}

if ($field->hasmeta('display_option')) {
	$attrs['data-display_option'] =  $field->getmeta('display_option');
}?>
<div class="select">
	<label id="<?php echo $name ?>"><?php echo $label ?></label>
	<select <?php echo $field->htmlattributes( $attrs ) ?>>
		<?php foreach ( $this->getmeta( 'options', array() ) as $key => $label ): ?>
			<option <?php if ($key == $selected): ?>selected<?php endif; ?>
			        value="<?php echo $key ?>">
				<?php echo $label ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>
