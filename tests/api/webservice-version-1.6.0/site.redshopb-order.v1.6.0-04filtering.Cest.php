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
 * Class SiteRedshopbOrder160filteringCest
 * @since 2.8.0
 */
class SiteRedshopbOrder160filteringCest
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
	protected $companyA;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $companyB;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $deliveryAddressA;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $deliveryAddressB;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $orderA;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $orderB;

	/**
	 * Prepares the following structure
	 *
	 * +---------+--------------------+-----------+---------------+
	 * |  Order  |  Delivery Address  |  Company  |  Status Code  |
	 * +---------+--------------------+-----------+---------------+
	 * |  OrderA |  deliveryAddressA  |  companyA |    pending    |
	 * |  OrderB |  deliveryAddressB  |  companyB |   cancelled   |
	 * +---------+--------------------+-----------+---------------+
	 */

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('Prepare data needed for the test');
		$this->faker = Faker\Factory::create();

		$I->comment('I prepare data for two orders');
		$this->orderA['quantity'] = 1;
		$this->orderA['erpId'] = (int) $this->faker->numberBetween(100, 1000);

		$this->orderB['quantity'] = 1;
		$this->orderB['erpId'] = (int) $this->faker->numberBetween(100, 1000);

		$I->comment('I create a category');
		$this->category['name'] = $this->faker->bothify('SiteRedshopbOrder160filteringCest category ?##?');
		$this->category['id'] = (int)$I->createCategory($this->category['name']);

		$I->comment('I create a product');
		$this->product['name'] = $this->faker->bothify('SiteRedshopbOrder160filteringCest product ?##?');
		$this->product['sku'] = $this->faker->numberBetween(100, 1000);
		$this->product['id'] = (int)$I->createProduct($this->product['name'], $this->product['sku'], $this->category['id']);

		$I->comment('I create two companies b2b');
		$this->companyA['name'] = $this->faker->bothify('SiteRedshopbOrder160filteringCest companyA ?##?');
		$this->companyA['id'] = (int)$I->createCompany($this->companyA['name']);

		$this->companyB['name'] = $this->faker->bothify('SiteRedshopbOrder160filteringCest companyB ?##?');
		$this->companyB['id'] = (int)$I->createCompany($this->companyB['name']);

		$I->comment('I prepare data for two delivery address');
		$this->deliveryAddressA['name1'] = $this->faker->bothify('SiteRedshopbOrder160filteringCest delivery_addressA ?##?');
		$this->deliveryAddressA['address_line1'] = $this->faker->streetAddress;
		$this->deliveryAddressA['zip'] = $this->faker->postcode;
		$this->deliveryAddressA['city'] = $this->faker->city;
		$this->deliveryAddressA['country_code'] = (string)'DK';

		$this->deliveryAddressB['name1'] = $this->faker->bothify('SiteRedshopbOrder160filteringCest delivery_addressB ?##?');
		$this->deliveryAddressB['address_line1'] = $this->faker->streetAddress;
		$this->deliveryAddressB['zip'] = $this->faker->postcode;
		$this->deliveryAddressB['city'] = $this->faker->city;
		$this->deliveryAddressB['country_code'] = (string)'DK';

		$I->comment('I create two delivery address');
		$this->deliveryAddressA['id'] = $I->webserviceCrudCreate(
			'delivery_address',
			[
				'name1' => $this->deliveryAddressA['name1'],
				'address_line1' => $this->deliveryAddressA['address_line1'],
				'zip' => $this->deliveryAddressA['zip'],
				'city' => $this->deliveryAddressA['city'],
				'country_code' => $this->deliveryAddressA['country_code'],
				'company_id' => $this->companyA['id']
			]
		);

		$this->deliveryAddressB['id'] = $I->webserviceCrudCreate(
			'delivery_address',
			[
				'name1' => $this->deliveryAddressB['name1'],
				'address_line1' => $this->deliveryAddressB['address_line1'],
				'zip' => $this->deliveryAddressB['zip'],
				'city' => $this->deliveryAddressB['city'],
				'country_code' => $this->deliveryAddressB['country_code'],
				'company_id' => $this->companyB['id']
			]
		);
	}

	/**
	 * @param ApiTester $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function create(ApiTester $I)
	{
		$I->wantTo('POST two orders to filtering');

		$I->comment('I create the orderA');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. "&id=" . $this->orderA['erpId']
			. "&delivery_address_id=" . $this->deliveryAddressA['id']
			. "&company_id=" . $this->companyA['id']
			. "&items[0][product_id]=" . $this->product['id']
			. "&items[0][quantity]=" . $this->orderA['quantity']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-order:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->orderA['id'] = $ids[0];
		$statuses = $I->grabDataFromResponseByJsonPath('$.status_code');
		$this->orderA['status_code'] = $statuses[0];

		$I->comment('I create the orderB');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. "&id=" . $this->orderB['erpId']
			. "&delivery_address_id=" . $this->deliveryAddressB['id']
			. "&company_id=" . $this->companyB['id']
			. "&items[0][product_id]=" . $this->product['id']
			. "&items[0][quantity]=" . $this->orderB['quantity']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-order:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->orderB['id'] = $ids[0];

		$I->comment('UPDATE status code of orderB to be filtered by status_code');
		$this->orderB['status_code'] = 'cancelled';

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPUT('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. "&id=" . $this->orderB['id']
			. "&status_code=" . $this->orderB['status_code']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readListFilteredByDeliveryAddressId(ApiTester $I)
	{
		$I->wantTo("GET a list of orders filtered by delivery address id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=order'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[delivery_address_id]=" . $this->deliveryAddressA['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->orderA['id']]);
		$I->seeResponseContainsJson(['delivery_address_id' => $this->deliveryAddressA['id']]);
		$I->dontseeResponseContainsJson(['id' => $this->orderB['id']]);
		$I->dontSeeResponseContainsJson(['delivery_address_id' => $this->deliveryAddressB['id']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readListFilteredByCompanyId(ApiTester $I)
	{
		$I->wantTo("GET a list of orders filtered by company id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=order'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[company_id]=" . $this->companyA['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->orderA['id']]);
		$I->seeResponseContainsJson(['company_id' => $this->companyA['id']]);
		$I->dontseeResponseContainsJson(['id' => $this->orderB['id']]);
		$I->dontSeeResponseContainsJson(['company_id' => $this->companyB['id']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readListFilteredByStatusCode(ApiTester $I)
	{
		$I->wantTo("GET a list of orders filtered by status code");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=order'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[status_code]=" . $this->orderA['status_code']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->orderA['id']]);
		$I->seeResponseContainsJson(['status_code' => $this->orderA['status_code']]);
		$I->dontseeResponseContainsJson(['id' => $this->orderB['id']]);
		$I->dontSeeResponseContainsJson(['status_code' => $this->orderB['status_code']]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('order', $this->orderA['id']);
		$I->webserviceCrudDelete('order', $this->orderB['id']);
		$I->webserviceCrudDelete('delivery_address', $this->deliveryAddressA['id']);
		$I->webserviceCrudDelete('delivery_address', $this->deliveryAddressB['id']);
		$I->webserviceCrudDelete('company', $this->companyA['id']);
		$I->webserviceCrudDelete('company', $this->companyB['id']);
		$I->webserviceCrudDelete('category', $this->category['id']);
		$I->webserviceCrudDelete('product', $this->product['id']);
	}
}