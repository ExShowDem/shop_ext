<?php


namespace Step\Frontend;

use Page\Frontend\OfferPage as OfferPage;
use Step\Acceptance\redshopb2b as redshopb2b;
use Page\Frontend\Redshopb2bPage as Redshopb2bPage ;
class OfferSteps extends redshopb2b
{
	/**
	 * @param $name
	 * @param $company
	 * @throws \Exception
	 */
	public function createOffer($name, $company)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Offers page in frontend');
		$I->amOnPage(OfferPage::$URL);

		$I->waitForElement(OfferPage::$newButton, 30);
		$I->click(OfferPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->waitForElement(OfferPage::$adminForm, 30);
		$I->fillField(OfferPage::$labelOfferName, $name);
		$I->selectOptionInChosenjs(OfferPage::$labelCustomerType, 'Company');
		$I->comment('Wait for Companies lists to load');
		$I->waitForElementVisible(OfferPage::$companyLabelXpath);
		$I->selectOptionInChosenById(OfferPage::$companySearch, $company);
		$I->click(OfferPage::$saveButton);
		$I->waitForElement(OfferPage::$messageSuccessID, 60);
		$I->see(OfferPage::$saveSuccess, OfferPage::$messageSuccessID);
		$I->click(OfferPage::$saveCloseButton);
		$I->waitForElement(OfferPage::$searchOfferId, 60);
		$I->seeElement(['link' => $name]);
	}

