<?php
/**
 * @package  AcceptanceTester
 * @copyright   Copyright (C) 2016 - 2018 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since    2.0.3
 */

namespace Step\Frontend;
use Page\Frontend\CollectionPage as CollectionPage;

class CollectionSteps extends \Step\Acceptance\redshopb2b
{
	/**
	 * @param   string $department         Name of department for collection
	 * @param   string $collectionName     Name of collection
	 * @param   string $company            Name of company
	 * @param   string $currency           Name of currency
	 * @param   string $status             Status of collection
	 * @param   array $product            Product inside Collection
	 * @throws \Exception
	 */
	public function create($department, $collectionName, $company, $currency, $status, $product = array())
	{
		$client = $this;
		$client->am('Administrator');
		$client->wantToTest('Collection creation in Frontend');
		$client->amGoingTo('Navigate to Collections page in frontend');
		$client->amOnPage(CollectionPage::$URL);
		$client->checkForPhpNoticesOrWarnings();

		$client->waitForElement(CollectionPage::$newButton, 30);
		$client->click(CollectionPage::$newButton);
		$client->comment('I am redirected to the form');
		$client->waitForElement(CollectionPage::$adminForm, 30);
		$client->checkForPhpNoticesOrWarnings();
		$client->waitForElement(CollectionPage::$nameID, 30);
		$client->fillField(CollectionPage::$nameID, $collectionName);
		$client->selectOptionInChosen(CollectionPage::$labelCompany,$company );
		$client->wait(0.5);
		$client->selectOptionInChosen(CollectionPage::$labelCollectionCurrency, $currency);
		$client->waitForElement(CollectionPage::$departmentsFieldID, 30);
		$client->selectMultipleOptionsInChosen(CollectionPage::$labelCustomerDepartments, [$department]);
		$client->wait(0.5);
		$client->selectOptionInRadioField(CollectionPage::$labelStatus, $status);
		$client->waitForElementVisible(CollectionPage::$buttonCreateNext, 30);
		$client->click(CollectionPage::$buttonCreateNext);
		$client->waitForElement(CollectionPage::$messageSuccessID, 30);
		$client->wait(1);
		$usePage = new CollectionPage();
		try
		{
			$client->waitForElementVisible($usePage->product(1), 30);
		} catch (\Exception $e)
		{
			$client->reloadPage();
			$client->waitForElementVisible($usePage->product(1), 30);
		}
		$client->waitForText($product['name'], 30);
		$client->waitForElement($usePage->product(1), 30);
		$client->click($usePage->product(1));
		$client->waitForElementVisible(CollectionPage::$nextButton, 30);
		$client->click(CollectionPage::$nextButton);
		$client->wait(0.5);
		$client->waitForElementVisible(CollectionPage::$endButton, 30);
		$client->click(CollectionPage::$endButton);
		$client->waitForElementVisible(CollectionPage::$saveButton, 30);
		$client->wait(1);
		$client->waitForElementVisible(CollectionPage::$saveCloseButton, 30);
		$client->wait(1);
		$client->click(CollectionPage::$saveCloseButton);
		$client->waitForElement(['link' => $collectionName], 30);
	}

