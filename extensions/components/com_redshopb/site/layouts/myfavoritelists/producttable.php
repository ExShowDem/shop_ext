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

$app                     = Factory::getApplication();
$customerId              = $app->getUserState('shop.customer_id', 0);
$customerType            = $app->getUserState('shop.customer_type', 'employee');
$defaultCurrency         = RedshopbHelperPrices::getCurrency($customerId, $customerType);
$redshopbConfig          = RedshopbEntityConfig::getInstance();
$enableOffer             = $redshopbConfig->getInt('enable_offer', 1);
$needChangeImpersonation = false;
$isShop                  = RedshopbHelperPrices::displayPrices();

extract($displayData);

// Placing this here so that we can see what vars are expected in the $displayData array
$action          = $displayData['action'];
$products        = $displayData['products'];
$item            = $displayData['item'];
$totalCurrencies = array();
$quantities      = $displayData['quantities'];
$cartPrefix      = $displayData['cart_prefix'];

$isSuperAdmin = RedshopbHelperACL::isSuperAdmin();

if ($isSuperAdmin && empty($customerId))
{
	$needChangeImpersonation = true;
}

$genericList     = array();
$collectionFound = false;
$ids             = array();

foreach ($products as $product)
{
	$ids[$product->id] = $product->quantity;
}

if (!empty($ids))
{
	$prices = RedshopbHelperPrices::getProductsPrice(
		$ids,
		$customerId,
		$customerType,
		null,
		array(),
		'',
		0,
		null,
		false,
		true
	);
}

foreach ($products as $product)
{
	$collectionId             = 0;
	$quantity                 = 1;
	$price                    = 0;
	$totalPrice               = 0;
	$product->collectionInput = '';

	if ($product->collection_ids)
	{
		$collectionIds   = explode(',', $product->collection_ids);
		$collectionFound = true;

		if (isset($collectionIds) && array_key_exists($product->id, $collectionIds))
		{
			$collectionId = $collectionIds[$product->id];
		}
		elseif (isset($prices[$product->id]->wid))
		{
			$collectionId = $prices[$product->id]->wid;
		}

		if (count($collectionIds) > 1)
		{
			$options = array();

			foreach ($collectionIds as $collectionIdGeneric)
			{
				$collectionName = RedshopbEntityCollection::getInstance($collectionIdGeneric)
					->loadItem()->get('name');
				$options[]      = HTMLHelper::_('select.option', $collectionIdGeneric, $collectionName);
			}

			$product->collectionInput = HTMLHelper::_('select.genericlist', $options,
				'collection_id[' . $product->id . ']',
				array('list.attr' => array(
					'class' => 'dropdownPriceCondition collectionIdClass',
					'onchange' => 'redSHOPB.favoritelist.updatePrice(event);',
					'data-product_id' => $product->id)),
				'value',
				'text',
				$collectionId
			);
		}
		else
		{
			$product->collectionInput = RedshopbEntityCollection::getInstance($collectionId)
				->loadItem()->get('name')
				. '<input type="hidden" name="collection_id[' . $product->id . ']" class="collectionIdClass" value="' . $collectionId . '">';
		}
	}

	if (!isset($product->collectionId))
	{
		$product->collectionId = $collectionId;
	}

	if (!empty($prices[$product->id]))
	{
		$price    = $prices[$product->id];
		$quantity = $product->quantity;

		if (isset($quantities[$product->id]))
		{
			$quantity = $quantities[$product->id];
		}

		$product->currencyId = $price->currency_id;

		if (!isset($price->price))
		{
			$price->price = $price->price_without_discount;
		}

		$price->quantity     = $quantity;
		$product->totalPrice = (float) $price->price * (float) $product->quantity;
	}
	else
	{
		$price              = new stdClass;
		$price->currency_id = $defaultCurrency;
		$price->quantity    = 1;

		if (isset($quantities[$product->id]))
		{
			$price->quantity = $quantities[$product->id];
		}

		$price->price         = 0;
		$product->totalPrice  = 0;
		$prices[$product->id] = $price;
		$product->currencyId  = $defaultCurrency;
	}

	if (!array_key_exists($product->currencyId, $totalCurrencies))
	{
		$totalCurrencies[$product->currencyId] = 0;
	}

	$totalCurrencies[$product->currencyId] += $product->totalPrice;

	$product->shopLink  = 'index.php?option=com_redshopb&view=shop&layout=product';
	$product->shopLink .= '&id=' . $product->id . '&category_id=' . (int) $product->category_id . '&collection=' . $product->collectionId;
}
?>

