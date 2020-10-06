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

class RedShopBCompaniesManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 *
	 * @since    1.0
	 */
	protected $waitForXpath = "//h1[text()='Companies']";
	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_redshopb&view=companies';

	/**
	 * Function to add company using the redshopb company manager
	 *
	 * @param string $name       Name of the Sample Company
	 *
	 * @param string $customerNo Sample Customer No.
	 *
	 * @param string $address    Sample customer address
	 *
	 * @param string $zip        Sample zip Code
	 *
	 * @param string $city       Sample Customer City
	 *
	 * @param string $country    ->Sample Customer Country
	 *
	 * @return RedShopBCompaniesManagerPage
	 */
	public function addCompany($name = 'Testing Company', $customerNo = '1234', $address = '1234 West Avenue', $zip = '452005', $city = 'Boston', $country = 'Afghanistan')
	{
		$d = $this->driver;
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('company.add')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"), 10);
		$nameField = $d->findElement(By::xPath("//input[@id='jform_name']"));
		$nameField->clear();
		$nameField->sendKeys($name);
		$customerNoField = $d->findElement(By::xPath("//input[@id='jform_customer_number']"));
		$customerNoField->clear();
		$customerNoField->sendKeys($customerNo);
		$addressField = $d->findElement(By::xPath("//input[@id='jform_address']"));
		$addressField->clear();
		$addressField->sendKeys($address);
		$zipField = $d->findElement(By::xPath("//input[@id='jform_zip']"));
		$zipField->clear();
		$zipField->sendKeys($zip);
		$cityField = $d->findElement(By::xPath("//input[@id='jform_city']"));
		$cityField->clear();
		$cityField->sendKeys($city);
		sleep(5);
		$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']/a"))->click();
		$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']/div/div/input"))->sendKeys($country);
		sleep(2);
		$d->findElement(By::xPath("//div[@id='jform_country_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $country . "')]"))->click();
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('company.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_companies']"), 10);
	}

	/**
	 * Function to Update/Edit Company details using RedshopB2B Company Manager
	 *
	 * @param $field       Name of the field which is to be updated
	 *
	 * @param $newValue    New value of the field
	 *
	 * @param $name        Name of the Company for which changes are to be done
	 *
	 * @return RedShopBCompaniesManagerPage
	 */
	public function updateCompany($field, $newValue, $name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_companies']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//tbody/tr/td[4]/a[contains(text(),'" . $name . "')]"), 10);
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
			case "CustomerNo":
				$customerNoField = $d->findElement(By::xPath("//input[@id='jform_customer_number']"));
				$customerNoField->clear();
				$customerNoField->sendKeys($newValue);
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
		}
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('company.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_companies']"), 10);
	}

	/**
	 * Delete a Company
	 *
	 * @param    string $name Test Company Title
	 *
	 * @return    boolean
	 */
	public function deleteCompany($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_companies']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn btn-danger']"))->click();
	}

	/**
	 * Get state  of a Company
	 *
	 * @param    string $name Company Title field
	 *
	 * @return    string    $result //Published or Unpublished
	 */
	public function getState($name)
	{
		$d = $this->driver;
		$result = false;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_companies']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[2]//a"))->getAttribute(@onclick);
		if (strpos($text, 'companies.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'companies.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}

	/**
	 * Change state of a Company in the Company Manager
	 *
	 * @param string $name  Company Title field
	 *
	 * @param string $state State of the Tag
	 *
	 * @return  void
	 */
	public function changeCompanyState($name, $state = 'published')
	{
		$d = $this->driver;
		$this->searchCompany($name);
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		if (strtolower($state) == 'published')
		{
			$d->findElement(By::xPath("//button[@class='btn'][2]"))->click();
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
		elseif (strtolower($state) == 'unpublished')
		{
			$d->findElement(By::xPath("//button[@class='btn'][3]"))->click();
			$this->driver->waitForElementUntilIsPresent(By::xPath($this->waitForXpath));
		}
		$this->searchCompany(" ");
	}

	/**
	 * Search for a Company
	 *
	 * @param    string $name Test Company Title
	 *
	 * @return  boolean
	 */
	public function searchCompany($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_companies']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$arrayElement = $this->driver->findElements(By::xPath("//tbody/tr/td[4]/a[contains(text(),'" . $name . "')]"));
		if (count($arrayElement))
		{
			return true;
		}
		else
			return false;
	}

	/**
	 * Delete All the Data related to Companies
	 *
	 * @return void
	 */
	public function deleteAllCompany()
	{
		$d = $this->driver;
		$arrayElement = $d->findElements(By::xPath("//input[@onclick ='Joomla.checkAll(this)']"));
		if (count($arrayElement) >= 1)
		{
			$this->checkAll();
			$d->findElement(By::xPath("//button[@class='btn btn-danger']"))->click();
		}
	}
} 