	/**
	 * @param   string $collectionName      Collection name need to edit
	 * @param   string $collectionNameEdit  Collection name edit  name change
	 *
	 * @return  void
	 * @since   2.0.3
	 * @throws  \Exception
	 */
	public function edit($collectionName, $collectionNameEdit ,$status = 'Published')
	{
		$client = $this;
		$client->wantToTest('Collection edit in Frontend');
		$client->amOnPage(CollectionPage::$URL);
		$client->searchForItemInFrontend($collectionName, ['search field locator id' => CollectionPage::$searchForItemInFrontend]);
		$client->waitForElementVisible(CollectionPage::$deleteButton, 30);
		$client->checkAllResults();
		$client->click(CollectionPage::$editButton);
		$client->wait(1);
		$client->comment('I am redirected to the form');
		$client->waitForElementVisible(CollectionPage::$adminForm, 30);
		$client->reloadPage();
		$client->wait(1);
		$client->waitForElementVisible(CollectionPage::$nameID, 30);
		$client->fillField(CollectionPage::$nameID, $collectionNameEdit);
		$client->waitForText(CollectionPage::$status, 30);
		$client->wait(0.5);
		$client->selectOptionInRadioField(CollectionPage::$labelStatus, $status);
		$client->waitForElementVisible(CollectionPage::$saveCloseButton,30);
		$client->click(CollectionPage::$saveCloseButton);
		$client->waitForElementVisible(CollectionPage::$iconClear, 30);
		$client->click(CollectionPage::$iconClear);
		$client->searchForItemInFrontend($collectionNameEdit, ['search field locator id' => CollectionPage::$searchForItemInFrontend]);
		$client->waitForElementVisible(['link' => $collectionNameEdit], 30);
		$client->waitForText($collectionNameEdit, 30);
	}
	/**
	 * @param   string  $collectionName collection name
	 *
	 * @return  void
	 * @since   2.0.3
	 * @throws  \Exception
	 */
	public function delete($collectionName)
	{
		$client = $this;
		$client->wantToTest('Delete a collection in Frontend');
		$client->amGoingTo('Navigate to Collections page in frontend');
		$client->amOnPage(CollectionPage::$URL);
		$client->searchForItemInFrontend($collectionName, ['search field locator id' => CollectionPage::$searchForItemInFrontend]);
		$client->waitForElement(CollectionPage::$deleteButton, 30);
		$client->checkAllResults();
		$client->click(CollectionPage::$deleteButton);
		$client->click(CollectionPage::$iconClear);
		$client->dontSeeElement(['link' => $collectionName]);
	}

	/**
	 * @param $collectionName
	 * @param $product
	 * @param $scenario
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function editCollectionWithPrices($collectionName, $product = array(), $position)
	{
		$client = $this;
		$client->comment('Collection edit in Frontend');
		$client->amOnPage(CollectionPage::$URL);
		$client->searchForItemInFrontend($collectionName, ['search field locator id' => CollectionPage::$searchForItemInFrontend]);
		$client->waitForElementVisible(CollectionPage::$deleteButton, 30);
		$client->checkAllResults();
		$client->waitForElementVisible(CollectionPage::$editButton, 30);
		$client->click(CollectionPage::$editButton);
		$client->waitForElementVisible(CollectionPage::$adminForm, 30);
		$client->comment('I am redirected to the form');
		$client->waitForElement(CollectionPage::$adminForm, 30);
		$client->reloadPage();
		$client->waitForElementVisible(CollectionPage::$nameID, 30);
		$client->waitForElementVisible(CollectionPage::$prices, 30);
		$client->click(CollectionPage::$prices);
		$client->waitForElementVisible(CollectionPage::$saveAllPrices, 30);
		$usePage = new CollectionPage();
		$client->waitForElementVisible($usePage->productPrices($position), 30);
		$client->fillField($usePage->productPrices($position), $product['price']);
		$client->waitForElementVisible(CollectionPage::$saveAllPrices, 30);
		$client->click(CollectionPage::$saveAllPrices);
		try
		{
			$client->waitForText(CollectionPage::$messageUpdatePrices, 30, CollectionPage::$alertMessage);
		}catch (\Exception $exception)
		{
			$client->waitForElementVisible(CollectionPage::$saveAllPrices, 30);
			$client->click(CollectionPage::$saveAllPrices);
			$client->waitForText(CollectionPage::$messageUpdatePrices, 30, CollectionPage::$alertMessage);
		}
		$client->waitForElementVisible(CollectionPage::$saveCloseButton, 30);
		$client->click(CollectionPage::$saveCloseButton);
		try
		{
			$client->waitForText(CollectionPage::$messageSuccess, 30, CollectionPage::$alertMessage);

		}catch (\Exception $exception)
		{
			$client->waitForElementVisible(CollectionPage::$saveCloseButton, 30);
			$client->click(CollectionPage::$saveCloseButton);
			$client->waitForText(CollectionPage::$messageSuccess, 30, CollectionPage::$alertMessage);
		}
	}
}