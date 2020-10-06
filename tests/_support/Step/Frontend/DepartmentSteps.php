<?php
namespace Step\Frontend;
use PDepend\Metrics\Analyzer\DependencyAnalyzer;
use Step\Acceptance\redshopb2b;
use Page\Frontend\DepartmentPage as DepartmentPage;
class DepartmentSteps extends redshopb2b
{
	/**
	 * @param array $department
	 * @throws \Exception
	 */
	public function createDepartment($department = array())
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Department creation in Frontend');

		$I->amOnPage(DepartmentPage::$URLDepartments);

		$I->waitForElement(DepartmentPage::$newButton, 30);
		$I->click(DepartmentPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->waitForElement(DepartmentPage::$adminForm, 30);

		if (isset($department['number']))
		{
			$I->fillField(DepartmentPage::$labelNumber, $department['number']);
		}

		$I->fillField(DepartmentPage::$nameID, $department['name']);

		if (isset($department['nameSecond']))
		{
			$I->fillField(DepartmentPage::$labelNameSecond, $department['nameSecond']);
		}

		if (isset($department['requisition']))
		{
			$I->fillField(DepartmentPage::$labelRequisition, $department['requisition']);
		}
		$I->selectOptionInChosenjs(DepartmentPage::$company, $department['company']);

		if (isset($department['parent']))
		{
			$I->selectOptionInChosenjs(DepartmentPage::$labelParent, $department['parent']);
		}

		if (isset($department['address']))
		{
			$I->fillField(DepartmentPage::$labelAddress, $department['address']);
		}

		if (isset($department['addressSecond']))
		{
			$I->fillField(DepartmentPage::$labelAddressSecond, $department['addressSecond']);
		}

		if (isset($department['zip']))
		{
			$I->fillField(DepartmentPage::$labelZipCode, $department['zip']);
		}
		$I->wait(1);
		if (isset($department['phone']))
		{
			$I->fillField(DepartmentPage::$labelPhone, $department['phone']);
		}
		$I->wait(1);
		if (isset($department['city']))
		{
			$I->fillField(DepartmentPage::$labelCity, $department['city']);
		}
		if (isset($department['country']))
		{
			$I->selectOptionInChosenjs(DepartmentPage::$labelCountry, $department['country']);
		}

		if (isset($department['status']))
		{
			$I->selectOptionInRadioField(DepartmentPage::$status, $department['status']);
		}
		$I->wait(1);
		$I->click(DepartmentPage::$saveCloseButton);
		$I->waitForElement(DepartmentPage::$messageSuccessID, 30);
		$I->waitForElement(['link' => $department['name']], 30);
		$I->click(['link'=> $department['name']]);
		$I->waitForElement(DepartmentPage::$adminForm, 30);
	}

	/**
	 * @param $name
	 * @param $newName
	 * @throws \Exception
	 */
	public function editDepartment($name, $newName)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Department edit in Frontend');

