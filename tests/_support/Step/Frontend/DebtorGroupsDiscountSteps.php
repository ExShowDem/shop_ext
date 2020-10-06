<?php
/**
 * @package  AcceptanceTester
 *
 * @since    2.1
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */

namespace Step\Frontend;
use Page\Frontend\DebtorGroupsDiscountPage as DebtorGroupsDiscountPage;
use Page\Frontend\DebtorGroupsPage as DebtorGroupsPage;
use Step\Acceptance\redshopb2b;
class DebtorGroupsDiscountSteps extends redshopb2b
{
	/**
	 * @param $nameDebtorGroupsDiscountSteps
	 * @param $code
	 * @param $ownerCompany
	 * @param $companies
	 * @throws \Exception
	 */
	public function create($name, $code, $ownerCompany, $companies)
	{
		$I = $this;
		$I->amOnPage(DebtorGroupsDiscountPage::$URL);
		$I->click(DebtorGroupsDiscountPage::$newButton);
		$I->waitForElement(DebtorGroupsDiscountPage::$adminForm, 30);
		$I->fillField(DebtorGroupsDiscountPage::$nameID, $name);
		$I->fillField(DebtorGroupsDiscountPage::$codeID, $code);
		if ($ownerCompany != 'Main')
		{
			$ownerCompany = '- ' . '('.$ownerCompany.') '.$ownerCompany;
		}
		else
		{
			$ownerCompany = '(main) Main Company';
		}
		$I->selectOptionInChosen(DebtorGroupsDiscountPage::$labelCompany, $ownerCompany);

		$I->waitForElementVisible(DebtorGroupsPage::$customerInput, 30);
		$I->click(DebtorGroupsPage::$customerInput);

		// @todo: the following code could be in a private UX function, because is related to interacting with the specific Javascript field

		foreach ($companies as $company)
		{
			$I->comment($company);
			$company = '- ' . '('.$company.') '.$company;
			$I->waitForElementVisible(DebtorGroupsPage::$customerInput);
			$I->click(DebtorGroupsPage::$customerInput);

			$I->waitForElement(DebtorGroupsPage::$customerInput, 30);
			$I->wait(1);
			$I->waitForElementVisible(DebtorGroupsPage::$customerInput, 30);
			$I->click(DebtorGroupsPage::$customerInput);
			$I->fillField(DebtorGroupsPage::$customerInput,$company);
			$I->pressKey(DebtorGroupsPage::$customerInput,\Facebook\WebDriver\WebDriverKeys::ENTER);
		}

		$I->click(DebtorGroupsDiscountPage::$saveCloseButton);
		$I->searchForItemInFrontend($name, ['search field locator id' => DebtorGroupsDiscountPage::$searchId]);
	}

	/**
	 * @param $name
	 * @param $nameEdit
	 * @param $companies
	 * @param $function
	 * @throws \Exception
	 */
	public function editName($name, $nameEdit, $companies, $function)
	{
		$I = $this;
		$I->amOnPage(DebtorGroupsDiscountPage::$URL);
		$I->searchForItemInFrontend($name, ['search field locator id' => DebtorGroupsDiscountPage::$searchId]);
		$I->click(['link' => $name]);
		$I->waitForElement(DebtorGroupsDiscountPage::$adminForm, 30);
		$I->seeInField(DebtorGroupsDiscountPage::$nameID, $name);
		$I->fillField(DebtorGroupsDiscountPage::$nameID, $nameEdit);
		
		foreach ($companies as $company)
		{
			$I->comment($company);
			$company = '- ' . '('.$company.') '.$company;
			$I->waitForText($company, 30);
		}

		switch ($function)
		{
			case 'save':
				$I->click(DebtorGroupsDiscountPage::$saveButton);
				$I->seeInField(DebtorGroupsDiscountPage::$nameID, $nameEdit);
				$I->click(DebtorGroupsDiscountPage::$closeButton);
				break;
			case 'save&close':
				$I->click(DebtorGroupsDiscountPage::$saveCloseButton);
				break;
			case 'save&new':
				$I->click(DebtorGroupsDiscountPage::$saveNewButton);
				$I->waitForElement(DebtorGroupsDiscountPage::$cancelButton, 30);
				$I->click(DebtorGroupsDiscountPage::$cancelButton);
				break;
			default:
				break;
		}
		$I->searchForItemInFrontend($nameEdit, ['search field locator id' => DebtorGroupsDiscountPage::$searchId]);
		$I->waitForElementVisible(['link' => $nameEdit], 30);
	}

