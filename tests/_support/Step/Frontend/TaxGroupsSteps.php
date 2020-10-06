<?php
/**
 * @package  AcceptanceTester
 *
 * @since    1.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */
namespace Step\Frontend;
use Page\Frontend\TaxGroupsPage as TaxGroupsPage;

class TaxGroupsSteps extends  \Step\Acceptance\redshopb2b
{
	/**
	 * @param $name
	 * @param $company
	 * @param $status
	 * @param $function
	 * @throws \Exception
	 */
	public function create($name, $company, $status, $function)
	{
		$I = $this;
		$I->amOnPage(TaxGroupsPage::$URLTaxGroups);
		$I->click(TaxGroupsPage::$newButton);
		$I->waitForElementVisible(TaxGroupsPage::$adminForm, 30);
		$I->fillField(TaxGroupsPage::$nameID, $name);
		$I->selectOptionInChosen(TaxGroupsPage::$company,$company);
		$I->selectOptionInRadioField(TaxGroupsPage::$status,$status);
		$I->wait(2);
		switch ($function)
		{
			case 'save':
				$I->click(TaxGroupsPage::$saveButton);
				$I->wait(1);
				$I->seeInField(TaxGroupsPage::$nameID,$name);
				$I->waitForElement(TaxGroupsPage::$messageSuccessID,30);
				$I->waitForText(TaxGroupsPage::$saveSuccess, 30, TaxGroupsPage::$messageSuccessID);
				$I->click(TaxGroupsPage::$saveCloseButton);
				break;
			case 'save&close':
				$I->wait(2);
				$I->click(TaxGroupsPage::$saveCloseButton);
				$I->waitForElement(TaxGroupsPage::$messageSuccessID,30);
				$I->waitForText(TaxGroupsPage::$saveSuccess, 30, TaxGroupsPage::$messageSuccessID);
				break;
			case 'save&new':
				$I->wait(3);
				$I->click(TaxGroupsPage::$saveNewButton);
				$I->waitForElement(TaxGroupsPage::$adminForm,30);
				$I->waitForElement(TaxGroupsPage::$messageSuccessID,30);
				$I->waitForText(TaxGroupsPage::$saveSuccess, 30, TaxGroupsPage::$messageSuccessID);
				$I->wait(0.5);
				$I->click(TaxGroupsPage::$cancelButton);
				break;
			default:
				break;
		}
	}

	/**
	 * @param $name
	 * @param $editName
	 * @param $function
	 * @throws \Exception
	 */
	public function edit($name,$editName,$function){
		$I = $this;
		$I->amOnPage(TaxGroupsPage::$URLTaxGroups);
		$I->searchForItemInFrontend($name, ['search field locator id' => TaxGroupsPage::$searchTaxId]);
		$I->checkAllResults();
		$I->wait(0.5);
		$I->click(TaxGroupsPage::$editButton);
		$I->wait(2);
		$I->waitForElement(TaxGroupsPage::$adminForm,30);
		$I->wait(1);
		$I->seeInField(TaxGroupsPage::$nameID, $name);
		$I->fillField(TaxGroupsPage::$nameID, $editName);

		switch ($function)
		{
			case 'save':
				$I->wait(1);
				$I->click(TaxGroupsPage::$saveButton);
				$I->seeInField(TaxGroupsPage::$nameID,$editName);
				$I->waitForElement(TaxGroupsPage::$messageSuccessID,30);
				$I->waitForText(TaxGroupsPage::$saveItem, 30, TaxGroupsPage::$messageSuccessID);
				$I->click(TaxGroupsPage::$saveCloseButton);
				break;
			case 'save&close':
				$I->wait(1);
				$I->click(TaxGroupsPage::$saveCloseButton);
				$I->waitForElement(TaxGroupsPage::$messageSuccessID,30);
				$I->waitForText(TaxGroupsPage::$saveItem, 30, TaxGroupsPage::$messageSuccessID);
				break;
			case 'save&new':
				$I->wait(1);
				$I->click(TaxGroupsPage::$saveNewButton);
				$I->waitForElement(TaxGroupsPage::$adminForm,30);
				$I->waitForElement(TaxGroupsPage::$messageSuccessID,30);
				$I->waitForText(TaxGroupsPage::$saveItem, 30, TaxGroupsPage::$messageSuccessID);
				$I->click(TaxGroupsPage::$cancelButton);
				break;
			default:
				break;
		}
	}
	
	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function delete($name)
	{
		$I = $this;
		$I->amOnPage(TaxGroupsPage::$URLTaxGroups);
		$I->searchForItemInFrontend($name, ['search field locator id' => TaxGroupsPage::$searchTaxId]);
		$I->checkAllResults();
		$I->click(TaxGroupsPage::$deleteButton);
		$I->searchForItemInFrontend($name, ['search field locator id' => TaxGroupsPage::$searchTaxId]);
		$I->dontSee($name);
	}
}