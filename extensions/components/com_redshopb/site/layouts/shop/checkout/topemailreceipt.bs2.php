<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

$data = (object) $displayData;

$output = isset($data->output) ? $data->output : '';

if (isset($data->order))
{
	if (property_exists($data->order, 'shipping_details'))
	{
		if (is_string($data->order->shipping_details))
		{
			$registry = new Registry;
			$registry->loadString($data->order->shipping_details);
			$data->order->shipping_details = $registry->toArray();
		}
	}
}

?>

<table class="topreceipt">
	<tr>
		<td class="delivery">
			<h4>
				<?php
				if (isset($data->order->shipping_details['pickup_stockroom_id']))
				{
					echo Text::_('COM_REDSHOPB_SHOP_PICKUP_ADDRESS_TITLE', true);
				}
				else
				{
					echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_ADDRESS_TITLE', true);
				}
				?>
			</h4>
			<p id="delivery-name">
				<?php if (isset($data->deliveryAddress->name)) : ?>
					<?php echo $data->deliveryAddress->name; ?>
				<?php endif; ?>
			</p>
			<p id="delivery-address">
				<?php if (isset($data->deliveryAddress->address)) : ?>
					<?php echo $data->deliveryAddress->address; ?>
				<?php endif; ?>
			</p>
			<p id="delivery-address2">
				<?php if (isset($data->deliveryAddress->address2)) : ?>
					<?php echo $data->deliveryAddress->address2; ?>
				<?php endif; ?>
			</p>
			<p id="delivery-location">
				<?php if (isset($data->deliveryAddress->zip) || isset($data->deliveryAddress->city)) : ?>
					<?php echo $data->deliveryAddress->zip . ' ' . $data->deliveryAddress->city; ?>
				<?php endif; ?>
			</p>
			<p>
				<span id="delivery-state">
					<?php if (isset($data->deliveryAddress->state_name)) : ?>
						<?php echo $data->deliveryAddress->state_name . ',&nbsp;'; ?>
					<?php endif; ?>
				</span>
				<span id="delivery-country">
					<?php if (isset($data->deliveryAddress->country)) : ?>
						<?php echo Text::_($data->deliveryAddress->country); ?>
					<?php endif; ?>
				</span>
			</p>
			<p id="delivery-phone">
				<?php if (isset($data->deliveryAddress->phone)) : ?>
					<?php echo Text::_('COM_REDSHOPB_ADDRESS_PHONE_LABEL') . ': ' . $data->deliveryAddress->phone; ?>
				<?php endif; ?>
			</p>
			<p id="delivery-email">
				<?php if (isset($data->deliveryAddress->email)) : ?>
					<?php echo Text::_('COM_REDSHOPB_ADDRESS_EMAIL_LABEL') . ': ' . $data->deliveryAddress->email; ?>
				<?php endif; ?>
			</p>
		</td>
		<?php
		$orderCompany = RedshopbEntityCompany::load($data->orderCompany->id);
		$billingInfo  = array('isEmail' => true);

		if (!$orderCompany->get('b2c', 0))
		{
			$billingInfo['company']    = $orderCompany;
			$billingInfo['department'] = !is_null($data->orderDepartment)
				? RedshopbEntityDepartment::load($data->orderDepartment->id)
				: RedshopbEntityDepartment::load();

			$billingInfo['employee'] = !is_null($data->orderEmployee)
				? RedshopbEntityUser::load($data->orderEmployee->id)
				: RedshopbEntityUser::load();

			$customerInfo = RedshopbLayoutHelper::render('addresses.billing.b2b', $billingInfo);
		}
		else
		{
			if (Factory::getUser()->guest)
			{
				$billingInfo['customerInfo'] = $data->deliveryAddress;
			}
			else
			{
				$billingInfo['customerInfo'] = RedshopbEntityCustomer::getInstance(
					$data->deliveryAddress->customer_id, $data->deliveryAddress->customer_type
				)
				->getAddress()
				->getExtendedData();
			}

			$customerInfo = RedshopbLayoutHelper::render('addresses.billing.b2c', $billingInfo);
		}
		?>
		<td class="customer">
			<h4><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_CUSTOMER_INFO', true); ?></h4>
			<?php echo $customerInfo; ?>
		</td>
		<td>&nbsp;</td>
		<td class="vendor">
			<h4><?php echo Text::_('COM_REDSHOPB_SHOP_VENDOR_INFORMATION', true); ?></h4>

			<?php if (!empty($data->orderVendor->name)) : ?>
				<p><?php echo $data->orderVendor->name; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->name)) : ?>
				<p><?php echo $data->orderVendor->address->name; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->address)) : ?>
				<p><?php echo $data->orderVendor->address->address; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->address2)) : ?>
				<p><?php echo $data->orderVendor->address->address2; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->zip) || !empty($data->orderVendor->address->city)) : ?>
				<p><?php echo $data->orderVendor->address->zip . ' ' . $data->orderVendor->address->city; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->country)) : ?>
				<p><?php echo Text::_($data->orderVendor->address->country); ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->vat_number)) : ?>
				<p><?php echo Text::_('COM_REDSHOPB_VAT') . ': ' . $data->orderVendor->vat_number; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->phone)) : ?>
				<p><?php echo $data->orderVendor->address->phone; ?></p>
			<?php endif; ?>

			<?php if (!empty($data->orderVendor->address->email)) : ?>
				<p><?php echo $data->orderVendor->address->email; ?></p>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td colspan="3">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td>
			<?php if (isset($data->order)): ?>
				<h4><?php echo Text::_('COM_REDSHOPB_SHOP_SHIPPING_METHOD', true); ?></h4>
				<p><?php echo RedshopbShippingHelper::getShippingRateName($data->order->shipping_rate_id, true, $data->order->currency, $data->order->id) ?></p>
				<?php
				PluginHelper::importPlugin('redshipping');

				Factory::getApplication()->triggerEvent('onAESECExtendedShippingInfo', array($data->order, &$extendedInfo));

				echo $extendedInfo;
				?>
			<?php endif; ?>
		</td>
		<td>
			<?php

			if (isset($data->order->shipping_details['pickup_stockroom_id'])
				&& $data->order->shipping_details['pickup_stockroom_id'])
			{
				$stockroomDetails     = RedshopbEntityStockroom::getInstance($data->order->shipping_details['pickup_stockroom_id']);
				$stockroomPickupTitle = $stockroomDetails->get('name');

				if ($stockroomPickupTitle) : ?>
					<h4><?php echo Text::_('COM_REDSHOPB_STOCKROOM_PICK_UP_LABEL', true) ?></h4>
					<h5><?php echo $stockroomPickupTitle; ?></h5>
				<?php endif;

				$result = $stockroomDetails->getAddress();

				if (!empty($result))
				{
					echo RedshopbLayoutHelper::render('shop.checkout.address', array('address' => $result));
				}
			}

			?>
		</td>
		<td>
			<?php if (isset($data->shippingDate) && $data->shippingDate) : ?>
				<h4><?php echo Text::_('COM_REDSHOPB_ORDER_DELIVERY_DATE', true); ?></h4>
				<?php echo '<p id="shipping-date">' . HTMLHelper::_('date', $data->shippingDate, Text::_('DATE_FORMAT_LC4')) . '</p>' ?>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td colspan="3">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td class="comment" colspan="2">
			<h4><?php echo Text::_('COM_REDSHOPB_ORDER_COMMENT', true); ?></h4>

			<?php if (isset($data->comment) && !empty($data->comment)) : ?>
				<p><?php echo $data->comment; ?></p>
			<?php else : ?>
				<p><?php echo Text::_('COM_REDSHOPB_SHOP_COMMENT_NOT_SET');?></p>
			<?php endif; ?>

		</td>
		<td class="requisition">
			<h4><?php echo Text::_('COM_REDSHOPB_ORDER_REQUISITION', true); ?></h4>

			<?php if (isset($data->requisition) && !empty($data->requisition)) : ?>
				<p><?php echo $data->requisition; ?></p>
			<?php else : ?>
				<p><?php echo Text::_('COM_REDSHOPB_SHOP_REQUISITION_NOT_SET'); ?></p>
			<?php endif; ?>
		</td>
	</tr>
</table>

<?php if (!empty($data->paymentName)) : ?>
	<div  class="row-fluid">
		<div id="paymentinfo_wrapper" class="span12">
			<?php echo RedshopbLayoutHelper::render('shop.checkout.paymentinfo', $displayData); ?>
		</div>
	</div>
<?php endif;

if (!empty($data->shippingRateId)) : ?>
	<div class="row-fluid">
		<div id="shippinginfo_wrapper" class="span12">
			<?php echo RedshopbLayoutHelper::render('shop.checkout.shippinginfo', $displayData); ?>
		</div>
	</div>
<?php endif;
