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

$data = $displayData;

$state = $data['state'];
$items = $data['dataCart']['items'];

$formName = $data['formName'];
$action   = $data['action'];

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');

$app                = Factory::getApplication();
$customerType       = $app->getUserState('shop.customer_type', '');
$customerId         = $app->getUserState('shop.customer_id', 0);
$showDiscountColumn = false;
$feeProducts        = RedshopbHelperShop::getChargeProducts('fee');
$orderId            = (int) $app->getUserState('checkout.orderId', 0);
$redshopbConfig     = RedshopbApp::getConfig();
$enableOffer        = $redshopbConfig->getInt('enable_offer', 1);
$showTaxes          = $redshopbConfig->getInt('show_taxes_in_cart_module', 1);

// Get thumbnail configuration.
$width  = $redshopbConfig->getInt('thumbnail_width', 144);
$height = $redshopbConfig->getInt('thumbnail_height', 144);

// Check if in checkout process
$inCheckout = false;

if ($app->input->getCmd('view', '') == 'shop'
	&& in_array($app->input->getCmd('layout', ''), array('confirm', 'delivery', 'payment', 'pay', 'receipt', 'shipping')))
{
	$inCheckout = true;
}

if (empty($customerId))
{
	$customerType = 'employee';
	$customerId   = RedshopbHelperUser::getUserRSid();
}

if ($items)
{
	foreach ($items as $item)
	{
		if (!empty($item['discount']) && (float) $item['discount'] > 0.0)
		{
			$showDiscountColumn = true;
			break;
		}
	}
}

$options = array();

if (isset($displayData['showRestoreSaveCartDropdown']) && $displayData['showRestoreSaveCartDropdown'])
{
	$savedCartsModel = RedshopbModel::getFrontInstance('Carts', array('ignore_request' => true));
	$savedCarts      = $savedCartsModel->getItems();

	if (!empty($savedCarts))
	{
		$options[] = HTMLHelper::_('select.option', '', Text::_('JSELECT'));

		foreach ($savedCarts as $savedCart)
		{
			if ($savedCart->user_cart == '0')
			{
				$options[] = HTMLHelper::_('select.option', $savedCart->id, $savedCart->name);
			}
		}
	}
}

$customerTotal = 0;

$cartTotal = array();
$taxList   = array();
?>
<script type='text/javascript'>
	jQuery(document).ready(function(){
		jQuery('.hasTooltipAccessory').tooltip({"html": true,"container": "#cart-productList", "title": function(){
			return jQuery(this).siblings(".classWithAcc").html();
		}});
		jQuery('.hasTooltipTax').tooltip({"html": true,"container": "#cart-productList", "title": function(){
			return jQuery(this).siblings(".classWithTaxInfo").html();
		}});
	});
