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

$action      = RedshopbRoute::_('index.php?option=com_redshopb&view=shop');
$paymentName = Factory::getApplication()->getUserState('checkout.payment_name', '');
$tabNumber   = 1;
$app         = Factory::getApplication();

$paymentSettings = array(
	'showTitle' => true,
	'paymentMethods' => $this->paymentMethods,
	'companyId' => $this->companyId
);
$orderCompany    = RedshopbEntityCompany::load($this->orderCompany->id);

$config               = RedshopbEntityConfig::getInstance();
$checkoutRegistration = $config->get('checkout_registration', 'registration_required');
$user                 = $this->user;

$showLoginForm = ($checkoutRegistration != 'registration_none' && $user->guest);
$hideCheckout  = ($checkoutRegistration == 'registration_required' && $user->guest);

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
			if (isset($cartItem->params)
				&& $cartItem->params instanceof Joomla\Registry\Registry
				&& $cartItem->params->get('delayed_order', 0) == 1)
			{
				$showShippingDelayOrder = true;
			}
		}
	}
}

?>
<div class="redshopb-shop-payment">
	<div class="content">
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
			<li class="active">
				<a><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_PAYMENT_TITLE'); ?></a>
			</li>
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
						<div class="row-fluid">
							<div class="<?php echo $showShippingDelayOrder ? 'span6' : 'span12' ?> well" id="redshopb-delivery-info-customer">
								<?php echo RedshopbLayoutHelper::render('checkout.payment_form', $paymentSettings);?>
							</div>
							<?php if ($showShippingDelayOrder): ?>
								<div class="span6 well" id="redshopb-delivery-delay-info-customer">
									<h3><?php echo Text::_('COM_REDSHOPB_SHOP_DELAY_ORDER') ?></h3>
									<?php echo RedshopbLayoutHelper::render('checkout.payment_delay_form', $paymentSettings); ?>
								</div>
							<?php endif ?>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<?php
								echo RedshopbLayoutHelper::render('checkout.order_header', $orderHeaderSettings); ?>
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
