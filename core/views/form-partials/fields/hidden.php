<?php defined('ABSPATH') or die;
	/* @var PixCustomizerFormField $field */
	/* @var PixCustomizerForm $form */
	/* @var mixed $default */
	/* @var string $name */
	/* @var string $idname */
	/* @var string $label */
	/* @var string $desc */
	/* @var string $rendering */

	isset($type) or $type = 'hidden';

	$attrs = array
		(
			'name' => $name,
			'id' => $idname,
			'type' => 'hidden',
			'value' => $form->autovalue($name)
		);
?>

<input <?php echo $field->htmlattributes($attrs) ?>/>
