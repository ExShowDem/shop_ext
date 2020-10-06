<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();

$formName   = $displayData['formName'];
$state      = $displayData['state'];
$return     = $displayData['return'];
$action     = $displayData['action'];
$items      = $displayData['items'];
$productId  = $displayData['productId'];
$pagination = $displayData['pagination'];

$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');

$searchToolsOptions = array(
	"searchFieldSelector" => "#filter_search_all_discounts",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_all_discounts",
	"limitFieldSelector" => "#list_all_discounts_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($displayData['activeFilters'])
);
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions); ?>
			);
		});
	})(jQuery);
</script>
<h4><?php echo Text::_('COM_REDSHOPB_DISCOUNT_LIST_TITLE') ?></h4>
<form action="<?php echo $action; ?>" name="<?php echo $formName ?>" class="adminForm" id="<?php echo $formName ?>" method="post">
	<div>
		<input type="hidden" name="task" value="product.saveModelState">
		<?php if ($return) : ?>
			<input type="hidden" name="return" value="<?php echo $return ?>">
		<?php endif; ?>
		<input type="hidden" name="filter[product_id]" value="<?php echo $productId; ?>">
		<input type="hidden" name="from_product" value="1">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	<?php
	echo RedshopbLayoutHelper::render(
		'searchtools.default',
		array(
			'view' => (object) array(
					'filterForm' => $displayData['filter_form'],
					'activeFilters' => $displayData['activeFilters']
				),
			'options' => $searchToolsOptions
		)
	);
	?>
	<hr/>
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>

		<?php return;
	endif; ?>
	<table class="table table-striped footable js-redshopb-footable redshopb-footable">
		<thead>
			<tr>
				<th><?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_DISCOUNT_TYPE', 'pd.type', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?></th>
				<th data-hide="phone"><?php echo Text::_('COM_REDSHOPB_DISCOUNT_TYPE_NAME'); ?></th>
				<th data-hide="phone"><?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_SALES_TYPE', 'pd.sales_type', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?></th>
				<th><?php echo Text::_('COM_REDSHOPB_DISCOUNT_SALES_NAME'); ?></th>
				<th data-hide="phone"><?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STARTTIME', 'pd.starting_date', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?></th>
				<th data-hide="phone"><?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ENDTIME', 'pd.ending_date', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?></th>
				<th><?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PERCENT', 'pd.percent', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?></th>
				<th data-hide="phone"><?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 'pd.id', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?></th>
			</tr>
		</thead>
		<?php foreach ($items as $item): ?>
		<tr>
			<td><?php echo ucwords($this->escape(RText::format($item->type, array('_' => ' ')))); ?></td>
			<td><?php echo $item->type_name; ?></td>
			<td><?php echo ucwords($this->escape(RText::format($item->sales_type, array('_' => ' ')))); ?></td>
			<td><?php echo $item->sales_name; ?></td>
			<td>
				<?php echo ($item->starting_date !== '0000-00-00 00:00:00') ?
					HTMLHelper::_('date', $item->starting_date, Text::_('DATE_FORMAT_LC4'), false) : ' - '; ?>
			</td>
			<td>
				<?php echo ($item->ending_date !== '0000-00-00 00:00:00') ?
					HTMLHelper::_('date', $item->ending_date, Text::_('DATE_FORMAT_LC4'), false) : ' - '; ?>
			</td>
			<td><?php echo $item->percent; ?></td>
			<td><?php echo $item->id; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
</form>
