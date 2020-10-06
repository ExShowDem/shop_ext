<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbManufacturer120ErpCest
{
	/**
	 * @param ApiTester $I
	 */
	public function prepare(ApiTester $I)
	{
		$this->faker = Faker\Factory::create();
	}

	/**
	 * @param ApiTester $I
	 */
	public function createWithErpId(ApiTester $I)
	{
		$I->wantTo('POST a new manufacturer with ERP Id');
		$this->name  = $this->faker->bothify('SiteRedshopbManufacturer120ErpCest manufacturer ?##?');
		$this->erpid = $this->faker->numberBetween(1, 9999);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->erpid"
			. "&name=$this->name"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-manufacturer:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->erpid]]);
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The ERP Id of the new created manufacturer with name '$this->name' is: $this->erpid");
	}

	/**
	 * @param ApiTester $I
	 */
	public function readItemUsingErpId(ApiTester $I)
	{
		$I->wantTo("GET an existing manufacturer with its ERP id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['state' => true]);
	}

	/**
	 * @param ApiTester $I
	 */
	public function updateUsingErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a manufacturer with PUT using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedName = 'new_erp_' . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpid"
			. "&name=$this->updatedName"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['name' => $this->updatedName]);

		$I->comment("The manufacturer name has been modified to: $this->updatedName");

		$this->name = $this->updatedName;
	}

	/**
	 * @param ApiTester $I
	 */
	public function updateErpidUsingErpid(ApiTester $I)
	{
		$I->wantTo('UPDATE a manufacturer ERP id using the ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->old_erpid = $this->erpid;
		$this->erpid = $this->faker->numberBetween(1, 9999);
		$I->sendPUT('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->old_erpid"
			. "&erp_id=$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['id_others' => ['erp.' . $this->old_erpid]]);
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->erpid]]);

		$I->comment("The manufacturer erp ID has been modified to: $this->erpid");
	}

	/**
	 * @param ApiTester $I
	 */
	public function unpublishERP(ApiTester $I)
	{
		$I->wantTo('UNPUBLISH a manufacturer with POST using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&task=unpublish"
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['state' => false]);

		$I->comment("The manufacturer $this->name has been unpublished");
	}

	/**
	 * @param ApiTester $I
	 */
	public function publishERP(ApiTester $I)
	{
		$I->wantTo('PUBLISH a manufacturer with POST using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&task=publish"
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['state' => true]);

		$I->comment("The manufacturer $this->name has been published");
	}

	/**
	 * @param ApiTester $I
	 */
	public function deleteERP(ApiTester $I)
	{
		$I->wantTo('DELETE a manufacturer with DELETE using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}
}
