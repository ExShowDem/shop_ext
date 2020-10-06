<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if ($isShop && $price && array_key_exists($productId, $products->prices) && $products->prices[$productId]->tax_rate)
{
	$taxRates = RedshopbHelperTax::getTaxRates(0, '', $productId);

	if (!empty($taxRates))
	{
		$taxNames = array();
		$percent  = 0;

		foreach ($taxRates as $taxRate)
		{
			$taxNames[] = $taxRate->name;
			$percent   += $taxRate->tax_rate;
		}

		echo '<div class="taxRatesBlock">' . implode(', ', $taxNames) . ' +' . $percent . '%</div>';
	}
}
