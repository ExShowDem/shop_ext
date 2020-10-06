<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if ($isShop && $extThis->placeOrderPermission) : ?>
	<button type="button"
			class="col-md-3 btn btn-info add-to-cart add-to-cart-product"
			name="addtocart_<?php echo $productId . '_' . $collectionId . $cartPrefix; ?>"
			data-price="<?php echo $price ?>"
			data-price-with-tax="<?php echo $products->prices[$productId]->price_with_tax;?>"
			data-currency="<?php echo $products->currency ?>"
			onclick="redSHOPB.shop.addToCart(event);">
		<i class="icon-shopping-cart"></i>
	</button>
<?php endif;