	/**
	 * @param array $offer
	 * @throws \Exception
	 */
	public function createFullPotions($offer = array())
	{
		$I = $this;
		$I->amGoingTo('Navigate to Offers page in frontend');
		$I->amOnPage(OfferPage::$URL);

		$I->waitForElement(OfferPage::$newButton, 30);
		$I->click(OfferPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->waitForElement(OfferPage::$adminForm, 30);


		$I->comment('I am redirected to the form');
		$I->waitForElement(OfferPage::$adminForm, 30);
		$I->fillField(OfferPage::$labelOfferName, $offer['name']);

		if (isset($offer['expiation']))
		{
			$I->selectOptionInRadioField(OfferPage::$labelExpirationDate,$offer['expiation']);
		}

		if (isset($offer['date']))
		{
			$I->fillField(OfferPage::$labelDate,$offer['date']);
		}

		if (isset($offer['type']))
		{
			$I->wantTo('Choice type of offers');
			$I->selectOptionInChosenjs(OfferPage::$labelCustomerType,$offer['type']);
		}

		if (isset($offer['customer']))
		{
			$I->wantToTest('I choice customer for offer');

			$I->selectOptionInChosenjs(OfferPage::$labelCustomer,$offer['customer']);
			if ($offer['type'] == 'Company')
			{
				$I->wantToTest('Choice company for offer');
				$I->waitForElementVisible(OfferPage::$companyLabelXpath);
				$company  = $offer['customer'];
				$I->selectOptionInChosenById(OfferPage::$companySearch, $company);
			}
			if($offer['type'] == 'Department')
			{
				$I->wantTo('Choice department');
				$department  = $offer['customer'];
				$I->selectOptionInChosenById(OfferPage::$departmentSearch, $department);
			}
			if($offer['type'] == 'Employee')
			{
				$I->wantTo('Choice department');
				$employee  = $offer['customer'];
				$I->comment('Get Employee');
				$I->comment($employee);
				$I->selectOptionInChosenById(OfferPage::$employeeSearch, $employee);
			}
		}
		if(isset($offer['collection']))
		{
			$I->selectOptionInChosenjs(OfferPage::$labelCollection,$offer['collection']);
		}

		if(isset($offer['status']))
		{
			$I->selectOptionInChosen(OfferPage::$status,$offer['status']);
		}

		$I->click(OfferPage::$saveButton);
		$I->waitForElement(OfferPage::$messageSuccessID, 60);
		$I->see(OfferPage::$saveSuccess, OfferPage::$messageSuccessID);
		$I->click(OfferPage::$saveCloseButton);
		$I->waitForElement(OfferPage::$searchOfferId, 60);
		$I->seeElement(['link' => $offer['name']]);
	}

	/**
	 * @param $name
	 * @param array $product0
	 * @param array $product1
	 * @throws \Exception
	 */
	public function offerWithProduct($name, $product0 = array(), $product1 = array())
	{
		$I= $this;
		$I->am('Administrator');
		$I->wantToTest('Offer edit in Frontend');
		$I->amGoingTo('Navigate to Offers page in frontend');
		$I->amOnPage(OfferPage::$URL);
		$I->searchForItemInFrontend($name, ['search field locator id' => OfferPage::$searchOffer]);
		$I->wait(0.5);
		$I->click(['link' => $name]);

		$I->comment('I am redirected to the form');
		$I->waitForElement(OfferPage::$adminForm, 30);
		$I->click(OfferPage::$offerProductsTab);
		$I->wantTo('Input wait time foe make sure filter show up ');
		$I->waitForElement(OfferPage::$searchProductsXpath, 60);
		$I->waitForText($product0['sku'], 30);
		$I->searchForItemInFrontend($product0['sku'], ['search field locator id' => OfferPage::$productOffer]);
		try
		{
			$I->waitForText($product0['sku'], 10);
		} catch (\Exception $e)
		{
			$I->waitForText(OfferPage::$detailOfferTab, 10);
			$I->click(OfferPage::$detailOfferTab);
			$I->waitForText($product0['sku'], 10);
		}

		$I->click(OfferPage::$buttonAddFirstProduct);
		$I->wait(1);
		$I->click(OfferPage::$saveButton);
		$I->wait(0.5);
		$I->click(OfferPage::$offerProductAtOffer);
		$I->wantTo('Check first product will be show up at offer');
		$I->click(OfferPage::$saveCloseButton);

		$I->comment('I am redirected to the list');
		$I->waitForElement(OfferPage::$searchOfferId, 60);
		$I->seeElement(['link' => $name]);
		$I->click(['link'=> $name]);
		$I->waitForElement(OfferPage::$adminForm);
		$I->click(OfferPage::$offerProductsTab);

		$I->waitForElement(OfferPage::$searchProductsXpath, 30);
		$I->searchForItemInFrontend($product1['sku'], ['search field locator id' => OfferPage::$productOffer]);
		$I->click(OfferPage::$offerProductAtOffer);
		$I->wait(1);
		$I->wantTo('Check first product will be show up at offer');
		$I->click(OfferPage::$offerProductsTab);
		$I->searchForItemInFrontend($product1['sku'], ['search field locator id' => OfferPage::$productOffer]);

		try
		{
			$I->waitForText($product1['sku'], 10);
		} catch (\Exception $e)
		{
			$I->waitForText(OfferPage::$detailOfferTab, 10);
			$I->click(OfferPage::$detailOfferTab);
			$I->waitForText($product1['sku'], 10);
		}

		$I->comment('add the second product');
		$I->waitForElement(OfferPage::$buttonAddFirstProduct, 30);
		$I->click(OfferPage::$buttonAddFirstProduct);
		$I->wait(1);
		$I->waitForElement(OfferPage::$offerProductAtOffer, 30);
		$I->click(OfferPage::$offerProductAtOffer);
		$I->click(OfferPage::$saveCloseButton);
	}

	/**
	 * @param $user
	 * @param array $offer
	 * @param array $product0
	 * @param array $product1
	 * @param $currencySeparator
	 * @param $currency
	 * @throws \Exception
	 */
	public function checkoutForOffer($user, $offer, $product0, $product1 ,$currencySeparator, $currency)
	{
		$I = $this;
		$I->doFrontEndLogin($user['username'], $user['username']);
		$I->amOnPage(OfferPage::$URLForEmployee);
		$I->waitForElement(OfferPage::$searchMyOfferId, 30);
		$I->waitForText($offer['name'], 30);
		$I->click($offer['name']);
		$I->waitForElement(OfferPage::$adminForm, 30);
		$I->waitForElement(OfferPage::$buttonAccept, 30);
		$I->waitForText(OfferPage::$accept, 30, OfferPage::$buttonAccept);
		$I->click(OfferPage::$buttonAccept);

		$I->waitForElementVisible(['id' => 'myOfferModal']);

		$I->waitForElement(OfferPage::$buttonYesForm, 30);
		$I->click(OfferPage::$buttonYesForm);
		$nameOffer = $offer['name'];
		$I->waitForText($nameOffer, 30);

		$totalWithQuantity = (int)$product1['price']+ (int)$product0['price'];
		$totalWithQuantity = (string)$totalWithQuantity . $currencySeparator.'00 '.$currency;
		try
		{
			$I->waitForText($totalWithQuantity, 10, Redshopb2bPage::$priceTotalFinal);
		} catch (\Exception $e)
		{
			$I->waitForElement(OfferPage::$buttonYesForm, 30);
			$I->click(OfferPage::$buttonYesForm);
		}
		$I->click(Redshopb2bPage::$nextButton);
//		$I->waitForText(Redshopb2bPage::$deliveryInfoContent, 30, Redshopb2bPage::$deliveryInfo);
//		$I->waitForElement(Redshopb2bPage::$userBillingInfor, 30);
//		$I->click(Redshopb2bPage::$userBillingInfor);
//		$I->waitForElement(Redshopb2bPage::$emailBilling, 30);
//		$I->wait(1);
//		$I->fillField(Redshopb2bPage::$emailBilling, $user['email']);
//		$I->fillField(Redshopb2bPage::$nameBilling, $user['name']);
//		$I->fillField(Redshopb2bPage::$name2Billing, $user['name2']);
//		$I->fillField(Redshopb2bPage::$phoneBilling, $user['phone']);
//		$I->waitForElement(Redshopb2bPage::$updateButtonBilling, 30);
		$I->wait(2);
//		try
//		{
//			$I->click(Redshopb2bPage::$updateButtonBilling);
//			$I->wait(1);
//			$I->scrollTo(Redshopb2bPage::$emailBilling);
//			$I->waitForText($user['email'], 30);
//		} catch (\Exception $e)
//		{
//			$I->waitForElement(Redshopb2bPage::$updateButtonBilling, 30);
//			$I->wait(1);
//			$I->click(Redshopb2bPage::$updateButtonBilling);
//			$I->wait(1);
//			$I->scrollTo(Redshopb2bPage::$emailBilling);
//			$I->waitForText($user['email'], 30);
//		}
		$I->waitForElement(Redshopb2bPage::$nextButton, 30);
		$I->click(Redshopb2bPage::$nextButton);
		$I->waitForElement(Redshopb2bPage::$completeOderButton, 30);
		$I->click(Redshopb2bPage::$completeOderButton);
		$I->waitForElement(Redshopb2bPage::$messageSuccessID, 30);
		$I->waitForText(Redshopb2bPage::$messageOrderSuccess, 30, Redshopb2bPage::$messageSuccessID);
	}

	/**
	 * @param       $user
	 * @param       $category1
	 * @param       $category2
	 * @param       $currencySeparator
	 * @param       $currency
	 * @param array $product1
	 * @param array $product2
	 * @throws \Exception
	 */
	public function createOfferByEmployee($user, $category1, $product1 = array(), $offer = array())
	{
		$I = $this;
		$I->amGoingTo('Create new offer by employee');
		$I->doFrontEndLogin($user['username'], $user['username']);
		$I->amOnPage(Redshopb2bPage::$URLShop);
		$I->waitForElement(['link' => $category1], 30);
		$I->click(['link' => $category1]);
		$I->waitForText($category1, 30, Redshopb2bPage::$categoryClass);
		try
		{
			$I->waitForElement(['link' => $product1['name']], 30);
		}catch (\Exception $e)
		{
			$I->click(['link' => $category1]);
			$I->waitForText($category1, 30, Redshopb2bPage::$categoryClass);
			$I->waitForElement(['link' => $product1['name']], 30);
		}
		$I->waitForElement(Redshopb2bPage::$buttonAddToCart, 30);
		$I->click(Redshopb2bPage::$buttonAddToCart);
		$I->waitForElement(Redshopb2bPage::$addToCartModal, 30);
		$I->waitForText(Redshopb2bPage::$messageAddToCartSuccess, 30);
		$I->waitForElementVisible(Redshopb2bPage::$btnGoesToCheckout, 30);
		$I->wait(0.5);
		$I->click(Redshopb2bPage::$btnGoesToCheckout);

		$I->waitForElementVisible(Redshopb2bPage::$linkCartFirst, 30);

		$I->comment("I try to create new offer");
		$I->waitForElement(OfferPage::$requestOffer, 30);
		$I->click(OfferPage::$requestOffer);
		$I->waitForElement(OfferPage::$offerName, 30);
		$I->fillField(OfferPage::$offerName, $offer['name']);
		$I->waitForElement(OfferPage::$offerDescription, 30);
		$I->fillField(OfferPage::$offerDescription, $offer['description']);
		$I->waitForElement(OfferPage::$submitSendRequest, 30);
		$I->click(OfferPage::$submitSendRequest);
		$I->waitForText(OfferPage::$messageSuccess, 30, OfferPage::$messageSuccessID);
		$I->doFrontendLogout();
	}

	/**
	 * @param array $offer
	 * @throws \Exception
	 */
	public function sendOfferByAdministrator($offer = array())
	{
		$I= $this;
		$I->am('Administrator');
		$I->amGoingTo('Send offer by administrator');
		$I->amOnPage(OfferPage::$URL);
		$I->searchForItemInFrontend($offer['name'], ['search field locator id' => OfferPage::$searchOffer]);
		$I->click(['link' => $offer['name']]);

		$I->comment('I try to edit and send offer');
		$I->selectOptionInChosen(OfferPage::$status,$offer['status']);
		$I->waitForElementVisible(OfferPage::$discount, 30);
		try
		{
			$I->fillField(OfferPage::$discount, $offer['discount']);
		} catch (\Exception $exception)
		{
			$I->waitForElementVisible(OfferPage::$discount, 30);
			$I->fillField(OfferPage::$discount, $offer['discount']);
		}

		$I->waitForElementVisible(OfferPage::$saveButton, 30);
		$I->click(OfferPage::$saveButton);
		try
		{
			$I->waitForText(OfferPage::$messageEditSuccess, 10, OfferPage::$messageSuccessID);
		}catch (\Exception $exception)
		{
			$I->click(OfferPage::$saveButton);
			$I->waitForText(OfferPage::$messageEditSuccess, 10, OfferPage::$messageSuccessID);
		}
	}

	/**
	 * @param $user
	 * @param $offer
	 * @param $product0
	 * @param $product1
	 * @param $currencySeparator
	 * @param $currency
	 * @throws \Exception
	 */
	public function checkoutWithOfferAndUpdateCartModule($user, $offer = array(), $category2, $product1 = array(), $product2 = array() ,$currencySeparator, $currency)
	{
		$I = $this;
		$I->doFrontEndLogin($user['username'], $user['username']);
		$I->amOnPage(OfferPage::$URLForEmployee);
		$I->waitForElement(OfferPage::$searchMyOfferId, 30);
		$I->waitForText($offer['name'], 30);
		$I->click($offer['name']);

		$I->comment('I try to accept offer');
		$I->waitForElement(OfferPage::$adminForm, 30);
		$I->waitForElement(OfferPage::$buttonAccept, 30);
		$I->waitForText(OfferPage::$accept, 30, OfferPage::$buttonAccept);
		$I->click(OfferPage::$buttonAccept);
		$I->waitForElementVisible(OfferPage::$myOfferModal);
		$I->waitForElement(OfferPage::$buttonYesForm, 30);
		$I->click(OfferPage::$buttonYesForm);
		$nameOffer = $offer['name'];
		$I->waitForText($nameOffer, 30);
		$priceProductFirstWithDiscount = (int) $product1['price'] * (100 - $offer['discount']) / 100;
		$priceCartWithDiscount = (string) $priceProductFirstWithDiscount . $currencySeparator . '00 ' . $currency;
		$I->waitForText($priceCartWithDiscount, 30, OfferPage::$priceTotalFinal);

		$I->comment('I try to add more products to cart module');
		$I->amOnPage(OfferPage::$URLShop);
		$I->waitForElement(['link' => $category2], 30);
		$I->click(['link' => $category2]);
		$I->waitForText($category2, 30, OfferPage::$categoryClass);
		try
		{
			$I->waitForElement(['link' => $product2['name']], 30);
		}catch (\Exception $e)
		{
			$I->click(['link' => $category2]);
			$I->waitForText($category2, 30, OfferPage::$categoryClass);
			$I->waitForElement(['link' => $product2['name']], 30);
		}
		$I->waitForElement(OfferPage::$buttonAddToCart, 30);
		$I->click(OfferPage::$buttonAddToCart);
		$I->waitForElement(OfferPage::$addToCartModal, 30);
		$I->waitForText(OfferPage::$messageAddToCartSuccess, 30);
		$I->waitForElement(OfferPage::$btnGoesToCheckout, 30);
		$I->click(OfferPage::$btnGoesToCheckout);
		$I->waitForElement(OfferPage::$linkCartFirst, 30);

		$I->comment('I try to check total prices');
		$totalPriceWithDiscount = (int) $product2['price'] + $priceProductFirstWithDiscount;
		$totalPrice = (string) $totalPriceWithDiscount . $currencySeparator . '00 ' . $currency;
		$I->waitForText($totalPrice, 30, OfferPage::$priceTotalFinal);
		$I->wait(0.5);
		$I->click(OfferPage::$nextButton);
		$I->waitForText(OfferPage::$deliveryInfoContent, 30, OfferPage::$deliveryInfo);
		$I->waitForElement(OfferPage::$nextButton, 30);
		$I->wait(0.5);
		$I->click(OfferPage::$nextButton);
		$I->waitForElement(OfferPage::$completeOderButton, 30);
		$I->click(OfferPage::$completeOderButton);
		$I->waitForElement(OfferPage::$messageSuccessID, 30);
		$I->waitForText(OfferPage::$messageOrderSuccess, 30, OfferPage::$messageSuccessID);
	}


	/**
	 * @param $name
	 * @param $nameNew
	 * @throws \Exception
	 */
	public function editOffer($name, $nameNew)
	{
		$I= $this;
		$I->am('Administrator');
		$I->wantToTest('Offer edit in Frontend');
		$I->amGoingTo('Navigate to Offers page in frontend');
		$I->amOnPage(OfferPage::$URL);
		$I->searchForItemInFrontend($name, ['search field locator id' => OfferPage::$searchOffer]);
		$I->click(['link' => $name]);

		$I->comment('I am redirected to the form');
		$I->waitForElement(OfferPage::$adminForm, 30);
		$I->wait(0.5);
		$I->waitForElementVisible(OfferPage::$nameID, 30);
		$I->fillField(OfferPage::$nameID, $nameNew);
		$I->waitForElementVisible(OfferPage::$discount, 30);
		$I->fillField(OfferPage::$discount,1);
		$I->waitForElementVisible(OfferPage::$saveButton, 30);
		$I->click(OfferPage::$saveButton);
		try
		{
			$I->waitForElement(['id' => 'system-message'], 10);
		}catch (\Exception $exception)
		{
			$I->click(OfferPage::$saveButton);
			$I->waitForElement(['id' => 'system-message'], 10);
		}

		$I->waitForText('Item saved', 30, ['id' => 'system-message']);
		$I->click(OfferPage::$saveCloseButton);

		$I->comment('I am redirected to the list');
		$I->waitForElement(OfferPage::$searchOfferId, 60);
		$I->seeElement(['link' => $nameNew]);
		$I->dontSeeElement(['link' => $name]);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteOffer($name)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Delete an offer in Frontend');
		$I->amGoingTo('Navigate to Offers page in frontend');
		$I->amOnPage(OfferPage::$URL);
		$I->searchForItemInFrontend($name, ['search field locator id' => OfferPage::$searchOffer]);
		$I->checkAllResults();
		$I->waitForElement(OfferPage::$deleteButton, 30);
		$I->click(OfferPage::$deleteButton);
		$I->comment('I am redirected to the list');
		$I->waitForElement(OfferPage::$messageSuccessID, 60);
		$I->see(OfferPage::$messageDeleteSuccess, OfferPage::$messageSuccessID);
		$I->dontSeeElement(['link' => $name]);
	}
}