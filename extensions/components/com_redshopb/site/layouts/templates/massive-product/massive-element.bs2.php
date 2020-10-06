<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Templates.List-product.List-element.Boostrap2
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * =============================
 * @var   array  $displayData Layout variables
 * @var   array  $products    Product list
 * @var   object $productData Product data
 */
extract($displayData);

if (isset($products->prices[$productId]))
{
	$price        = $products->prices[$productId]->price_without_discount;
	$productPrice = $products->prices[$productId];
}
else
{
	$price        = 0;
	$productPrice = new stdClass;
}

$outlet             = isset($productPrice->outlet) && $productPrice->outlet;
$hasNoItems         = empty(RedshopbHelperProduct::getProductItems($productId));
$productLink        = RedshopbRoute::_(
	'index.php?option=com_redshopb&view=shop&layout=product&id=' . $productId
	. '&category_id=' . $productData->category_id . '&collection=' . $collectionId
);
$productDescription = isset($productData->description) && is_object($productData->description) ? $productData->description->description_intro : '';
$manufactureName    = RedshopbEntityManufacturer::getInstance($productData->manufacturer_id)->get('name');

if ($isShop) :
	$unit = $products->items[$productId]->unit_measure_text;
endif;

$extThis->product               = $extThis->productMassive[$collectionId];
$extThis->product->collectionId = $collectionId;
$product                        = $productData;
?>
<div class="row-fluid productList-item">
	<div class="row-fluid shop-category-product-info">
		<div class="span6">
			<h4 class="shop-category-product-title">
				<a href="<?php echo $productLink ?>"><?php echo $productData->name ?></a>
			</h4>
		</div>
		<div class="span6 text-right">
			<span class="caption"><?php echo Text::_('COM_REDSHOPB_PRODUCT_SKU') . ': ' ?></span>
			<span class="value"><?php echo $productData->sku ?></span>
			<?php if ($manufactureName) : ?>
				<div class="manufactureName">
					<span class="value"><?php echo $manufactureName ?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span3">
			<div class="img-center img-list">
				{product-list.product.image}
			</div>
		</div>
		<div class="span9">
			<div class="row-fluid">
				<div class="span4">
					{product.dropdownattributes}
				</div>
				<div class="span4">
					<div class="shop-category-product-description"><?php echo $productDescription ?></div>
					<?php $productAttributes = RedshopbHelperProduct::getAttributesAsArray($productData->id); ?>

					<?php if (empty($productAttributes)): ?>
						{product-list.product.add-to-favoritelist}
					<?php endif; ?>
					<a href="<?php echo $productLink ?>" class="btn-details">
						<?php echo Text::_('COM_REDSHOPB_DETAILS') ?>
					</a>
				</div>
				<div class="span4">
					<?php if ($hasNoItems) : ?>
						<div class="prices-block-class">
							<?php
							if ($isShop) : ?>
								<span class="shop-category-product-price-title"><?php echo Text::_('COM_REDSHOPB_SHOP_PRICE') . ' ' . $unit; ?></span>
								<span class="shop-category-old-product-price">
						<?php if ($outlet && $productPrice->oldPrice > 0): ?>
							<?php echo RedshopbHelperProduct::getProductFormattedPrice($productPrice->oldPrice, $productPrice->currency); ?>
						<?php endif; ?>
					</span>
								<?php $currency           = (!empty($products->prices[$productId]->currency)) ? $products->prices[$productId]->currency : $products->currency; ?>
								<?php $volumePricingClass = ($productData->hasVolumePricing) ? ' js-volume_pricing' : ''; ?>
								<span class="shop-category-product-price<?php echo $volumePricingClass; ?>"
									  data-product_id="<?php echo $productId; ?>"
									  data-collection_id="<?php echo $collectionId; ?>">

						<?php if ($price > 0):
							echo RedshopbHelperProduct::getProductFormattedPrice($price, $currency);
						endif; ?>
					</span>
								<?php
								$additionalPrices = '';
								RFactory::getDispatcher()->trigger('onRedshopbShopProductListPrices', array($products, $productId, 0, $currency, &$additionalPrices));
								echo $additionalPrices;
							endif;
							?>
							{product-list.product.accessorydropdown}
						</div>
						<div class="btn-group btn-group-list">
							{product-list.product.addtocartandquantity}
						</div>
					<?php else: ?>
						<?php $isOneProduct = false; ?>
						{product.addtocart}
					<?php endif; ?>
				</div>
			</div>
			<div class="row-fluid">
				{product.attributevariants}
			</div>
		</div>
	</div>
	<?php if (RedshopbApp::useRichSnippets()): ?>
		{product-list.product.snippet}
	<?php endif; ?>
</div>
