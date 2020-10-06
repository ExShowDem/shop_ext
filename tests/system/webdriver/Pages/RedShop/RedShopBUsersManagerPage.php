<?php
/**
 * Created by PhpStorm.
 * User: punekala
 * Date: 12/18/13
 * Time: 6:04 PM
 */
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * @package     Aesir.E-Commerce.Test
 * @subpackage  Webdriver
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end component tags menu.
 *
 * @package    RedShopb.Test
 * @subpackage Webdriver
 * @since      1.0
 */

class RedShopBUsersManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 *
	 * @since    1.0
	 */
	protected $waitForXpath = "//h1[text()='Users']";
	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_redshopb&view=users';

	/**
	 * Function to add user using the redshopb user manager
	 *
	 * @param string $name            Name of the Test User
	 *
	 * @param string $loginName       Login Name of Test User
	 *
	 * @param string $password        Password for Test User
	 *
	 * @param string $confirmPassword Password Confirmed
	 *
	 * @param string $email           email for Test user
	 *
	 * @param string $deparment       department for test user
	 *
	 * @param string $country         Country for the test user
	 *
	 * @param string $role            Role for Test User
	 *
	 * @return RedShopBUsersManagerPage
	 */
	public function addUser($name = 'Testing Name', $loginName = 'Login Testing', $password = '1234', $confirmPassword = '1234', $email = 'test@test.com', $department = 'ROOT', $country = 'Afghanistan', $role = 'Administrator')
	{
		$d = $this->driver;
		$tabAddress = 'Address';
		$tabRole = 'role';
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('user.add')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"), 10);
		$nameField = $d->findElement(By::xPath("//input[@id='jform_name']"));
		$nameField->clear();
		$nameField->sendKeys($name);
		$loginField = $d->findElement(By::xPath("//input[@id='jform_username']"));
		$loginField->clear();
		$loginField->sendKeys($loginName);
		$passwordField = $d->findElement(By::xPath("//input[@id='jform_password']"));
		$passwordField->clear();
		$passwordField->sendKeys($password);
		$confirmPasswordField = $d->findElement(By::xPath("//input[@id='jform_password2']"));
		$confirmPasswordField->clear();
		$confirmPasswordField->sendKeys($confirmPassword);
		$emailField = $d->findElement(By::xPath("//input[@id='jform_email']"));
		$emailField->clear();
		$emailField->sendKeys($email);
		$d->findElement(By::xPath("//div[@id='jform_department_id_chzn']/a"))->click();
		$d->findElement(By::xPath("//div[@id='jform_department_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $department . "')]"))->click();
		$d->findElement(By::xPath("//a[contains(.,'" . $tabAddress . "')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_address']"), 10);
		$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']/a"))->click();
		$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']/div/div/input"))->sendKeys($country);
		$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $country . "')]"))->click();
		$d->findElement(By::xPath("//a[contains(@href,'" . $tabRole . "')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//div[@id='jform_role_type_id_chzn']/a"), 10);
		$d->findElement(By::xPath("//div[@id='jform_role_type_id_chzn']/a"))->click();
		$d->findElement(By::xPath("//div[@id='jform_role_type_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $role . "')]"))->click();
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('user.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_users']"), 10);
	}

	/**
	 * Function to Update/Edit User details using RedshopB2B User Manager
	 *
	 * @param $field       Name of the field which is to be updated
	 *
	 * @param $newValue    New value of the field
	 *
	 * @param $name        Name of the User for which changes are to be done
	 *
	 * @return RedShopBUsersManagerPage
	 */
	public function updateUser($field, $newValue, $name)
	{
		$d = $this->driver;
		$tabAddress = 'Address';
		$tabRole = 'role';
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_users']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//tbody/tr/td[2]/a[contains(text(),'" . $name . "')]"), 10);
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn'][1]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"), 10);
		switch ($field)
		{
			case "Name":
				$nameField = $d->findElement(By::xPath("//input[@id='jform_name']"));
				$nameField->clear();
				$nameField->sendKeys($newValue);
				break;
			case "Login Name":
				$loginField = $d->findElement(By::xPath("//input[@id='jform_username']"));
				$loginField->clear();
				$loginField->sendKeys($newValue);
				break;
			case "Password":
				$passwordField = $d->findElement(By::xPath("//input[@id='jform_password']"));
				$passwordField->clear();
				$passwordField->sendKeys($newValue);
				$confirmPasswordField = $d->findElement(By::xPath("//input[@id='jform_password2']"));
				$confirmPasswordField->clear();
				$confirmPasswordField->sendKeys($newValue);
				break;
			case "Email":
				$emailField = $d->findElement(By::xPath("//input[@id='jform_email']"));
				$emailField->clear();
				$emailField->sendKeys($newValue);
				break;
			case "Department":
				$d->findElement(By::xPath("//div[@id='jform_department_id_chzn']/a"))->click();
				$d->findElement(By::xPath("//div[@id='jform_department_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $newValue . "')]"))->click();
				break;
			case "Country":
				$d->findElement(By::xPath("//a[contains(.,'" . $tabAddress . "')]"))->click();
				$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_address']"), 10);
				$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']/a"))->click();
				$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']/div/div/input"))->sendKeys($newValue);
				$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $newValue . "')]"))->click();
				break;
			case "Role":
				$d->findElement(By::xPath("//a[contains(@href,'" . $tabRole . "')]"))->click();
				$d->waitForElementUntilIsPresent(By::xPath("//div[@id='jform_role_type_id_chzn']/a"), 10);
				$d->findElement(By::xPath("//div[@id='jform_role_type_id_chzn']/a"))->click();
				$d->findElement(By::xPath("//div[@id='jform_role_type_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $newValue . "')]"))->click();
				break;
		}
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('user.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_users']"), 10);
	}

	/**
	 * Search for a User
	 *
	 * @param    string $name sample User Name
	 *
	 * @return  boolean
	 */
	public function searchUser($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_users']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$arrayElement = $this->driver->findElements(By::xPath("//tbody/tr/td[2]/a[contains(text(),'" . $name . "')]"));
		if (count($arrayElement))
		{
			return true;
		}
		else
			return false;
	}

	/**
	 * Delete a User
	 *
	 * @param    string $name Name of the User
	 *
	 * @return    boolean
	 */
	public function deleteUser($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_users']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn btn-danger']"))->click();
	}

	/**
	 * Verify  User Company
	 *
	 * @param    string $name Name of the User
	 *
	 * @return    string    $companyName    Name of the Company
	 */
	public function verifyUserCompany($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_users']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name);
		$companyName = $d->findElement(By::xPath("//tbody/tr['" . $row . "']/td[3]"))->getText();
		return $companyName;
	}

	/**
	 * Verify User's Role
	 *
	 * @param    string $name Name of the User
	 *
	 * @return    string    $userRole    Role Of the User
	 */
	public function verifyUserRole($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_users']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name);
		$userRole = $d->findElement(By::xPath("//tbody/tr['" . $row . "']/td[6]"))->getText();
		return $userRole;
	}

	/**
	 * Function to Credit Amount
	 *
	 * @param string $name   Name of the User
	 *
	 * @param string $amount Amount which is to be Credited
	 *
	 * @return void
	 */
	public function creditMoney($name, $amount = '500')
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_users']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		sleep(5);
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//i/..[@class='btn' and contains(.,'Credit')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='assign-amount-id']"));
		sleep(5);
		$amountField = $d->findElement(By::xPath("//input[@id='assign-amount-id']"));
		$amountField->clear();
		$amountField->sendKeys($amount);
		$d->findElement(By::xPath("//button[@class='btn btn-primary']"))->click();
		sleep(3);
	}

	/**
	 * Function  to return the Credit Amount
	 *
	 * @param $name Name of the User
	 *
	 * @return String Credit Amount for the User
	 */
	public function returnCredit($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_users']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		sleep(5);
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn'][1]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"), 10);
		$d->findElement(By::xPath("//a[contains(text(),'Credit')]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//table/tbody/tr/th[contains(text(),'Currency')]"));
		$amount = $d->findElement(By::xPath("//table/tbody/tr[2]/td[2]"))->getText();
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('user.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_users']"), 10);
		return $amount;
	}
}

