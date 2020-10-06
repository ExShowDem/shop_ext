<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$relatedProductsCount = (isset($relatedProductsCount) ? $relatedProductsCount : 12);
$relatedProducts      = RedshopbHelperTag::getRelatedProducts($product->id, $relatedProductsCount, $extThis->product->collectionId);

if (!empty($relatedProducts))
{
	$app = Factory::getApplication();
	RModelAdmin::addIncludePath(JPATH_SITE . '/components/com_redshopb/models');
	$shopModel                  = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
	$customerId                 = $app->getUserStateFromRequest('shop.customer_id', 'customer_id', 0, 'int');
	$customerType               = $app->getUserStateFromRequest('shop.customer_type', 'customer_type', '', 'string');
	$preparedItems              = $shopModel->prepareItemsForShopView($relatedProducts, $customerId, $customerType, $extThis->product->collectionId, true);
	$preparedItems->productData = $relatedProducts;
	$relatedProductsImgWidth    = (isset($relatedProductsImgWidth) ? $relatedProductsImgWidth : 200);
	$relatedProductsImgHeight   = (isset($relatedProductsImgHeight) ? $relatedProductsImgHeight : 200);
	$relatedProductsItemMargin  = (isset($relatedProductsItemMargin) ? $relatedProductsItemMargin : 19);
	$doc                        = Factory::getDocument();
	$doc->addStyleDeclaration(
		'.relatedProductSlider_' . $product->id . ' .slides .relatedProductImage {
			min-height: ' . $relatedProductsImgHeight . 'px;
		}
		.relatedProductSlider_' . $product->id . ' .slides li {
			margin-right: ' . $relatedProductsItemMargin . 'px;
		}
	'
	);
	$cartPrefix               = '_relatedProduct' . $product->id;
	$relatedProductsDirection = (isset($relatedProductsDirection) ? $relatedProductsDirection : 'horizontal');
	$relatedProductsMinItems  = (int) (isset($relatedProductsMinItems) ? $relatedProductsMinItems : 2);
	$flexsliderOptions        = array(
		'slideshow' => (bool) (isset($relatedProductsSlideShow) ? $relatedProductsSlideShow : 1),
		'directionNav' => (bool) (isset($relatedProductsDirectionNav) ? $relatedProductsDirectionNav : 1),
		'controlNav' => (bool) (isset($relatedProductsControlNav) ? $relatedProductsControlNav : 0),
		'animation' => 'slide',
		'animationLoop' => (bool) (isset($relatedProductsAnimationLoop) ? $relatedProductsAnimationLoop : 0),
		'itemWidth' => (int) (isset($relatedProductsItemWidth) ? $relatedProductsItemWidth : $relatedProductsImgWidth),
		'itemMargin' => (int) $relatedProductsItemMargin,
		'minItems' => $relatedProductsMinItems,
		'maxItems' => (int) (isset($relatedProductsMaxItems) ? $relatedProductsMaxItems : 6),
		'direction' => isset($accessoriesProductsDirection) ? $accessoriesProductsDirection : 'horizontal'
	);

	if ($relatedProductsDirection != 'vertical')
	{
		$flexsliderOptions['itemWidth'] = (int) (isset($relatedProductsItemWidth) ? $relatedProductsItemWidth : $relatedProductsImgWidth);
	}

	HTMLHelper::_('rjquery.flexslider', '#relatedProductSlider_' . $product->id, $flexsliderOptions);
	$showStockAs   = RedshopbHelperStockroom::getStockVisibility();
	$itemsPerSlide = 0;
	?>
	<div class="flexslider relatedProductSlider relatedProductSlider_<?php echo $product->id; ?>"
		 id="relatedProductSlider_<?php echo $product->id; ?>">
		<ul class="slides">
			<?php foreach ($preparedItems->ids as $productId) :
				$productData = $preparedItems->productData[$productId];
				$categoryId  = RedshopbHelperCategory::getUrlCategoryId($productData->categories);
				$link        = RedshopbRoute::_(
					'index.php?option=com_redshopb&view=shop&layout=product&id=' . $productId . '&category_id=' . $categoryId
					. '&collection=' . $extThis->product->collectionId
				);

				if (isset($preparedItems->prices[$productId]))
				{
					$price        = $preparedItems->prices[$productId]->price_without_discount;
					$productPrice = $preparedItems->prices[$productId];
					$taxRate      = $productPrice->tax_rate;
					$priceWithTax = $productPrice->price_with_tax;
				}
				else
				{
					$price        = 0;
					$productPrice = new stdClass;
					$taxRate      = 0;
					$priceWithTax = 0;
				}

				$outlet = (isset($productPrice->outlet) && $productPrice->outlet) ? true : false;
				$unit   = $preparedItems->items[$productId]->unit_measure_text;

				if ($relatedProductsDirection == 'vertical')
				{
					if ($itemsPerSlide == 1)
					{
						echo '<li class="relatedProductOne">';
					}
				}
				else
				{
					echo '<li class="relatedProductOne">';
				}
				?>
					<div class="row">
						<div class="col-md-12">
							<div class="img-center relatedProductImage">
								<div class="relatedInfoLabels">
									<?php if ($outlet): ?>
										<span class="relatedDiscountLabel label label-important">
											<?php echo Text::_('COM_REDSHOPB_DISCOUNT_TITLE'); ?>
										</span>
									<?php endif; ?>
								</div>
								<?php if (isset($preparedItems->productImages[$productId]) && !empty($preparedItems->productImages[$productId])) : ?>
									<a href="<?php echo $link ?>">
									<img src="<?php
									echo RedshopbHelperThumbnail::originalToResize($preparedItems->productImages[$productId][0]->name, $relatedProductsImgWidth, $relatedProductsImgHeight, 100, 0, 'products', false, $preparedItems->productImages[$productId][0]->remote_path);
									?>" alt="<?php echo RedshopbHelperThumbnail::safeAlt($preparedItems->productImages[$productId][0]->alt, $productData->name) ?>"/>
									</a>
								<?php else : ?>
									<a href="<?php echo $link ?>">
									<?php echo RedshopbHelperMedia::drawDefaultImg($relatedProductsImgWidth, $relatedProductsImgHeight, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf'); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="pull-left">
								<h4 class="relatedProductTitle">
									<a href="<?php echo $link ?>">
									<?php echo $productData->name; ?>
								</a>
								</h4>
								<?php if ($showStockAs != 'hide') :
									if (RedshopbHelperStockroom::productHasInStock($productId))
									{
										echo '<span class="relatedProductInStockFlag"><i class="icon icon-circle text-success"></i><span class="availableStock">' . Text::_('COM_REDSHOPB_PRODUCT_ON_STOCK') . '</span></span>';
									}
									else
									{
										echo '<span class="relatedProductNoStockFlag"><i class="icon icon-circle text-error"></i><span class="availableStock">' . Text::_('COM_REDSHOPB_PRODUCT_NO_STOCK') . '</span></span>';
									}
								endif;

if ($isShop && array_key_exists($productId, $preparedItems->accessories)) :
	echo RedshopbHelperProduct::renderAccessoriesDropdown($preparedItems->accessories[$productId], $productId, $cartPrefix);
endif;

if ($isShop && array_key_exists($productId, $preparedItems->complimentaryProducts)) :
	echo RedshopbHelperProduct::renderComplimentaryProducts($preparedItems->complimentaryProducts[$productId], $productId, $cartPrefix);
endif;
								?>
								<div class="prices-block-class">
								<?php if ($isShop && $price) : ?>
									<span class="relatedProductTitle-price-title">
										<?php echo Text::_('COM_REDSHOPB_SHOP_PRICE'),' ', $unit; ?>
									</span>
									<?php if ($outlet): ?>
										<span class="relatedProductOldPrice">
											<?php echo RedshopbHelperProduct::getProductFormattedPrice($productPrice->oldPrice, $productPrice->currency); ?>
										</span>
									<?php endif; ?>
									<span class="relatedProductPrice">
										<?php echo RedshopbHelperProduct::getProductFormattedPrice($price, $productPrice->currency); ?>

										<?php if ($taxRate && $taxRate != 0 && $price > 0): ?>
											<div class="relatedProductPriceTaxLabel"><small><?php  echo Text::_('COM_REDSHOPB_PRODUCTS_PRICE_WITHOUT_TAX'); ?></small></div>
										<?php endif; ?>
									</span>
									<?php if ($taxRate && $taxRate != 0 && $price > 0): ?>
										<span class="relatedProductPriceWithTax" data-product_id="<?php echo $productId; ?>">
											<?php  echo RedshopbHelperProduct::getProductFormattedPrice($priceWithTax, $productPrice->currency); ?>
											<div class="relatedProductPriceTaxLabel"><small><?php  echo Text::_('COM_REDSHOPB_PRODUCTS_PRICE_WITH_TAX'); ?></small></div>
										</span>
									<?php endif; ?>
								<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="btn-group">
								<a href="<?php echo $link ?>"
								   class="btn btn-danger">
									<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
								</a>
							</div>
						</div>
					</div>
			<?php
			if ($relatedProductsDirection == 'vertical')
				{
				if ($itemsPerSlide >= $accessoriesProductsMinItems)
				{
					$itemsPerSlide = 0;
					echo '</li>';
				}
			}
			else
				{
				echo '</li>';
			}
			endforeach; ?>
		</ul>
	</div>
<?php
}
