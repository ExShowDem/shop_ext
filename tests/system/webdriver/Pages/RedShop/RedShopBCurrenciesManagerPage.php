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
 * Page class for the back-end RedShop Component
 *
 * @package     RedShop.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class RedShopBCurrenciesManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $waitForXpath =  "//h1[text()='Currencies']";

	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_redshopb&view=currencies';

	/**
	 * Function to add currency using the redshopb currency manager
	 *
	 * @param string $name		Name of the Sample Currency
	 *
	 * @param string $alpha3	Sample Alpha3 Value
	 *
	 * @param string $numeric	Sample Numeric Value
	 *
	 * @param string $decimals	Sample Decimal Value
	 *
	 * @param string $symbol	Symbol for the Sample Currency
	 *
	 * @return RedShopBCurrenciesManagerPage
	 */
	public function addCurrency($name='Testing Currency', $alpha3='Sample', $numeric='5', $decimals='2', $symbol='Rs')
	{
		$d = $this->driver;
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('currency.add')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"),10);
		$nameField = $d->findElement(By::xPath("//input[@id='jform_name']"));
		$nameField->clear();
		$nameField->sendKeys($name);
		$alpha3Field = $d->findElement(By::xPath("//input[@id='jform_alpha3']"));
		$alpha3Field->clear();
		$alpha3Field->sendKeys($alpha3);
		$numericField = $d->findElement(By::xPath("//input[@id='jform_numeric']"));
		$numericField->clear();
		$numericField->sendKeys($numeric);
		$decimalField = $d->findElement(By::xPath("//input[@id='jform_decimals']"));
		$decimalField->clear();
		$decimalField->sendKeys($decimals);
		$symbolField = $d->findElement(By::xPath("//input[@id='jform_symbol']"));
		$symbolField->clear();
		$symbolField->sendKeys($symbol);
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('currency.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_currencies']"),10);
	}

	/**
	 * Function to Update/Edit Currency details using RedshopB2B Currency Manager
	 *
	 * @param $field	Name of the field which is to be updated
	 *
	 * @param $newValue	New value of the field
	 *
	 * @param $name	Name of the Currency for which changes are to be done
	 *
	 * @return RedShopBCurrenciesManagerPage
	 */
	public function updateCurrency($field, $newValue, $name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_currencies']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//tbody/tr/td[3]/a[contains(text(),'" . $name . "')]"), 10);
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" .$row ."']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" .$row ."']"))->click();
		$d->findElement(By::xPath("//button[@class='btn'][1]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"), 10);
		switch ($field){
			case "Name":
				$nameField = $d->findElement(By::xPath("//input[@id='jform_name']"));
				$nameField->clear();
				$nameField->sendKeys($newValue);
				break;
		    case "Alpha3":
				$alpha3Field = $d->findElement(By::xPath("//input[@id='jform_alpha3']"));
				$alpha3Field->clear();
				$alpha3Field->sendKeys($newValue);
				break;
			case "Numeric":
				$numericField = $d->findElement(By::xPath("//input[@id='jform_numeric']"));
				$numericField->clear();
				$numericField->sendKeys($newValue);
				break;
			case "Decimals":
				$decimalField = $d->findElement(By::xPath("//input[@id='jform_decimals']"));
				$decimalField->clear();
				$decimalField->sendKeys($newValue);
				break;
			case "Symbol":
				$symbolField = $d->findElement(By::xPath("//input[@id='jform_symbol']"));
				$symbolField->clear();
				$symbolField->sendKeys($symbol);
				break;
		}
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('currency.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_currencies']"), 10);
	}

	/**
	 * Search for a Currency
	 *
	 * @param	string	$name	Test Currency Title
	 *
	 * @return  boolean
	 */
	public function searchCurrency($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_currencies']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" .$row ."']"), 10);
		$arrayElement = $this->driver->findElements(By::xPath("//tbody/tr/td[3]/a[contains(text(),'" . $name . "')]"));
		if(count($arrayElement))
		{
			return true;
		}
		else
			return false;
	}

	/**
	 * Delete a Currency
	 *
	 * @param	string	$name	Test Currency Title
	 *
	 * @return	boolean
	 */
	public function deleteCurrency($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_currencies']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" .$row ."']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" .$row ."']"))->click();
		$d->findElement(By::xPath("//button[@class='btn btn-danger']"))->click();
	}

	/**
	 * Get state  of a Currency
	 *
	 * @param	string	$name	Currency Title field
	 *
	 * @return	string	$result //Published or Unpublished
	 */
	public function getState($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_currencies']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$result = false;
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[2]//a"))->getAttribute(@onclick);
		if (strpos($text, 'currencies.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'currencies.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}

	/**
	 * Change state of a Currency item in the Currency Manager
	 *
	 * @param string   $name	Currency Title field
	 *
	 * @param string   $state	State of the Tag
	 *
	 * @return  void
	 */
	public function changeCurrencyState($name, $state = 'published')
	{
		$d = $this->driver;
		$this->searchCurrency($name);
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" .$row ."']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" .$row ."']"))->click();
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
		$this->searchCurrency(" ");
	}
}
