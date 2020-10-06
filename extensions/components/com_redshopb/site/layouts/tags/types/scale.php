<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$currentTypeAlias  = 'scale';
$currentFieldAlias = isset($currentFieldAlias) ? $currentFieldAlias : null;

if ($fieldsData)
{
	foreach ($fieldsData as $fieldData)
	{
		if ($fieldData->field_alias == $currentFieldAlias)
		{
			?>
			<div class="redshopb-field redshopb-field-<?php echo $currentFieldAlias; ?>">
				<?php echo $fieldData->value; ?>
			</div>
			<?php
		}
	}
}
