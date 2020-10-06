<?php
/**
 * @package    RedCore
 * @subpackage Model
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Users: Add / Edit  Screen.
 *
 * @package     Aesir.E-Commerce.Test
 * @subpackage  Webdriver
 * @since       3.0
 */

class RedShopBUser0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The menu name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuLinkName = 'RedShopBUsersManagerPage';
	/**
	 * The menu group name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuGroupName = 'RedSHOPB2B_User';

	/**
	 * Function to create User using Users Manager
	 *
	 * @test
	 *
	 * @return void
	 */
	public function createUser()
	{
		$cfg = new SeleniumConfig();
		$rand = rand();
		$nameCompany = 'Testing Redshop Company' . $rand;
		$customerNo = $rand;
		$nameUser = 'Testing Users' . $rand;
		$loginName = 'TestingLogin' . $rand;
		$password = '*&^%$' . $rand;
		$email = $rand . '123@email.com';
		$role = 'Administrator';
		$departmentName = 'Testing Redshop Department' . $rand;
		$address = 'Sample City' . $rand;
		$zip = '1234' . $rand;
		$city = 'Sample and Wow!' . $rand;
		$country = 'Afghanistan';
		$amount = '500';
		$companyPage = 'administrator/index.php?option=com_redshopb&view=companies';
		$departmentPage = 'administrator/index.php?option=com_redshopb&view=departments';
		$userPage = 'administrator/index.php?option=com_redshopb&view=users';
		$companyUrl = $cfg->host . $cfg->path . $companyPage;
		$d = $this->driver;
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->addCompany($nameCompany, $customerNo, $address, $zip, $city, $country);
		$departmentUrl = $cfg->host . $cfg->path . $departmentPage;
		$d->get($departmentUrl);
		$this->redShopBDepartmentsManagerPage = $this->getPageObject('RedShopBDepartmentsManagerPage');
		$this->redShopBDepartmentsManagerPage->addDepartment($departmentName, $nameCompany, $address, $zip, $city, $country);
		$userUrl = $cfg->host . $cfg->path . $userPage;
		$d->get($userUrl);
		$this->redShopBUsersManagerPage = $this->getPageObject('RedShopBUsersManagerPage');
		$this->redShopBUsersManagerPage->addUser($nameUser, $loginName, $password, $password, $email, $departmentName, $country, $role);
		$this->assertEquals($this->redShopBUsersManagerPage->verifyUserCompany($nameUser), $nameCompany, 'User Should be Created in the same Sample Company');
		$this->assertEquals($this->redShopBUsersManagerPage->verifyUserRole($nameUser), '01 :: Administrator', 'Administrator Role');
		$this->assertTrue($this->redShopBUsersManagerPage->searchUser($nameUser), 'Sample User Must be present');
		$this->redShopBUsersManagerPage->creditMoney($nameUser, $amount);
		$this->assertEquals($this->redShopBUsersManagerPage->returnCredit($nameUser), $amount, 'Both Values Must be Equal');
		$this->redShopBUsersManagerPage->deleteUser($nameUser);
		$d->get($departmentUrl);
		$this->redShopBDepartmentsManagerPage = $this->getPageObject('RedShopBDepartmentsManagerPage');
		$this->redShopBDepartmentsManagerPage->deleteDepartment($departmentName);
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->deleteCompany($nameCompany);
	}

	/**
	 * Function to edit/Update Users using Users Manager
	 *
	 * @test
	 *
	 * @return void
	 */
	public function editUser()
	{
		$cfg = new SeleniumConfig();
		$rand = rand();
		$nameCompany = 'Testing Redshop Company' . $rand;
		$customerNo = $rand;
		$nameUser = 'Testing Users' . $rand;
		$newNameUser = 'New Name' . $rand;
		$loginName = 'TestingLogin' . $rand;
		$password = '*&^%$' . $rand;
		$email = $rand . '123@email.com';
		$role = 'Administrator';
		$departmentName = 'Testing Redshop Department' . $rand;
		$address = 'Sample City' . $rand;
		$zip = '1234' . $rand;
		$city = 'Sample and Wow!' . $rand;
		$country = 'Afghanistan';
		$amount = '500';
		$companyPage = 'administrator/index.php?option=com_redshopb&view=companies';
		$departmentPage = 'administrator/index.php?option=com_redshopb&view=departments';
		$userPage = 'administrator/index.php?option=com_redshopb&view=users';
		$companyUrl = $cfg->host . $cfg->path . $companyPage;
		$d = $this->driver;
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->addCompany($nameCompany, $customerNo, $address, $zip, $city, $country);
		$departmentUrl = $cfg->host . $cfg->path . $departmentPage;
		$d->get($departmentUrl);
		$this->redShopBDepartmentsManagerPage = $this->getPageObject('RedShopBDepartmentsManagerPage');
		$this->redShopBDepartmentsManagerPage->addDepartment($departmentName, $nameCompany, $address, $zip, $city, $country);
		$userUrl = $cfg->host . $cfg->path . $userPage;
		$d->get($userUrl);
		$this->redShopBUsersManagerPage = $this->getPageObject('RedShopBUsersManagerPage');
		$this->redShopBUsersManagerPage->addUser($nameUser, $loginName, $password, $password, $email, $departmentName, $country, $role);
		$this->assertEquals($this->redShopBUsersManagerPage->verifyUserCompany($nameUser), $nameCompany, 'User Should be Created in the same Sample Company');
		$this->assertEquals($this->redShopBUsersManagerPage->verifyUserRole($nameUser), '01 :: Administrator', 'Administrator Role');
		$this->assertTrue($this->redShopBUsersManagerPage->searchUser($nameUser), 'Sample User Must be present');
		$this->redShopBUsersManagerPage->creditMoney($nameUser, $amount);
		$this->assertEquals($this->redShopBUsersManagerPage->returnCredit($nameUser), $amount, 'Both Values Must be Equal');
		$this->redShopBUsersManagerPage->updateUser('Name', $newNameUser, $nameUser);
		$this->assertTrue($this->redShopBUsersManagerPage->searchUser($newNameUser), 'Sample User Must be present');
		$this->redShopBUsersManagerPage->deleteUser($newNameUser);
		$d->get($departmentUrl);
		$this->redShopBDepartmentsManagerPage = $this->getPageObject('RedShopBDepartmentsManagerPage');
		$this->redShopBDepartmentsManagerPage->deleteDepartment($departmentName);
		$d->get($companyUrl);
		$this->redShopBCompaniesManagerPage = $this->getPageObject('RedShopBCompaniesManagerPage');
		$this->redShopBCompaniesManagerPage->deleteCompany($nameCompany);
	}
}