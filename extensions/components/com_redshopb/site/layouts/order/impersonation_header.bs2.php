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

$rsbUser       = RedshopbHelperUser::getUser();
$customerOrder = $displayData['current_customer_order'];
$canEdit       = isset($displayData['edit']) ? $displayData['edit'] : true;
$hasOffer      = (!empty($customerOrder->offers));

$redshopbConfig = RedshopbEntityConfig::getInstance();
$offersEnabled  = $redshopbConfig->getInt('enable_offer', 1);

$offerUrl       = 'index.php?option=com_redshopb&view=myoffer&layout=requestoffer';
$canImpersonate = RedshopbHelperACL::getPermissionInto('impersonate', 'order');

$customerType = $customerOrder->customerType;
$customerId   = $customerOrder->customerId;

if ($canImpersonate)
{
	$offerUrl .= '&source=' . $customerOrder->customerType . '_' . $customerOrder->customerId;
}

$customerName = RedshopbHelperShop::getCustomerName($customerId, $customerType);

if (!empty($customerOrder->customer_name)
	&& $customerOrder->customer_name != $customerName)
{
	$customerName = $customerOrder->customer_name;
}

if (empty($customerName) && !$canEdit)
{
	return;
}

$saveCartSettings                 = array();
$saveCartSettings['formAction']   = 'index.php?option=com_redshopb&view=shop';
$saveCartSettings['customerId']   = $customerId;
$saveCartSettings['customerType'] = $customerType;
$saveCartSettings['orderId']      = $displayData['orderId'];
$saveCartSettings['return']       = $displayData['return'];

$headerText = Text::_('COM_REDSHOPB_ORDER_CUSTOMER_TITLE')
	. ' : ' . $customerName
	. ' (' . Text::_('COM_REDSHOPB_' . strtoupper($customerType)) . ')';
?>

<div class="row-fluid">
	<div class="span12 <?php echo ($canImpersonate) ? 'alert alert-success' : 'well-small';?>">
			<div class="pull-right">
				<?php if (!empty($rsbUser->id) && $canEdit === true):?>
					<?php echo RedshopbLayoutHelper::render('cart.savecart', $saveCartSettings);?>
				<?php endif; ?>

				<?php if ($canEdit === true && $offersEnabled && !$hasOffer) :?>
					<a class="btn btn-small hasTooltip" href="<?php
					echo RedshopbRoute::_($offerUrl);?>" title="<?php echo Text::_('COM_REDSHOPB_SHOP_SEND_LETTER'); ?>">
						<i class="icon-envelope"></i>&nbsp;<?php echo Text::_('COM_REDSHOPB_ORDER_ORDERITEMS_REQUEST_OFFER') ?>
					</a>
				<?php endif; ?>
			</div>
			<div>
				<?php if ($canImpersonate) : ?>
					<h4><?php echo $headerText; ?></h4>
				<?php endif; ?>
			</div>
	</div>
</div>


