<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir . E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Step\Integration\ReturnOrder;
use Step\Acceptance\redshopb2b;
use Page\Integration\ReturnOrder\ReturnOrderEmployeeLoginPage as ReturnOrderEmployeeLoginPage;
use Page\Frontend\Redshopb2bPage as Redshopb2bPage;

/**
 * Class ReturnOrderEmployeeLogin
 *
 * @package Step\Integration
 * @since 2.8.0
 */
class ReturnOrderEmployeeLogin extends redshopb2b
{
	/**
	 * @param       $user
	 * @param array $products
	 * @param       $order
	 * @param       $comment
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function returnOrderEmployeeLogin($user, $category, $products = array(), $currencySeparator, $currency, $comment)
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
		try
		{
			$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 30);
		}catch (\Exception $e)
		{
			$I->reloadPage();
			$I->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 30);
			$I->click(Redshopb2bPage::$buttonAddToCart);
			$I->waitForElementVisible(Redshopb2bPage::$addToCartModal, 30);
		}
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForText($products['name'], 30);
		$I->click(Redshopb2bPage::$btnGoesToCheckout);
		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);
		$totalWithQuantity = (int) $products['price'];
		$totalWithQuantity = (string) $totalWithQuantity . $currencySeparator . '00 ' . $currency;
		$I->waitForText($totalWithQuantity, 30, Redshopb2bPage::$priceTotalFinal);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->wait(0.5);
		$I->waitForText(Redshopb2bPage::$deliveryInfoContent, 30, Redshopb2bPage::$deliveryInfo);
		$I->waitForElementVisible(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForElementVisible(Redshopb2bPage::$completeOderButton, 30);
		$I->click(Redshopb2bPage::$completeOderButton);
		$I->waitForElementVisible(Redshopb2bPage::$messageSuccessID, 30);
		$I->waitForText(Redshopb2bPage::$messageOrderSuccess, 30, Redshopb2bPage::$messageSuccessID);
		$oderID = $I->grabTextFrom(ReturnOrderEmployeeLoginPage::$orderXpath);
		$nameOrder = substr($oderID, 0, 8);
		$I->amOnPage(ReturnOrderEmployeeLoginPage::$returnOrderUrl);
		$I->comment('I try to return order');
		$I->selectOptionInChosenXpath(ReturnOrderEmployeeLoginPage::$selectOrderJform, $nameOrder);
		$I->selectOptionInChosenXpath(ReturnOrderEmployeeLoginPage::$productJform, $products['name']);
		$I->waitForElementVisible(ReturnOrderEmployeeLoginPage::$quantityProducts, 30);
		$I->fillField(ReturnOrderEmployeeLoginPage::$quantityProducts, $products['quantity']);
		$I->waitForElementVisible(ReturnOrderEmployeeLoginPage::$comment, 30);
		$I->fillField(ReturnOrderEmployeeLoginPage::$comment, $comment);
		$I->waitForElementVisible(ReturnOrderEmployeeLoginPage::$addIcon, 30);
		$I->click(ReturnOrderEmployeeLoginPage::$addIcon);
		$I->waitForText(ReturnOrderEmployeeLoginPage::$saveSuccess, 30, ReturnOrderEmployeeLoginPage::$systemContainer);
		$I->doFrontendLogout();
	}

	/**
	 * @param $user
	 * @param $product
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function checkReturnOrderByAdmin($user, $product)
	{
		$I = $this;
		$I->doFrontEndLogin();
		$I->amOnPage(ReturnOrderEmployeeLoginPage::$returnOrderAdminUrl);
		$I->comment('Supper try to check return order');
		$I->waitForElementVisible(ReturnOrderEmployeeLoginPage::$search, 30);
		$I->fillField(ReturnOrderEmployeeLoginPage::$search, $product);
		$I->waitForElementVisible(ReturnOrderEmployeeLoginPage::$iconSearch, 30);
		$I->click(ReturnOrderEmployeeLoginPage::$iconSearch);
		$I->waitForText($user, 30);
		$I->waitForText($product, 30);
	}
}