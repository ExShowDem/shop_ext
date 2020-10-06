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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

// HTML helpers
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('vnrbootstrap.tooltip');
HTMLHelper::_('rjquery.chosen', 'select');
HTMLHelper::_('rsortablelist.main');
RedshopbHtml::loadFooTable();

$action               = RedshopbRoute::_('index.php?option=com_redshopb&view=shop');
$orderId              = Factory::getApplication()->input->getInt('orderId', 0);
$config               = RedshopbEntityConfig::getInstance();
$tabNumber            = 1;
$checkoutRegistration = $config->get('checkout_registration', 'registration_required');
$user                 = $this->user;

$orderCompany        = RedshopbEntityCompany::load($this->orderCompany->id);
$orderHeaderSettings = array('isEmail' => false);

// Settings for checkout.delivery_info
$orderHeaderSettings['deliverySettings']                        = array('showTitle' => true);
$orderHeaderSettings['deliverySettings']['deliveryAddress']     = $this->deliveryAddress;
$orderHeaderSettings['deliverySettings']['orderCompany']        = $orderCompany;
$orderHeaderSettings['deliverySettings']['orderDepartment']     = $this->orderDepartment;
$orderHeaderSettings['deliverySettings']['orderEmployee']       = $this->orderEmployee;
$orderHeaderSettings['deliverySettings']['deliveryAddressSpan'] = (!empty($this->orderVendor->name)) ? 'col-md-8' : 'col-md-12';
$orderHeaderSettings['deliverySettings']['showLoginForm']       = false;

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

/** @var RedshopbModelShop $model */
$model           = $this->getModel('Shop');
$isMultipleOrder = count($this->customerOrder) > 0 ? true : false;
$allOrders       = $this->customerOrder;

if ($isMultipleOrder)
{
	// Left just first order
	foreach ($allOrders as $key => $value)
	{
		$this->customerOrder = array($key => $value);
		break;
	}
}

// Settings for checkout.customer_items
$customerItemsSettings = array(
	'config'              => $config,
	'state'               => $model->getState(),
	'customerOrders'      => $this->customerOrder,
	'form'                => $model->getCustomForm('cartitems'),
	'showStockAs'         => RedshopbHelperStockroom::getStockVisibility(),
	'showToolbar'         => false,
	'checkbox'            => false,
	'quantityfield'       => 'quantity',
	'canEdit'             => false,
	'lockquantity'        => true,
	'shippingRateId'      => $this->shippingRateId,
	'orderId'             => $orderId,
	'showDeliveryAddress' => false,
	'isEmail'             => false,
	'view'                => Factory::getApplication()->input->get('view'),
	'delivery'            => $config->get('stockroom_delivery_time', 'hour'),
	'feeProducts'         => RedshopbHelperShop::getChargeProducts('fee'),
	'return'              => base64_encode('index.php?option=com_redshopb&view=shop&layout=confirm'),
);

$checkoutMode = $config->get('checkout_mode', 'default', 'string');
?>
<?php if ($orderId): ?>
	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				$('.redshopb-receipt-reorder-btn').click(function(event) {
					event.preventDefault();

					var orderId = $(this).attr("data-order");

					// Perform ajax request for remove saved cart
					$.ajax({
						url: "<?php echo Uri::root(); ?>index.php?option=com_redshopb&task=order.ajaxCheckoutCartFromOrder",
						data: {
							"id": orderId,
							"<?php echo Session::getFormToken() ?>": 1
						},
						cache: false,
						type: 'POST',
						dataType: "JSON"
					})
						.success(function(data) {
							if (data == '1') {
								window.location.href = "<?php echo RedshopbRoute::_('index.php?option=com_redshopb&view=shop&layout=cart', false) ?>";
							}
						});
				});
			});
		})(jQuery);
	</script>
