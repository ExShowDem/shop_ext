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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

RedshopbHtml::loadFooTable();

$action    = RedshopbRoute::_('index.php?option=com_redshopb&view=carts');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder == 'ordering';
$isShop    = RedshopbHelperPrices::displayPrices();
$isAdmin   = RedshopbHelperACL::isSuperAdmin();

if (!empty($this->items))
{
	foreach ($this->items as $i => $item)
	{
		if ($item->user_cart == '1')
		{
			unset($this->items[$i]);
		}
	}
}

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>

<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			redSHOPB.carts.init(
				'<?php echo Session::getFormToken() ?>',
				'<?php echo Text::_('SUCCESS') ?>',
				'<?php echo Text::_('WARNING') ?>',
				'<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=cart', false, 0) ?>'
			);
		});
	})(jQuery);
</script>

<div class="redshopb-saved-orders">
	<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

		<?php
		echo RedshopbLayoutHelper::render(
			'searchtools.default',
			array(
				'view' => $this,
				'options' => array(
					'searchField' => 'search_carts',
					'searchFieldSelector' => '#filter_search_carts',
					'limitFieldSelector' => '#list_carts_limit',
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
			<div class="redshopb-saved-orders-table">
				<table class="table table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled js-redshopb-tree-order"
					id="savedCartsTable">
					<thead>
						<tr>
							<th width="1%" class="nowrap">
								&nbsp;
							</th>
							<?php if ($isAdmin): ?>
							<th width="1%" class="nowrap center">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'JSTATUS', 'sc.state', $listDirn, $listOrder) ?>
							</th>
							<?php endif; ?>
							<th class="nowrap" data-toggle="true">
								<?php echo HTMLHelper::_('rsearchtools.sort', 'COM_REDSHOPB_NAME', 'sc.name', $listDirn, $listOrder) ?>
							</th>
							<th class="nowrap">
								<?php echo Text::_('COM_REDSHOPB_SAVED_CARTS_LAST_ORDER_DATE') ?>
							</th>
							<th class="nowrap">
								<?php echo HTMLHelper::_(
									'rsearchtools.sort',
									'COM_REDSHOPB_SAVED_CARTS_NUMBER_OF_PRODUCTS',
									'products_count',
									$listDirn,
									$listOrder
								) ?>
							</th>
							<?php if ($isShop) : ?>
							<th class="nowrap">
								<?php echo Text::_('COM_REDSHOPB_SAVED_CARTS_TOTAL_PRICE') ?>
							</th>
							<?php endif; ?>

							<?php if ($isAdmin): ?>
							<th class="nowrap">
								<?php echo Text::_('COM_REDSHOPB_SAVED_CARTS_OWNER') ?>
							</th>
							<?php endif; ?>

							<?php if ($isShop) : ?>
							<th class="nowrap">
								&nbsp;
							</th>
							<?php endif; ?>
						</tr>
					</thead>
					<?php if ($this->items): ?>
						<tbody>
						<?php foreach ($this->items as $i => $item): ?>

						<?php
						$cartItems = RedshopbEntityCart::getInstance($item->id);
						$cartItems->applyCartItemsPrices();
						$totalVariantsPriceArray = array();

						foreach ($cartItems->getItems() as $cartItem)
						{
							$db    = Factory::getDbo();
							$query = $db->getQuery(true);

							$query->select(array('*'));
							$query->from($db->qn('#__redshopb_product_price'));
							$query->where($db->qn('product_id') . ' = ' . $db->q($cartItem->get('product_id')));
							$query->where($db->qn('product_item_id') . ' = ' . $db->q($cartItem->get('product_item_id')));

							$pricesAttrib  = $db->setQuery($query)->loadObjectList();
							$productItemId = $cartItem->get('product_item_id');

							if (isset($productItemId) && $productItemId > 0)
							{
								$totalVariantsPriceArray[$productItemId]['quantity'] = (float) $cartItem->get('quantity');
							}

							if (!is_object($cartItem->get('price')))
							{
								foreach ($pricesAttrib as $priceAttrib)
								{
									$totalVariantsPriceArray[$priceAttrib->product_item_id]['price']    = (float) $priceAttrib->price;
									$totalVariantsPriceArray[$priceAttrib->product_item_id]['currency'] = (float) $priceAttrib->currency_id;
								}
							}
						}
						?>
							<?php
								$canChange  = RedshopbHelperACL::getPermission('manage', 'order', Array('edit.state'), true);
								$canEdit    = RedshopbHelperACL::getPermission('manage', 'order', Array('edit','edit.own'), true);
								$canCheckin = $canEdit;
							?>
							<tr id="row-<?php echo $item->id ?>">
								<td style="vertical-align: middle">
									<a class="btn-remove-saved-cart" data-id="<?php echo $item->id ?>" href="#">
										<i class="icon-trash"></i>
									</a>
								</td>
								<?php if ($isAdmin): ?>
								<td style="vertical-align: middle">
									<?php echo HTMLHelper::_('rgrid.published', $item->state, $i, 'carts.', $canChange, 'cb') ?>
								</td>
								<?php endif; ?>
								<td style="vertical-align: middle">
									<a href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=cart&id=' . $item->id, false) ?>"
										title="<?php echo $this->escape($item->name); ?>">
										<?php echo $this->escape($item->name) ?>
									</a>
								</td>
								<td style="vertical-align: middle">
									<?php
									if (is_null($item->last_order))
									{
										echo Text::_('COM_REDSHOPB_REPORTS_NEVER');
									}
									else
									{
										echo HTMLHelper::_('date', $item->last_order, Text::_('DATE_FORMAT_LC4'));
									}
									?>
								</td>
								<td style="vertical-align: middle">
									<?php echo $item->products_count ?>
								</td>
								<?php if ($isShop) : ?>
								<td style="vertical-align: middle">
									<?php
									$allTotals = array();

									// If product
									if (isset($item->totals) && !empty($item->totals))
									{
										foreach ($item->totals as $currency => $total)
										{
											if (!isset($allTotals[$currency]))
											{
												$allTotals[$currency] = 0;
											}

											$allTotals[$currency] += $total;
										}
									}

									// If product item
									if (!empty($totalVariantsPriceArray))
									{
										$totalVariantsCurrenciesPrice = array();

										foreach ($totalVariantsPriceArray as $variant)
										{
											$currencyId  = $variant['currency'];
											$currencyObj = RHelperCurrency::getCurrency($currencyId);
											$currency    = $currencyObj->alpha3;

											if (!isset($totalVariantsCurrenciesPrice[$currency]))
											{
												$totalVariantsCurrenciesPrice[$currency] = 0;
											}

											$totalVariantsCurrenciesPrice[$currency] += ($variant['quantity'] * $variant['price']);
										}

										foreach ($totalVariantsCurrenciesPrice as $currency => $total)
										{
											if (!isset($allTotals[$currency]))
											{
												$allTotals[$currency] = 0;
											}

											$allTotals[$currency] += $total;
										}
									}

									foreach ($allTotals as $currency => $allTotal)
									{
										echo RedshopbHelperProduct::getProductFormattedPrice($allTotal, $currency) . '<br />';
									}
									?>
								</td>
								<?php endif; ?>

								<?php if ($isAdmin): ?>
								<td style="vertical-align: middle">
									<?php echo $item->user_name ?>
								</td>
								<?php endif; ?>

								<?php if ($isShop) : ?>
									<td style="vertical-align: middle; text-align: right">
									<span class="img-loading-cart img-loading-cart-<?php echo $item->id ?>">
										<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', ''); ?>
									</span>
									<button class="btn btn-default btn-checkout-saved-cart" data-id="<?php echo $item->id ?>">
										<?php echo Text::_('COM_REDSHOPB_SAVED_CARTS_CHECK_OUT') ?>
									</button>
								</td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
						</tbody>
					<?php endif; ?>
				</table>
			</div>
			<div class="redshopb-saved-orders-pagination">
				<?php echo $this->pagination->getPaginationLinks(null, array('showLimitBox' => false)) ?>
			</div>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
