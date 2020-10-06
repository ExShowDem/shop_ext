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
$offers       = $displayData['offers'];
$hasOffers    = $displayData['hasOffers'];
$isChekout    = $displayData['isCheckout'];

// Prepare offers
foreach ($offers as &$offer)
{
	if (!is_array($offer))
	{
		$offer = (array) $offer;
	}

	$offer['quantity_total'] = 0;

	foreach ($offer['items'] as $item)
	{
		if (!is_array($item))
		{
			$item = (array) $item;
		}

		$offer['quantity_total'] += $item['quantity'];
	}
}
?>
<div class="offerCartItems text-left">
	<?php if ($hasOffers): ?>
		<div class="titleCartOffers">
			<?php echo Text::_('COM_REDSHOPB_CART_OFFERS_TITLE'); ?>
		</div>
		<table class="table table-condensed table-striped table-bordered">
			<?php foreach ($offers as $offer):?>
				<tr>
					<td><?php echo $offer['name']; ?> </td>
					<td><?php echo RedshopbHelperProduct::getProductFormattedPrice($offer['total'], $offer['currency']); ?></td>
					<td>
						<a href="javascript:void(0);"
						   class="btn btn-mini js-shopping-cart-offer-remove"
						   name="shop-cart-offer-remove_<?php echo $customerType; ?>_<?php echo $customerId . '_' . $offer['id']; ?>"
						   onclick="redSHOPB.cart.removeOfferFromShoppingCart(event);"
							<?php echo ($isChekout) ? ' data-checkout' : '';?>
						>
							<i class="icon-trash"></i>
						</a>
						<input type="hidden" class="shopping-cart-quantity" name="offerQuantity[]" value="<?php echo $offer['quantity_total']; ?>">
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
</div>

