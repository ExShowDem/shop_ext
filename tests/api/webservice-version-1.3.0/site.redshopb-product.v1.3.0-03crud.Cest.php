<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProduct130crudCest
 * @since 2.6.0
 */
class SiteRedshopbProduct130crudCest
{
	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $name;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $sku;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $category;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $id;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $updatedName;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function create(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product');
		$this->faker = Faker\Factory::create();

		$this->name = $this->faker->bothify('SiteRedshopbProduct130crudCest product ?##?');
		$this->sku  = $this->faker->numberBetween(100, 1000);

		$this->category['name'] = $this->faker->bothify('SiteRedshopbProduct130crudCest category ?##?');
		$this->category['id']   = (int) $I->createCategory($this->category['name']);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=$this->name"
			. "&sku=$this->sku"
			. "&category_id=" . $this->category['id']
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created product with name '$this->name' is: '$this->id'");
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing product");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponsecontainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of products");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function update(ApiTester $I)
	{
		$I->wantTo("UPDATE a new product using PUT");
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedName = 'new_' . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->id"
			. "&name=$this->updatedName"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['name' => $this->updatedName]);

		$I->comment("The product name has been modified to: $this->updatedName");
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a new Product using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteCategory($this->category['id']);
	}
}
