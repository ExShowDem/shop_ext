<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// Unit of measure shows near by quantity, so hide it, when product available just as catalog
if (!$isShop || !$isOneProduct || !$extThis->placeOrderPermission)
{
	return;
}

$product = $displayData['product'];

echo $product->unit_measure_text;
