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

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<script type="text/javascript">
	var rsbftTablet = 960;
	var rsbftPhone = 768;
</script>

<?php
RedshopbHtml::loadFooTable();

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canEdit   = RedshopbHelperACL::getPermission('manage', 'fields', array('edit', 'edit.own'), true);
$canChange = RedshopbHelperACL::getPermission('manage', 'fields', Array('edit.state'), true);

$saveOrderingUrl = 'index.php?option=com_redshopb&task=fields.saveOrderAjax&tmpl=component';
HTMLHelper::_('rsortablelist.sortable', 'fieldList', 'adminForm', 'asc', $saveOrderingUrl);
?>
<div class="redshopb-descriptions">
	<form action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=fields'); ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
			echo RedshopbLayoutHelper::render(
				'searchtools.default',
				array(
					'view' => $this,
					'options' => array(
						'searchField' => 'search_fields',
						'searchFieldSelector' => '#filter_search_fields',
						'limitFieldSelector' => '#list_fields_limit',
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
			<div class="redshopb-fields-table">
				<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled" id="fieldList">
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
						<th width="1%">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'f.state', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_NAME_LABEL', 'f.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_SCOPE_LABEL', 'f.scope', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_GROUP_LABEL', 'fg.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_TYPE_ID_LABEL', 't.alias', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_ALIAS_LABEL', 'f.alias', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'f.id', $listDirn, $listOrder); ?>
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
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'fields.', $canChange, 'cb'); ?>
								</td>
								<td>
									<?php if ($canEdit):
										$itemUrl = 'index.php?option=com_redshopb&task=field.edit&id=' . $item->id;
									?>
									<a href="<?php echo RedshopbRoute::_($itemUrl); ?>">
									<?php endif; ?>
										<?php echo $this->escape($item->name); ?>

									<?php if ($canEdit): ?>
									</a>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $this->escape($item->scope);?>
								</td>
								<td>
									<?php echo $this->escape($item->field_group_name);?>
								</td>
								<td>
									<?php echo $this->escape($item->field_type_name);?>
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
