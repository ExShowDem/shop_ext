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

$formName = (isset($displayData['formName'])) ? $displayData['formName'] : 'adminForm';
$state    = $displayData['state'];
$url      = isset($displayData['action']) ? $displayData['action'] : 'index.php?option=com_redshopb&view=product_compositions';

$return = isset($displayData['return']) ? $displayData['return'] : false;

if ($return)
{
	$url .= '&return=' . $return;
}

$action = RedshopbRoute::_($url);

$productId  = $displayData['productId'];
$items      = $displayData['items'];
$pagination = $displayData['pagination'];

$canReadOnly = RedshopbEntityProduct::getInstance($productId)->canReadOnly();
$showToolbar = isset($displayData['showToolbar']) && !$canReadOnly ? $displayData['showToolbar'] : false;

$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');

$searchToolsOptions = array(
	'view' => (object) array(
		'filterForm' => $displayData['filter_form'],
		'activeFilters' => $displayData['activeFilters']
	),
	'options' => array
		(
			'filterButton' => false,
			'searchFieldSelector' => '#filter_search_product_compositions',
			'orderFieldSelector' => '#list_fullordering',
			'searchField' => 'search_product_compositions',
			'limitFieldSelector' => '#list_product_compositions_limit',
			'activeOrder' => $listOrder,
			'activeDirection' => $listDirn,
			'formSelector' => ('#' . $formName),
			'filtersHidden' => (bool) empty($displayData['activeFilters'])
		)
	);

$searchTools = RedshopbLayoutHelper::render('searchtools.default', $searchToolsOptions);
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
<h4><?php echo Text::_('COM_REDSHOPB_PRODUCT_COMPOSITION_LIST_TITLE') ?></h4>
<form action="<?php echo $action; ?>" name="<?php echo $formName ?>" class="adminForm" id="<?php echo $formName ?>" method="post">

	<?php if ($showToolbar):?>
		<?php echo RedshopbLayoutHelper::render('product.compositions.toolbar', $displayData);?>
	<?php endif;?>

	<?php echo $searchTools?>
	<hr/>
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else: ?>
		<table class="table table-striped table-hover footable js-redshopb-footable productCompositionsList redshopb-footable toggle-circle-filled" id="productCompositionsList">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="1%" class="nowrap" data-hide="phone, tablet">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 'pc.id', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap center" data-toggle="true">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT', 'product_name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap center" data-toggle="true">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_COMPOSITION_FLAT_ATTRIBUTE_LABEL', 'product_attribute_value_name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap center" data-toggle="true">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_COMPOSITION_TYPE', 'pc.type', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
				</th>
				<th class="nowrap" data-hide="phone, tablet">
					<?php echo Text::_('COM_REDSHOPB_PRODUCT_COMPOSITION_QUALITY'); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="6">
					<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($items as $i => $item): ?>
				<tr>
					<td>
						<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
					</td>
					<td>
						<?php echo $item->id; ?>
					</td>
					<td>
						<?php echo $this->escape($item->product_name); ?>
					</td>
					<td>
						<?php echo $this->escape($item->product_attribute_value_name); ?>
					</td>
					<td>
						<?php echo $this->escape($item->type); ?>
					</td>
					<td>
						<?php echo $this->escape($item->quality); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ($productId): ?>
		<input type="hidden" name="filter[product_id]" value="<?php echo $productId; ?>">
	<?php endif; ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="boxchecked" value="0">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
