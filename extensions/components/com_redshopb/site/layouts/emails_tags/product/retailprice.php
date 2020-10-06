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

$hidePrice = false;
RFactory::getDispatcher()->trigger(
	'onBeforeRedshopbProcessTagPrice', array(&$product->prices->retail_price, &$hidePrice, 0, $product->id)
);

echo RedshopbHelperProduct::getProductFormattedPrice(
	(float) $product->prices->retail_price,
	$product->prices->retail_currency_id
);
