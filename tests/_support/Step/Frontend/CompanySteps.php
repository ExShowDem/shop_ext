<?php
/**
 * @package  AcceptanceTester
 *
 * @since    2.2
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage#StepObjects
 */
namespace Step\Frontend;
use Page\Frontend\CompanyPage as ComanyPage;


use Page\Frontend\CompanyPage;
use Step\Acceptance\redshopb2b;

class CompanySteps extends redshopb2b
{
	/**
	 * @param $name
	 * @param $city
	 * @param $postcode
	 * @param $address
	 * @param $customerNumber
	 * @param $company
	 * @param $country
	 *
	 * Method create with save button
	 * @throws \Exception
	 */
	public function create($name,$city,$postcode,$address,$customerNumber,$company,$country)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Company creation in Frontend');

		$I->amGoingTo('Navigate to Companies page in frontend');
		$I->amOnPage(ComanyPage::$URLCompanies);
		$I->checkForPhpNoticesOrWarnings();

		$I->waitForElement(ComanyPage::$newButton, 30);
		$I->click(ComanyPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->waitForElement(ComanyPage::$adminForm, 30);
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForElementVisible(ComanyPage::$customerNumber);

		$I->fillField(ComanyPage::$customerNumber, $customerNumber);
		$I->fillField(CompanyPage::$nameID, $name);
		$I->fillField(CompanyPage::$addressField, $address);
		$I->fillField(CompanyPage::$zipCodeField, $postcode);
		$I->fillField(CompanyPage::$cityField, $city);
		$I->selectOptionInChosenjs(CompanyPage::$customerAt, $company);
		$I->selectOptionInChosenjs(CompanyPage::$countryField, $country);
		$I->wait(1);
		$I->click(CompanyPage::$saveButton);
		try
		{
			$I->waitForElement(CompanyPage::$messageSuccessID, 10);
		}catch (\Exception $exception)
		{
			$I->click(CompanyPage::$saveButton);
		}
		$I->wait(1);
		$I->waitForText(CompanyPage::$saveCompanySuccess, 30, CompanyPage::$messageSuccessID);
		$I->wait(1);
		$I->click(ComanyPage::$saveCloseButton);
		$I->waitForElement(['id' => CompanyPage::$searchCompanies], 30);
		$I->searchForItemInFrontend($name, ['search field locator id' => CompanyPage::$searchCompanies]);
		$I->wait(0.5);
		$I->waitForElement(['link' => $name], 30);
	}

	/**
	 * @param $name
	 * @param $city
	 * @param $postcode
	 * @param $address
	 * @param $customerNumber
	 * @param $company
	 * @param $country
	 * @param $phone
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function createCompanyWithPhoneNumber($name,$city,$postcode,$address,$customerNumber,$company,$country,$phone)
	{
		$I = $this;
		$I->am('Administrator');
		$I->amGoingTo('Navigate to Companies page in frontend');
		$I->amOnPage(ComanyPage::$URLCompanies);
		$I->waitForElementVisible(ComanyPage::$newButton, 30);
		$I->click(ComanyPage::$newButton);
		$I->waitForElementVisible(ComanyPage::$adminForm, 30);
		$I->waitForElementVisible(ComanyPage::$customerNumber);
		$I->fillField(ComanyPage::$customerNumber, $customerNumber);
		$I->fillField(CompanyPage::$nameID, $name);
		$I->fillField(CompanyPage::$addressField, $address);
		$I->fillField(CompanyPage::$zipCodeField, $postcode);
		$I->fillField(CompanyPage::$cityField, $city);
		$I->fillField(CompanyPage::$phoneField, $phone);
		$I->selectOptionInChosenjs(CompanyPage::$customerAt, $company);
		$I->selectOptionInChosenjs(CompanyPage::$countryField, $country);
		$I->waitForElementVisible(CompanyPage::$saveButton, 30);
		$I->click(CompanyPage::$saveButton);
		try
		{
			$I->waitForElementVisible(CompanyPage::$messageSuccessID, 30);
		}catch (\Exception $exception)
		{
			$I->click(CompanyPage::$saveButton);
			$I->waitForElementVisible(CompanyPage::$messageSuccessID, 30);
		}
		$I->waitForElementVisible(CompanyPage::$phoneFieldLbl, 30);
		$I->scrollTo(CompanyPage::$phoneFieldLbl);
		$use = new CompanyPage();
		$I->waitForElementVisible($use->returnValueInput($phone), 30);
		$I->waitForElementVisible(CompanyPage::$saveCloseButton, 30);
		$I->click(ComanyPage::$saveCloseButton);
		$I->waitForElementVisible(['id' => CompanyPage::$searchCompanies], 30);
		$I->searchForItemInFrontend($name, ['search field locator id' => CompanyPage::$searchCompanies]);
		$I->waitForElementVisible(['link' => $name], 30);
	}

	/**
	 * @param $name
	 * @param $newName
	 *
	 * Method edit name of company
	 * @throws \Exception
	 */
	public function edit($name,$newName)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Company edit in Frontend');

