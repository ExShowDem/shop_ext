<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$data           = $displayData;
$customerOrders = $data['customerorders'];
$shippingPrice  = 0;
$cartTotal      = array();
$config         = RedshopbEntityConfig::getInstance();
$showTaxes      = $config->getInt('show_taxes_in_cart_module', 1);
?>
<div class="redcore">
	<h2 style="text-align: center; padding-top:30px;"><?php echo Text::_('COM_REDSHOPB_ORDER') . ' ' . $data['orderid']; ?></h2>

	<?php if (empty($customerOrders) || !is_array($customerOrders)): ?>
		<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
	<?php else: ?>
		<?php foreach ($customerOrders as $customerOrder): ?>
			<?php
			$customerType             = $customerOrder->customerType;
			$customerId               = $customerOrder->customerId;
			$items                    = $customerOrder->regular->items;
			$currency                 = $customerOrder->currency;
			$subtotalWithoutDiscounts = $customerOrder->regular->subtotalWithoutDiscounts;
			$customer                 = RedshopbHelperOrder::getEntityFromCustomer($customerId, $customerType);
			$showDiscountColumn       = false;
			$showAttributes           = false;

			if (!isset($cartTotal[$currency]))
			{
				$cartTotal[$currency] = 0;
			}

			$cartTotal[$currency] += $customerOrder->totalFinal;

			foreach ($items as $item)
			{
				if (isset($item->discount) && $item->discount > 0)
				{
					$showDiscountColumn = true;
				}

				if (isset($item->attributes) && !empty($item->attributes))
				{
					$showAttributes = true;
				}
			}

			if (RedshopbHelperACL::getPermissionInto('impersonate', 'order') && !RedshopbEntityCompany::load($customerOrder->customer_company)->get('hide_company')):
				?>
				<h4 class="alert alert-success">
					<?php echo Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TITLE') . ' : ' . $customer->name . ' (' . Text::_('COM_REDSHOPB_' . strtoupper($customerOrder->customerType)) . ')'; ?>
				</h4>
			<?php endif; ?>
			<table class="table table-striped">
			<thead>
			<tr>
				<th><?php echo Text::_('COM_REDSHOPB_PRODUCT_FORM_TITLE'); ?><br />
					<small>(<?php echo Text::_('COM_REDSHOPB_PRODUCT_ITEM_CODE'); ?>)</small></th>

				<?php if ($showAttributes): ?>
					<th><?php echo Text::_('COM_REDSHOPB_ORDER_PRODUCT_ATTRIBUTES'); ?></th>
				<?php endif; ?>
				<th><?php echo Text::_('COM_REDSHOPB_QTY'); ?></th>
				<th><?php echo Text::_('COM_REDSHOPB_PRICE'); ?></th>

				<?php if ($showDiscountColumn): ?>
					<th><?php echo Text::_('COM_REDSHOPB_DISCOUNT_TITLE'); ?></th>
				<?php endif; ?>
				<th><?php echo Text::_('COM_REDSHOPB_ORDER_ITEMS_FINAL_PRICE'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if (count($items)):
				foreach ($items as $item):
					$accessoryPrice = 0;
					$colspan        = 0;
					?>
					<tr>
						<td>
							<?php
							$colspan++;
							echo RedshopbHelperProduct::loadProduct($item->product_id)->name; ?><br />
							<small>(<?php echo ($item->product_item_id) ? $item->product_item_code : $item->product_sku; ?>)</small>
							<?php
							if (isset($item->accessories) && is_array($item->accessories)): ?>
								<br /><br />
								<table class="table table-condensed table-bordered">
									<?php foreach ($item->accessories as $accessory):
										$accessoryPrice += $accessory['price'];?>
										<tr><td>
												<small><?php echo '+ ' . $accessory['sku'] . ' ' . $accessory['product_name'] . ($accessory['price'] > 0 ? '(' . RedshopbHelperProduct::getProductFormattedPrice($accessory['price'], $accessory['currency'], false) . ')' : ''); ?></small>
											</td></tr>
									<?php endforeach; ?>
								</table>
							<?php endif; ?>
						</td>
						<?php if ($showAttributes):
							$colspan++; ?>
							<td>
								<table class="table-bordered table redshopb-attributes table-condensed">
									<?php foreach ($item->attributes as $attrName => $attrValue) : ?>
										<tr>
											<td><small><strong><?php echo $attrName;?></strong></small></td>
											<td><small><?php echo $attrValue->value; ?></small></td>
										</tr>
									<?php endforeach; ?>
								</table>
							</td>
						<?php endif; ?>
						<td>
							<?php
							$colspan++;
							echo $item->quantity; ?>
						</td>
						<td>
							<?php
							$colspan++;
							echo RedshopbHelperProduct::getProductFormattedPrice($item->price_without_discount, $currency, false); ?>
							<?php
							$additionalPrices = '';
							RFactory::getDispatcher()->trigger('onRedshopbOrderHistoryPrices', array($items, $item, $currency, &$additionalPrices));
							echo $additionalPrices;
							?>
						</td>
						<?php if ($showDiscountColumn):
							$colspan++;
							?>
							<td>
								<?php
								if ($item->discount)
								{
									if ($item->discount_type == 'total')
									{
										echo RedshopbHelperProduct::getProductFormattedPrice($item->discount, $currency, false);
									}
									else
									{
										echo $item->discount . '%';
									}
								}
								?>
							</td>
						<?php endif; ?>
						<td class="final-price">
							<?php
							$colspan++;
							echo RedshopbHelperProduct::getProductFormattedPrice(($item->price + $accessoryPrice) * $item->quantity, $currency, false);
							$additionalPrices = '';
							RFactory::getDispatcher()->trigger('onRedshopbOrderHistoryFinalPrices', array($items, $item, $currency, $item->quantity, $item->price, &$additionalPrices));
							echo $additionalPrices;
							?>
						</td>
					</tr>
				<?php endforeach; ?>

				<?php if ($showDiscountColumn):
					if (!isset($customerOrder->regular->discount_type))
					{
						$customerOrder->regular->discount_type = 'total';
					}

					// Order total contain taxes and shipping price, lets take away it for get product total
					if (!empty($customerOrder->shipping_rate_id))
					{
						$customerOrder->regular->total -= $customerOrder->shipping_price;
					}

					if (!empty($customerOrder->taxs))
					{
						foreach ($customerOrder->taxs as $tax)
						{
							$customerOrder->regular->total -= $tax->tax;
						}
					}

					if ($customerOrder->regular->discount_type == 'total')
					{
						$globalDiscount = $customerOrder->regular->discount;
					}
					else
					{
						$globalDiscount = $customerOrder->regular->total * $customerOrder->regular->discount / (100 - $customerOrder->regular->discount);
					}

					$sumDiscount = $subtotalWithoutDiscounts - ($customerOrder->regular->total + $globalDiscount);
					$sumFinal    = $subtotalWithoutDiscounts - $sumDiscount;
				?>
				<tr>
					<td colspan="<?php echo ($colspan - 3); ?>">
						<strong><?php
							echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SUBTOTALS'); ?> / <?php
							echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SUM_DISCOUNT'); ?> / <?php
							echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SUM_FINAL_PRICE'); ?></strong>
					</td>
					<td><?php echo RedshopbHelperProduct::getProductFormattedPrice($subtotalWithoutDiscounts, $currency, false) ?></td>
					<td><?php echo RedshopbHelperProduct::getProductFormattedPrice($sumDiscount, $currency, false) ?></td>
					<td><?php echo RedshopbHelperProduct::getProductFormattedPrice($sumFinal, $currency, false) ?></td>
				</tr>
				<?php endif; ?>

				<?php if (!$showDiscountColumn): ?>
				<tr>
					<td class="text-right" colspan="<?php echo $colspan - 1; ?>">
						<strong>
							<?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SUBTOTAL'); ?>
						</strong>
					</td>
					<td>
						<strong>
							<?php echo RedshopbHelperProduct::getProductFormattedPrice($customerOrder->regular->subtotalWithoutDiscounts, $currency) ?>
						</strong>
					</td>
				</tr>
				<?php endif; ?>

				<?php if (isset($customerOrder->regular->discount) && $customerOrder->regular->discount > 0):
					if (!isset($customerOrder->regular->discount_type))
					{
						$customerOrder->regular->discount_type = 'total';
					}

					if ($customerOrder->regular->discount_type == 'total')
					{
						$discount = RedshopbHelperProduct::getProductFormattedPrice($customerOrder->regular->discount, $currency);
					}
					else
					{
						$discount = $customerOrder->regular->discount . '%';
					}

				?>
				<tr>
					<td class="text-right" colspan="<?php echo $colspan - 1; ?>">
						<strong>
							<?php echo Text::_('COM_REDSHOPB_OFFER_GLOBAL_DISCOUNT'); ?>
						</strong>
					</td>
					<td>
						<strong>
							<?php echo $discount; ?>
						</strong>
					</td>
				</tr>
				<?php endif;

if (!empty($customerOrder->shipping_rate_id)) :
	$shippingPrice = $customerOrder->shipping_price;
	?>
	<tr>
<td class="text-right" colspan="<?php echo $colspan - 1; ?>">
<strong>
<?php echo Text::_('COM_REDSHOPB_ORDER_SHIPPING_PRICE'); ?>
</strong>
</td>
<td>
<strong>
<?php echo RedshopbHelperProduct::getProductFormattedPrice($customerOrder->shipping_price, $currency) ?>
</strong>
</td>
	</tr>
<?php endif;

if (!empty($customerOrder->taxs) && $showTaxes): ?>
					<!-- Show taxes -->
					<?php foreach ($customerOrder->taxs as $tax): ?>
						<tr>
							<td class="text-right" colspan="<?php echo $colspan - 1; ?>">
								<strong>
									<strong><?php echo $tax->name ?></strong>
									<small>(<?php echo number_format(($tax->tax_rate * 100), 2, ',', '.') ?> %)</small>
								</strong>
							</td>
							<td>
								<strong>
									<?php echo RedshopbHelperProduct::getProductFormattedPrice($tax->tax, $currency) ?>
								</strong>
							</td>
						</tr>
					<?php endforeach; ?>
<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		<tr><td colspan="<?php echo $colspan; ?>">&nbsp;</td></tr>
		<tr>
			<td colspan="<?php echo $colspan - 1; ?>" class="text-right">
				<strong>
					<p>
						<?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_TOTAL'); ?>
					</p>
				</strong>
			</td>
			<td>
				<strong>
					<?php if (count($cartTotal) > 0) : ?>
						<?php foreach ($cartTotal as $currency => $total): ?>
							<?php if ($total > 0) : ?>
								<p><?php echo RedshopbHelperProduct::getProductFormattedPrice($total, $currency); ?></p>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php else : ?>
						<p><?php echo RedshopbHelperProduct::getProductFormattedPrice(0, $currency) . '<br />'; ?></p>
					<?php endif; ?>
				</strong>
			</td>
		</tr>
		</tbody>
		</table>
	<?php endif; ?>
</div>
