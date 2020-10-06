<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_REDCORE') or die;

$fields = $displayData['fields'];
?>
<?php foreach ($fields as $field): ?>
	<tr id="field-<?php echo $field->field_id; ?>">
		<td>
			<?php echo $field->field_name; ?>
		</td>
		<td><?php echo $field->field_type_name; ?></td>

		<td><?php echo $field->type_code;?></td>
		<td>
			<a href="javascript:void(0)" class="btn btn-small btn-sm btn-success" onclick="modalAddField(event);" data-id="<?php echo (int) $field->field_id;?>">
				<i class="icon-plus"></i>
			</a>
			<input type="hidden" name="jform[fields][<?php echo $field->field_id;?>]" value="<?php echo $field->field_id;?>" />
		</td>
	</tr>
<?php endforeach;
