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

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=product_attributes');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'a.ordering';

$saveOrderingUrl = 'index.php?option=com_redshopb&task=product_attributes.saveOrderAjax&tmpl=component';
HTMLHelper::_('rsortablelist.sortable', 'productAttributeList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
?>

<div class="redshopb-product_attributes">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<!-- Search tools -->
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'filterButton' => false,
					'searchField' => 'search_product_attributes',
					'searchFieldSelector' => '#filter_search_product_attributes',
					'limitFieldSelector' => '#list_product_attributes_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>
		<!-- End Search tools -->
		<hr/>
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-product_attributes-table">
				<table class="table table-striped table-hover" id="productAttributeList">
					<thead>
					<tr>
						<th width="4%" class="nowrap center hidden-phone">
							<?php echo HTMLHelper::_('rgrid.sort', null, 'a.ordering', $listDirn, $listOrder, null, 'asc', '', 'icon-sort'); ?>
						</th>
						<th width="1%" class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rgrid.sort', 'COM_REDSHOPB_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo Text::_('COM_REDSHOPB_VALUES'); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('rgrid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>
							<?php
							$canChange  = 1;
							$canEdit    = 1;
							$canCheckin = 1;
							?>
							<tr>
								<td class="order nowrap center hidden-phone">
									<?php if ($canChange) :
										$disableClassName = '';
										$disabledLabel    = '';

										if (!$saveOrder)
										{
											$disabledLabel    = Text::_('JORDERINGDISABLED');
											$disableClassName = 'inactive tip-top';
										}
										?>
										<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>"
											  title="<?php echo $disabledLabel ?>">
											<i class="icon-ellipsis-vertical"></i>
										</span>
										<input type="text" style="display:none" name="order[]" size="5"
											   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
									<?php else : ?>
										<span class="sortable-handler inactive">
											<i class="icon-ellipsis-vertical"></i>
										</span>
									<?php endif; ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php if ($item->checked_out) : ?>
										<?php echo HTMLHelper::_('rgrid.checkedout', $i, $item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
											$item->checked_out_time, 'product_attributes.', $canCheckin
										); ?>
									<?php endif; ?>
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=product_attribute.edit&id=' . $item->id); ?>">
										<?php echo $this->escape($item->name); ?>
									</a>
								</td>
								<td>
									<?php foreach ($item->values as $value) : ?>
										<span class="label label-success"><?php echo $value->value ?></span>
									<?php endforeach; ?>
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
			<div class="redshopb-product_attributes-pagination">
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
