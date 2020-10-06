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
 * Page class for the back-end Products Menu.
 *
 * @package    RedShopb.Test
 * @subpackage Webdriver
 * @since      1.0
 */

class RedShopBProductsManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 *
	 * @since    1.0
	 */
	protected $waitForXpath = "//h1[text()='Products']";
	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_redshopb&view=products';

	/**
	 * Function to create a New Product
	 *
	 * @param string $name   Name of the Product That we want to create
	 *
	 * @param string $status Status of the product
	 *
	 * @param string $price  Price of the Test Product
	 *
	 * @param string $sku    SKU for the test Product
	 *
	 * @return RedShopBProductsManagerPage
	 */
	public function addProduct($name = 'Sample Product', $status = 'Published', $price = '100', $sku = '12345')
	{
		$d = $this->driver;
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('product.add')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='jform_name']"), 10);
		$nameField = $d->findElement(By::xPath("//input[@id='jform_name']"));
		$nameField->clear();
		$nameField->sendKeys($name);
		$priceField = $d->findElement(By::xPath("//input[@id='jform_price']"));
		$priceField->clear();
		$priceField->sendKeys($price);
		$skuField = $d->findElement(By::xPath("//input[@id='jform_sku']"));
		$skuField->clear();
		$skuField->sendKeys($sku);
		if ($status == 'Published')
		{
			$d->findElement(By::xPath("//label[contains(text(),'Published')]"))->click();
		}
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('product.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_products']"), 10);
	}

	/**
	 * Function to Edit a Product
	 *
	 * @param $field      Name of the Field which is to be modified
	 *
	 * @param $newValue   New Value for the Field
	 *
	 * @param $name       Name of the Product which is getting edited
	 *
	 * @return RedShopBProductsManagerPage
	 */
	public function editProduct($field, $newValue, $name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_products']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//tbody/tr/td[5]/a/span[contains(text(),'" . $name . "')]"), 10);
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
			case "Price":
				$priceField = $d->findElement(By::xPath("//input[@id='jform_price']"));
				$priceField->clear();
				$priceField->sendKeys($newValue);
				break;
			case "SKU":
				$skuField = $d->findElement(By::xPath("//input[@id='jform_sku']"));
				$skuField->clear();
				$skuField->sendKeys($newValue);
				break;
		}
		$d->findElement(By::xPath("//button[@onclick=\"Joomla.submitbutton('product.save')\"]"))->click();
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='filter_search_products']"), 10);
	}

	/**
	 * Function to Delete a Product
	 *
	 * @param $name Name of the Product to be deleted
	 *
	 * @return void
	 */
	public function deleteProduct($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_products']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn btn-danger']"))->click();
	}

	/**
	 * Function to Get State of a Product
	 *
	 * @param $name Name of the Product
	 *
	 * @return string
	 */
	public function getState($name)
	{
		$d = $this->driver;
		$result = false;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_products']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		$row = $this->getRowNumber($name);
		$text = $this->driver->findElement(By::xPath("//tbody/tr[" . $row . "]/td[2]//a"))->getAttribute(@onclick);
		if (strpos($text, 'products.unpublish') > 0)
		{
			$result = 'published';
		}
		if (strpos($text, 'products.publish') > 0)
		{
			$result = 'unpublished';
		}
		return $result;
	}

	/**
	 * Change State of the Product
	 *
	 * @param string $name  Name of the Product
	 *
	 * @param string $state State of the Product
	 *
	 * @return void
	 */
	public function changeProductState($name, $state = 'published')
	{
		$d = $this->driver;
		$this->searchProduct($name);
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
		$this->searchProduct(" ");
	}

	/**
	 * Function to Search for the Product
	 *
	 * @param $name  Name of the Product
	 *
	 * @return bool  Result True, False
	 */
	public function searchProduct($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_products']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		sleep(5);
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$arrayElement = $this->driver->findElements(By::xPath("//tbody/tr/td[5]/a/span[contains(text(),'" . $name . "')]"));
		if (count($arrayElement))
		{
			return true;
		}
		else
			return false;
	}

	/**
	 * Function to Discontinue a Product
	 *
	 * @param $name Name of the Product
	 *
	 * @return void
	 */
	public function discontinueProduct($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_products']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		sleep(5);
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$d->findElement(By::xPath("//input[@id='cb" . $row . "']"))->click();
		$d->findElement(By::xPath("//button[@class='btn btn-warning']"))->click();
	}

	/**
	 * Function to Verify Discontinue
	 *
	 * @param $name Name of the Product
	 *
	 * @return bool Return Value
	 */
	public function isDiscontinued($name)
	{
		$d = $this->driver;
		$searchField = $d->findElement(By::xPath("//input[@id='filter_search_products']"));
		$searchField->clear();
		$searchField->sendKeys($name);
		$d->findElement(By::xPath("//button[@data-original-title='Search' or @title='Search']"))->click();
		sleep(5);
		$row = $this->getRowNumber($name) - 1;
		$d->waitForElementUntilIsPresent(By::xPath("//input[@id='cb" . $row . "']"), 10);
		$value = $d->findElement(By::xPath("//tbody/tr/td[3]/span"))->getText();
		if ($value == 'Yes')
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}