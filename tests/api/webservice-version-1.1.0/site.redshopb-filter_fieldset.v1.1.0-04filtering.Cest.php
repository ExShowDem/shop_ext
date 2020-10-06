<?php
/**
 * @package     Aesir E-Commerce.
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Step\Api\redshopb2b;

/**
 * Class SiteRedshopbFilterFieldset110filteringCest
 * @since 2.8.0
 */
class SiteRedshopbFilterFieldset110filteringCest
{
	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $filterA;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $filterA_id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $filterB;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $filterB_id;

	/**
	 * Prepares the following structure
	 *
	 * +---------+---------+
	 * | filter  |  state  |
	 * +---------+---------+
	 * | filterA |  true   |
	 * | filterB |  false  |
	 * +---------+---------+
	 */

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('Prepare data needed for the test');

		$this->faker = Faker\Factory::create();
		$I->comment('I create the filterA');
		$this->filterA['name'] = $this->faker->bothify('SiteRedshopbFilterFieldset110filteringCest filterA ?##?');
		$this->filterA['erpId'] = (int) $this->faker->numberBetween(100, 1000);

		$I->comment('I create the filteB');
		$this->filterB['name'] = $this->faker->bothify('SiteRedshopbFilterFieldset110filteringCest filterB ?##?');
		$this->filterB['erpId'] = (int) $this->faker->numberBetween(100, 1000);
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function create(redshopb2b $I)
	{
		$I->wantTo('POST two filter fieldset to filtering');
		$I->comment('I create the filterA');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=" . $this->filterA['erpId']
			. "&name=" . $this->filterA['name']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->filterA_id = $ids[0];

		$I->comment('I create the filterB');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=" . $this->filterB['erpId']
			. "&name=" . $this->filterB['name']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->filterB_id = $ids[0];

		$I->comment('I unpublish the filterB to be filtered by state');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->filterB_id"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of filter fieldset filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[search]=" . $this->filterA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->filterA['name']]);
		$I->seeResponseContainsJson(['id' => $this->filterA_id]);
		$I->dontseeResponseContainsJson(['name' => $this->filterB['name']]);
		$I->dontseeResponseContainsJson(['id' => $this->filterB_id]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readListFilteredByState(ApiTester $I)
	{
		$I->wantTo("GET a list of filter fieldset filtered by state");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[state]=false"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->filterB['name']]);
		$I->seeResponseContainsJson(['id' => $this->filterB_id]);
		$I->dontseeResponseContainsJson(['name' => $this->filterA['name']]);
		$I->dontseeResponseContainsJson(['id' => $this->filterA_id]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('filter_fieldset', $this->filterA_id, '1.1.0');
		$I->webserviceCrudDelete('filter_fieldset', $this->filterB_id, '1.1.0');
	}
}