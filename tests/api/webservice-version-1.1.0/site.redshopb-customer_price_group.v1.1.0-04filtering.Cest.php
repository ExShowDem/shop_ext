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
 * Class SiteRedshopbCustomerPriceGroup110filteringCest
 * @since 2.8.0
 */
class SiteRedshopbCustomerPriceGroup110filteringCest
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
	protected $customer_price_groupA;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $customer_price_groupA_id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $customer_price_groupB;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $customer_price_groupB_id;

	/**
	 * Prepares the following structure
	 *
	 * +-----------------------+-------+
	 * | customer_price_group  | state |
	 * +-----------------------+-------+
	 * | customer_price_groupA | true  |
	 * | customer_price_groupB | false |
	 * +-----------------------+-------+
	 */

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('I will create what is necessary for the tests.');
		$this->faker = Faker\Factory::create();

		$I->comment('I create two customer_price_group');
		$this->customer_price_groupA['erpId'] = (int) $this->faker->numberBetween(100, 1000);
		$this->customer_price_groupA['name'] = $this->faker->bothify('SiteRedshopbCustomer_Price_Group110filteringCest groupA ?##?');

		$this->customer_price_groupB['erpId'] = (int) $this->faker->numberBetween(100, 1000);
		$this->customer_price_groupB['name'] = $this->faker->bothify('SiteRedshopbCustomer_Price_Group110filteringCest groupB ?##?');
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function create(redshopb2b $I)
	{
		$I->wantTo('POST two customer_price_group to filtering');
		$I->comment('I create the customer_price_groupA');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=" . $this->customer_price_groupA['erpId']
			. "&name=" . $this->customer_price_groupA['name']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->customer_price_groupA_id = $ids[0];

		$I->comment('I create the customer_price_groupB');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=" . $this->customer_price_groupB['erpId']
			. "&name=" . $this->customer_price_groupB['name']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->customer_price_groupB_id = $ids[0];

		$I->comment('I unpublish the customer_price_groupB to be filtered by state');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=" . $this->customer_price_groupB_id
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of customer_price_group filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[search]=" . $this->customer_price_groupA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->customer_price_groupA['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->customer_price_groupB['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readListFilteredByState(ApiTester $I)
	{
		$I->wantTo('GET a list of customer_price_group filtered by state');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[state]=false"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->customer_price_groupB_id]);
		$I->seeResponseContainsJson(['name' => $this->customer_price_groupB['name']]);
		$I->dontSeeResponseContainsJson(['id' => $this->customer_price_groupA_id]);
		$I->dontSeeResponseContainsJson(['name' => $this->customer_price_groupA['name']]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('customer_price_group', $this->customer_price_groupA_id, '1.1.0');
		$I->webserviceCrudDelete('customer_price_group', $this->customer_price_groupB_id, '1.1.0');
	}
}