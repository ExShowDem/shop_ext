<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbStockroom100CrudCest
{
	/**
	 * @var Stockroom to be used in the test
	 */
	public $stockroom;

	/**
	 * @var The webservice version to be used
	 */
	public $webserviceVersion = '1.0.0';

	public function create(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new stockroom');
		$this->faker = Faker\Factory::create();
		$this->stockroom['name']  = $this->faker->bothify('SiteRedshopbStockroom120CrudCest stockroom ?##?');
		$this->stockroom['company_id'] = $I->getMainCompanyId();

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
				'index.php'
				. '?option=redshopb&view=stockroom'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=' . $this->webserviceVersion
				. '&name=' . $this->stockroom['name']
				. '&company_id=' . $this->stockroom['company_id']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-stockroom:self']['href']);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->stockroom['id'] = $ids[0];
		$I->comment('The id of the newly created stockroom with name ' . $this->stockroom['name'] . ' is: ' . $this->stockroom['id']);
	}

	public function readItem(ApiTester $I)
	{
		$I->wantTo('GET an existing Stockroom');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroom['id']
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->stockroom['name']]);
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo('GET an existing Stockroom');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->stockroom['id']]);
		$I->seeResponseContainsJson(['name' => $this->stockroom['name']]);
	}

	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a new Product using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->stockroom['updatedName'] = 'new_' . $this->stockroom['name'];

		$I->sendPUT('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroom['id']
			. '&name=' . $this->stockroom['updatedName']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroom['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->stockroom['name']]);
		$I->seeResponseContainsJson(['name' => $this->stockroom['updatedName']]);

		$I->comment('The Stockroom name has been modified to: ' . $this->stockroom['updatedName']);
	}

	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a new Stockroom using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroom['id']
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroom['id']
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}
}
