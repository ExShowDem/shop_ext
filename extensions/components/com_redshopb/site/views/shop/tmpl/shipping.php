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

$action               = RedshopbRoute::_('index.php?option=com_redshopb&view=shop');
$shippingRateId       = Factory::getApplication()->getUserState('checkout.shipping_rate_id', '');
$orderId              = Factory::getApplication()->getUserState('checkout.orderId', 0);
$config               = RedshopbEntityConfig::getInstance();
$checkoutRegistration = $config->get('checkout_registration', 'registration_required');
$user                 = $this->user;

$showLoginForm = ($checkoutRegistration != 'registration_none' && $user->guest);
$hideCheckout  = ($checkoutRegistration == 'registration_required' && $user->guest);

$shippingMethodSettings = array(
	'showTitle' => true,
	'options' => array(
		'shippingMethods' => $this->shippingMethods,
		'extensionName' => 'com_redshopb',
		'ownerName' => implode(',', RedshopbEntityCompany::getInstance($this->companyId)->getPriceGroups()),
		'name' => 'shipping_rate_id',
		'value' => $shippingRateId,
		'id' => 'shipping_rate_id',
		'attributes' => '',
		'customer' => $this->customer
	)
);

$orderCompany = RedshopbEntityCompany::load($this->orderCompany->id);

$orderHeaderSettings = array('isEmail' => false);

// Settings for checkout.delivery_info
$orderHeaderSettings['deliverySettings']                        = array('showTitle' => true);
$orderHeaderSettings['deliverySettings']['deliveryAddress']     = $this->deliveryAddress;
$orderHeaderSettings['deliverySettings']['orderCompany']        = $orderCompany;
$orderHeaderSettings['deliverySettings']['orderDepartment']     = $this->orderDepartment;
$orderHeaderSettings['deliverySettings']['orderEmployee']       = $this->orderEmployee;
$orderHeaderSettings['deliverySettings']['deliveryAddressSpan'] = (!empty($this->orderVendor->name)) ? 'col-md-8' : 'col-md-12';
$orderHeaderSettings['deliverySettings']['showLoginForm']       = $showLoginForm;

// Settings for checkout.vendor_info
$orderHeaderSettings['vendorSettings']                = array('showTitle' => true);
$orderHeaderSettings['vendorSettings']['orderVendor'] = $this->orderVendor;

$commentOrRequisitionIsEmpty = (empty($this->comment) || empty($this->requisition));

// Settings for checkout.comment_requisition comment
$orderHeaderSettings['commentSettings']                = array('showTitle' => true);
$orderHeaderSettings['commentSettings']['comment']     = $this->comment;
$orderHeaderSettings['commentSettings']['commentSpan'] = ($commentOrRequisitionIsEmpty) ? 'col-md-12' : 'col-md-8';

// Settings for checkout.comment_requisition requisition
$orderHeaderSettings['requisitionSettings']                    = array('showTitle' => true);
$orderHeaderSettings['requisitionSettings']['requisition']     = $this->requisition;
$orderHeaderSettings['requisitionSettings']['requisitionSpan'] = ($commentOrRequisitionIsEmpty) ? 'col-md-12' : 'col-md-4';

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

$tabNumber              = 1;
$showShippingDelayOrder = false;

if ($config->getInt('use_shipping_date', 0))
{
	/** @var RedshopbModelShop $model */
	$model          = RedshopbModel::getInstance('Shop', 'RedshopbModel');
	$customerOrders = $model->getCustomerOrders();

	foreach ($customerOrders as $customerOrder)
	{
		foreach ($customerOrder->regular->items as $cartItem)
		{
			if ($cartItem->params->get('delayed_order', 0) == 1)
			{
				$showShippingDelayOrder = true;
			}
		}
	}
}
?>
<div class="redshopb-shop-shipping">
	<div class="content">
		<ul class="nav nav-tabs">
			<li>
				<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_CART_TITLE'); ?></a>
			</li>
			<li>
				<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_TITLE'); ?></a>
			</li>
			<li class="active">
				<a><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_SHIPPING_TITLE'); ?></a>
			</li>
			<?php if ($this->usingPayments) : ?>
				<li>
					<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_PAYMENT_TITLE'); ?></a>
				</li>
			<?php endif; ?>
			<li>
				<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_CONFIRM_TITLE'); ?></a>
			</li>
			<li>
				<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_RECEIPT_TITLE'); ?></a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active">
				<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
					<div class="container-fluid" id="redshopb-delivery-info">
						<div class="row">
							<div class="<?php echo $showShippingDelayOrder ? 'col-md-6' : 'col-md-12' ?> well" id="redshopb-delivery-info-customer">
								<?php echo RedshopbLayoutHelper::render('checkout.shipping_form', $shippingMethodSettings); ?>
							</div>
							<?php if ($showShippingDelayOrder): ?>
								<div class="col-md-6 well" id="redshopb-delivery-delay-info-customer">
									<h4><?php echo Text::_('COM_REDSHOPB_SHOP_DELAY_ORDER') ?></h4>
									<?php
									$shippingMethodSettings['showTitle']        = false;
									$shippingMethodSettings['delayOrder']       = true;
									$shippingMethodSettings['options']['value'] = Factory::getApplication()->getUserState(
										'checkout.shipping_rate_id_delay', ''
									);
									echo RedshopbLayoutHelper::render('checkout.shipping_form', $shippingMethodSettings); ?>
								</div>
							<?php endif; ?>
						</div>
						<div class="row">
							<div class="col-md-12">
								<?php echo RedshopbLayoutHelper::render('checkout.order_header', $orderHeaderSettings); ?>
							</div>
						</div>
					</div>
					<input type="hidden" name="boxchecked" value="1">
					<input type="hidden" name="task" value="">
					<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			</div>
		</div>
	</div>
</div>
