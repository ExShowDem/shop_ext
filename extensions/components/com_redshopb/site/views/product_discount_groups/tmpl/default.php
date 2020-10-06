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
use Joomla\CMS\Factory;

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=product_discount_groups');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-product_discount_groups">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_product_discount_groups',
					'searchFieldSelector' => '#filter_search_product_discount_groups',
					'limitFieldSelector' => '#list_product_discount_groups_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>

		<hr/>

		<?php if (empty($this->items)): ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else: ?>
		<div class="redshopb-product_discount_groups-table">
			<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="discountProductGroupList">
				<thead>
					<tr>
						<th>
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="nowrap center">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'pdg.state', $listDirn, $listOrder); ?>
						</th>
						<th data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_GROUP_NAME', 'pdg.name', $listDirn, $listOrder); ?>
						</th>
						<th data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_GROUP_CODE', 'pdg.code', $listDirn, $listOrder); ?>
						</th>
						<th data-hide="phone" width="50%">
							<?php echo Text::_('COM_REDSHOPB_CUSTOMER_DISCOUNT_GROUP_PRODUCTS'); ?>
						</th>
						<th width="10%" class="nowrap" data-hide="phone">
							<?php echo Text::_('COM_REDSHOPB_OWNER_COMPANY_LABEL'); ?>
						</th>
						<th data-hide="phone"><?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 'pdg.id', $listDirn, $listOrder); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item): ?>
					<?php
					$canChange  = RedshopbHelperACL::getPermission('manage', 'product', Array('edit.state'), true);
					$canEdit    = RedshopbHelperACL::getPermission('manage', 'product', Array('edit','edit.own'), true);
					$canCheckin = $canEdit;
					?>
					<tr>
						<td>
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
							<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'product_discount_groups.', $canChange, 'cb'); ?>
						</td>
						<td>
							<?php if ($item->checked_out): ?>
								<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'product_discount_groups.', $canCheckin); ?>
							<?php endif;

if ($canEdit):?>
							<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=product_discount_group.edit&id=' . $item->id); ?>">
<?php endif;
							echo $this->escape($item->name);

if ($canEdit):?>
							</a>
<?php endif;?>
						</td>
						<td><?php echo $this->escape($item->code); ?></td>
						<td><?php echo $this->escape($item->products_names); ?></td>
						<td><?php echo $this->escape($item->company); ?></td>
						<td><?php echo $item->id; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="redshopb-product_discount_groups-pagination">
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
