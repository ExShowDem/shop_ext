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
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;

$currentTypeAlias  = 'checkbox';
$currentFieldAlias = isset($currentFieldAlias) ? $currentFieldAlias : null;
HTMLHelper::_('bootstrap.tooltip');

$fieldImageWidth   = !isset($fieldImageWidth) ? 50 : $fieldImageWidth;
$fieldImageHeight  = !isset($fieldImageHeight) ? 50 : $fieldImageHeight;
$fieldImageQuality = !isset($fieldImageQuality) ? 100 : $fieldImageQuality;
$fieldImageCrop    = !isset($fieldImageCrop) ? 0 : $fieldImageCrop;

if (empty($fieldsData))
{
	return;
}

foreach ($fieldsData as $fieldData):
	$image = null;

	if ($fieldData->field_alias != $currentFieldAlias)
	{
		continue;
	}

	$params    = new Registry($fieldData->field_value_params);
	$innerHtml = $fieldData->value;

	if ($params->get('image'))
	{
		$image = RedshopbHelperThumbnail::originalToResize(
			$params->get('image'),
			$fieldImageWidth,
			$fieldImageHeight,
			$fieldImageQuality,
			$fieldImageCrop,
			'field_values'
		);

		$innerHtml = '<img src="' . $image . '" class="hasTooltip redshopb-field-image" title="' . HTMLHelper::tooltipText($fieldData->value) . '"/>';
	}

	$imageLink = $params->get('imageLink');

	if ($imageLink)
	{
		if (Uri::isInternal($imageLink))
		{
			$imageLink = Route::_($imageLink);
		}

		$innerHtml = '<a href="' . $imageLink . '">' . $innerHtml . '</a>';
	}
	?>
	<div class="redshopb-field redshopb-field-<?php echo $currentFieldAlias; ?>">
		<?php echo $innerHtml;?>
	</div>
<?php endforeach;
