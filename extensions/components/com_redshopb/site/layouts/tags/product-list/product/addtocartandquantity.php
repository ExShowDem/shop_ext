<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if (!$isShop || !$extThis->placeOrderPermission || !empty($productData->items))
{
	return;
}

$dataQty = '';

if (!empty($productData->hasVolumePricing))
{
	$dataQty  = ' data-qty_min="' . (int) $products->prices[$productId]->quantity_min . '"';
	$dataQty .= ' data-qty_max="' . (int) $products->prices[$productId]->quantity_max . '"';
}

$product = $products->items[$productId];
$maxSale = (!empty($product->max_sale)) ? 'max="' . $product->max_sale . '"' : '';

$namePostfix = $productId . '_' . $collectionId . $cartPrefix;

$minSaleValue = (!empty($product->min_sale)) ? $product->min_sale : 0;

if (isset($product->pkg_size))
{
	// If min sale is lower than pkg size, min sale must at least be equal to pkg size
	$minSaleValue = $minSaleValue < $product->pkg_size ? $product->pkg_size : $minSaleValue;
}

?>

<div class="input-append">
	<input
		type="number"
		value="<?php echo $minSaleValue; ?>"
		step="<?php echo isset($product->pkg_size) ? $product->pkg_size : 1; ?>"
		min="<?php echo $minSaleValue; ?>"
		class="amountInput<?php echo $cartPrefix; ?> quantityForOneProduct input-small"
		name="quantity_<?php echo $namePostfix ?>"
		data-product_id="<?php echo $productId ?>"
		<?php echo $maxSale; ?>
		<?php echo $dataQty;?>
	/>
	<span class="add-on unit-name" ><?php echo $unit; ?></span>
	<button
		type="button"
		class="btn btn-info add-to-cart add-to-cart-product"
		name="addtocart_<?php echo $namePostfix; ?>"
		data-product_id="<?php echo $productId ?>"
		data-price="<?php echo $price ?>"
		data-price-with-tax="<?php echo $products->prices[$productId]->price_with_tax;?>"
		data-currency="<?php echo $products->currency ?>"
		onclick="redSHOPB.shop.addToCart(event);"
	>
		<i class="icon-shopping-cart"></i>
	</button>
</div>
