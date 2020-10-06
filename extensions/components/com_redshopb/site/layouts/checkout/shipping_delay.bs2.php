<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Views
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ===============================
 *
 * @var  array  $displayData             Layout data
 * @var  array  $customerOrders          Customer orders data
 * @var  array  $shippingMethodSettings  Order count
 */

extract($displayData);

?>
<div class="row-fluid">
	<div class="span12 well">
		<div class="row-fluid">
			<div class="span12">
				<?php
				$shippingMethodSettings['showTitle']  = false;
				$shippingMethodSettings['delayOrder'] = true;
				echo RedshopbLayoutHelper::render('checkout.shipping_form', $shippingMethodSettings); ?>
			</div>
		</div>
		<?php if ($config->getInt('use_shipping_date', 0)): ?>
			<div class="row-fluid">
				<div class="span12">
					<?php foreach ($customerOrders as $customerOrder):
						$shippingDateSettings['customerOrder'] = $customerOrder;
						$shippingDateSettings['delayOrder']    = true;
						echo RedshopbLayoutHelper::render('checkout.shipping_date', $shippingDateSettings); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
