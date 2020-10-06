<?php
/**
 * @package    RedCore
 * @subpackage Model
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Company: Add / Edit  Screen.
 *
 * @package     Aesir.E-Commerce.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class RedShopBCompany0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The menu name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuLinkName = 'RedShopBCompaniesManagerPage';
	/**
	 * The menu group name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuGroupName = 'RedSHOPB2B_Company';

	/**
	 * Function to create Company Using Company Manager
	 *
	 * @test
	 *
	 * @return void
	 */
	public function createCompany()
	{
		$rand = rand();
		$name = 'Testing Redshop Company' . $rand;
		$customerNo = $rand;
		$address = 'Sample City' . $rand;
		$zip = '1234' . $rand;
		$city = 'Sample and Wow!' . $rand;
		$country = 'Afghanistan';
		$this->appTestPage->addCompany($name, $customerNo, $address, $zip, $city, $country);
		$this->assertTrue($this->appTestPage->searchCompany($name), 'Sample Company Must be Present');
		$this->appTestPage->deleteCompany($name);
	}

	/**
	 * Function to Edit Company
	 *
	 * @test
	 *
	 * @return void
	 */
	public function editCompany()
	{
		$rand = rand();
		$name = 'Testing Redshop Company' . $rand;
		$customerNo = $rand;
		$newName = 'Again Testing' . $rand;
		$address = 'Sample City' . $rand;
		$zip = '1234' . $rand;
		$city = 'Sample and Wow!' . $rand;
		$country = 'Afghanistan';
		$this->appTestPage->addCompany($name, $customerNo, $address, $zip, $city, $country);
		$this->assertTrue($this->appTestPage->searchCompany($name), 'Sample Company Must be Present');
		$this->appTestPage->updateCompany('Name', $newName, $name);
		$this->assertTrue($this->appTestPage->searchCompany($newName), 'New Company Name must be present');
		$this->appTestPage->deleteCompany($newName);
	}

	/**
	 * Function to change the state of a Company
	 *
	 * @test
	 *
	 * @return void
	 */
	public function changeState()
	{
		$rand = rand();
		$name = 'Testing Redshop Company' . $rand;
		$customerNo = $rand;
		$address = 'Sample City' . $rand;
		$zip = '1234' . $rand;
		$city = 'Sample and Wow!' . $rand;
		$country = 'Afghanistan';
		$this->appTestPage->addCompany($name, $customerNo, $address, $zip, $city, $country);
		$currentState = $this->appTestPage->getState($name);
		$this->assertEquals($currentState, 'published', 'Initial State Must be Published');
		$this->appTestPage->changeCompanyState($name, 'unpublished');
		$currentState = $this->appTestPage->getState($name);
		$this->assertEquals($currentState, 'unpublished', 'State Must be Unpublished');
		$this->appTestPage->deleteCompany($name);
	}

	/**
	 * Function to Test Shop View
	 *
	 * @test
	 *
	 * @return void
	 */
	public function testShopView()
	{
		$rand = rand();
		$name = 'Testing Redshop Company' . $rand;
		$customerNo = $rand;
		$address = 'Sample City' . $rand;
		$zip = '1234' . $rand;
		$city = 'Sample and Wow!' . $rand;
		$country = 'Afghanistan';
		$this->appTestPage->deleteAllCompany();
		$this->appTestPage->addCompany($name, $customerNo, $address, $zip, $city, $country);
		$currentState = $this->appTestPage->getState($name);
		$this->assertEquals($currentState, 'published', 'Initial State Must be Published');
		$shopPage = 'administrator/index.php?option=com_redshopb&view=shop';
		$companyPage = 'administrator/index.php?option=com_redshopb&view=companies';
		$cfg = new SeleniumConfig();
		$shopUrl = $cfg->host . $cfg->path . $shopPage;
		$d = $this->driver;
		$d->get($shopUrl);
		$this->redShopBShopsManagerPage = $this->getPageObject('RedShopBShopsManagerPage');
		$this->assertTrue($this->redShopBShopsManagerPage->verifyCompany($name), 'This Must be True');
		$companyUrl = $cfg->host . $cfg->path . $companyPage;
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->deleteCompany($name);
	}
}