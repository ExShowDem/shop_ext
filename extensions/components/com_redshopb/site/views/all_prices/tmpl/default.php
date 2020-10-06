<?php
/**
 * @package     Aesir.E-Commerce.Backend
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
?>
<script type="text/javascript">
	var rsbftPhone = 675;
</script>
<?php
RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=all_prices');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<div class="redshopb-all_prices">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm redshopb-all_prices-form" id="adminForm" method="post">
		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view'    => $this,
				'options' => array(
					"searchFieldSelector" => "#filter_search_all_prices",
					"searchField"         => "search_all_prices",
					"limitFieldSelector"  => "#list_all_prices_limit",
					"activeOrder"         => $listOrder,
					"activeDirection"     => $listDirn,
				)
			)
		);
		?>
		<hr/>
		<?php if (empty($this->items)) : ?>
			<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
		<?php else : ?>
			<div class="redshopb-all_prices-table">
				<table id="allPricesList"
					   class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled">
					<thead>
					<tr>
						<th>
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<th class="nowrap" data-hide="phone">
							<?php echo Text::_('COM_REDSHOPB_TYPE'); ?>
						</th>
						<th data-hide="phone"><?php echo Text::_('COM_REDSHOPB_PRODUCT_DESC'); ?></th>
						<th data-toggle="true"><?php echo Text::_('COM_REDSHOPB_SKU'); ?></th>
						<th class="nowrap" data-hide="phone">
							<?php echo Text::_('COM_REDSHOPB_SALES_TYPE'); ?>
						</th>
						<th data-hide="phone"><?php echo Text::_('COM_REDSHOPB_DISCOUNT_SALES_NAME'); ?></th>
						<th class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_START', 'pp.starting_date', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_END', 'pp.ending_date', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_VOLUME', 'pp.quantity_min', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_PRICE', 'pp.price', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" data-hide="phone">
							<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_ID', 'pp.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($this->items as $i => $item): ?>
						<?php
						$canChange  = RedshopbHelperACL::getPermission('manage', 'product', array('edit.state'), true);
						$canEdit    = RedshopbHelperACL::getPermission('manage', 'product', array('edit', 'edit.own'), true);
						$canCheckin = $canEdit;
						$salesType  = '';
						$itemType   = '';

						switch ($item->sales_type)
						{
							case 'customer_price':
								$salesType = Text::_('COM_REDSHOPB_PRODUCT_PRICE_DEBTOR');
								break;
							case 'all_customers':
								$salesType = Text::_('COM_REDSHOPB_PRODUCT_PRICE_ALL_DEBTOR');
								break;
							case 'customer_price_group':
								$salesType = Text::_('COM_REDSHOPB_PRODUCT_PRICE_DEBTOR_GROUP');
								break;
							case 'campaign':
								$salesType = Text::_('COM_REDSHOPB_PRODUCT_PRICE_CAMPAIGN');
								break;
						}

						switch ($item->type)
						{
							case 'product_item':
								$itemType = Text::_('COM_REDSHOPB_PRODUCT_ITEM_FORM_TITLE');
								break;
							case 'product':
								$itemType = Text::_('COM_REDSHOPB_PRODUCT');
								break;
						}
						?>
						<tr>
							<td>
								<?php echo HTMLHelper::_('rgrid.id', $i, $item->id); ?>
							</td>
							<td>
								<?php echo $itemType; ?>
							</td>
							<td>
								<?php echo $item->product_name; ?>
							</td>
							<td>
								<?php if ($canEdit) : ?>
								<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&task=all_price.edit&id=' . $item->id); ?>">
								<?php endif; ?>
									<?php echo $item->sku; ?>

									<?php if ($canEdit) : ?>
								</a>
									<?php endif; ?>
							</td>
							<td>
								<?php echo $salesType; ?>
							</td>
							<td>
								<?php echo $item->sales_name; ?>
							</td>
							<td>
								<?php echo ($item->starting_date == '0000-00-00 00:00:00' ?
									' - ' : HTMLHelper::_('date', $item->starting_date, Text::_('DATE_FORMAT_LC4'), null)) ?>
							</td>
							<td>
								<?php echo ($item->ending_date == '0000-00-00 00:00:00' ?
									' - ' : HTMLHelper::_('date', $item->ending_date, Text::_('DATE_FORMAT_LC4'), null)) ?>
							</td>
							<td>
								<?php if ($item->is_multiple): ?>
									<?php echo '* ' . $item->quantity_min ?>
								<?php elseif ($item->quantity_min && $item->quantity_max): ?>
									<?php echo $item->quantity_min . ' - ' . $item->quantity_max ?>
								<?php elseif ($item->quantity_min): ?>
									<?php echo '> ' . $item->quantity_min ?>
								<?php elseif ($item->quantity_max): ?>
									<?php echo '< ' . $item->quantity_max ?>
								<?php endif; ?>
							</td>
							<td>
								<?php echo RedshopbHelperProduct::getProductFormattedPrice($item->price, $item->alpha3); ?>
							</td>
							<td>
								<?php echo $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="redshopb-all_prices-pagination">
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
