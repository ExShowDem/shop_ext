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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;

HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
$isShop = RedshopbHelperPrices::displayPrices();
?>
<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			redSHOPB.carts.init(
				'<?php echo Session::getFormToken() ?>',
				'<?php echo Text::_('SUCCESS') ?>',
				'<?php echo Text::_('WARNING') ?>',
				'<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=cart', false) ?>',
				'<?php echo $this->item->id ?>'
			);
		});
	})(jQuery);

	var rsbftPhone = 675;
</script>

<?php
RedshopbHtml::loadFooTable();

echo RedshopbBrowserBreadcrumbs::renderBreadcrumbs();
?>
<h3>
	<?php echo $this->item->name ?>
	<span class="img-loading-cart img-loading-cart-<?php echo $this->item->id ?>">
		<?php echo HTMLHelper::image('media/com_redshopb/images/ajax-loader.gif', ''); ?>
	</span>
</h3>
<hr />
<?php if (!count($this->item->cartItems)): ?>
	<?php echo RedshopbLayoutHelper::render('common.nodata'); ?>
<?php else: ?>
<table class="table table-striped table-hover footable js-redshopb-footable redshopb-footable toggle-circle-filled"
	id="tableCartItems<?php echo $this->item->id ?>">
	<thead>
		<tr>
			<th><?php echo Text::_('COM_REDSHOPB_SAVED_CART_PRODUCT_NAME') ?></th>
			<th><?php echo Text::_('COM_REDSHOPB_SAVED_CART_PRODUCT_SKU') ?></th>
			<th><?php echo Text::_('COM_REDSHOPB_SAVED_CART_PRODUCT_ATTRIBUTES') ?></th>
			<th><?php echo Text::_('COM_REDSHOPB_SAVED_CART_PRODUCT_QUANTITY') ?></th>
			<?php if ($isShop): ?>
			<th><?php echo Text::_('COM_REDSHOPB_PRICE') ?></th>
			<th><?php echo Text::_('COM_REDSHOPB_SAVED_CARTS_TOTAL_PRICE') ?></th>
			<?php endif; ?>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$totalAmount    = array();
			$cloneCartItems = clone $this->item->cartItems;
		?>

		<?php foreach ($this->item->cartItems as $cartItem): ?>

		<?php
		// Skip accessories
		if ($cartItem->get('parent_cart_item_id'))
		{
			continue;
		}

		$isProduct = true;

		if ($cartItem->get('product_item_id', 0))
		{
			$product   = RedshopbEntityProduct_Item::getInstance($cartItem->get('product_item_id'))->getProduct();
			$isProduct = false;
		}
		else
		{
			$product = RedshopbEntityProduct::getInstance($cartItem->get('product_id'));
		}

		$productLink = RedshopbRoute::_(
			'index.php?option=com_redshopb&view=shop&layout=product&id=' . $product->get('id')
				. '&category_id=' . (int) $product->get('category_id')
				. '&collection=' . (isset($product->collectionId) ? $product->collectionId : 0)
		);

		$collectionId = (int) $cartItem->get('collection_id');
		$prices       = $cartItem->get('price');
		$price        = 0;
		$currency     = null;

		// If product
		if (is_object($prices))
		{
			$price    = $prices->price;
			$currency = $prices->currency;
		}

		// If product item
		if (!is_object($prices))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			$query->select(array('*'));
			$query->from($db->qn('#__redshopb_product_price'));
			$query->where($db->qn('product_id') . ' = ' . $db->q($cartItem->get('product_id')));
			$query->where($db->qn('product_item_id') . ' = ' . $db->q($cartItem->get('product_item_id')));

			$prices1 = $db->setQuery($query)->loadObjectList();

			$pricesAttrib     = array();
			$currenciesAttrib = array();

			foreach ($prices1 as $itemPrice)
			{
				$pricesAttrib[$itemPrice->product_item_id]     = $itemPrice->price;
				$currenciesAttrib[$itemPrice->product_item_id] = $itemPrice->currency_id;
			}
		}
		?>
		<tr id="row-<?php echo $cartItem->get('id') ?>">
			<td style="vertical-align: middle">
				<a href="<?php echo $productLink; ?>"><?php echo $product->get('name') ?></a>
					<?php
					$showAccessory = false;

					foreach ($cloneCartItems as $accessory)
					{
						$accessoryData = $accessory->get('accessory');

						if ($accessory->get('parent_cart_item_id') == $cartItem->get('id')
							&& is_array($accessoryData)
							&& !($collectionId && $accessoryData['hide_on_collection'] == 1))
						{
							$showAccessory = true;
							break;
						}
					}
					?>
					<?php if ($showAccessory): ?>
						<div class="accessory-list">
							<table class="table table-condensed table-bordered">
								<?php foreach ($cloneCartItems as $accessory): ?>

									<?php $accessoryData = $accessory->get('accessory'); ?>

									<?php
									if ($accessory->get('parent_cart_item_id') == $cartItem->get('id')
										&& is_array($accessoryData)
										&& !($collectionId && $accessoryData['hide_on_collection'] == 1)):
									?>
										<tr>
											<td>
												<small>+ <?php echo $accessoryData['product_sku'] . ' ' . $accessoryData['product_name']; ?></small>
											</td>
										</tr>
									<?php endif; ?>
								<?php endforeach; ?>
							</table>
						</div>
					<?php endif; ?>
				</td>
				<td style="vertical-align: middle">
					<?php if (!$isProduct): ?>
						<?php echo RedshopbEntityProduct_Item::getInstance($cartItem->get('product_item_id'))->get('sku'); ?>
					<?php else: ?>
						<?php echo $product->get('sku') ?>
					<?php endif; ?>
				</td>
				<td style="vertical-align: middle">
					<?php if (!$isProduct): ?>
						<?php $attributes = RedshopbHelperProduct_Item::getAttributeValues($cartItem->get('product_item_id')); ?>

						<?php if (!empty($attributes)): ?>
							<?php foreach ($attributes as $attributeId => $attributeValue): ?>
								<?php $attribute = RedshopbEntityProduct_Attribute::getInstance($attributeId); ?>
								<?php echo $attribute->get('name') . ': ' . $attributeValue . '<br />'; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endif; ?>
				</td>
				<td style="vertical-align: middle"><?php echo $cartItem->get('quantity') ?></td>
				<?php if ($isShop): ?>
					<td style="vertical-align: middle">
						<?php
						if ($price > 0)
						{
							echo RedshopbHelperProduct::getProductFormattedPrice($price, $currency);
						}
						elseif (isset($pricesAttrib) && !empty($pricesAttrib))
						{
							$price = $pricesAttrib[$cartItem->get('product_item_id')];
							echo RedshopbHelperProduct::getProductFormattedPrice($price, $currency);
						}

						$totalProductPrice = (float) $price * $cartItem->get('quantity');
						?>

						<?php if ($showAccessory): ?>
							<div class="accessory-list">
								<table class="table table-condensed table-bordered">
						<?php endif; ?>

						<?php foreach ($cloneCartItems as $accessory): ?>

							<?php $accessoryData = $accessory->get('accessory'); ?>

							<?php
							if ($accessory->get('parent_cart_item_id') == $cartItem->get('id')
								&& is_array($accessoryData)
								&& $accessoryData['price'] > 0):
							?>
								<?php
								if ($accessoryData['quantity'] <= 1)
								{
									$accessoryQuantity = $cartItem->get('quantity');
								}
								else
								{
									$accessoryQuantity = $accessoryData['quantity'];
								}

								$totalProductPrice += $accessoryQuantity * $accessoryData['price'];
								?>
								<?php if ($showAccessory && !($collectionId && $accessoryData['hide_on_collection'] == 1)): ?>
									<tr>
										<td>
											<small>
												+ <?php
												echo RedshopbHelperProduct::getProductFormattedPrice($accessoryData['price'], $currency);
												echo ($accessoryQuantity != $cartItem->get('quantity') ? '&nbsp;x&nbsp;' . $accessoryQuantity : '');
												?> (<?php echo $accessoryData['product_sku'] ?>)
											</small>
										</td>
									</tr>
								<?php endif; ?>
							<?php endif; ?>
						<?php endforeach; ?>

						<?php if ($showAccessory): ?>
								</table>
							</div>
						<?php endif; ?>
					</td>
					<td style="vertical-align: middle">
						<?php if ($price > 0): ?>
							<?php echo RedshopbHelperProduct::getProductFormattedPrice($totalProductPrice, $currency); ?>
						<?php endif; ?>
					</td>
				<?php endif; ?>
				<td style="vertical-align: middle">
					<?php
					$route = 'index.php?option=com_redshopb&task=cart.removeCartItem&id=' . $this->item->id
					. '&cartItem=' . $cartItem->get('id') . '&' . Session::getFormToken() . '=' . 1;
					?>
					<a href="<?php echo RedshopbRoute::_($route); ?>" class="btn btn-default btnRemoveCartItem">
						<i class="icon-remove text-error"></i>
					</a>
				</td>
			</tr>
		<?php
		if ($price > 0)
		{
			if (is_object($prices) && !is_null($prices->currency_id))
			{
				$currencyId = $prices->currency_id;
			}
			elseif (isset($currenciesAttrib) && !empty($currenciesAttrib))
			{
				$currencyId = $currenciesAttrib[$cartItem->get('product_item_id')];
			}

			if (!isset($totalAmount[$currencyId]))
			{
				$totalAmount[$currencyId] = 0;
			}

			$totalAmount[$currencyId] += $totalProductPrice;
		}
		?>

		<?php endforeach; ?>

		<?php if ($isShop): ?>
			<?php foreach ($totalAmount as $currencyId => $amount): ?>
				<tr>
					<td colspan="5"></td>
					<td colspan="2">
						<strong><?php echo RedshopbHelperProduct::getProductFormattedPrice($amount, $currencyId) ?></strong>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>

<?php endif; ?>

<form action="<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=cart') ?>" method="post" name="adminForm" id="adminForm" class="">
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="cartId" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
