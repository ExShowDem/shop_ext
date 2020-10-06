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
 * Page class for the back-end Brands RedShopb.
 *
 * @package    RedShopb.Test
 * @subpackage Webdriver
 * @since      1.0
 */
class RedShopBBrandsManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 *
	 * @since    1.0
	 */
	protected $waitForXpath = "//h1[text()='Brands']";
	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_redshopb&view=brands';

	/**
	 * Function to Add a new Brand
	 *
	 * @param string $title Name of the Brand that we are creating
	 *
	 * @return RedShopBBrandsManagerPage
	 */
	public function addBrand($title = 'Testing Brands')
	{
		$d = $this->driver;
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('brand.add')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"), 10);
		$nameField = $d->findElement(By::xPath("//input[@id='jform_name']"));
		$nameField->clear();
		$nameField->sendKeys($title);
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('brand.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_brands']"), 10);
	}

	/**
	 * Function to Update the Brand Title
	 *
	 * @param $title    Title of the Brand which we are going to edit
	 *
	 * @param $newTitle New title for the Brand
	 *
	 * @return RedShopBBrandsManagerPage
	 */
	public function editBrand($title, $newTitle)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_brands']"));
		$searchField->clear();
		$searchField->sendKeys($title);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//tbody/tr/td[2]/a[contains(text(),'" . $title . "')]"), 10);
		$row = $this->getRowNumber($title) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn'][1]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"), 10);
		$nameField = $d->findElement(By::xPath("//input[@id='jform_name']"));
		$nameField->clear();
		$nameField->sendKeys($newTitle);
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('brand.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_brands']"), 10);
	}

	/**
	 * Function to delete a Brand
	 *
	 * @param $title Brand which we are going to delete
	 *
	 * @return void
	 */
	public function deleteBrand($title)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_brands']"));
		$searchField->clear();
		$searchField->sendKeys($title);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($title) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn btn-danger']"))->click();
	}

	/**
	 * Function to Search for a Brand
	 *
	 * @param $title Name of the Brand that we are searching
	 *
	 * @return bool
	 */
	public function searchBrand($title)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_brands']"));
		$searchField->clear();
		$searchField->sendKeys($title);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($title) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		/** @var $arrayElement Array of Elements */
		$arrayElement = $this->driver->findElements(By::xPath("//tbody/tr/td[2]/a[contains(text(),'" . $title . "')]"));
		if (count($arrayElement))
		{
			return true;
		}
		else
			return false;
	}
}
