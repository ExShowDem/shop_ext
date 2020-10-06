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

$customerOrders      = $displayData['customerOrders'];
$showDeliveryAddress = isset($displayData['showDeliveryAddress']) ? $displayData['showDeliveryAddress'] : false;
$hasOrderItems       = (is_array($customerOrders) && !empty($customerOrders));
$showCartHeader      = isset($displayData['showCartHeader']) ? $displayData['showCartHeader'] : false;

$style = ($displayData['isEmail']) ? 'style="text-align: right"' : '';

if (isset($displayData['renderedFrom']) && strcmp($displayData['renderedFrom'], 'receipt') === 0)
{
	$shippingPrice = $customerOrders[$displayData['orderId']]->shipping_price;
}
else
{
	$shippingPrice = !empty($displayData['shippingRateId']) ? RedshopbShippingHelper::getShippingRatePrice($displayData['shippingRateId']) : 0;

	if (!empty($displayData['shippingRateIdDelay']))
	{
		$shippingPrice += RedshopbShippingHelper::getShippingRatePrice($displayData['shippingRateIdDelay']);
	}
}

$showTaxes       = RedshopbEntityConfig::getInstance()->getInt('show_taxes_in_cart_module', 1);
$cartTotal       = array();
$hasDelayProduct = false;

