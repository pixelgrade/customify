<?php
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.Security.EscapeOutput.ExceptionNotEscaped,WordPress.Security.EscapeOutput.UnsafePrintingFunction -- Legacy Customify view output contains trusted config HTML, dynamic CSS/JS, or WordPress Customizer binding attributes.
defined('ABSPATH') or die;
	/* @var $form PixCustomifyForm */
	/* @var $conf PixCustomifyMeta */

	/* @var $f PixCustomifyForm */
	$f = &$form;
?>

<?php foreach ($conf->get('fields', array()) as $fieldname): ?>

	<?php echo $f->field($fieldname)
		->addmeta('special_sekrit_property', '!!')
		->render() ?>

<?php endforeach; ?>
