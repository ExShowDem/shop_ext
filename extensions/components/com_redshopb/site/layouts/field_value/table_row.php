<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data        = $displayData;
$defaultText = array(Text::_('JNo'), Text::_('JYes'));

$class = '';

if (isset($data->isNew))
{
	$class = ' class="success"';
}
?>

<tr data-id="<?php echo $data->id; ?>"
	data-default="<?php echo (int) $data->default; ?>"
	data-value="<?php echo $this->escape($data->value); ?>"
	data-name="<?php echo $this->escape($data->name); ?>"
	<?php echo $class;?>>
	<td class="order nowrap center hidden-phone">
		<span class="sortable-handler">
			<span class="icon-move"></span>
		</span>
		<input type="text" style="display:none" name="order[]" value="<?php echo $data->ordering; ?>" />
		<input type="checkbox" style="display:none" name="cid[]" value="<?php echo $data->id; ?>" />
	</td>
	<td>
		<a href="javascript:void(0);"
		   onclick="redSHOPB.fields.modalEdit(event);">
			<?php echo $this->escape($data->name); ?>
		</a>
	</td>
	<td>
		<?php echo $this->escape($data->value); ?>
	</td>
	<td data-name="default">
		<?php echo $defaultText[(int) $data->default];?></td>
	<td>
		<a href="javascript:void(0)"
		   class="btn btn-small btn-sm btn-danger"
		   onclick="redSHOPB.fields.modalDelete(event);">
			<i class="icon-remove"></i>
		</a>
	</td>
</tr>
