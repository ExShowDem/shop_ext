<?php

namespace Step\Frontend;
use ImageOptimizer\Exception\Exception;
use Page\Frontend\OrderPage as OrderPage;
use Page\Frontend\ShopPage as ShopPage;
use Step\Acceptance\redshopb2b;

class OrderSteps extends redshopb2b
{
	/**
	 * @param $customer
	 * @param array $product
	 * @param $category
	 * @param array $priceConfiguration
	 * @throws \Exception
	 */
	public function createOrder($customer, $product = array(), $category, $priceConfiguration = array())
	{
		$I = $this;
		$I->wantTo('Create order by super user');
		$I->amOnPage(OrderPage::$Url);
		$I->click(OrderPage::$newButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
		$I->waitForElement(ShopPage::$searchId, 30);
		$I->searchForCompany($customer, ['search field locator id' => ShopPage::$searchShop]);
		$I->waitForText($customer, 30, ShopPage::$nameShop);
		$I->wait(1);
		$I->click(['link' => ShopPage::$buttonShop]);
		$I->waitForElement(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForElement(['link' => $product['name']], 30);
		$I->click(['link' => $product['name']]);
		$I->waitForText($product['name'], 30, ShopPage::$productNameH1);
		$I->fillField(ShopPage::$quantityProduct, $product['quantity']);

		$totalWithQuantity = (int)$product['price']* $product['quantity'];
		$totalWithQuantity = (string)$totalWithQuantity . $priceConfiguration['currencySeparator'].'00 '.$priceConfiguration['currencySymbol'];

		$I->click(ShopPage::$buttonAddToCart);
		$I->waitForElementVisible(ShopPage::$addToCartModal, 15);
		$I->see(ShopPage::$messageAddToCartSuccess);
		$I->wait(1);
		$I->waitForElement(ShopPage::$btnGoesToCheckout, 30);
		$I->click(ShopPage::$btnGoesToCheckout);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 60);
		$I->waitForElement(ShopPage::$shopCart, 30);
		$I->see($totalWithQuantity, ShopPage::$priceFinalXpath);
		$I->click(ShopPage::$nextButton);
		$I->wait(1);
		$I->click(ShopPage::$nextButton);
		$I->waitForElement(ShopPage::$completeOderButton, 30);
		$I->wait(1);
		$I->click(ShopPage::$completeOderButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElement(ShopPage::$messageSuccessID,30);
		$I->wait(1);
		$I->amOnPage(OrderPage::$Url);
		$I->waitForElement(OrderPage::$iconClear, 30);
		$I->wait(1);
		$I->click(OrderPage::$iconClear);
		$I->searchForItemInFrontend($customer, ['search field locator id' => OrderPage::$searchOrder]);
		$I->waitForText("Company: $customer", 30, OrderPage::$adminForm);
	}
	
	/**
	 * @param $employeeWithLogin
	 * @param $category
	 * @param array $productCurrent
	 * @param array $productAdd
	 * @param array $vatSetting
	 * @param $enviroment
	 * @throws \Exception
	 */
	public function changeOrderItem($employeeWithLogin, $category, $productCurrent, $productAdd, $vatSetting)
	{
		$I = $this;
		$I->wantTo('Login with super user and add more product for orders ');
		$I->amOnPage(OrderPage::$Url);
		$I->wantToTest('Get order from order of user is employeeWithLogin');
		$I->searchForItemInFrontend($employeeWithLogin, ['search field locator id' => OrderPage::$searchOrder]);
		try
		{
			$I->waitForText("Employee: $employeeWithLogin", 5, OrderPage::$adminForm);
		} catch (\Exception $e) {
			$I->reloadPage();
			$I->searchForItemInFrontend($employeeWithLogin, ['search field locator id' => OrderPage::$searchOrder]);
			$I->waitForText("Employee: $employeeWithLogin", 5, OrderPage::$adminForm);
		}
		$I->executeJS( "jQuery('thead th:first-child input').click()");
		$I->click(OrderPage::$editButton);
		$I->wait(1);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElement(OrderPage::$adminForm, 30);
		$I->comment('I ensure that no shipping or payment information is shown');
		$I->dontSee(OrderPage::$shippingTitle, OrderPage::$titleAtOrderDetail);
		$I->waitForElement(OrderPage::$orderItemTab, 30);
		$I->click(OrderPage::$orderItemTab);
		$I->wantTo('goes on category page and add more product');
		$I->waitForElement(OrderPage::$buttonChangeItem, 30);
		$I->click(OrderPage::$buttonChangeItem);
		$I->waitForElement(['link' => $category], 30);
		$I->click(['link' => $category]);
		$I->waitForElement(['link' => $productAdd['name']], 30);
		$I->click(ShopPage::$buttonAddToCart);
		$I->waitForElement(ShopPage::$addToCartModal, 30);
		$I->see(ShopPage::$messageAddToCartSuccess);
		$I->click(ShopPage::$btnGoesToCheckout);
		$I->waitForElement(ShopPage::$shopCart, 30);

		$totalWithQuantityProductAdd = (int)$productAdd['price'];

		$I->comment('total With Quantity Product Add');
		$I->comment($totalWithQuantityProductAdd);

		$totalWithQuantityProductCurrent = (int)$productCurrent['price'];

		$I->comment('total With Quantity Product Current');
		$I->comment($totalWithQuantityProductCurrent);
		$totalWithQuantity = (string)($totalWithQuantityProductAdd + $totalWithQuantityProductCurrent ). $vatSetting['currencySeparator'].'00 '.$vatSetting['currencySymbol'];

		$I->comment('total with quantity');
		$I->comment($totalWithQuantity);
		$I->waitForElement(ShopPage::$priceTotalFinal, 30);
		$I->waitForText($totalWithQuantity, 30, ShopPage::$priceTotalFinal);
		$I->waitForElement(OrderPage::$buttonUpdateOrder, 30);

		$I->wantTo('Update order with more product');
		$I->click(OrderPage::$buttonUpdateOrder);
		$I->waitForElement(OrderPage::$buttonChangeItem, 30);

		$I->click(OrderPage::$orderItemTab);
		$I->wantTo('Make sure inside order will have 2 products');
		$I->seeLink($productAdd['name']);
		$I->seeLink($productCurrent['name']);
		$I->comment('Get 2 product inside cart');
	}
	
	/**
	 * @param $employeeWithLogin
	 * @param $type
	 * @throws \Exception
	 */
	public function changeOrderStatusToConfirmed($employeeWithLogin, $type)
	{
		$I = $this;
		$I->comment('Changing the status of an Order in Frontend');
		$I->amOnPage(OrderPage::$Url);
		$typeResults = $type.':';
		$I->searchForItemInFrontend($employeeWithLogin, ['search field locator id' => OrderPage::$searchOrder]);
		$I->see("$typeResults $employeeWithLogin");
		$I->executeJS( "jQuery('thead th:first-child input').click()");
		$I->click(OrderPage::$editButton);
		$I->waitForElement(OrderPage::$adminForm, 30);
		$I->comment('I ensure that no shipping or payment information is shown');
		$I->dontSee(OrderPage::$shippingTitle, OrderPage::$titleAtOrderDetail);
		$I->dontSee(OrderPage::$paymentTitle, OrderPage::$titleAtOrderDetail);
		$I->selectOptionInChosenjs(OrderPage::$labelStatusOrder, OrderPage::$confirmed);
		$I->doFrontEndLoginRetry();
		$I->click(OrderPage::$saveCloseButton);
		$I->wait(2);
		$I->waitForElement(OrderPage::$adminForm, 30);
		$I->waitForText(OrderPage::$confirmed, 30, OrderPage::$statusOrder);
	}
	
	/**
	 * @param $employeeWithLogin
	 * @param $type
	 * @throws \Exception
	 */
	public function deleteOrder($employeeWithLogin, $type)
	{
		$I = $this;
		$I->am('Administrator');
		$I->comment('changing the status of an Order in Frontend');
		$I->amOnPage(OrderPage::$Url);
		$typeResults = $type.':';
		$I->searchForItemInFrontend($employeeWithLogin, ['search field locator id' => OrderPage::$searchOrder]);
		$I->waitForText("$typeResults $employeeWithLogin", 30, OrderPage::$adminForm);
		$I->executeJS( "jQuery('thead th:first-child input').click()");
		$I->waitForElement(OrderPage::$deleteButton, 30);
		$I->click(OrderPage::$deleteButton);
		$I->searchForItemInFrontend($employeeWithLogin, ['search field locator id' => OrderPage::$searchOrder]);
		$I->dontSee("$typeResults $employeeWithLogin", OrderPage::$adminForm);
	}

	/**
	 * @throws \Exception
	 */
	public function deleteAllOrder()
	{
		$I = $this;
		$I->am('Administrator');
		$I->amOnPage(OrderPage::$Url);
		$I->wait(0.5);
		$I->checkAllResults();
		$I->waitForElement(OrderPage::$deleteButton, 30);
		$I->click(OrderPage::$deleteButton);
		$I->waitForElement(OrderPage::$alertHead, 30, OrderPage::$alertMessage);
	}
	/**
	 * @param $value
	 * @throws \Exception
	 */
	public function searchOrder($value)
	{
		$client = $this;
		$client->waitForElement(OrderPage::$filterOrderId, 30);
		$client->fillField(OrderPage::$filterOrderId, $value);
	}

	/**
	 * @param int $retry
	 */
	public function doFrontEndLoginRetry($retry = 3)
	{
		$this->retry('clickApplySave', [], $retry);
	}

	/**
	 * @throws \Exception
	 */
	public function clickApplySave()
	{
		$I = $this;
		$I->click(OrderPage::$btnApplyStatusOrder);
		$I->waitForText(OrderPage::$messageSaveOrderSuccess, 30, OrderPage::$messageSuccessID);
	}
}