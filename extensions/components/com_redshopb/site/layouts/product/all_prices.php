<?php
/**
 * @package     Aesir.E-Commerce.Backend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$formName = $displayData['formName'];
$state    = $displayData['state'];
$return   = $displayData['return'];
$url      = $displayData['action'];

if ($return)
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

$items         = $displayData['items'];
$productId     = $displayData['productId'];
$productItemId = isset($displayData['productItemId']) ? $displayData['productItemId'] : false;
$pagination    = $displayData['pagination'];
$canReadOnly   = RedshopbEntityProduct::getInstance($productId)->canReadOnly();
$showToolbar   = isset($displayData['showToolbar']) && !$canReadOnly ? $displayData['showToolbar'] : false;

$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');

$searchToolsOptions = array(
	'view'    => (object) array(
		'filterForm'    => $displayData['filter_form'],
		'activeFilters' => $displayData['activeFilters']
	),
	'options' => array(
		'filterButton'        => true,
		'searchFieldSelector' => '#filter_search_all_prices',
		'orderFieldSelector'  => '#list_fullordering',
		'searchField'         => 'search_all_prices',
		'limitFieldSelector'  => '#list_all_prices_limit',
		'activeOrder'         => $listOrder,
		'activeDirection'     => $listDirn,
		'formSelector'        => ('#' . $formName),
		'filtersHidden'       => (bool) empty($displayData['activeFilters'])
	)
);

$salesTypes = array(
	'customer_price'       => Text::_('COM_REDSHOPB_PRODUCT_PRICE_DEBTOR'),
	'all_customers'        => Text::_('COM_REDSHOPB_PRODUCT_PRICE_ALL_DEBTOR'),
	'customer_price_group' => Text::_('COM_REDSHOPB_PRODUCT_PRICE_DEBTOR_GROUP'),
	'campaign'             => Text::_('COM_REDSHOPB_PRODUCT_PRICE_CAMPAIGN')
);

$itemTypes = array(
	'product_item' => Text::_('COM_REDSHOPB_PRODUCT_ITEM_FORM_TITLE'),
	'product'      => Text::_('COM_REDSHOPB_PRODUCT')
);

$nullDate = '0000-00-00 00:00:00';
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('#<?php echo $formName; ?>').searchtools(
				<?php echo json_encode($searchToolsOptions['options']); ?>
			);
		});
	})(jQuery);
</script>
<h4>
	<?php echo Text::_('COM_REDSHOPB_PRICES'); ?>
</h4>
<form action="<?php echo $action; ?>" name="<?php echo $formName ?>" class="adminForm" id="<?php echo $formName ?>"
	  method="post">
	<?php if ($showToolbar): ?>
		<?php echo RedshopbLayoutHelper::render('product.all_prices.toolbar', $displayData); ?>
	<?php endif; ?>
	<?php echo RedshopbLayoutHelper::render('searchtools.default', $searchToolsOptions); ?>
	<hr/>
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled"
			   id="allPricesList">
			<thead>
			<tr>
				<th>
					<input type="checkbox" name="checkall-toggle" value=""
						   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo Text::_('COM_REDSHOPB_TYPE'); ?>
				</th>
				<th data-toggle="true">
					<?php echo Text::_('COM_REDSHOPB_SKU'); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo Text::_('COM_REDSHOPB_SALES_TYPE'); ?>
				</th>
				<th data-hide="phone">
					<?php echo Text::_('COM_REDSHOPB_DISCOUNT_SALES_NAME'); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_START', 'pp.starting_date', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_END', 'pp.ending_date', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_VOLUME', 'pp.quantity_min', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRICE', 'pp.price', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 'pp.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($items as $i => $item): ?>
				<tr>
					<?php $canChange  = RedshopbHelperACL::getPermission('manage', 'product', array('edit.state'), true); ?>
					<?php $canEdit    = RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true); ?>
					<?php $canCheckin = $canEdit; ?>
					<?php $salesType  = $salesTypes[$item->sales_type]; ?>
					<?php $itemType   = $itemTypes[$item->type]; ?>
					<?php $startDate  = ($item->starting_date == $nullDate) ? ' - ' : HTMLHelper::_('date', $item->starting_date, Text::_('DATE_FORMAT_LC4'), null); ?>
					<?php $endDate    = ($item->ending_date == $nullDate) ? ' - ' : HTMLHelper::_('date', $item->ending_date, Text::_('DATE_FORMAT_LC4'), null); ?>

					<td>
						<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
					</td>
					<td>
						<?php echo $itemType; ?>
					</td>
					<td>
						<?php if ($canEdit): ?>
							<?php $itemUrl = 'index.php?option=com_redshopb&task=all_price.edit&id=' . $item->id . '&product_id=' . $productId; ?>

							<?php if ($return): ?>
								<?php $itemUrl .= '&return=' . $return; ?>
							<?php endif; ?>
							<a href="<?php echo RedshopbRoute::_($itemUrl); ?>"><?php echo $item->sku; ?></a>
						<?php else: ?>
							<?php echo $item->sku; ?>
						<?php endif; ?>

					</td>
					<td>
						<?php echo $salesType; ?>
					</td>
					<td>
						<?php echo $item->sales_name; ?>
					</td>
					<td>
						<?php echo $startDate; ?>
					</td>
					<td>
						<?php echo $endDate; ?>
					</td>
					<td>
						<?php if ($item->is_multiple): ?>
							<?php echo '* ' . $item->quantity_min ?>
						<?php elseif ($item->quantity_min && $item->quantity_max): ?>
							<?php echo $item->quantity_min . ' - ' . $item->quantity_max ?>
						<?php elseif ($item->quantity_min): ?>
							<?php echo '> ' . $item->quantity_min ?>
						<?php elseif ($item->quantity_max): ?>
							<?php echo '< ' . $item->quantity_max ?>
						<?php endif; ?>
					</td>
					<td>
						<?php echo RedshopbHelperProduct::getProductFormattedPrice($item->price, $item->alpha3); ?>
					</td>
					<td>
						<?php echo $item->id; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="layout_filter[product_id]" value="<?php echo $productId;
?>">
	<?php // @todo refactor this so we are not using jform{var] in list views ?>
	<input type="hidden" name="jform[product_id]" value="<?php echo $productId; ?>">
	<input type="hidden" name="jform[product_item_id]" value="<?php echo $productItemId; ?>">

	<input type="hidden" name="product_id" value="<?php echo $productId; ?>">
	<input type="hidden" name="product_item_id" value="<?php echo $productItemId; ?>">
	<input type="hidden" name="boxchecked" value="0">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
