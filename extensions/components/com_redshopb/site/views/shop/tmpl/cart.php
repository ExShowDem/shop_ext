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
use Joomla\CMS\Factory;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');

// HTMLHelper::_('rsortablelist.main');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('rbootstrap.tooltip');
RedshopbHtml::loadFooTable();

$app          = Factory::getApplication();
$customerId   = $app->getUserState('shop.customer_id', 0);
$customerType = $app->getUserState('shop.customer_type', '');
/** @var RedshopbModelShop $model */
$model        = $this->getModel('Shop');
$action       = RedshopbRoute::_('index.php?option=com_redshopb&view=shop');
$orderId      = $app->getUserState('checkout.orderId', 0);
$config       = RedshopbEntityConfig::getInstance();
$checkoutMode = $config->get('checkout_mode', 'default', 'string');
$orderCompany = RedshopbEntityCompany::load($this->orderCompany->id);

$billingAddressId = 0;

switch ($customerType)
{
	case 'employee':
		$billingAddressId = RedshopbEntityUser::getInstance($customerId)->getBillingAddress()->getExtendedData()->id;
		break;
	case 'department':
		$billingAddressId = RedshopbEntityDepartment::getInstance($customerId)->getBillingAddress()->getExtendedData()->id;
		break;
	case  'company':
		$billingAddress   = RedshopbEntityCompany::load($customerId);
		$billingAddressId = $billingAddress->get('address_id');
		break;
}

if ($checkoutMode === 'default')
{
	$app->setUserState('checkout.delivery_address_id', $billingAddressId);
	$app->setUserState("checkout.usebilling", true);
}

$renderConfig = array (
	'config'              => $config,
	'state'               => $model->getState(),
	'customerOrders'      => $model->getCustomerOrders(),
	'form'                => $model->getCustomForm('cartitems'),
	'showStockAs'         => RedshopbHelperStockroom::getStockVisibility(),
	'showToolbar'         => false,
	'total'               => false,
	'quantityfield'       => 'quantity',
	'checkbox'            => false,
	'userCart'            => true,
	'return'              => base64_encode('index.php?option=com_redshopb&view=shop&layout=cart'),
	'action'              => $action,
	'orderId'             => $orderId,
	'usingShipping'       => $this->usingShipping,
	'usingPayments'       => $this->usingPayments,
	'paymentMethods'      => $this->paymentMethods,
	'shippingMethods'     => $this->shippingMethods,
	'shippingRateId'      => $this->shippingRateId,
	'companyId'           => $this->companyId,
	'deliveryAddress'     => $this->deliveryAddress,
	'orderCompany'        => $orderCompany,
	'orderDepartment'     => $this->orderDepartment,
	'orderEmployee'       => $this->orderEmployee,
	'orderVendor'         => $this->orderVendor,
	'checkoutFields'      => $this->checkoutFields,
	'ownAddressManage'    => $this->ownAddressManage,
	'showCartHeader'      => true,
	'showDeliveryAddress' => false,
	'isEmail'             => false,
	'lockquantity'        => false,
	'canEdit'             => true,
	'view'                => $app->input->get('view', 'order'),
	'delivery'            => $config->get('stockroom_delivery_time', 'hour'),
	'feeProducts'         => RedshopbHelperShop::getChargeProducts('fee'),
	'terms'               => (!empty($this->terms)) ? $this->terms : '',
	'user'                => $this->user,
	'toolbar'             => $this->getCheckoutCartToolbar($orderId, new RToolbarButtonGroup('pull-right'))
);

$renderConfig['shippingMethodSettings'] = array(
	'showTitle' => true,
	'options'   => array(
		'shippingMethods' => $this->shippingMethods,
		'extensionName'   => 'com_redshopb',
		'ownerName'       => implode(',', RedshopbEntityCompany::getInstance($this->companyId)->getPriceGroups()->ids()),
		'name'            => 'shipping_rate_id',
		'value'           => $this->shippingRateId,
		'id'              => 'shipping_rate_id',
		'attributes'      => '',
		'customer'        => $this->customer
	)
);
?>
<div class="row">
	<div class="col-md-12">
		<?php echo RedshopbLayoutHelper::render('checkout.' . $checkoutMode, $renderConfig); ?>
	</div>
</div>
<script>
	// Make sure the customer can only click the button once.
	jQuery('.js-complete-order.btn-success').click(function(){
		jQuery('.js-complete-order.btn-success').css('pointer-events', 'none');
	});
</script>
