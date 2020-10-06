<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCollection100StructureCest
{
	public function prepare(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new collection');
		$this->faker = Faker\Factory::create();
		$this->name  = $this->faker->bothify('SiteRedshopbCollection100StructureCest collection ?##?');
		$this->company_id  = $I->getMainCompanyId();
		$this->currency_id = $I->getCurrencyId('Euro');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
				'index.php'
				. '?option=redshopb&view=collection'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.0.0'
				. "&name=$this->name"
				. "&company_id=$this->company_id"
				. "&currency_id=$this->currency_id"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-collection:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids      = $I->grabDataFromResponseByJsonPath('$.fields.id');
		$this->id = $ids[0];
		$I->comment("The id of the new created collection with name '$this->name' is: $this->id");
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing collection with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=collection'
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
												'href' => "$baseUrl/index.php?option=com_redshopb&view=collection&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
												'title' => 'Documentation',
												'name' => 'redshopb-collection',
												'templated' => true,
											],
							],
						'base' =>
							[
									'href' => "$baseUrl/?api=Hal",
									'title' => 'Default page',
							],
						'redshopb-collection:list' =>
							[
									'href' => "$baseUrl/index.php?option=com_redshopb&view=collection&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
							],
						'redshopb-collection:self' =>
							[
									'href' => "$baseUrl/index.php?option=com_redshopb&view=collection&webserviceVersion=1.0.0&webserviceClient=site&id=$this->id&api=Hal",
							],
						'redshopb-company' =>
							[
									'href' => "$baseUrl/index.php?option=redshopb-company&webserviceVersion=1.0.0&webserviceClient=site&id=$this->company_id&api=Hal",
									'title' => 'Aesir E-Commerce - Company Webservice',
									'templated' => true,
							],
						'redshopb-currency' =>
							[
									'href' => "$baseUrl/index.php?option=redshopb-currency&webserviceVersion=1.0.0&webserviceClient=site&id=$this->currency_id&api=Hal",
									'title' => 'Aesir E-Commerce - Currency Webservice',
									'templated' => true,
							],
					],
				'fields' =>
					[
						'id' => $this->id,
						'name' => $this->name,
						'company_id' => $this->company_id,
						'currency_id' => $this->currency_id,
						'state' => true,
						'departments' => NULL,
					]
			]
		);
	}

	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=collection'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}
