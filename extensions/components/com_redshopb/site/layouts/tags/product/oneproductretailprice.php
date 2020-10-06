<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if ($isShop && $isOneProduct
	&& RedshopbHelperCompany::checkStatusDisplayRetailPrice($extThis->customerId, $extThis->customerType)
	&& isset($extThis->product->prices[$product->id]->retail_price)
	&& $extThis->product->prices[$product->id]->retail_price)
{
	$hidePrice = false;
	RFactory::getDispatcher()->trigger(
		'onBeforeRedshopbProcessTagPrice', array(&$extThis->product->prices[$product->id]->retail_price, &$hidePrice, 0, $product->id)
	);

	if ($hidePrice)
	{
		return;
	}

	echo RedshopbHelperProduct::getProductFormattedPrice(
		(float) $extThis->product->prices[$product->id]->retail_price,
		$extThis->product->prices[$product->id]->retail_currency_id
	);
}
