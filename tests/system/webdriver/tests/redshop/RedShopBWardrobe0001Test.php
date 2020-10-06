<?php
/**
 * @package RedCore
 * @subpackage Model
 * @copyright Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Wardrobe: Add / Edit  Screen.
 *
 * @package     Aesir.E-Commerce.Test
 * @subpackage  Webdriver
 * @since       3.0
 */

class RedShopBWardrobe0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The menu name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuLinkName = 'RedShopBWardrobesManagerPage';

	/**
	 * The menu group name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuGroupName = 'RedSHOPB2B_Wardrobe';

	/**
	 * Function to Create Wardrobe
	 *
	 * @test
	 *
	 * @return void
	 */
	public function createWardrobe()
	{
		$cfg = new SeleniumConfig();
		$rand = rand();
		$nameCompany = 'Testing Redshop Company' . $rand;
		$customerNo = $rand;
		$departmentName = 'Testing Redshop Department' . $rand;
		$wardrobeName = 'Wardrobe Testing' . $rand;
		$address = 'Sample City' . $rand;
		$zip = '1234' . $rand;
		$city = 'Sample and Wow!' . $rand;
		$country = 'Afghanistan';
		$companyPage = 'administrator/index.php?option=com_redshopb&view=companies';
		$departmentPage = 'administrator/index.php?option=com_redshopb&view=departments';
		$wardrobePage = 'administrator/index.php?option=com_redshopb&view=wardrobes';
		$companyUrl = $cfg->host . $cfg->path . $companyPage;
		$d = $this->driver;
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->addCompany($nameCompany, $customerNo, $address, $zip, $city, $country);
		$departmentUrl = $cfg->host . $cfg->path . $departmentPage;
		$d->get($departmentUrl);
		$this->redShopBDepartmentsManagerPage = $this->getPageObject('RedShopBDepartmentsManagerPage');
		$this->redShopBDepartmentsManagerPage->addDepartment($departmentName, $nameCompany, $address, $zip, $city, $country);
		$wardrobeUrl = $cfg->host . $cfg->path . $wardrobePage;
		$d->get($wardrobeUrl);
		$this->redShopBWardrobesManagerPage = $this->getPageObject('RedShopBWardrobesManagerPage');
		$this->redShopBWardrobesManagerPage->addWardrobe($wardrobeName, $nameCompany, $departmentName);
		$this->assertTrue($this->redShopBWardrobesManagerPage->searchWardrobe($wardrobeName), 'Wardrobe Must be Present');
		$this->redShopBWardrobesManagerPage->deleteWardrobe($wardrobeName);
		$d->get($departmentUrl);
		$this->redShopBDepartmentsManagerPage = $this->getPageObject('RedShopBDepartmentsManagerPage');
		$this->redShopBDepartmentsManagerPage->deleteDepartment($departmentName);
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->deleteCompany($nameCompany);
	}

	/**
	 * Function to Update Wardrobe
	 *
	 * @test
	 *
	 * @return void
	 */
	public function updateWardrobe()
	{
		$cfg = new SeleniumConfig();
		$rand = rand();
		$nameCompany = 'Testing Redshop Company' . $rand;
		$departmentName = 'Testing Redshop Department' . $rand;
		$wardrobeName = 'Wardrobe Testing' . $rand;
		$newWardrobeName = 'New Wardrobe' . $rand;
		$address = 'Sample City' . $rand;
		$zip = '1234' . $rand;
		$city = 'Sample and Wow!' . $rand;
		$country = 'Afghanistan';
		$companyPage = 'administrator/index.php?option=com_redshopb&view=companies';
		$departmentPage = 'administrator/index.php?option=com_redshopb&view=departments';
		$wardrobePage = 'administrator/index.php?option=com_redshopb&view=wardrobes';
		$companyUrl = $cfg->host . $cfg->path . $companyPage;
		$d = $this->driver;
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->addCompany($nameCompany, $address, $zip, $city, $country);
		$departmentUrl = $cfg->host . $cfg->path . $departmentPage;
		$d->get($departmentUrl);
		$this->redShopBDepartmentsManagerPage = $this->getPageObject('RedShopBDepartmentsManagerPage');
		$this->redShopBDepartmentsManagerPage->addDepartment($departmentName, $nameCompany, $address, $zip, $city, $country);
		$wardrobeUrl = $cfg->host . $cfg->path . $wardrobePage;
		$d->get($wardrobeUrl);
		$this->redShopBWardrobesManagerPage = $this->getPageObject('RedShopBWardrobesManagerPage');
		$this->redShopBWardrobesManagerPage->addWardrobe($wardrobeName, $nameCompany, $departmentName);
		$this->assertTrue($this->redShopBWardrobesManagerPage->searchWardrobe($wardrobeName), 'Wardrobe Must be Present');
		$this->redShopBWardrobesManagerPage->editWardrobe('Name', $newWardrobeName, $wardrobeName);
		$this->assertTrue($this->redShopBWardrobesManagerPage->searchWardrobe($newWardrobeName), 'New Wardrobe must be present');
		$this->assertEquals($this->redShopBWardrobesManagerPage->getState($newWardrobeName),'published', 'Initial State Must Be Published');
		$this->redShopBWardrobesManagerPage->editWardrobe('Status', 'Unpublished', $newWardrobeName);
		$this->assertEquals($this->redShopBWardrobesManagerPage->getState($newWardrobeName),'unpublished', 'New State Must Be Unpublished');
		$this->redShopBWardrobesManagerPage->deleteWardrobe($newWardrobeName);
		$d->get($departmentUrl);
		$this->redShopBDepartmentsManagerPage = $this->getPageObject('RedShopBDepartmentsManagerPage');
		$this->redShopBDepartmentsManagerPage->deleteDepartment($departmentName);
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->deleteCompany($nameCompany);
	}
}