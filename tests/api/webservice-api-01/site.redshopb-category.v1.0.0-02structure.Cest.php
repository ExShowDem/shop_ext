<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCategory100StructureCest
{
	public function prepare(ApiTester $I)
	{
		$I->wantTo('POST a new category');
		$this->faker = Faker\Factory::create();
		$this->name  = $this->faker->bothify('SiteRedshopbCategory100StructureCest category ?##?');
		$this->erpid = $this->faker->numberBetween(1, 9999);

		$I->sendPOST('',['username' => 'admin', 'password' => 'admin']);
		$token = $I->grabResponse('token');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
				'index.php'
				. '?option=redshopb&view=category'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.0.0'
				. "&id=$this->erpid"
				. "&name=$this->name"
				. '&company_id=1'
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created category with name '$this->name' is: $this->id");
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing category with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=category'
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
					'curies'                   => [
							0 => [
									'href'      => "$baseUrl/index.php?option=com_redshopb&view=category&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
									'title'     => 'Documentation',
									'name'      => 'redshopb-category',
									'templated' => true
							]
					],
					'base'                     => [
							'href'  => "$baseUrl/?api=Hal",
							'title' => 'Default page',
					],
					'redshopb-category:list'   => [
							'href' => "$baseUrl/index.php?option=com_redshopb&view=category&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
					],
					'redshopb-category:self'   => [
							'href' => "$baseUrl/index.php?option=com_redshopb&view=category&webserviceVersion=1.0.0&webserviceClient=site&id=$this->id&api=Hal",
					],
					'redshopb-category-parent' => [
							'href'      => "$baseUrl/index.php?option=redshopb&view=category&webserviceVersion=1.0.0&webserviceClient=site&id=0&api=Hal",
							'title'     => 'Aesir E-Commerce - Parent Category Webservice',
							'templated' => true
					]
				],
				'id'                => $this->id,
				'id_others'         => ["erp.$this->erpid"],
				'name'              => $this->name,
				'alias'             => $I->getAlias($this->name),
				'company_id'        => 1,
				'company_id_others' => [],
				'parent_id'         => 0,
				'parent_id_others'  => null,
				'image'             => null,
				'description'       => '',
				'template_code'     => '',
				'state'             => true
			]
		);
	}

	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=category'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}
