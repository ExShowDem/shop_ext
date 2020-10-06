<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Step\Api\redshopb2b as redshopb2b;

class SiteRedshopbHoliday100FilteringCest
{
	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $holiday1 = array();

	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $holiday2 = array();

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$I->wantTo('Create create holiday for filter to be tested');

		$I->comment('I create a holiday1 to be filtered by SEARCH');
		$this->holiday1['name']         = (string) $this->faker->bothify('holiday1?##?');
		$this->holiday1['day']          = rand(1,28);
		$this->holiday1['month']        = rand(1,12);
		$this->holiday1['country_id']   = '59';
		$this->holiday1['id'] = (int) $I->createHoliday($this->holiday1['name'], $this->holiday1['day'], $this->holiday1['month'], $this->holiday1['country_id'], '1.0.0');

		$I->comment('I create a holiday2 to be filtered by SEARCH');
		$this->holiday2['name']         = (string) $this->faker->bothify('holiday2?##?');
		$this->holiday2['day']          = rand(1,28);
		$this->holiday2['month']        = rand(1,12);
		$this->holiday2['country_id']   = '1';
		$this->holiday2['id'] = (int) $I->createHoliday($this->holiday2['name'], $this->holiday2['day'], $this->holiday2['month'], $this->holiday2['country_id'], '1.0.0');
	}

	/**
	 * @param ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of history filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&filter[search]=' . $this->holiday1['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->holiday1['id']]);
		$I->seeResponseContainsJson(['name' => $this->holiday1['name']]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->deleteHoliday($this->holiday1['id'], '1.0.0');
		$I->deleteHoliday($this->holiday2['id'], '1.0.0');
	}
}
