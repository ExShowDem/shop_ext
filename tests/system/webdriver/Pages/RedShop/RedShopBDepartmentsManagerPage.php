<?php
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

class RedShopBDepartmentsManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 *
	 * @since    1.0
	 */
	protected $waitForXpath = "//h1[text()='Departments']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_redshopb&view=departments';

	/**
	 * Function to Add Department
	 *
	 * @param string $name    Name of the Department
	 *
	 * @param string $company Name of the Company to which the department belongs
	 *
	 * @param string $address Address of the Department
	 *
	 * @param string $zip     Zip Code
	 *
	 * @param string $city    City
	 *
	 * @param string $country Country for the Sample Department
	 *
	 * @return  RedShopBDepartmentsManagerPage
	 */
	public function addDepartment($name = 'Sample Department', $company, $address = 'Sample Address', $zip = '1234', $city = 'Sample City!', $country = 'Afghanistan')
	{
		$d = $this->driver;
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('department.add')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"), 10);
		$nameField = $d->findElement(By::xPath("//input[@id='jform_name']"));
		$nameField->clear();
		$nameField->sendKeys($name);
		$addressField = $d->findElement(By::xPath("//input[@id='jform_address']"));
		$addressField->clear();
		$addressField->sendKeys($address);
		$zipField = $d->findElement(By::xPath("//input[@id='jform_zip']"));
		$zipField->clear();
		$zipField->sendKeys($zip);
		$cityField = $d->findElement(By::xPath("//input[@id='jform_city']"));
		$cityField->clear();
		$cityField->sendKeys($city);
		$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']/a"))->click();
		$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']/div/div/input"))->sendKeys($country);
		$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $country . "')]"))->click();
		$d->findElement(By::xPath("//div[@id='jform_company_id_chzn']/a"))->click();
		$d->findElement(By::xPath("//div[@id='jform_company_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $company . "')]"))->click();
		sleep(5);
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('department.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_departments']"), 10);
	}

	/**
	 * Function to Update/Edit Department details using redshopb Department Manager Page
	 *
	 * @param $field       Name of the field which is to be updated
	 *
	 * @param $newValue    New value of the field
	 *
	 * @param $name        Name of the Department for which changes are to be done
	 *
	 * @return RedShopBUsersManagerPage
	 */
	public function updateDepartment($field, $newValue, $name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_departments']"));
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
			case "Address":
				$addressField = $d->findElement(By::xPath("//input[@id='jform_address']"));
				$addressField->clear();
				$addressField->sendKeys($newValue);
				break;
			case "Zip":
				$zipField = $d->findElement(By::xPath("//input[@id='jform_zip']"));
				$zipField->clear();
				$zipField->sendKeys($newValue);
				break;
			case "City":
				$cityField = $d->findElement(By::xPath("//input[@id='jform_city']"));
				$cityField->clear();
				$cityField->sendKeys($newValue);
				break;
			case "Country":
				$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']/a"))->click();
				$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']/div/div/input"))->sendKeys($newValue);
				$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $newValue . "')]"))->click();
				break;
			case "Company":
				$d->findElement(By::xPath("//div[@id='jform_company_id_chzn']/a"))->click();
				$d->findElement(By::xPath("//div[@id='jform_company_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $newValue . "')]"))->click();
				break;
		}
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('department.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_departments']"), 10);
	}

	/**
	 * Search for a Department
	 *
	 * @param    string $name Name for the Department
	 *
	 * @return  boolean
	 */
	public function searchDepartment($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_departments']"));
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
	 * Delete a Department
	 *
	 * @param    string $name Name of the Department
	 *
	 * @return    boolean
	 */
	public function deleteDepartment($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_departments']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn btn-danger']"))->click();
	}
}