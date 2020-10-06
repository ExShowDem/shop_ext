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
	$price          = floatval($products->prices[$item->id]->price);
	$productPrice   = $quantity * $price;
	$productsPrice += $productPrice;
}

?>

<span id="productsShopTotal" class="products-shop-total">
	<?php echo RedshopbHelperProduct::getProductFormattedPrice($productsPrice, $products->currency); ?>
</span>
