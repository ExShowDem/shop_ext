<?php
/**
 * @package     Redshopb.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('rbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

?>
<script type="text/javascript">
	var rsbftTablet = 960;
	var rsbftPhone = 768;
</script>
<?php
RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=free_shippings');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';

// Global ACL permissions since there is no company property over currencies
$canChange  = RedshopbHelperACL::getPermission('manage', 'product', Array('edit.state'), true);
$canEdit    = RedshopbHelperACL::getPermission('manage', 'product', Array('edit', 'edit.own'), true);
$canCheckin = $canEdit;

?>
<div class="redshopb-free_shippings">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_free_shippings',
					'searchFieldSelector' => '#filter_search_free_shippings',
					'limitFieldSelector' => '#list_free_shipping_limit',
					'activeOrder' => $listOrder,
					'activeDirection' => $listDirn
				)
			)
		);
		?>

		<hr/>

		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('redshopb.common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-free_shippings-table">
				<table class="table table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled js-redshopb-tree-order"
					   id="freeShippingList">
					<thead>
						<tr>
							<th width="1%" class="hidden-phone">
								<input type="checkbox" name="checkall-toggle" value=""
									title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
							</th>
							<th class="nowrap hidden-phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRODUCT_DISCOUNT_GROUP', 'pdg.name', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap hidden-phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_CATEGORY_LABEL', 'fs.category_id', $listDirn, $listOrder); ?>
							</th>
							<th class="nowrap hidden-phone">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_THRESHOLD_EXPENDITURE',
									'fs.threshold_expenditure', $listDirn, $listOrder
								); ?>
							</th>
						</tr>
					</thead>
					<?php if ($this->items) : ?>
						<tbody>
							<?php foreach ($this->items as $i => $item) : ?>
								<tr>
									<td>
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									</td>
									<td>
										<?php if ($item->checked_out) : ?>
											<?php echo HTMLHelper::_('rgrid.checkedout', $i,
												$item->checked_out > 0 ? Factory::getUser($item->checked_out)->get('name') : '',
												$item->checked_out_time, 'free_shippings.', $canCheckin
											); ?>
										<?php endif; ?>

										<?php if ($canEdit) : ?>
											<a href="<?php
												echo RedshopbRoute::_('index.php?option=com_redshopb&task=free_shipping.edit&id=' . $item->id); ?>">
										<?php endif; ?>

										<?php echo $this->escape($item->name); ?>

										<?php if ($canEdit) : ?>
											</a>
										<?php endif; ?>
									</td>
									<td>
										<?php echo $this->escape($item->category_name); ?>
									</td>
									<td>
										<?php echo $this->escape(Text::_($item->threshold_expenditure)); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-free_shippings-pagination">
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
