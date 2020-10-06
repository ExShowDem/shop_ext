<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbDepartment110StructureCest
{
	public function prepare(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new category');
		$this->faker = Faker\Factory::create();
		$this->name  = $this->faker->bothify('SiteRedshopbDepartment110StructureCest deparment ?##?');
		$this->company_id = $I->getMainCompanyId();
		$this->parent_id = 0;

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
				'index.php'
				. '?option=redshopb&view=department'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.1.0'
				. "&name=$this->name"
				. "&company_id=$this->company_id"
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
				. '?option=redshopb&view=department'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.1.0'
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
										'href' => "$baseUrl/index.php?option=com_redshopb&view=department&webserviceVersion=1.1.0&webserviceClient=site&format=doc&api=Hal#{rel}",
										'title' => 'Documentation',
										'name' => 'redshopb-department',
										'templated' => true,
									],
							],
						'base' =>
							[
								'href' => "$baseUrl/?api=Hal",
								'title' => 'Default page',
							],
						'redshopb-department:list' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=department&webserviceVersion=1.1.0&webserviceClient=site&api=Hal",
							],
						'redshopb-department:self' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=department&webserviceVersion=1.1.0&webserviceClient=site&id=$this->id&api=Hal",
							],
						'redshopb-company' =>
							[
								'href' => "$baseUrl/index.php?option=redshopb&view=company&webserviceVersion=1.1.0&webserviceClient=site&id=$this->company_id&api=Hal",
								'title' => 'Aesir E-Commerce - Company Webservice',
								'templated' => true,
							],
						'redshopb-department' =>
							[
								'href' => "$baseUrl/index.php?option=redshopb&view=department&webserviceVersion=1.1.0&webserviceClient=site&id=$this->parent_id&api=Hal",
								'title' => 'Aesir E-Commerce - Parent Department Webservice',
								'templated' => true,
							],
						'redshopb-country' =>
							[
								'href' => '?api=Hal',
								'title' => 'Aesir E-Commerce - Country Webservice',
								'templated' => true,
							],
					],
				'id' => $this->id,
				'id_others' => [],
				'name' => $this->name,
				'name2' => '',
				'alias' => $I->getAlias($this->name),
				'image' => NULL,
				'company_id' => $this->company_id,
				'company_id_others' =>
					[
						0 => 'erp.main',
					],
				'parent_id' => 0,
				'parent_id_others' => NULL,
				'address_name1' => NULL,
				'address_name2' => '',
				'address_line1' => NULL,
				'address_line2' => NULL,
				'zip' => '',
				'city' => '',
				'country_code' => '',
				'state' => true
			]
		);
	}

	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}
