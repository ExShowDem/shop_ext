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

extract($displayData);

$inputName = 'filter[' . $filter->id . ']';

if ($multiple)
{
	$inputName .= '[]';
}
?>

<div class="redshopb-filter-dropdown" id="redshopbFilterDropdownWrapper-<?php echo $filter->id ?>">
	<select name="<?php echo $inputName ?>" class="input input-xlarge"
		<?php if (!empty($jsCallback)): ?>
		onchange="javascript:<?php echo $jsCallback ?>"
		<?php endif; ?>

		<?php if ($multiple): ?>
		multiple="true"
		<?php endif; ?>>
		<option value="">-- <?php echo Text::_('JSELECT') . ' ' . $filter->title ?> --</option>
		<?php if (!empty($filterValues)): ?>
			<?php foreach ($filterValues as $filterValue):
				if (is_object($filterValue)) :
					$fData  = isset($filterValue->name) ? $filterValue->name : $filterValue->data;
					$fValue = $filterValue->identifier;
				else:
					$fData  = $filterValue;
					$fValue = $filterValue;
				endif; ?>
				<option
					value="<?php echo htmlentities($fValue) ?>"
					<?php if (!empty($value) && (is_array($value) ? in_array($fValue, $value) : $fValue == $value)): ?>
						selected="selected"
					<?php endif; ?>
					><?php echo $fData ?>
					<?php if (isset($filterValue->count)) : ?>
					&nbsp;(<?php echo $filterValue->count ?>)
					<?php endif; ?>
				</option>
			<?php endforeach; ?>
		<?php endif; ?>
	</select>
	<?php if ($multiple): ?>
	<input class="hidden" type="checkbox" name="filter[<?php echo $filter->id ?>][]" checked="checked" value="" />
	<?php endif; ?>
</div>
