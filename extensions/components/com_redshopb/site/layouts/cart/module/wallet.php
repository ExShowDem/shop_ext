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
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('rbootstrap.tooltip');

$customerType = $displayData['customerType'];
$customerId   = $displayData['customerId'];

if (empty($customerId))
{
	$customerType = 'employee';
	$customerId   = RedshopbHelperUser::getUserRSid();
}

$currency    = RedshopbHelperUser::getCurrency($customerId);
$recordCount = 0;

$userWallet  = RedshopbHelperWallet::getUserWallet($customerId);
$moneyAmount = array();

if (!is_null($userWallet))
{
	$moneyAmount = RedshopbHelperWallet::getMoneyAmount($userWallet->id);
}

?>

<div class="col-md-12 cartLabelCredit">
	<span class="cartLabelCreditText">
		<?php echo Text::_('COM_REDSHOPB_SHOP_EMPLOYEE_CREDIT'); ?>:
	</span>
	<span class="cartLabelCreditSubtotalValue">
		<?php foreach ($moneyAmount as $employeeMoney): ?>
			<?php if ((float) $employeeMoney['amount'] != 0.0):?>
				<?php echo RedshopbHelperProduct::getProductFormattedPrice($employeeMoney['amount'], $employeeMoney['alpha']);?>
				<br/>
				<?php $recordCount++; ?>
			<?php endif; ?>
		<?php endforeach; ?>

		<?php if ($recordCount == 0): ?>
				0,00<br/>
		<?php endif; ?>
	</span>
</div>
