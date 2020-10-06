<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$currentTypeAlias  = 'date';
$currentFieldAlias = isset($currentFieldAlias) ? $currentFieldAlias : null;

if ($fieldsData)
{
	foreach ($fieldsData as $fieldData)
	{
		if ($fieldData->field_alias == $currentFieldAlias)
		{
			?>
			<div class="redshopb-field redshopb-field-<?php echo $currentFieldAlias; ?>">
				<?php echo HTMLHelper::_('date', $fieldData->value, Text::_('DATE_FORMAT_LC4'), null); ?>
			</div>
		<?php
		}
	}
}
