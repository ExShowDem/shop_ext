<?php
namespace Step\Frontend;
use Step\Acceptance\redshopb2b;
use Page\Frontend\StatesPage as StatesPage;

class StateSteps extends redshopb2b
{
	/**
	 * @param $state
	 * @throws \Exception
	 */
	public function create( $state)
	{
		$I = $this;
		$I->amOnPage(StatesPage::$URL);
		$I->click(StatesPage::$newButton);
		$I->waitForElement(StatesPage::$adminForm,30);
		$I->fillField(StatesPage::$nameID, $state['name']);
		$I->fillField(StatesPage::$alpha2Code, $state['alpha2']);
		$I->fillField(StatesPage::$alpha3Code, $state['alpha3']);
		$I->selectOptionInChosen(StatesPage::$labelCountry, $state['country']);
		$I->wait(1);
		if ($state['company'] != null)
		{
			$I->waitForText(StatesPage::$labelCompany, 60);
			$I->comment('company not  null');
			if($state['company'] == 'Main Company')
			{
				$I->selectOptionInChosenjs(StatesPage::$labelCompany, $state['company']);

			}else{
				$compamy = '- ' . $state['company'];
				$I->comment($compamy);
				$I->selectOptionInChosenjs(StatesPage::$labelCompany, $compamy);
			}
		}
		$I->wait(1);
		$I->click(StatesPage::$saveButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->seeInField(StatesPage::$alpha2Code, $state['alpha2']);
		$I->wait(1);
		$I->click(StatesPage::$saveCloseButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->searchForItemInFrontend($state['name'], ['search field locator id' => StatesPage::$searchID]);
		$I->waitForElement(['link'=> $state['name']], 30);
	}
	
	/**
	 * @param $name
	 * @param $nameEdit
	 * @throws \Exception
	 */
	public function edit($name, $nameEdit)
	{
		$I = $this;
		$I->amOnPage(StatesPage::$URL);
		$I->searchForItemInFrontend($name, ['search field locator id' => StatesPage::$searchID]);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElement(['link'=> $name], 30);
		$I->click(['link' => $name]);
		$I->wait(1);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElement(StatesPage::$adminForm,30);
		$I->wait(1);
		$I->fillField(StatesPage::$nameID, $nameEdit);
		$I->wait(1);
		$I->click(StatesPage::$saveCloseButton);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElement(StatesPage::$messageSuccessID,30);
		$I->searchForItemInFrontend($nameEdit, ['search field locator id' => StatesPage::$searchID]);
		$I->wait(1);
		$I->waitForElement(['link'=> $nameEdit], 30);
		$I->searchForItemInFrontend($name, ['search field locator id' => StatesPage::$searchID]);
		$I->dontSeeElement(['link'=> $name]);
	}
	
	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function delete($name)
	{
		$I = $this;
		$I->amOnPage(StatesPage::$URL);
		$I->searchForItemInFrontend($name, ['search field locator id' => StatesPage::$searchID]);
		$I->waitForElement(['link' => $name], 30);
		$I->checkAllResults();
		$I->click(StatesPage::$deleteButton);
		$I->searchForItemInFrontend($name, ['search field locator id' => StatesPage::$searchID]);
		$I->dontSeeElement(['link' => $name]);
	}
	
	/**
	 * @param $state
	 * @throws \Exception
	 */
	public function checkMissing($state)
	{
		$I = $this;
		$I->amOnPage(StatesPage::$URL);
		$I->click(StatesPage::$newButton);
		$I->waitForElement(StatesPage::$adminForm,30);

		$I->comment('Check Missing Country');
		$I->fillField(StatesPage::$nameID, $state['name']);
		$I->fillField(StatesPage::$alpha2Code, $state['alpha2']);
		$I->fillField(StatesPage::$alpha3Code, $state['alpha3']);
		$I->click(StatesPage::$saveButton);
		try {
			$I->waitForText(StatesPage::$messageRequiredCountry, 10, StatesPage::$messageSuccessID);
		} catch (\Exception $e) {
			$I->click(StatesPage::$saveCloseButton);
			$I->waitForText(StatesPage::$messageRequiredCountry, 10, StatesPage::$messageSuccessID);
		}

		$I->comment('Check Missing Name');
		$I->clearViewField();
		$I->fillField(StatesPage::$alpha2Code, $state['alpha2']);
		$I->fillField(StatesPage::$alpha3Code, $state['alpha3']);
		$I->selectOptionInChosen(StatesPage::$labelCountry, $state['country']);
		$I->click(StatesPage::$saveButton);
		$I->waitForText(StatesPage::$messageRequiredName,30,StatesPage::$messageSuccessID);

		$I->comment('Check Missing alPha2');
		$I->clearViewField();
		$I->fillField(StatesPage::$nameID, $state['name']);
		$I->fillField(StatesPage::$alpha3Code, $state['alpha3']);
		$I->selectOptionInChosen(StatesPage::$labelCountry, $state['country']);
		$I->click(StatesPage::$saveButton);
		$I->waitForText(StatesPage::$messageRequiredCode2,30,StatesPage::$messageSuccessID);

		$I->comment('Check Missing alPha3');
		$I->clearViewField();
		$I->fillField(StatesPage::$nameID, $state['name']);
		$I->fillField(StatesPage::$alpha2Code, $state['alpha2']);
		$I->selectOptionInChosen(StatesPage::$labelCountry, $state['country']);
		$I->click(StatesPage::$saveButton);
		$I->waitForText(StatesPage::$messageRequiredCode3,30,StatesPage::$messageSuccessID);

		$I->comment('check alPha2 ready takend');
		$I->fillField(StatesPage::$nameID, $state['name']);
		$I->fillField(StatesPage::$alpha2Code, $state['alpha2']);
		$I->fillField(StatesPage::$alpha3Code, $state['alpha3']);
		$I->selectOptionInChosen(StatesPage::$labelCountry, $state['country']);

		if ($state['company'] != null)
		{
			$I->waitForText(StatesPage::$labelCompany, 60);
			$I->comment('company not  null');
			if($state['company'] == 'Main Company')
			{
				$I->selectOptionInChosenjs(StatesPage::$labelCompany, $state['company']);

			}
			else{

				$compamy = '- ' . $state['company'];
				$I->comment($compamy);
				$I->selectOptionInChosenjs(StatesPage::$labelCompany, $compamy);
			}
		}

		$I->click(StatesPage::$saveCloseButton);
		$I->waitForElement(StatesPage::$cancelButton,30);
		$I->click(StatesPage::$cancelButton);
		$I->searchForItemInFrontend($state['name'], ['search field locator id' => StatesPage::$searchID]);
		$I->dontSeeElement(['link' => $state['name']]);

		$I->comment('check alPha3 ready takend');
		$state['alpha2'] = 'ax';
		$I->click(StatesPage::$newButton);
		$I->waitForElement(StatesPage::$adminForm,30);
		$I->fillField(StatesPage::$nameID, $state['name']);
		$I->fillField(StatesPage::$alpha2Code, $state['alpha2']);
		$I->fillField(StatesPage::$alpha3Code, $state['alpha3']);
		$I->selectOptionInChosen(StatesPage::$labelCountry, $state['country']);

		if ($state['company'] != null)
		{
			$I->waitForText(StatesPage::$labelCompany, 60);
			$I->comment('company not  null');
			if($state['company'] == 'Main Company')
			{
				$I->selectOptionInChosenjs(StatesPage::$labelCompany, $state['company']);

			}
			else{

				$compamy = '- ' . $state['company'];
				$I->comment($compamy);
				$I->selectOptionInChosenjs(StatesPage::$labelCompany, $compamy);
			}

		}
		$I->click(StatesPage::$saveCloseButton);
		$I->waitForElement(StatesPage::$cancelButton,30);
		$I->click(StatesPage::$cancelButton);
		$I->searchForItemInFrontend($state['name'], ['search field locator id' => StatesPage::$searchID]);
		$I->dontSeeElement(['link' => $state['name']]);
	}

	public function clearViewField()
	{
		$I = $this;
		$I->fillField(StatesPage::$nameID,"");
		$I->fillField(StatesPage::$alpha2Code,"");
		$I->fillField(StatesPage::$alpha3Code,"");
	}
}