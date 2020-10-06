<?php
use SeleniumClient\By;
use SeleniumClient\SelectElement;
use SeleniumClient\WebDriver;
use SeleniumClient\WebDriverWait;
use SeleniumClient\DesiredCapabilities;
use SeleniumClient\WebElement;

/**
 * @package    RedCore
 * @subpackage Model
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Page class for the back-end Wardrobes Menu.
 *
 * @package    RedShopb.Test
 * @subpackage Webdriver
 * @since      1.0
 */
class RedShopBWardrobesManagerPage extends AdminManagerPage
{
	/**
	 * @var string Xpath to Uniquely Identify this Page
	 */
	protected $waitForXpath = "//h1[text()='Wardrobes']";

	/**
	 * @var string URL to Uniquely Identify the page
	 */
	protected $url = 'administrator/index.php?option=com_redshopb&view=wardrobes';

	/**
	 * Function to Add a Wardrobe
	 *
	 * @param string $name       Name of the Wardrobe
	 *
	 * @param string $company    Name of the Company for the Wardrobe
	 *
	 * @param string $department Name of the Department for the new Wardrobe
	 *
	 * @return RedShopBWardrobesManagerPage
	 */
	public function addWardrobe($name = 'Sample Wardrobe', $company = 'Sample Company', $department = 'Main Department')
	{
		$d = $this->driver;
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('wardrobe.create')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"), 10);
		$nameField = $d->findElement(By::xPath("//input[@id='jform_name']"));
		$nameField->clear();
		$nameField->sendKeys($name);
		$d->findElement(By::xPath("//div[@id='jform_company_id_chzn']/a"))->click();
		$d->findElement(By::xPath("//div[@id='jform_company_id_chzn']//ul[@class='chzn-results']/li[contains(.,'" . $company . "')]"))->click();
		sleep(5);
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('wardrobe.createNext')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@class='default']"), 10);
		$departmentField = $d->findElement(By::xPath("//input[@class='default']"));
		$departmentField->sendKeys($department);
		sleep(5);
		$d->findElement(By::xPath("//li[@id='jform_department_ids_chzn_o_0']"))->click();
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('wardrobe.createNext')\"]"))->click();
		sleep(5);
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('wardrobe.createCancel')\"]"))->click();
		sleep(2);
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('wardrobe.cancel')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_wardrobes']"), 10);
	}

	/**
	 * Function to Delete a Wardrobe
	 *
	 * @param $name Name of the Wardrobe
	 *
	 * @return void
	 */
	public function deleteWardrobe($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_wardrobes']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn btn-danger']"))->click();
	}

	/**
	 * Function to get the State of a Wardrobe
	 *
	 * @param $name Name of the wardrobe
	 *
	 * @return string
	 */
	public function getState($name)
	{
		$d = $this->driver;
		$result = false;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_wardrobes']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[2]//a"))->getAttribute(@onclick);
		if (strpos($text, 'wardrobes.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'wardrobes.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}

	/**
	 * Function to Search for a wardrobe
	 *
	 * @param $name Name of the wardrobe
	 *
	 * @return bool
	 */
	public function searchWardrobe($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_wardrobes']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$arrayElement = $this->driver->findElements(By::xPath("//tbody/tr/td[3]/a[contains(text(),'" . $name . "')]"));
		if (count($arrayElement))
		{
			return true;
		}
		else
			return false;
	}

	/**
	 * Function to Edit Wardrobe
	 *
	 * @param $field    Name of the Field which is to be Changed
	 *
	 * @param $newValue Value for the Field
	 *
	 * @param $name     Name of the wardrobe
	 *
	 * @return RedShopBWardrobesManagerPage
	 */
	public function editWardrobe($field, $newValue, $name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_wardrobes']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//tbody/tr/td[3]/a[contains(text(),'" . $name . "')]"), 10);
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
			case "Status":
				if ($newValue == 'Published')
				{
					$d->findElement(By::xPath("//label[contains(text(),'Published')]"))->click();
				}
				else
				{
					$d->findElement(By::xPath("//label[contains(text(),'Unpublished')]"))->click();
				}
				break;
		}
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('wardrobe.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_wardrobes']"), 10);
	}
}