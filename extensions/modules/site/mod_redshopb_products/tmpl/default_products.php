<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$sliderKey   = $module->id . '_' . $collectionId;
$cartPrefix  = '_modProducts' . $module->id;
$showStockAs = RedshopbHelperStockroom::getStockVisibility();
?>
<div class="modRedshopbProductList">
	<?php if (empty($products->items)) : ?>
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<div class="pagination-centered">
				<h3><?php echo Text::_('MOD_REDSHOPB_PRODUCTS_NOTHING_TO_DISPLAY') ?></h3>
			</div>
		</div>
	<?php else : ?>
		<div class="flexslider modProductSlider modProductSlider_<?php echo $module->id; ?>"
			 id="modBProductSlider_<?php echo $sliderKey; ?>">
			<ul class="slides">
				<?php foreach ($products->ids as $productId) :
					$price        = 0;
					$priceWithTax = 0;
					$productPrice = new stdClass;

					if (isset($products->prices[$productId]))
					{
						$price        = $products->prices[$productId]->price_without_discount;
						$productPrice = $products->prices[$productId];
						$taxRate      = $productPrice->tax_rate;
						$priceWithTax = $productPrice->price_with_tax;
					}

					$productData = $products->productData[$productId];

					$productTitle    = $productData->name;
					$productImage    = null;
					$imageRemotePath = null;

					if ($params->get('useExtraFields', 0))
					{
						$titleAlias = $params->get('titleFieldAlias');
						$imageAlias = $params->get('imageFieldAlias');

						foreach ($productData->extrafields AS $field)
						{
							if ($field->field_alias == $titleAlias)
							{
								$productTitle = $field->value;

								continue;
							}

							if ($field->field_alias == $imageAlias
								&& property_exists($field, 'field_data_params'))
							{
								$imageParams  = json_decode($field->field_data_params);
								$section      = 'field-images';
								$productImage = $imageParams->internal_url;
							}
						}
					}

					$alt = null;

					if (is_null($productImage)
						&& isset($products->productImages[$productId])
						&& !empty($products->productImages[$productId]))
					{
						$section         = 'products';
						$productImage    = $products->productImages[$productId][0]->name;
						$imageRemotePath = $products->productImages[$productId][0]->remote_path;

						if (!empty($products->productImages[$productId][0]->alt))
						{
							$alt = $products->productImages[$productId][0]->alt;
						}
					}

					$categoryId  = RedshopbHelperCategory::getUrlCategoryId($productData->categories);
					$outlet      = (isset($productPrice->outlet) && $productPrice->outlet) ? true : false;
					$productLink = RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=product&id=' . $productId . '&category_id=' . $categoryId . '&collection=' . $collectionId);
					?>
					<li class="modProductOne">
						<div class="row">
							<div class="col-md-12">
								<div class="img-center modProductImage">
									<div class="modInfoLabels">
										<?php if ($outlet): ?>
										<span class="modDiscountLabel label label-important">
											<?php echo Text::_('MOD_REDSHOPB_PRODUCTS_DISCOUNT'); ?>
										</span>
										<?php endif; ?>
									</div>
									<?php if (!is_null($productImage)) : ?>
										<a href="<?php echo $productLink ?>">
											<img src="<?php
											echo RedshopbHelperThumbnail::originalToResize($productImage, $width, $height, 100, 0, $section, false, $imageRemotePath);
											?>" alt="<?php echo RedshopbHelperThumbnail::safeAlt($alt, $productTitle) ?>"/>
										</a>
									<?php else : ?>
										<a href="<?php echo $productLink ?>">
											<?php echo RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf'); ?>
										</a>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="pull-left">
									<h4 class="modProductTitle">
										<a href="<?php echo $productLink ?>">
										<?php echo $productTitle; ?>
										</a>
									</h4>
									<?php if ($showStockAs != 'hide' && $params->get('stockInfo')) :
										if (RedshopbHelperStockroom::productHasInStock($productId))
										{
											echo '<span class="modProductInStockFlag"><i class="icon icon-circle text-success"></i><span class="availableStock">' . Text::_('COM_REDSHOPB_PRODUCT_ON_STOCK') . '</span></span>';
										}
										else
										{
											echo '<span class="modProductNoStockFlag"><i class="icon icon-circle text-error"></i><span class="availableStock">' . Text::_('COM_REDSHOPB_PRODUCT_NO_STOCK') . '</span></span>';
										}
									endif;

if ($isShop && array_key_exists($productId, $products->accessories)) :
	echo RedshopbHelperProduct::renderAccessoriesDropdown($products->accessories[$productId], $productId, $cartPrefix);
endif;

if ($isShop && array_key_exists($productId, $products->complimentaryProducts)) :
	echo RedshopbHelperProduct::renderComplimentaryProducts($products->complimentaryProducts[$productId], $productId, $cartPrefix);
endif;
									?>
									<?php if ($isShop) : ?>
									<div class="prices-block-class">
									<?php if ($isShop && $price) : ?>
										<span class="shop-category-product-price-title">
											<?php echo Text::_('COM_REDSHOPB_SHOP_PRICE') . ' (' . $products->items[$productId]->unit_measure_text . '): '; ?>
										</span>

										<?php if ($outlet): ?>
											<span class="modProductOldPrice">
											<?php echo RedshopbHelperProduct::getProductFormattedPrice($productPrice->oldPrice, $productPrice->currency); ?>
										</span>
										<?php endif; ?>
										<span class="modProductPrice" data-product_id="<?php echo $productId; ?>">
											<?php echo RedshopbHelperProduct::getProductFormattedPrice($price, $productPrice->currency); ?>

											<?php if ($taxRate && $taxRate != 0): ?>
											<div class="modProductPriceTaxLabel"><small><?php  echo Text::_('MOD_REDSHOPB_PRODUCTS_PRICE_WITHOUT_TAX'); ?></small></div>
											<?php endif; ?>
										</span>
										<?php if ($taxRate && $taxRate != 0): ?>
										<span class="modProductPriceWithTax" data-product_id="<?php echo $productId; ?>">
											<?php  echo RedshopbHelperProduct::getProductFormattedPrice($priceWithTax, $productPrice->currency); ?>
											<div class="modProductPriceTaxLabel"><small><?php  echo Text::_('MOD_REDSHOPB_PRODUCTS_PRICE_WITH_TAX'); ?></small></div>
										</span>
										<?php endif; ?>
									<?php endif; ?>
									</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="btn-group">
									<a href="<?php echo $productLink ?>"
									   class="btn btn-danger">
										<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
									</a>
									<?php if ($isShop): ?>
									<button type="button" class="btn btn-info add-to-cart add-to-cart-product"
											name="addtocart_<?php echo $productId; ?>_<?php echo $collectionId . $cartPrefix; ?>"
											data-price="<?php echo $price ?>"
											data-price-with-tax="<?php echo $priceWithTax; ?>"
											onclick="redSHOPB.shop.addToCart(event);"
											data-currency="<?php echo $products->currency ?>">
										<i class="glyphicon glyphicon-shopping-cart"></i>
									</button>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>
