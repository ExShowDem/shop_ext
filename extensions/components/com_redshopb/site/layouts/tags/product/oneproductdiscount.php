<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if ($isShop && $isOneProduct && $extThis->placeOrderPermission
	&& isset($extThis->product->prices[$product->id]->discount)
	&& $extThis->product->prices[$product->id]->discount)
{
	echo RedshopbHelperPrices::displayDiscount(
		$extThis->product->prices[$product->id]->discount,
		$extThis->product->prices[$product->id]->discount_type,
		$extThis->product->prices[$product->id]->currency
	);
}
