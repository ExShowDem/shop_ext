<?php
/**
 * @package RedCore
 * @subpackage Model
 * @copyright Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Products: Add / Edit  Screen.
 *
 * @package     Aesir.E-Commerce.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class RedShopBProduct0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The menu name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuLinkName = 'RedShopBProductsManagerPage';

	/**
	 * The menu group name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuGroupName = 'RedSHOPB2B_Product';

	/**
	 * Function to Create a Product
	 *
	 * @test
	 *
	 * @return void
	 */
	public function createProduct()
	{
		$rand = rand();
		$name = 'Sample Product' . $rand;
		$price = $rand;
		$sku = 'SKU' . $rand;
		$status = 'Published';
		$this->appTestPage->addProduct($name, $status, $price, $sku);
		$this->assertTrue($this->appTestPage->searchProduct($name), 'Product Must be Present');
		$this->appTestPage->deleteProduct($name);
	}

	/**
	 * Function to Test Update Functionality
	 *
	 * @test
	 *
	 * @return void
	 */
	public function updateProduct()
	{
		$rand = rand();
		$name = 'Sample Product' . $rand;
		$newName = 'Updated Name' . $rand;
		$price = $rand;
		$sku = 'SKU' . $rand;
		$status = 'Published';
		$this->appTestPage->addProduct($name, $status, $price, $sku);
		$this->assertTrue($this->appTestPage->searchProduct($name), 'Product Must be Present');
		$this->appTestPage->editProduct('Name', $newName, $name);
		$this->assertTrue($this->appTestPage->searchProduct($newName), 'New Product Must be Present');
		$this->appTestPage->deleteProduct($newName);
	}

	/**
	 * Function to Change the State for the Product
	 *
	 * @test
	 *
	 * @return void
	 */
	public function changeState()
	{
		$rand = rand();
		$name = 'Sample Product' . $rand;
		$price = $rand;
		$sku = 'SKU' . $rand;
		$status = 'Published';
		$this->appTestPage->addProduct($name, $status, $price, $sku);
		$this->assertTrue($this->appTestPage->searchProduct($name), 'Product Must be Present');
		$currentState = $this->appTestPage->getState($name);
		$this->assertEquals($currentState, 'published', 'Initial State Must be Published');
		$this->appTestPage->changeProductState($name, 'unpublished');
		$currentState = $this->appTestPage->getState($name);
		$this->assertEquals($currentState, 'unpublished', 'State Must be Unpublished');
		$this->appTestPage->deleteProduct($name);
	}

	/**
	 * Function to test Discontinue Feature
	 *
	 * @test
	 *
	 * @return void
	 */
	public function Discontinue()
	{
		$rand = rand();
		$name = 'Sample Product' . $rand;
		$price = $rand;
		$sku = 'SKU' . $rand;
		$status = 'Published';
		$this->appTestPage->addProduct($name, $status, $price, $sku);
		$this->assertTrue($this->appTestPage->searchProduct($name), 'Product Must be Present');
		$this->appTestPage->discontinueProduct($name);
		$this->assertTrue($this->appTestPage->isDiscontinued($name), 'Product Must have Discontinued');
		$this->appTestPage->deleteProduct($name);
	}
}