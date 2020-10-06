<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * @var array    $displayData
 * @var stdClass $product
 */
extract($displayData);

$productDescription = isset($product->description) && is_object($product->description)
	? $product->description->description_intro
	: '';

$productData = $product;
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><em class="icon-remove"></em></button>
</div>
<div class="modal-body">
	<div class="row">
		<div class="col-md-4">
			{product-list.product.image}
		</div>
		<div class="col-md-8">
			<div class="flex items-baseline justify-between">
				<h1>
					<?php echo $product->name ?>
				</h1>
				<div class="product-sku pull-right">
					<span class="caption"><?php echo Text::_('COM_REDSHOPB_PRODUCT_SKU') . ': '; ?></span>
					<span class="value"><?php echo  $product->sku; ?></span>
				</div>
			</div>
			<div class="shop-category-product-description"> <?php echo $productDescription; ?></div>

			{product.attributeoptions}

			{product.attributevariants}

			<div class="flex items-baseline justify-between">
				<div class="shop-category-product-price<?php echo $volumePricingClass; ?>"
					 data-product_id="<?php echo $product->id; ?>"
					 data-collection_id="<?php echo $collectionId; ?>">
					<?php
					if (is_object($price) && $price->price > 0) :
						echo RedshopbHelperProduct::getProductFormattedPrice($price->price, $currency);
					endif;
					?>
				</div>
				<button type="button"
						class="btn btn-success add-to-cart"
						name="addtocart_<?php echo $product->id . '_' . $collectionId . $cartPrefix; ?>"
						data-price="<?php echo $price->price ?>"
						data-price-with-tax="<?php echo $price->price_with_tax;?>"
						data-currency="<?php echo $currency ?>"
						onclick="redSHOPB.shop.addToCart(event); jQuery('.modalVariants').modal('hide');"
				>
					<?php echo Text::_('COM_REDSHOPB_SHOP_ADD_TO_CART'); ?>
				</button>
			</div>
		</div>
	</div>
</div>
