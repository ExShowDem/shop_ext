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
HTMLHelper::_('rjquery.ui');
HTMLHelper::_('rjquery.datepicker');
RHelperAsset::load('rdatepicker.min.css', 'redcore');

$app    = Factory::getApplication();
$jinput = $app->input;
$itemId = $jinput->getInt('Itemid', 0);
$action = RedshopbRoute::_('index.php?option=com_redshopb&view=shop');
$config = RedshopbApp::getConfig();

/** @var RedshopbModelShop $model */
$model        = $this->getModel('Shop');
$orderCompany = RedshopbEntityCompany::load($this->orderCompany->id);

$checkoutRegistration = $config->get('checkout_registration', 'registration_required');
$isGuest              = $this->user->guest;

$showLoginForm = ($checkoutRegistration != 'registration_none' && $isGuest);
$hideCheckout  = ($checkoutRegistration == 'registration_required' && $isGuest);

// Settings for checkout.delivery_info
$deliverySettings                    = array('showTitle' => true);
$deliverySettings['deliveryAddress'] = $this->deliveryAddress;
$deliverySettings['orderCompany']    = $orderCompany;
$deliverySettings['orderDepartment'] = $this->orderDepartment;
$deliverySettings['orderEmployee']   = $this->orderEmployee;
$deliverySettings['showLoginForm']   = $showLoginForm;

$deliveryAddressSpan = (!empty($this->orderVendor->name)) ? 'span6' : 'span12';

// Settings for checkout.vendor_info
$vendorSettings                = array('showTitle' => true);
$vendorSettings['orderVendor'] = $this->orderVendor;

// Settings for checkout.delivery_address_form
$deliveryFormSettings                     = array('showTitle' => true);
$deliveryFormSettings['address']          = (array) $this->deliveryAddress;
$deliveryFormSettings['fields']           = $this->checkoutFields;
$deliveryFormSettings['manageOwnAddress'] = $this->ownAddressManage;
$deliveryFormSettings['showLoginForm']    = $showLoginForm;

$requisition = $this->checkoutFields->requisition;
$comment     = $this->checkoutFields->comment;
$tabNumber   = 1;

if ($config->getInt('use_shipping_date', 0))
{
	$customerOrders      = $model->getCustomerOrders();
	$countCustomerOrders = count($customerOrders);
	$ids                 = array();
	$shippingDate        = (array) $app->getUserState('checkout.shipping_date', array());

	// Settings for checkout.shipping_date
	$shippingDateSettings                      = array();
	$shippingDateSettings['orderCount']        = count($customerOrders);
	$shippingDateSettings['shippingDate']      = (array) $app->getUserState('checkout.shipping_date', array());
	$shippingDateSettings['shippingDateDelay'] = (array) $app->getUserState('checkout.shipping_date_delay', array());

	$datePickerSettings               = array();
	$datePickerSettings['buttonText'] = '<i class="icon-calendar icon-2x"></i>';
	$datePickerSettings['dateFormat'] = 'yy-mm-dd';
	$datePickerSettings['minDate']    = 1;
	$datePickerSettings['showOn']     = 'both';

	$shippingDateSettings['datePickerSettings'] = json_encode((object) $datePickerSettings);
}

?>
<div class="redshopb-shop-delivery">
	<div class="content">
		<ul class="nav nav-tabs">
			<li>
				<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_CART_TITLE'); ?></a>
			</li>
			<li class="active">
				<a><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_TITLE'); ?></a>
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
			<li>
				<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_CONFIRM_TITLE'); ?></a>
			</li>
			<li>
				<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_RECEIPT_TITLE'); ?></a>
			</li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active">
				<?php if ($isGuest):?>
					<div class="row-fluid">
						<div class="span12">
							<?php echo RedshopbLayoutHelper::render('checkout.login_register', $deliveryFormSettings); ?>
						</div>
					</div>
				<?php endif;?>
				<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
					<div class="container-fluid" id="redshopb-delivery-info">
						<div class="row-fluid">
							<?php if (!$isGuest):?>
								<div class="span6">
									<div class="row-fluid">
										<div class="span12 well">
											<div class="row-fluid">
												<div class="span12" id="redshopb-delivery-info-address">
													<?php echo RedshopbLayoutHelper::render('checkout.delivery_address_form', $deliveryFormSettings);?>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php endif;?>
							<div class="span6">
								<div class="row-fluid">
									<div class="span12 well">
										<?php if (!$isGuest):?>
											<div class="row-fluid">
												<div class="span12">
													<?php echo RedshopbLayoutHelper::render('checkout.delivery_info', $deliverySettings);?>
												</div>
											</div>
										<?php endif; ?>

										<?php if ($config->getInt('use_shipping_date', 0)):
											$showShippingDelayOrder = false; ?>
											<div class="row-fluid">
												<div class="span12">
													<?php foreach ($customerOrders as $customerOrder):
														$shippingDateSettings['customerOrder'] = $customerOrder;

														foreach ($customerOrder->regular->items as $cartItem)
														{
															if ($cartItem->params->get('delayed_order', 0) == 1)
															{
																$showShippingDelayOrder = true;
															}
														}

														echo RedshopbLayoutHelper::render('checkout.shipping_date', $shippingDateSettings);?>
													<?php endforeach;?>
												</div>
											</div>
										<?php if ($showShippingDelayOrder): ?>
											<div class="row-fluid">
												<div class="span12">
													<h4><?php echo Text::_('COM_REDSHOPB_SHOP_DELAY_ORDER') ?></h4>
													<?php foreach ($customerOrders as $customerOrder):
														$shippingDateSettings['customerOrder'] = $customerOrder;
														$shippingDateSettings['delayOrder']    = true;
														echo RedshopbLayoutHelper::render('checkout.shipping_date', $shippingDateSettings); ?>
													<?php endforeach; ?>
												</div>
											</div>
										<?php endif;?>
										<?php endif;?>
										<div class="row-fluid">
											<div class="span12">
												<?php echo RedshopbLayoutHelper::render('checkout.vendor_info', $vendorSettings);?>
											</div>
										</div>
										<div class="row-fluid">
											<div class="span12" id="redshopb-delivery-info-comments">
												<?php echo RedshopbLayoutHelper::render('checkout.additional_info', $deliveryFormSettings);?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php echo $this->checkoutFields->type->input; ?>
						<input type="hidden" name="boxchecked" value="1">
						<input type="hidden" name="task" value="">
						<?php echo HTMLHelper::_('form.token'); ?>
				</form>
			</div>
		</div>
	</div>
</div>

<?php echo RedshopbLayoutHelper::render('checkout.address.controls');
