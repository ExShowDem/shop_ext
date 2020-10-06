<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

$data = (object) $displayData;

$output  = isset($data->output) ? $data->output : '';
$isEmail = ($output == 'email');

if ($isEmail)
{
	echo RedshopbLayoutHelper::render('shop.checkout.topemailreceipt', $displayData);

	return;
}

$deliveryAddressSpan = 'span12';

if (!empty($data->orderVendor->name))
{
	$deliveryAddressSpan = 'span8';
}

$commentSpan     = 'span12';
$requisitionSpan = 'span12';

if (!empty($data->comment) && !empty($data->requisition))
{
	$commentSpan     = 'span8';
	$requisitionSpan = 'span4';
}

?>

<div  class="row-fluid">
	<div id="delivery_address_wrapper" class="<?php echo $deliveryAddressSpan;?>">
		<?php echo RedshopbLayoutHelper::render('shop.checkout.deliveryaddress', $displayData); ?>
	</div>
	<?php if ($deliveryAddressSpan === 'span8'):?>
		<div id="vendorinfo_wrapper" class="span4">
			<?php echo RedshopbLayoutHelper::render('shop.checkout.vendorinfo', $displayData); ?>
		</div>
	<?php endif;?>
</div>
<div class="row-fluid">
	<?php if (!empty($data->comment)):?>
		<div id="comment_wrapper" class="<?php echo $commentSpan;?>">
			<?php echo RedshopbLayoutHelper::render('shop.checkout.comment', $displayData); ?>
		</div>
	<?php endif;?>

	<?php if (!empty($data->requisition)):?>
		<div id="requisition_wrapper" class="<?php echo $requisitionSpan;?>">
			<?php echo RedshopbLayoutHelper::render('shop.checkout.requisition', $displayData); ?>
		</div>
	<?php endif;?>
</div>

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
