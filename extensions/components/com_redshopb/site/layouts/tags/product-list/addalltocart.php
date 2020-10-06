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

if (!$isShop || !$extThis->placeOrderPermission)
{
	return;
}

$classes    = array('btn', 'btn-info', 'btn-small', 'add-all-to-cart');
$collection = (!isset($extThis->product->collectionId) || empty($extThis->product->collectionId)) ? 0 : (int) $extThis->product->collectionId;
$buttonName = 'addalltocart_' . $collection;
?>
<button
	type="button"
	name="<?php echo $buttonName;?>"
	class="<?php echo implode(' ', $classes);?>"
	onclick="redSHOPB.shop.addAllToCart(event);"
>
	<i class="icon-shopping-cart"></i>
	<?php echo Text::_('COM_REDSHOPB_SHOP_ADD_ALL_TO_CART'); ?>
</button>
