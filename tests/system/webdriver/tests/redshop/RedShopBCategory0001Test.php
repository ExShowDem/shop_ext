<?php
/**
 * @package RedCore
 * @subpackage Model
 * @copyright Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Category: Add / Edit  Screen.
 *
 * @package     Aesir.E-Commerce.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class RedShopBCategory0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The menu name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuLinkName = 'RedShopBCategoriesManagerPage';

	/**
	 * Function for creating Category
	 *
	 * @test
	 *
	 * @return void
	 */
	public function createCategory()
	{
		$rand = rand();
		$name = 'Testing Redshop' . $rand;
		$this->appTestPage->addCategory($name);
		$this->assertTrue($this->appTestPage->searchCategory($name), 'Category RedShop Must be Present');
		$this->appTestPage->deleteCategory($name);
	}

	/**
	 * Function to Test Editing of a category
	 *
	 * @test
	 *
	 * @return void
	 */
	public function editCategory()
	{
		$rand = rand();
		$name = 'Testing Redshop' . $rand;
		$newName = 'Testing RedShop Again' . $rand;
		$this->appTestPage->addCategory($name);
		$this->appTestPage->updateCategory($name, $newName);
		$this->assertTrue($this->appTestPage->searchCategory($newName), 'New Category Name Must be Present');
		$this->appTestPage->deleteCategory($newName);
	}

	/**
	 * function to change the State of a Category
	 *
	 * @test
	 *
	 * @return void
	 */
	public function changeState()
	{
		$rand = rand();
		$name = 'Testing RedShop' . $rand;
		$this->appTestPage->addCategory($name);
		$currentState = $this->appTestPage->getState($name);
		$this->assertEquals($currentState, 'published', 'Initial State Must be Published');
		$this->appTestPage->changeCategoryState($name, 'unpublished');
		$currentState = $this->appTestPage->getState($name);
		$this->assertEquals($currentState, 'unpublished', 'State Must be Unpublished');
		$this->appTestPage->deleteCategory($name);
	}
}
