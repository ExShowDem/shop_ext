<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;
?>
<div class="redshopb-shop-pay">
	<?php
	echo RApiPaymentHelper::displayPayment(
		$this->paymentData['payment_name'],
		$this->paymentData['extension_name'],
		$this->paymentData['owner_name'],
		$this->paymentData
	);
	?>
</div>
