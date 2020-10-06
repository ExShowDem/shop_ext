<?php
/**
 * @package     Aesir E-Commerce.
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Step\Api\redshopb2b;

/**
 * Class SiteRedshopbOrder170erpCest
 * @since 2.8.0
 */
class SiteRedshopbOrder170erpCest
{
	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $category;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $product;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $company;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $deliveryAddress;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $order;

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('Prepare data needed for the test');
		$this->faker = Faker\Factory::create();
		$this->order['quantity'] = 1;
		$this->order['erpId'] = (int) $this->faker->numberBetween(100, 1000);

		$I->comment('I create a category');
		$this->category['name'] = $this->faker->bothify('SiteRedshopbOrder170erpCest category ?##?');
		$this->category['id'] = (int)$I->createCategory($this->category['name']);

		$I->comment('I create a product');
		$this->product['name'] = $this->faker->bothify('SiteRedshopbOrder170erpCest product ?##?');
		$this->product['sku'] = $this->faker->numberBetween(100, 1000);
		$this->product['id'] = (int)$I->createProduct($this->product['name'], $this->product['sku'], $this->category['id']);

		$I->comment('I create a company b2b');
		$this->company['name'] = $this->faker->bothify('SiteRedshopbOrder170erpCest company ?##?');
		$this->company['id'] = (int)$I->createCompany($this->company['name']);

		$I->comment('I prepare data to the delivery address');
		$this->deliveryAddress['name1'] = $this->faker->bothify('SiteRedshopbOrder170erpCest delivery_address ?##?');
		$this->deliveryAddress['address_line1'] = $this->faker->streetAddress;
		$this->deliveryAddress['zip'] = $this->faker->postcode;
		$this->deliveryAddress['city'] = $this->faker->city;
		$this->deliveryAddress['country_code'] = (string)'DK';

		$this->deliveryAddress['id'] = $I->webserviceCrudCreate(
			'delivery_address',
			[
				'name1' => $this->deliveryAddress['name1'],
				'address_line1' => $this->deliveryAddress['address_line1'],
				'zip' => $this->deliveryAddress['zip'],
				'city' => $this->deliveryAddress['city'],
				'country_code' => $this->deliveryAddress['country_code'],
				'company_id' => $this->company['id']
			]
		);
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function createWithErpId(redshopb2b $I)
	{
		$I->wantTo('POST a new order with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.7.0'
			. "&id=" . $this->order['erpId']
			. "&delivery_address_id=" . $this->deliveryAddress['id']
			. "&company_id=" . $this->company['id']
			. "&items[0][product_id]=" . $this->product['id']
			. "&items[0][quantity]=" . $this->order['quantity']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-order:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$statuses = $I->grabDataFromResponseByJsonPath('$.status_code');
		$this->order['status_code'] = $statuses[0];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readWithErpId(ApiTester $I)
	{
		$I->wantTo('GET an existing order with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.7.0'
			. "&id=erp." . $this->order['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->order['erpId']]]);
		$I->seeResponseContainsJson(['status_code' => $this->order['status_code']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function updateWithErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a order using PUT with erp id');
		$this->order['updated_status_code'] = 'cancelled';

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPUT('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.7.0'
			. "&id=erp." . $this->order['erpId']
			. "&status_code=" . $this->order['updated_status_code']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();

		$I->sendGET('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.7.0'
			. "&id=erp." . $this->order['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->dontSeeResponseContainsJson(['status_code' => $this->order['status_code']]);
		$I->seeResponseContainsJson(['status_code' => $this->order['updated_status_code']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function deleteWithErpId(ApiTester $I)
	{
		$I->wantTo('DELETE a order using DELETE with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.7.0'
			. "&id=erp." . $this->order['erpId']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.7.0'
			. "&id=erp." . $this->order['erpId']
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('delivery_address', $this->deliveryAddress['id']);
		$I->webserviceCrudDelete('company', $this->company['id']);
		$I->webserviceCrudDelete('category', $this->category['id']);
		$I->webserviceCrudDelete('product', $this->product['id']);
	}
}