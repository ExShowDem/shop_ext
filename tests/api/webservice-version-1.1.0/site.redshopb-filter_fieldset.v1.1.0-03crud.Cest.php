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
 * Class SiteRedshopbFilterFieldset110crudCest
 * @since 2.8.0
 */
class SiteRedshopbFilterFieldset110crudCest
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
	protected $erpId;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $new_name;

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('Prepare data needed for the test');

		$this->faker = Faker\Factory::create();
		$this->name = $this->faker->bothify('SiteRedshopbFilterFieldset110crudCest filter ?##?');
		$this->new_name = 'new_' . $this->name;
		$this->erpId = (int) $this->faker->numberBetween(100, 1000);
	}

	/**
	 * @param ApiTester $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function create(ApiTester $I)
	{
		$I->wantTo('POST a new filter fieldset');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=filter_fieldset'
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
		$I->wantTo('GET an existing filter fieldset');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
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
		$I->wantTo("GET a list of filter fieldset");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id' => $this->id]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a filter fieldset using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPUT('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
			. "&name=$this->new_name"
		);
		$I->seeResponseCodeis(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponsecodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['name' => $this->new_name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a filter fieldset using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsjson();
	}
}