<?php
/**
 * @package     Aesir.E-Commerce.Tag
 * @subpackage  Layouts
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

if (!$isShop || !$isOneProduct && !array_key_exists($product->id, $extThis->product->prices))
{
	return;
}

$price     = $extThis->product->prices[$product->id]->price;
$hidePrice = false;
RFactory::getDispatcher()->trigger('onBeforeRedshopbProcessTagPrice', array(&$price, &$hidePrice, 0, $product->id));

if ($hidePrice)
{
	return;
}

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
