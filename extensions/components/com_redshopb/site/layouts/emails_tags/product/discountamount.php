<?php
/**
 * @package     Aesir.E-Commerce.Email_tags
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$product = $displayData['product'];

echo RedshopbHelperPrices::displayDiscount(
	($product->discount->percent / 100) * $product->prices->fallback_price,
	RedshopbHelperPrices::DISCOUNT_TOTAL,
	$product->prices->currency
);