		$I->amGoingTo('Navigate to Departments page in frontend');
		$I->amOnPage(DepartmentPage::$URLDepartments);
		$I->waitForElement(['link' => $name], 30);
		$I->wait(1);
		$I->checkAllResults();
		$I->click(DepartmentPage::$editButton);
		$I->wait(1);
		$I->comment('I am redirected to the form');
		$I->waitForElement(DepartmentPage::$adminForm, 30);
		$I->fillField(DepartmentPage::$nameID, $newName);
		$I->click(DepartmentPage::$saveCloseButton);
		$I->waitForText(DepartmentPage::$editDepartmentSuccess, 30, DepartmentPage::$messageSuccessID);
		$I->comment('I am redirected to the list');
		$I->waitForElement(DepartmentPage::$searchDepartmentId, 30);
		$I->waitForElement(['link' => $newName], 30);
		$I->dontSeeElement(['link' => $name]);
	}
	
	/**
	 * @param $name
	 * @throws \Exception
	 */
	public function deleteDepartment($name)
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Delete a department in Frontend');

		$I->amOnPage(DepartmentPage::$URLDepartments);
		$I->searchForItemInFrontend($name, ['search field locator id' => DepartmentPage::$searchDepartment]);
		$I->waitForElement(DepartmentPage::$deleteButton, 30);
		$I->checkAllResults();
		$I->click(DepartmentPage::$deleteButton);
		$I->waitForElementVisible(DepartmentPage::$departmentModal, 30);
		$I->waitForElementVisible(DepartmentPage::$deleteDepartment, 30);
		$I->click(DepartmentPage::$deleteDepartment);
		try
		{
			$I->waitForElementNotVisible(DepartmentPage::$departmentModal, 10);
		} catch (\Exception $e)
		{
			$I->click(DepartmentPage::$deleteDepartment);
			$I->waitForElementNotVisible(DepartmentPage::$departmentModal, 10);
		}

		$I->comment('I am redirected to the list');
		$I->waitForElement(DepartmentPage::$searchDepartmentId, 30);
		$I->dontSeeElement(['link' => $name]);
	}
	
	/**
	 * @param array $department
	 * @throws \Exception
	 */
	public function createMissing($department = array())
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Department creation in Frontend');
		$I->amOnPage(DepartmentPage::$URLDepartments);

		$I->waitForElement(DepartmentPage::$newButton, 30);
		$I->click(DepartmentPage::$newButton);

		$I->comment('I am redirected to the form');
		$I->waitForElement(DepartmentPage::$adminForm, 30);
		$I->comment('Testing with missing name and company');
		$I->click(DepartmentPage::$saveButton);
		try
		{
			$I->waitForText(DepartmentPage::$missingNameRequired, 30, DepartmentPage::$messageSuccessID);
		}catch (\Exception $e)
		{
			$I->click(DepartmentPage::$saveButton);
			$I->waitForText(DepartmentPage::$missingNameRequired, 30, DepartmentPage::$messageSuccessID);
		}
		
		$I->waitForText(DepartmentPage::$missingCompanyRequired, 30, DepartmentPage::$messageSuccessID);
		$I->wantToTest('Create with missing company');
		$I->fillField(DepartmentPage::$nameID, $department['name']);
		$I->click(DepartmentPage::$saveButton);
		try
		{
			$I->waitForText(DepartmentPage::$missingCompanyRequired, 10, DepartmentPage::$messageSuccessID);
		}catch (\Exception $e)
		{
			$I->click(DepartmentPage::$saveButton);
			$I->waitForText(DepartmentPage::$missingCompanyRequired, 10, DepartmentPage::$messageSuccessID);
		}
		$I->click(DepartmentPage::$closeButton);
	}
	
	/**
	 * @param array $department
	 * @throws \Exception
	 */
	public function createMissingName($department = array())
	{
		$I = $this;
		$I->am('Administrator');
		$I->wantToTest('Department creation in Frontend');
		$I->amOnPage(DepartmentPage::$URLDepartments);
		
		$I->waitForElement(DepartmentPage::$newButton, 30);
		$I->click(DepartmentPage::$newButton);
		
		$I->comment('I am redirected to the form');
		$I->waitForElement(DepartmentPage::$adminForm, 30);
		$I->comment('Testing with missing name and company');
		$I->selectOptionInChosenjs(DepartmentPage::$company, $department['company']);
		$I->click(DepartmentPage::$saveButton);
		try
		{
			$I->waitForText(DepartmentPage::$missingNameRequired, 10, DepartmentPage::$messageSuccessID);
		}catch (\Exception $e)
		{
			$I->click(DepartmentPage::$saveButton);
			$I->waitForText(DepartmentPage::$missingNameRequired, 10, DepartmentPage::$messageSuccessID);
		}
		$I->click(DepartmentPage::$closeButton);
	}
}
