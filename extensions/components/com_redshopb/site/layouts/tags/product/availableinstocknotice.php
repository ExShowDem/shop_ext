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

$stockrooms = RedshopbHelperStockroom::getProductsStockroomData($product->id);

if ($isShop && $extThis->placeOrderPermission)
{
	$showStockAs = $extThis->product->showStockAs;

	if ($showStockAs == 'hide')
	{
		return null;
	}

	if (!RedshopbHelperStockroom::productHasInStock($product->id) || empty($stockrooms))
	{
		echo '<div class="productNoStockFlag">' . Text::_('COM_REDSHOPB_PRODUCT_NO_STOCK') . '</div>';

		return null;
	}

	echo '<div class="productInStockFlag">' . Text::_('COM_REDSHOPB_PRODUCT_ON_STOCK') . '</div>';
}
