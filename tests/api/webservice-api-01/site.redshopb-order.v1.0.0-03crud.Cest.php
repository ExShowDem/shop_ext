<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbOrder100crudCest
{
	/**
	 * @var Category to be used in the test
	 */
	protected $category;

	/**
	 * @var Product address to be used in the test
	 */
	protected $product;

	/**
	 * @var Company to be used in the test
	 */
	protected $company;

	/**
	 * @var Delivery address to be used in the test
	 */
	protected $deliveryAddress;

	/**
	 * @var Order to be used in the test
	 */
	protected $order;

	/**
	 * @var Webserviceversion to be used in the test
	 */
	protected $webserviceVersion = '1.0.0';


	public function prepare(\Step\Api\redshopb2b $I)
	{
		$I->wantTo("Prepare data needed for CRUD tests");
		$this->faker = Faker\Factory::create();

		$this->category['name'] = $this->faker->bothify('SiteRedshopbStockroom_product100FilteringCest category ?##?');
		$this->category['id']  = (int) $I->createCategory($this->category['name']);

		$this->product['name'] = $this->faker->bothify('SiteRedshopbStockroom_product100FilteringCest product ?##?');
		$this->product['sku'] = $this->faker->randomNumber(3);
		$this->product['price'] = $this->faker->randomNumber(3);
		$this->product['id'] = $I->webserviceCrudCreate(
			'product',
			[
				'name' => $this->product['name'],
				'sku' => $this->product['sku'],
				'category' => $this->category['id'],
				'price' => $this->product['price']
			]
		);

		$this->company['name'] = $this->faker->bothify('SiteRedshopbOrder150CrudCest company ?##?');
		$this->company['id'] = $I->createCompany($this->company['name']);

		// Create delivery address
		$this->deliveryAddress['name1'] = $this->faker->bothify('SiteRedshopbOrder150CrudCest delivery_address ?##?');
		$this->deliveryAddress['address_line1'] = $this->faker->streetAddress;
		$this->deliveryAddress['zip'] = $this->faker->postcode;
		$this->deliveryAddress['city'] = $this->faker->city;
		$this->deliveryAddress['country_code'] = (string) 'DK';

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

		$this->order['quantity'] = $this->faker->randomDigitNotNull();
	}

	public function create(ApiTester $I)
	{
		$I->wantTo('CREATE an order using POST');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
			'index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&delivery_address_id=' . $this->deliveryAddress['id']
			. '&company_id=' . $this->company['id']
			. '&items[0][product_id]=' . $this->product['id']
			. '&items[0][quantity]=' . $this->order['quantity']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-order:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->order['id'] = $ids[0];
		$statuses = $I->grabDataFromResponseByJsonPath('$.status_code');
		$this->order['status_code'] = $statuses[0];
		$I->comment("The id of the new created order is: " . $this->order['id'] . ", and the status is: " . $this->order['status_code']);
	}

	public function readItem(ApiTester $I)
	{
		$I->wantTo('GET an existing order');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=order'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=' . $this->webserviceVersion
					. "&id=" . $this->order['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->order['id']]);
		$I->seeResponseContainsJson(['status_code' => $this->order['status_code']]);
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo('GET a list of all existing orders');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=order'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=' . $this->webserviceVersion
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->order['id']]);
	}

	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a new order using PUT');
		$this->order['updated_status_code'] = 'cancelled';
		$I->amHttpAuthenticated('admin', 'admin');

		$I->sendPUT('index.php'
					. '?option=redshopb&view=order'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=' . $this->webserviceVersion
					. '&id=' . $this->order['id']
					. '&status_code=' . $this->order['updated_status_code']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
					. '?option=redshopb&view=order'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=' . $this->webserviceVersion
					. "&id=" . $this->order['id']
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['status_code' => $this->order['status_code']]);
		$I->seeResponseContainsJson(['status_code' => $this->order['updated_status_code']]);

		$I->comment("The order status has been updated to: " . $this->order['updated_status_code']);
	}

	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a new order using DELETE');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. "&id=" . $this->order['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
					. '?option=redshopb&view=order'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=' . $this->webserviceVersion
					. "&id=" . $this->order['id']
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}

	public function cleanUp(\Step\Api\redshopb2b $I)
	{
		$I->webserviceCrudDelete('delivery_address', $this->deliveryAddress['id']);
		$I->webserviceCrudDelete('company', $this->company['id']);
		$I->webserviceCrudDelete('category', $this->category['id']);
		$I->webserviceCrudDelete('product', $this->product['id']);
	}
}
