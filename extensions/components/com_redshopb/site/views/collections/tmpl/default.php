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
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=collections');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<script>
jQuery(document).ready(function() {
	var csvButton = jQuery('#main > div.redcore div.btn-group.pull-right button:contains("Csv")');
	var onclick   = csvButton.attr('onclick');

	csvButton.removeAttr('onclick');

	csvButton.on('click', function(){
		var url = onclick.slice(onclick.indexOf('http'),-2);
		redSHOPB.ajax.generateCsvFile("collections", "#collectionList", url);
	});
});
</script>
<div class="redshopb-collections">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'filterButton' => false,
					'searchField' => 'search_collections',
					'searchFieldSelector' => '#filter_search_collections',
					'limitFieldSelector' => '#list_collection_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>

		<hr />
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-collections-table">
				<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable" id="collectionList">
					<thead>
					<tr>
						<th width="1%">
							<input
								type="checkbox"
								name="checkall-toggle"
								value=""
								title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"
								onclick="Joomla.checkAll(this)"
							/>
						</th>
						<th width="1%" class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'w.state', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'w.name', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COMPANY_LABEL', 'w.company', $listDirn, $listOrder); ?>
						</th>
						<th width="40%" class="nowrap" data-hide="phone">
							<?php echo Text::_('COM_REDSHOPB_CUSTOMER_DEPARTMENTS_LABEL'); ?>
						</th>
						<th></th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'w.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
							$canChange  = RedshopbHelperACL::getPermission('manage', 'collection', array('edit.state'), true);
							$canEdit    = RedshopbHelperACL::getPermission('manage', 'collection', array('edit','edit.own'), true);
							$canCheckin = $canEdit;
							?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'collections.', $canChange, 'cb'); ?>
								</td>
								<td>
									<?php if ($item->checked_out) : ?>
										<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '', $item->checked_out_time, 'collections.', $canCheckin); ?>
									<?php endif; ?>

									<?php if ($canEdit) : ?>
										<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=collection.edit&id=' . $item->id); ?>">
									<?php endif; ?>

									<?php echo $this->escape($item->name); ?>

									<?php if ($canEdit) : ?>
										</a>
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
									<?php echo HTMLHelper::_('rgrid.action', $i, 'collections.generateProductSheets', array('active_class' => 'file', 'active_title' => 'COM_REDSHOPB_PDF_GENERATE_PRODUCT_INFORMATION', 'tip' => true)); ?>
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
			<div class="redshopb-collections-pagination">
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
