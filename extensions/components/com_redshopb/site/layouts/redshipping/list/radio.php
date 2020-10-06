<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Controllers
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$data = $displayData;

$shippingMethods    = $data['options']['shippingMethods'];
$extensionName      = $data['options']['extensionName'];
$ownerName          = $data['options']['ownerName'];
$customer           = $data['options']['customer'];
$delayOrder         = isset($data['delayOrder']) ? $data['delayOrder'] : false;
$name               = !empty($data['options']['name']) ? $data['options']['name'] : 'redshipping_shipping';
$value              = !empty($data['options']['value']) ? $data['options']['value'] : '';
$id                 = !empty($data['options']['id']) ? $data['options']['id'] : 'redshipping_shipping';
$attr               = !empty($data['options']['attributes']) ? $data['options']['attributes'] : '';
$options            = array();
$notShipperSelected = false;
HTMLHelper::_('vnrbootstrap.tooltip');

$suffix = $delayOrder ? '_delay' : '';

if (!empty($shippingMethods)) : ?>
	<div class="controls">
<?php foreach ($shippingMethods as $key => $shippingMethod) : ?>
		<h5>
			<?php echo $shippingMethod->params->get('shipping_title', $shippingMethod->text) ?>
			<?php if ($shippingMethod->params->get('shipping_logo', '') != '') : ?>
				<br />
				<img
					src="<?php echo $shippingMethod->params->get('shipping_logo', '') ?>"
					alt="<?php echo RedshopbHelperThumbnail::safeAlt($shippingMethod->params->get('shipping_title', $shippingMethod->text)) ?>" />
			<?php endif; ?>
		</h5>
		<?php

		if ($shippingMethod->params->get('is_shipper', 0))
		{
			$class = 'isShipper';
		}
		else
		{
			$class = 'isNotShipper';
		}

		foreach ($shippingMethod->shippingRates as $shippingRate) :
			$checked = ($value == $shippingRate->id) || (count($shippingMethods) == 1 && count($shippingMethod->shippingRates) == 1) ?
				' checked="checked" ' : '';

			if ($class == 'isNotShipper' && $checked)
			{
				$notShipperSelected = true;
			}
			?>
			<label for="shippingRate<?php echo $shippingRate->id . $suffix ?>" id="<?php echo $shippingRate->id . $suffix ?>-lbl" class="radio">
				<input type="radio" name="<?php echo $name . $suffix ?>" id="shippingRate<?php echo $shippingRate->id . $suffix ?>" class="<?php echo $class; ?>"
					value="<?php echo $shippingRate->id ?>" <?php echo trim($attr) . $checked ?> />
				<?php echo $shippingRate->name ?> (<?php echo RHelperCurrency::getFormattedPrice($shippingRate->price, $customer->currency_id) ?>)
			</label>

			<?php if (method_exists($shippingMethod->helper, 'extend')) : ?>
					<div class="shippingRate<?php echo $shippingRate->id . $suffix ?> extended-data" style="display: none;">
						<?php echo $shippingMethod->helper->extend($data, $shippingRate); ?>
					</div>
			<?php endif; ?>

		<?php endforeach; ?>
<?php endforeach; ?>
<script>
	var methods = jQuery('#shippingMethods input:radio[name=shipping_rate_id]');

	if (0 === methods.length)
	{
		methods = jQuery('#redshopb-delivery-info-customer input:radio[name=shipping_rate_id]');
	}

	methods.each(function (key, value) {
		value = jQuery(value);
		var checked = value.attr('checked') !== undefined;

		if (true === checked)
		{
			jQuery('.extended-data.' + value.attr('id')).show();
		}
	});

	jQuery('#shippingMethods, #redshopb-delivery-info-customer').on('click', 'input[id^=shippingRate]', function () {
		jQuery('.extended-data').hide();
		jQuery('.extended-data.' + this.id).show();
	});
</script>
<?php
$pickUpStockroomList = RedshopbHelperStockroom::getPickUpStockroomList($customer->id);