if ($hasOrderItems) :
	foreach ($customerOrders as $customerOrderEntity) :
		$customerOrderSettings                           = $displayData;
		$customerOrderSettings['customerType']           = $customerOrderEntity->customerType;
		$customerOrderSettings['customerId']             = $customerOrderEntity->customerId;
		$customerOrderSettings['currency']               = $customerOrderEntity->currency;
		$customerOrderSettings['current_customer_order'] = $customerOrderEntity;

		if (!$hasDelayProduct && !empty($customerOrderEntity->regular->hasDelayProduct))
		{
			$hasDelayProduct = $customerOrderEntity->regular->hasDelayProduct;
		}

		if (!isset($customerOrderSettings['shippingPrice'])) :
			$customerOrderSettings['shippingPrice'] = 0;
		endif;

		// Counter used for display subtotals when order entities more than one for current customer
		$countCustomerSubtotals = 0;

		if (!empty($customerOrderEntity->regular->items)) :
			$countCustomerSubtotals++;
		endif;

		if (!empty($customerOrderEntity->offers)) :
			$countCustomerSubtotals += count($customerOrderEntity->offers);
		endif;

		$customerOrderSettings['countCustomerSubtotals'] = $countCustomerSubtotals;

		$customerOrderSettings['customerName'] = RedshopbHelperShop::getCustomerName(
			$customerOrderSettings['customerId'],
			$customerOrderSettings['customerType']
		);

		if (!isset($cartTotal[$customerOrderEntity->currency])) :
			$cartTotal[$customerOrderEntity->currency] = 0;
		endif;

		$cartTotal[$customerOrderEntity->currency] += $customerOrderEntity->totalFinal;

		if ($showCartHeader && !$displayData['user']->guest) :
			echo RedshopbLayoutHelper::render('order.impersonation_header', $customerOrderSettings);
		endif;

		// Display regular products
		if (isset($customerOrderEntity->regular->items) && !empty($customerOrderEntity->regular->items)) :
			$customerOrderSettings['customerOrder'] = $customerOrderEntity->regular;
			$customerOrderSettings['items']         = $customerOrderSettings['customerOrder']->items;
			$customerOrderSettings['isOffer']       = false;

			// Use for show subtotal
			if (!empty($customerOrderEntity->taxs)) :
				$customerOrderSettings['customerOrder']->taxs = $customerOrderEntity->taxs;
			endif;

			if ($showDeliveryAddress) :
				echo RedshopbLayoutHelper::render('order.delivery_address', array('current_customer_order' => $customerOrderEntity,));
			endif;

			if ($showDeliveryAddress && !empty($customerOrderEntity->ip_address)) :
				?>
				<div class="col-md-12 well">
					<h5><?php echo Text::_('COM_REDSHOPB_ORDER_CUSTOMER_IP_ADDRESS', true); ?></h5>
					<p><?php echo $customerOrderEntity->ip_address ?></p>
				</div>
				<?php
			endif;

			echo RedshopbLayoutHelper::render('checkout.products', $customerOrderSettings);
		endif;

		if (isset($customerOrderEntity->offers) && !empty($customerOrderEntity->offers)) :
			$tableIdPrefix = $customerOrderEntity->customerType . '_' . $customerOrderEntity->customerId . '_offer';

			foreach ($customerOrderEntity->offers as $offer) :
				?>
					<form class="adminForm adminFormOrder" id="adminForm_<?php echo $tableIdPrefix;?>">
				<?php
				echo RedshopbLayoutHelper::render(
					'order.offers',
					array(
						'current_customer_order' => $customerOrderEntity,
						'current_offer' => $offer,
						'lockquantity' => $displayData['lockquantity']
					)
				);

				$items                    = $offer->items;
				$total                    = $offer->total;
				$subtotalWithoutDiscounts = $offer->subtotalWithoutDiscounts;
				$customerOrder            = $offer;
				$isOffer                  = true;

				// Used to show subtotal
				if (!empty($customerOrderEntity->taxs)):
					$customerOrder->taxs = $customerOrderEntity->taxs;
				endif;

				echo RedshopbLayoutHelper::render('order.products', compact(array_keys(get_defined_vars())));
				?>
					</form>
				<?php
			endforeach;
		endif;

		if (!empty($displayData['shippingRateId'])) :
			echo RedshopbLayoutHelper::render(
				'order.shipping_rate',
				array(
					'shippingPrice' => $shippingPrice,
					'currency' => $customerOrderSettings['currency'],
					'isEmail' => $displayData['isEmail']
				)
			);
		endif;

		if (!empty($customerOrderEntity->taxs) && $showTaxes) :
			foreach ($customerOrderEntity->taxs as $tax):
				echo RedshopbLayoutHelper::render(
					'order.tax',
					array(
						'orderid' => $displayData['orderId'],
						'tax' => $tax,
						'shippingPrice' => $shippingPrice,
						'currency' => $customerOrderSettings['currency'],
						'style' => $style
					)
				);
			endforeach;
		endif;
		?>
		<hr />
		<div class="row">
			<div class="col-md-10">
				<div class="pull-right text-right">
					<strong><?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_TOTAL'); ?></strong>
				</div>
			</div>
			<div class="col-md-2 tnumber" id="totalfinal" <?php echo $style; ?>>
				<strong>
					<span>
						<?php
						if (count($cartTotal) > 0) :
							foreach ($cartTotal as $currency => $total) :
								// Shipping price already included in $total for order
								if (!$displayData['orderId']):
									if (!empty($customerOrderEntity->taxs) && $shippingPrice) :
										$shippingWithTax = 0;

										foreach ($customerOrderEntity->taxs as $tax) :
											$shippingWithTax += $tax->tax_rate * $shippingPrice;
										endforeach;

										$total += $shippingWithTax;
									endif;

									$total += $shippingPrice;
								endif;

								echo RedshopbHelperProduct::getProductFormattedPrice($total, $currency) . '<br />';
							endforeach;
						else :
							echo RedshopbHelperProduct::getProductFormattedPrice(0, '') . '<br />';
						endif;
						?>
					</span>
				</strong>
			</div>
		</div>
		<?php
	endforeach;
endif;
echo JHtmlForm::token();
?>
	<input type="hidden" id="hasDelayProduct" name="hasDelayProduct" value="<?php echo $hasDelayProduct ? 1 : 0 ?>">
	</form>
<?php
if (!$hasOrderItems):
	echo RedshopbLayoutHelper::render('common.nodata');
endif;
