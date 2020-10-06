<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Step onePageCheckoutWithProductVariants
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir . E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Step\Integration\OnePageCheckout\ProductVariants;
use Page\Frontend\CheckoutPage as CheckoutPage;
use Step\Integration\SaveCartWithProductVariant as SaveCartWithProductVariantSteps;
use Step\Integration\OnePageCheckout\DefaultSteps\onePageCheckout as onePageCheckoutSteps;
use Page\Integration\CheckoutWithProductVariants\CheckoutWithProductVariantsPage as CheckoutWithProductVariantsPage;

/**
 * Class onePageCheckoutWithProductVariants
 *
 * @package Step\Integration\OnePageCheckout\ProductVariants
 * @since 2.8.0
 */
class onePageCheckoutWithProductVariants extends onePageCheckoutSteps
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
	 * @param $quantity
	 * @param $cart
	 * @param $vatSetting
	 * @param $delivery
	 * @param $register
	 * @param $vendor
	 * @param $scenario
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function onePageCheckoutWithProductVariants($user, $category, $product, $nameAttributeFirst, $nameAttributeSecond, $valueColor, $valueSize, $prices, $quantity, $cart, $vatSetting, $delivery, $register, $vendor, $wallet, $scenario)
	{
		$client = $this;
		$client->addToCarProductVariants($category, $product, $nameAttributeFirst, $nameAttributeSecond, $valueColor, $valueSize, $prices, $quantity, $vatSetting);
		$client->comment('The kinds of checkout');

		switch ($register)
		{
			case 'log_in':
				$client->comment('Login with account');
				$client->loginAccount($user);
				break;

			case 'sign_up':
				$client->comment('Create new account');
				$client->signUpNewAccount($user, $delivery);
				$client = new SaveCartWithProductVariantSteps($scenario);
				$client->saveCartAfterAddToCart($cart);
				$client->doFrontendLogout();

				$client->doFrontEndLogin();
				$client->comment("Administrator login and add credit for this user");
				$client->addCreditToEmployeeWithLogin($user['name'], $vatSetting['defaultCurrency'], $wallet);
				$client->doFrontendLogout();

				$client->doFrontEndLogin($user['name'], $user['name']);
				$client->comment('User login and checkout from save cart');
				$client->amOnPage(CheckoutPage::$URLCart);
				$client->waitForElement(CheckoutPage::$cartTable, 30);
				$client->searchForItemInFrontend($cart, ['search field locator id' => CheckoutPage::$searchCart]);
				$client->waitForElementVisible(CheckoutPage::$btnCheckoutCart, 30);
				$client->click(CheckoutPage::$btnCheckoutCart);
				break;

			case 'guest':
				$client->comment('Guest');
				$client->guest($user);
				break;

			default:
				break;
		}

		if (isset($vendor['requisition']))
		{
			$client->waitForElement(CheckoutPage::$requisition, 30);
			$client->scrollTo(CheckoutPage::$requisition);
			$client->fillField(CheckoutPage::$requisition, $vendor['requisition']);

			if (isset($vendor['invoice_email']))
			{
				$client->waitForElement(CheckoutPage::$comment, 30);
				$client->fillField(CheckoutPage::$comment, $vendor['invoice_email']);
			}
		}

		$client->amGoingTo('Complete Order');
		$client->click(CheckoutWithProductVariantsPage::$completeOderButton);
		$client->waitForElementVisible(CheckoutWithProductVariantsPage::$messageSuccessID, 30);
		$client->waitForText(CheckoutWithProductVariantsPage::$messageOrderSuccess, 30, CheckoutWithProductVariantsPage::$messageSuccessID);
	}
}