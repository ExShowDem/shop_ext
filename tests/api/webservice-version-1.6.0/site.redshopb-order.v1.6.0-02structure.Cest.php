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
 * Class SiteRedshopbOrder160structureCest
 * @since 2.8.0
 */
class SiteRedshopbOrder160structureCest
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
	protected $order_quantity;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $order_id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $baseUrl;

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('Prepare data needed for the test');
		$this->faker = Faker\Factory::create();

		$I->comment('I create a category');
		$this->category['name'] = $this->faker->bothify('SiteRedshopbOrder160structureCest category ?##?');
		$this->category['id'] = (int)$I->createCategory($this->category['name']);

		$I->comment('I create a product');
		$this->product['name'] = $this->faker->bothify('SiteRedshopbOrder160structureCest product ?##?');
		$this->product['sku'] = $this->faker->numberBetween(100, 1000);
		$this->product['id'] = (int)$I->createProduct($this->product['name'], $this->product['sku'], $this->category['id']);

		$I->comment('I create a company b2b');
		$this->company['name'] = $this->faker->bothify('SiteRedshopbOrder160structureCest company ?##?');
		$this->company['id'] = (int)$I->createCompany($this->company['name']);

		$I->comment('I prepare data to the delivery address');
		$this->deliveryAddress['name1'] = $this->faker->bothify('SiteRedshopbOrder160structureCest delivery_address ?##?');
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

		$I->comment('I create a new order');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->order_quantity = 1;
		$I->sendPOST('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. "&delivery_address_id=" . $this->deliveryAddress['id']
			. "&company_id=" . $this->company['id']
			. "&items[0][product_id]=" . $this->product['id']
			. "&items[0][quantity]=$this->order_quantity"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->order_id = $ids[0];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo('GET an existing order');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. "&id=$this->order_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' => [
					'curies' => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=order&webserviceVersion=1.6.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-order',
							'templated' => true
						]
					],
					'base' => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page'
					],
					'redshopb-order:list' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=order&webserviceVersion=1.6.0&webserviceClient=site&api=Hal"
					],
					'redshopb-order:self' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=order&webserviceVersion=1.6.0&webserviceClient=site&id=$this->order_id&api=Hal"
					],
				],
				'id'                                => $this->order_id,
				'delivery_address_id'               => $this->deliveryAddress['id'],
				'company_id'                        => $this->company['id'],
				'department_id'                     => 0,
				'department_id_others'              => null,
				'user_id'                           => 0,
				'user_id_others'                    => null,
				'currency_code'                     => 'EUR',
				'total_price'                       => 0,
				'discount_type'                     => 'percent',
				'discount'                          => 0,
				'user_company_id'                   => 0,
				'shipping_code'                     => '',
				'shipping_price'                    => 0,
				'status_code'                       => 'pending',
				'ip_address'                        => '',
				'requisition'                       => '',
				'comment'                           => '',
				'shipping_date'                     => '',
				'payment_method'                    => '',
				'payment_status'                    => '',
			]
		);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a order using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. "&id=$this->order_id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. "&id=$this->order_id"
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