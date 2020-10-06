<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Extracted variables
 *
 * @var $extThis
 * @var $product
 * @var $displayData
 */

if (isset($extThis->product->complimentaryProducts[$product->id]))
{
	echo RedshopbHelperProduct::renderComplimentaryProducts(
		$extThis->product->complimentaryProducts[$product->id], $product->id, (isset($cartPrefix) ? '_' . $cartPrefix : null),
		isset($accessoryLayout) ? $accessoryLayout : '', $displayData
	);
}
