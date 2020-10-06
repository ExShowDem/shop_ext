<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCompany150CrudCest
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
	protected $ErpId;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $mainCompanyId;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $address_line1;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $zip;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $city;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $country_code;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $currency_code;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $updatedName;

	/**
	 * @since 2.5.0
	 */
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function create(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new company');
		$this->name = $this->faker->bothify('SiteRedshopbCompany150CrudCest company ?##?');
		$this->ErpId = rand(1, 9999);
		$this->mainCompanyId = $I->getMainCompanyId('1.5.0');
		$this->address_line1 = 'test address';
		$this->zip = $this->faker->postcode;
		$this->city = $this->faker->city;
		$this->country_code = 'DK';
		$this->currency_code = 'DKK';
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&name=$this->name"
			. "&id=$this->ErpId"
			. "&parent_id=$this->mainCompanyId"
			. "&address_line1=$this->address_line1"
			. "&zip=$this->zip"
			. "&city=$this->city"
			. "&country_code=$this->country_code"
			. "&currency_code=$this->currency_code"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-company:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The id of the new created company with name '$this->name' is: $this->id");
	}

	/**
	 * @param ApiTester $I
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing company");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of company");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a new company using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedName = 'new_' . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=$this->id"
			. "&name=$this->updatedName"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->name]);
		$I->seeResponseContainsJson(['name' => $this->updatedName]);

		$I->comment("The company name has been modified to: $this->updatedName");
	}

	/**
	 * @param ApiTester $I
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a new company using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE(
			'index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET(
			'index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(["message" => "No record with ID $this->id was found"]);
	}
}
