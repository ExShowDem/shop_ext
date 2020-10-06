<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbTableLock100CrudCest
{
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	public function create(ApiTester $I)
	{
		$I->wantTo('POST a new table lock');
		$this->faker = Faker\Factory::create();
		$this->table_name  = $this->faker->bothify('SiteRedshopbTableLock100AvailabilityCest table_name ?##?');
		$this->column_name  = $this->faker->bothify('SiteRedshopbTableLock100AvailabilityCest column_name ?##?');
		$this->table_id  = $this->faker->numberBetween(1,9999);

		$this->table_name_update  = $this->faker->bothify('SiteRedshopbTableLock100AvailabilityCest table_name_update ?##?');
		$this->column_name_update  = $this->faker->bothify('SiteRedshopbTableLock100AvailabilityCest column_name_update ?##?');
		$this->table_id_update  = $this->faker->numberBetween(1,9999);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
			'index.php'
			. '?option=redshopb&view=table_lock'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&table_name=$this->table_name"
			. "&table_id=$this->table_id"
			. "&column_name=$this->column_name"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-table_lock:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The id of the new created table lock with name '$this->table_name' is: $this->id");
	}

	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing Table lock");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=table_lock'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['table_name' => $this->table_name]);
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of Table lock");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=table_lock'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['table_name' => $this->table_name]);
	}

	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a new Table lock using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPUT('index.php'
			. '?option=redshopb&view=table_lock'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
			. "&table_name=$this->table_name_update"
			. "&table_id=$this->table_id_update"
			. "&column_name=$this->column_name_update"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=table_lock'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['table_name' => $this->table_name]);
		$I->seeResponseContainsJson(['table_name' => $this->table_name_update]);

		$I->comment("The Table lock name has been modified to: $this->table_name_update");
	}

	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a new Table lock using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=table_lock'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=table_lock'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}
}
