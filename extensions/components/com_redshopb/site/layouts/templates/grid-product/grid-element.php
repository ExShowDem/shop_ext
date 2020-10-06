<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Layouts.Templates.Grid-product.Grid-element
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

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

$outlet             = (isset($productPrice->outlet) && $productPrice->outlet) ? true : false;
$hasNoItems         = empty(RedshopbHelperProduct::getProductItems($productId));
$productLink        = RedshopbRoute::_(
	'index.php?option=com_redshopb&view=shop&layout=product&id=' . $productId
	. '&category_id=' . $productData->category_id . '&collection=' . $collectionId
);
$manufactureName    = RedshopbEntityManufacturer::getInstance($productData->manufacturer_id)->get('name');
$volumePricingClass = ($productData->hasVolumePricing) ? ' js-volume_pricing' : '';
?>
<div class="col-md-<?php echo $spanSize ?>">
	<div class="img-center redshopb-product-image">
		{product-list.product.image}
	</div>
	<div>
		<h4 class="shop-category-product-title">
			<a href="<?php echo $productLink ?>">
				<?php echo $productData->name ?>
			</a>
		</h4>
		<?php if ($manufactureName) : ?>
			<div class="manufactureName"><?php echo $manufactureName ?></div>
		<?php endif; ?>

		<?php if ($isShop) : ?>
			<div class="prices-block-class">
				<span class="shop-category-product-price-title">
					<?php echo Text::_('COM_REDSHOPB_SHOP_PRICE') . ' (' . $products->items[$productId]->unit_measure_text . '): '; ?>
				</span>
				<span class="shop-category-old-product-price">
					<?php
					if ($outlet && $productPrice->oldPrice > 0) :
						echo RedshopbHelperProduct::getProductFormattedPrice($productPrice->oldPrice, $productPrice->currency);
					endif;
					?>
				</span>
				<?php $currency = ($products->prices[$productId]->currency) ? $products->prices[$productId]->currency : $products->currency; ?>
				<span class="shop-category-product-price<?php echo $volumePricingClass; ?>"
					data-product_id="<?php echo $productId; ?>"
					data-collection_id="<?php echo $collectionId; ?>">
					<?php
					if ($price > 0) :
						echo RedshopbHelperProduct::getProductFormattedPrice($price, $currency);
					endif;
					?>
					</span>
				<?php
				$additionalPrices = '';
				RFactory::getDispatcher()->trigger('onRedshopbShopProductListPrices', array($products, $productId, 0, $currency, &$additionalPrices));
				echo $additionalPrices;
				?>
			</div>
		<?php endif; ?>
		{product-list.product.accessorydropdown}
	</div>
	<div class="container-fluid text-center">
		<?php $productAttributes = RedshopbHelperProduct::getAttributesAsArray($productData->id); ?>

		<?php if (empty($productAttributes)): ?>
			{product-list.product.add-to-favoritelist}
		<?php endif; ?>
		<a href="<?php
		echo $productLink ?>"
		class="btn col-md-9 btn-danger">
			<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
		</a>
		<?php if ($hasNoItems) : ?>
			<div class="btn-group btn-group-list">
				{product-list.product.addtocartandquantity}
			</div>
		<?php else : ?>
			{product-list.product.addtocartvariants}
		<?php endif; ?>
	</div>
	<?php if (RedshopbApp::useRichSnippets()) : ?>
		{product-list.product.snippet}
	<?php endif; ?>
</div>
