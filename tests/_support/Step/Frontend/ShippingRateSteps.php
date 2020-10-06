<?php
namespace Step\Frontend;
use Step\Acceptance\redshopb2b;
use Page\Frontend\ShippingMethodPage as ShippingMethodPage; 
class ShippingRateSteps extends redshopb2b
{
	/**
	 * @param $shippingMethod
	 * @param $countries
	 * @param $categories
	 * @param array $products
	 * @param array $shippingRate
	 * @throws \Exception
	 */
	public function createShippingRate($shippingMethod, $countries, $categories, $products = array(), $shippingRate = array())
	{
		$I = $this;
		$I->amGoingTo('Navigate to Shipping Rates page in frontend');
		$I->amOnPage(ShippingMethodPage::$URLShippingRate);

		$I->waitForElement(ShippingMethodPage::$newSuccessButton, 30);
		$I->click(ShippingMethodPage::$newSuccessButton);
		$I->waitForElement(ShippingMethodPage::$adminForm, 30);

		$I->comment('I am redirected to the Shipping Rate Edit Form');
		$I->selectOptionInChosenjs('Shipping method', $shippingMethod);

		$I->fillField(ShippingMethodPage::$nameID, $shippingRate['name']);
		$I->wait(1);
		$I->comment('Start for country');
		if (isset($countries))
		{
			foreach ($countries as $country)
			{
				$I->selectOptionInChosenjs(ShippingMethodPage::$labelCountry, $country);
			}
		}
		if (isset($products))
		{
			foreach ($products as $product)
			{
				$I->selectOptionsInSelect2(ShippingMethodPage::$labelProduct, [$product]);
			}
		}
		$I->wait(1);
		$I->comment('Start for $categories');
		if (isset($categories))
		{
			foreach ($categories as $category)
			{
				$I->selectOptionInChosenjs(ShippingMethodPage::$labelCategory, $category);
			}
		}

		if (isset($shippingRate['priority']))
		{
			$I->fillField(ShippingMethodPage::$priority, $shippingRate['priority']);
		}
		
		if (isset($shippingRate['price']))
		{
			$I->fillField(ShippingMethodPage::$priceShipping, $shippingRate['price']);
		}

		$I->wait(1);
		if (isset($shippingRate['status']))
		{
			$I->selectOptionInRadioField(ShippingMethodPage::$status, $shippingRate['status']);
		}
		$I->wait(2);
		$I->waitForElement(ShippingMethodPage::$saveCloseButton, 30);
		$I->scrollTo(ShippingMethodPage::$saveCloseButton);
		$I->wait(1);
		$I->click(ShippingMethodPage::$saveCloseButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->searchForItemInFrontend($shippingRate['name'], ['search field locator id' => ShippingMethodPage::$searchShippingRate]);
		$I->waitForElement(['link' => $shippingRate['name']], 30);
	}
	
	/**
	 * @param $name
	 * @param $nameEdit
	 * @throws \Exception
	 */
	public function createShippingEdit($name, $nameEdit)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Shipping Rates page in frontend');
		$I->amOnPage(ShippingMethodPage::$URLShippingRate);
		$I->wait(1);
		$I->searchForItemInFrontend($name, ['search field locator id' => ShippingMethodPage::$searchShippingRate]);
		$I->waitForElement(['link' => $name], 30);
		$I->wait(0.5);
		$I->checkAllResults();
		$I->click(ShippingMethodPage::$editButton);
		$I->wait(1);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElement(ShippingMethodPage::$adminForm, 30);
		$I->wait(1);
		$I->fillField(ShippingMethodPage::$nameID, $nameEdit);
		$I->waitForElement(ShippingMethodPage::$saveCloseButton, 30);
		$I->click(ShippingMethodPage::$saveCloseButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->searchForItemInFrontend($nameEdit, ['search field locator id' => ShippingMethodPage::$searchShippingRate]);
		$I->waitForElement(['link' => $nameEdit], 30);
		$I->dontSeeElement(['link' => $name]);
	}
	
	/**
	 * @param $shippingMethod
	 * @param $nameShippingRate
	 * @throws \Exception
	 */
	public function createWrongValue($shippingMethod, $priceShippingRate)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Shipping Rates page in frontend');
		$I->amOnPage(ShippingMethodPage::$URLShippingRate);
		$I->waitForElement(ShippingMethodPage::$newSuccessButton, 30);
		$I->click(ShippingMethodPage::$newSuccessButton);
		$I->waitForElement(ShippingMethodPage::$adminForm, 30);

		$I->comment('I am redirected to the Shipping Rate Edit Form');
		$I->selectOptionInChosenjs('Shipping method', $shippingMethod);
		$I->fillField(ShippingMethodPage::$priceShipping, $priceShippingRate);
		$I->comment('Check Missing name');
		$I->click(ShippingMethodPage::$saveButton);
		$I->waitForText(ShippingMethodPage::$messageInvalidFieldName, 30, ShippingMethodPage::$systemContainer);
	}
	
