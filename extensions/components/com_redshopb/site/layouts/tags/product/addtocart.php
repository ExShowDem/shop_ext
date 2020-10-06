<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!$isShop || !$extThis->placeOrderPermission)
{
	return;
}

$classes  = array('btn', 'btn-info', 'btn-small', 'add-to-cart');
$dataAttr = array();

if ($isOneProduct)
{
	$hidePrice    = false;
	$price        = (isset($extThis->product->prices[$product->id]) ? $extThis->product->prices[$product->id]->price : '');
	$priceWithTax = isset($extThis->product->prices[$product->id]) && isset($extThis->product->prices[$product->id]->price_with_tax) ?
		$extThis->product->prices[$product->id]->price_with_tax : '';
	RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$price, &$hidePrice, 0, $product->id));

	if ($hidePrice)
	{
		$price = 0;
	}

	$classes[]  = 'add-to-cart-product';
	$dataAttr[] = 'data-price="' . $price . '"';
	$dataAttr[] = 'data-currency="' . $extThis->product->currency . '"';
	$dataAttr[] = 'data-price-with-tax="' . $priceWithTax . '"';
}

$buttonName = 'addtocart_' . $product->id . '_' . $extThis->product->collectionId . '_' . $cartPrefix;
?>
<button
	type="button"
	name="<?php echo $buttonName;?>"
	class="<?php echo implode(' ', $classes);?>"
	onclick="redSHOPB.shop.addToCart(event);"
	<?php echo ' ' . implode(' ', $dataAttr);?>
>
	<i class="icon-shopping-cart"></i>
	<?php echo Text::_('COM_REDSHOPB_SHOP_ADD_TO_CART'); ?>
</button>

<button class="btn btn-small clearAmounts" type="button" id="clearAmounts_<?php
echo $extThis->product->collectionId; ?>_<?php
echo $product->id; ?>_<?php
echo $cartPrefix;
?>"
		name="clearAmounts_<?php
		echo $extThis->product->collectionId; ?>_<?php
		echo $product->id; ?>_<?php
		echo $cartPrefix;
		?>">
	<?php echo Text::_('COM_REDSHOPB_SHOP_CLEAR_CART'); ?>
</button>
