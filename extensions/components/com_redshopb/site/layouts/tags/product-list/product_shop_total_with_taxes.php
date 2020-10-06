<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$productsPrice = 0.0;

foreach ($products->items as $item)
{
	$quantity       = floatval($item->pkg_size);
	$taxedPrice     = floatval($products->prices[$item->id]->price_with_tax);
	$productPrice   = $quantity * $taxedPrice;
	$productsPrice += $productPrice;
}

?>

<span id="productsShopTotalWithTaxes" class="products-shop-total-with-taxes">
	<?php echo RedshopbHelperProduct::getProductFormattedPrice($productsPrice, $products->currency); ?>
</span>
