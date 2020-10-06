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

RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=unit_measures');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<div class="redshopb-tags">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_unit_measures',
					'searchFieldSelector' => '#filter_search_unit_measures',
					'limitFieldSelector' => '#list_unit_measure_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn,
					'filterButton' => false
				)
			)
		);
		?>

		<hr/>
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-tags-table">
				<table class="table table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled js-redshopb-tree-order" id="tagList">
					<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="nowrap" data-toggle="true">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'um.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_UNIT_MEASURE_ALIAS_LBL', 'um.alias', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'JGRID_HEADING_ID', 'um.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php
						$canChange  = RedshopbHelperACL::getPermission('manage', 'tag', Array('edit.state'), true);
						$canEdit    = RedshopbHelperACL::getPermission('manage', 'tag', Array('edit','edit.own'), true);
						$canCheckin = $canEdit;
						?>
						<?php foreach ($this->items as $i => $item): ?>
							<tr>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php if ($canEdit) : ?>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=unit_measure.edit&id=' . $item->id); ?>" title="<?php echo $this->escape($item->name); ?>">
									<?php endif; ?>
										<?php echo $this->escape($item->name); ?>

										<?php if ($canEdit) : ?>
									</a>
										<?php endif; ?>
								</td>
								<td>
									<?php echo $this->escape($item->alias); ?>
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
			<div class="redshopb-tags-pagination">
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
