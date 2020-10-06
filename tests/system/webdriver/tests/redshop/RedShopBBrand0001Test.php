<?php
/**
 * @package    RedCore
 * @subpackage Model
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once 'JoomlaWebdriverTestCase.php';

/**
 * This class tests the  Brands: Add / Edit  Screen.
 *
 * @package     Aesir.E-Commerce.Test
 * @subpackage  Webdriver
 * @since       3.0
 */
class RedShopBBrand0001Test extends JoomlaWebdriverTestCase
{
	/**
	 * The menu name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuLinkName = 'RedShopBBrandsManagerPage';
	/**
	 * The menu group name being tested.
	 *
	 * @var     string
	 * @since   3.0
	 */
	protected $appMenuGroupName = 'RedSHOPB2B_Brand';

	/**
	 * Function to Create a New Brand
	 *
	 * @test
	 *
	 * @return void
	 */
	public function createBrand()
	{
		$rand = rand();
		$title = 'RedShopBrand' . $rand;
		$this->appTestPage->addBrand($title);
		$this->assertTrue($this->appTestPage->searchBrand($title), 'Brand Should Be Created');
		$this->appTestPage->deleteBrand($title);
	}

	/**
	 * Function to Update Brand
	 *
	 * @test
	 *
	 * @return void
	 */
	public function updateBrand()
	{
		$rand = rand();
		$title = 'RedShopBrand' . $rand;
		$newTitle = 'NewRedShopBrand' . $rand;
		$this->appTestPage->addBrand($title);
		$this->assertTrue($this->appTestPage->searchBrand($title), 'Brand Must Be Present');
		$this->appTestPage->editBrand($title, $newTitle);
		$this->assertTrue($this->appTestPage->searchBrand($newTitle), 'Brand Must be upDated');
		$this->appTestPage->deleteBrand($newTitle);
	}
}