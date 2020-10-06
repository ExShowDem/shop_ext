<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbDepartment100CrudCest
{
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	public function create(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new department');
		$this->name = $this->faker->bothify('SiteRedshopbDepartment100CrudCest department ?##?');
		$this->company_id = $I->getMainCompanyId();
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
			'index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&name=$this->name"
			. "&company_id=$this->company_id"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-department:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The id of the new created department with name '$this->name' is: $this->id");
	}

	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing Department");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of Departments");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a new department using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedName = 'new_' . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
			. "&name=$this->updatedName"
			. "&company_id=$this->company_id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['name' => $this->updatedName]);

		$I->comment("The department name has been modified to: $this->updatedName");
	}

	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a new department using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
		. '?option=redshopb&view=department'
		. '&api=Hal'
		. '&webserviceClient=site'
		. '&webserviceVersion=1.0.0'
		. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(["message" => "No record with ID $this->id was found"]);
	}
}
