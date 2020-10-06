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

$showTitle       = $displayData['showTitle'];
$deliveryAddress = $displayData['deliveryAddress'];
$orderCompany    = $displayData['orderCompany'];
$orderDepartment = $displayData['orderDepartment'];
$orderEmployee   = $displayData['orderEmployee'];
$showLoginForm   = $displayData['showLoginForm'];
$isB2C           = $orderCompany->get('b2c', 0);
$addressClass    = 'col-md-6';
$billingInfo     = array('isNew' => $orderCompany->getTable()->get('isNew'));

if ($isB2C && $deliveryAddress->customer_id)
{
	$billingInfo['customerInfo'] = RedshopbEntityCustomer::getInstance(
		$deliveryAddress->customer_id, $deliveryAddress->customer_type
	)
	->getAddress()
	->getExtendedData();
}
elseif ($isB2C && !$deliveryAddress->customer_id)
{
	$billingInfo['customerInfo'] = $deliveryAddress;
}
else
{
	$orderDepartmentId         = $orderDepartment ? $orderDepartment->id : null;
	$orderEmployeeId           = $orderEmployee ? $orderEmployee->id : null;
	$billingInfo['company']    = $orderCompany;
	$billingInfo['department'] = RedshopbEntityDepartment::load($orderDepartmentId);
	$billingInfo['employee']   = RedshopbEntityUser::load($orderEmployeeId);
}

?>
<?php if ($showTitle) : ?>
	<h4 class="delivery-info-title"><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_INFORMATION_TITLE', true); ?></h4>
<?php endif;?>
<div class="row-fluid">
	<div class="<?php echo $addressClass; ?>" id="billing">

		<?php if (!$showLoginForm || (!$isB2C && !$orderCompany->get('hide_company'))) : ?>
			<h5><?php echo Text::_('COM_REDSHOPB_SHOP_BILLING_ADDRESS_INFO', true); ?></h5>
		<?php endif;?>

		<?php if ($isB2C) : ?>
				<?php echo RedshopbLayoutHelper::render('addresses.billing.b2c', $billingInfo);?>
		<?php elseif (!$isB2C && !$orderCompany->get('hide_company')) : ?>
				<?php echo RedshopbLayoutHelper::render('addresses.billing.b2b', $billingInfo);?>
		<?php endif; ?>
	</div>

	<div class="<?php echo $addressClass; ?>" id="delivery">

		<?php if (!empty($deliveryAddress) || !$showLoginForm) : ?>
			<h5><?php echo Text::_('COM_REDSHOPB_SHOP_DELIVERY_ADDRESS_INFO', true); ?></h5>
			<?php echo RedshopbLayoutHelper::render('addresses.shipping_address', $deliveryAddress);?>
		<?php endif;?>
	</div>
</div>
