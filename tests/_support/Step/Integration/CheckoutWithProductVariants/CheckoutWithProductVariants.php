<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir . E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Step\Integration\CheckoutWithProductVariants;
use Step\Acceptance\redshopb2b;
use Page\Integration\CheckoutWithProductVariants\CheckoutWithProductVariantsPage as CheckoutWithProductVariantsPage;

/**
 * Class CheckoutWithProductVariants
 * @package Step\Integration\CheckoutWithProductVariants
 * @since 2.8.0
 */
class CheckoutWithProductVariants extends redshopb2b
{
	/**
	 * @param $user
	 * @param $category
	 * @param $product
	 * @param $nameAttributeFirst
	 * @param $nameAttributeSecond
	 * @param $valueColor
	 * @param $valueSize
	 * @param $prices
	 * @param $currencySeparator
	 * @param $currency
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function CheckoutWithProductVariants($user, $category, $product, $nameAttributeFirst, $nameAttributeSecond, $valueColor, $valueSize, $prices, $currencySeparator, $currency, $quantity)
	{
		$I = $this;
		$I->doFrontEndLogin($user, $user);
		$I->amOnPage(CheckoutWithProductVariantsPage::$URLShop);
		$I->waitForElementVisible(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForText($category, 30, CheckoutWithProductVariantsPage::$categoryClass);
		$I->waitForElementVisible(['link' => $product], 30);
		$I->waitForElementVisible(CheckoutWithProductVariantsPage::$btnInfoAddToCart, 30);
		$I->comment('Try to select attribute of product');
		$I->click(CheckoutWithProductVariantsPage::$btnInfoAddToCart);
		$I->selectOptionInChosenjs($nameAttributeFirst, $valueColor);
		$I->selectOptionInChosenjs($nameAttributeSecond, $valueSize);
		try
		{
			$I->waitForElementVisible(CheckoutWithProductVariantsPage::$attributeQuantity, 60);
			$I->wait(1);
			$I->fillField(CheckoutWithProductVariantsPage::$attributeQuantity, $quantity);
		} catch (\Exception $e)
		{
			$I->selectOptionInChosenjs($nameAttributeFirst, $valueColor);
			$I->selectOptionInChosenjs($nameAttributeSecond, $valueSize);
			$I->waitForElementVisible(CheckoutWithProductVariantsPage::$attributeQuantity, 60);
			$I->wait(1);
			$I->fillField(CheckoutWithProductVariantsPage::$attributeQuantity, $quantity);
		}
		$I->scrollTo(CheckoutWithProductVariantsPage::$addToCartVariants);
		$I->waitForElementVisible(CheckoutWithProductVariantsPage::$addToCartVariants, 60);
		$I->click(CheckoutWithProductVariantsPage::$addToCartVariants);
		try
		{
			$I->waitForElementVisible(CheckoutWithProductVariantsPage::$addToCartModal, 60);
			$I->waitForText(CheckoutWithProductVariantsPage::$messageAddToCartSuccess, 30);
		} catch (\Exception $e)
		{
			$I->scrollTo(CheckoutWithProductVariantsPage::$buttonAddToCart);
			$I->waitForElementVisible(CheckoutWithProductVariantsPage::$buttonAddToCart, 30);
			$I->click(CheckoutWithProductVariantsPage::$buttonAddToCart);
			$I->waitForElementVisible(CheckoutWithProductVariantsPage::$addToCartModal, 60);
			$I->waitForText(CheckoutWithProductVariantsPage::$messageAddToCartSuccess, 30);
		}
		$I->waitForElementVisible(CheckoutWithProductVariantsPage::$btnGoesToCheckout, 30);
		$I->click(CheckoutWithProductVariantsPage::$btnGoesToCheckout);
		$I->waitForElementVisible(CheckoutWithProductVariantsPage::$linkCartFirst, 30);

		$I->comment('Try to check prices and attributes');
		$I->waitForElementVisible(CheckoutWithProductVariantsPage::$orderItemRow, 30);
		$I->click(CheckoutWithProductVariantsPage::$orderItemRow);
		$I->waitForText($nameAttributeFirst, 30);
		$I->waitForText($valueColor, 30);
		$I->waitForText($nameAttributeSecond, 30);
		$I->waitForText($valueSize, 30);
		$totalPrices = $prices * $quantity;
		$I->waitForText((string) $totalPrices . $currencySeparator . '00 ' . $currency, 30, CheckoutWithProductVariantsPage::$priceTotalFinal);
		$I->waitForElementVisible(CheckoutWithProductVariantsPage::$nextButton, 30);
		$I->click(CheckoutWithProductVariantsPage::$nextButton);
		$I->waitForText(CheckoutWithProductVariantsPage::$deliveryInfoContent, 30, CheckoutWithProductVariantsPage::$deliveryInfo);
		$I->waitForElementVisible(CheckoutWithProductVariantsPage::$nextButton, 30);
		$I->click(CheckoutWithProductVariantsPage::$nextButton);
		$I->waitForElementVisible(CheckoutWithProductVariantsPage::$completeOderButton, 30);
		$I->click(CheckoutWithProductVariantsPage::$completeOderButton);
		$I->waitForElementVisible(CheckoutWithProductVariantsPage::$messageSuccessID, 30);
		$I->waitForText(CheckoutWithProductVariantsPage::$messageOrderSuccess, 30, CheckoutWithProductVariantsPage::$messageSuccessID);
	}
}