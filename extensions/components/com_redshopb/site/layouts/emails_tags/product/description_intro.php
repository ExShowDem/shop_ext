<?php
/**
 * @package     Aesir.E-Commerce.Email_tags
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

$product = $displayData['product'];

if (!empty($product->description->description_intro))
{
	echo $product->description->description_intro;
}
