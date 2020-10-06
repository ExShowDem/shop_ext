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
 * @var $accessories
 * @var $id
 * @var $extThis
 * @var $hidePrices
 */

extract($displayData);

if (empty($accessories))
{
	return;
}

if (!$hidePrices)
{
	$hidePrices = !RedshopbHelperPrices::displayPrices();
}

$newOrder = array();

foreach ($accessories as $accessory)
{
	if ($accessory->collection_id
		&& $accessory->hide_on_collection == 1)
	{
		continue;
	}

	$accessory->id                              = $accessory->accessory_product_id;
	$accessory->accessory_description           = $accessory->description;
	$newOrder[$accessory->accessory_product_id] = $accessory;
}

$app = Factory::getApplication();
RModelAdmin::addIncludePath(JPATH_SITE . '/components/com_redshopb/models');
$shopModel                  = RModelAdmin::getInstance('Shop', 'RedshopbModel', array('ignore_request' => true));
$customerId                 = $app->getUserStateFromRequest('shop.customer_id', 'customer_id', 0, 'int');
$customerType               = $app->getUserStateFromRequest('shop.customer_type', 'customer_type', '', 'string');
$preparedItems              = $shopModel->prepareItemsForShopView($newOrder, $customerId, $customerType, $extThis->product->collectionId, true);
$preparedItems->productData = $newOrder;

$accessoriesProductsImgWidth   = (isset($accessoriesProductsImgWidth) ? $accessoriesProductsImgWidth : 200);
$accessoriesProductsImgHeight  = (isset($accessoriesProductsImgHeight) ? $accessoriesProductsImgHeight : 200);
$accessoriesProductsItemMargin = (isset($accessoriesProductsItemMargin) ? $accessoriesProductsItemMargin : 19);
$doc                           = Factory::getDocument();
$doc->addStyleDeclaration(
	'.accessoriesProductSlider_' . $id . ' .slides .accessoryProductImage {
			min-height: ' . $accessoriesProductsImgHeight . 'px;
		}
		.accessoriesProductSlider_' . $id . ' .slides li {
			margin-right: ' . $accessoriesProductsItemMargin . 'px;
		}
	'
);

$cartPrefixAccessory          = '_accessoriesProduct' . $id;
$accessoriesProductsDirection = (isset($accessoriesProductsDirection) ? $accessoriesProductsDirection : 'horizontal');
$accessoriesProductsMinItems  = (int) (isset($accessoriesProductsMinItems) ? $accessoriesProductsMinItems : 2);
$flexsliderOptions            = array(
	'slideshow' => (bool) (isset($accessoriesProductsSlideShow) ? $accessoriesProductsSlideShow : 1),
	'directionNav' => (bool) (isset($accessoriesProductsDirectionNav) ? $accessoriesProductsDirectionNav : 1),
	'controlNav' => (bool) (isset($accessoriesProductsControlNav) ? $accessoriesProductsControlNav : 0),
	'animation' => 'slide',
	'animationLoop' => (bool) (isset($accessoriesProductsAnimationLoop) ? $accessoriesProductsAnimationLoop : 0),
	'itemMargin' => (int) $accessoriesProductsItemMargin,
	'minItems' => $accessoriesProductsMinItems,
	'maxItems' => (int) (isset($accessoriesProductsMaxItems) ? $accessoriesProductsMaxItems : 6),
	'direction' => $accessoriesProductsDirection,
	'pauseOnHover' => true
);

if ($accessoriesProductsDirection != 'vertical')
{
	$flexsliderOptions['itemWidth'] = (int) (isset($accessoriesProductsItemWidth) ? $accessoriesProductsItemWidth : $accessoriesProductsImgWidth);
}

HTMLHelper::_('rjquery.flexslider', '#accessoriesProductSlider_' . $id, $flexsliderOptions);
$showStockAs   = RedshopbHelperStockroom::getStockVisibility();
$itemsPerSlide = 0;

$taxes         = RedshopbHelperTax::getProductsTaxRates(array_keys($newOrder));
$globalTaxRate = 0;

if (array_key_exists(0, $taxes) && !empty($taxes[0]))
{
	foreach ($taxes[0] as $taxRateData)
	{
		$globalTaxRate += $taxRateData->tax_rate;
	}
}

foreach ($newOrder as $productId => $accessory)
{
	$accessory->tax_rate = $globalTaxRate;

	if (array_key_exists($productId, $taxes) && !empty($taxes[$productId]))
	{
		foreach ($taxes[$productId] as $taxRateData)
		{
			$accessory->tax_rate += $taxRateData->tax_rate;
		}
	}

	$accessory->price_with_tax = $accessory->price;

	if ($accessory->tax_rate)
	{
		$accessory->price_with_tax += $accessory->price * $accessory->tax_rate;
	}
}

