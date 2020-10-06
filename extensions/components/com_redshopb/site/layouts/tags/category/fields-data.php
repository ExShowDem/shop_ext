<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$empty = true;

$defaultText = array(Text::_('JNO'), Text::_('JYES'));

if ($fieldsData):
	$groupedFieldsData = RedshopbHelperField::getFieldDataFieldGroups($fieldsData);

	foreach ($groupedFieldsData as $groupName => $groupedFieldData):
		if ($empty) : ?>
			<table class="table table-striped CategoryFieldTable">
		<?php endif; ?>
		<tr>
			<?php if ($groupName): ?>
				<th class="categoryFieldGroup text-center" colspan="2">
					<?php echo $groupName; ?>
				</th>
			<?php endif; ?>
		</tr>
		<?php foreach ($groupedFieldData as $fieldData):
			if (!in_array($fieldData->type_alias, array('documents', 'videos', 'field-images', 'files'))):
				if ($fieldData->type_alias == 'radioyes' && $fieldData->value == 0):?>
					<?php continue; ?>
				<?php endif; ?>

					<?php if (isset($defaultText[$fieldData->value]) || !in_array($fieldData->type_alias, array('radioboolean', 'radioyes'))) : ?>
			<tr>
				<td class="categoryFieldTitle"><?php echo ($fieldData->title ? $fieldData->title : $fieldData->name); ?></td>
				<td class="categoryFieldValue">
					<?php echo RedshopbEntityField::getFloatFieldValue($fieldData); ?>
				</td>
			</tr>
			<?php
					endif;
			endif;
		endforeach;
		$empty = false;
	endforeach;

	if (!$empty) : ?>
		</table>
	<?php endif;
endif;
