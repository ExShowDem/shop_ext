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

/** @var array $displayData */

$showTitle            = $displayData['showTitle'];
$shippingRateTitle    = $displayData['shippingRateTitle'];
$stockroomPickupTitle = $displayData['stockroomPickupTitle'];
$stockroomPickupId    = $displayData['stockroomPickupId'];
$data                 = (object) $displayData;

$stockroomAddress = null;

if ($stockroomPickupTitle && !empty($stockroomPickupId))
{
	$stockroomAddress = RedshopbEntityStockroom::getInstance($stockroomPickupId)->getAddress();
}

if (is_array($data->shippingDate))
{
	$data->shippingDate = current($data->shippingDate);
}
?>
<div class="<?php echo isset($data->shippingDate) && $data->shippingDate ? 'span6 col-md-6' : 'span12 col-md-12' ?>">
	<?php if ($showTitle):?>
	<h4><?php echo Text::_('COM_REDSHOPB_SHOP_SHIPPING_METHOD', true); ?></h4>
	<?php endif;?>

	<p><?php echo $shippingRateTitle; ?></p>

	<?php
	PluginHelper::importPlugin('redshipping');

	Factory::getApplication()->triggerEvent('onAESECExtendedShippingInfo', array($data, &$extendedInfo));

	echo $extendedInfo;
	?>
	<div>
	<?php if ($stockroomPickupTitle): ?>
		<h4><?php echo Text::_('COM_REDSHOPB_STOCKROOM_PICK_UP_LABEL', true); ?></h4>
		<h5><?php echo $stockroomPickupTitle; ?></h5>

		<?php if (!empty($stockroomAddress)):?>
			<?php echo RedshopbLayoutHelper::render('addresses.shipping_address', $stockroomAddress);?>
		<?php endif;?>
	<?php endif; ?>
	</div>
</div>
<?php if (isset($data->shippingDate) && $data->shippingDate) : ?>
	<div class="<?php echo isset($data->shippingDate) && $data->shippingDate ? 'span6 col-md-6' : 'span12 col-md-12' ?>">
		<h4><?php echo Text::_('COM_REDSHOPB_ORDER_DELIVERY_DATE', true); ?></h4>
		<?php echo '<p id="shipping-date">' . HTMLHelper::_('date', $data->shippingDate, Text::_('DATE_FORMAT_LC4')) . '</p>' ?>
	</div>
<?php endif;
