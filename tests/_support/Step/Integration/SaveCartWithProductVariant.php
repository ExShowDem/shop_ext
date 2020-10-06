<?php
/**
 * @package  AcceptanceTester
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since    2.5.1
 */
namespace Step\Integration;
use Step\Frontend\CheckoutSteps as CheckoutSteps;
use Page\Frontend\Redshopb2bPage as Redshopb2bPage;

class SaveCartWithProductVariant extends CheckoutSteps
{
	/**
	 * @param $cart
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function saveCartAfterAddToCart($cart)
	{
		$I = $this;
		$I->waitForElementVisible(Redshopb2bPage::$buttonSaveCart, 30);
		$I->click(Redshopb2bPage::$buttonSaveCart);

		$I->comment('I wait for Save Cart modal to approved');
		$I->waitForText(Redshopb2bPage::$labelSaveCartLabel, 30, Redshopb2bPage::$labelSaveCartXpath);
		$I->waitForElementVisible(Redshopb2bPage::$nameCartField, 60);
		$I->wait(0.5);
		$I->fillField(Redshopb2bPage::$nameCartField, $cart);
		$I->waitForElementVisible(Redshopb2bPage::$saveCloseButton, 30);
		$I->click(Redshopb2bPage::$saveCloseButton);

		$I->comment('I wait for Save Cart modal to close');
		$I->waitForText(Redshopb2bPage::$messageSaveCartSuccess, 10, Redshopb2bPage::$systemContainer);
		try
		{
			$I->waitForText(Redshopb2bPage::$messageSaveCartSuccess, 10, Redshopb2bPage::$systemContainer);
		}catch (\Exception $exception)
		{
			$I->waitForElementVisible(Redshopb2bPage::$nameCartField, 30);
			$I->fillField(Redshopb2bPage::$nameCartField, $cart);
			$I->waitForElementVisible(Redshopb2bPage::$saveCloseButton, 30);
			$I->click(Redshopb2bPage::$saveCloseButton);
			$I->comment('I wait for Save Cart modal to close again');
			$I->waitForText(Redshopb2bPage::$messageSaveCartSuccess, 10, Redshopb2bPage::$systemContainer);
		}
	}

	/**
	 * @param $category
	 * @param $product
	 * @param $cart
	 * @param $nameAttributeFirst
	 * @param $nameAttributeSecond
	 * @param $valueColor
	 * @param $valueSize
	 * @param $prices
	 * @param $quantity
	 * @param $vatSetting
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function saveCartWithProductVariant($category, $product, $cart, $nameAttributeFirst, $nameAttributeSecond, $valueColor, $valueSize, $prices, $quantity, $vatSetting)
	{
		$I = $this;
		$I->addToCarProductVariants($category, $product, $nameAttributeFirst, $nameAttributeSecond, $valueColor, $valueSize, $prices, $quantity, $vatSetting);
		$I->saveCartAfterAddToCart($cart);
		$I->amOnPage(Redshopb2bPage::$URLCart);
		$I->waitForElementVisible(Redshopb2bPage::$cartTable, 30);
		$I->searchForItemInFrontend($cart, ['search field locator id' => Redshopb2bPage::$searchCart]);
		$I->waitForText($cart, 30, Redshopb2bPage::$cartTable);
		$I->waitForElementVisible(['link' => $cart], 30);
		$I->click(['link' => $cart]);
		$I->waitForText($product, 30);
		$I->waitForText($nameAttributeFirst . ": " . $valueColor, 30);
		$I->waitForText($nameAttributeSecond . ": " . $valueSize, 30);
		$totalPrices = $prices * $quantity;
		$I->waitForText((string) $totalPrices . $vatSetting['currencySeparator'].'00 '.$vatSetting['currencySymbol'], 30);
	}
}