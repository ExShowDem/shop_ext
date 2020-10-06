<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;


use Joomla\Registry\Registry;

$empty = true;

if ($fieldsData):
	foreach ($fieldsData as $fieldData):
		if ($fieldData->type_alias == 'files'):
			if ($empty) : ?>
				<table class="table table-striped ProductFieldTable">
			<?php endif;?>
			<tr>
				<td class="productFieldTitle"><?php echo ($fieldData->title ? $fieldData->title : $fieldData->name); ?></td>
				<td class="productFieldValue"><?php
					$params = new Registry($fieldData->field_data_params);
					$href   = $params->get('external_url', null);
					$title  = $params->get('title', null);

				if (!$title)
					{
					$title = $fieldData->description;
				}

				if (!$href)
					{
					$scope = RInflector::pluralize($fieldData->scope);
					$href  = RedshopbHelperMedia::getFullMediaPath($params->get('internal_url', ''), $scope, 'files');
				}
					?>
					<div class="redshopb-field redshopb-field-files">
						<a title="<?php echo $fieldData->description; ?>" href="<?php echo $href; ?>" target="_blank">
							<i class="icon icon-file"></i> <?php echo $fieldData->value; ?>
						</a>
					</div>
				</td>
			</tr>
			<?php
			$empty = false;
		endif;
	endforeach;

	if (!$empty) : ?>
		</table>
	<?php endif;
endif;
