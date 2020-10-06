<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if ($isShop && array_key_exists($productId, $products->accessories)) :
	echo RedshopbHelperProduct::renderAccessoriesDropdown($products->accessories[$productId], $productId, $cartPrefix);
endif;
