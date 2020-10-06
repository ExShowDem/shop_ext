<?php
/**
 * @package RedCore
 * @subpackage Model
 * @copyright Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Currency: Add / Edit  Screen.
 *
 * @package     Aesir.E-Commerce.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class RedShopBCurrency0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The menu name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuLinkName = 'RedShopBCurrenciesManagerPage';

	/**
	 * The menu group name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuGroupName = 'RedSHOPB2B_Currency';

	/**
	 * Function to create Currency using Currency Manager
	 *
	 * @test
	 *
	 * @return void
	 */
	public function createCurrency()
	{
		$rand = rand();
		$name = 'Testing Redshop' . $rand;
		$alpha3 = $rand . 'Testing Alpha';
		$numeric = $rand . '10';
		$decimals = '3';
		$symbol = 'Rs';
		$this->appTestPage->addCurrency($name, $alpha3, $numeric, $decimals, $symbol);
		$this->assertTrue($this->appTestPage->searchCurrency($name), 'Currency RedShop Must be Present');
		$this->appTestPage->deleteCurrency($name);
	}

	/**
	 * Function to Edit Currency
	 *
	 * @test
	 *
	 * @return void
	 */
	public function editCurrency()
	{
		$rand = rand();
		$name = 'Testing Redshop' . $rand;
		$newName = 'Testing RedShop Again' . $rand;
		$alpha3 = 'Testing Alpha' . $rand;
		$numeric = '10' . $rand;
		$decimals = '3';
		$symbol = 'Rs';
		$this->appTestPage->addCurrency($name, $alpha3, $numeric, $decimals, $symbol);
		$this->appTestPage->updateCurrency('Name', $newName, $name);
		$this->assertTrue($this->appTestPage->searchCurrency($newName), 'New Category Name Must be Present');
		$this->appTestPage->deleteCurrency($newName);
	}

	/**
	 * Function to change the state of a Currency
	 *
	 * @test
	 *
	 * @return void
	 */
	public function changeState()
	{
		$rand = rand();
		$name = 'Testing Redshop' . $rand;
		$alpha3 = 'Testing Alpha' . $rand;
		$numeric = '10' . $rand;
		$decimals = '3';
		$symbol = 'Rs';
		$this->appTestPage->addCurrency($name, $alpha3, $numeric, $decimals, $symbol);
		$currentState = $this->appTestPage->getState($name);
		$this->assertEquals($currentState, 'published', 'Initial State Must be Published');
		$this->appTestPage->changeCurrencyState($name, 'unpublished');
		$currentState = $this->appTestPage->getState($name);
		$this->assertEquals($currentState, 'unpublished', 'State Must be Unpublished');
		$this->appTestPage->deleteCurrency($name);
	}
}
