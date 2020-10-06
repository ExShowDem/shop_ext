<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbTag110ErpCest
{
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	public function create(ApiTester $I)
	{
		$I->wantTo('POST a new tag with erp id');
		$this->name = $this->faker->bothify('SiteRedshopbTag110ErpCest tag ?##?');
		$this->erpid = $this->faker->numberBetween(1, 9999);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->erpid"
			. "&name=$this->name"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created tag with name '$this->name' is: $this->id");
	}

	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing tag with its ERP id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=tag'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.1.0'
					. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a tag with PUT using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedName = 'new_erp_' . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid"
			. "&name=$this->updatedName"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['name' => $this->updatedName]);

		$I->comment("The tag name has been modified to: $this->updatedName");

		$this->name = $this->updatedName;
	}

	public function updateErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a tag ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->old_erpid = $this->erpid;
		$this->erpid = $this->faker->numberBetween(1, 9999);
		$I->sendPUT('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->old_erpid"
			. "&erp_id=$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['id_others' => ['erp.' . $this->old_erpid]]);
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->erpid]]);

		$I->comment("The tag erp ID has been modified to: $this->erpid");
	}

	public function unpublish(ApiTester $I)
	{
		$I->wantTo('UNPUBLISH a tag with POST using its internal id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&task=unpublish"
			. "&id=erp.$this->erpid"
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
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['state' => false]);

		$I->comment("The tag $this->name has been unpublished");
	}

	public function publish(ApiTester $I)
	{
		$I->wantTo('PUBLISH a tag with POST using its internal id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&task=publish"
			. "&id=erp.$this->erpid"
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
		$I->seeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['state' => true]);

		$I->comment("The tag $this->name has been published");
	}

	public function translate(ApiTester $I)
	{
		$I->wantTo('Create a translation for a Tag with POST using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->translatedName = 'french-' . $this->name;
		$I->sendPOST('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translate'
			. '&language=fr-FR'
			. "&id=erp.$this->erpid"
			. "&name=$this->translatedName"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid"
			. '&language=fr-FR'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->translatedName]);
	}

	public function translateRemoveERP(ApiTester $I)
	{
		$I->wantTo('Delete a translation for a Tag with POST using its internal id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translateRemove'
			. '&language=fr-FR'
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid"
			. '&language=fr-FR'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->translatedName]);
		$I->SeeResponseContainsJson(['name' => $this->name]);
	}

	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a tag with DELETE using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
		. '?option=redshopb&view=tag'
		. '&api=Hal'
		. '&webserviceClient=site'
		. '&webserviceVersion=1.1.0'
		. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}
}
