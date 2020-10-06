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
	var rsbftTablet = 960;
	var rsbftPhone = 768;
</script>
<script>
jQuery(document).ready(function() {
	var csvButton = jQuery('#main > div.redcore div.btn-group.pull-right button:contains("Csv")');
	var onclick   = csvButton.attr('onclick');

	csvButton.removeAttr('onclick');

	csvButton.on('click', function(){
		var url = onclick.slice(onclick.indexOf('http'),-2);
		redSHOPB.ajax.generateCsvFile("companies", "#companyList", url);
	});
});
</script>
<?php
RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=companies');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

if (strtolower($listOrder) == 'c.lft' && strtolower($listDirn) == 'asc')
	:
	RHelperAsset::load('redshopbtreetable.js', 'com_redshopb');
endif;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-companies">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_companies',
					'searchFieldSelector' => '#filter_search_companies',
					'limitFieldSelector' => '#list_company_limit',
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
			<div class="redshopb-companies-table">
				<table class="table table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled js-redshopb-tree-order" id="companyList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'c.state', $listDirn, $listOrder); ?>
						</th>
						<th data-hide="phone, tablet">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COMPANY_CUSTOMER_NUMBER', 'c.customer_number', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TREE_LABEL', 'c.lft', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'c.name', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap" data-hide="phone, tablet">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COMPANY_PARENT_LBL', 'c.customer_at', $listDirn, $listOrder); ?>
						</th>
						<th width="4%" class="nowrap" data-hide="phone, tablet">
							<?php echo Text::_('COM_REDSHOPB_USERS') ?>
						</th>
						<th width="12%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ADDRESS_LABEL', 'c.address', $listDirn, $listOrder); ?>
						</th>
						<th width="8%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ZIP_LABEL', 'c.zip', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_CITY_LABEL', 'c.city', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_COUNTRY_LABEL', 'c.country', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone, tablet">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
							$canChange  = RedshopbHelperACL::getPermission('manage', 'company', Array('edit.state'), false, $item->asset_id);
							$canEdit    = RedshopbHelperACL::getPermission('manage', 'company', Array('edit','edit.own'), false, $item->asset_id);
							$canCheckin = $canEdit;
							?>
							<tr data-parent="<?php echo $item->parent_id; ?>" data-id="<?php echo $item->id; ?>" data-level="<?php echo $item->level; ?>">
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'companies.', $canChange, 'cb'); ?>
								</td>
								<td>
									<?php echo $this->escape($item->customer_number); ?>
								</td>
								<td class="js-redshopb-tree">
									<div class="gi">
										<span class="js-redshop-children" style="display: none;">
											<i class="icon-chevron-up"></i>
										</span>
									</div>
								</td>
								<td class="js-redshopb-title" width="20%">
									<table class="js-redshopb-tree-hierarchy">
										<tr>
											<?php
											echo ($item->level ? str_repeat('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>', $item->level - 1) : '')
											?>
											<td class="js-redshopb-tree-hierarchy-separator">
												<i class="icon-angle-right"></i>&nbsp;
											</td>
											<td>
												<?php if ($item->checked_out) : ?>
													<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
														$item->checked_out_time, 'companies.', $canCheckin
													); ?>
												<?php endif; ?>

												<?php if ($canEdit) : ?>
													<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=company.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->name); ?>">
												<?php endif; ?>
														<?php echo $this->escape($item->name); ?>

														<?php if ($item->name2): ?>
															<br/><small><?php echo $this->escape($item->name2); ?></small>
														<?php endif; ?>

												<?php if ($canEdit) : ?>
													</a>
												<?php endif; ?>
											</td>
										</tr>
									</table>
								</td>
								<td>
									<?php echo $this->escape($item->customer_at); ?>
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
			<div class="redshopb-companies-pagination">
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
	'vnrbootstrap.renderModal', 'companiesModal',
	array(
		'title' => Text::_('COM_REDSHOPB_COMPANY_DELETE_CONFIRM'),
		'footer' => '<button class="btn btn" data-dismiss="modal" aria-hidden="true">' . Text::_('JNO') . '</button>'
			. '<button class="btn btn-primary" data-dismiss="modal" onclick="Joomla.submitbutton(\'companies.delete\')">' . Text::_('JYES') . '</button>'
	),
	Text::_('COM_REDSHOPB_COMPANY_DELETE_INFO')
);
