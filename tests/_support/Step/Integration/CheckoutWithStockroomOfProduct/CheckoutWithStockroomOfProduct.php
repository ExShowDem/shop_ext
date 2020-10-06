<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Step\Integration\CheckoutWithStockroomOfProduct;
use Step\Acceptance\redshopb2b;
use Page\Frontend\ProductPage as ProductPage;

/**
 * Class CheckoutWithStockroomOfProduct
 *
 * @package Step\Integration\CheckoutWithStockroomOfProduct
 * @since 2.8.0
 */
class CheckoutWithStockroomOfProduct extends redshopb2b
{
	/**
	 * @param       $user
	 * @param       $category
	 * @param array $products
	 * @param       $currencySeparator
	 * @param       $currency
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function checkoutWithStockroomOfProduct($user, $category, $products = array(), $currencySeparator, $currency)
	{
		$I = $this;
		$I->doFrontEndLogin($user['username'], $user['username']);
		$I->amOnPage(ProductPage::$URLShop);
		$I->waitForElement(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForText($category, 30, ProductPage::$categoryClass);
		try
		{
			$I->waitForElement(['link' => $products['name']], 30);
		}catch (\Exception $e)
		{
			$I->click(['link' => $category]);
			$I->waitForText($category, 30, ProductPage::$categoryClass);
			$I->waitForElement(['link' => $products['name']], 30);
		}
		$I->waitForElementVisible(ProductPage::$quantityProductStock, 60);
		$I->fillField(ProductPage::$quantityProductStock, $products['quantity']);
		$I->waitForElementVisible(ProductPage::$buttonAddToCart, 30);
		$I->click(ProductPage::$buttonAddToCart);
		$I->waitForElementVisible(ProductPage::$addToCartModal, 30);
		$I->waitForText(ProductPage::$messageAddToCartSuccess, 30);
		$I->waitForText($products['name'], 30);
		$I->waitForElementVisible(ProductPage::$btnGoesToCheckout, 30);
		$I->click(ProductPage::$btnGoesToCheckout);
		$I->waitForElementVisible(ProductPage::$linkCartFirst, 30);
		$totalWithQuantity = (int) $products['price'] * $products['quantity'];
		$totalPrices = (string) $totalWithQuantity . $currencySeparator . '00 ' . $currency;
		$I->waitForText($totalPrices, 30, ProductPage::$priceTotalFinal);
		$I->waitForElementVisible(ProductPage::$nextButton, 30);
		$I->click(ProductPage::$nextButton);
		$I->waitForText(ProductPage::$deliveryInfoContent, 30, ProductPage::$deliveryInfo);
		$I->waitForElementVisible(ProductPage::$nextButton, 30);
		$I->click(ProductPage::$nextButton);
		$I->waitForElementVisible(ProductPage::$completeOderButton, 30);
		$I->click(ProductPage::$completeOderButton);
		$I->waitForElementVisible(ProductPage::$messageSuccessID, 30);
		$I->waitForText(ProductPage::$messageOrderSuccess, 30, ProductPage::$messageSuccessID);
		$I->doFrontendLogout();
	}

	/**
	 * @param       $user
	 * @param       $category
	 * @param array $products
	 * @param       $currencySeparator
	 * @param       $currency
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function productIsOutOfStock($user, $category, $products = array())
	{
		$I = $this;
		$I->doFrontEndLogin($user['username'], $user['username']);
		$I->amOnPage(ProductPage::$URLShop);
		$I->waitForElementVisible(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForText($category, 30, ProductPage::$categoryClass);
		try
		{
			$I->waitForElementVisible(['link' => $products['name']], 30);
		}catch (\Exception $e)
		{
			$I->click(['link' => $category]);
			$I->waitForText($category, 30, ProductPage::$categoryClass);
			$I->waitForElementVisible(['link' => $products['name']], 30);
		}
		
		$I->comment('Check add to cart product is out of stock on category page');
		$I->waitForElementVisible(ProductPage::$quantityProductStock, 60);
		$I->fillField(ProductPage::$quantityProductStock, $products['outOfStock']);
		$I->waitForElementVisible(ProductPage::$buttonAddToCart, 30);
		$I->click(ProductPage::$buttonAddToCart);
		$I->waitForElementVisible(ProductPage::$addToCartModal, 30);
		$I->waitForText(ProductPage::$messageStockNotEnough, 30);
		$I->waitForElementVisible(ProductPage::$shopMore, 30);
		$I->click(ProductPage::$shopMore);

		$I->comment('Check add to cart product is out of stock on product detail page');
		$I->waitForElementVisible(['link' => $products['name']], 30);
		$I->click(['link' => $products['name']]);
		$I->waitForText($products['name'], 30, ProductPage::$productNameH1);
		$I->waitForElementVisible(ProductPage::$quantityProductStock, 60);
		$I->fillField(ProductPage::$quantityProductStock, $products['outOfStock']);
		$I->waitForElementVisible(ProductPage::$buttonAddToCart, 30);
		$I->click(ProductPage::$buttonAddToCart);
		$I->waitForElementVisible(ProductPage::$addToCartModal, 30);
		$I->waitForText(ProductPage::$messageStockNotEnough, 30);
		$I->waitForElementVisible(ProductPage::$shopMore, 30);
		$I->click(ProductPage::$shopMore);
		$I->doFrontendLogout();
	}
}