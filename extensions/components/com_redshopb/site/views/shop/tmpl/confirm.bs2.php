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

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsortablelist.main');
RedshopbHtml::loadFooTable();

$action               = RedshopbRoute::_('index.php?option=com_redshopb&view=shop');
$orderId              = Factory::getApplication()->getUserState('checkout.orderId', 0);
$config               = RedshopbEntityConfig::getInstance();
$checkoutRegistration = $config->get('checkout_registration', 'registration_required');
$user                 = $this->user;

$showLoginForm = ($checkoutRegistration != 'registration_none' && $user->guest);
$hideCheckout  = ($checkoutRegistration == 'registration_required' && $user->guest);

$tabNumber = 1;

$orderCompany        = RedshopbEntityCompany::load($this->orderCompany->id);
$orderHeaderSettings = array('isEmail' => false);

// Settings for checkout.delivery_info
$orderHeaderSettings['deliverySettings']                        = array('showTitle' => true);
$orderHeaderSettings['deliverySettings']['deliveryAddress']     = $this->deliveryAddress;
$orderHeaderSettings['deliverySettings']['orderCompany']        = $orderCompany;
$orderHeaderSettings['deliverySettings']['orderDepartment']     = $this->orderDepartment;
$orderHeaderSettings['deliverySettings']['orderEmployee']       = $this->orderEmployee;
$orderHeaderSettings['deliverySettings']['deliveryAddressSpan'] = (!empty($this->orderVendor->name)) ? 'span8' : 'span12';
$orderHeaderSettings['deliverySettings']['showLoginForm']       = $showLoginForm;

// Settings for checkout.vendor_info
$orderHeaderSettings['vendorSettings']                = array('showTitle' => true);
$orderHeaderSettings['vendorSettings']['orderVendor'] = $this->orderVendor;

$commentOrRequisitionIsEmpty = (empty($this->comment) || empty($this->requisition));

// Settings for checkout.comment_requisition comment
$orderHeaderSettings['commentSettings']                = array('showTitle' => true);
$orderHeaderSettings['commentSettings']['comment']     = $this->comment;
$orderHeaderSettings['commentSettings']['commentSpan'] = ($commentOrRequisitionIsEmpty) ? 'span12' : 'span8';

// Settings for checkout.comment_requisition requisition
$orderHeaderSettings['requisitionSettings']                    = array('showTitle' => true);
$orderHeaderSettings['requisitionSettings']['requisition']     = $this->requisition;
$orderHeaderSettings['requisitionSettings']['requisitionSpan'] = ($commentOrRequisitionIsEmpty) ? 'span12' : 'span4';

// Settings for checkout.payment_info
$orderHeaderSettings['paymentSettings']                 = array('showTitle' => true);
$orderHeaderSettings['paymentSettings']['paymentTitle'] = $this->paymentTitle;
$orderHeaderSettings['paymentSettings']['paymentName']  = $this->paymentName;
$orderHeaderSettings['paymentSettings']['orderId']      = $orderId;

// Settings for checkout.shipping_info
$orderHeaderSettings['shippingSettings']                         = array('showTitle' => true);
$orderHeaderSettings['shippingSettings']['shippingDate']         = $this->shippingDate;
$orderHeaderSettings['shippingSettings']['shippingRateId']       = $this->shippingRateId;
$orderHeaderSettings['shippingSettings']['shippingRateTitle']    = $this->shippingRateTitle;
$orderHeaderSettings['shippingSettings']['stockroomPickupTitle'] = $this->stockroomPickupTitle;
$orderHeaderSettings['shippingSettings']['stockroomPickupId']    = $this->stockroomPickupId;

if ($this->shippingRateIdDelay)
{
	$cart                                         = RedshopbHelperCart::getFirstTotalPrice();
	$orderHeaderSettings['shippingDelaySettings'] = array('showTitle' => false, 'stockroomPickupTitle' => '');

	if ($this->stockroomPickupIdDelay)
	{
		$orderHeaderSettings['shippingDelaySettings']['stockroomPickupTitle'] = RedshopbEntityStockroom::getInstance($this->stockroomPickupIdDelay)
			->get('name');
	}

	$orderHeaderSettings['shippingDelaySettings']['shippingRateId']    = $this->shippingRateIdDelay;
	$orderHeaderSettings['shippingDelaySettings']['shippingRateTitle'] = RedshopbShippingHelper::getShippingRateName(
		$this->shippingRateIdDelay, true, key($cart)
	);
	$orderHeaderSettings['shippingDelaySettings']['stockroomPickupId'] = $this->stockroomPickupIdDelay;
}

