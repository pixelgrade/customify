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
<div class="postbox">
	<div class="handlediv" title="Click to toggle"><br></div>
	<h3 class="hndle"><span><?php echo $label ?></span></h3>

	<div class="inside">
		<?php foreach ($field->getmeta('options', array()) as $fieldname => $fieldconfig):

			$field = $form->field($fieldname, $fieldconfig);
			// we set the fields to default to inline
			$field->ensuremeta('rendering', 'blocks');
			// export field meta for processing
			$fielddesc = $field->getmeta('desc', null);
			$show_group = $field->getmeta('show_group', null);  ?>

			<div class="row" <?php if ( $fieldconfig['type'] == 'group' ) echo 'id="' . $fieldname . '"'; ?>>
				<?php if ( ! empty($fielddesc)): ?>
					<div class="field-desc"><?php echo $fielddesc ?></div>
				<?php endif;
				echo $field->render();
				if ( ! empty($fieldnote)): ?>
					<span class="note"><?php echo $fieldnote ?></span>
				<?php endif; ?>
			</div>

		<?php endforeach; ?>
	</div>
</div>