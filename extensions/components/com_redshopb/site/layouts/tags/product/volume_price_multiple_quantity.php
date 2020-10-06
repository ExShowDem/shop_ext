<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if ($isShop)
{
	// Get all available prices of products
	$prices = RedshopbHelperPrices::getProductsPrice(
		array($product->id),
		$extThis->customerId,
		$extThis->customerType,
		$extThis->product->prices[$product->id]->currency,
		array(0),
		'',
		$extThis->companyId,
		null,
		false,
		false,
		false
	);

	// Remove prices are not "multiple of"
	if (!empty($prices))
	{
		foreach ($prices as $index => $price)
		{
			if (!$price->is_multiple)
			{
				unset($prices[$index]);
			}
		}
	}

	$prices = !empty($prices) ? reset($prices) : null;

	if (!empty($prices))
	{
		echo (int) $prices->quantity_min;
	}
}
