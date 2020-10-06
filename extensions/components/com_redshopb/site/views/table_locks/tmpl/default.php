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
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
?>

<script type="text/javascript">
	var rsbftTablet = 960;
	var rsbftPhone = 768;
</script>
<?php
RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=table_locks');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = false;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-table_locks">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_table_locks',
					'searchFieldSelector' => '#filter_search_table_locks',
					'limitFieldSelector' => '#list_table_lock_limit',
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
			<div class="redshopb-table_locks-table">
				<table class="table table-hover toggle-circle-filled" id="table_lockList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TABLE_LOCK_TABLE_NAME_TITLE', 'tl.table_name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TABLE_LOCK_TABLE_ID_TITLE', 'tl.table_id', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TABLE_LOCK_COLUMN_NAME_TITLE', 'tl.column_name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TABLE_LOCK_LOCKED_BY_TITLE', 'tl.locked_by', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TABLE_LOCK_LOCKED_DATE_TITLE', 'tl.locked_date', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TABLE_LOCK_LOCKED_METHOD_TITLE', 'tl.locked_method', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'tl.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item):
								$canChange = RedshopbHelperACL::getPermission('manage', 'table_lock', Array('edit.state'), true);
								$canEdit   = RedshopbHelperACL::getPermission('manage', 'table_lock', Array('edit'), true);
							?>
							<tr>
								<td>
									<?php if ($canChange):?>
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									<?php endif;?>
								</td>
								<td class="js-redshopb-title" width="20%">
									<?php if ($canEdit) : ?>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=table_lock.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->table_name); ?>">
									<?php endif; ?>
											<?php echo $this->escape($item->table_name); ?>

									<?php if ($canEdit) : ?>
									</a>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $this->escape($item->table_id); ?>
								</td>
								<td>
									<?php echo $this->escape($item->column_name); ?>
								</td>
								<td>
									<?php echo $this->escape($item->locked_by_name); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('date', $this->item->locked_date, 'Y-m-d H:i:s'); ?>
								</td>
								<td>
									<?php echo $this->escape($item->locked_method); ?>
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
			<div class="redshopb-table_locks-pagination">
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
<div id="bulkDelete" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-content">
		<div class="modal-dialog">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 id="myModalLabel"><i class="icon-warning-sign"></i>&nbsp;<?php echo Text::_('COM_REDSHOPB_TABLE_LOCK_BULK_DELETE_CONFIRM'); ?></h3>
			</div>
			<div class="modal-body">
				<p><?php echo Text::sprintf('COM_REDSHOPB_TABLE_LOCK_BULK_DELETE_CONFIRM_NUMBER_OF_ROWS', $this->pagination->total); ?></p>
			</div>
			<div class="modal-footer">
				<button class="btn btn" data-dismiss="modal" aria-hidden="true"><?php echo Text::_('JNO')?></button>
				<button class="btn btn-primary" data-dismiss="modal" onclick="Joomla.submitbutton('table_locks.bulkDelete')"><?php echo Text::_('JYES')?></button>
			</div>
		</div>
	</div>
</div>
