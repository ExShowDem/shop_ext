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

<?php
RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=manufacturers');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

if (strtolower($listOrder) == 'm.lft' && strtolower($listDirn) == 'asc')
	:
	RHelperAsset::load('redshopbtreetable.js', 'com_redshopb');
endif;

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-manufacturers">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_manufacturers',
					'searchFieldSelector' => '#filter_search_manufacturers',
					'limitFieldSelector' => '#list_manufacturer_limit',
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
			<div class="redshopb-manufacturers-table">
				<table class="table table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled js-redshopb-tree-order" id="categoryList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'm.state', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_TREE_LABEL', 'm.lft', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGLOBAL_TITLE', 'm.name', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'm.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
								$canChange  = RedshopbHelperACL::getPermission('manage', 'product', Array('edit.state'), true);
								$canEdit    = RedshopbHelperACL::getPermission('manage', 'product', Array('edit','edit.own'), true);
								$canCheckin = $canEdit;
								$thumb      = RedshopbHelperManufacturer::getImageThumbHtml($item->id);
							?>
							<tr data-parent="<?php echo $item->parent_id; ?>" data-id="<?php echo $item->id; ?>" data-level="<?php echo $item->level; ?>">
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'manufacturers.', $canChange, 'cb'); ?>
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
														$item->checked_out_time, 'manufacturers.', $canCheckin
													); ?>
												<?php endif; ?>

												<?php if ($canEdit) : ?>
												<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=manufacturer.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->name); ?>">
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
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-manufacturer-pagination">
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
