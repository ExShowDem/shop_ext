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
use Joomla\CMS\Factory;

$showTitle   = $displayData['showTitle'];
$payments    = $displayData['paymentMethods'];
$paymentName = Factory::getApplication()->getUserState('checkout.payment_name', '');

$paymentSettings = array(
	'options' => array(
		'payments' => $payments,
		'extensionName' => 'com_redshopb',
		'ownerName' => implode(',', RedshopbEntityCompany::getInstance($displayData['companyId'])->getPriceGroups()->ids()),
		'name' => 'payment_name',
		'value' => $paymentName,
		'id' => 'payment_name',
		'attributes' => ''));
?>
<?php if ($showTitle) : ?>
<h4><?php echo Text::_('COM_REDSHOPB_SHOP_PAYMENT_METHOD', true); ?></h4>
<?php endif; ?>
<div>
	<?php echo RedshopbLayoutHelper::render('redpayment.list.radio', $paymentSettings); ?>
</div>
