<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbHoliday100erpCest
{
	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $name;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $day;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $month;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $nameUpdate;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $dayUpdate;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $monthUpdate;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $id;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $erp_id;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $erp_id_update;

	/**
	 * @since 2.5.0
	 */
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	/**
	 * @param \ApiTester $I
	 *
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function createWithErpId(ApiTester $I)
	{
		$I->wantTo('POST a new Holiday with ERP Id');
		$this->faker = Faker\Factory::create();
		$this->name             = $this->faker->bothify('holiday ?##?');
		$this->nameUpdate       = $this->faker->bothify('holiday update ?##?');
		$this->day              = rand(1,28);
		$this->dayUpdate        = rand(2,25);
		$this->month            = rand(1,12);
		$this->monthUpdate      = rand(1,5);
		$this->erp_id           = $this->faker->bothify('erp_id ?##?');
		$this->erp_id_update    = $this->faker->bothify('erp_id_update ?##?');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
			'index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&name=$this->name"
			. '&country_id=1'
			. "&day=$this->day"
			. "&month=$this->month"
			. "&erp_id=$this->erp_id"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-holiday:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The ERP Id of the new created holiday with name '$this->name' is: $this->id");
	}

	/**
	 * @param \ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function readItemUsingErpId(ApiTester $I)
	{
		$I->wantTo("GET an existing Table lock");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
			. "&erp_id=$this->erp_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param \ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function updateUsingErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a new Table lock using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPUT('index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
			. "&erp_id=$this->erp_id_update"
			. "&name=$this->nameUpdate"
			. "&month=$this->monthUpdate"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
			. "&erp_id=$this->erp_id_update"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param \ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function deleteERP(ApiTester $I)
	{
		$I->wantTo('DELETE a new Table lock using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
			. "&erp_id=$this->erp_id_update"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}
}
