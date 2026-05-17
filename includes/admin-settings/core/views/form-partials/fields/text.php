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

	isset($type) or $type = 'text';

	$attrs = array
		(
			'name' => $name,
			'id' => $idname,
			'type' => 'text',
			'value' => $form->autovalue($name)
		);
?>

<?php if ($rendering == 'inline'): ?>
	<input <?php echo $field->htmlattributes($attrs) ?>/>
<?php elseif ($rendering == 'blocks'):  ?>
<div class="text">
	<label id="<?php echo $name ?>"><?php echo $label ?></label>
	<input <?php echo $field->htmlattributes($attrs) ?> />
	<span><?php echo $desc ?></span>
</div>
<?php else: # ?>
	<div>
		<p><?php echo $desc ?></p>
		<label id="<?php echo $name ?>">
			<?php echo $label ?>
			<input <?php echo $field->htmlattributes($attrs) ?>/>
		</label>
	</div>
<?php endif; ?>
