<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Step onePageCheckout
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir . E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Step\Integration\OnePageCheckout\DefaultSteps;
use Page\Frontend\CheckoutPage as CheckoutPage;
use Step\Frontend\CheckoutSteps as CheckoutSteps;

/**
 * Class onePageCheckout
 *
 * @package Step\Integration\OnePageCheckout\DefaultSteps
 * @since 2.8.0
 */
class onePageCheckout extends CheckoutSteps
{
	/**
	 * @param $user
	 * @param $delivery
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function signUpNewAccount($user, $delivery)
	{
		$client = $this;
		$client->waitForText(CheckoutPage::$signupform, 30);
		$client->click(CheckoutPage::$signupform);
		$client->waitForElementVisible(CheckoutPage::$userName, 30);
		$client->fillField(CheckoutPage::$firstName, $user['name']);
		$client->fillField(CheckoutPage::$emailUser, $user['email']);
		$client->fillField(CheckoutPage::$userName, $user['name']);
		$client->fillField(CheckoutPage::$passWord, $user['name']);
		$client->fillField(CheckoutPage::$passWordConfirm, $user['name']);
		$client->fillField(CheckoutPage::$billingAddressFirst, $user['address']);
		$client->fillField(CheckoutPage::$billingAddressSecond, $user['address']);
		$client->fillField(CheckoutPage::$billingZip, $user['zip']);
		$client->fillField(CheckoutPage::$billingPhone, $user['phone']);
		$client->fillField(CheckoutPage::$billingCity, $user['city']);
		$client->selectOptionInChosenById(CheckoutPage::$billingCountry, $user['country']);
		$client->waitForText(CheckoutPage::$userBillingForShipping, 30);

		if ($user['address'] != $delivery['address'])
		{
			$client->waitForElementVisible(CheckoutPage::$shippingAddressCheckbox, 30);
			$client->click(CheckoutPage::$shippingAddressCheckbox);
			$client->waitForElementVisible(CheckoutPage::$shippingAddress1, 30);
			$client->fillField(CheckoutPage::$shippingName1, $delivery['name']);
			$client->fillField(CheckoutPage::$shippingName2, $delivery['name']);
			$client->fillField(CheckoutPage::$shippingAddress1, $delivery['address']);
			$client->fillField(CheckoutPage::$shippingAddress2, $delivery['address']);
			$client->fillField(CheckoutPage::$shippingZipCode, $delivery['zip']);
			$client->fillField(CheckoutPage::$shippingPhone, $delivery['phone']);
			$client->selectOptionInChosenById(CheckoutPage::$shippingCountry, $delivery['country']);
		}

		$client->waitForText(CheckoutPage::$registerButon, 30);
		$client->click(CheckoutPage::$registerButon);
		$client->waitForElement(CheckoutPage::$alertMessage, 30);
		$client->waitForText(CheckoutPage::$createUserSuccessMessage, 30);
		$client->waitForText(CheckoutPage::$changeDeliveryAddress, 30);
		$client->waitForText(CheckoutPage::$deliveryInfo, 30);
	}

	/**
	 * @param $user
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function loginAccount($user)
	{
		$client = $this;
		$client->comment('Login with account');
		$client->waitForText(CheckoutPage::$loginForm, 30);
		$client->click(CheckoutPage::$loginForm);
		$client->waitForElementVisible(CheckoutPage::$usernameId, 30);
		$client->fillField(CheckoutPage::$usernameId, $user['name']);
		$client->waitForElementVisible(CheckoutPage::$passwordId, 30);
		$client->fillField(CheckoutPage::$passwordId, $user['name']);
		$client->waitForElementVisible(CheckoutPage::$loginButtonCheckout, 30);
		$client->click(CheckoutPage::$loginButtonCheckout);
	}

	/**
	 * @param $user
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function guest($user)
	{
		$client = $this;
		$client->comment('Guest');
		$client->waitForText(CheckoutPage::$checkoutGuestForm, 30);
		$client->waitForElementVisible(CheckoutPage::$nameGuest, 30);
		$client->scrollTo(CheckoutPage::$nameGuest);
		$client->click(CheckoutPage::$nameGuest);
		$client->fillField(CheckoutPage::$nameGuest, $user['name']);
		$client->waitForElementVisible(CheckoutPage::$emailGuest, 30);
		$client->fillField(CheckoutPage::$emailGuest, $user['email']);
		$client->fillField(CheckoutPage::$phoneGuest, $user['phone']);
		$client->waitForElementVisible(CheckoutPage::$guestAddress, 30);
		$client->fillField(CheckoutPage::$guestAddress, $user['address']);
		$client->waitForElementVisible(CheckoutPage::$guestAddress2, 30);
		$client->fillField(CheckoutPage::$guestAddress2, $user['address']);
		$client->fillField(CheckoutPage::$guestCity, $user['city']);
		$client->waitForElementVisible(CheckoutPage::$guestZip, 30);
		$client->fillField(CheckoutPage::$guestZip, $user['zip']);
		$client->selectOptionInChosenById(CheckoutPage::$guestCountry, $user['country']);
		$client->selectOptionInChosenById(CheckoutPage::$guestCountry, $user['country']);
	}
}