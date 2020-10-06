<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbSynonym100ErpCest
{
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	public function create(ApiTester $I)
	{
		$I->wantTo('POST a new synonym');
		$this->word  = $this->faker->bothify('SiteRedshopbSynonym100CrudCest word ?##?');
		$this->erpid = $this->faker->numberBetween(1, 9999);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&word=$this->word"
			. "&id=$this->erpid"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-synonym:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->erpid]]);
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The id of the new created synonym with name '$this->word' is: $this->erpid");
	}

	public function readItemUsingErpId(ApiTester $I)
	{
		$I->wantTo("GET an existing Synonym");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['word' => strtolower($this->word)]);
		$I->seeResponseContainsJson(['shared' => false]);
	}

	public function updateUsingErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a new Synonym using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedWord = 'new_' . strtolower($this->word);
		$I->sendPUT('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
			. "&word=$this->updatedWord"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['word' => $this->word]);
		$I->seeResponseContainsJson(['word' => $this->updatedWord]);

		$I->comment("The synonym name has been modified to: $this->updatedWord");
	}

	public function deleteERP(ApiTester $I)
	{
		$I->wantTo('DELETE a new Synonym using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}
}
