<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * ===============================
 *
 * @var  array  $displayData        Layout data
 * @var  object $customerOrder      Customer order data
 * @var  int    $orderCount         Order count
 * @var  array  $shippingDate       Shipping dates
 * @var  array  $shippingDateDelay  Shipping dates
 * @var  string $datePickerSettings Date picker setting in JSON format.
 */

extract($displayData);

if (!isset($delayOrder))
{
	$delayOrder = false;
}

$suffix = '';

if ($delayOrder)
{
	$suffix       = '_delay';
	$shippingDate = $shippingDateDelay;
}

$key          = $customerOrder->customerType . '_' . $customerOrder->customerId;
$customerName = '';

if ($orderCount > 1)
{
	$customerName = RedshopbHelperShop::getCustomerName($customerOrder->customerId, $customerOrder->customerType) . ' - ';
}

$minDate      = RedshopbApp::getConfig()->getInt('shipping_skip_from_today', 0);
$showSaturday = RedshopbApp::getConfig()->getBool('shipping_include_saturday', 1);
$showSunday   = RedshopbApp::getConfig()->getBool('shipping_include_sunday', 1);
$showDayOff   = RedshopbApp::getConfig()->getBool('shipping_include_day_off', 1);

if (!$showDayOff)
{
	/** @var RedshopbEntityCompany $vendor */
	$vendor   = RedshopbHelperCompany::getVendorCompanyByCustomer($customerOrder->customerId, $customerOrder->customerType);
	$holidays = RedshopbHelperStockroom::getHolidays($vendor->getAddress()->get('country_id'));
}

$shippingDateResult = true;

if (array_key_exists($key, $shippingDate) && !empty($shippingDate[$key]))
{
	if (!RedshopbHelperOrder::isShippingDateAvailable($shippingDate[$key], $customerOrder->customerType, $customerOrder->customerId))
	{
		$shippingDateResult = false;
	}

	if ($shippingDateResult && RedshopbApp::getConfig()->getAllowSplittingOrder())
	{
		$minStockroomDeliveryDate = RedshopbHelperStockroom::getMinimumDeliveryPeriodForOrder(
			$customerOrder->customerId, $customerOrder->customerType, !$delayOrder
		);

		if ($minStockroomDeliveryDate == -1)
		{
			$shippingDateResult = false;
		}
		elseif ($minStockroomDeliveryDate > 0)
		{
			$customerDeliveryDay = new DateTime($shippingDate[$key]);
			$minDeliveryDay      = new DateTime('today');
			$minDeliveryDay->modify('+' . (int) $minStockroomDeliveryDate . ' day');

			if ($minDeliveryDay->format('Ymd') > $customerDeliveryDay->format('Ymd'))
			{
				$shippingDateResult = false;
			}
		}
	}
}

?>
<div class="control-group">
	<div class="control-label">
		<label id="shipping_date<?php echo $suffix ?>_<?php echo $key; ?>-lbl"
			   for="shipping_date<?php echo $suffix ?>_<?php echo $key; ?>" class="hasTooltip" title=""
			   data-original-title="<strong><?php echo Text::_('COM_REDSHOPB_ORDER_DELIVERY_DATE'); ?></strong><br /><?php echo Text::_('COM_REDSHOPB_ORDER_DELIVERY_DATE_DESC'); ?>">
			<?php echo $customerName . Text::_('COM_REDSHOPB_ORDER_DELIVERY_DATE'); ?>
		</label>
	</div>
	<div class="controls">
		<input id="shipping_date<?php echo $suffix ?>_<?php echo $key; ?>"
			   name="shipping_date<?php echo $suffix ?>[<?php echo $key; ?>]"
			   type="text" class="rdatepicker input-medium<?php echo !$shippingDateResult ? ' notProperDeliveryDateSelected' : '' ?>"
			   value="<?php echo (array_key_exists($key, $shippingDate)) ? $shippingDate[$key] : ''; ?>">
	</div>
</div>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			<?php if (RedshopbApp::getConfig()->getAllowSplittingOrder()): ?>
			$('#shipping_date<?php echo $suffix ?>_<?php echo $key;?>').on('change', function (event) {
				redSHOPB.shop.checkout.updateShippingDate(event);
			});
			<?php endif; ?>
			$('#shipping_date<?php echo $suffix ?>_<?php echo $key;?>').datepicker(<?php echo $datePickerSettings ?>);
			$('#shipping_date<?php echo $suffix ?>_<?php echo $key;?>').datepicker("option", "minDate", <?php echo $minDate ?>);

			<?php if (!$showSaturday || !$showSunday || !$showDayOff):?>
			$('#shipping_date<?php echo $suffix ?>_<?php echo $key;?>').datepicker("option", "beforeShowDay", function (date) {
				var weekDay = date.getDay();

				<?php if (!$showSaturday):?>
				// Don't show saturday.
				if (weekDay == 6) {
					return [false];
				}
				<?php endif; ?>

				<?php if (!$showSunday):?>
				// Don't show sunday.
				if (weekDay == 0) {
					return [false];
				}
				<?php endif; ?>
				<?php
				if (!$showDayOff && !empty($holidays)):?>
				var holidays = <?php echo json_encode($holidays) ?>;
				if (holidays['date'].indexOf(jQuery.datepicker.formatDate('yy-mm-dd', date)) != -1
					|| holidays['annual'].indexOf(jQuery.datepicker.formatDate('mm-dd', date)) != -1){
					return [false];
				}
				<?php endif; ?>

				return [true];
			});
			<?php endif; ?>
		});
	})(jQuery)
</script>
