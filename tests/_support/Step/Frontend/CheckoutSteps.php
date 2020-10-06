<?php

namespace Step\Frontend;
use Page\Frontend\Redshopb2bPage as Redshopb2bPage;
use Page\Frontend\CheckoutPage as CheckoutPage;
use Step\Acceptance\redshopb2b as redshopb2b;
use Step\Integration\OnePageCheckout\DefaultSteps\onePageCheckout as OnePageCheckoutSteps;
use Page\Integration\CheckoutWithProductVariants\CheckoutWithProductVariantsPage as CheckoutWithProductVariantsPage;

/**
 * Class CheckoutSteps
 *
 * @package Step\Frontend
 * @since 2.8.0
 */
class CheckoutSteps extends redshopb2b
{
	/**
	 * @param $category
	 * @param $product
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function addToCartWithProductNormal($category, $product)
	{
		$client = $this;
		$client->amOnPage(Redshopb2bPage::$URLShop);
		$client->waitForElementVisible(['link' => $category], 60);
		$client->click(['link' => $category]);
		$client->waitForElementVisible(['link' => $product['name']], 60);
		$client->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 60);
		$client->wait(0.5);
		$client->click(Redshopb2bPage::$buttonAddToCart);
		$client->waitForElementVisible(Redshopb2bPage::$addToCartModal, 40);
		$client->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$client->waitForText($product['name'], 30);
		$client->click(Redshopb2bPage::$btnGoesToCheckout);
	}

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
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function addToCarProductVariants($category, $product, $nameAttributeFirst, $nameAttributeSecond, $valueColor, $valueSize, $prices, $quantity, $vatSetting)
	{
		$client = $this;
		$client->amOnPage(CheckoutWithProductVariantsPage::$URLShop);
		$client->waitForElementVisible(['link' => $category], 30);
		$client->click(['link' => $category]);
		$client->waitForText($category, 30, CheckoutWithProductVariantsPage::$categoryClass);
		$client->waitForElementVisible(['link' => $product], 30);
		$client->waitForElementVisible(CheckoutWithProductVariantsPage::$btnInfoAddToCart, 30);
		$client->click(CheckoutWithProductVariantsPage::$btnInfoAddToCart);
		$client->selectOptionInChosenjs($nameAttributeFirst, $valueColor);
		$client->selectOptionInChosenjs($nameAttributeSecond, $valueSize);

		try
		{
			$client->waitForElementVisible(CheckoutWithProductVariantsPage::$attributeQuantity, 60);
			$client->wait(1);
			$client->fillField(CheckoutWithProductVariantsPage::$attributeQuantity, $quantity);
		} catch (\Exception $e)
		{
			$client->selectOptionInChosenjs($nameAttributeFirst, $valueColor);
			$client->selectOptionInChosenjs($nameAttributeSecond, $valueSize);
			$client->waitForElementVisible(CheckoutWithProductVariantsPage::$attributeQuantity, 60);
			$client->wait(1);
			$client->fillField(CheckoutWithProductVariantsPage::$attributeQuantity, $quantity);
		}

		$client->scrollTo(CheckoutWithProductVariantsPage::$addToCartVariants);
		$client->waitForElementVisible(CheckoutWithProductVariantsPage::$addToCartVariants, 30);
		$client->click(CheckoutWithProductVariantsPage::$addToCartVariants);

		try
		{
			$client->waitForElementVisible(CheckoutWithProductVariantsPage::$addToCartModal, 60);
			$client->waitForText(CheckoutWithProductVariantsPage::$messageAddToCartSuccess, 30);
		} catch (\Exception $e)
		{
			$client->scrollTo(CheckoutWithProductVariantsPage::$buttonAddToCart);
			$client->waitForElementVisible(CheckoutWithProductVariantsPage::$buttonAddToCart, 30);
			$client->click(CheckoutWithProductVariantsPage::$buttonAddToCart);
			$client->waitForElementVisible(CheckoutWithProductVariantsPage::$addToCartModal, 60);
			$client->waitForText(CheckoutWithProductVariantsPage::$messageAddToCartSuccess, 30);
		}

		$client->waitForElementVisible(CheckoutWithProductVariantsPage::$btnGoesToCheckout, 30);
		$client->click(CheckoutWithProductVariantsPage::$btnGoesToCheckout);
		$client->scrollTo(CheckoutWithProductVariantsPage::$orderItemRow);
		$client->waitForElementVisible(CheckoutWithProductVariantsPage::$orderItemRow, 30);
		$client->click(CheckoutWithProductVariantsPage::$orderItemRow);
		$client->waitForText($nameAttributeFirst, 30);
		$client->waitForText($valueColor, 30);
		$client->waitForText($nameAttributeSecond, 30);
		$client->waitForText($valueSize, 30);
		$totalPrices = $prices * $quantity;
		$client->waitForText((string) $totalPrices . $vatSetting['currencySeparator'].'00 '.$vatSetting['currencySymbol'], 30, CheckoutWithProductVariantsPage::$priceTotalFinal);
	}

	/**
	 * @param array $user
	 * @param $category
	 * @param $totalCurrency
	 * @param array $products
	 * @param $cart
	 * @param $delivery
	 * @throws \Exception
	 * Delivery depend on configuration page for get Login , Register , one Page checkout
	 */
	public function deliverySteps($user , $vatSetting, $product, $delivery, $register, $vendor)
	{
		$client = $this;
		$client->amOnPage(Redshopb2bPage::$URLShop);
		$client->waitForElementVisible(['link' => $product['category']], 60);
		$client->click(['link' => $product['category']]);
		$client->waitForElement(['link' => $product['name']], 60);
		$client->waitForElementVisible(Redshopb2bPage::$buttonAddToCart, 60);
		$client->wait(0.5);
		$client->click(Redshopb2bPage::$buttonAddToCart);
		$client->waitForElement(Redshopb2bPage::$addToCartModal, 40);
		$client->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$client->waitForText($product['name'], 30);
		$client->click(Redshopb2bPage::$btnGoesToCheckout);
		$client->waitForElement(Redshopb2bPage::$linkCartFirst, 30);

		$totalWithQuantity = (int) $product['price'];
		$totalWithQuantity = (string) $totalWithQuantity .$vatSetting['currencySeparator'].'00 '.$vatSetting['currencySymbol'];
		$client->comment($totalWithQuantity);
		$client->waitForText($totalWithQuantity, 30);
		$client->waitForElement(CheckoutPage::$nextButton, 30);
		$client->click(CheckoutPage::$nextButton);

		$client->amGoingTo('The kind of checkout');

		switch ($register)
		{
			case 'log_in':
				$client->wantToTest('Login with account ');
				$client->waitForText(CheckoutPage::$loginForm, 30);
				$client->click(CheckoutPage::$loginForm);
				$client->waitForElement(CheckoutPage::$usernameId, 30);
				$client->fillField(CheckoutPage::$usernameId, $user['name']);
				$client->waitForElement(CheckoutPage::$passwordId, 30);
				$client->fillField(CheckoutPage::$passwordId, $user['name']);
				$client->waitForElement(CheckoutPage::$loginButtonCheckout, 30);
				$client->click(CheckoutPage::$loginButtonCheckout);
				break;

			case 'sign_up':
				$client->wantToTest('Create new account ');
				$client->waitForText(CheckoutPage::$signupform, 30);
				$client->click(CheckoutPage::$signupform);
				$client->waitForElement(CheckoutPage::$userName, 30);
				$client->fillField(CheckoutPage::$firstName, $user['name']);
				$client->fillField(CheckoutPage::$lastName, $user['name']);
				$client->fillField(CheckoutPage::$emailUser, $user['email']);
				$client->fillField(CheckoutPage::$userName, $user['name']);
				$client->fillField(CheckoutPage::$passWord, $user['name']);
				$client->fillField(CheckoutPage::$passWordConfirm, $user['name']);
				$client->fillField(CheckoutPage::$billingAddressFirst, $user['address']);
				$client->fillField(CheckoutPage::$billingAddressSecond, $user['address']);
				$client->fillField(CheckoutPage::$billingZip, 5000);
				$client->fillField(CheckoutPage::$billingPhone, 1223);
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
					$client->fillField(CheckoutPage::$shippingPhone, 2535345);
					$client->selectOptionInChosenById(CheckoutPage::$shippingCountry, $delivery['country']);
				}

				$client->waitForText(CheckoutPage::$registerButon, 30);
				$client->click(CheckoutPage::$registerButon);
				$client->waitForElement(CheckoutPage::$alertMessage, 30);
				$client->waitForText(CheckoutPage::$createUserSuccessMessage, 30);
				$client->waitForText(CheckoutPage::$changeDeliveryAddress, 30);
				$client->waitForText(CheckoutPage::$deliveryInfo, 30);
				break;

			case 'guest':
				$client->waitForText(CheckoutPage::$checkoutGuestForm, 30);
				$client->waitForElementVisible(CheckoutPage::$nameGuest, 30);
				$client->scrollTo(CheckoutPage::$nameGuest);
				$client->click(CheckoutPage::$nameGuest);
				$client->fillField(CheckoutPage::$nameGuest, $user['name']);
				$client->waitForElementVisible(CheckoutPage::$emailGuest, 30);
				$client->fillField(CheckoutPage::$emailGuest, $user['email']);
				$client->fillField(CheckoutPage::$phoneGuest, $user['phone']);
				$client->waitForElementVisible(CheckoutPage::$guestAddress,30);
				$client->fillField(CheckoutPage::$guestAddress, $user['address']);
				$client->waitForElementVisible(CheckoutPage::$guestAddress2,30);
				$client->fillField(CheckoutPage::$guestAddress2, $user['address']);
				$client->fillField(CheckoutPage::$guestCity, ' Ho Chi Minh');
				$client->waitForElementVisible(CheckoutPage::$guestZip, 30);
				$client->fillField(CheckoutPage::$guestZip, $user['zip']);
				$client->selectOptionInChosenById(CheckoutPage::$guestCountry, $user['country']);
				$client->selectOptionInChosenById(CheckoutPage::$guestCountry, $user['country']);
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
				$client->waitForElement(CheckoutPage::$contactPersonEmail, 30);
				$client->click(CheckoutPage::$contactPersonEmail);
				$client->waitForElement(CheckoutPage::$invoiceMail, 30);
				$client->fillField(CheckoutPage::$invoiceMail, $vendor['invoice_email']);
			}
		}

		$client->click(CheckoutPage::$nextButton);
		$client->comment('User go to Confirm tab');

		if ($register == 'guest')
		{
			$client->see($user['email']);
			$client->waitForElement(['link' => $product['name']], 30);
			$client->see($totalWithQuantity);
			$client->click(CheckoutPage::$completeOderButton);
			$client->see($user['email']);
			$client->waitForElement(['link' => $product['name']], 30);
			$client->see($totalWithQuantity);
		}
	}
	
	/**
	 * @param $user
	 * @param $vatSetting
	 * @param $product
	 * @param $delivery
	 * @throws \Exception
	 * The method fot make confirm done on checkout page
	 */
	public function confirmSteps($user, $vatSetting, $product, $delivery)
	{
		$client = $this;
		$client->amOnPage(CheckoutPage::$deliveryURL);
		$client->click(CheckoutPage::$nextButton);
		$client->waitForText(CheckoutPage::$vendorInformation, 30);
		$client->waitForText($user['address'], 30);

		if (isset($delivery['address']))
		{
			$client->waitForText($delivery['address'], 30);
		}

		$totalWithQuantity = (int) $product['price'];
		$client->waitForElement(['link' => $product['name']], 30);
		$totalWithQuantity = (string) $totalWithQuantity .$vatSetting['currencySeparator'].'00 '.$vatSetting['currencySymbol'];
		$client->comment($totalWithQuantity);
		$client->waitForText($totalWithQuantity, 30);
		$client->click(CheckoutPage::$completeOderButton);
		$client->waitForElement(['link' => $product['name']], 30);
		$client->waitForText($totalWithQuantity, 30);
	}
	
	/**
	 * @param $user
	 * @param $company
	 * @param $vatSetting
	 * @param $product
	 * @throws \Exception
	 * The method for confirm with Emloyee belong the child company
	 *
	 */
	public function confirmStepsForEmployee($user, $company, $vatSetting, $product)
	{
		$client = $this;
		$client->amOnPage(CheckoutPage::$deliveryURL);
		$client->click(CheckoutPage::$nextButton);
		$client->waitForText(CheckoutPage::$vendorInformation, 30);
		$client->waitForText($user['name'], 30);
		$totalWithQuantity = (int) $product['price'];
		$client->waitForElement(['link' => $product['name']], 30);
		$totalWithQuantity = (string) $totalWithQuantity .$vatSetting['currencySeparator'].'00 '.$vatSetting['currencySymbol'];
		$client->comment($totalWithQuantity);
		$client->waitForText($totalWithQuantity, 30);
		$client->click(CheckoutPage::$completeOderButton);
		$client->waitForElement(['link' => $product['name']], 30);
		$client->waitForText($totalWithQuantity, 30);
	}
	
	/**
	 * @param $currency
	 * @param $price
	 * @param $currencySymbol
	 * @return string
	 */
	public function totalPrice($currency, $price, $currencySymbol)
	{
		$showPrice = $currencySymbol. $price. $currency;
		
		return $showPrice;
	}
}