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
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Page class for the back-end shops Menu.
 *
 * @package    RedShopb.Test
 * @subpackage Webdriver
 * @since      1.0
 */

class RedShopBShopsManagerPage extends AdminManagerPage
{
	/**
	 * XPath string used to uniquely identify this page
	 *
	 * @var    string
	 *
	 * @since    1.0
	 */
	protected $waitForXpath = "//h1[contains(text(),'Shop')]";
	/**
	 * URL used to uniquely identify this page
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $url = 'administrator/index.php?option=com_redshopb&view=shop';

	/**
	 * Function to Verify Company in Shop View
	 *
	 * @param $companyName Name of the Company which is to be looked in the shop View
	 *
	 * @return bool
	 */
	public function verifyCompany($companyName)
	{
		$d = $this->driver;
		$arrayElement = $d->findElements(By::xPath("//a[contains(text(),'" . $companyName . "')]"));
		if (count($arrayElement) >= 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}