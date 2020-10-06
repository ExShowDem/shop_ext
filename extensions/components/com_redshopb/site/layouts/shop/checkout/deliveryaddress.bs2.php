<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$data = (object) $displayData;

$output       = isset($data->output) ? $data->output : '';
$isEmail      = ($output == 'email');
$orderCompany = RedshopbEntityCompany::load($data->orderCompany->id);
?>
<div class="well">
	<h4><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_ADDRESS_TITLE', true); ?></h4>
	<div class="row-fluid">
		<div class="span6" id="delivery">
			<h5><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_ADDRESS_INFO', true); ?></h5>
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
						<?php echo $data->deliveryAddress->country; ?>
					<?php endif; ?>
				</span>
			</p>
		</div>
		<?php
		$orderCompany = RedshopbEntityCompany::load($data->orderCompany->id);
		$billingInfo  = array('isEmail' => $isEmail);

		if (!$orderCompany->get('b2c', 0))
		{
			$billingInfo['company']    = $orderCompany;
			$billingInfo['department'] = RedshopbEntityDepartment::load($data->orderDepartment->id);
			$billingInfo['employee']   = RedshopbEntityUser::load($data->orderEmployee->id);

			$customerInfo = RedshopbLayoutHelper::render('addresses.billing.b2b', $billingInfo);
		}
		else
		{
			$billingInfo['customerInfo'] = RedshopbEntityCustomer::getInstance(
				$data->deliveryAddress->customer_id, $data->deliveryAddress->customer_type
			)
			->getAddress()
			->getExtendedData();

			$customerInfo = RedshopbLayoutHelper::render('addresses.billing.b2c', $billingInfo);
		}
		?>
		<div class="span6">
			<h5><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_CUSTOMER_INFO', true); ?></h5>
			<?php echo $customerInfo; ?>
		</div>
	</div>
	<?php if (isset($data->shippingDate) && $data->shippingDate): ?>
		<h5><?php echo Text::_('COM_REDSHOPB_ORDER_DELIVERY_DATE', true); ?></h5>
		<div class="row-fluid">
			<div class="span6">
				<?php echo '<p id="shipping-date">' . HTMLHelper::_('date', $data->shippingDate, Text::_('DATE_FORMAT_LC4')) . '</p>' ?>
			</div>
		</div>
	<?php endif; ?>
</div>