if ($pickUpStockroomList)
{
	$stockroomPickupId = Factory::getApplication()->getUserState('checkout.pickup_stockroom_id' . $suffix, 0);
	Factory::getDocument()->addScriptDeclaration('
			(function($){
				$(document).ready(function () {
					$(\'.isNotShipper\').on(\'click\', function(){
						$(\'#pickUpStockrooms\').removeClass(\'hide\');
					});
					$(\'.isShipper\').on(\'click\', function(){
						$(\'#pickUpStockrooms\').addClass(\'hide\');
					});
				});
			})(jQuery);
			'
	);

?>
<div class="pickUpStockrooms <?php echo ($notShipperSelected ? '' : 'hide'); ?>" id="pickUpStockrooms">
<h5><?php echo Text::_('COM_REDSHOPB_STOCKROOM_PICK_UP_LABEL'); ?></h5>
<?php

$quantities = array();
$customers  = RedshopbHelperCart::getCartCustomers();

foreach ($customers as $customer)
{
	$cstring      = explode('.', $customer);
	$customerId   = $cstring[1];
	$customerType = $cstring[0];
	$cart         = RedshopbHelperCart::getCart($customerId, $customerType);
	$items        = $cart->get('items', array());

	foreach ($items as $cartItem)
	{
		$isDelayOrder = $cartItem['params']->get('delayed_order', 0);

		if ($delayOrder ? $isDelayOrder == 0 : $isDelayOrder == 1)
		{
			continue;
		}

		if (!empty($cartItem['productItem']))
		{
			$key = 'productItem_' . $cartItem['productItem'];
		}
		else
		{
			$key = 'productId_' . $cartItem['productId'];
		}

		if (!array_key_exists($key, $quantities))
		{
			$quantities[$key] = 0;
		}

		$quantities[$key] += $cartItem['quantity'];
	}
}

foreach ($pickUpStockroomList as $key => $pickUpStockroom)
{
	$availableQuantity = true;

	foreach ($quantities as $qKey => $quantity)
	{
		list($type, $id) = explode('_', $qKey);

		if ($type == 'productItem')
		{
			$stockroom = RedshopbHelperStockroom::getProductItemStockroomData($id, array($pickUpStockroom->id));

			if ($stockroom)
			{
				$stockroom = $stockroom[$id . '_' . $pickUpStockroom->id];
			}
		}
		else
		{
			$stockroom = RedshopbHelperStockroom::getProductStockroomData($id, $pickUpStockroom->id);
		}

		if ($stockroom)
		{
			if (!$stockroom->unlimited && $stockroom->amount < $quantity)
			{
				$availableQuantity = false;
				break;
			}
		}
		else
		{
			$availableQuantity = false;
			break;
		}
	}

	$checked = '';

	if ((!$stockroomPickupId && $key == 0)
		|| ($stockroomPickupId == $pickUpStockroom->id))
	{
		$checked = ' checked="checked" ';
	}

	?>
	<label for="pickupStockroom<?php echo $pickUpStockroom->id . $suffix ?>" id="pickupStockroom<?php echo $pickUpStockroom->id . $suffix ?>-lbl" class="radio">
		<input type="radio" name="pickup_stockroom_id<?php echo $suffix ?>" id="pickupStockroom<?php echo $pickUpStockroom->id . $suffix ?>"
			   value="<?php echo $pickUpStockroom->id ?>" <?php echo $checked ?> />
		<?php if ($availableQuantity): ?>
						<?php echo $pickUpStockroom->name ?>
		<?php else: ?>
						<span class="label label-warning hasTooltip" title="<?php echo Text::_('COM_REDSHOPB_STOCKROOM_LOCATION_DONT_HAVE_WHOLE_PRODUCTS'); ?>">
							<?php echo $pickUpStockroom->name ?>
						</span>
		<?php endif; ?>
		</label>
		<?php
}
?>
</div>
<?php
}
		?>
	</div>
<?php endif;
