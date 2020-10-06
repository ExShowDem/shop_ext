<?php
/**
 * @package     Aesir.E-Commerce.Admin
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

$data = (object) $displayData;

?>

<div class="well">
	<div class="<?php echo isset($data->shippingDate) && $data->shippingDate ? 'col-md-6' : 'col-md-12' ?>">
		<h4><?php echo Text::_('COM_REDSHOPB_SHOP_SHIPPING_METHOD', true); ?></h4>
		<p><?php echo $data->shippingRateTitle; ?></p>
		<?php
		PluginHelper::importPlugin('redshipping');

		Factory::getApplication()->triggerEvent('onAESECExtendedShippingInfo', array($data->order, &$extendedInfo));

		echo $extendedInfo;
		?>

		<?php if ($data->stockroomPickupTitle): ?>
			<h4><?php echo Text::_('COM_REDSHOPB_STOCKROOM_PICK_UP_LABEL', true); ?></h4>
			<h5><?php echo $data->stockroomPickupTitle; ?></h5>
			<?php

			if (isset($data->stockroomPickupId) && $data->stockroomPickupId)
			{
				$result = RedshopbEntityStockroom::getInstance($data->stockroomPickupId)
					->getAddress();

				if ($result)
				{
					echo RedshopbLayoutHelper::render('shop.checkout.address', array('address' => $result));
				}
			}
			?>
		<?php endif; ?>
	</div>
	<?php if (isset($data->shippingDate) && $data->shippingDate) : ?>
		<div class="<?php echo isset($data->shippingDate) && $data->shippingDate ? 'col-md-6' : 'col-md-12' ?>">
			<h4><?php echo Text::_('COM_REDSHOPB_ORDER_DELIVERY_DATE', true); ?></h4>
			<?php echo '<p id="shipping-date">' . HTMLHelper::_('date', $data->shippingDate, Text::_('DATE_FORMAT_LC4')) . '</p>' ?>
		</div>
	<?php endif; ?>
</div>
