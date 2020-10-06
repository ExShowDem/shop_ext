<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$orderId       = $displayData['orderid'];
$tax           = $displayData['tax'];
$shippingPrice = $displayData['shippingPrice'];
$currency      = $displayData['currency'];
$style         = $displayData['style'];


if ($shippingPrice && empty($orderId))
{
	$tax->tax += $tax->tax_rate * $shippingPrice;
}

?>
<div class="row">
	<div class="col-md-10">
		<div class="pull-right">
			<strong><?php echo $tax->name ?></strong>
			<small>(<?php echo number_format(($tax->tax_rate * 100), 2, ',', '.') ?> %)</small>
		</div>
	</div>
	<div class="col-md-2 tnumber" <?php echo $style;?>>
		<strong><?php echo RedshopbHelperProduct::getProductFormattedPrice($tax->tax, $currency) ?></strong>
	</div>
</div>
