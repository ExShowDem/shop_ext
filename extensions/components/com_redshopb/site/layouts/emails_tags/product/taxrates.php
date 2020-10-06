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

$taxRates = RedshopbHelperTax::getTaxRates(0, '', $product->id);

if (!empty($taxRates))
{
	echo '<div class="taxRatesBlock">';

	foreach ($taxRates as $taxRate)
	{
		echo '<div class="oneTaxRateInProduct">' . $taxRate->name . ' +' . $taxRate->tax_rate * 100 . '%</div>';
	}

	echo '</div>';
}
