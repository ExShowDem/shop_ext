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

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rdropdown.init');
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
		redSHOPB.ajax.generateCsvFile("categories", "#categoryList", url);
	});
});
</script>

<?php
RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=categories');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = false;

if (strtolower($listOrder) == 'c.lft' && strtolower($listDirn) == 'asc')
{
	$saveOrder = true;
	RHelperAsset::load('redshopbtreetable.js', 'com_redshopb');
	$saveOrderingUrl = 'index.php?option=com_redshopb&task=categories.saveOrderAjax&tmpl=component';
	HTMLHelper::_('rsortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-categories">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_categories',
					'searchFieldSelector' => '#filter_search_categories',
					'limitFieldSelector' => '#list_category_limit',
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
			<div class="redshopb-categories-table">
				<table class="table table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled js-redshopb-tree-order" id="categoryList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'c.state', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_FIELD_ORDERING_LABEL', 'c.lft', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo Text::_('COM_REDSHOPB_TREE_LABEL'); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGLOBAL_TITLE', 'c.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-hide="phone">
							<?php echo Text::_('COM_REDSHOPB_COMPANY_LABEL'); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'c.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
							$isParentCategory = !in_array($item->company_id, $this->availableCompanies);
							$canChange        = RedshopbHelperACL::getPermission('manage', 'category', Array('edit.state'), true);
							$orderkey         = array_search($item->id, $this->ordering[$item->parent_id]);

							if ($canChange && $isParentCategory)
							{
								$canChange = false;
							}

							$canEdit = RedshopbHelperACL::getPermission('manage', 'category', Array('edit','edit.own'), true);

							if ($canEdit && $isParentCategory)
							{
								$canEdit = false;
							}

							$canCheckin = $canEdit;
							$thumb      = RedshopbHelperCategory::getCategoryImageThumbHtml($item->id);

							// Get the parents of item for sorting
							if ($item->level > 1)
							{
								$parentsStr      = "";
								$currentParentId = $item->parent_id;
								$parentsStr      = " " . $currentParentId;

								for ($i2 = 0; $i2 < $item->level; $i2++)
								{
									foreach ($this->ordering as $k => $v)
									{
										$v = implode("-", $v);
										$v = "-" . $v . "-";

										if (strpos($v, "-" . $currentParentId . "-") !== false)
										{
											$parentsStr     .= " " . $k;
											$currentParentId = $k;
											break;
										}
									}
								}
							}
							else
							{
								$parentsStr = "";
							}

							?>
							<tr data-parent="<?php echo $item->parent_id; ?>"
								data-id="<?php echo $item->id; ?>"
								data-level="<?php echo $item->level; ?>"
								sortable-group-id="<?php echo $item->parent_id; ?>"
								item-id="<?php echo $item->id ?>"
								parents="<?php echo $parentsStr ?>"
								level="<?php echo $item->level ?>">
								<td>
									<?php if ($canChange):?>
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									<?php endif;?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'categories.', $canChange, 'cb'); ?>
								</td>
								<td class="order hidden-phone">
									<?php
									$iconClass = '';

									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::tooltipText('JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
										<span class="icon-move"></span>
									</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
									<?php endif; ?>
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
														$item->checked_out_time, 'categories.', $canCheckin
													); ?>
												<?php endif; ?>

												<?php if ($canEdit) : ?>
												<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=category.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->name); ?>">
												<?php endif; ?>
													<?php
													if ($thumb != '') :
													?>
													<span class="hasTooltip"
														  data-original-title="<div class='thumb'><?php echo htmlspecialchars($thumb, ENT_COMPAT, 'UTF-8'); ?></span>">
												<?php
													endif;
												?>
												<?php echo $this->escape($item->name); ?>
												<?php
												if ($thumb != '') :
												?>
													</span>
												<?php
												endif;
												?>
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
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-categories-pagination">
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
