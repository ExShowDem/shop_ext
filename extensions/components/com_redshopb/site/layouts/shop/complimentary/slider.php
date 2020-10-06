<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Extracted
 *
 * @var $displayData
 * @var $products
 * @var $id
 * @var $extThis
 * @var $hidePrices
 * @var $cartPrefix
 */

extract($displayData);

if (empty($products))
{
	return;
}

$hidePrices = !RedshopbHelperPrices::displayPrices();
$newOrder   = array();

foreach ($products as $complimentary)
{
	$complimentary->id                                  = $complimentary->complimentary_product_id;
	$newOrder[$complimentary->complimentary_product_id] = $complimentary;
}

$app = Factory::getApplication();
RModelAdmin::addIncludePath(JPATH_SITE . '/components/com_redshopb/models');
$shopModel                  = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
$customerId                 = $app->getUserStateFromRequest('shop.customer_id', 'customer_id', 0, 'int');
$customerType               = $app->getUserStateFromRequest('shop.customer_type', 'customer_type', '', 'string');
$preparedItems              = $shopModel->prepareItemsForShopView($newOrder, $customerId, $customerType, $extThis->product->collectionId, true);
$preparedItems->productData = $newOrder;

$complimentaryProductsImgWidth   = (isset($complimentaryProductsImgWidth) ? $complimentaryProductsImgWidth : 200);
$complimentaryProductsImgHeight  = (isset($complimentaryProductsImgHeight) ? $complimentaryProductsImgHeight : 200);
$complimentaryProductsItemMargin = (isset($complimentaryProductsItemMargin) ? $complimentaryProductsItemMargin : 19);
$doc                             = Factory::getDocument();
$doc->addStyleDeclaration(
	'.complimentaryProductsSlider_' . $id . ' .slides .complimentaryImage {
			min-height: ' . $complimentaryProductsImgHeight . 'px;
		}
		.complimentaryProductsSlider_' . $id . ' .slides li {
			margin-right: ' . $complimentaryProductsItemMargin . 'px;
		}
	'
);

$cartPrefixComplimentary        = '_complimentaryProduct' . $id;
$complimentaryProductsDirection = (isset($complimentaryProductsDirection) ? $complimentaryProductsDirection : 'horizontal');
$complimentaryProductsMinItems  = (int) (isset($complimentaryProductsMinItems) ? $complimentaryProductsMinItems : 2);
$flexsliderOptions              = array(
	'slideshow' => (bool) (isset($complimentaryProductsSlideShow) ? $complimentaryProductsSlideShow : 1),
	'directionNav' => (bool) (isset($complimentaryProductsDirectionNav) ? $complimentaryProductsDirectionNav : 1),
	'controlNav' => (bool) (isset($complimentaryProductsControlNav) ? $complimentaryProductsControlNav : 0),
	'animation' => 'slide',
	'animationLoop' => (bool) (isset($complimentaryProductsAnimationLoop) ? $complimentaryProductsAnimationLoop : 0),
	'itemMargin' => (int) $complimentaryProductsItemMargin,
	'minItems' => $complimentaryProductsMinItems,
	'maxItems' => (int) (isset($complimentaryProductsMaxItems) ? $complimentaryProductsMaxItems : 6),
	'direction' => $complimentaryProductsDirection,
	'pauseOnHover' => true
);

if ($complimentaryProductsDirection != 'vertical')
{
	$flexsliderOptions['itemWidth'] = (int) (isset($complimentaryProductsItemWidth) ? $complimentaryProductsItemWidth : $complimentaryProductsImgWidth);
}

HTMLHelper::_('rjquery.flexslider', '#complimentaryProductsSlider_' . $id, $flexsliderOptions);
$showStockAs   = RedshopbHelperStockroom::getStockVisibility();
$itemsPerSlide = 0;

