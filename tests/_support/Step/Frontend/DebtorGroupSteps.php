<?php

namespace Step\Frontend;
use Page\Frontend\DebtorGroupsPage as DebtorGroupsPage;
use Step\Acceptance\redshopb2b as redshopb2b;

class DebtorGroupSteps extends redshopb2b
{
	/**
	 * @param $name
	 * @param $nameEdit
	 * @param $function
	 * @throws \Exception
	 */
	public function edit($name, $nameEdit,$function)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Debtor page in frontend');
		$I->amOnPage(DebtorGroupsPage::$URLDebtorGroups);
		$I->searchForItemInFrontend($name, ['search field locator id' => DebtorGroupsPage::$searchPriceDebtorGroup]);
		$I->waitForElement(['link' => $name], 60);
		$I->waitForElement(['link' => $name], 30);
		$I->click(['link' => $name]);
		$I->wait(1);
		$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		$I->waitForElement(DebtorGroupsPage::$adminForm, 30);
		$I->fillField(DebtorGroupsPage::$nameID,$nameEdit);
		$I->waitForElementVisible(DebtorGroupsPage::$unpublishLabel, 30);
		$I->click(DebtorGroupsPage::$unpublishLabel);
		$I->wait(1);
		switch ($function)
		{
			case 'save':
				$I->waitForElementVisible(DebtorGroupsPage::$saveButton, 30);
				$I->click(DebtorGroupsPage::$saveButton);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
				$I->waitForText(DebtorGroupsPage::$debtorGroupEditSuccess, 30, DebtorGroupsPage::$systemContainer);
				$I->seeInField(DebtorGroupsPage::$nameID,$nameEdit);
				$I->click(DebtorGroupsPage::$closeButton);
				break;
			case 'save&close':
				$I->waitForElementVisible(DebtorGroupsPage::$saveCloseButton, 30);
				$I->click(DebtorGroupsPage::$saveCloseButton);
				$I->waitForText(DebtorGroupsPage::$debtorGroupEditSuccess, 30, DebtorGroupsPage::$systemContainer);
				$I->searchForItemInFrontend($nameEdit, ['search field locator id' => DebtorGroupsPage::$searchPriceDebtorGroup]);
				$I->waitForElement(['link' => $nameEdit], 60);
				$I->waitForElement(['link' => $nameEdit], 30);
				break;
			case 'save&new':
				$I->waitForElementVisible(DebtorGroupsPage::$saveNewButton, 30);
				$I->click(DebtorGroupsPage::$saveNewButton);
				$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
				$I->waitForText(DebtorGroupsPage::$debtorGroupEditSuccess, 30, DebtorGroupsPage::$systemContainer);
				$I->dontSeeInField(DebtorGroupsPage::$nameID,$nameEdit);
				$I->click(DebtorGroupsPage::$cancelButton);
				$I->searchForItemInFrontend($nameEdit, ['search field locator id' => DebtorGroupsPage::$searchPriceDebtorGroup]);
				$I->waitForElement(['link' => $nameEdit], 60);
				$I->waitForElement(['link' => $nameEdit], 30);
				break;
			case 'close':
				$I->waitForElementVisible(DebtorGroupsPage::$newButton, 30);
				$I->click(DebtorGroupsPage::$closeButton);
				$I->waitForElement(['link' => $name], 60);
				$I->waitForElement(['link' => $name], 30);
				break;
			default:
				break;
		}
	}
	
	/**
	 * @param $name
	 * @param $code
	 * @throws \Exception
	 */
	public function editDebtorGroupReadyCode($name, $code)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Debtor page in frontend');
		$I->amOnPage(DebtorGroupsPage::$URLDebtorGroups);
		$I->searchForItemInFrontend($name, ['search field locator id' => DebtorGroupsPage::$searchPriceDebtorGroup]);
		$I->waitForElement(['link' => $name], 60);
		$I->waitForElement(['link' => $name], 30);
		$I->click(['link' => $name]);
		$I->waitForElement(DebtorGroupsPage::$adminForm, 30);
		$I->fillField(DebtorGroupsPage::$codeId,$code);
		$I->waitForElementVisible(DebtorGroupsPage::$saveButton, 30);
		$I->click(DebtorGroupsPage::$saveButton);
		try
		{
			$I->waitForText(DebtorGroupsPage::$messageErrorSave, 10, DebtorGroupsPage::$alertHead);
		}catch (\Exception $exception)
		{
			$I->click(DebtorGroupsPage::$saveButton);
			$I->waitForText(DebtorGroupsPage::$messageErrorSave, 30, DebtorGroupsPage::$alertHead);
		}
	}
	
	/**
	 * @param $name
	 * @param $companies
	 * @param null $ownerCompany
	 * @param $code
	 * @throws \Exception
	 */
	public function createMissing($name, $companies, $ownerCompany = null, $code)
	{
		$I = $this;
		$I->amGoingTo('Navigate to Debtor page in frontend');
		$I->amOnPage(DebtorGroupsPage::$URLDebtorGroups);

		$userPage = new DebtorGroupsPage();
		$I->waitForElement(DebtorGroupsPage::$newButton, 30);
		$I->click(DebtorGroupsPage::$newButton);
		$I->comment('I am redirected to the form');
		$I->waitForElement(DebtorGroupsPage::$adminForm, 30);

		$I->comment('Create with missing Code');
		$I->fillField(DebtorGroupsPage::$nameID, $name);

		if (!is_null($ownerCompany))
		{
			$I->selectOptionInChosenjs(DebtorGroupsPage::$labelOwnerCompany, $ownerCompany);
		}
		$I->waitForElementVisible(DebtorGroupsPage::$customerInput,30);
//		$I->click(DebtorGroupsPage::$customerInput);
		$I->wait(0.5);
		$I->fillField(DebtorGroupsPage::$customerInput,$companies);
		$I->wait(0.5);
		$I->pressKey(DebtorGroupsPage::$customerInput,\Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->fillField(DebtorGroupsPage::$codeId,"");
		$I->click(DebtorGroupsPage::$saveButton);
		$I->waitForElement(DebtorGroupsPage::$alertHead,30);
		$I->waitForText(DebtorGroupsPage::$messageMissingCode, '30', DebtorGroupsPage::$systemContainer);

		$I->comment('Create missing name ');

		$I->fillField(DebtorGroupsPage::$nameID, "");

		if (!is_null($ownerCompany))
		{
			$I->selectOptionInChosenjs(DebtorGroupsPage::$labelOwnerCompany, $ownerCompany);
		}

		$I->wait(1);
		$I->fillField(DebtorGroupsPage::$codeId, $code);
		$I->click(DebtorGroupsPage::$saveButton);
		$I->waitForElement(DebtorGroupsPage::$systemContainer,30);
		$I->waitForText(DebtorGroupsPage::$messageMissingName, '30', DebtorGroupsPage::$systemContainer);
	}
}