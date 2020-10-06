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
 * Class SiteRedshopbCustomerPriceGroup110crudCest
 * @since 2.8.0
 */
class SiteRedshopbCustomerPriceGroup110crudCest
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
	protected $name;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $new_name;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $erpId;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $id;

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('prepare for POST a new customer_price_group');
		$this->faker = Faker\Factory::create();

		$this->erpId = (int) $this->faker->numberBetween(100, 1000);
		$this->name = $this->faker->bothify('SiteRedshopbCustomer_Price_Group110crudCest ?##?');
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function create(redshopb2b $I)
	{
		$I->wantTo('POST a new customer_price_group');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->erpId"
			. "&name=$this->name"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readItem(ApiTester $I)
	{
		$I->wantTo('GET an existing customer_price_group');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readList(ApiTester $I)
	{
		$I->wantTo('GET a list of customer_price_group');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a customer_price_group using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->new_name = "new_" . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
			. "&name=$this->new_name"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['name' => $this->new_name]);
		$I->dontSeeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a customer_price_group using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
	}
}