if ($this->paymentDelayName)
{
	$orderHeaderSettings['paymentDelaySettings']                 = array('showTitle' => false);
	$orderHeaderSettings['paymentDelaySettings']['paymentTitle'] = $this->paymentDelayTitle;
	$orderHeaderSettings['paymentDelaySettings']['paymentName']  = $this->paymentDelayName;
	$orderHeaderSettings['paymentDelaySettings']['orderId']      = $orderId;
}

/** @var RedshopbModelShop $model */
$model        = $this->getModel('Shop');
$config       = RedshopbEntityConfig::getInstance();
$checkoutMode = $config->get('checkout_mode', 'default', 'string');

// Settings for checkout.customer_items
$customerItemsSettings = array(
	'config'              => $config,
	'state'               => $model->getState(),
	'customerOrders'      => $model->getCustomerOrders(),
	'form'                => $model->getCustomForm('cartitems'),
	'showStockAs'         => RedshopbHelperStockroom::getStockVisibility(),
	'showToolbar'         => false,
	'checkbox'            => false,
	'quantityfield'       => 'quantity',
	'canEdit'             => false,
	'lockquantity'        => true,
	'shippingRateId'      => $this->shippingRateId,
	'shippingRateIdDelay' => $this->shippingRateIdDelay,
	'orderId'             => $orderId,
	'showDeliveryAddress' => false,
	'isEmail'             => false,
	'view'                => Factory::getApplication()->input->get('view'),
	'delivery'            => $config->get('stockroom_delivery_time', 'hour'),
	'feeProducts'         => RedshopbHelperShop::getChargeProducts('fee'),
	'return'              => base64_encode('index.php?option=com_redshopb&view=shop&layout=confirm'),
);
?>
<script>
	jQuery(document).ready(function()
	{
		var control = jQuery('.js-complete-order');
		control.attr('onclick', '');

		control.click(function (event)
		{
			Joomla.submitbutton('shop.completeorder');
			jQuery(this).unbind('click');
		});

		<?php if (!empty($this->terms)) :?>
		var terms = jQuery('#terms-and-conditions');

		if (terms.is(':checked'))
		{
			control.attr('disabled', false);
		}
		else
		{
			control.attr('disabled', true);
		}

		terms.on('change', function() {
			if (terms.is(':checked'))
			{
				control.attr('disabled', false);
				jQuery('#terms-hidden').val('1');
			}
			else
			{
				control.attr('disabled', true);
				jQuery('#terms-hidden').val('0');
			}
		});
		<?php endif; ?>
	});
</script>
<div class="redshopb-shop-confirm">
	<?php if ($checkoutMode == 'default'):?>
		<ul class="nav nav-tabs">
			<li>
				<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_CART_TITLE'); ?></a>
			</li>
			<li>
				<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_TITLE'); ?></a>
			</li>
			<?php if ($this->usingShipping) : ?>
				<li>
					<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_SHIPPING_TITLE'); ?></a>
				</li>
			<?php endif; ?>

			<?php if ($this->usingPayments) : ?>
				<li>
					<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_PAYMENT_TITLE'); ?></a>
				</li>
			<?php endif; ?>
			<li class="active">
				<a><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_CONFIRM_TITLE'); ?></a>
			</li>
			<li>
				<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_RECEIPT_TITLE'); ?></a>
			</li>
		</ul>
	<?php endif;?>
	<div class="tab-content">
		<div class="tab-pane active">
			<div class="container-fluid">
				<div class="row-fluid">
					<div class="span12">
						<div class="row-fluid">
							<div class="span12">
								<?php echo RedshopbLayoutHelper::render('checkout.order_header', $orderHeaderSettings); ?>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<?php
									$customerBasket = RedshopbLayoutHelper::render('checkout.customer_basket', $customerItemsSettings);
									Factory::getApplication()->triggerEvent('onRedshopbRenderCustomerBasket', array(&$customerBasket));
									echo $customerBasket;
								?>
							</div>
						</div>

						<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">

							<?php if (!empty($this->terms)) :?>
								<div class="accept-terms-wrapper">
									<label class="checkbox">
										<input type="checkbox" id="terms-and-conditions" class="accept-terms-checkbox" />
										<?php echo Text::_('COM_REDSHOPB_PLEASE_ACCEPT'); ?>&nbsp;
										<a href="#" onclick="jQuery('#acceptTerms').modal('toggle');">
											<?php echo Text::_('COM_REDSHOPB_SHOP_TERMS_AND_CONDITIONS')?>
										</a>
									</label>
								</div>
								<input type="hidden" id="terms-hidden" name="terms" value="0" />
							<?php endif; ?>
							<input type="hidden" name="boxchecked" value="1">
							<input type="hidden" name="task" value="">
							<?php echo HTMLHelper::_('form.token'); ?>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php if (!empty($this->terms)) :?>
		<?php echo RedshopbLayoutHelper::render('shop.terms', array('terms' => $this->terms)); ?>
	<?php endif; ?>
</div>
