<?php

/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
class SiteRedshopbTag100StructureCest
{
	public function prepare(ApiTester $I)
	{
		$I->wantTo('POST a new tag');
		$this->faker      = Faker\Factory::create();
		$this->name       = $this->faker->bothify('SiteRedshopbTag100StructureCest tag ?##?');
		$this->erpid      = $this->faker->numberBetween(1, 9999);
		$this->type       = $this->faker->word;
		$this->company_id = 1; // Main company

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
			'index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->erpid"
			. "&name=$this->name"
			. "&type=$this->type"
			. "&company_id=$this->company_id"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids      = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created tag with name '$this->name' is: $this->id");
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing tag with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
			'index.php'
			. '?option=redshopb&view=tag'
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
				'_links'            => [
					'curies'              => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=tag&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-tag',
							'templated' => true
						]
					],
					'base'                => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page',
					],
					'redshopb-tag:list'   => [
						'href' => "$baseUrl/index.php?option=com_redshopb&view=tag&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
					],
					'redshopb-tag:self'   => [
						'href' => "$baseUrl/index.php?option=com_redshopb&view=tag&webserviceVersion=1.0.0&webserviceClient=site&id=$this->id&api=Hal",
					],
					'redshopb-company'    => [
						'href'      => "$baseUrl/index.php?option=redshopb&view=company&webserviceVersion=1.1.0&webserviceClient=site&id=1&api=Hal",
						'title'     => 'Aesir E-Commerce - Company Webservice',
						'templated' => true,
					],
					'redshopb-parent-tag' => [
						'href'      => "$baseUrl/index.php?option=redshopb&view=tag&webserviceVersion=1.0.0&webserviceClient=site&id=0&api=Hal",
						'title'     => 'Aesir E-Commerce - Tag Webservice',
						'templated' => true,
					],

				],
				'id'                => $this->id,
				'id_others'         => ["erp.$this->erpid"],
				'name'              => $this->name,
				'alias'             => $this->type . '-' . $I->getAlias($this->name),
				'type'              => $this->type,
				'parent_id'         => 0,
				'parent_id_others'  => null,
				'image'             => null,
				'company_id'        => $this->company_id,
				'company_id_others' => [],
				'state'             => true
			]
		);
	}

	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE(
			'index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}
