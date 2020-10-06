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
?>

<script type="text/javascript">
	var rsbftPhone = 750;
</script>
<script>
jQuery(document).ready(function() {
	var csvButton = jQuery('#main > div.redcore div.btn-group.pull-right button:contains("Csv")');
	var onclick   = csvButton.attr('onclick');

	csvButton.removeAttr('onclick');

	csvButton.on('click', function(){
		var url = onclick.slice(onclick.indexOf('http'),-2);
		redSHOPB.ajax.generateCsvFile("departments", "#departmentList", url);
	});
});
</script>
<?php
RedshopbHtml::loadFooTable();
RHelperAsset::load('redshopbtreetable.js', 'com_redshopb');

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=departments');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-departments">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_departments',
					'searchFieldSelector' => '#filter_search_departments',
					'limitFieldSelector' => '#list_department_limit',
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
			<div class="redshopb-departments-table">
				<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled js-redshopb-tree-order" id="departmentList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TREE_LABEL', 'c.lft', $listDirn, $listOrder); ?>
						</th>
						<th data-hide="phone, tablet">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_DEPARTMENT_NUMBER_LBL', 'd.department_number', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'd.name', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COMPANY_LABEL', 'd.company', $listDirn, $listOrder); ?>
						</th>
						<th width="4%" class="nowrap" data-hide="phone">
							<?php echo Text::_('COM_REDSHOPB_USERS') ?>
						</th>
						<th width="18%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ADDRESS_LABEL', 'd.address', $listDirn, $listOrder); ?>
						</th>
						<th width="8%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ZIP_LABEL', 'd.zip', $listDirn, $listOrder); ?>
						</th>
						<th width="12%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_CITY_LABEL', 'd.city', $listDirn, $listOrder); ?>
						</th>
						<th width="12%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COUNTRY_LABEL', 'd.country', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'd.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
							$canChange  = RedshopbHelperACL::getPermission('manage', 'department', Array('edit.state'), false, $item->asset_id);
							$canEdit    = RedshopbHelperACL::getPermission('manage', 'department', Array('edit','edit.own'), false, $item->asset_id);
							$canCheckin = $canEdit;
							$thumb      = RedshopbHelperDepartment::getImageThumbHtml($item->id);
							?>
							<tr data-parent="<?php echo $item->parent_id; ?>" data-id="<?php echo $item->id; ?>" data-level="<?php echo $item->level; ?>">
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="js-redshopb-tree">
									<div class="gi">
										<span class="js-redshop-children" style="display: none;">
											<i class="icon-chevron-up"></i>
										</span>
									</div>
								</td>
								<td>
									<?php echo $this->escape($item->department_number); ?>
								</td>
								<td  class="js-redshopb-title" width="20%">
									<table class="js-redshopb-tree-hierarchy">
										<tr>
											<?php
												echo str_repeat('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>', $item->level - 1)
											?>
											<td class="js-redshopb-tree-hierarchy-separator">
												<i class="icon-angle-right"></i>&nbsp;
											</td>
											<td>
												<?php if ($item->checked_out) : ?>
													<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
														$item->checked_out_time, 'departments.', $canCheckin
													); ?>
												<?php endif; ?>

												<?php if ($canEdit) : ?>
												<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=department.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->name); ?>">
												<?php endif; ?>

													<?php if ($thumb != '') : ?>
													<span class="hasTooltip"
														  data-original-title="<div class='thumb'><?php echo htmlspecialchars($thumb, ENT_COMPAT, 'UTF-8'); ?></span>">
													<?php endif;?>

												<?php echo $this->escape($item->name); ?>

												<?php if ($thumb != '') : ?>
													</span>
												<?php endif; ?>

													<?php if ($canEdit) : ?>
												</a>
													<?php endif; ?>
											</td>
										</tr>
									</table>
								</td>
								<td>
									<?php echo $this->escape($item->company); ?>
								</td>
								<td>
									<?php echo $this->escape($item->users); ?>
								</td>
								<td>
									<?php echo $this->escape($item->address); ?>
								</td>
								<td>
									<?php echo $this->escape($item->zip); ?>
								</td>
								<td>
									<?php echo $this->escape($item->city); ?>
								</td>
								<td>
									<?php echo $this->escape(Text::_($item->country)); ?>
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
			<div class="redshopb-departments-pagination">
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
<?php echo RHtml::_(
	'vnrbootstrap.renderModal', 'departmentsModal',
	array(
		'title' => Text::_('COM_REDSHOPB_DEPARTMENT_DELETE_CONFIRM'),
		'footer' => '<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">' . Text::_('JNO') . '</button>'
			. '<button class="btn btn-primary" data-dismiss="modal" onclick="Joomla.submitbutton(\'departments.delete\')">' . Text::_('JYES') . '</button>'
	),
	Text::_('COM_REDSHOPB_DEPARTMENT_DELETE_INFO')
);
