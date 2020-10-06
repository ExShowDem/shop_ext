<?php
/**
 * @package  AcceptanceTester
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since    2.5.0
 */
namespace Step\Integration;
use Step\Acceptance\redshopb2b;
use Page\Frontend\Redshopb2bPage as Redshopb2bPage;
use Page\Frontend\CheckoutPage;
class CheckoutWithTaxRate extends redshopb2b
{
	/**
	 * @param       $employeeWithLogin
	 * @param       $category
	 * @param       $currencySeparator
	 * @param       $currency
	 * @param array $products
	 * @param       $taxName
	 * @param       $taxRate
	 * @throws \Exception
	 */
	public function checkoutWithApplyTaxRate($user, $category, $currencySeparator, $currency, $products = array(), $taxName, $taxRate, $function)
	{
		$I = $this;
		$I->doFrontEndLogin($user['username'], $user['username']);
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElementVisible(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForText($category, 30, Redshopb2bPage::$categoryClass);
		try
		{
			$I->waitForElementVisible(['link' => $products['name']], 30);
		}catch (\Exception $e)
		{
			$I->click(['link' => $category]);
			$I->waitForText($category, 30, Redshopb2bPage::$categoryClass);
			$I->waitForElementVisible(['link' => $products['name']], 30);
		}
		$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 30);
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForElementVisible(Redshopb2bPage::$btnGoesToCheckout, 30);
		$I->click(Redshopb2bPage::$btnGoesToCheckout);
		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);
		switch ($function)
		{
			case 'baseOnProduct':
				$I->comment('Checkout with apply VAT/TAX rate for Product ');
				$text = $taxName . ' for product ' . $products['name'];
				$totalWithQuantity = (int) $products['price'];
				$taxRateTotal = (int) $totalWithQuantity * $taxRate;
				$totalPrice = (int) $totalWithQuantity + $taxRateTotal;
				$taxRateTotal = (string) $taxRateTotal . $currencySeparator . '00 ' . $currency;
				$totalWithQuantity = (string) $totalPrice . $currencySeparator . '00 ' . $currency;
				$taxNotice = new CheckoutPage();
				$I->waitForElementVisible($taxNotice->taxNotice($text), 30);
				$I->waitForText($taxRateTotal, 30);
				$I->waitForText($totalWithQuantity, 30);
				break;
			case 'baseOnCompany':
				$I->comment('Checkout with apply VAT/TAX rate for Company');
				$totalWithQuantity = (int) $products['price'];
				$taxRateTotal = (int) $totalWithQuantity * $taxRate;
				$totalPrice = (int) $totalWithQuantity + $taxRateTotal;
				$taxRateTotal = (string) $taxRateTotal . $currencySeparator . '00 ' . $currency;
				$totalWithQuantity = (string) $totalPrice . $currencySeparator . '00 ' . $currency;
				$taxNotice = new CheckoutPage();
				$I->waitForElementVisible($taxNotice->taxNotice($taxName), 30);
				$I->waitForText($taxRateTotal, 30);
				$I->waitForText($totalWithQuantity, 30);
				break;
			case 'baseOnCompany&Product':
				$I->comment('Checkout with apply VAT/TAX rate for both Company and Product');
				$text = $taxName . ' for product ' . $products['name'];
				$totalWithQuantity = (int) $products['price'];
				$taxRateBaseOnCompany = (int) $totalWithQuantity * $taxRate;
				$taxRateBaseOnProduct = (int) $totalWithQuantity * $taxRate;
				$totalPrice = (int) $totalWithQuantity + $taxRateBaseOnCompany + $taxRateBaseOnProduct;
				$taxRateBaseOnProduct = (string) $taxRateBaseOnProduct . $currencySeparator . '00 ' . $currency;
				$taxRateBaseOnCompany = (string) $taxRateBaseOnCompany . $currencySeparator . '00 ' . $currency;
				$totalWithQuantity = (string) $totalPrice . $currencySeparator . '00 ' . $currency;
				$taxNotice = new CheckoutPage();
				try
				{
					$I->waitForElementVisible($taxNotice->taxNotice($text), 30);
				}catch (\Exception $exception)
				{
					$I->reloadPage();
					$I->waitForElementVisible($taxNotice->taxNotice($text), 30);
				}
				$I->waitForElementVisible($taxNotice->taxNotice($taxName), 30);
				$I->waitForText($taxRateBaseOnProduct, 30);
				$I->waitForText($taxRateBaseOnCompany, 30);
				$I->waitForText($totalWithQuantity, 30);
		}
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForText(Redshopb2bPage::$deliveryInfoContent, 30, Redshopb2bPage::$deliveryInfo);
//		$I->waitForElement(Redshopb2bPage::$userBillingInfor, 30);
//		$I->click(Redshopb2bPage::$userBillingInfor);
//		$I->waitForElement(Redshopb2bPage::$emailBilling, 30);
//		$I->wait(1);
//		$I->fillField(Redshopb2bPage::$emailBilling, $user['email']);
//		$I->fillField(Redshopb2bPage::$nameBilling, $user['name']);
//		$I->fillField(Redshopb2bPage::$name2Billing, $user['name2']);
//		$I->fillField(Redshopb2bPage::$phoneBilling, $user['phone']);
//		$I->waitForElement(Redshopb2bPage::$updateButtonBilling, 30);
//		$I->wait(1);
//		$I->click(Redshopb2bPage::$updateButtonBilling);
//		$I->wait(1);
//		$I->reloadPage();
//		$I->waitForElement(Redshopb2bPage::$userBillingInfor, 30);
//		$I->click(Redshopb2bPage::$userBillingInfor);
//		$I->scrollTo(Redshopb2bPage::$emailBilling);
//		$I->waitForText($user['email'], 30);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
//		$I->waitForText($user['email'], 30);
		$I->waitForElementVisible(Redshopb2bPage::$completeOderButton, 30);
		try
		{
			$I->click(Redshopb2bPage::$completeOderButton);
			$I->waitForElementVisible(Redshopb2bPage::$messageSuccessID, 30);
		} catch (\Exception $exception)
		{
			$I->waitForElementVisible(Redshopb2bPage::$completeOderButton, 30);
			$I->click(Redshopb2bPage::$completeOderButton);
			$I->waitForElementVisible(Redshopb2bPage::$messageSuccessID, 30);
		}
//		$I->waitForText(Redshopb2bPage::$messageOrderSuccess, 30, Redshopb2bPage::$messageSuccessID);
	}
}