</script>
<form action="<?php echo $action; ?>" name="<?php echo $formName; ?>" class="adminForm shopping-cart-form" id="<?php echo $formName; ?>"
	  method="post">
	<div class="container-fluid container-cart">
		<?php if (count($items) == 0 && count($data['dataCart']['offers']) == 0) : ?>
			<div class="alert alert-info">
				<div class="pagination-centered">
					<h3><?php echo Text::_('COM_REDSHOPB_SHOP_NO_ITEMS_IN_CART') ?></h3>
				</div>
			</div>
			<?php if (!empty($options)): ?>
				<div class="text-right form-inline">
					<label id="savedCartRestoreLabel" class="inline" for="savedCartId"><?php echo Text::_('COM_REDSHOPB_SAVED_CART_RESTORE') ?></label>
					<?php echo HTMLHelper::_(
						'select.genericlist', $options, 'savedCartId',
						' class="cartDropdown" onchange="restoreCart(this.value);"'
					); ?>
				</div>
			<?php endif ?>
		<?php else : ?>
			<div class="row">
				<div id="cart-spinner" class="spinner pagination-centered" style="display: none; margin: 10px 10px;">
					<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', '') ?>
				</div>
				<div id="cart-productList">
					<?php if (count($items) > 0) : ?>
						<table id="cart-table" class="table table-condensed table-striped table-bordered">
							<tbody>
							<?php foreach ($items as $i => $item): ?>
								<?php
								$item = Joomla\Utilities\ArrayHelper::toObject($item);

								if (in_array($item->productItem, $feeProducts))
								{
									$item->editable = false;
								}
								else
								{
									$item->editable = true;
								}

								$thumb       = RedshopbHelperProduct::getProductImageThumbHtml($item->productId, $item->productItem, 0, true, $width, $height);
								$productLink = RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=product&id=' . $item->productId . '&category_id=' . (int) $item->category_id . '&collection=' . (isset($item->collectionId) ? $item->collectionId : 0));
								$nameKey     = '';

								$hidePrice = false;
								RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$item->price, &$hidePrice, 0, $item->productId));

								if ($hidePrice)
								{
									$item->price                  = 0;
									$item->price_without_discount = 0;
									$customerTotal               += 0;
									$cartTotal[$item->currency]   = 0 + (empty($cartTotal[$item->currency]) ? 0 : $cartTotal[$item->currency]);
								}
								else
								{
									$customerTotal             += $item->subtotal;
									$cartTotal[$item->currency] = $item->subtotal + (empty($cartTotal[$item->currency]) ? 0 : $cartTotal[$item->currency]);

									if ($showTaxes)
									{
										if (!array_key_exists($item->currency, $taxList))
										{
											$taxList[$item->currency] = array();
										}

										// Get product taxes
										$taxes = RedshopbHelperTax::getProductsTaxRates(array($item->productId), $customerId, $customerType, true);

										if ($taxes)
										{
											foreach ($taxes as $tax)
											{
												$singleTax           = new stdClass;
												$singleTax->name     = $tax->name;
												$singleTax->product  = Text::sprintf('COM_REDSHOPB_TAX_FROM_PRODUCT', $tax->name, $item->product_name);
												$singleTax->tax_rate = $tax->tax_rate;
												$singleTax->tax      = $item->subtotal * $tax->tax_rate;

												$taxList[$item->currency][] = $singleTax;
											}
										}
									}
								}

								foreach (RedshopbHelperCart::cartFieldsForCheck() as $itemForCheck) :
									$nameKey .= '_';

									if (property_exists($item, $itemForCheck)) :
										$nameKey .= $item->{$itemForCheck};
									endif;
								endforeach;
								?>
								<tr>
									<td>
										<div class='img-thumb'>
											<a href="<?php echo $productLink ?>">
												<?php echo ($thumb != '') ? $thumb : RedshopbHelperMedia::drawDefaultImg($width, $height, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL'), '#999999', '#dfdfdf'); ?>
											</a>
										</div>

									</td>
									<td>
										<div class="shop-cart-product-title">
										<span class="shop-cart-product-name">
											<a href="<?php echo $productLink ?>">
												<?php if (!empty($item->string_value)) : ?>
													<?php echo $this->escape($item->product_name . ' ' . $item->string_value); ?>
												<?php else : ?>
													<?php echo $this->escape($item->product_name); ?>
												<?php endif;?>
											</a>
										</span>
											<?php
											$checkAccessories = (array) $item->accessories;

											if (!empty($checkAccessories)) : ?>
												<span class="highlight-1 hasTooltipAccessory cartAccessories"><i class="icon-info-sign"></i>&nbsp;<?php echo Text::_('COM_REDSHOPB_ACCESSORIES_TITLE'); ?></span>
												<div class="hidden classWithAcc">
													<table class="table table-condensed table-bordered ">
														<?php foreach ($item->accessories as $accessory): ?>
															<?php $accessoryPrice = "" ?>
															<?php if (!$hidePrice && $accessory->price > 0) :
																$accessoryPrice = "<strong>(+&nbsp;" . RedshopbHelperProduct::getProductFormattedPrice($accessory->price, $accessory->currency) . ")</strong>";
															endif; ?>

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
										<div class="shop-cart-product-info">
										<span class="shop-cart-product-quantity shop-cart-product-quantity">

											<?php if (!isset($data['updateQuantity']) || $data['updateQuantity'] === false): ?>
												<input
														type="hidden"
														name="shop-cart-product-quantity_<?php
														echo $customerType; ?>_<?php
														echo $customerId; ?><?php
														echo $nameKey; ?>"
														value="<?php echo $item->quantity; ?>"
														class="hidden required shopping-cart-quantity <?php echo (!is_null($item->decimal) && $item->decimal) ? 'decimal-product' : '' ?>" />
												<?php echo $item->quantity ?>
											<?php else: ?>
												<input
													<?php if (isset($item->editable) && !$item->editable) : ?>
														disabled="disabled"
													<?php endif;?>
														type="text"
														name="shop-cart-product-quantity_<?php
														echo $customerType; ?>_<?php
														echo $customerId; ?><?php
														echo $nameKey; ?>"
														value="<?php echo $item->quantity; ?>"
														class="input-xmini required shopping-cart-quantity <?php echo (!is_null($item->decimal) && $item->decimal) ? 'decimal-product' : '' ?>" />
											<?php endif; ?>
											&#215;
										</span>
											<span class="shop-cart-product-price" data-product_id="<?php echo $item->productId;?>" data-quantity="<?php echo $item->quantity; ?>">

											<?php if ($item->price >= 0) : ?>
												<?php $priceWithoutDiscount = !empty($item->price_without_discount) ? $item->price_without_discount : $item->price ?>
												<?php echo RedshopbHelperProduct::getProductFormattedPrice($priceWithoutDiscount, $item->currency); ?>
											<?php endif; ?>
										</span>

											<?php if (!$hidePrice) : ?>
												<span class="shop-cart-product-additional-price">
											<?php
											$additionalPrices = '';
											RFactory::getDispatcher()->trigger('onRedshopbShopProductListPrices', array($items, $item->productId, $item->productItem, $item->currency, &$additionalPrices));
											echo $additionalPrices;
											?>
										</span>
											<?php endif; ?>
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
											<?php if (isset($item->editable) && !$item->editable) : ?>
												disabled="disabled"
											<?php endif;?>
												class="btn btn-mini shopping-cart-remove"
												type="button"
												name="shop-cart-product-remove_<?php echo $customerType; ?>_<?php echo $customerId; ?><?php echo $nameKey; ?>">
											<i class="icon-trash"></i>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
					<div class="offerCartItems text-left">
						<?php if (!empty($data['dataCart']['offers'])): ?>
							<div class="titleCartOffers">
								<?php echo Text::_('COM_REDSHOPB_CART_OFFERS_TITLE'); ?>
							</div>
							<table class="table table-condensed table-striped table-bordered">
								<?php foreach ($data['dataCart']['offers'] as $offer):
									$cartTotal[$offer['currency']] = $offer['total'] + (empty($cartTotal[$offer['currency']]) ? 0 : $cartTotal[$offer['currency']]);
									$offerQuantity                 = 0;

									if ($showTaxes)
									{
										if (!array_key_exists($offer['currency'], $taxList))
										{
											$taxList[$offer['currency']] = array();
										}

										foreach ($offer['items'] as $item)
										{
											$offerQuantity += $item['quantity'];

											// Get product taxes
											$taxes = RedshopbHelperTax::getTaxRates($customerId, $customerType, $item['productId'], true);

											if ($taxes)
											{
												foreach ($taxes as $tax)
												{
													$singleTax           = new stdClass;
													$singleTax->name     = $tax->name;
													$singleTax->product  = Text::sprintf('COM_REDSHOPB_TAX_FROM_PRODUCT', $tax->name, $item['product_name']);
													$singleTax->tax_rate = $tax->tax_rate;
													$singleTax->tax      = $item['subtotal'] * $tax->tax_rate;

													$taxList[$offer['currency']][] = $singleTax;
												}
											}
										}
									}
									else
									{
										foreach ($offer['items'] as $item)
										{
											$offerQuantity += $item['quantity'];
										}
									}

									?>
									<tr>
										<td><?php echo $offer['name']; ?> </td>
										<td><?php echo RedshopbHelperProduct::getProductFormattedPrice($offer['total'], $offer['currency']); ?></td>
										<td>
											<button class="btn btn-mini shopping-cart-offer-remove" type="button" name="shop-cart-offer-remove_<?php echo $offer['id']; ?>_<?php echo $customerType; ?>_<?php echo $customerId; ?>">
												<i class="icon-trash"></i>
											</button>
											<input type="hidden" class="shopping-cart-quantity" name="offerQuantity[]" value="<?php echo $offerQuantity; ?>">
										</td>
									</tr>
								<?php endforeach; ?>
							</table>
						<?php endif; ?>
					</div>
					<?php if ($showTaxes): ?>
						<div class="cartModuleTaxes">
							<?php

							foreach ($cartTotal as $currency => $total)
							{
								if ((float) $total <= 0)
								{
									continue;
								}

								// Get global taxes
								$taxes = RedshopbHelperTax::getTaxRates($customerId, $customerType);

								if ($taxes)
								{
									if (!array_key_exists($currency, $taxList))
									{
										$taxList[$currency] = array();
									}

									foreach ($taxes as $tax)
									{
										$singleTax           = new stdClass;
										$singleTax->name     = $tax->name;
										$singleTax->tax_rate = $tax->tax_rate;
										$singleTax->tax      = $total * $tax->tax_rate;

										$taxList[$currency][] = $singleTax;
									}
								}

								if (array_key_exists($currency, $taxList)
									&& count($taxList[$currency]) > 0)
								{
									?><table class="table table-condensed table-striped table-bordered"><?php
foreach ($taxList[$currency] as $tax):
	?><tr>
	<td>
<?php echo $tax->name;

if (property_exists($tax, 'product')):
?>
<span class="highlight-1 hasTooltipTax cartAccessories">
<i class="icon-info-sign"></i>
</span>
<div class="hidden classWithTaxInfo">
<?php echo $tax->product ?>
			</div>
<?php endif; ?>
	</td>
	<td class="text-right"><?php echo RedshopbHelperProduct::getProductFormattedPrice($tax->tax, $currency) ?></td>
	</tr><?php
endforeach;
									?></table><?php
								}
							}
							?>
						</div>
					<?php endif; ?>
				</div>
				<div class="text-right form-inline">
					<?php if (!empty($options)): ?>
						<label id="savedCartRestoreLabel" class="inline" for="savedCartId"><?php echo Text::_('COM_REDSHOPB_SAVED_CART_RESTORE') ?></label>
						<?php echo HTMLHelper::_(
							'select.genericlist', $options, 'savedCartId',
							' class="cartDropdown" onchange="restoreCart(this.value);"'
						); ?>
					<?php endif ?>
					<?php if ($enableOffer && empty($data['dataCart']['offers'])): ?>
						<a class="btn btn-link btn-small requestOfferCartButton" href="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=myoffer&layout=requestoffer');?>">
							<i class="icon-envelope"></i>&nbsp;<?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_REQUEST_OFFER');?>
						</a>
					<?php endif; ?>
					<button class="btn btn-link btn-small shopping-cart-clear" type="button">
						<?php echo Text::_('COM_REDSHOPB_SHOP_CLEAR_CART'); ?>
					</button>
				</div>
			</div>
		<?php endif; ?>
		<div class="row cartBottomBar">
			<?php if ($customerType == 'employee') :?>
			<?php $currency = RedshopbHelperUser::getCurrency($customerId); ?>
			<div class="col-md-12 cartLabelCredit">
				<span class="cartLabelCreditText"><?php echo Text::_('COM_REDSHOPB_SHOP_EMPLOYEE_CREDIT'); ?>:</span>
				<span class="cartLabelCreditSubtotalValue">
					<?php
					$userWallet = RedshopbHelperWallet::getUserWallet($customerId);

					if (!is_null($userWallet))
					{
						$moneyAmount = RedshopbHelperWallet::getMoneyAmount($userWallet->id);
					}
					else
					{
						$moneyAmount = array();
					}

					$recordCount = 0;

					?>
					<?php if (count($moneyAmount) > 0): ?>
						<?php foreach ($moneyAmount as $employeeMoney): ?>
							<?php if ((float) $employeeMoney['amount'] != 0.0):?>
								<?php echo RedshopbHelperProduct::getProductFormattedPrice($employeeMoney['amount'], $employeeMoney['alpha']) . '<br />';
								$recordCount++; ?>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php if ($recordCount == 0): ?>
						&nbsp;0,00<br />
					<?php endif; ?>
				</span>
			</div>
			<div class="col-md-12 pull-right">
			<?php else: ?>
				<div class="col-md-12 pull-right">
			<?php endif; ?>

					<?php if (count($items) > 0 && !$inCheckout) : ?>
						<a class="shopping-cart-checkout" id="lc-shopping-cart-checkout" href="#">
							<?php echo Text::_('COM_REDSHOPB_SHOP_CHECKOUT'); ?>
						</a>
					<?php endif; ?>
					<div class="cartLabelSubtotalText"><?php echo Text::_('COM_REDSHOPB_SHOP_TOTAL'); ?>:</div>
					<div class="cartLabelSubtotalValue">
						<?php if (count($cartTotal) >= 0) : ?>
							<?php foreach ($cartTotal as $currency => $total): ?>
								<?php if ((float) $total >= 0.0) :?>
									<div class="oneCurrencyTotal">
										<?php

										if (array_key_exists($currency, $taxList))
										{
											foreach ($taxList[$currency] as $tax)
											{
												$total += $tax->tax;
											}
										}

										echo RedshopbHelperProduct::getProductFormattedPrice($total, $currency); ?>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php else : ?>
							<?php
							if ($customerType == 'company')
							{
								$company = RedshopbHelperCompany::getCompanyById($customerId);

								if ($company->type == 'customer')
								{
									$currency = $company->currency_id;
								}
								else
								{
									$currency = null;
								}
							}
							else
							{
								$currency = null;
							}
							?>
							<?php if (is_null($currency)) : ?>
								<div class="oneCurrencyTotal">
									&nbsp;0,00
								</div>
							<?php else : ?>
								<div class="oneCurrencyTotal">
									<?php echo RedshopbHelperProduct::getProductFormattedPrice(0, $currency); ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div>
				<input type="hidden" name="task" value="shop.checkout">
				<input type="hidden" name="layout" value="cart">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
