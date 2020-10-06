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

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=stockrooms');
?>
<script type="text/javascript">
	var rsbftPhone = 660;
</script>
<?php
RedshopbHtml::loadFooTable();

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

$canChange       = RedshopbHelperACL::getPermission('manage', 'stockroom', Array('edit.state'), true);
$canEdit         = RedshopbHelperACL::getPermission('manage', 'stockroom', Array('edit','edit.own'), true);
$saveOrderingUrl = 'index.php?option=com_redshopb&task=stockrooms.saveOrderAjax&tmpl=component';
HTMLHelper::_('rsortablelist.sortable', 'stockroomList', 'adminForm', 'asc', $saveOrderingUrl);

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-stockrooms">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					"searchFieldSelector" => "#filter_search",
					"searchField"         => "search",
					"limitFieldSelector"  => "#list_stockrooms_limit",
					"activeOrder"         => $listOrder,
					"activeDirection"     => $listDirn,
				)
			)
		);
		?>
		<hr/>
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="stockroomList">
				<thead>
				<tr>
					<th width="1%" class="nowrap">
						<input type="checkbox" name="checkall-toggle" value=""
							title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<a href="#" onclick="return false;" class="js-stools-column-order"
							data-order="s.ordering"
							data-direction="ASC"
							data-name="<?php echo Text::_('JGLOBAL_ORDERING');?>">
							<i class="icon-sort-by-order"></i>
						</a>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 's.state', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STOCKROOM_NAME_LABEL', 's.name', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STOCKROOM_COMPANY_LABEL', 'company_name', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STOCKROOM_MIN_DELIVERY_TIME_LABEL', 's.min_delivery_time', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STOCKROOM_MAX_DELIVERY_TIME_LABEL', 's.max_delivery_time', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 's.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item): ?>
					<tr>
						<td>
							<?php echo HTMLHelper::_('rgrid.id', $i, $item->id) ?>
						</td>
						<td class="order nowrap center hidden-phone">
							<span class="sortable-handler">
								<span class="icon-move"></span>
							</span>
							<input type="text" style="display:none" name="order[]" value="<?php echo $item->ordering; ?>" />
						</td>
						<td>
							<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'stockrooms.', $canChange, 'cb'); ?>
						</td>
						<td>
							<div style="width:25px; display:inline-block; background-color: <?php echo !empty($item->color) ? $item->color : 'none'; ?>;">&nbsp;</div>

							<?php if ($item->checked_out) : ?>
								<?php
								$canCheckin = ($item->checked_out == Factory::getUser()->id) || ($item->checked_out == 0);
								echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'stockrooms.', $canCheckin) . $item->name;
								?>
							<?php else: ?>
								<a href="index.php?option=com_redshopb&task=stockroom.edit&id=<?php echo $item->id; ?>">
									<?php echo $item->name; ?>
								</a>
							<?php endif; ?>
						</td>
						<td>
							<?php echo (!$item->company_id) ? Text::_('COM_REDSHOPB_MAIN_WAREHOUSE') : $item->company_name ?>
						</td>
						<td>
							<?php echo ($item->min_delivery_time) ? (int) $item->min_delivery_time : '-'; ?>
						</td>
						<td>
							<?php echo ($item->max_delivery_time) ? (int) $item->max_delivery_time : '-'; ?>
						</td>
						<td>
							<?php echo $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)); ?>
		<?php endif; ?>
		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
