<?php
/**
 * @package     Aesir-ec
 * @subpackage  Step Address
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

namespace Step\Frontend\Stockrooms;
use Page\Frontend\Stockrooms\StockroomsPage as StockroomsPage;
use \Step\Acceptance\redshopb2b as redshopb2b;

/**
 * Class Stockrooms
 *
 * @package Step\Frontend\Stockrooms
 * @since 2.6.0
 */
class Stockrooms extends redshopb2b
{
	/**
	 * @param array $stockRooms
	 * @param       $function
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function createStockrooms($stockRooms = array(), $function)
	{
		$I = $this;
		$I->amGoingTo('Create new stockroom in frontend');
		$I->amOnPage(StockroomsPage::$url);
		$I->waitForElementVisible(StockroomsPage::$newButton, 30);
		$I->click(StockroomsPage::$newButton);

		$I->comment('Input the name of stockrooms');
		$I->waitForElementVisible(StockroomsPage::$nameID, 30);
		$I->fillField(StockroomsPage::$nameID, $stockRooms['name']);

		$I->comment('Select the company for stockrooms');
		$I->waitForText(StockroomsPage::$company, 60);
		$I->waitForElementVisible(StockroomsPage::$companyStockroomsId, 30);
		$I->selectOptionInChosenXpath(StockroomsPage::$companyStockroomsJform, $stockRooms['company']);

		$I->comment('Input the min delivery time of stockrooms');
		$I->waitForElementVisible(StockroomsPage::$minDeliveryTime, 30);
		$I->fillField(StockroomsPage::$minDeliveryTime, $stockRooms['minDeliveryTime']);

		$I->comment('Input the max delivery time of stockrooms');
		$I->waitForElementVisible(StockroomsPage::$maxDeliveryTime, 30);
		$I->fillField(StockroomsPage::$maxDeliveryTime, $stockRooms['maxDeliveryTime']);

		$I->comment('Input the lower level of stockrooms');
		$I->waitForElementVisible(StockroomsPage::$lowerLevel, 30);
		$I->fillField(StockroomsPage::$lowerLevel, $stockRooms['lowerLevel']);

		$I->comment('Input the upper level of stockrooms');
		$I->waitForElementVisible(StockroomsPage::$upperLevel, 30);
		$I->fillField(StockroomsPage::$upperLevel, $stockRooms['upperLevel']);

		$I->waitForElementVisible(StockroomsPage::$saveButton, 30);
		$I->scrollTo(StockroomsPage::$saveButton);
		switch ($function)
		{
			case 'save':
				$I->waitForElementVisible(StockroomsPage::$saveButton, 30);
				$I->click(StockroomsPage::$saveButton);
				$I->waitForText(StockroomsPage::$saveSuccess, 30, StockroomsPage::$messageSuccessID);
				$I->waitForElementVisible(StockroomsPage::$saveCloseButton, 30);
				$I->click(StockroomsPage::$saveCloseButton);
				$I->waitForElementVisible(['link' => $stockRooms['name']], 30);
				break;
			case 'save&close':
				$I->waitForElementVisible(StockroomsPage::$saveCloseButton, 30);
				$I->click(StockroomsPage::$saveCloseButton);
				$I->waitForText(StockroomsPage::$saveSuccess, 30, StockroomsPage::$messageSuccessID);
				$I->waitForElementVisible(['link' => $stockRooms['name']], 30);
				break;
			case 'save&new':
				$I->waitForElementVisible(StockroomsPage::$saveNewButton, 30);
				$I->click(StockroomsPage::$saveNewButton);
				$I->waitForElementVisible(StockroomsPage::$adminForm, 30);
				$I->waitForElementVisible(StockroomsPage::$cancelButton, 30);
				$I->click(StockroomsPage::$cancelButton);
				break;
			case 'cancel':
				$I->waitForElementVisible(StockroomsPage::$cancelButton, 30);
				$I->click(StockroomsPage::$cancelButton);
				break;
			default:
				break;
		}
	}

	/**
	 * @param $stockRoomsEdit
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function editStockrooms($stockRoomsEdit = array())
	{
		$I = $this;
		$I->amGoingTo('Edit stockrooms in frontend');
		$I->amOnPage(StockroomsPage::$url);
		$I->searchForItemInFrontend($stockRoomsEdit['name'],['search field locator id' => StockroomsPage::$searchStockrooms]);
		$I->waitForElementVisible(['link' => $stockRoomsEdit['name']], 30);
		$I->click(['link' => $stockRoomsEdit['name']]);
		$I->waitForElementVisible(StockroomsPage::$adminForm, 30);

		$I->comment('Edit name of stockrooms in frontend');
		$I->waitForElementVisible(StockroomsPage::$nameID, 30);
		$I->fillField(StockroomsPage::$nameID, $stockRoomsEdit['nameEdit']);

		$I->comment('Change company of stockrooms in frontend');
		$I->waitForText(StockroomsPage::$company, 60);
		$I->waitForElementVisible(StockroomsPage::$companyStockroomsId, 30);
		$I->selectOptionInChosenXpath(StockroomsPage::$companyStockroomsJform, $stockRoomsEdit['company']);

		$I->waitForElementVisible(StockroomsPage::$saveButton, 30);
		$I->scrollTo(StockroomsPage::$saveButton);
		$I->click(StockroomsPage::$saveButton);
		$I->waitForText(StockroomsPage::$saveItemSuccess, 30, StockroomsPage::$messageSuccessID);
		$I->waitForElementVisible(StockroomsPage::$saveCloseButton, 30);
		$I->click(StockroomsPage::$saveCloseButton);
		$I->waitForElementVisible(StockroomsPage::$iconClear, 30);
		$I->click(StockroomsPage::$iconClear);
		$I->waitForElementVisible(['link' => $stockRoomsEdit['nameEdit']], 30);
	}

	/**
	 * @param $stockRoomsMissingName
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function createMissingName($stockRoomsMissingName = array())
	{
		$I = $this;
		$I->amGoingTo('Create new stockroom with missing name in frontend');
		$I->amOnPage(StockroomsPage::$url);
		$I->waitForElementVisible(StockroomsPage::$newButton, 30);
		$I->click(StockroomsPage::$newButton);
		$I->waitForText(StockroomsPage::$company, 60);
		$I->waitForElementVisible(StockroomsPage::$companyStockroomsId, 30);
		$I->selectOptionInChosenXpath(StockroomsPage::$companyStockroomsJform, $stockRoomsMissingName['company']);
		$I->waitForElementVisible(StockroomsPage::$minDeliveryTime, 30);
		$I->fillField(StockroomsPage::$minDeliveryTime, $stockRoomsMissingName['minDeliveryTime']);
		$I->waitForElementVisible(StockroomsPage::$maxDeliveryTime, 30);
		$I->fillField(StockroomsPage::$maxDeliveryTime, $stockRoomsMissingName['maxDeliveryTime']);
		$I->waitForElementVisible(StockroomsPage::$lowerLevel, 30);
		$I->fillField(StockroomsPage::$lowerLevel, $stockRoomsMissingName['lowerLevel']);
		$I->waitForElementVisible(StockroomsPage::$upperLevel, 30);
		$I->fillField(StockroomsPage::$upperLevel, $stockRoomsMissingName['upperLevel']);
		$I->waitForElementVisible(StockroomsPage::$saveButton, 30);
		$I->scrollTo(StockroomsPage::$saveButton);
		$I->click(StockroomsPage::$saveButton);
		$I->waitForText(StockroomsPage::$missingNameRequired, 30);
	}

	/**
	 * @param $stockRoomsMissingDeliveryTime
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function createMissingMinDeliveryTime($stockRoomsMissingMinDeliveryTime = array())
	{
		$I = $this;
		$I->amGoingTo('Create new stockroom with missing min delivery time in frontend');
		$I->amOnPage(StockroomsPage::$url);
		$I->waitForElementVisible(StockroomsPage::$newButton, 30);
		$I->click(StockroomsPage::$newButton);
		$I->waitForElementVisible(StockroomsPage::$nameID, 30);
		$I->fillField(StockroomsPage::$nameID, $stockRoomsMissingMinDeliveryTime['name']);
		$I->waitForText(StockroomsPage::$company, 60);
		$I->waitForElementVisible(StockroomsPage::$companyStockroomsId, 30);
		$I->selectOptionInChosenXpath(StockroomsPage::$companyStockroomsJform, $stockRoomsMissingMinDeliveryTime['company']);
		$I->waitForElementVisible(StockroomsPage::$maxDeliveryTime, 30);
		$I->fillField(StockroomsPage::$maxDeliveryTime, $stockRoomsMissingMinDeliveryTime['maxDeliveryTime']);
		$I->waitForElementVisible(StockroomsPage::$lowerLevel, 30);
		$I->fillField(StockroomsPage::$lowerLevel, $stockRoomsMissingMinDeliveryTime['lowerLevel']);
		$I->waitForElementVisible(StockroomsPage::$upperLevel, 30);
		$I->fillField(StockroomsPage::$upperLevel, $stockRoomsMissingMinDeliveryTime['upperLevel']);
		$I->waitForElementVisible(StockroomsPage::$saveButton, 30);
		$I->scrollTo(StockroomsPage::$saveButton);
		$I->click(StockroomsPage::$saveButton);
		$I->waitForText(StockroomsPage::$saveSuccess, 30, StockroomsPage::$messageSuccessID);
		$I->waitForElementVisible(StockroomsPage::$saveCloseButton, 30);
		$I->click(StockroomsPage::$saveCloseButton);
		$I->waitForElementVisible(StockroomsPage::$iconClear, 30);
		$I->click(StockroomsPage::$iconClear);
		$I->waitForElementVisible(['link' => $stockRoomsMissingMinDeliveryTime['name']], 30);
	}

	/**
	 * @param $stockRoomsMissingLowerLevel
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function createMissingLowerLevel($stockRoomsMissingLowerLevel = array())
	{
		$I = $this;
		$I->amGoingTo('Create new stockroom with missing lower level in frontend');
		$I->amOnPage(StockroomsPage::$url);
		$I->waitForElementVisible(StockroomsPage::$newButton, 30);
		$I->click(StockroomsPage::$newButton);
		$I->waitForElementVisible(StockroomsPage::$nameID, 30);
		$I->fillField(StockroomsPage::$nameID, $stockRoomsMissingLowerLevel['name']);
		$I->waitForText(StockroomsPage::$company, 60);
		$I->waitForElementVisible(StockroomsPage::$companyStockroomsId, 30);
		$I->selectOptionInChosenXpath(StockroomsPage::$companyStockroomsJform, $stockRoomsMissingLowerLevel['company']);
		$I->waitForElementVisible(StockroomsPage::$minDeliveryTime, 30);
		$I->fillField(StockroomsPage::$minDeliveryTime, $stockRoomsMissingLowerLevel['minDeliveryTime']);
		$I->waitForElementVisible(StockroomsPage::$maxDeliveryTime, 30);
		$I->fillField(StockroomsPage::$maxDeliveryTime, $stockRoomsMissingLowerLevel['maxDeliveryTime']);
		$I->waitForElementVisible(StockroomsPage::$upperLevel, 30);
		$I->fillField(StockroomsPage::$upperLevel, $stockRoomsMissingLowerLevel['upperLevel']);
		$I->waitForElementVisible(StockroomsPage::$saveButton, 30);
		$I->scrollTo(StockroomsPage::$saveButton);
		$I->click(StockroomsPage::$saveButton);
		$I->waitForText(StockroomsPage::$saveSuccess, 30, StockroomsPage::$messageSuccessID);
		$I->waitForElementVisible(StockroomsPage::$saveCloseButton, 30);
		$I->click(StockroomsPage::$saveCloseButton);
		$I->waitForElementVisible(StockroomsPage::$iconClear, 30);
		$I->click(StockroomsPage::$iconClear);
		$I->waitForElementVisible(['link' => $stockRoomsMissingLowerLevel['name']], 30);
	}

	/**
	 * @param array $stockRooms
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function createStockroomsWithMaxSmallerThanMinDelivery($stockRooms = array())
	{
		$I = $this;
		$I->amGoingTo('Check case input Max Delivery Time smaller than Min Delivery Time');
		$I->amOnPage(StockroomsPage::$url);
		$I->waitForElementVisible(StockroomsPage::$newButton, 30);
		$I->click(StockroomsPage::$newButton);
		$I->waitForElementVisible(StockroomsPage::$nameID, 30);
		$I->fillField(StockroomsPage::$nameID, $stockRooms['name']);
		$I->waitForElementVisible(StockroomsPage::$minDeliveryTime, 30);
		$I->fillField(StockroomsPage::$minDeliveryTime, $stockRooms['minDeliveryTime']);
		$I->waitForElementVisible(StockroomsPage::$maxDeliveryTime, 30);
		$I->fillField(StockroomsPage::$maxDeliveryTime, $stockRooms['maxDeliveryTime']);
		$I->waitForElementVisible(StockroomsPage::$lowerLevel, 30);
		$I->fillField(StockroomsPage::$lowerLevel, $stockRooms['lowerLevel']);
		$I->waitForElementVisible(StockroomsPage::$upperLevel, 30);
		$I->fillField(StockroomsPage::$upperLevel, $stockRooms['upperLevel']);
		$I->waitForElementVisible(StockroomsPage::$saveButton, 30);
		$I->click(StockroomsPage::$saveButton);
		$I->waitForText(StockroomsPage::$messageMinSmallerThanMaxDeliveryTime, 30);
	}

	/**
	 * @param array $stockRooms
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function createStockroomsWithUpperSmallerThanLowerLevel($stockRooms = array())
	{
		$I = $this;
		$I->amGoingTo('Check case input Upper level smaller than Lower level');
		$I->amOnPage(StockroomsPage::$url);
		$I->waitForElementVisible(StockroomsPage::$newButton, 30);
		$I->click(StockroomsPage::$newButton);
		$I->waitForElementVisible(StockroomsPage::$nameID, 30);
		$I->fillField(StockroomsPage::$nameID, $stockRooms['name']);
		$I->waitForElementVisible(StockroomsPage::$minDeliveryTime, 30);
		$I->fillField(StockroomsPage::$minDeliveryTime, $stockRooms['minDeliveryTime']);
		$I->waitForElementVisible(StockroomsPage::$maxDeliveryTime, 30);
		$I->fillField(StockroomsPage::$maxDeliveryTime, $stockRooms['maxDeliveryTime']);
		$I->waitForElementVisible(StockroomsPage::$lowerLevel, 30);
		$I->fillField(StockroomsPage::$lowerLevel, $stockRooms['lowerLevel']);
		$I->waitForElementVisible(StockroomsPage::$upperLevel, 30);
		$I->fillField(StockroomsPage::$upperLevel, $stockRooms['upperLevel']);
		$I->waitForElementVisible(StockroomsPage::$saveButton, 30);
		$I->click(StockroomsPage::$saveButton);
		$I->waitForText(StockroomsPage::$messageLowerSmallerThanUpperLevel, 30);
	}

	/**
	 * @param $name
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function deleteStockrooms($name)
	{
		$I = $this;
		$I->amGoingTo('Edit stockrooms in frontend');
		$I->amOnPage(StockroomsPage::$url);
		$I->searchForItemInFrontend($name, ['search field locator id' => StockroomsPage::$searchStockrooms]);
		$I->waitForElementVisible(['link' => $name], 30);
		$I->checkAllResults();
		$I->waitForElementVisible(StockroomsPage::$deleteButton, 30);
		$I->click(StockroomsPage::$deleteButton);
		$I->waitForText(StockroomsPage::$messageDeleteSuccess, 30, StockroomsPage::$messageSuccessID);
		$I->waitForElementVisible(StockroomsPage::$iconClear, 30);
		$I->click(StockroomsPage::$iconClear);
		$I->searchForItemInFrontend($name, ['search field locator id' => StockroomsPage::$searchStockrooms]);
		$I->dontSeeElement(['link' => $name]);
	}

	/**
	 * @param $name
	 * @param $address
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function createStockroomsAddress($name, $address)
	{
		$I = $this;
		$I->amGoingTo('Edit stockrooms in frontend with address');
		$I->amOnPage(StockroomsPage::$url);
		$I->searchForItemInFrontend($name,['search field locator id' => StockroomsPage::$searchStockrooms]);
		$I->waitForElementVisible(['link' => $name], 30);
		$I->click(['link' => $name]);
		$I->waitForElementVisible(StockroomsPage::$adminForm, 30);
		$I->waitForElementVisible(StockroomsPage::$address, 30);
		$I->click(StockroomsPage::$address);
		$I->waitForElementVisible(StockroomsPage::$addressName, 30);
		$I->fillField(StockroomsPage::$addressName, $address['name']);
		$I->waitForElementVisible(StockroomsPage::$addressName2, 30);
		$I->fillField(StockroomsPage::$addressName2, $address['name_second']);
		$I->waitForElementVisible(StockroomsPage::$addressAddress, 30);
		$I->fillField(StockroomsPage::$addressAddress, $address['address']);
		$I->waitForElementVisible(StockroomsPage::$addressSecondLine, 30);
		$I->fillField(StockroomsPage::$addressSecondLine, $address['address_second']);
		$I->waitForElementVisible(StockroomsPage::$addressZipCode, 30);
		$I->fillField(StockroomsPage::$addressZipCode, $address['code']);
		$I->waitForElementVisible(StockroomsPage::$addressCity, 30);
		$I->fillField(StockroomsPage::$addressCity, $address['city']);
		$I->selectOptionInChosenjs(StockroomsPage::$addressCountry, $address['country']);
		$I->waitForElementVisible(StockroomsPage::$saveButton, 30);
		$I->scrollTo(StockroomsPage::$saveButton);
		$I->click(StockroomsPage::$saveButton);
		$I->waitForText(StockroomsPage::$saveItemSuccess, 30, StockroomsPage::$messageSuccessID);
		$I->waitForElementVisible(StockroomsPage::$saveCloseButton, 30);
		$I->click(StockroomsPage::$saveCloseButton);
	}
}