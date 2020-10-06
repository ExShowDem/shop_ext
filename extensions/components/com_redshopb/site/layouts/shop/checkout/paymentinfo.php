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

$data = (object) $displayData;

?>
<div class="well">
	<h4><?php echo Text::_('COM_REDSHOPB_SHOP_PAYMENT_METHOD', true); ?></h4>

	<?php if (!empty($data->paymentTitle)) : ?>
		<p><?php echo $data->paymentTitle; ?></p>
	<?php endif; ?>
	<?php

	if (isset($data->orderId))
	{
		$paymentExtraFields = RedshopbHelperOrder::getPaymentExtraInformation($data->orderId);
	}
	elseif (isset($data->paymentName))
	{
		$paymentExtraFields = RedshopbHelperOrder::getPaymentExtraInformationFromPaymentName($data->paymentName);
	}

	if (!empty($paymentExtraFields))
	{
		foreach ($paymentExtraFields as $paymentExtraField)
		{
			?>
			<p>
				<strong><?php echo $paymentExtraField->title; ?>: </strong>
				<?php echo $paymentExtraField->value; ?>
			</p>
			<?php
		}
	}

	?>
</div>


