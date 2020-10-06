<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Shop.Pages.Pagination
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

extract($displayData);

if (!isset($enableDirectLinks) || !$enableDirectLinks)
{
	return;
}

// If client request from ajax, then client support javascript and direct links not needed, it helpful just for search crawlers
if ($numberOfPages <= 1 || RedshopbHelperAjax::isAjaxRequest())
{
	return '';
}

/**
 * @var   int  $range  Number of pages to show around current page, from start and till end page
 */
$range = isset($displayData['range']) ? $displayData['range'] : 1;

/**
 * @var   int  $paginationButtons  Number of buttons to display inside pagination list (including "..." fields)
 */
$paginationButtons = isset($displayData['paginationButtons']) ? $displayData['paginationButtons'] : 8;

if ($numberOfPages > $paginationButtons)
{
	$start  = 1;
	$end    = $numberOfPages;
	$middle = ceil($numberOfPages / 2);

	$startZone    = range($start, $start + $range, 1);
	$middleZone   = range($middle - $range, $middle + $range, 1);
	$endZone      = range($end - $range, $end, 1);
	$pageZone     = range($currentPage - $range, $currentPage + $range, 1);
	$displayZones = array_merge($startZone, $middleZone, $endZone, $pageZone);
}
?>
<ul class="pagination-list directPaginationLinks">
	<?php
	$uri                 = Uri::getInstance();
	$app                 = Factory::getApplication();
	$layout              = $app->input->getCmd('layout', $app->getUserState('shop.layout', ''));
	$id                  = $app->input->getInt('id', 0);
	$itemKey             = $layout . '_' . $id;
	$paginationVariables = $app->getUserState('shop.pagination.variables.' . $itemKey, array());

	for ($i = 1; $i <= $numberOfPages; $i++)
	{
		if ($currentPage != $i && (!empty($displayZones) ? in_array($i, $displayZones) : true))
		{
			$cloneUrl = clone $uri;
			$cloneUrl->setVar('page', $i);

			if (!empty($paginationVariables))
			{
				foreach ($paginationVariables as $paginationVariableName => $paginationVariableNameValue)
				{
					$cloneUrl->setVar($paginationVariableName, $paginationVariableNameValue);
				}
			}
			?>
			<li>
				<a href="<?php echo $cloneUrl->toString() ?>">
					<?php echo $i; ?>
				</a>
			</li>
			<?php
		}
	}
	?>
</ul>