	/**
	 * @param $name
	 * @param $code
	 * @param $ownerCompany
	 * @param $companies
	 * @throws \Exception
	 */
	public function createWithWrongCase($name, $code, $ownerCompany, $companies)
	{
		$I = $this;
		$I->amOnPage(DebtorGroupsDiscountPage::$URL);
		$I->click(DebtorGroupsDiscountPage::$newButton);
		$I->waitForElement(DebtorGroupsDiscountPage::$adminForm, 30);
		$I->comment('Add new Missing companies');
		$I->fillField(DebtorGroupsDiscountPage::$nameID, $name);
		$I->fillField(DebtorGroupsDiscountPage::$codeID, $code);
		$I->click(DebtorGroupsDiscountPage::$saveButton);
		$I->waitForText(DebtorGroupsDiscountPage::$missingCompaniesMessage, 30, DebtorGroupsDiscountPage::$messageSuccessID);

		$I->comment('Clear all Field');
		$I->fillField(DebtorGroupsDiscountPage::$nameID,"");
		$I->fillField(DebtorGroupsDiscountPage::$codeID,"");
		$I->fillField(DebtorGroupsDiscountPage::$nameID, $name);
		if ($ownerCompany != 'Main')
		{
			$ownerCompany = '- ' . '('.$ownerCompany.') '.$ownerCompany;
		}
		else
		{
			$ownerCompany = '(main) Main Company';
		}
		$I->selectOptionInChosen(DebtorGroupsDiscountPage::$labelCompany, $ownerCompany);

		$I->waitForElementVisible(DebtorGroupsPage::$customerInput);
		$I->click(DebtorGroupsPage::$customerInput);

		// @todo: the following code could be in a private UX function, because is related to interacting with the specific Javascript field

		foreach ($companies as $company)
		{
			$I->comment($company);
			$company = '- ' . '('.$company.') '.$company;
			$I->waitForElementVisible(DebtorGroupsPage::$customerInput);
			$I->click(DebtorGroupsPage::$customerInput);

			$I->waitForElement(DebtorGroupsPage::$customerInput, 30);
			$I->wait(1);
			$I->waitForElementVisible(DebtorGroupsPage::$customerInput, 30);
			$I->click(DebtorGroupsPage::$customerInput);
			$I->fillField(DebtorGroupsPage::$customerInput,$company);
			$I->pressKey(DebtorGroupsPage::$customerInput,\Facebook\WebDriver\WebDriverKeys::ENTER);
		}

		$I->click(DebtorGroupsDiscountPage::$saveCloseButton);
		$I->waitForText(DebtorGroupsDiscountPage::$missingCodeMessage, 30, DebtorGroupsDiscountPage::$messageSuccessID);

		$I->comment('Clear all Field');
		$I->fillField(DebtorGroupsDiscountPage::$nameID,"");
		$I->fillField(DebtorGroupsDiscountPage::$codeID,"");
		$I->fillField(DebtorGroupsDiscountPage::$codeID, $code);
		$I->click(DebtorGroupsDiscountPage::$saveButton);
		$I->waitForText(DebtorGroupsDiscountPage::$missingNameMessage, 30, DebtorGroupsDiscountPage::$messageSuccessID);
		$I->click(DebtorGroupsDiscountPage::$cancelButton);
	}

	/**
	 * @param $name
	 * @param $code
	 * @param $ownerCompany
	 * @param $companies
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function debtorGroupsAlreadyTaken($name, $code, $ownerCompany, $companies)
	{
		$I = $this;
		$I->amOnPage(DebtorGroupsDiscountPage::$URL);
		$I->click(DebtorGroupsDiscountPage::$newButton);
		$I->waitForElement(DebtorGroupsDiscountPage::$adminForm, 30);
		$I->comment('Add new Missing companies');
		$I->fillField(DebtorGroupsDiscountPage::$nameID, $name);
		$I->fillField(DebtorGroupsDiscountPage::$codeID, $code);
		if ($ownerCompany != 'Main')
		{
			$ownerCompany = '- ' . '('.$ownerCompany.') '.$ownerCompany;
		}
		else
		{
			$ownerCompany = '(main) Main Company';
		}
		$I->selectOptionInChosen(DebtorGroupsDiscountPage::$labelCompany, $ownerCompany);

		foreach ($companies as $company)
		{
			$I->comment($company);
			$company = '- ' . '('.$company.') '.$company;
			$I->waitForElementVisible(DebtorGroupsPage::$customerInput);
			$I->click(DebtorGroupsPage::$customerInput);

			$I->waitForElement(DebtorGroupsPage::$customerInput, 30);
			$I->wait(1);
			$I->waitForElementVisible(DebtorGroupsPage::$customerInput, 30);
			$I->click(DebtorGroupsPage::$customerInput);
			$I->fillField(DebtorGroupsPage::$customerInput,$company);
			$I->pressKey(DebtorGroupsPage::$customerInput,\Facebook\WebDriver\WebDriverKeys::ENTER);
		}

		$I->waitForElementVisible(DebtorGroupsDiscountPage::$saveButton, 30);
		$I->click(DebtorGroupsDiscountPage::$saveButton);

		$userPage = new DebtorGroupsDiscountPage();
		$I->waitForText($userPage->messageCodeReady($code), 30, DebtorGroupsDiscountPage::$alertMessage);
		$I->click(DebtorGroupsDiscountPage::$cancelButton);
	}
	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function delete($name)
	{
		$I = $this;
		$I->amOnPage(DebtorGroupsDiscountPage::$URL);
		$I->searchForItemInFrontend($name, ['search field locator id' => DebtorGroupsDiscountPage::$searchId]);
		$I->waitForElementVisible(['link' => $name], 30);
		$I->checkAllResults();
		$I->click(DebtorGroupsDiscountPage::$deleteButton);
		$I->waitForElement(DebtorGroupsDiscountPage::$newButton);
		$I->searchForItemInFrontend($name, ['search field locator id' => DebtorGroupsDiscountPage::$searchId]);
		$I->waitForElementNotVisible(['link' => $name]);
	}
}