<?php
namespace Step\Frontend;

/**
 * @package     Aesir.E-Commerce
 * @subpackage  Steps Class
 * @copyright   Copyright (C) 2012 - 2019 Aesir. E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\redshopb2b;
use Page\Frontend\HolidaysPage as HolidaysPage;

/**
 * Class HolidaySteps
 * @package Step\Frontend
 * @since 2.5.1
 */
class HolidaySteps extends redshopb2b
{
	/**
	 * @param array $holiday
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function createHolidays($holiday = array())
	{
		$I = $this;
		$I->amOnPage(HolidaysPage::$URL);
		$I->waitForElementVisible(HolidaysPage::$newButton, 30);
		$I->click(HolidaysPage::$newButton);
		$I->waitForElementVisible(HolidaysPage::$nameID, 30);
		$I->fillField(HolidaysPage::$nameID, $holiday['name']);
		$I->waitForElementVisible(HolidaysPage::$day, 30);
		$I->fillField(HolidaysPage::$day, $holiday['day']);
		$I->waitForElementVisible(HolidaysPage::$month, 30);
		$I->fillField(HolidaysPage::$month, $holiday['month']);
		$I->waitForElementVisible(HolidaysPage::$year, 30);
		$I->fillField(HolidaysPage::$year, $holiday['year']);
		$I->selectOptionInChosen(HolidaysPage::$country, $holiday['country']);
		$I->waitForElementVisible(HolidaysPage::$saveButton, 30);
		$I->click(HolidaysPage::$saveButton);
		$I->seeInField(HolidaysPage::$nameID, $holiday['name']);
		$I->waitForElementVisible(HolidaysPage::$saveCloseButton, 30);
		$I->click(HolidaysPage::$saveCloseButton);
		$I->searchForItemInFrontend($holiday['name'], ['search field locator id' => HolidaysPage::$searchID]);
		$I->waitForElementVisible(['link'=> $holiday['name']], 30);
	}

	/**
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function createHolidaysMissingName()
	{
		$I = $this;
		$I->amOnPage(HolidaysPage::$URL);
		$I->waitForElementVisible(HolidaysPage::$newButton, 30);
		$I->click(HolidaysPage::$newButton);
		$I->waitForElementVisible(HolidaysPage::$nameID, 30);
		$I->fillField(HolidaysPage::$nameID, '');
		$I->waitForElementVisible(HolidaysPage::$saveButton, 30);
		$I->click(HolidaysPage::$saveButton);
		$I->waitForText(HolidaysPage::$missingNameRequired, 30, HolidaysPage::$messageSuccessID);
	}

	/**
	 * @param array $holiday
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function createHolidaysWrongData($holiday = array())
	{
		$I = $this;
		$I->amOnPage(HolidaysPage::$URL);
		$I->waitForElementVisible(HolidaysPage::$newButton, 30);
		$I->click(HolidaysPage::$newButton);
		$I->comment('Check create holiday with wrong days');
		$I->waitForElementVisible(HolidaysPage::$nameID, 30);
		$I->fillField(HolidaysPage::$nameID, $holiday['name']);
		$I->waitForElementVisible(HolidaysPage::$day, 30);
		$I->fillField(HolidaysPage::$day, $holiday['day']);
		$I->waitForElementVisible(HolidaysPage::$saveButton, 30);
		$I->click(HolidaysPage::$saveButton);
		$I->waitForText(HolidaysPage::$warningDay, 30);
		$I->comment('Check create holiday with wrong months');
		$I->waitForElementVisible(HolidaysPage::$month, 30);
		$I->fillField(HolidaysPage::$month, $holiday['month']);
		$I->waitForElementVisible(HolidaysPage::$saveButton, 30);
		$I->click(HolidaysPage::$saveButton);
		$I->waitForText(HolidaysPage::$warningMonth, 30);
	}

	/**
	 * @param array $holiday
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function editHolidays($holiday = array())
	{
		$I = $this;
		$I->amOnPage(HolidaysPage::$URL);
		$I->searchForItemInFrontend($holiday['name'], ['search field locator id' => HolidaysPage::$searchID]);
		$I->waitForText($holiday['name'], 30);
		$I->checkAllResults();
		$I->waitForElementVisible(HolidaysPage::$editButton, 30);
		$I->click(HolidaysPage::$editButton);
		$I->waitForElementVisible(HolidaysPage::$year, 30);
		$I->fillField(HolidaysPage::$year, $holiday['year']);
		$I->selectOptionInChosen(HolidaysPage::$country, $holiday['country']);
		$I->waitForElementVisible(HolidaysPage::$saveButton, 30);
		$I->click(HolidaysPage::$saveButton);
		$I->seeInField(HolidaysPage::$nameID, $holiday['name']);
		$I->waitForElementVisible(HolidaysPage::$saveCloseButton, 30);
		$I->click(HolidaysPage::$saveCloseButton);
		$I->searchForItemInFrontend($holiday['name'], ['search field locator id' => HolidaysPage::$searchID]);
		$I->waitForElementVisible(['link'=> $holiday['name']], 30);
	}

	/**
	 * @param array $holiday
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function deleteHolidays($holiday = array())
	{
		$I = $this;
		$I->amOnPage(HolidaysPage::$URL);
		$I->searchForItemInFrontend($holiday['name'], ['search field locator id' => HolidaysPage::$searchID]);
		$I->waitForText($holiday['name'], 30);
		$I->checkAllResults();
		$I->waitForElementVisible(HolidaysPage::$deleteButton, 30);
		$I->click(HolidaysPage::$deleteButton);
		$I->waitForElementVisible(HolidaysPage::$messageSuccessID, 30);
		$I->dontSee($holiday['name'], null);
	}
}