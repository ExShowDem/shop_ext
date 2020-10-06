<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Step\Api\redshopb2b as redshopb2b;

/**
 * Class SiteRedshopbProductComplementaryProduct100erpCest
 * @since 2.5.1
 */
class SiteRedshopbProductComplementaryProduct100erpCest
{
	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $id;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $nameProduct;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $nameProductSecond;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $sku1;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $sku2;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $category;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $erpid;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $erpidUpdate;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $id1;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $id2;

	/**
	 * @since 2.5.1
	 */
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 *
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function createWithErpId(redshopb2b $I)
	{
		$I->wantTo('POST a new product');
		$this->faker = Faker\Factory::create();
		$this->nameProduct  = $this->faker->bothify('SiteRedshopbProductComplementaryProduct100CrudCest product ?##?');
		$this->nameProductSecond  = $this->faker->bothify('SiteRedshopbProductComplementaryProduct100CrudCest product second ?##?');
		$this->sku1  = $this->faker->randomNumber(3);
		$this->sku2  = $this->faker->randomNumber(4);
		$this->category['name'] = $this->faker->bothify('SiteRedshopbProductComplementaryProduct100CrudCest category ?##?');
		$this->category['id']  = (int) $I->createCategory($this->category['name']);
		$this->erpid = $this->faker->numberBetween(1, 9999);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&name=$this->nameProduct"
			. "&sku=$this->sku1"
			. "&category_id=" . $this->category['id']
		);
		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$id1 = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id1 = $id1[0];
		$I->comment("The id of the new created product with name '$this->nameProduct' is: $this->id1");

		$I->wantTo('POST the second product');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&name=$this->nameProductSecond"
			. "&sku=$this->sku2"
			. "&category_id=" . $this->category['id']
		);
		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$id2 = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id2 = $id2[0];
		$I->comment("The id of the new created product with name '$this->nameProductSecond' is: $this->id2");

		$I->wantTo('POST a new Product Complementary Product with ERP Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
			'index.php'
			. '?option=redshopb&view=product_complementary_product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&erp.id=$this->erpid"
			. "&product_id=$this->id1"
			. "&complimentary_product_id=$this->id2"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$id3 = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $id3[0];
		$I->comment("The id of the new Product Complementary Product is: $this->id and ERP ID is: $this->erpid");
	}

	/**
	 * @param \ApiTester $I
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function readItemUsingErpId(ApiTester $I)
	{
		$I->wantTo("GET an existing Product Complementary Product with ERP Id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_complementary_product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
			. "&erp.id=$this->erpid"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
	}

	/**
	 * @param \ApiTester $I
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function updateUsingErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a new Product Complementary Product using PUT with ERP Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->erpidUpdate = $this->faker->numberBetween(1, 9999);
		$I->sendPUT('index.php'
			. '?option=redshopb&view=product_complementary_product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
			. "&erp.id=$this->erpidUpdate"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_complementary_product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function deleteERP(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product_complementary_product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_complementary_product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
			. "&erp_id=$this->erpidUpdate"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id1"
		);
		$I->seeResponseCodeIs(200);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id1"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id2"
		);
		$I->seeResponseCodeIs(200);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id2"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');

		$I->deleteCategory($this->category['id'], '1.0.0');
	}
}
