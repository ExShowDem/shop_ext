<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$data = $displayData;

$state       = $data['state'];
$items       = $data['items'];
$pagination  = $data['pagination'];
$filterForm  = $displayData['filter_form'];
$formName    = $data['formName'];
$showToolbar = isset($data['showToolbar']) ? $data['showToolbar'] : false;
$return      = isset($data['return']) ? $data['return'] : null;
$action      = RedshopbRoute::_('index.php?option=com_redshopb&view=orders');

// Allow to override the form action
if (isset($data['action']))
{
	$action = $data['action'];
}

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$userId    = Factory::getApplication()->input->getInt('id');
$listOrder = $state->get('list.ordering');
$listDirn  = $state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

$canEdit = RedshopbHelperACL::getPermission('manage', 'order', Array('edit', 'edit.own'), true);

$searchToolsOptions = array(
	'filterButton' => false,
	"searchFieldSelector" => "#filter_search_orders",
	"orderFieldSelector" => "#list_fullordering",
	"searchField" => "search_orders",
	"limitFieldSelector" => "#list_order_limit",
	"activeOrder" => $listOrder,
	"activeDirection" => $listDirn,
	"formSelector" => ("#" . $formName),
	"filtersHidden" => (bool) empty($data['activeFilters'])
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
<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm" id="<?php echo $formName; ?>"
	  method="post">
	<?php
	// Render the toolbar?
	if ($showToolbar)
	{
		echo RedshopbLayoutHelper::render('orders.toolbar', $data);
	}
	?>

	<?php
	echo RedshopbLayoutHelper::render(
		'user.orders.searchtools.default',
		array(
			'view' => (object) array(
					'filterForm' => $data['filter_form'],
					'activeFilters' => $data['activeFilters']
				),
			'options' => $searchToolsOptions
		)
	);
	?>

	<hr/>
	<?php if (empty($items)) : ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else : ?>
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th style="width:1%" class="hidden-phone">
				<input type="checkbox" name="checkall-toggle" value=""
					   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
			</th>
			<th class="nowrap hidden-phone">
				<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_ID_TITLE', 'o.id', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
			</th>
			<th class="nowrap hidden-phone">
				<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_STATUS', 'o.status', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
			</th>
			<th class="nowrap hidden-phone">
				<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_REQUISITION', 'o.requisition', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
			</th>
			<th class="nowrap hidden-phone">
				<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_TOTAL_PRICE', 'o.total_price', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
			</th>
			<th class="nowrap hidden-phone">
				<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_DATE_CREATED', 'o.created_date', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
			</th>
			<th class="nowrap hidden-phone">
				<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_CREATED_USER', 'u3.name', $listDirn, $listOrder, null, 'asc', '', null, $formName); ?>
			</th>
			<th class="nowrap hidden-phone">
				<span><?php echo Text::_('COM_REDSHOPB_ACTIONS'); ?></span>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($items as $i => $item):
			$order      = ($item instanceof RedshopbEntityOrder) ? $item : RedshopbEntityOrder::getInstance($item->id)->bind($item);
			$canEdit    = RedshopbHelperACL::getPermission('manage', 'order', array('edit', 'edit.own'), true);
			$canCheckin = $canEdit;
			?>
			<tr>
				<td>
					<?php echo HTMLHelper::_('rgrid.id', $i, $item->id, false, 'cid', $formName); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
							$item->checked_out_time, 'orders.', $canCheckin, 'cb', $formName
						); ?>
					<?php endif; ?>
					<?php
					$itemUrl = 'index.php?option=com_redshopb&task=order.edit&id=' . $item->id
						. '&jform[user_id]=' . $userId . '&from_user=1';

					if ($return)
					{
						$itemUrl .= '&return=' . $return;
					}
					?>
					<?php if ($canEdit) : ?>
						<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
					<?php endif; ?>
						<?php echo $item->id; ?>

					<?php if ($canEdit) : ?>
						</a>
					<?php endif; ?>
				</td>
				<td>
					<?php echo $this->escape($order->getStatusName()); ?>
				</td>
				<td>
					<?php echo $this->escape($item->requisition); ?>
				</td>
				<td>
					<?php echo $this->escape($item->total_price) . ' ' . $this->escape(RedshopbHelperProduct::getCurrency($item->currency_id)->alpha3); ?>
				</td>
				<td>
					<?php echo HTMLHelper::_('date', $item->created_date, Text::_('DATE_FORMAT_LC4')) ?>
				</td>
				<td>
					<?php echo $this->escape($item->author); ?>
				</td>
				<td>
					<a class="btn btn-sm btn-primary"
					   href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=order.printPDF&id=' . $item->id) ?>"
					   target="_blank"
					   title="<?php echo Text::_('COM_REDSHOPB_PRINT'); ?>">
						<i class="icon-print"></i>
					</a>
					<a class="btn btn-sm btn-success" href="index.php?option=com_redshopb&task=order.edit&id=<?php echo $item->id;?>" title="<?php echo Text::_('JEDIT'); ?>">
						<i class="icon-edit"></i>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $pagination->getPaginationLinks('tab.pagination.links', array('showLimitBox' => false)); ?>
	<?php endif; ?>

	<div>
		<input type="hidden" name="task" value="user.saveModelState">
		<?php if ($return) : ?>
			<input type="hidden" name="return" value="<?php echo $return ?>">
		<?php endif; ?>
		<input type="hidden" name="jform[user_id]" value="<?php echo $userId; ?>">
		<input type="hidden" name="from_user" value="1">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
