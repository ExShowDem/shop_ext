<?php
/**
 * @package RedCore
 * @subpackage Model
 * @copyright Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Department: Add / Edit  Screen.
 *
 * @package     Aesir.E-Commerce.Test
 * @subpackage  Webdriver
 * @since       3.0
 */

class RedShopBDepartment0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The menu name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuLinkName = 'RedShopBDepartmentsManagerPage';

	/**
	 * The menu group name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuGroupName = 'RedSHOPB2B_Department';

	/**
	 * Function to create Department using Department Manager
	 *
	 * @test
	 *
	 * @return void
	 */
	public function createDepartment()
	{
		$cfg = new SeleniumConfig();
		$rand = rand();
		$nameCompany = 'Testing Redshop Company' . $rand;
		$customerNo = $rand;
		$departmentName = 'Testing Redshop Department' . $rand;
		$address = 'Sample City' . $rand;
		$zip = '1234' . $rand;
		$city = 'Sample and Wow!' . $rand;
		$country = 'Afghanistan';
		$companyPage = 'administrator/index.php?option=com_redshopb&view=companies';
		$departmentPage = 'administrator/index.php?option=com_redshopb&view=departments';
		$companyUrl = $cfg->host . $cfg->path . $companyPage;
		$d = $this->driver;
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->addCompany($nameCompany, $customerNo, $address, $zip, $city, $country);
		$departmentUrl = $cfg->host . $cfg->path . $departmentPage;
		$d->get($departmentUrl);
		$this->redShopBDepartmentsManagerPage = $this->getPageObject('RedShopBDepartmentsManagerPage');
		$this->redShopBDepartmentsManagerPage->addDepartment($departmentName, $nameCompany, $address, $zip, $city, $country);
		$this->assertTrue($this->redShopBDepartmentsManagerPage->searchDepartment($departmentName), 'Sample Department Must be Present');
		$this->redShopBDepartmentsManagerPage->deleteDepartment($departmentName);
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->deleteCompany($nameCompany);
	}

	/**
	 * Function to edit/Update Department using Department Manager
	 *
	 * @test
	 *
	 * @return void
	 */
	public function editDepartment()
	{
		$cfg = new SeleniumConfig();
		$rand = rand();
		$nameCompany = 'Testing Redshop Company' . $rand;
		$customerNo = $rand;
		$departmentName = 'Testing Redshop Department' . $rand;
		$departmentNewName = $rand . 'Testing RedShopB Department';
		$address = 'Sample City' . $rand;
		$zip = '1234' . $rand;
		$city = 'Sample and Wow!' . $rand;
		$country = 'Afghanistan';
		$companyPage = 'administrator/index.php?option=com_redshopb&view=companies';
		$departmentPage = 'administrator/index.php?option=com_redshopb&view=departments';
		$companyUrl = $cfg->host . $cfg->path . $companyPage;
		$d = $this->driver;
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->addCompany($nameCompany, $customerNo, $address, $zip, $city, $country);
		$departmentUrl = $cfg->host . $cfg->path . $departmentPage;
		$d->get($departmentUrl);
		$this->redShopBDepartmentsManagerPage = $this->getPageObject('RedShopBDepartmentsManagerPage');
		$this->redShopBDepartmentsManagerPage->addDepartment($departmentName, $nameCompany, $address, $zip, $city, $country);
		$this->assertTrue($this->redShopBDepartmentsManagerPage->searchDepartment($departmentName), 'Sample Department Must be Present');
		$this->redShopBDepartmentsManagerPage->updateDepartment('Name', $departmentNewName, $departmentName);
		$this->assertTrue($this->redShopBDepartmentsManagerPage->searchDepartment($departmentNewName), 'Sample Department Must be Present');
		$this->redShopBDepartmentsManagerPage->deleteDepartment($departmentNewName);
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->deleteCompany($nameCompany);
	}
}