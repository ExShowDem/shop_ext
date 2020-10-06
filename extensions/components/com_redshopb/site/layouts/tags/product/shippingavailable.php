<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$product = $displayData['product'];

if (RedshopbShippingHelper::checkSelfPickupAvailableForProduct($product->id))
{
	?>
<div class="shippingAvailableForProduct"><i class="icon-map-marker"></i> <?php echo Text::_('COM_REDSHOPB_SHOP_SHIPPING_AVAILABLE_FOR_PRODUCT'); ?></div>
<?php
}
