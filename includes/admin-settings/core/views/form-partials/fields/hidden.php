<?php
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.Security.EscapeOutput.ExceptionNotEscaped,WordPress.Security.EscapeOutput.UnsafePrintingFunction -- Legacy Customify view output contains trusted config HTML, dynamic CSS/JS, or WordPress Customizer binding attributes.
defined('ABSPATH') or die;
	/* @var PixCustomifyFormField $field */
	/* @var PixCustomifyForm $form */
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
