<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProduct120crudCest
{
	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();
		$this->category['name'] = $this->faker->bothify('SiteRedshopbProduct120CrudCest category ?##?');
		$this->category['id']  = (int) $I->createCategory($this->category['name']);
	}

	public function create(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product');
		$this->name  = $this->faker->bothify('SiteRedshopbProduct120CrudCest product ?##?');
		$this->sku  = $this->faker->randomNumber(3);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&name=$this->name"
			. "&sku=$this->sku"
			. "&category_id=" . $this->category['id']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The id of the new created product with name '$this->name' is: $this->id");
	}

	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing Product");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=product'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.2.0'
					. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of Products");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=product'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.2.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a new Product using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedName = 'new_' . $this->name;
		$I->sendPUT('index.php'
					. '?option=redshopb&view=product'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.2.0'
					. "&id=$this->id"
					. "&name=$this->updatedName"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
					. '?option=redshopb&view=product'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.2.0'
					. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['name' => $this->updatedName]);

		$I->comment("The product name has been modified to: $this->updatedName");
	}

	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a new Product using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
					. '?option=redshopb&view=product'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.2.0'
					. "&id=$this->id"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->deleteCategory($this->category['id']);
	}
}