?>
<div class="sliderProductAccessories">
	<div class="flexslider accessoriesProductSlider accessoriesProductSlider_<?php echo $id; ?>"
		 id="accessoriesProductSlider_<?php echo $id; ?>">
		<ul class="slides">
			<?php
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

				if (!$hidePrices && isset($productData->price) && $productData->price > 0)
				{
					$price        = $productData->price;
					$taxRate      = $productData->tax_rate;
					$priceWithTax = $productData->price_with_tax;
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
						$preparedItems->productImages[$productId][0]->name, $accessoriesProductsImgWidth,
						$accessoriesProductsImgHeight, 100, 0, 'products', false, $preparedItems->productImages[$productId][0]->remote_path
					);
				}

				if (!$imgPath)
				{
					$img = RedshopbHelperMedia::drawDefaultImg(
						$accessoriesProductsImgWidth, $accessoriesProductsImgHeight,
						Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf'
					);
				}
				else
				{
					$img = '<img src="' . $imgPath . '"/>';
				}

				if ($accessoriesProductsDirection == 'vertical')
				{
					if ($itemsPerSlide == 1)
					{
						echo '<li class="accessoryProductOne">';
					}
				}
				else
				{
					echo '<li class="accessoryProductOne">';
				}

				?>
				<div class="oneAccessoryProduct">
					<div class="row-fluid">
						<div class="span12">
							<div class="img-center accessoryProductImage">
								<?php if ($link): ?>
									<a href="<?php echo $link ?>">
								<?php endif; ?>

									<?php echo $img; ?>

									<?php if ($link): ?>
									</a>
									<?php endif; ?>
							</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<div class="pull-left">
								<h4 class="accessoryProductTitle">
									<a href="<?php echo $link ?>">
										<?php echo $productData->name; ?>
									</a>
								</h4>
								<?php if ($productData->accessory_description): ?>
									<div class="accessoryDescription">
										<?php echo $productData->accessory_description ?>
									</div>
								<?php endif; ?>

								<?php if ($showStockAs != 'hide') :
									if (RedshopbHelperStockroom::productHasInStock($productId))
									{
										echo '<span class="accessoryProductInStockFlag"><i class="icon icon-circle text-success"></i><span class="availableStock">' . Text::_('COM_REDSHOPB_PRODUCT_ON_STOCK') . '</span></span>';
									}
									else
									{
										echo '<span class="accessoryProductNoStockFlag"><i class="icon icon-circle text-error"></i><span class="availableStock">' . Text::_('COM_REDSHOPB_PRODUCT_NO_STOCK') . '</span></span>';
									}
								endif;
								?>
								<div class="prices-block-class">
									<?php if ($isShop && $price > 0) : ?>
										<span class="accessoryProductTitle-price-title">
											<?php echo Text::_('COM_REDSHOPB_SHOP_PRICE'), ' ', $unit; ?>
										</span>
										<span class="accessoryProductPrice">
										<?php echo RedshopbHelperProduct::getProductFormattedPrice($price, $productData->currency); ?>

											<?php if ($taxRate > 0): ?>
												<div class="accessoryProductPriceTaxLabel"><small><?php echo Text::_('COM_REDSHOPB_PRODUCTS_PRICE_WITHOUT_TAX'); ?></small></div>
											<?php endif; ?>
									</span>
									<?php if ($taxRate > 0): ?>
										<span class="accessoryProductPriceWithTax"
										data-product_id="<?php echo $productId; ?>">
										<?php echo RedshopbHelperProduct::getProductFormattedPrice($priceWithTax, $productData->currency); ?>
										<div class="accessoryProductPriceTaxLabel"><small><?php echo Text::_('COM_REDSHOPB_PRODUCTS_PRICE_WITH_TAX'); ?></small></div>
									</span>
									<?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<div class="controls">
								<div class="<?php echo ($link && $isShop ? 'input-prepend' : '') . ($isShop ? ' input-append' : '') ?>">
									<?php if ($link): ?>
										<a href="<?php echo $link ?>"
											class="btn btn-danger">
											<?php echo Text::_('COM_REDSHOPB_DETAILS'); ?>
										</a>
									<?php endif; ?>

									<?php if ($isShop): ?>
										<input type="text" class="form-control bfh-number input-mini quantityAccessory_<?php
										echo $id . $cartPrefixAccessory . '_' . $productData->accessory_id ?>" value="<?php
										echo $productData->selection !== 'optional' ? 1 : '' ?>" name="quantity_<?php
										echo $productId ?>_<?php echo $extThis->product->collectionId . $cartPrefixAccessory ?>"/>
										<button type="button" class="btn btn-info add-to-cart add-to-cart-product"
											name="addtocart_<?php echo $productId; ?>_<?php
											echo $extThis->product->collectionId . $cartPrefixAccessory; ?>"
											data-price="<?php echo $price ?>"
											data-currency="<?php echo $preparedItems->currency ?>"
										>
											<i class="icon-shopping-cart"></i>
										</button>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
					<input type="checkbox" checked="checked" name="accessory[<?php
					echo $id ?>][]" id="dropCheckboxAccessory_<?php
					echo $id . $cartPrefixAccessory . '_' . $productData->accessory_id ?>" class="hide dropCheckboxAccessory dropCheckboxAccessory_<?php
					echo $id . $cartPrefix ?>" value="<?php echo $productData->accessory_id ?>"/>
				</div>
				<?php
				if ($accessoriesProductsDirection == 'vertical')
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
			}
			?>
		</ul>
	</div>
</div>
