<?php defined('ABSPATH') or die;
	/* @var PixCustomifyFormField $field */
	/* @var PixCustomifyForm $form */
	/* @var mixed $default */
	/* @var string $name */
	/* @var string $idname */
	/* @var string $label */
	/* @var string $desc */
	/* @var string $rendering */
?>

<tr valign="top">
	<th scope="row">
		<?php echo $label ?>
	</th>
	<td>
		<fieldset>

			<legend class="screen-reader-text">
				<span><?php echo $label ?></span>
			</legend>

			<?php foreach ($field->getmeta('options', array()) as $fieldname => $conf): ?>
				<?php echo $form->field($fieldname, $conf)->render() ?>
				<br/>
			<?php endforeach; ?>

			<?php if ($field->hasmeta('note')): ?>
				<small>
					<em>(<?php echo $field->getmeta('note') ?>)</em>
				</small>
			<?php endif; ?>

		</fieldset>
	</td>
</tr>
