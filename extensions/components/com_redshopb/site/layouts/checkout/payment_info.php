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

$showTitle    = $displayData['showTitle'];
$paymentTitle = $displayData['paymentTitle'];
$paymentName  = $displayData['paymentName'];

if (!empty($displayData['orderId']))
{
	$paymentExtraFields = RedshopbHelperOrder::getPaymentExtraInformation($displayData['orderId']);
}
elseif (!empty($paymentName))
{
	$paymentExtraFields = RedshopbHelperOrder::getPaymentExtraInformationFromPaymentName($paymentName);
}
?>
<?php if ($showTitle):?>
<h4><?php echo Text::_('COM_REDSHOPB_SHOP_PAYMENT_METHOD', true); ?></h4>
<?php endif;?>

<?php if (!empty($paymentTitle)) : ?>
	<p><?php echo $paymentTitle; ?></p>
<?php endif; ?>
<div>
<?php foreach ($paymentExtraFields as $paymentExtraField):?>
	<p>
		<strong>
			<?php echo $paymentExtraField->title; ?>:
		</strong>&nbsp;
		<?php echo $paymentExtraField->value; ?>
	</p>
<?php endforeach;?>
</div>
