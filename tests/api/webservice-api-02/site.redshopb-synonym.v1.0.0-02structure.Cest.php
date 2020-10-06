<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbSynonym100StructureCest
{
	public function prepare(ApiTester $I)
	{
		$I->wantTo('POST a new synonym');
		$this->faker = Faker\Factory::create();
		$this->word  = strtolower($this->faker->bothify('SiteRedshopbSynonym100StructureCest word ?##?'));
		$this->erpid = $this->faker->numberBetween(1, 9999);
		$this->synonym  = strtolower($this->faker->bothify('SiteRedshopbSynonym100CrudCest synonym 1 ?##?'));
		$this->synonym2  = strtolower($this->faker->bothify('SiteRedshopbSynonym100CrudCest synonym 2 ?##?'));


		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
				'index.php'
				. '?option=redshopb&view=synonym'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.0.0',
			[
				'id'		=> $this->erpid,
				'word'		=> $this->word,
				'shared'	=> true,
				'synonyms'	=> [$this->synonym, $this->synonym2]
			]
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created synonym with name '$this->word' is: $this->id");
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing synonym with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=synonym'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.0.0'
				. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
			'_links' =>
				[
				  'curies' =>
					  [
						  0 =>
							  [
								  'href' => "$baseUrl/index.php?option=com_redshopb&view=synonym&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
								  'title' => 'Documentation',
								  'name' => 'redshopb-synonym',
								  'templated' => true,
							  ],
					  ],
				  'base' =>
					  [
						  'href' => "$baseUrl/?api=Hal",
						  'title' => 'Default page',
					  ],
				  'redshopb-synonym:list' =>
					  [
						  'href' => "$baseUrl/index.php?option=com_redshopb&view=synonym&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
					  ],
				  'redshopb-synonym:self' =>
					  [
						  'href' => "$baseUrl/index.php?option=com_redshopb&view=synonym&webserviceVersion=1.0.0&webserviceClient=site&id=$this->id&api=Hal",
					  ],
  					],
  					'id' => $this->id,
  					'synonyms' =>
  					[
						  0 => $this->synonym,
						  1 => $this->synonym2,
  					],
  					'shared' => true,
  					'word' => $this->word,
  					'id_others' => [0 => "erp.$this->erpid"]
				]
		);
	}

	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=synonym'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}
