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
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$formName = $displayData['formName'];
$state    = $displayData['state'];
$return   = $displayData['return'];

$url = $displayData['action'];

if ($return)
{
	$url .= '&return=' . $return;
}

$action      = RedshopbRoute::_($url);
$items       = $displayData['items'];
$productId   = $displayData['productId'];
$pagination  = $displayData['pagination'];
$canReadOnly = RedshopbEntityProduct::getInstance($productId)->canReadOnly();
$showToolbar = isset($displayData['showToolbar']) && !$canReadOnly ? $displayData['showToolbar'] : false;

$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');

$searchToolsOptions = array(
	'view' => (object) array(
		'filterForm' => $displayData['filter_form'],
		'activeFilters' => $displayData['activeFilters']
	),
	'options' => array(
						'filterButton' => false,
						'searchFieldSelector' => '#filter_search_collections',
						'orderFieldSelector' => '#list_fullordering',
						'searchField' => 'search_collections',
						'limitFieldSelector' => '#list_collection_limit',
						'activeOrder' => $listOrder,
						'activeDirection' => $listDirn,
						'formSelector' => ('#' . $formName),
						'filtersHidden' => (bool) empty($displayData['activeFilters'])
));
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
	<?php echo Text::_('COM_REDSHOPB_COLLECTION_LIST_TITLE'); ?>
</h4>
<form action="<?php echo $action; ?>" name="<?php echo $formName ?>" class="adminForm" id="<?php echo $formName ?>"
	  method="post">
	<?php if ($showToolbar):?>
		<?php echo RedshopbLayoutHelper::render('product.collections.toolbar', $displayData);?>
	<?php endif;?>
	<?php echo RedshopbLayoutHelper::render('searchtools.default', $searchToolsOptions); ?>
	<hr/>
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
		<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="collectionList">
			<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value=""
						   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this, 'col')"/>
				</th>
				<th width="1%">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'w.state', $listDirn, $listOrder); ?>
				</th>
				<th data-toggle="true">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'w.name', $listDirn, $listOrder); ?>
				</th>
				<th width="20%" class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COMPANY_LABEL', 'w.company', $listDirn, $listOrder); ?>
				</th>
				<th width="40%" class="nowrap" data-hide="phone">
					<?php echo Text::_('COM_REDSHOPB_CUSTOMER_DEPARTMENTS_LABEL'); ?>
				</th>
				<th width="1%" class="nowrap" data-hide="phone">
					<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'w.id', $listDirn, $listOrder); ?>
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
			<?php if ($items): ?>
				<tbody>
				<?php foreach ($items as $i => $item): ?>
					<tr>
						<?php $canChange = RedshopbHelperACL::getPermission('manage', 'collection', array('edit.state'), true) && !$canReadOnly;?>
						<?php $canEdit   = RedshopbHelperACL::getPermission('manage', 'collection', array('edit', 'edit.own'), true);?>
						<td>
							<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName, 'cln'); ?>
						</td>
						<td>
							<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'collections.', $canChange, 'cln', null, null, $formName); ?>
						</td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'collections.', $canEdit, 'cln', $formName); ?>
							<?php endif; ?>

							<?php if ($canEdit) :?>
								<?php $itemUrl = 'index.php?option=com_redshopb&id=' . $item->id . '&product_id=' . $productId . '&task=collection.edit';?>

								<?php if ($return):?>
									<?php $itemUrl .= '&return=' . $return;?>
								<?php endif;?>

								<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
									<?php echo $this->escape($item->name); ?>
								</a>
							<?php else:?>
								<?php echo $this->escape($item->name); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $this->escape($item->company); ?>
						</td>
						<td>
							<?php if (!empty($item->departments)) : ?>
								<?php echo $this->escape(implode(', ', $item->departments)); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			<?php endif; ?>
		</table>
	<?php endif; ?>
	<div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