<div id="js-product-list-wrapper">
	<form action="<?php echo $action; ?>" method="post">
		<div class="row-fluid">
			<div class="col-md-12">
				<table class="favorite-products table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled"
					   id="table-favorite-products">
					<thead>
						<tr>
							<th>&nbsp;</th>
							<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_PRODUCT'); ?></th>

							<?php if ($collectionFound): ?>
								<th><?php echo Text::_('COM_REDSHOPB_COLLECTION'); ?></th>
							<?php endif; ?>
							<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_STOCK_STATUS'); ?></th>

							<?php if ($isShop) : ?>
							<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_QUANTITY'); ?></th>
							<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_PRICE'); ?></th>
							<th><?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_TOTAL'); ?></th>
							<th>&nbsp;</th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($products as $index => $product): ?>
						<?php $productItemId = !is_null($product->product_item_id) ? $product->product_item_id : 0; ?>
						<tr data-product_id="<?php echo $product->id; ?>" data-product_item_id="<?php echo $productItemId; ?>">
							<td>
								<?php $image = RedshopbHelperProduct::getProductImageThumbPath($product->id); ?>
								<a href="<?php echo RedshopbRoute::_($product->shopLink); ?>">

									<?php if ($image): ?>
										<img src="<?php echo $image ?>" alt="<?php echo RedshopbHelperThumbnail::safeAlt($product->name) ?>" />
									<?php else: ?>
										<?php echo RedshopbHelperMedia::drawDefaultImg(72, 72, Text::_('COM_REDSHOPB_NO_IMAGE_LABEL')) ?>
									<?php endif; ?>
								</a>
							</td>
							<td>
								<div class="product-sku"><?php echo $product->sku; ?></div>

								<?php if (!is_null($product->attr_name)): ?>
									<div class="product-sku"><?php echo $product->attr_name; ?></div>
								<?php endif; ?>
								<div class="product-name">
									<a href="<?php echo RedshopbRoute::_($product->shopLink) ?>"><?php echo $product->name ?></a>
								</div>
							</td>
							<?php if ($collectionFound): ?>
								<td><?php echo $product->collectionInput; ?></td>
							<?php endif; ?>
							<td class="verticalAlignTableInput">
								<?php if (RedshopbHelperStockroom::productHasInStock($product->id)): ?>
									<div class="product-on-stock"><?php echo Text::_('COM_REDSHOPB_PRODUCT_ON_STOCK') ?></div>
								<?php else: ?>
									<div class="product-no-stock"><?php echo Text::_('COM_REDSHOPB_PRODUCT_NO_STOCK') ?></div>
								<?php endif; ?>
							</td>
							<?php if ($isShop) : ?>
							<td>
								<div class="input-group">
									<input type="number"
											step="<?php echo $product->pkg_size; ?>"
											min="<?php echo $product->min_sale; ?>"

											<?php if ($product->max_sale > 0): ?>
												max="<?php echo $product->max_sale; ?>"
											<?php endif; ?>
											class="input-small productQuantity quantityValue"
											name="quantity[<?php echo $product->id; ?>][<?php echo $productItemId; ?>]"
											value="<?php echo $product->quantity;?>"
											onchange="redSHOPB.favoritelist.updatePrice(event);"
											onkeypress="if (event.which == 13 || event.keyCode == 13){ redSHOPB.favoritelist.updatePrice(event); return false; }"
											tabindex="<?php echo $index++;?>"
											data-product_id="<?php echo $product->id;?>"
											/>
									<?php if (!empty($product->unit_mesure_code)):?>
										<span class="add-on"><?php echo $product->unit_measure_code ?></span>
									<?php endif;?>
								</div>
								<?php // Temporary fix for the cart system ?>
								<input type="hidden"
									   class="js-hidden-quantity"
									   name="quantity_<?php echo $product->id; ?>_<?php echo $product->collectionId; ?>_<?php echo $cartPrefix;?>"
									   value="<?php echo $product->quantity;?>"
									   data-product_id="<?php echo $product->id;?>"
									   />
								<input type="hidden" name="price[<?php echo $product->id; ?>][<?php echo $productItemId; ?>]" value="<?php echo $prices[$product->id]->price; ?>">
								<input type="hidden" name="currency[<?php echo $product->id; ?>][<?php echo $productItemId; ?>]" value="<?php echo $prices[$product->id]->currency_id; ?>">

								<?php if (!$collectionFound):?>
									<input type="hidden" name="collection_id[<?php echo $product->id; ?>][<?php echo $productItemId; ?>]" value="<?php echo $product->collectionId; ?>">
								<?php endif;?>
							</td>
							<td class="verticalAlignTableInput productPrice">
								<?php echo RedshopbHelperProduct::getProductFormattedPrice($prices[$product->id]->price, $prices[$product->id]->currency_id); ?>
							</td>
							<td class="verticalAlignTableInput productFinalPrice">
								<?php echo RedshopbHelperProduct::getProductFormattedPrice($prices[$product->id]->price * $product->quantity, $prices[$product->id]->currency_id); ?>
							</td>
							<td>
								<div class="btn-group">
									<?php if ($needChangeImpersonation): ?>
										<button data-toggle="modal" data-target="#changeimpersonation" class="btn btn-info btn-sm">
											<i class="icon-shopping-cart"></i>
										</button>
									<?php else: ?>
										<button data-price="<?php echo $prices[$product->id]->price; ?>"
												data-price-with-tax="<?php echo $prices[$product->id]->price_with_tax; ?>"
												data-currency="<?php echo $prices[$product->id]->currency_id; ?>"
												data-product_id ="<?php echo $product->id; ?>"
												data-attribute-id ="<?php echo $productItemId; ?>"
												class="btn btn-info btn-small add-to-cart add-to-cart-product addToCartButton" type="button"
												onclick="redSHOPB.favoritelist.addToCart(event);"
												name="addtocart_<?php echo $product->id; ?>_<?php echo $product->collectionId; ?>_<?php echo $cartPrefix;?>">
											<i class="icon-shopping-cart"></i>
										</button>
									<?php endif; ?>
									<a href="javascript:void(0);"
										class="product-item-remove btn btn-sm"
										onclick="redSHOPB.favoritelist.removeProduct(event)"
										data-product_id="<?php echo $product->id; ?>"
										data-product_item_id="<?php echo $productItemId; ?>"
									>
										<i class="icon icon-remove icon text-error"></i>
									</a>
								</div>
							</td>
							<?php endif; ?>
						</tr>
					<?php endforeach;?>
					</tbody>
				</table>
				<?php if (empty($products)): ?>
					<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
				<?php endif;?>
			</div>
		</div>
		<input type="hidden" name="cart_prefix" value="<?php echo $cartPrefix;?>"/>
		<input type="hidden" name="product_id" value=""/>
		<input type="hidden" name="product_item_id" value=""/>
		<input type="hidden" name="id" value="<?php echo $item->id; ?>"/>
		<input type="hidden" name="task" value=""/>
		<?php echo HTMLHelper::_('form.token'); ?>

	<?php if ($item->id) : ?>
		<hr/>
		<div class="row">
			<div class="col-md-12">
				<div class="products-total pull-right">
					<?php if (count($totalCurrencies)): ?>
						<?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_LIST_TOTAL'); ?>:

						<?php foreach ($totalCurrencies as $currencyId => $total): ?>
							<span class="totalPriceCurrency_<?php echo $currencyId; ?>">
					<?php echo RedshopbHelperProduct::getProductFormattedPrice($total, $currencyId) . '<br />'; ?>
					</span>
							<input type="hidden" name="total[<?php echo $currencyId;?>]" value="<?php echo $total;?>"/>
						<?php endforeach;?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php if ($enableOffer && count($totalCurrencies)): ?>
			<hr/>
			<div class="row">
				<div class="col-md-12">
					<div class="pull-right">
						<button onclick="redSHOPB.favoritelist.requestOffer(event);" class="btn btn-default request-offer-button">
							<?php echo Text::_('COM_REDSHOPB_MYFAVORITELIST_PRODUCTS_REQUEST_OFFER'); ?>
						</button>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endif;?>
	</form>
</div>
