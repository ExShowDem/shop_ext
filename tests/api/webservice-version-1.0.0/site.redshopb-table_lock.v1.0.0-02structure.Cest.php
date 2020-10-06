<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbTableLock100StructureCest
{
	public function prepare(ApiTester $I)
	{
		$I->wantTo('POST a new table lock');
		$this->faker = Faker\Factory::create();
		$this->table_name  = $this->faker->bothify('SiteRedshopbTableLock100AvailabilityCest table_name ?##?');
		$this->column_name  = $this->faker->bothify('SiteRedshopbTableLock100AvailabilityCest column_name ?##?');
		$this->table_id  = $this->faker->numberBetween(1,9999);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
				'index.php'
				. '?option=redshopb&view=table_lock'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.0.0'
				. "&table_name=$this->table_name"
				. "&table_id=$this->table_id"
				. "&column_name=$this->column_name"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created table lock with name '$this->table_name' is: $this->table_id");
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing table lock with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=table_lock'
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
									'href'      => "$baseUrl/index.php?option=com_redshopb&view=table_lock&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
									'title'     => 'Documentation',
									'name'      => 'redshopb-table_lock',
									'templated' => true
							]
					],
					'base'                     => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page',
					],
					'redshopb-table_lock:list'   => [
						'href' => "$baseUrl/index.php?option=com_redshopb&view=table_lock&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
					],
					'redshopb-table_lock:self'   => [
						'href' => "$baseUrl/index.php?option=com_redshopb&view=table_lock&webserviceVersion=1.0.0&webserviceClient=site&id=$this->id&api=Hal",
					],
				],
				'id'                => $this->id,
				'table_name'        => $this->table_name,
				'table_id'          => $this->table_id,
				'column_name'       => $this->column_name,
				'locked_method'     => "User",
				'id_others'         => []
			]
		);
	}

	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=table_lock'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}
