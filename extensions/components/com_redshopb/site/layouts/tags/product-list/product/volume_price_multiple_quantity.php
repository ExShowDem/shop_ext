<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

if ($isShop && $price)
{
	$app          = Factory::getApplication();
	$customerId   = $app->getUserState('shop.customer_id', 0);
	$customerType = $app->getUserState('shop.customer_type', '');

	// Get all available prices of products
	$prices = RedshopbHelperPrices::getProductsPrice(
		array($productId),
		$customerId,
		$customerType,
		$productPrice->currency,
		array(0),
		'',
		RedshopbHelperCompany::getCompanyIdByCustomer($customerId, $customerType),
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
