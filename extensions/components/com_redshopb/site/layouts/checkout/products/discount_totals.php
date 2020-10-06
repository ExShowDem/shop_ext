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

$customerOrder            = $displayData['customerOrder'];
$currency                 = $displayData['currency'];
$shippingPrice            = $displayData['shippingPrice'];
$subtotalWithoutDiscounts = $displayData['subtotalWithoutDiscounts'];
$colspan                  = $displayData['colspan'];
$isFromOrder              = $displayData['isFromOrder'];

if (!isset($customerOrder->discount_type))
{
	$customerOrder->discount_type = 'total';
}

if (!isset($customerOrder->taxs) || !is_array($customerOrder->taxs))
{
	$customerOrder->taxs = array();
}

// Order total contain taxes and shipping price, lets take away it for get product total

if ($isFromOrder)
{
	$customerOrder->total -= $shippingPrice;

	foreach ($customerOrder->taxs as $tax)
	{
		$customerOrder->total -= $tax->tax;
	}
}

$globalDiscount = $customerOrder->discount;

if ($customerOrder->discount_type != 'total')
{
	$globalDiscount = $customerOrder->total * $customerOrder->discount / (100 - $customerOrder->discount);
}

$sumDiscount = $subtotalWithoutDiscounts - ($customerOrder->total + $globalDiscount);
$sumFinal    = $subtotalWithoutDiscounts - $sumDiscount;
?>

<tr>
	<td colspan="<?php echo ($colspan - 4); ?>">
		<strong><?php
			echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SUBTOTALS'); ?> / <?php
			echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SUM_DISCOUNT'); ?> / <?php
			echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_SUM_FINAL_PRICE'); ?></strong>
	</td>
	<td><?php echo RedshopbHelperProduct::getProductFormattedPrice($subtotalWithoutDiscounts, $currency, false) ?></td>
	<td><?php echo RedshopbHelperProduct::getProductFormattedPrice($sumDiscount, $currency, false) ?></td>
	<td><?php echo RedshopbHelperProduct::getProductFormattedPrice($sumFinal, $currency, false) ?></td>
	<td></td>
</tr>