	/**
	 * @param $employeeWithLogin
	 * @param $category
	 * @param $currencySymbol
	 * @param $shippingRates
	 * @param $productPrice
	 * @throws \Exception
	 */
	public function checkoutWithShippingRate($employeeWithLogin, $category, $currencySymbol, $shippingRates, $productPrice)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Product checkout in Frontend with Shipping and Payment method');

		$I->doFrontEndLogin($employeeWithLogin, $employeeWithLogin);

		$I->amOnPage(ShippingMethodPage::$URLShop);
		$I->waitForElementVisible(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForText($category, 30, ShippingMethodPage::$categoryClass);
		$I->waitForElementVisible(ShippingMethodPage::$buttonAddToCart, 30);
		$I->click(ShippingMethodPage::$buttonAddToCart);
		$I->waitForElementVisible(ShippingMethodPage::$addToCartModal, 15);
		$I->waitForText(ShippingMethodPage::$messageAddToCartSuccess, 30);
		$I->waitForElementVisible(ShippingMethodPage::$btnGoesToCheckout, 30);
		$I->click(ShippingMethodPage::$btnGoToCheckout);
		$I->wait(1);
		$I->waitForElementVisible(ShippingMethodPage::$linkCartFirst, 30);
		$I->waitForElementVisible(ShippingMethodPage::$nextButton, 30);
		$I->click(ShippingMethodPage::$nextButton);
		$I->waitForText(ShippingMethodPage::$deliveryInfoContent, 30, ShippingMethodPage::$deliveryInfo);
		$I->wait(1);
		$I->waitForElementVisible(ShippingMethodPage::$nextButton, 30);
		$I->click(ShippingMethodPage::$nextButton);
		$I->waitForText(ShippingMethodPage::$selectShippingMethodContent, 30, ShippingMethodPage::$selectShippingMethod);

		$lenght = count($shippingRates);

		for ($i = 0; $i< $lenght; $i++)
		{
			$shippingRate = $shippingRates[$i];

			$j = $i +1;
			$j = (string)$j;
			$usePage = new ShippingMethodPage();
			$I->waitForElementVisible($usePage->returnShipping($j), 30);
			$I->click($usePage->returnShipping($j));
			$shippingRate['price'] = $shippingRate['price']. ',00';
			$I->seeElement($usePage->shippingName($shippingRate['name'], $currencySymbol, $shippingRate['price']));
			$I->waitForElement(ShippingMethodPage::$nextButton, 30);
			$I->click(ShippingMethodPage::$nextButton);
			$priceFinal = $shippingRate['price'] ;
			$priceFinal = (int)$priceFinal +  (int)$productPrice;
			$I->waitForText($currencySymbol .' '. $priceFinal . ',00',30);

			if ($i != ($lenght-1))
			{
				$I->waitForElementVisible(ShippingMethodPage::$backButton, 30);
				$I->click(ShippingMethodPage::$backButton);
			}
			$I->comment( $shippingRate['price']);
		}

//        //This should be open when codeception work for delete order VNR-4642
		$I->comment('I check that shipping rate price is shown');
		$I->click(ShippingMethodPage::$completeOderButton);
		$I->amOnPage(ShippingMethodPage::$URLShop);
		$I->waitForElement(['link' => $category], 30);
	}
}