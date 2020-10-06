<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$customerOrder = $displayData['current_customer_order'];
$customerType  = $customerOrder->customerType;
$customerId    = $customerOrder->customerId;

$offer          = $displayData['current_offer'];
$quantityLocked = $displayData['lockquantity'];

if (Factory::getApplication()->input->get('view') == 'order')
{
	$quantityLocked = true;
}

$items                    = $offer->items;
$total                    = $offer->total;
$subtotalWithoutDiscounts = $offer->subtotalWithoutDiscounts;
$customerOrder            = $offer;
$isOffer                  = true;

$offerId = $customerType . '_' . $customerId . '_' . $offer->id
?>

<div class="row">
	<div class="col-md-12 alert alert-info">
		<h5>
			<?php echo Text::sprintf('COM_REDSHOPB_OFFER_ORDER_TITLE', $offer->name); ?>

			<?php if (!$quantityLocked): ?>
				<a class="btn btn-sm btn-danger order-delete-offer pull-right" href="javascript:void(0);"
				   name="order-delete-offer_<?php echo $offerId?>"
				   onclick="redSHOPB.cart.removeOfferFromShoppingCart(event);" data-checkout>
					<i class="icon icon-trash"></i>
					<?php echo Text::_('JREMOVE'); ?></a>
				<div class="clearfix"></div>
			<?php endif; ?>
		</h5>
	</div>
</div>
