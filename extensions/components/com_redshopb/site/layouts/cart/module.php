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
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rbootstrap.tooltip');

$customerType = $displayData['customerType'];
$customerId   = $displayData['customerId'];

if (empty($customerId))
{
	$customerType = 'employee';
	$customerId   = RedshopbHelperUser::getUserRSid();
}

$items       = $displayData['items'];
$offers      = $displayData['offers'];
$hasItems    = (!empty($items));
$hasOffers   = (!empty($offers));
$totals      = $displayData['totals'];
$taxes       = $displayData['taxes'];
$feeProducts = $displayData['fees'];
$isCheckout  = $displayData['isCheckout'];
$mShowDisc   = !isset($displayData['showDiscColumn']) ? 1 : $displayData['showDiscColumn'];

/** @var RedshopbEntityConfig $config */
$config      = $displayData['config'];
$showImages  = $config->get('cart_show_product_image', 0);
$enableOffer = (array_key_exists('enableOffer', $displayData)) ? $displayData['enableOffer'] : $config->getInt('enable_offer', 1);
$showTaxes   = (array_key_exists('showTaxes', $displayData)) ? $displayData['showTaxes'] : $config->getInt('show_taxes_in_cart_module', 1);
$thumbWidth  = (!empty($displayData['thumbWidth'])) ? $displayData['thumbWidth'] : $config->getInt('cart_image_width', 100);
$thumbHeight = (!empty($displayData['thumbHeight'])) ? $displayData['thumbHeight'] : $config->getInt('cart_image_height', 100);

$showDiscountColumn = false;
$counter            = 1;
$cartFieldsForCheck = RedshopbHelperCart::cartFieldsForCheck();

// Prepare products
foreach ($items as $i => $item)
{
	if ($mShowDisc && !empty($item['discount']) && (float) $item['discount'] > 0.0)
	{
		$showDiscountColumn = true;
	}

	$item['thumbHtml'] = RedshopbHelperProduct::getProductImageThumbHtml($item['productId'], $item['productItem'], 0, true, $thumbWidth, $thumbHeight);

	if (empty($item['thumbHtml']))
	{
		$item['thumbHtml'] = RedshopbHelperMedia::drawDefaultImg($thumbWidth, $thumbHeight, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf');
	}

	$productUrl  = 'index.php?option=com_redshopb&view=shop&layout=product&id=' . $item['productId'];
	$productUrl .= '&category_id=' . (int) $item['category_id'];
	$productUrl .= '&collection=' . (isset($item['collectionId']) ? $item['collectionId'] : 0);

	$item['productLink']  = RedshopbRoute::_($productUrl);
	$item['string_value'] = (empty($item['string_value'])) ? '' : $item['string_value'];

	$nameKey = $customerType . '_' . $customerId;

	foreach ($cartFieldsForCheck as $itemForCheck)
	{
		$nameKey .= '_';

		if (array_key_exists($itemForCheck, $item))
		{
			$nameKey .= $item[$itemForCheck];
		}
	}

	$item['nameKey'] = $nameKey . '_' . $counter;
	$counter++;

	$step                         = !empty($item['pkg_size']) ? $item['pkg_size'] : 1;
	$item['inputStep']            = $step;
	$item['inputType']            = 'number';
	$item['inputClass']           = array('required', 'shopping-cart-quantity', 'input-xmini');
	$item['inputDisabled']        = '';
	$item['removeButtonDisabled'] = '';

	if (in_array($item['productItem'], $feeProducts))
	{
		$item['inputDisabled']        = ' disabled="disabled"';
		$item['removeButtonDisabled'] = $item['inputDisabled'];
	}

	if ((!is_null($item['decimal']) && $item['decimal']))
	{
		$item['inputStep']    = '0.' . str_pad($step, $item['decimal'], '0', STR_PAD_LEFT);
		$item['inputClass'][] = 'decimal-product';
	}

	if (!isset($displayData['updateQuantity']) || $displayData['updateQuantity'] === false)
	{
		$item['inputClass'][]  = 'readonly';
		$item['inputDisabled'] = '';
	}

	$hidePrice = false;
	RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$item['price'], &$hidePrice, 0, $item['productId']));

	$item['hidePrice'] = $hidePrice;

	$items[$i] = Joomla\Utilities\ArrayHelper::toObject($item);
}

