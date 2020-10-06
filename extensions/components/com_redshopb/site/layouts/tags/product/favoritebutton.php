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
use Joomla\CMS\Factory;

$product           = $extThis->product->items[0];
$app               = Factory::getApplication();
$productAttributes = RedshopbHelperProduct::getAttributesAsArray($product->id);

if ($app->getUserState('shop.customer_id', 0)
	&& $app->getUserState('shop.customer_type', '') == 'employee'
	&& empty($productAttributes)) :
	RHelperAsset::load('redshopbcart.js', 'com_redshopb');
	Text::script('COM_REDSHOPB_NOTHING_SELECTED');
	Text::script('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_ADDED_SUCESSFULY');
	Text::script('COM_REDSHOPB_MYFAVORITELIST_REMOVED_SUCCESSFULLY');
	Text::script('COM_REDSHOPB_MYFAVORITELIST_PRODUCT_SUCCESSFULLY_ADDED_TO');
	?>
	<button type="button" class="btn btn-info hasTooltip add-to-favoritelist<?php if ($product->inFavouriteList) : ?> added<?php
																			endif; ?>"
			name="addtofavorite_<?php echo $product->id; ?>" id="addtofavorite_<?php echo $product->id; ?>" title="<?php echo Text::_('COM_REDSHOPB_SHOP_ADD_TO_FAVORITE'); ?>">
		<i class="icon-star"></i>
	</button>
<?php
endif;