?>
	<div class="sliderProductComplimentaryProducts">
	<div class="flexslider complimentaryProductsSlider complimentaryProductsSlider_<?php echo $id; ?>"
		 id="complimentaryProductsSlider_<?php echo $id; ?>">
		<ul class="slides"><?php

		foreach ($preparedItems->ids as $productId)
		{
			$productData = $preparedItems->productData[$productId];
			$link        = '';
			$itemsPerSlide++;

			if (!$productData->service)
			{
				$categoryId = RedshopbHelperCategory::getUrlCategoryId($productData->categories);
				$link       = RedshopbRoute::_(
					'index.php?option=com_redshopb&view=shop&layout=product&id=' . $productId . '&category_id=' . $categoryId
					. '&collection=' . $extThis->product->collectionId
				);
			}

			if (!$hidePrices && isset($preparedItems->prices[$productId]))
			{
				$taxRate      = $preparedItems->prices[$productId]->tax_rate;
				$price        = $preparedItems->prices[$productId]->price;
				$priceWithTax = $preparedItems->prices[$productId]->price_with_tax;
			}
			else
			{
				$price        = 0;
				$taxRate      = 0;
				$priceWithTax = 0;
			}

			$unit    = $preparedItems->items[$productId]->unit_measure_text;
			$imgPath = '';

			if (isset($preparedItems->productImages[$productId]) && !empty($preparedItems->productImages[$productId]))
			{
				$imgPath = RedshopbHelperThumbnail::originalToResize(
					$preparedItems->productImages[$productId][0]->name, $complimentaryProductsImgWidth,
					$complimentaryProductsImgHeight, 100, 0, 'products', false, $preparedItems->productImages[$productId][0]->remote_path
				);
			}

			if (!$imgPath)
			{
				$img = RedshopbHelperMedia::drawDefaultImg(
					$complimentaryProductsImgWidth, $complimentaryProductsImgHeight,
					Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf'
				);
			}
			else
			{
				$img = '<img src="' . $imgPath . '" alt="'
					. RedshopbHelperThumbnail::safeAlt($preparedItems->productImages[$productId][0]->alt, $productData->name) . '"/>';
			}

			if ($complimentaryProductsDirection == 'vertical')
			{
				if ($itemsPerSlide == 1)
				{
					echo '<li class="complimentaryProductOne">';
				}
			}
			else
			{
				echo '<li class="complimentaryProductOne">';
			}

			?>
			<div class="oneComplimentaryProduct">
			<div class="row">
				<div class="col-md-12">
					<div class="img-center complimentaryImage">
						<?php if ($link): ?>
								<a href="<?php echo $link ?>">
						<?php endif;
							echo $img;

if ($link): ?>
								</a>
<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="pull-left">
								<h4 class="complimentaryProductTitle">
									<a href="<?php echo $link ?>">
										<?php echo $productData->name; ?>
									</a>
								</h4>
								<?php if (!empty($preparedItems->items[$productId]->description->description_intro)): ?>
								<div class="complimentaryDescription">
									<?php echo $preparedItems->items[$productId]->description->description_intro ?>
								</div>
								<?php endif; ?>

								<?php if ($showStockAs != 'hide') :
									if (RedshopbHelperStockroom::productHasInStock($productId))
									{
										echo '<span class="complimentaryProductInStockFlag"><i class="icon icon-circle text-success"></i><span class="availableStock">' . Text::_('COM_REDSHOPB_PRODUCT_ON_STOCK') . '</span></span>';
									}
									else
									{
										echo '<span class="complimentaryProductNoStockFlag"><i class="icon icon-circle text-danger"></i><span class="availableStock">' . Text::_('COM_REDSHOPB_PRODUCT_NO_STOCK') . '</span></span>';
									}
								endif;
						?>
						<div class="prices-block-class">
							<?php if ($isShop && $price > 0) : ?>
									<span class="complimentaryProductTitle-price-title">
										<?php echo Text::_('COM_REDSHOPB_SHOP_PRICE'), ' ', $unit; ?>
									</span>
									<span class="complimentaryProductPrice">
										<?php echo RedshopbHelperProduct::getProductFormattedPrice($price, $preparedItems->currency); ?>

											<?php if ($taxRate > 0): ?>
												<div class="complimentaryProductPriceTaxLabel"><small><?php echo Text::_('COM_REDSHOPB_PRODUCTS_PRICE_WITHOUT_TAX'); ?></small></div>
											<?php endif; ?>
									</span>
										<?php if ($taxRate > 0): ?>
									<span class="complimentaryProductPriceWithTax"
										  data-product_id="<?php echo $productId; ?>">
										<?php echo RedshopbHelperProduct::getProductFormattedPrice($priceWithTax, $preparedItems->currency); ?>
										<div class="complimentaryProductPriceTaxLabel"><small><?php echo Text::_('COM_REDSHOPB_PRODUCTS_PRICE_WITH_TAX'); ?></small></div>
									</span>
										<?php endif; ?>
							<?php endif; ?>
							</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="controls">
								<div class="input-group">
									<?php if ($link): ?>
										<a href="<?php echo $link ?>"
										   class="btn btn-danger">
											<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
										</a>
									<?php endif; ?>

									<?php if ($isShop):
										$minSaleValue = (!empty($productData->min_sale)) ? $productData->min_sale : 0;

										if (isset($product->pkg_size))
										{
											// If min sale is lower than pkg size, min sale must at least be equal to pkg size
											$minSaleValue = $minSaleValue < $productData->pkg_size ? $productData->pkg_size : $minSaleValue;
										}

									?>
										<input
											type="number"
											class="form-control bfh-number input-mini quantityComplimentary_<?php echo $id . $cartPrefixComplimentary . '_' . $productData->complimentary_id ?>"
											value="<?php echo $minSaleValue; ?>"
											step="<?php echo isset($productData->pkg_size) ? $productData->pkg_size : 1; ?>"
											min="<?php echo $minSaleValue; ?>"
											name="quantity_<?php echo $productId ?>_<?php echo $extThis->product->collectionId . $cartPrefixComplimentary ?>"
										/>
										<button type="button" class="btn btn-info add-to-cart add-to-cart-product"
												name="addtocart_<?php echo $productId; ?>_<?php
												echo $extThis->product->collectionId . $cartPrefixComplimentary; ?>"
												data-price="<?php echo $price ?>"
												onclick="redSHOPB.shop.addToCart(event);"
												data-price-with-tax="<?php echo $priceWithTax; ?>"
												data-currency="<?php echo $preparedItems->currency ?>">
											<i class="icon-shopping-cart"></i>
										</button>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				if ($complimentaryProductsDirection == 'vertical')
				{
					if ($itemsPerSlide >= $complimentaryProductsMinItems)
					{
						$itemsPerSlide = 0;
						echo '</li>';
					}
				}
				else
				{
					echo '</li>';
				}
		}
			?></ul>
	</div>
	</div><?php
