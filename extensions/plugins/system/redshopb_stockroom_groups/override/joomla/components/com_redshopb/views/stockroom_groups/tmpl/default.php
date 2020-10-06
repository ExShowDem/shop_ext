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

$action = RedshopbRoute::_('index.php?option=com_redshopb&view=stockroom_groups');
?>
<script type="text/javascript">
	var rsbftPhone = 660;
</script>
<?php
RedshopbHtml::loadFooTable();

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

$canChange       = RedshopbHelperACL::getPermission('manage', 'stockroom_group', Array('edit.state'), true);
$canEdit         = RedshopbHelperACL::getPermission('manage', 'stockroom_group', Array('edit','edit.own'), true);
$saveOrderingUrl = 'index.php?option=com_redshopb&task=stockroom_groups.saveOrderAjax&tmpl=component';
HTMLHelper::_('rsortablelist.sortable', 'stockroom_groupList', 'adminForm', 'asc', $saveOrderingUrl);

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-stockroom_groups">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					"searchFieldSelector" => "#filter_search",
					"searchField"         => "search",
					"limitFieldSelector"  => "#list_stockroom_groups_limit",
					"activeOrder"         => $listOrder,
					"activeDirection"     => $listDirn,
				)
			)
		);
		?>
		<hr/>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<div class="pagination-centered">
					<h3><?php echo Text::_('COM_REDSHOPB_NOTHING_TO_DISPLAY') ?></h3>
				</div>
			</div>
		<?php else : ?>
			<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="stockroom_groupList">
				<thead>
				<tr>
					<th width="1%" class="nowrap">
						<input type="checkbox" name="checkall-toggle" value=""
							title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<a href="#" onclick="return false;" class="js-stools-column-order"
							data-order="sg.ordering"
							data-direction="ASC"
							data-name="<?php echo Text::_('JGLOBAL_ORDERING');?>">
							<i class="icon-sort-by-order"></i>
						</a>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'sg.state', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STOCKROOM_GROUP_NAME_LABEL', 'sg.name', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo HTMLHelper::_(
							'rsearchtools.sort', substr(Text::_('COM_REDSHOPB_STOCKROOM_GROUP_MONDAY_LABEL'), 0, 3), 'sg.deadline_weekday_1', $listDirn, $listOrder
						); ?>
					</th>
					<th class="nowrap">
						<?php echo HTMLHelper::_(
							'rsearchtools.sort', substr(Text::_('COM_REDSHOPB_STOCKROOM_GROUP_TUESDAY_LABEL'), 0, 3), 'sg.deadline_weekday_2', $listDirn, $listOrder
						); ?>
					</th>
					<th class="nowrap">
						<?php echo HTMLHelper::_(
							'rsearchtools.sort', substr(Text::_('COM_REDSHOPB_STOCKROOM_GROUP_WEDNESDAY_LABEL'), 0, 3), 'sg.deadline_weekday_3', $listDirn, $listOrder
						); ?>
					</th>
					<th class="nowrap">
						<?php echo HTMLHelper::_(
							'rsearchtools.sort', substr(Text::_('COM_REDSHOPB_STOCKROOM_GROUP_THURSDAY_LABEL'), 0, 3), 'sg.deadline_weekday_4', $listDirn, $listOrder
						); ?>
					</th>
					<th class="nowrap">
						<?php echo HTMLHelper::_(
							'rsearchtools.sort', substr(Text::_('COM_REDSHOPB_STOCKROOM_GROUP_FRIDAY_LABEL'), 0, 3), 'sg.deadline_weekday_5', $listDirn, $listOrder
						); ?>
					</th>
					<th class="nowrap">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_STOCKROOM_GROUP_STOCKROOMS_LABEL', 'stockroom_names', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" data-hide="phone">
						<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 'sg.id', $listDirn, $listOrder); ?>
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
							<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'stockroom_groups.', $canChange, 'cb'); ?>
						</td>
						<td>
							<div style="width:25px; display:inline-block; background-color: <?php echo !empty($item->color) ? $item->color : 'none'; ?>;">&nbsp;</div>

							<?php if ($item->checked_out) : ?>
								<?php
								$checkouter = Factory::getUser($item->checked_out);
								$canCheckin = ($item->checked_out == Factory::getUser()->id) || ($item->checked_out == 0);
								echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'stockroom_groups.', $canCheckin) . $item->name;
								?>
							<?php else: ?>
								<a href="index.php?option=com_redshopb&task=stockroom_group.edit&id=<?php echo $item->id; ?>">
									<?php echo $item->name; ?>
								</a>
							<?php endif; ?>
						</td>
						<td>
							<?php echo date('H:i', strtotime($item->deadline_weekday_1)); ?>
						</td>
						<td>
							<?php echo date('H:i', strtotime($item->deadline_weekday_2)); ?>
						</td>
						<td>
							<?php echo date('H:i', strtotime($item->deadline_weekday_3)); ?>
						</td>
						<td>
							<?php echo date('H:i', strtotime($item->deadline_weekday_4)); ?>
						</td>
						<td>
							<?php echo date('H:i', strtotime($item->deadline_weekday_5)); ?>
						</td>
						<td>
							<?php echo $item->stockroom_names; ?>
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