<?php endif; ?>
<div class="redshopb-shop-receipt">
	<div class="row col-md-12">
		<ul class="nav nav-tabs">
			<?php if ($checkoutMode == 'default'):?>
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
				<li>
					<a class="disabled" style="cursor: not-allowed"><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_CONFIRM_TITLE'); ?></a>
				</li>
				<li class="active">
					<a><?php echo $tabNumber++; ?>. <?php echo Text::_('COM_REDSHOPB_SHOP_RECEIPT_TITLE'); ?></a>
				</li>
			<?php endif;?>

			<?php if ($orderId && !$this->user->guest): ?>

				<li class="pull-right">
					<a href="javascript:void(0);" class="redshopb-receipt-reorder-btn1" data-order="<?php echo $orderId; ?>">
						<?php echo Text::_('COM_REDSHOPB_SHOP_REORDER') ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active">
				<div class="container-fluid">
					<div class="row">
						<?php
						if ($this->orderPlaced):?>

							<div class="row">
								<div class="col-md-12">
									<?php echo RedshopbLayoutHelper::render('checkout.order_header', $orderHeaderSettings); ?>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<?php $customerItemsSettings['renderedFrom'] = 'receipt'; ?>
									<?php echo RedshopbLayoutHelper::render('checkout.customer_basket', $customerItemsSettings); ?>
								</div>
							</div>
						<?php if ($isMultipleOrder):
								$i = 0;

								/** @var RedshopbModelOrder $orderModel */
								$orderModel = RModel::getAdminInstance('Order', array('ignore_request' => true), 'com_redshopb');

							foreach ($allOrders as $key => $value):
								$i++;

								// Skip first order
								if ($i < 2)
								{
									continue;
								}

								$order        = (object) $orderModel->getItem($key)->getProperties();
								$orderCompany = RedshopbHelperCompany::getCompanyByCustomer($order->customer_id, $order->customer_type);
								$orderCompany = RedshopbEntityCompany::load($orderCompany->id);

								$orderVendor          = RedshopbEntityCompany::getInstance($orderCompany->get('id'))->getVendor()->getItem();
								$orderVendor->address = RedshopbEntityAddress::getInstance($orderVendor->address_id)->getExtendedData();

								// Settings for checkout.delivery_info
								$orderHeaderSettings['deliverySettings']['deliveryAddress']     = RedshopbEntityAddress::getInstance($order->delivery_address_id)
								->getExtendedData();
								$orderHeaderSettings['deliverySettings']['orderCompany']        = $orderCompany;
								$orderHeaderSettings['deliverySettings']['orderDepartment']     = $this->orderDepartment;
								$orderHeaderSettings['deliverySettings']['orderEmployee']       = $this->orderEmployee;
								$orderHeaderSettings['deliverySettings']['deliveryAddressSpan'] = (!empty($orderVendor->name)) ? 'span8' : 'span12';

								// Settings for checkout.vendor_info
								$orderHeaderSettings['vendorSettings']['orderVendor'] = $orderVendor;

								$commentOrRequisitionIsEmpty = (empty($order->comment) || empty($order->requisition));

								// Settings for checkout.comment_requisition comment
								$orderHeaderSettings['commentSettings']['comment']     = $order->comment;
								$orderHeaderSettings['commentSettings']['commentSpan'] = ($commentOrRequisitionIsEmpty) ? 'span12' : 'span8';

								// Settings for checkout.comment_requisition requisition
								$orderHeaderSettings['requisitionSettings']['requisition']     = $order->requisition;
								$orderHeaderSettings['requisitionSettings']['requisitionSpan'] = ($commentOrRequisitionIsEmpty) ? 'span12' : 'span4';

								// Settings for checkout.payment_info
								$orderHeaderSettings['paymentSettings']['paymentTitle'] = RedshopbHelperOrder::getPaymentMethodTitle($order->customer_company, $order->payment_name);
								$orderHeaderSettings['paymentSettings']['paymentName']  = $order->payment_name;

								$stockroomPickupTitle = '';
								$stockroomPickupId    = 0;

								if (isset($order->shipping_details['pickup_stockroom_id'])
									&& $order->shipping_details['pickup_stockroom_id'])
								{
									$stockroomPickupId    = $order->shipping_details['pickup_stockroom_id'];
									$stockroomPickupTitle = RedshopbEntityStockroom::getInstance($order->shipping_details['pickup_stockroom_id'])->get('name');
								}

								// Settings for checkout.shipping_info
								$orderHeaderSettings['shippingSettings']['shippingRateId']       = $order->shipping_rate_id;
								$orderHeaderSettings['shippingSettings']['shippingRateTitle']    = RedshopbShippingHelper::getShippingRateName($order->shipping_rate_id, true, $order->currency, $order->id);
								$orderHeaderSettings['shippingSettings']['stockroomPickupTitle'] = $stockroomPickupTitle;
								$orderHeaderSettings['shippingSettings']['stockroomPickupId']    = $stockroomPickupId;

								// Settings for checkout.customer_items
								$customerItemsSettings['customerOrders'] = array($key => $value);
								$customerItemsSettings['shippingRateId'] = $order->shipping_rate_id;
								?>
								<hr />
								<div class="row">
								<div class="col-md-12">
								<?php echo RedshopbLayoutHelper::render('checkout.order_header', $orderHeaderSettings); ?>
								</div>
								</div>
								<div class="row">
								<div class="col-md-12">
								<?php echo RedshopbLayoutHelper::render('checkout.customer_basket', $customerItemsSettings); ?>
								</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php else:?>
							<div class="alert alert-error">
								<button type="button" class="close" data-dismiss="alert">&times;</button>
								<div class="pagination-centered">
									<h3><?php echo Text::_('COM_REDSHOPB_ORDER_NOT_EXIST') ?></h3>
								</div>
							</div>
						<?php endif;?>
						<form action="<?php echo $action; ?>" name="adminForm" class="adminForm" id="adminForm" method="post">
							<input type="hidden" name="boxchecked" value="1">
							<input type="hidden" name="task" value="">
							<input type="hidden" name="orderId" value="<?php echo $orderId; ?>">
							<input type="hidden" name="multipleOrderIds"
								   value="<?php echo Factory::getApplication()->input->getString('multipleOrderIds', ''); ?>">
							<?php echo HTMLHelper::_('form.token'); ?>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
