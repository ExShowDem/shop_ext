<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbStockroom_product_item100CrudCest
{
	/**
	 * @var Product to be used in the test
	 */
	public $product;

	/**
	 * @var Product attribute to be used in the test
	 */
	public $product_attribute;

	/**
	 * @var Product attribute value to be used in the test
	 */
	public $product_attribute_value;

	/**
	 * @var Product item to be used in the test
	 */
	public $product_item;

	/**
	 * @var Stockroom to be used in the test
	 */
	public $stockroom;

	/**
	 * @var Product stockroom to be used in the test
	 */
	public $stockroom_product_item;

	/**
	 * @var The webservice version to be used in the test
	 */
	public $webserviceVersion = '1.0.0';

	public function prepare(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('Prepare data needed for CRUD tests');
		$this->faker = Faker\Factory::create();

		$this->category['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100CrudCest category ?##?');
		$this->category['id'] = $I->webserviceCrudCreate(
			'category',
			[
				'name' => $this->category['name']
			]
		);

		$this->product['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100CrudCest product ?##?');
		$this->product['sku'] = $this->faker->randomNumber(3);
		$this->product['id'] = $I->webserviceCrudCreate(
			'product',
			[
				'name' => $this->product['name'],
				'sku' => $this->product['sku'],
				'category' => $this->category['id']
			]
		);

		$this->product_attribute['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100CrudCest product_attribute ?##?');
		$this->product_attribute['id'] = $I->webserviceCrudCreate(
			'product_attribute',
			[
				'name' => $this->product_attribute['name'],
				'product_id' => $this->product['id'],
				'type_id' => '1'
			]
		);

		$this->product_attribute_value['sku'] = $this->faker->randomNumber(3);
		$this->product_attribute_value['value'] = $this->faker->bothify('?##?');
		$this->product_attribute_value['id'] = $I->webserviceCrudCreate(
			'product_attribute_value',
			[
				'product_attribute_id' => $this->product_attribute['id'],
				'sku' => $this->product_attribute_value['sku'],
				'value' => $this->product_attribute_value['value']
			]
		);

		$this->product_item['id'] = $I->webserviceCrudCreate(
			'product_item',
			[
				'product_id' => $this->product['id'],
				'product_attribute_value_ids' => $this->product_attribute_value['id']
			]
		);

		$this->stockroom['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100CrudCest stockroom ?##?');
		$this->stockroom['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroom['name']
			]
		);
	}

	public function create(ApiTester $I)
	{
		$this->stockroom_product_item['amount'] = $this->faker->randomNumber(2);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
				'index.php'
				. '?option=redshopb&view=stockroom_product_item'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=' . $this->webserviceVersion
				. '&stockroom_id=' . $this->stockroom['id']
				. '&product_item_id=' . $this->product_item['id']
				. '&amount=' . $this->stockroom_product_item['amount']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = (array) $I->grabDataFromResponseByJsonPath('$.result');
		$this->stockroom_product_item['id'] = $ids[0];
		$I->comment('The id of the newly created product product item stockroom is:' . $this->stockroom_product_item['id']);
	}

	public function readItem(ApiTester $I)
	{
		$I->wantTo('GET an existing product item stockroom');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom_product_item'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroom_product_item['id']
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->stockroom_product_item['id']]);
		$I->seeResponseContainsJson(['amount' => $this->stockroom_product_item['amount']]);
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo('GET all existing product item stockrooms');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom_product_item'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->stockroom_product_item['id']]);
		$I->seeResponseContainsJson(['amount' => $this->stockroom_product_item['amount']]);
	}

	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a new Product Item Stockroom using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->stockroom_product_item['updatedAmount'] = $this->faker->randomNumber(3);

		$I->sendPUT('index.php'
			. '?option=redshopb&view=stockroom_product_item'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroom_product_item['id']
			. '&amount=' . $this->stockroom_product_item['updatedAmount']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom_product_item'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroom_product_item['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['amount' => $this->stockroom_product_item['amount']]);
		$I->seeResponseContainsJson(['amount' => $this->stockroom_product_item['updatedAmount']]);

		$I->comment("The Product Item Stockroom's amount has been modified to: " . $this->stockroom_product_item['updatedAmount']);
	}

	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a new Stockroom using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=stockroom_product_item'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroom_product_item['id']
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom_product_item'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroom_product_item['id']
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Remove all items created by the test');
		$I->webserviceCrudDelete('product', $this->product['id']);
		$I->webserviceCrudDelete('category', $this->category['id']);
		$I->webserviceCrudDelete('stockroom', $this->stockroom['id']);
	}
}
