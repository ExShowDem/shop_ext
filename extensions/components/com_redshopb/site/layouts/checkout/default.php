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

$orderId       = $displayData['orderId'];
$action        = $displayData['action'];
$usingPayments = $displayData['usingPayments'];
$usingShipping = $displayData['usingShipping'];
$tabNumber     = 1;
?>
<script>
	jQuery(document).ready(function()
	{
		jQuery('.ajax-quantity-change').on('change', function (event)
		{
			redSHOPB.shop.cart.updateItemQuantity(event);
		});
	});
</script>
<div class="redshopb-shop-cart">
	<div class="row">
		<div class="col-md-12">
			<?php if ((int) $orderId == 0) : ?>
			<ul class="nav nav-tabs">
				<li class="active">
					<a><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_CART_TITLE'); ?></a>
				</li>
				<li>
					<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_TITLE'); ?></a>
				</li>
				<?php if ($usingShipping) : ?>
					<li>
						<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_SHIPPING_TITLE'); ?></a>
					</li>
				<?php endif; ?>

				<?php if ($usingPayments) : ?>
					<li>
						<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_PAYMENT_TITLE'); ?></a>
					</li>
				<?php endif; ?>
				<li>
					<a class="disabled" style="cursor:not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_CONFIRM_TITLE'); ?></a>
				</li>
				<li>
					<a class="disabled" style="cursor:not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_RECEIPT_TITLE'); ?></a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active">
					<div class="container-fluid">
			<?php endif; ?>
						<div id="shopcart">
							<?php echo RedshopbLayoutHelper::render('checkout.customer_basket', $displayData); ?>
						</div>
						<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
							<input type="hidden" name="boxchecked" value="1">
							<input type="hidden" name="task" value="checkout">
							<?php echo HTMLHelper::_('form.token'); ?>
						</form>
						<?php if ((int) $orderId == 0) : ?>
					</div>
				</div>
			</div>
						<?php endif; ?>
		</div>
	</div>
</div>
