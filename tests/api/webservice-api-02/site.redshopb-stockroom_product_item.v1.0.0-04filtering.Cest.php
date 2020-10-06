<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbStockroom_product_item100FilteringCest
{
	/**
	 * @var Category to be used in the test
	 */
	public $category;

	/**
	 * @var Product to be used in the test
	 */
	public $product;

	/**
	 * @var Product item to be used in the test
	 */
	public $product_itemA;

	/**
	 * @var Product item to be used in the test
	 */
	public $product_itemB;

	/**
	 * @var Product attribute to be used in the test
	 */
	public $product_attribute;

	/**
	 * @var Product attribute value to be used in the test
	 */
	public $product_attribute_valueA;

	/**
	 * @var Product attribute value to be used in the test
	 */
	public $product_attribute_valueB;

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
	public $stockroom_product_itemA;

	/**
	 * @var Stockroom to be filtered by Stockroom Id
	 */
	public $stockroom_product_itemB;

	/**
	 * @var Version of webservice to use
	 */
	public $webserviceVersion = '1.0.0';


	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$this->category['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100FilteringCest category ?##?');
		$this->category['id'] = $I->webserviceCrudCreate(
			'category',
			[
				'name' => $this->category['name']
			]
		);

		$this->product['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100FilteringCest product ?##?');
		$this->product['sku'] = $this->faker->randomNumber(3);
		$this->product['id'] = $I->webserviceCrudCreate(
			'product',
			[
				'name' => $this->product['name'],
				'sku' => $this->product['sku'],
				'category' => $this->category['id']
			]
		);

		$this->product_attribute['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100FilteringCest product_attribute ?##?');
		$this->product_attribute['id'] = $I->webserviceCrudCreate(
			'product_attribute',
			[
				'name' => $this->product_attribute['name'],
				'product_id' => $this->product['id'],
				'type_id' => '1'
			]
		);

		$this->product_attribute_valueA['sku'] = $this->faker->randomNumber(3);
		$this->product_attribute_valueA['value'] = $this->faker->bothify('?##?');
		$this->product_attribute_valueA['id'] = $I->webserviceCrudCreate(
			'product_attribute_value',
			[
				'product_attribute_id' => $this->product_attribute['id'],
				'sku' => $this->product_attribute_valueA['sku'],
				'value' => $this->product_attribute_valueA['value']
			]
		);

		$this->product_attribute_valueB['sku'] = $this->faker->randomNumber(3);
		$this->product_attribute_valueB['value'] = $this->faker->bothify('?##?');
		$this->product_attribute_valueB['id'] = $I->webserviceCrudCreate(
			'product_attribute_value',
			[
				'product_attribute_id' => $this->product_attribute['id'],
				'sku' => $this->product_attribute_valueB['sku'],
				'value' => $this->product_attribute_valueB['value']
			]
		);

		$this->product_itemA['id'] = $I->webserviceCrudCreate(
			'product_item',
			[
				'product_id' => $this->product['id'],
				'product_attribute_value_ids' => $this->product_attribute_valueA['id']
			]
		);

		$this->product_itemB['id'] = $I->webserviceCrudCreate(
			'product_item',
			[
				'product_id' => $this->product['id'],
				'product_attribute_value_ids' => $this->product_attribute_valueB['id']
			]
		);

		$this->stockroomA['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100FilteringCest stockroom ?##?');
		$this->stockroomA['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomA['name']
			]
		);

		$this->stockroomB['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100FilteringCest stockroom ?##?');
		$this->stockroomB['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomB['name']
			]
		);

		$I->comment('I create a Product Item Stockroom to be filtered by Product id');
		$this->stockroom_product_itemA['amount'] = $this->faker->randomNumber(3);
		$this->stockroom_product_itemA['id'] = $I->webserviceCrudCreate(
			'stockroom_product_item',
			[
				'stockroom_id' => $this->stockroomA['id'],
				'product_item_id' => $this->product_itemB['id'],
				'amount' => $this->stockroom_product_itemA['amount']
			],
			$this->webserviceVersion
		);


		$I->comment('I create a Product Item Stockroom to be filtered by Stockroom id');
		$this->stockroom_product_itemB['amount'] = $this->faker->randomNumber(4);
		$this->stockroom_product_itemB['id'] = $I->webserviceCrudCreate(
			'stockroom_product_item',
			[
				'stockroom_id' => $this->stockroomB['id'],
				'product_item_id' => $this->product_itemA['id'],
				'amount' => $this->stockroom_product_itemB['amount']
			],
			$this->webserviceVersion
		);
	}

	public function readListfilteredByProductItem(ApiTester $I)
	{
		$I->wantTo('GET a list of product item stockrooms filtered by Product item id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom_product_item'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[product_item_id]=' . $this->product_itemB['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->stockroom_product_itemA['id']]);
		$I->dontSeeResponseContainsJson(['id' => $this->stockroom_product_itemB['id']]);
	}

	public function readListfilteredByStockroom(ApiTester $I)
	{
		$I->wantTo('GET a list of product item stockrooms filtered by Stockroom id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom_product_item'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[stockroom_id]=' . $this->stockroomB['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['id' => $this->stockroom_product_itemA['id']]);
		$I->seeResponseContainsJson(['id' => $this->stockroom_product_itemB['id']]);
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->webserviceCrudDelete('category', $this->category['id']);
		$I->webserviceCrudDelete('product', $this->product['id']);
		$I->webserviceCrudDelete('stockroom', $this->stockroomA['id']);
		$I->webserviceCrudDelete('stockroom', $this->stockroomB['id']);
	}
}
