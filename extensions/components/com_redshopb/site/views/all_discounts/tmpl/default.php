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

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=all_discounts');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>
<script type="text/javascript">
	var rsbftPhone = 750;
</script>
<?php
RedshopbHtml::loadFooTable();
echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-all_discounts">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_all_discounts',
					'searchFieldSelector' => '#filter_search_all_discounts',
					'limitFieldSelector' => '#list_all_discounts_limit',
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
			<div class="redshopb-all_discounts-table">
				<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled"
					id="allDiscountsList">
					<thead>
						<tr>
							<th>
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
									onclick="Joomla.checkAll(this)" />
							</th>
							<th>
								<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'pd.state', $listDirn, $listOrder); ?>
							</th>
							<th data-toggle="true">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_DISCOUNT_TYPE', 'pd.type', $listDirn, $listOrder); ?>
							</th>
							<th data-hide="phone">
								<?php echo Text::_('COM_REDSHOPB_DISCOUNT_TYPE_NAME'); ?>
							</th>
							<th data-hide="phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_SALES_TYPE', 'pd.sales_type', $listDirn, $listOrder); ?>
							</th>
							<th data-hide="phone">
								<?php echo Text::_('COM_REDSHOPB_DISCOUNT_SALES_NAME'); ?>
							</th>
							<th data-hide="phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STARTTIME', 'pd.starting_date', $listDirn, $listOrder); ?>
							</th>
							<th data-hide="phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ENDTIME', 'pd.ending_date', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_DISCOUNT_KIND', 'pd.kind', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PERCENT', 'pd.percent', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_DISCOUNT_TOTAL', 'pd.amount', $listDirn, $listOrder); ?>
							</th>
							<th data-hide="phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 'pd.id', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item): ?>
						<?php
						$canChange  = RedshopbHelperACL::getPermission('manage', 'product', array('edit.state'), true);
						$canEdit    = RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true);
						$canCheckin = $canEdit;
						?>
						<tr>
							<td>
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
								<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'all_discounts.', $canChange, 'cb'); ?>
							</td>
							<td>
								<?php if ($item->checked_out) : ?>
									<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
										$item->checked_out_time, 'all_discounts.', $canCheckin
									); ?>
								<?php endif; ?>

								<?php if ($canEdit) : ?>
								<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=all_discount.edit&id=' . $item->id); ?>">
								<?php endif; ?>
									<?php echo ucwords($this->escape(RText::format($item->type, array('_' => ' ')))); ?>

									<?php if ($canEdit) : ?>
								</a>
									<?php endif; ?>
								</td>
							<td><?php echo $this->escape($item->type_name); ?></td>
							<td><?php echo ucwords($this->escape(RText::format($item->sales_type, array('_' => ' ')))); ?></td>
							<td><?php echo $this->escape($item->sales_name); ?></td>
							<td>
								<?php echo ($item->starting_date !== '0000-00-00 00:00:00') ?
									HTMLHelper::_('date', $item->starting_date, Text::_('DATE_FORMAT_LC4'), false) : ' - '; ?>
							</td>
							<td>
								<?php echo ($item->ending_date !== '0000-00-00 00:00:00') ?
									HTMLHelper::_('date', $item->ending_date, Text::_('DATE_FORMAT_LC4'), false) : ' - '; ?>
							</td>
							<td>
								<?php if ($item->kind == 1): ?>
									<span class="label label-success">
										<?php echo Text::_('COM_REDSHOPB_DISCOUNT_KIND_OPTION_TOTAL') ?>
									</span>
								<?php else: ?>
									<span class="label label-info">
										<?php echo Text::_('COM_REDSHOPB_DISCOUNT_KIND_OPTION_PERCENTAGE') ?>
									</span>
								<?php endif; ?>
							</td>
							<td><?php echo ($item->kind == 1) ? '-' : $item->percent . '%' ?></td>
							<td><?php echo ($item->kind == 1) ? $item->total : '-' ?></td>
							<td><?php echo $item->id; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="redshopb-all_discounts-pagination">
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