?>
<script type='text/javascript'>
	jQuery(document).ready(function(){
		jQuery('.hasTooltipAccessory')
			.tooltip(
				{
					"html": true,
					"container": "#cart-productList",
					"title": function ()
					{
						return jQuery(this).siblings(".classWithAcc").html();
					}
				}
			);
		jQuery('.hasTooltipTax')
			.tooltip(
				{"html": true,
					"container": "#cart-productList",
					"title": function()
					{
						return jQuery(this).siblings(".classWithTaxInfo").html();
					}
				}
			);
	});
</script>
<div class="container-fluid container-cart">
	<?php if ($hasItems || $hasOffers):?>
		<div class="row-fluid">
			<div id="cart-productList">
				<?php if ($hasItems) : ?>
					<table id="cart-table" class="table table-condensed table-striped table-bordered">
						<tbody>
						<?php foreach ($items as $i => $item): ?>
							<tr>
								<?php if ($showImages):?>
									<td>
										<div class='img-thumb'>
											<a href="<?php echo $item->productLink; ?>">
												<?php echo $item->thumbHtml; ?>
											</a>
										</div>
									</td>
								<?php endif;?>
								<td>
									<div class="row shop-cart-product-title">
										<div class="col-md-12">
											<div class="shop-cart-product-name">
												<a href="<?php echo$item->productLink; ?>">
													<?php echo trim($this->escape($item->product_name . ' ' . $item->string_value)); ?>
												</a>
											</div>
											<?php
											$customText = null;
											RFactory::getDispatcher()->trigger('onVanirProductCustomTextGetField', array($item, &$customText));
											echo $customText;
											?>
											<?php if (!empty((array) $item->accessories)) : ?>
												<span class="highlight-1 hasTooltipAccessory cartAccessories" title="">
															<i class="icon-info"></i>&nbsp;
													<?php echo Text::_('COM_REDSHOPB_ACCESSORIES_TITLE'); ?>
														</span>
												<div class="hidden classWithAcc">
													<table class="table table-condensed table-bordered ">
														<?php foreach ($item->accessories as $accessory): ?>
															<?php $accessoryPrice = "" ?>
															<?php if (!$item->hidePrice && $accessory->price > 0) :?>
																<?php $accessoryPrice = "<strong>(+&nbsp;"
																	. RedshopbHelperProduct::getProductFormattedPrice($accessory->price, $accessory->currency)
																	. ")</strong>";?>
															<?php endif;?>

															<?php if ($accessory->hide_on_collection == 0): ?>
																<tr>
																	<td>
																		<span><?php echo "+&nbsp;" . $accessory->product_name . " " . $accessoryPrice ?></span>
																	</td>
																</tr>
															<?php endif; ?>
														<?php endforeach; ?>
													</table>
												</div>
											<?php endif; ?>
										</div>
									</div>
									<div class="row shop-cart-product-info">
										<div class="col-md-12">
											<span class="shop-cart-product-quantity shop-cart-product-quantity input-append">
												<input type="<?php echo $item->inputType;?>"
													   name="shop-cart-product-quantity_<?php echo $item->nameKey; ?>"
													   data-cart_item_hash="<?php echo $item->hash; ?>"
													   value="<?php echo $item->quantity; ?>"
													   step="<?php echo $item->inputStep;?>"
													   class="<?php echo implode(' ', (array) $item->inputClass);?>"
													   onkeypress="return avoidUpdate(event);"
													   onchange="redSHOPB.cart.updateShoppingCartItemQuantity(event);"
													<?php echo $item->inputDisabled;?>
													<?php echo ($isCheckout) ? ' data-checkout' : '';?>
												/>
												<?php
												if (!empty($item->unit_measure_id))
												{
													$unitMeasure = RedshopbEntityUnit_Measure::getInstance($item->unit_measure_id);
													$unitMeasure->getItem();

													$unitMeasureName = $unitMeasure->get('name');

													echo '<span class="add-on">' . trim($unitMeasureName) . '</span>';
												}
												?>
											</span>
											&#215;
											<span class="shop-cart-product-price" data-product_id="<?php echo $item->productId;?>" data-quantity="<?php echo $item->quantity; ?>">

											<?php if ($item->price >= 0) : ?>
												<?php $price = (!empty($item->price_without_discount) && $showDiscountColumn) ? $item->price_without_discount : $item->price ?>
												<?php echo RedshopbHelperProduct::getProductFormattedPrice($price, $item->currency); ?>
											<?php endif; ?>
										</span>

											<?php if (!$hidePrice) : ?>
												<?php $additionalPrices = '';?>
												<?php RFactory::getDispatcher()->trigger('onRedshopbShopProductListPrices',
													array(
														$items,
														$item->productId,
														$item->productItem,
														$item->currency,
														&$additionalPrices
													)
												);?>
												<span class="shop-cart-product-additional-price">
												<?php echo $additionalPrices; ?>
											</span>
											<?php endif; ?>
										</div>
									</div>
								</td>
								<?php if ($showDiscountColumn) : ?>
									<td>
										<?php if (!empty($item->discount)): ?>
											<?php echo RedshopbHelperPrices::displayDiscount($item->discount, $item->discount_type, $item->currency) ?>
										<?php endif; ?>
									</td>
								<?php endif; ?>
								<td>
									<button
										<?php echo $item->removeButtonDisabled;?>
											class="btn btn-mini shopping-cart-remove"
											type="button"
											name="shop-cart-product-remove_<?php echo $item->nameKey; ?>"
											data-cart_item_hash="<?php echo $item->hash; ?>"
											onclick="redSHOPB.cart.removeItemFromShoppingCart(event);"
										<?php echo ($isCheckout) ? ' data-checkout' : '';?>>
										<i class="icon-trash"></i>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
				<?php echo RedshopbLayoutHelper::render(
					'cart.module.offers',
					array (
						'customerType' => $customerType,
						'customerId'   => $customerId,
						'offers'       => $offers,
						'hasOffers'    => $hasOffers,
						'isCheckout'   => $isCheckout
					)
				);?>

				<?php if ($showTaxes): ?>
					<?php echo RedshopbLayoutHelper::render(
						'cart.module.taxes',
						array (
							'customerType' => $customerType,
							'customerId'   => $customerId,
							'totals'       => $totals,
							'taxes'        => $taxes
						)
					);?>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if (!$hasItems && !$hasOffers): ?>
		<div class="row">
			<div class="alert alert-info">
				<div class="pagination-centered">
					<h3><?php echo Text::_('COM_REDSHOPB_SHOP_NO_ITEMS_IN_CART') ?></h3>
				</div>
			</div>
		</div>
	<?php endif;?>
	<div class="row">
		<div class="col-md-12 text-right">
			<?php echo RedshopbLayoutHelper::render(
				'cart.module.controls',
				array(
					'enableOffer' => $enableOffer,
					'hasOffers' => $hasOffers,
					'hasItems' => $hasItems
				)
			);?>
		</div>
	</div>
	<?php echo RedshopbLayoutHelper::render('cart.module.footer',
		array(
			'customerType' => $customerType,
			'customerId' => $customerId,
			'items' => $items,
			'totals' => $totals,
			'taxes' => $taxes,
			'showTaxes' => $showTaxes,
			'config' => $config
		)
	);?>
</div>
<script>
	// Make sure the cart gets updated and not refreshed
	// when you press enter after having changed the quantity of your cart
	function avoidUpdate(e) {
		if (e.keyCode == 13) {
			redSHOPB.cart.updateShoppingCartItemQuantity(event);
		}
	}
</script>
