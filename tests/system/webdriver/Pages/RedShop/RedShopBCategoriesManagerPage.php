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
class RedShopBCategoriesManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 *
	 * @since    1.0
	 */
	protected $waitForXpath = "//h1[text()='Categories']";
	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_redshopb&view=categories';

	/**
	 * Add a new Category using redShop Categories Manager
	 *
	 * @param string $name //Test Category Title
	 *
	 * @return    RedShopBCategoriesManagerPage
	 */
	public function addCategory($name = 'Testing Redshop')
	{
		$d = $this->driver;
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('category.add')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_title']"), 10);
		$nameField = $d->findElement(By::xPath("//input[@id='jform_title']"));
		$nameField->clear();
		$nameField->sendKeys($name);
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('category.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_categories']"), 10);
	}

	/**
	 * update a category using redShop Categories Manager
	 *
	 * @param string $name    Test Category Title
	 *
	 * @param string $newName New Edited Title for the category
	 *
	 * @return  RedShopBCategoriesManagerPage
	 */
	public function updateCategory($name, $newName)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_categories']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//tbody/tr/td[3]/a[contains(text(),'" . $name . "')]"), 10);
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn'][1]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_title']"), 10);
		$nameField = $d->findElement(By::xPath("//input[@id='jform_title']"));
		$nameField->clear();
		$nameField->sendKeys($newName);
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('category.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_categories']"), 10);
	}

	/**
	 * Delete a Category
	 *
	 * @param string $name Test Category Title
	 *
	 * @return  boolean
	 */
	public function deleteCategory($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_categories']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn btn-danger']"))->click();
	}

	/**
	 * Get state  of a Category
	 *
	 * @param    string $name Category Title field
	 *
	 * @return    string    //Published or Unpublished
	 */
	public function getState($name)
	{
		$d = $this->driver;
		$result = false;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_categories']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[2]//a"))->getAttribute(@onclick);
		if (strpos($text, 'categories.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'categories.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}

	/**
	 * Change state of a Category item in the Category Manager
	 *
	 * @param string $name  Category Title field
	 *
	 * @param string $state State of the Tag
	 *
	 * @return  void
	 */
	public function changeCategoryState($name, $state = 'published')
	{
		$d = $this->driver;
		$this->searchCategory($name);
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
		$this->searchCategory(" ");
	}

	/**
	 * Search for a  Category
	 *
	 * @param string $name Test Category Title
	 *
	 * @return  boolean
	 */
	public function searchCategory($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_categories']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		/** @var $arrayElement Array of Elements */
		$arrayElement = $this->driver->findElements(By::xPath("//tbody/tr/td[3]/a[contains(text(),'" . $name . "')]"));
		if (count($arrayElement))
		{
			return true;
		}
		else
			return false;
	}
}
