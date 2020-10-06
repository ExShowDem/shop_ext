<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbStockroom_product100FilteringCest
{
	/**
	 * @var Category to be used in the test
	 */
	public $category;

	/**
	 * @var Product to be used in the test
	 */
	public $productA;

	/**
	 * @var Product to be used in the test
	 */
	public $productB;

	/**
	 * @var Stockroom to be used in the test
	 */
	public $stockroomA;

	/**
	 * @var Stockroom to be used in the test
	 */
	public $stockroomB;

	/**
	 * @var Stockroom to be filtered by Product id
	 */
	public $stockroom_productA;

	/**
	 * @var Stockroom to be filtered by Stockroom id
	 */
	public $stockroom_productB;

	/**
	 * @var Version of webservice to use
	 */
	public $webserviceVersion = '1.0.0';


	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$this->category['name'] = $this->faker->bothify('SiteRedshopbStockroom_product100FilteringCest category ?##?');
		$this->category['id']  = (int) $I->createCategory($this->category['name']);

		$this->productA['name'] = $this->faker->bothify('SiteRedshopbStockroom_product100FilteringCest product ?##?');
		$this->productA['sku'] = $this->faker->randomNumber(3);
		$this->productA['id'] = $I->webserviceCrudCreate(
			'product',
			[
				'name' => $this->productA['name'],
				'sku' => $this->productA['sku'],
				'category' => $this->category['id']
			]
		);

		$this->productB['name'] = $this->faker->bothify('SiteRedshopbStockroom_product100FilteringCest product ?##?');
		$this->productB['sku'] = $this->faker->randomNumber(3);
		$this->productB['id'] = $I->webserviceCrudCreate(
			'product',
			[
				'name' => $this->productB['name'],
				'sku' => $this->productB['sku'],
				'category' => $this->category['id']
			]
		);

		$this->stockroomA['name'] = $this->faker->bothify('SiteRedshopbStockroom_product100FilteringCest stockroom ?##?');
		$this->stockroomA['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomA['name']
			]
		);

		$this->stockroomB['name'] = $this->faker->bothify('SiteRedshopbStockroom_product100FilteringCest stockroom ?##?');
		$this->stockroomB['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomB['name']
			]
		);

		$I->comment('I create a Stockroom to be filtered by Product id');
		$this->stockroom_productA['amount'] = $this->faker->randomNumber(3);
		$this->stockroom_productA['id'] = $I->webserviceCrudCreate(
			'stockroom_product',
			[
				'stockroom_id' => $this->stockroomA['id'],
				'product_id' => $this->productB['id'],
				'amount' => $this->stockroom_productA['amount']
			],
			$this->webserviceVersion
		);


		$I->comment('I create a Stockroom to be filtered by Stockroom id');
		$this->stockroom_productB['amount'] = $this->faker->randomNumber(4);
		$this->stockroom_productB['id'] = $I->webserviceCrudCreate(
			'stockroom_product',
			[
				'stockroom_id' => $this->stockroomB['id'],
				'product_id' => $this->productA['id'],
				'amount' => $this->stockroom_productB['amount']
			],
			$this->webserviceVersion
		);
	}

	public function readListfilteredByProduct(ApiTester $I)
	{
		$I->wantTo('GET a list of product stockrooms filtered by Product id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom_product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[product_id]=' . $this->productB['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->stockroom_productA['id']]);
		$I->dontSeeResponseContainsJson(['id' => $this->stockroom_productB['id']]);
	}

	public function readListfilteredByStockroom(ApiTester $I)
	{
		$I->wantTo('GET a list of product stockrooms filtered by Stockroom id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom_product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[stockroom_id]=' . $this->stockroomB['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['id' => $this->stockroom_productA['id']]);
		$I->seeResponseContainsJson(['id' => $this->stockroom_productB['id']]);
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->webserviceCrudDelete('product', $this->productA['id']);
		$I->webserviceCrudDelete('product', $this->productB['id']);
		$I->webserviceCrudDelete('category', $this->category['id']);
		$I->webserviceCrudDelete('stockroom', $this->stockroomA['id']);
		$I->webserviceCrudDelete('stockroom', $this->stockroomB['id']);
	}
}
