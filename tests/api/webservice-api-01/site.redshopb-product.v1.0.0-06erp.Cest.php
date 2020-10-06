<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProduct100ErpCest
{
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	public function createWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product with ERP Id');
		$this->name  = $this->faker->bothify('SiteRedshopbProduct100erpCest product ?##?');
		$this->erpid = $this->faker->numberBetween(1, 9999);
		$this->sku  = $this->faker->randomNumber(3);
		$this->category['name'] = $this->faker->bothify('SiteRedshopbProduct100ErpCest category ?##?');
		$this->category['id']  = (int) $I->createCategory($this->category['name']);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&name=$this->name"
			. "&sku=$this->sku"
			. "&category_id=" . $this->category['id']
			. "&id=$this->erpid"
		);


		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->erpid]]);
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The ERP Id of the new created product with name '$this->name' is: $this->erpid");
	}

	public function readItemUsingErpId(ApiTester $I)
	{
		$I->wantTo("GET an existing product with its ERP id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['state' => true]);
	}

	public function updateUsingErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a product with PUT using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedName = 'new_erp_' . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
			. "&name=$this->updatedName"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['name' => $this->updatedName]);

		$I->comment("The product name has been modified to: $this->updatedName");

		$this->name = $this->updatedName;
	}

	public function updateErpidUsingErpid(ApiTester $I)
	{
		$I->wantTo('UPDATE a product ERP id using the ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->old_erpid = $this->erpid;
		$this->erpid = $this->faker->numberBetween(1, 9999);
		$I->sendPUT('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->old_erpid"
			. "&erp_id=$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['id_others' => ['erp.' . $this->old_erpid]]);
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->erpid]]);

		$I->comment("The product erp ID has been modified to: $this->erpid");
	}

	public function unpublishERP(ApiTester $I)
	{
		$I->wantTo('UNPUBLISH a product with POST using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&task=unpublish"
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['state' => false]);

		$I->comment("The product $this->name has been unpublished");
	}

	public function publishERP(ApiTester $I)
	{
		$I->wantTo('PUBLISH a product with POST using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&task=publish"
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['state' => true]);

		$I->comment("The product $this->name has been published");
	}

	public function translateERP(ApiTester $I)
	{
		$I->wantTo('Create a translation for a product with POST using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->translatedName = 'french-' . $this->name;
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&task=translate'
			. '&language=fr-FR'
			. "&id=erp.$this->erpid"
			. "&name=$this->translatedName"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
			. '&language=fr-FR'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->translatedName]);
	}

	public function translateRemoveERP(ApiTester $I)
	{
		$I->wantTo('Delete a translation for a product with POST using its internal id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&task=translateRemove'
			. '&language=fr-FR'
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
			. '&language=fr-FR'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->translatedName]);
		$I->SeeResponseContainsJson(['name' => $this->name]);
	}

	public function deleteERP(ApiTester $I)
	{
		$I->wantTo('DELETE a product with DELETE using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->deleteCategory($this->category['id'], '1.0.0');
	}
}
