<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Step\Api\redshopb2b as redshopb2b;

class SiteRedshopbCompany150erpCest
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
	protected $erpid;

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
	 * @var
	 * @since 2.5.0
	 */
	protected $old_erpid;

	/**
	 * @since 2.5.0
	 */
	public function prepare()
	{
		$this->faker = Faker\Factory::create();
	}

	/**
	 * @param ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function createWithErpId(redshopb2b $I)
	{
		$I->wantTo('POST a new company with ERP Id');
		$this->erpid = $this->faker->numberBetween(1, 9999);
		$this->name = $this->faker->bothify('SiteRedshopbCompany150CrudCest company ?##?');
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
			. "&id=$this->erpid"
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
		$I->comment("The id of the new created company with name '$this->name' is: $this->erpid");
	}

	/**
	 * @param ApiTester $I
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function readItemUsingErpId(ApiTester $I)
	{
		$I->wantTo("GET an existing company with its ERP id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=erp.$this->erpid"
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
	public function updateUsingErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a new company using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedName = 'new_' . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=erp.$this->erpid"
			. "&name=$this->updatedName"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=erp.$this->erpid"
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
	public function updateErpidUsingErpid(ApiTester $I)
	{
		$I->wantTo('UPDATE a company ERP id using the ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->old_erpid = $this->erpid;
		$this->erpid = $this->faker->numberBetween(1, 9999);
		$I->sendPUT('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=erp.$this->old_erpid"
			. "&erp_id=$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['id_others' => ['erp.' . $this->old_erpid]]);
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->erpid]]);

		$I->comment("The company erp ID has been modified to: $this->erpid");
	}

	/**
	 * @param redshopb2b $I
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function unpublishERP(redshopb2b $I)
	{
		$I->wantTo('UNPUBLISH a company with POST using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&task=unpublish"
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => false]);

		$I->comment("The company $this->name has been unpublished");
	}

	/**
	 * @param redshopb2b $I
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function publishERP(redshopb2b $I)
	{
		$I->wantTo('PUBLISH a company with POST using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&task=publish"
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => true]);

		$I->comment("The company $this->name has been published");
	}

	/**
	 * @param ApiTester $I
	 * @throws \Exception
	 * @since 2.5.0
	 */
	public function deleteERP(ApiTester $I)
	{
		$I->wantTo('DELETE a company with DELETE using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}
}
