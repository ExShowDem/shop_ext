<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProductDescription110CrudCest
{
	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $name;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $erpid;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $description;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $description_new;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $product;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $id;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.5.0
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product_description');
		$this->faker = Faker\Factory::create();
		$this->name  = $this->faker->bothify('SiteRedshopbProductDescription110crudCest product_description ?##?');
		$this->erpid = (int) $this->faker->numberBetween(1, 9999);
		$this->description = $this->faker->bothify('SiteRedshopbProductDescription110crudCest description ?##?');
		$this->description_new = $this->faker->bothify('SiteRedshopbProductDescription110crudCest description_new ?##?');

		$this->product['name'] = (string) $this->faker->bothify('SiteRedshopbProductDescription110crudCest product ?##?');
		$this->product['sku'] = (int) $this->faker->numberBetween(1, 9999);

		$this->product['category_name'] = (string) $this->faker->bothify('SiteRedshopbProductDescription110crudCest category ?##?');
		$this->product['category_id'] = (int)
		$I->createCategory($this->product['category_name']);
		$this->product['id'] = (int)
		$I->createProduct($this->product['name'], $this->product['sku'], $this->product['category_id']);	
	}

	/**
	 * @param ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function create(ApiTester $I)
	{
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&name=$this->name"
			. "&id=$this->erpid"
			. "&product_id=" . $this->product['id']
			. "&description=$this->description"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product_description:self']['href']);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];

		$I->comment("The id of the new created product_description with name '$this->name' is: '$this->id'");
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing product_description with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(
			[
				'id'                        => $this->id,
				'id_others'                 => ['erp.' . $this->erpid],
				'product_id'                => $this->product['id'],
				'product_id_others'         => [],
				'main_attribute_value_id'   => 0,
				'main_attribute_id_others'  => NULL,
				'description_intro'         => '<p>' . $this->description . '</p>',
				'description'               => '<p>' . $this->description . '</p>'
			]
		);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function readList(ApiTester $I)
	{
		$I->wantTo('GET a list of Product Descriptions');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a new description using PUT');
		$I->amHttpAuthenticated('admin', 'admin');

		$I->sendPUT('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
			. "&description=$this->description_new"
		);

		$I->seeResponseCodeIs(200);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(
			[
				'id'                        => $this->id,
				'id_others'                 => ['erp.' . $this->erpid],
				'product_id'                => $this->product['id'],
				'product_id_others'         => [],
				'main_attribute_value_id'   => 0,
				'main_attribute_id_others'  => NULL,
				'description_intro'         => '<p>' . $this->description . '</p>',
				'description'               => '<p>' . $this->description . '</p>' . '<hr id="system-readmore" />' .'<p>' . $this->description_new . '</p>'
			]
		);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a description using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.5.0
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteProduct($this->product['id']);
		$I->deleteCategory($this->product['category_id']);
	}
}