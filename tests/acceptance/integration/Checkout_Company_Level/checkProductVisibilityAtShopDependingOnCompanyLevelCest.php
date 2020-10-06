<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Frontend\CategorySteps;
use Step\Acceptance\redshopb2b as redshopb2b;
class CheckProductVisibilityAtShopDependingOnCompanyLevelCest
{
	public $categoryWarehouse;
	public $productWarehouse;
	public $productAsService;

	public $category0;
	public $product0;

	public $company1;
	public $category1;
	public $product1;
	public $EmployeeWithLogin1;

	public $company1_A;
	public $EmployeeWithLogin1_A;

	public $company2;
	public $category2;
	public $product2;

	public $company2_A;
	public $EmployeeWithLogin2_A;


	/**
	 * Prepare the following structure
	 *
	 * +-------------------+--------------------+-------------------+------------------------+
	 * |      COMPANY      |   CATEGORY         |   PRODUCT         |   USER                 |
	 * +-------------------+--------------------+-------------------+------------------------+
	 * |                   |                    |                   |                        |
	 * | - Main Warehouse  | CategoryWarehouse  | productWarehouse  |                        |
	 * |                   |                    |                   |                        |
	 * |                   | CategoryWarehouse  | productAsService  |                        |
	 * |                   |                    |                   |                        |
	 * | - Main Company    | Category0          | product0          |                        |
	 * |                   |                    |                   |                        |
	 * |  - Company1       | Category1          | product1          |  EmployeeWithLogin1    |
	 * |                   |                    |                   |                        |
	 * |    - Company1_A   |                    |                   |  EmployeeWithLogin1_A  |
	 * |                   |                    |                   |                        |
	 * |  - Company2       | Category2          | product2          |                        |
	 * |                   |                    |                   |                        |
	 * |    - Company2_A   |                    |                   |  EmployeeWithLogin2_A  |
	 * +-------------------+--------------------+-------------------+------------------------+
	 */
	/**
	 * @param redshopb2b $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 */
	public function prepare(redshopb2b $I, \Codeception\Scenario $scenario)
	{
		$this->faker = Faker\Factory::create();

		$I->doFrontEndLogin();

		$this->categoryWarehouse['name'] = $this->faker->bothify('CategoryWarehouse ?##?');
		$I = new CategorySteps($scenario);
		$I->create($this->categoryWarehouse['name'],'save');
		$this->productWarehouse['name'] = $this->faker->bothify('ProductWarehouse ?##?');
		$I = new ProductSteps($scenario);
		$I->create(
				$this->productWarehouse['name'],
				$this->faker->bothify('SKUVisibleProduct1 ?##?'),
				$this->categoryWarehouse['name'],
				1,
				1,null, 'save&close');
		
		$I->create(
			$this->productWarehouse['name'],
			$this->faker->bothify('2SKUVisibleProduct ?##?'),
			$this->categoryWarehouse['name'],
			1,
			1,null, 'save&close');
		$this->productAsService['name'] = $this->faker->bothify('ProductAsService ?##?');
		
		$I->create(
			$this->productAsService['name'],
			$this->faker->bothify('LevelCest ?##?'),
			$this->categoryWarehouse['name'],
			1,1,
			'Main Warehouse',
			'save&close',
			['As Service' => 'Yes']
		);
		
		
		$this->category0['name'] = $this->faker->bothify('Category0 ?##?');
		$I->createRedshopbCategory(
				$this->category0['name'],
				'Main Company',
				['Description' => 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest']
		);
		$this->product0['name'] = $this->faker->bothify('product0 ?##?');
		$I->create($this->product0['name'],$this->faker->bothify('SKU)) value ?####?'),
			$this->category0['name'], 2, 2, null, 'save&close');

		$this->company1['name'] = $this->faker->bothify('Company1 ?##?');
		$this->company1['number'] = $this->faker->bothify('Number ?##?');
		$this->company1['id'] = $I->createRedshopbCompany(
				$this->company1['name'],
				$this->company1['number'],
				'address',
				$this->faker->postcode,
				$this->faker->city,
				'Denmark',
				'Main Company',
				['Name Second Line' => 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest']
		);
		$this->category1['name'] = $this->faker->bothify('Category1 ?##?');
		$I->createRedshopbCategory(
				$this->category1['name'],
				'- (' . $this->company1['number'] . ') ' . $this->company1['name'] . ' ' . 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest',
				['Description' => 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest']
		);
		$this->product1['name'] = $this->faker->bothify('product1 ?##?');
		$I->wantTo('Create new product');
		$I->wait(1);
		$I->create($this->product1['name'], $this->faker->bothify('Product 1 SKU ?###?'),
			$this->category1['name'], 3, 3,
			'(' . $this->company1['number'] . ') ' . $this->company1['name'] . ' ' . 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest', 'save&close');
		
		$this->EmployeeWithLogin1['name'] = $this->faker->bothify('EmployeeWithLogin1 ?##?');
		$I->wait(1);
		$I->createRedshopbUserEmployeeWithLogin(
				$this->EmployeeWithLogin1['name'],
				$this->faker->email,
				'- (' . $this->company1['number'] . ') ' . $this->company1['name'] . ' ' . 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest',
				['Name Second Line' => 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest']
		);

		$this->company1_A['name'] = $this->faker->bothify('Company1_A ?##?');
		$this->company1_A['number'] = $this->faker->bothify('companyNumber_A ?##?');
		$I->wait(1);
		$this->company1_A['id'] = $I->createRedshopbCompany(
				$this->company1_A['name'],
				$this->company1_A['number'],
				'address',
				$this->faker->postcode,
				$this->faker->city,
				'Denmark',
				'- ' . $this->company1['name'] . ' ' . 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest',
				['Name Second Line' => 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest']
		);
		$this->EmployeeWithLogin1_A['name'] = $this->faker->bothify('EmployeeWithLogin1_A ?##?');
		$I->wait(1);
		$I->createRedshopbUserEmployeeWithLogin(
				$this->EmployeeWithLogin1_A['name'],
				$this->faker->email,
				'- - (' . $this->company1_A['number'] . ') ' . $this->company1_A['name'] . ' ' . 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest',
				['Name Second Line' => 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest']
		);

		$this->company2['name'] = $this->faker->bothify('Company2 ?##?');
		$this->company2['number'] = $this->faker->bothify('numberCompany2 ?##?');
		$I->wait(1);
		$this->company2['id'] = $I->createRedshopbCompany(
				$this->company2['name'],
				$this->company2['number'],
				'address',
				$this->faker->postcode,
				$this->faker->city,
				'Denmark',
				'Main Company',
				['Name Second Line' => 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest']
		);
		$this->category2['name'] = $this->faker->bothify('Category2 ?##?');
		$I->createRedshopbCategory(
				$this->category2['name'],
			'- (' . $this->company2['number'] . ') ' . $this->company2['name'] . ' ' . 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest',
				['Description' => 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest']
		);
		$this->product2['name'] = $this->faker->bothify('product2 ?##?');
		
		$I->wantTo('Create new product');
		$I->create($this->product2['name'],
			$this->faker->bothify('CompanyLevelCest ?##?'),
			$this->category2['name'],
		4, 4,
			'(' . $this->company2['number'] . ') ' . $this->company2['name'] . ' ' . 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest',
		'save&close');

		$this->company2_A['name'] = $this->faker->bothify('Company2_A ?##?');
		$this->company2_A['number'] = $this->faker->bothify('Company2_ANumber ?##?');
		$this->company2_A['id'] = $I->createRedshopbCompany(
				$this->company2_A['name'],
				$this->company2_A['number'],
				'address',
				$this->faker->postcode,
				$this->faker->city,
				'Denmark',
				'- ' . $this->company2['name'] . ' ' . 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest',
				['Name Second Line' => 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest']
		);
		$this->EmployeeWithLogin2_A['name'] = $this->faker->bothify('EmployeeWithLogin2_A ?##?');
		$I->createRedshopbUserEmployeeWithLogin(
				$this->EmployeeWithLogin2_A['name'],
				$this->faker->email,
				'- - (' . $this->company2_A['number'] . ') ' . $this->company2_A['name'] . ' ' . 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest',
				['Name Second Line' => 'CheckProductVisibilityAtShopDependingOnCompanyLevelCest']
		);
	}
	
	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 */
	public function checkCompany1CanSeeProductsFromMainWarehouseAndMainCompanyAndCompany1(\AcceptanceTester $I)
	{
		$I->wantToTest('login in as EmployeeWithLogin1 (company1) and I can only see products (and categories) from: Main Warehouse, Main Company');
		$I->doFrontEndLogin($this->EmployeeWithLogin1['name'], $this->EmployeeWithLogin1['name']);
		$I->amOnPage('index.php?option=com_redshopb&view=shop&layout=categories');
		$I->waitForElement(['link' => $this->categoryWarehouse['name']], 60);
		$I->seeLink($this->categoryWarehouse['name']);
		$I->seeLink($this->category0['name']);
		$I->dontseeLink($this->category1['name']);
		$I->dontSeeLink($this->category2['name']);
	}
	
	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 */
	public function checkCompany1_ACanSeeProductsFromMainWarehouseAndMainCompanyAndCompany1(\AcceptanceTester $I)
	{
		$I->wantToTest('login in as EmployeeWithLogin1_A (company1_A) and I can only see products (and categories) from: Main Warehouse, Main Company and Company1');
		$I->doFrontEndLogin($this->EmployeeWithLogin1_A['name'], $this->EmployeeWithLogin1_A['name']);
		$I->amOnPage('index.php?option=com_redshopb&view=shop&layout=categories');
		$I->waitForElement(['link' => $this->categoryWarehouse['name']], 60);
		$I->seeLink($this->categoryWarehouse['name']);
		$I->seeLink($this->category0['name']);
		$I->seeLink($this->category1['name']);
		$I->dontSeeLink($this->category2['name']);
	}
	
	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 */
	public function checkCompany2_ACanSeeProductsFromMainWarehouseAndMainCompanyAndCompany2(\AcceptanceTester $I)
	{
		$I->wantToTest('login in as EmployeeWithLogin2_A (company1_A) and I can only see products (and categories) from: Main Warehouse, Main Company and Company1');
		$I->doFrontEndLogin($this->EmployeeWithLogin2_A['name'], $this->EmployeeWithLogin2_A['name']);
		$I->amOnPage('index.php?option=com_redshopb&view=shop&layout=categories');
		$I->waitForElement(['link' => $this->categoryWarehouse['name']], 60);
		$I->seeLink($this->categoryWarehouse['name']);
		$I->seeLink($this->category0['name']);
		$I->dontSeeLink($this->category1['name']);
		$I->seeLink($this->category2['name']);
	}
	
	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 */
	public function checkCompany2_ACantSeeProductAsService(\AcceptanceTester $I)
	{
		$I->wantToTest('login in as EmployeeWithLogin2_A (company2_A) and I cannot see marked as service (accessory)');
		$I->doFrontEndLogin($this->EmployeeWithLogin2_A['name'], $this->EmployeeWithLogin2_A['name']);
		$I->amOnPage('index.php?option=com_redshopb&view=shop&layout=categories');
		$I->waitForElement(['link' => $this->categoryWarehouse['name']], 60);
		$I->click(['link' => $this->categoryWarehouse['name']]);
		$I->waitForText($this->categoryWarehouse['name'], 60, ['css' => 'h1']);
		$I->seeLink($this->productWarehouse['name']);
		$I->dontSeeLink($this->productAsService['name']);
	}

	public function checkCompany2_ACantSeeProductAsFee(\AcceptanceTester $I)
	{
		// @todo: once RSBTB-3080 get's fixed
	}

	public function checkCompany2_ACantSeeExclusiveProductsForCompany1_A(\AcceptanceTester $I, $scenario)
	{
		$scenario->skip();
		// @todo: this test is not yet created.
		/*
		 * Rule 3: Exclude exclusive products from other companies
		 * When setting up a product, you can restrict it to appear to just some companies (technically that's the
		 * redshopb_product_company) table. When some product is restricted to some company, that product (or their
		 * categories and filters) should not appear to a logged in user of a different company from the ones set up.
		 */
	}
	
	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->doFrontEndLogin();

		$I->deleteRedshopbProduct($this->productWarehouse['name']);
		$I->deleteRedshopbProduct($this->productAsService['name']);
		$I->deleteRedshopbProduct($this->product0['name']);
		$I->deleteRedshopbProduct($this->product1['name']);
		$I->deleteRedshopbProduct($this->product2['name']);

		$I->deleteRedshopbCategory($this->category0['name']);
		$I->deleteRedshopbCategory($this->category1['name']);
		$I->deleteRedshopbCategory($this->category2['name']);
		$I->deleteRedshopbCategory($this->categoryWarehouse['name']);

		$I->deleteRedshopbCompany($this->company1_A['name']);
		$I->deleteRedshopbCompany($this->company1['name']);
		$I->deleteRedshopbCompany($this->company2_A['name']);
		$I->deleteRedshopbCompany($this->company2['name']);
	}
}
