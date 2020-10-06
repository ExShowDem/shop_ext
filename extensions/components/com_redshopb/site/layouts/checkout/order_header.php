<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$isEmail = $displayData['isEmail'];

if ($isEmail)
{
	echo RedshopbLayoutHelper::render('checkout.email_header', $displayData);

	return;
}

$deliverySettings    = $displayData['deliverySettings'];
$deliveryAddressSpan = $deliverySettings['deliveryAddressSpan'];
$vendorSettings      = $displayData['vendorSettings'];
$commentSettings     = $displayData['commentSettings'];
$requisitionSettings = $displayData['requisitionSettings'];
$paymentSettings     = $displayData['paymentSettings'];
$shippingSettings    = $displayData['shippingSettings'];
$showDelayShipping   = false;
$showDelayPayment    = false;

$deliverySettings['isEmail'] = $isEmail;

if (!empty($displayData['shippingDelaySettings']['shippingRateId']))
{
	$showDelayShipping = true;
}

if (!empty($displayData['paymentDelaySettings']['paymentTitle']))
{
	$showDelayPayment = true;
}
?>

<div class="row">
	<div id="delivery_address_wrapper" class="<?php echo $deliveryAddressSpan;?>">
		<div class="well">
			<?php echo RedshopbLayoutHelper::render('checkout.delivery_info', $deliverySettings); ?>
		</div>
	</div>
	<?php if ($deliveryAddressSpan === 'col-md-8'):?>
		<div id="vendorinfo_wrapper" class="col-md-4">
			<?php echo RedshopbLayoutHelper::render('shop.checkout.vendorinfo', $vendorSettings); ?>
		</div>
	<?php endif;?>
</div>

<?php if (!empty($commentSettings['comment']) || !empty($requisitionSettings['requisition'])):?>
	<div class="row">
		<?php if (!empty($commentSettings['comment'])):?>
			<div id="comment_wrapper"  class="<?php echo $commentSettings['commentSpan'];?> well">
				<?php echo RedshopbLayoutHelper::render('checkout.comment_requisition', $commentSettings); ?>
			</div>
		<?php endif;?>

		<?php if (!empty($requisitionSettings['requisition'])):?>
			<div id="requisition_wrapper" class="<?php echo $requisitionSettings['requisitionSpan'];?> well">
				<?php echo RedshopbLayoutHelper::render('checkout.comment_requisition', $requisitionSettings); ?>
			</div>
		<?php endif;?>
	</div>
<?php endif;?>


<?php if (!empty($paymentSettings['paymentName'])) : ?>
	<div  class="row">
		<div id="paymentinfo_wrapper" class="<?php echo $showDelayShipping ? 'col-md-6' : 'col-md-12' ?> well">
			<?php echo RedshopbLayoutHelper::render('checkout.payment_info', $paymentSettings); ?>
		</div>
		<?php if ($showDelayPayment): ?>
		<div id="paymentinfo_delay_wrapper" class="col-md-6 well">
			<h4><?php echo Text::_('COM_REDSHOPB_SHOP_DELAY_ORDER') ?></h4>
			<?php echo RedshopbLayoutHelper::render('checkout.payment_info', $displayData['paymentDelaySettings']); ?>
		</div>
		<?php endif ?>
	</div>
<?php endif;?>

<?php if (!empty($shippingSettings['shippingRateId'])) : ?>
	<div class="row">
		<div id="shippinginfo_wrapper" class="<?php echo $showDelayShipping ? 'col-md-6' : 'col-md-12' ?> well">
			<?php echo RedshopbLayoutHelper::render('checkout.shipping_info', $shippingSettings); ?>
		</div>
		<?php if ($showDelayShipping): ?>
			<div id="shippinginfo_delay_wrapper" class="col-md-6 well">
				<h4><?php echo Text::_('COM_REDSHOPB_SHOP_DELAY_ORDER') ?></h4>
				<?php echo RedshopbLayoutHelper::render('checkout.shipping_info', $displayData['shippingDelaySettings']); ?>
			</div>
		<?php endif ?>
	</div>
<?php endif;