		$I->amGoingTo('Navigate to Companies page in frontend');
		$I->amOnPage(CompanyPage::$URLCompanies);
		$I->searchForItemInFrontend($name, ['search field locator id' => CompanyPage::$searchCompanies]);
		$I->checkAllResults();
		$I->wait(0.5);
		$I->click(CompanyPage::$editButton);
		$I->wait(1);
		try
		{
			$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		}catch (\Exception $exception)
		{
			$I->click(CompanyPage::$editButton);
			$I->waitForJS('return !!window.jQuery && window.jQuery.active == 0;', 30);
		}
		$I->comment('I am redirected to the form');
		$I->waitForElement(ComanyPage::$adminForm, 30);
		$I->fillField(ComanyPage::$nameID, $newName);
		$I->wait(1);
		$I->click(CompanyPage::$saveButton);
		$I->waitForElementVisible(CompanyPage::$messageSuccessID, 30);
		$I->click(CompanyPage::$saveCloseButton);
		$I->comment('I am redirected to the list');
		$I->waitForElement(['id' => CompanyPage::$searchCompanies], 30);
		$I->waitForElementVisible(['link' => $newName], 30);
		$I->dontSeeElement(['link' => $name]);
	}
	
	/**
	 * @param $name
	 *
	 * Method for edit company to B2C company
	 * @throws \Exception
	 */
	public function editB2CCompany($name, $option = 'No')
	{
		$I = $this;
		$I->amGoingTo('Navigate to Companies page in frontend');
		$I->amOnPage(CompanyPage::$URLCompanies);
		$I->searchForItemInFrontend($name, ['search field locator id' => CompanyPage::$searchCompanies]);
		$I->click(['link' => $name]);
		$I->wait(1);
		$I->comment('I am redirected to the form');
		$I->waitForElement(ComanyPage::$adminForm, 30);
		$I->waitForElement(CompanyPage::$nameID, 30);
		$I->see(CompanyPage::$setB2C);
		
		if($option == 'No')
		{
			$I->selectOptionInRadioField(CompanyPage::$setB2C, $option);
		}
		else
		{
			$I->selectOptionInRadioField(CompanyPage::$setB2C, $option);
			$I->acceptPopup();
		}
		
		$I->scrollUp();
		$I->waitForElementVisible(CompanyPage::$saveCloseButton, 30);
		$I->wait(0.5);
		$I->click(CompanyPage::$saveButton);
		$I->waitForElement(CompanyPage::$messageSuccessID, 30);
		$I->waitForElementVisible(CompanyPage::$saveCloseButton, 30);
		$I->click(CompanyPage::$saveButton);
		$I->waitForElementVisible(CompanyPage::$messageSuccessID, 30);
		$I->waitForText(CompanyPage::$editCompanySuccess, 30);
	}

	/**
	 * @param $name
	 * @param $typeCompany
	 *
	 * Method delete company
	 * @throws \Exception
	 */
	public function delete($name,$typeCompany)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Delete a company in Frontend');

		$I->amGoingTo('Navigate to Companies page in frontend');
		$I->amOnPage(CompanyPage::$URLCompanies);

		switch ($typeCompany){
			case 'main':
				$I->click(CompanyPage::$iconClear);
				break;
			default:
				$I->searchForItemInFrontend($name, ['search field locator id' => CompanyPage::$searchCompanies]);
				break;
		}

		$I->waitForElement(CompanyPage::$deleteButton, 30);
		$I->checkAllResults();
		$I->click(CompanyPage::$deleteButton);
		$I->waitForElementVisible(CompanyPage::$companyModal);
		$I->waitForElement(CompanyPage::$deleteButtonCompany, 30);
		$I->wait(1);
		$I->click(CompanyPage::$deleteButtonCompany);
		$I->waitForElementNotVisible(CompanyPage::$companyModal);
		$I->comment('I am redirected to the list');
		$I->waitForElement(['id' => CompanyPage::$searchCompanies], 30);


		switch ($typeCompany)
		{
			case 'main':
				$I->see(CompanyPage::$messageDeleteMainCompany, CompanyPage::$alertMessage);
				break;
			default:
				$I->dontSeeElement(['link' => $name]);
				break;
		}
	}

	/**
	 * @param $name
	 * @param $city
	 * @param $postcode
	 * @param $address
	 * @param $customerNumber
	 * @param $company
	 * @param $country
	 * @param $function
	 *
	 * Method create with missing fields
	 * @throws \Exception
	 */
	public function createMissingData($name,$city,$postcode,$address,$customerNumber,$company,$country,$function)
	{
		$I = $this;
		$I->am('Administrator');
		$I->amGoingTo('Navigate to Companies page in frontend');
		$I->amOnPage(ComanyPage::$URLCompanies);
		$I->click(CompanyPage::$newButton);
		$I->checkForPhpNoticesOrWarnings();

		switch ($function)
		{
			case 'company':
				$I->clearFieldAll();
				$I->fillField(ComanyPage::$customerNumber, $customerNumber);
				$I->fillField(CompanyPage::$nameID, $name);
				$I->fillField(CompanyPage::$addressField, $address);
				$I->fillField(CompanyPage::$zipCodeField, $postcode);
				$I->fillField(CompanyPage::$cityField, $city);
				break;
			case 'name':
				$I->clearFieldAll();
				$I->fillField(ComanyPage::$customerNumber, $customerNumber);
				$I->fillField(CompanyPage::$addressField, $address);
				$I->fillField(CompanyPage::$zipCodeField, $postcode);
				$I->fillField(CompanyPage::$cityField, $city);
				$I->selectOptionInChosenjs(CompanyPage::$customerAt, $company);
				break;
			case 'customerNumber':
				$I->clearFieldAll();
				$I->fillField(CompanyPage::$nameID, $name);
				$I->fillField(CompanyPage::$addressField, $address);
				$I->fillField(CompanyPage::$zipCodeField, $postcode);
				$I->fillField(CompanyPage::$cityField, $city);
				$I->selectOptionInChosenjs(CompanyPage::$customerAt, $company);
				break;
			case 'address':
				$I->clearFieldAll();
				$I->fillField(ComanyPage::$customerNumber, $customerNumber);
				$I->fillField(CompanyPage::$nameID, $name);
				$I->fillField(CompanyPage::$zipCodeField, $postcode);
				$I->fillField(CompanyPage::$cityField, $city);
				$I->selectOptionInChosenjs(CompanyPage::$customerAt, $company);
				break;
			case 'zip':
				$I->clearFieldAll();
				$I->fillField(ComanyPage::$customerNumber, $customerNumber);
				$I->fillField(CompanyPage::$nameID, $name);
				$I->fillField(CompanyPage::$addressField, $address);
				$I->fillField(CompanyPage::$cityField, $city);
				$I->selectOptionInChosenjs(CompanyPage::$customerAt, $company);
				break;
			case 'city':
				$I->fillField(ComanyPage::$customerNumber, $customerNumber);
				$I->fillField(CompanyPage::$nameID, $name);
				$I->fillField(CompanyPage::$addressField, $address);
				$I->fillField(CompanyPage::$zipCodeField, $postcode);
				$I->selectOptionInChosenjs(CompanyPage::$customerAt, $company);
		}

		$I->click(ComanyPage::$saveButton);
		$I->waitForElement(CompanyPage::$alertError);
	}

	/**
	 * Method clear all fields
	 * @throws \Exception
	 */
	public function clearFieldAll()
	{
		$I = $this;
		$I->comment('I am redirected to the form');
		$I->waitForElement(ComanyPage::$adminForm, 30);
		$I->checkForPhpNoticesOrWarnings();
		$I->waitForElementVisible(ComanyPage::$customerNumber);
		$I->comment('Clear all Field');
		$I->fillField(CompanyPage::$nameID,"");
		$I->fillField(CompanyPage::$customerNumber,"");
		$I->fillField(ComanyPage::$addressField,"");
		$I->fillField(ComanyPage::$zipCodeField,"");
		$I->fillField(ComanyPage::$cityField,"");
	}
}