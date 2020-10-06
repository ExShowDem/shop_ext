<?php

namespace Step\Frontend;
use Page\Frontend\CountryPage;
use Step\Acceptance\redshopb2b;

class CountrySteps extends redshopb2b
{
	/**
	 * @param array $country
	 *
	 * @return void
	 * @since 2.3.0
	 * @throws \Exception
	 */
	public function createCountry($country = array())
	{
		$I = $this;
		$I->amOnPage(CountryPage::$URL);
		$I->click(CountryPage::$newButton);
		$I->waitForElement(CountryPage::$adminForm, 30);
		$I->fillField(CountryPage::$nameID, $country['name']);
		$I->fillField(CountryPage::$code2Id, $country['code2']);
		$I->fillField(CountryPage::$code3Id, $country['code3']);
		$I->fillField(CountryPage::$numberCodeId, $country['numberCode']);
		$I->selectOptionInRadioField(CountryPage::$labelEurozone, $country['euro']);
		$I->selectOptionInChosen(CountryPage::$company, $country['company']);
		$I->click(CountryPage::$saveButton);
		$I->waitForElement(CountryPage::$messageSuccessID, 30);
		$I->click(CountryPage::$saveCloseButton);
		$I->searchForItemInFrontend($country['code2'], ['search field locator id' => CountryPage::$searchCountry]);
		$I->waitForText($country['code2'], 30);
	}

	/**
	 * @param array $country
	 *
	 * @return void
	 * @since 2.3.0
	 * @throws \Exception
	 */
	public function editCountry($country = array())
	{
		$I = $this;
		$I->amOnPage(CountryPage::$URL);
		$I->searchForItemInFrontend($country['name'], ['search field locator id' => CountryPage::$searchCountry]);
		$I->waitForText($country['code2'], 30);
		$I->checkAllResults();
		$I->click(CountryPage::$editButton);
		$I->waitForElement(CountryPage::$adminForm, 30);
		$I->seeInField(CountryPage::$code2Id, $country['code2']);
		$I->fillField(CountryPage::$code2Id, $country['code3']);
		$I->click(CountryPage::$saveButton);
		$I->click(CountryPage::$saveCloseButton);
		$I->searchForItemInFrontend($country['code3'], ['search field locator id' => CountryPage::$searchCountry]);
		$I->dontSee($country['code2']);
		$I->waitForText($country['code3'], 30);
	}

	/**
	 * @param array $country
	 *
	 * @return void
	 * @since 2.3.0
	 * @throws \Exception
	 */
	public function delete($country = array())
	{
		$I = $this;
		$I->amOnPage(CountryPage::$URL);
		$I->searchForItemInFrontend($country['name'], ['search field locator id' => CountryPage::$searchCountry]);
		$I->waitForText($country['name'], 30);
		$I->waitForText($country['code2'], 30);
		$I->checkAllResults();
		$I->click(CountryPage::$deleteButton);
		$I->waitForElement(CountryPage::$messageSuccessID, 30);
		$I->dontSee($country['name'], null);
	}

	/**
	 * @param array $country
	 *
	 * @return void
	 * @since 2.3.0
	 * @throws \Exception
	 */
	public function countryMissing($country = array())
	{
		$I = $this;
		$I->amOnPage(CountryPage::$URL);
		$I->click(CountryPage::$newButton);
		$I->waitForElement(CountryPage::$adminForm, 30);
		$I->wantTo('Check missing code2 and code3');
		$I->click(CountryPage::$saveButton);
		try
		{
			$I->waitForText(CountryPage::$messageMissingCode2, 5);
		}catch (\Exception $exception)
		{
			$I->click(CountryPage::$saveButton);
			$I->waitForText(CountryPage::$messageMissingCode2, 5);
		}
		$I->waitForText(CountryPage::$messageMissingCode3, 30);

		$I->wantTo('Check missing code3');
		$I->fillField(CountryPage::$code2Id, $country['code2']);
		$I->click(CountryPage::$saveButton);
		$I->waitForText(CountryPage::$messageMissingCode3, 30);

		$I->wantTo('Check missing code 2');
		$I->fillField(CountryPage::$code2Id,'');
		$I->fillField(CountryPage::$code3Id, $country['code3']);
		$I->click(CountryPage::$saveButton);
		$I->waitForText(CountryPage::$messageMissingCode2, 30);
	}

	/**
	 * @param array $country
	 *
	 * @return void
	 * @since 2.3.0
	 * @throws \Exception
	 */
	public function countryAlreadyCode($country = array())
	{
		$I = $this;
		$I->amOnPage(CountryPage::$URL);
		$I->click(CountryPage::$newButton);
		$I->waitForElement(CountryPage::$adminForm, 30);
		$I->fillField(CountryPage::$nameID, $country['name']);
		$I->fillField(CountryPage::$code2Id, $country['code2']);
		$I->fillField(CountryPage::$code3Id, $country['code3']);
		$I->fillField(CountryPage::$numberCodeId, $country['numberCode']);
		$I->comment('Test at already');
		$I->selectOptionInRadioField(CountryPage::$labelEurozone, $country['euro']);
		$I->selectOptionInChosen(CountryPage::$company, $country['company']);
		$I->wantTo('Check already code 2');
		$I->click(CountryPage::$saveCloseButton);
		$usePage = new CountryPage();
		$I->waitForText($usePage->messageAlreadyAlpha2($country['code2']), 30);
		$I->wantTo('Check already code 3');
		$I->fillField(CountryPage::$code2Id, $country['code3']);
		$I->click(CountryPage::$saveButton);
		$I->waitForText($usePage->messageAlreadyAlpha3($country['code3']), 30);
		$I->wantTo('Check already with Number erric');
		$country['name'] = 'checking name';
		$country['code3'] = 'nhb';
		$I->fillField(CountryPage::$nameID, $country['name']);
		$I->fillField(CountryPage::$code3Id, $country['name']);
		$I->waitForElementVisible(CountryPage::$saveButton, 30);
		$I->click(CountryPage::$saveButton);
		try
		{
			$I->waitForText($usePage->messageAlreadyNumberric($country['numberCode']), 10);
		}catch (\Exception $exception)
		{
			$I->click(CountryPage::$saveButton);
			$I->waitForText($usePage->messageAlreadyNumberric($country['numberCode']), 10);
		}
	}
}