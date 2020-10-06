<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if (!$isShop || !$isOneProduct)
{
	return;
}

$price     = (isset($extThis->product->prices[$product->id]) ? $extThis->product->prices[$product->id]->fallback_price : '');
$hidePrice = false;
RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$price, &$hidePrice, 0, $product->id));

if ($hidePrice)
{
	return;
}

$displayPrice = RedshopbHelperProduct::getProductFormattedPrice(
	(float) $price,
	$extThis->product->currency
);

?>
<span class="fallbackprice">
	<?php echo $displayPrice;?>
</span>
