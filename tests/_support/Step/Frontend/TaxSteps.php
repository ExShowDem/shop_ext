<?php
/**
 * @package  AcceptanceTester
 *
 * @since    1.4
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */
namespace Step\Frontend;
use Page\Frontend\TaxPage as TaxPage;
use Step\Acceptance\redshopb2b as redshopb2b;
class TaxSteps extends redshopb2b
{
	/**
	 * @param $name
	 * @param $taxRate
	 * @param $country
	 * @param $company
	 * @param $function
	 * @throws \Exception
	 */
	public function create($name, $taxRate, $country, $company, $function)
	{
		$I = $this;
		$I->amOnPage(TaxPage::$URLTax);
		$I->click(TaxPage::$newButton);
		$I->waitForElementVisible(TaxPage::$adminForm,30);
		$I->fillField(TaxPage::$nameID,$name);
		$I->fillField(TaxPage::$labelTaxRate,$taxRate);
		$I->wait(1);
		$I->selectOptionInChosen(TaxPage::$country,$country);
		$I->selectOptionInChosen(TaxPage::$company,$company);
		$I->wait(1);
		switch ($function)
		{
			case 'save':
				$I->click(TaxPage::$saveButton);
				$I->seeInField(TaxPage::$nameID,$name);
				$I->waitForElementVisible(TaxPage::$messageSuccessID,30);
				$I->waitForElementVisible(TaxPage::$closeButton, 30);
				$I->click(TaxPage::$closeButton);
				break;
			case 'save&close':
				$I->click(TaxPage::$saveCloseButton);
				$I->waitForElementVisible(TaxPage::$messageSuccessID,30);
				$I->click(TaxPage::$iconClear);
				$I->waitForElementVisible(TaxPage::$editButton,30);
				$I->searchForItemInFrontend($name, ['search field locator id' => TaxPage::$searchTaxId]);
				$I->waitForText($name, 30);
				break;
			case 'save&new':
				$I->click(TaxPage::$saveNewButton);
				$I->waitForElementVisible(TaxPage::$messageSuccessID,30);
				$I->dontSeeInField(TaxPage::$nameID,$name);
				$I->waitForElementVisible(TaxPage::$cancelButton, 30);
				$I->click(TaxPage::$cancelButton);
				break;
			case 'cancel':
				$I->click(TaxPage::$cancelButton);
				$I->dontSee($name);
				break;
			default:
				break;
		}
	}

	/**
	 * @param $name
	 * @param $taxRate
	 * @param $country
	 * @param $company
	 * @param $taxGroup
	 * @throws \Exception
	 */
	public function createTaxWithTaxGroup($name, $taxRate, $country, $company, $taxGroup)
	{
		$I = $this;
		$I->amOnPage(TaxPage::$URLTax);
		$I->click(TaxPage::$newButton);
		$I->waitForElementVisible(TaxPage::$adminForm,30);
		$I->waitForElementVisible(TaxPage::$nameID, 30);
		$I->fillField(TaxPage::$nameID,$name);
		$I->waitForElementVisible(TaxPage::$labelTaxRate, 30);
		$I->fillField(TaxPage::$labelTaxRate, $taxRate);
		$I->selectOptionInChosen(TaxPage::$country, $country);
		$I->selectOptionInChosen(TaxPage::$company, $company);
		$I->click(TaxPage::$taxGroup);
		$usePage = new TaxPage();
		$I->waitForElementVisible($usePage->returnChoice($taxGroup), 30);
		$I->click($usePage->returnChoice($taxGroup));
		$I->waitForElementVisible(TaxPage::$saveCloseButton, 30);
		$I->click(TaxPage::$saveCloseButton);
		$I->waitForElementVisible(TaxPage::$messageSuccessID,30);
		$I->click(TaxPage::$iconClear);
		$I->waitForElementVisible(TaxPage::$editButton,30);
		$I->searchForItemInFrontend($name, ['search field locator id' => TaxPage::$searchTaxId]);
		$I->waitForText($name, 30);
	}

