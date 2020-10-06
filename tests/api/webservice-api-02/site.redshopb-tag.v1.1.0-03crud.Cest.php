<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbTag110CrudCest
{
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	public function create(ApiTester $I)
	{
		$I->wantTo('POST a new tag');
		$this->name = $this->faker->bothify('SiteRedshopbTag110CrudCest tag ?##?');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&name=$this->name"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-tag:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The id of the new created category with name '$this->name' is: $this->id");
	}

	public function createChild(ApiTester $I)
	{
		$I->wantTo('POST a new tag (child of the first tag)');
		$this->name2 = $this->faker->bothify('SiteRedshopbTag110CrudCest tag ?##?');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&name=$this->name2"
			. "&company_id=2"
			. "&parent_id=$this->id"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-tag:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['parent_id' => $this->id]);

		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id2 = $ids[0];
		$I->comment("The id of the new created tag with name '$this->name2' is: $this->id2");
	}

	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a tag with PUT using its internal id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedName = 'new_' . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
			. "&name=$this->updatedName"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['name' => $this->updatedName]);

		$I->comment("The tag name has been modified to: $this->updatedName");

		$this->name = $this->updatedName;
	}

	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing tag with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"name":"' . $this->name . '"');
		$I->seeResponseContains('"company_id":0');
		$I->seeResponseContains('"parent_id":0');
		$I->seeResponseContains('"state":true');
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of tags");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['id' => $this->id2]);
		$I->seeResponseContainsJson(['name' => $this->name2]);
	}

	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a tag with DELETE using its internal id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
		. '?option=redshopb&view=tag'
		. '&api=Hal'
		. '&webserviceClient=site'
		. '&webserviceVersion=1.1.0'
		. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');

		$I->sendGET('index.php'
					. '?option=redshopb&view=tag'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.1.0'
					. "&id=$this->id2"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}
}
