<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if (!$isShop || !$isOneProduct || !$extThis->placeOrderPermission)
{
	return;
}

$dataQty = '';

if (!empty($extThis->product->items[0]->hasVolumePricing))
{
	$dataQty  = ' data-qty_min="' . (int) $extThis->product->prices[$product->id]->quantity_min . '"';
	$dataQty .= ' data-qty_max="' . (int) $extThis->product->prices[$product->id]->quantity_max . '"';
}

$minSaleValue = (!empty($product->min_sale)) ? $product->min_sale : 0;

// If min sale is lower than pkg size, min sale must at least be equal to pkg size
$minSaleValue = $minSaleValue < $product->pkg_size ? $product->pkg_size : $minSaleValue;
?>
	<input
		type="number"
		value="<?php echo $minSaleValue; ?>"

		step="<?php echo $product->pkg_size; ?>"
		min="<?php echo $minSaleValue; ?>"

		<?php if ($product->max_sale > 0): ?>
			max="<?php echo $product->max_sale; ?>"
		<?php endif; ?>
		class="input-xmini input-sm amountInput quantityForOneProduct"
		name="quantity_<?php echo $product->id . '_' . $extThis->product->collectionId . '_' . $cartPrefix; ?>"
		data-product_id="<?php echo $product->id; ?>"
		<?php echo $dataQty;?>
	/>