	/**
	 * @param $name
	 * @param $nameEdit
	 * @param $value
	 * @param $function
	 * @throws \Exception
	 */
	public function edit($name, $nameEdit, $value, $function)
	{
		$I = $this;
		$I->amOnPage(TaxPage::$URLTax);
		$I->click(TaxPage::$iconClear);
		$I->searchForItemInFrontend($name, ['search field locator id' => TaxPage::$searchTaxId]);
		$I->see($name);
		$I->checkAllResults();
		$I->click(TaxPage::$editButton);
		$I->wait(0.5);
		$I->waitForElement(TaxPage::$adminForm,30);
		$I->seeInField(TaxPage::$nameID,$name);
		$I->wait(0.5);
		$I->fillField(TaxPage::$nameID,$nameEdit);
		$I->wait(0.5);
		$I->fillField(TaxPage::$labelTaxRate,$value);

		switch ($function){
			case 'save':
				$I->waitForElement(TaxPage::$saveButton, 30);
				$I->wait(1);
				$I->click(TaxPage::$saveButton);
				$I->seeInField(TaxPage::$nameID,$nameEdit);
				$I->waitForElement(TaxPage::$messageSuccessID,30);
				$I->waitForElement(TaxPage::$saveCloseButton, 30);
				$I->wait(1);
				$I->click(TaxPage::$saveCloseButton);
				break;
			case 'save&close':
				$I->wantTo('Test save and Close button with edit name');
				$I->wait(1);
				$I->waitForElement(TaxPage::$saveCloseButton, 10);
				$I->wait(0.5);
				$I->click(TaxPage::$saveCloseButton);
				$I->waitForElement(TaxPage::$messageSuccessID,30);
				$I->see($nameEdit);
				break;
			case 'save&new':
				$I->wait(1);
				$I->click(TaxPage::$saveNewButton);
				$I->waitForElement(TaxPage::$adminForm, 30);
				$I->dontSeeInField(TaxPage::$nameID,$nameEdit);
				$I->waitForElement(TaxPage::$cancelButton, 30);
				$I->click(TaxPage::$cancelButton);
				break;
			case 'close':
				$I->wantTo('Test close buton');
				$I->click(TaxPage::$closeButton);
				$I->wantTo('Test Close button with edit name');
				$I->see($name);
				break;
			default:
				break;
		}
		$I->amOnPage(TaxPage::$URLTax);
		$I->comment('Clicks on Clear button');
		$I->click(TaxPage::$iconClear);
		$I->waitForElement(TaxPage::$newButton,30);
	}
	
	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function delete($name)
	{
		$I = $this;
		$I->amOnPage(TaxPage::$URLTax);
		$I->click(TaxPage::$iconClear);
		$I->searchForItemInFrontend($name, ['search field locator id' => TaxPage::$searchTaxId]);
		$I->checkAllResults();
		$I->click(TaxPage::$deleteButton);
		$I->dontSee($name);
	}
	
	/**
	 * @param $name
	 * @param $taxRate
	 * @throws \Exception
	 */
	public function checkMissing($name, $taxRate)
	{
		$I = $this;
		$I->amOnPage(TaxPage::$URLTax);
		$I->click(TaxPage::$newButton);
		$I->waitForElement(TaxPage::$adminForm,30);

		$I->comment('Check with missing name');
		$I->fillField(TaxPage::$nameID,$name);
		$I->wait(1);
		$I->click(TaxPage::$saveButton);
		$I->waitForElement(TaxPage::$messageClass,30);
		$I->see(TaxPage::$missingTaxRate);
		$I->clearViewField();

		$I->comment('Check with missing tax rate value');
		$I->fillField(TaxPage::$labelTaxRate,$taxRate);
		$I->wait(1);
		$I->click(TaxPage::$saveButton);
		$I->wait(1);
		$I->waitForElement(TaxPage::$messageClass,30);
		$I->see(TaxPage::$missingNameRequired);
	}
	
	/**
	 * clear data on all fields
	 */
	public function clearViewField()
	{
		$I = $this;
		$I->fillField(TaxPage::$nameID,"");
		$I->fillField(TaxPage::$labelTaxRate,"");
	}
}