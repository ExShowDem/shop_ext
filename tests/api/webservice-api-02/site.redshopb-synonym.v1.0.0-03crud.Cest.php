<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbSynonym100CrudCest
{
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	public function create(ApiTester $I)
	{
		$I->wantTo('POST a new synonym');
		$this->word  = $this->faker->bothify('SiteRedshopbSynonym100CrudCest word ?##?');
		$this->synonym  = $this->faker->bothify('SiteRedshopbSynonym100CrudCest synonym 1 ?##?');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0',
			[
				'word' => $this->word,
				'shared' => true,
				'synonyms' => [$this->synonym]
			]
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-synonym:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The id of the new created synonym with name '$this->word' is: $this->id");
	}

	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing Synonym");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&id=' . $this->id
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['word' => strtolower($this->word)]);
		$I->seeResponseContainsJson(['shared' => true]);
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of Synonyms");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['word' => strtolower($this->word)]);
		$I->seeResponseContainsJson(['word' => strtolower($this->synonym)]);
	}

	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a new Synonym using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedWord = 'new_' . strtolower($this->word);
		$I->sendPUT('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
			. "&word=$this->updatedWord"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['word' => $this->word]);
		$I->seeResponseContainsJson(['word' => $this->updatedWord]);

		$I->comment("The synonym name has been modified to: $this->updatedWord");
	}

	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a new Synonym using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');

		// Remove the synonym
		$I->sendGET('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&filter=$this->synonym"
		);

		$ids = $I->grabDataFromResponseByJsonPath('$._embedded.item[0].id');
		$synonymId = $ids[0];

		$I->sendDELETE('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$synonymId"
		);

		$I->sendGET('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$synonymId"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');

	}
}
