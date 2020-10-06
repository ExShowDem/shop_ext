<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbManufacturer120StructureCest
{
	/**
	 * @param ApiTester $I
	 */
	public function prepare(ApiTester $I)
	{
		$I->wantTo('POST a new manufacturer');
		$this->faker = Faker\Factory::create();
		$this->name  = $this->faker->bothify('SiteRedshopbManufacturer120StructureCest manufacturer ?##?');
		$this->erpid = $this->faker->numberBetween(1, 9999);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->erpid"
			. "&name=$this->name"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created manufacturer with name '$this->name' is: '$this->id'");
	}

	/**
	 * @param ApiTester $I
	 */
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing manufacturer with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' => [
					'curies' => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=manufacturer&webserviceVersion=1.2.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-manufacturer',
							'templated' => true
						]
					],
					'base' => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page',
					],
					'redshopb-manufacturer:list' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=manufacturer&webserviceVersion=1.2.0&webserviceClient=site&api=Hal",
					],
					'redshopb-manufacturer:self' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=manufacturer&webserviceVersion=1.2.0&webserviceClient=site&id=$this->id&api=Hal",
					],
					'redshopb-parent-manufacturer' => [
						'href'  => "$baseUrl/index.php?option=redshopb&view=manufacturer&webserviceVersion=1.1.0&webserviceClient=site&id=0&api=Hal",
						'title' => 'Aesir E-Commerce - Manufacturer Webservice',
						'templated' => true
					]
				],
				'id'                => $this->id,
				'id_others'         => ["erp.$this->erpid"],
				'name'              => $this->name,
				'parent_id'         => 0,
				'featured'          => false,
				'state'             => true,
				'image'             => null,
				'category'          => '',
				'parent_id_others'  => null
			]
		);
	}

	/**
	 * @param ApiTester $I
	 */
	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}
