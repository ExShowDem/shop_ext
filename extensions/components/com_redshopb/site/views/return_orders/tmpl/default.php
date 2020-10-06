<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=return_orders');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'name';
?>
<script type="text/javascript">
	var rsbftPhone = 660;
</script>
<?php
RedshopbHtml::loadFooTable();

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-return_orders">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'filterButton' => false,
					'searchField' => 'search_return_orders',
					'searchFieldSelector' => '#filter_search_return_orders',
					'limitFieldSelector' => '#list_return_orders_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>

		<hr/>

		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-return_orders-table">
				<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="return_ordersList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_CUSTOMER_TITLE', 'o.customer_name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ORDER_ID_TITLE', 'ro.order_id', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_NAME', 'product_name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_UOM_PCS', 'ro.quantity', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_RETURN_ORDER_COMMENT', 'ro.comment', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'sr.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
							$canChange    = RedshopbHelperACL::getPermission('manage', 'order', Array('edit.state'), true);
							$canEdit      = RedshopbHelperACL::getPermission('manage', 'order', Array('edit','edit.own'), true);
							$canCheckin   = $canEdit;
							$customerName = isset($item->customer_name) ?
								$item->customer_name : RedshopbHelperShop::getCustomerName($item->customerId, $item->customer_type);
							?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo $customerName; ?>
								</td>
								<td>
									<?php echo Text::_('COM_REDSHOPB_ORDER_ID_TITLE'); ?>:&nbsp;
									<?php echo $this->escape($item->order_id); ?>
								</td>
								<td>
									<?php echo $this->escape($item->product_name); ?> (<?php echo $this->escape($item->product_sku); ?>)
								</td>
								<td>
									<?php echo $this->escape($item->quantity); ?>
								</td>
								<td>
									<?php echo $this->escape($item->comment); ?>
								</td>
								<td>
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-return_orders-pagination">
				<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
			</div>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
