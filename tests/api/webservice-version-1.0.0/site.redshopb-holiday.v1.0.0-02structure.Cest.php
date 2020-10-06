<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbHoliday100StructureCest
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
	protected $id;

	/**
	 * @param \ApiTester $I
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function prepare(ApiTester $I)
	{
		$I->wantTo('POST a new holiday');
		$this->faker = Faker\Factory::create();
		$this->name  = $this->faker->bothify('holiday ?##?');
		$this->day   = rand(1,28);
		$this->month = rand(1,12);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
				'index.php'
				. '?option=redshopb&view=holiday'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.0.0'
				. "&name=$this->name"
				. '&country_id=59'
				. "&day=$this->day"
				. "&month=$this->month"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created holiday with name '$this->name' is: $this->id");
	}

	/**
	 * @param \ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing holiday with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=holiday'
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
									'href'      => "$baseUrl/index.php?option=com_redshopb&view=holiday&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
									'title'     => 'Documentation',
									'name'      => 'redshopb-holiday',
									'templated' => true
							]
					],
					'base'                     => [
							'href'  => "$baseUrl/?api=Hal",
							'title' => 'Default page',
					],
					'redshopb-holiday:list'   => [
							'href' => "$baseUrl/index.php?option=com_redshopb&view=holiday&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
					],
					'redshopb-holiday:self'   => [
							'href' => "$baseUrl/index.php?option=com_redshopb&view=holiday&webserviceVersion=1.0.0&webserviceClient=site&id=$this->id&api=Hal",
					]
				],
				'id'                => $this->id,
				'name'              => $this->name,
				'day'               => $this->day,
				'month'             => $this->month,
				'year'              => '0',
				'country_id'        => '59'
			]
		);
	}

	/**
	 * @param \ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}
