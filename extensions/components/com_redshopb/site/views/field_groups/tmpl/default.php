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

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-descriptions">
	<form action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=field_groups'); ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
			echo RedshopbLayoutHelper::render(
				'searchtools.default',
				array(
					'view' => $this,
					'options' => array(
						'searchField' => 'search_field_groups',
						'searchFieldSelector' => '#filter_search_field_groups',
						'limitFieldSelector' => '#list_field_groups_limit',
						'activeOrder' => $listOrder,
						'activeDirection' => $listDirn
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
			<div class="redshopb-field-groups-table">
				<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="fieldGroupList">
					<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<a href="#" onclick="return false;" class="js-stools-column-order"
							   data-order="f.ordering"
							   data-direction="ASC"
							   data-name="<?php echo Text::_('JGLOBAL_ORDERING');?>">
								<i class="icon-sort-by-order"></i>
							</a>
						</th>
						<th width="1%" class="nowrap">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_GROUP_NAME_LABEL', 'fg.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_SCOPE_LABEL', 'fg.scope', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_ALIAS_LABEL', 'fg.alias', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'fg.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<tr>
								<td class="order nowrap center hidden-phone">
									<span class="sortable-handler">
										<span class="icon-move"></span>
									</span>
									<input type="text" style="display:none" name="order[]" value="<?php echo $item->ordering; ?>" />
								</td>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php
										$itemUrl = 'index.php?option=com_redshopb&task=field_group.edit&id=' . $item->id;
									?>
									<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
										<?php echo $this->escape($item->name); ?>
									</a>
								</td>
								<td>
									<?php echo $this->escape($item->scope);?>
								</td>
								<td>
									<?php echo $this->escape($item->alias);?>
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
			<div class="redshopb-descriptions-pagination">
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
