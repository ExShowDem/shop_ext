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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

$data = (object) $displayData;

$shippingTitle = 'COM_REDSHOPB_SHOP_DELIVERY_ADDRESS_TITLE';

if (!empty($data->order->shipping_details['pickup_stockroom_id']))
{
	$shippingTitle = 'COM_REDSHOPB_SHOP_PICKUP_ADDRESS_TITLE';
}

$deliveryAddress = $data->deliveryAddress;
?>
<table style="width: 100%;">
	<tr>
		<td style="width: 33%;" valign="top">
			<h4>
				<?php echo Text::_($shippingTitle, true); ?>
			</h4>
			<br />
			<div>
				<?php echo RedshopbLayoutHelper::render('addresses.shipping_address', $deliveryAddress);?>
			</div>
		</td>
		<td style="width: 33%;" valign="top">
			<?php if (!$data->orderCompany->get('b2c', 0) && !$data->orderCompany->get('hide_company', 0)): ?>
			<h4><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_CUSTOMER_INFO', true); ?></h4>
			<br />
			<div>
				<?php if (isset($data->orderCompany->name) && !empty($data->orderCompany->name)): ?>
				<p><?php echo Text::_('COM_REDSHOPB_COMPANY') . ': ' . $data->orderCompany->name ?></p>
				<?php endif; ?>

				<?php if (isset($data->orderDepartment->name) && !empty($data->orderDepartment->name)): ?>
				<p><?php echo Text::_('COM_REDSHOPB_DEPARTMENT') . ': ' . $data->orderDepartment->name ?></p>
				<?php endif; ?>

				<?php if (isset($data->orderEmployee->name) && !empty($data->orderEmployee->name)): ?>
				<p>
					<?php
					echo Text::_('COM_REDSHOPB_EMPLOYEE') . ': '
						. ((!empty($data->orderEmployee->number)) ? $data->orderEmployee->number . ' ' : '')
						. $data->orderEmployee->name;
					?>
				</p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</td>
		<td style="width: 33%;" valign="top">
			<h4><?php echo Text::_('COM_REDSHOPB_SHOP_VENDOR_INFORMATION', true); ?></h4>
			<br />
			<div>
				<?php
				if (isset($data->orderVendor->name) && !empty($data->orderVendor->name))
				{
					echo '<p>' . $data->orderVendor->name . '</p>';
				}

				if (isset($data->orderVendor->address->address) && !empty($data->orderVendor->address->address))
				{
					echo '<p>' . $data->orderVendor->address->address . '</p>';
				}

				if (isset($data->orderVendor->address->zip) && !empty($data->orderVendor->address->zip))
				{
					echo '<p>' . $data->orderVendor->address->zip . '</p>';
				}

				if (isset($data->orderVendor->address->city) && !empty($data->orderVendor->address->city))
				{
					echo '<p>' . $data->orderVendor->address->city . '</p>';
				}

				if (isset($data->orderVendor->address->country) && !empty($data->orderVendor->address->country))
				{
					echo '<p>' . Text::_($data->orderVendor->address->country) . '</p>';
				}
				?>
			</div>
		</td>
	</tr>
	<tr>
		<td style="width: 33%;" valign="top">
			<br />
			<h4><?php echo Text::_('COM_REDSHOPB_SHOP_SHIPPING_METHOD', true); ?></h4>
			<br />
			<div>
				<p><?php echo RedshopbShippingHelper::getShippingRateName($data->order->shipping_rate_id, true, $data->order->currency, $data->order->id) ?></p>
				<?php
				PluginHelper::importPlugin('redshipping');

				Factory::getApplication()->triggerEvent('onAESECExtendedShippingInfo', array($data->order, &$extendedInfo));

				if (isset($extendedInfo))
				{
					echo '<br/>' . $extendedInfo;
				}

				?>
			</div>
		</td>
		<td style="width: 33%;" valign="top">
			<?php
			if (isset($data->order->shipping_details['pickup_stockroom_id'])
				&& $data->order->shipping_details['pickup_stockroom_id'])
			{
				$stockroomDetails     = RedshopbEntityStockroom::getInstance($data->order->shipping_details['pickup_stockroom_id']);
				$stockroomPickupTitle = $stockroomDetails->get('name');

				if ($stockroomPickupTitle):
					?>
					<br />
					<h4><?php echo Text::_('COM_REDSHOPB_STOCKROOM_PICK_UP_LABEL', true) ?></h4>
					<br />
					<div>
						<p><?php echo $stockroomPickupTitle; ?></p>
					</div>
				<?php endif;

				$result = $stockroomDetails->getAddress();

				if (!empty($result))
				{
					echo RedshopbLayoutHelper::render('shop.checkout.address', array('address' => $result));
				}
			}

				?>
		</td>
		<td style="width: 33%;" valign="top">
			<?php if (isset($data->order->shipping_date) && $data->order->shipping_date): ?>
				<br />
				<h4><?php echo Text::_('COM_REDSHOPB_ORDER_DELIVERY_DATE', true); ?></h4>
				<br />
				<div>
					<p><?php echo $data->order->shipping_date ?></p>
				</div>
			<?php endif; ?>
		</td>
	</tr>
</table>
<br />
<table>
	<tr>
		<td style="width: 66%;">
			<?php if (isset($data->comment) && !empty($data->comment)):?>
			<h4><?php echo Text::_('COM_REDSHOPB_ORDER_COMMENT', true); ?></h4>
			<br />
			<p><?php echo $data->comment; ?></p>
			<?php endif;?>
		</td>
		<td style="width: 33%;">
			<?php if (isset($data->requisition) && !empty($data->requisition)):?>
			<h4><?php echo Text::_('COM_REDSHOPB_ORDER_REQUISITION', true); ?></h4>
			<br />
			<p><?php echo $data->requisition;?></p>
			<?php endif;?>
		</td>
	</tr>
</table>
