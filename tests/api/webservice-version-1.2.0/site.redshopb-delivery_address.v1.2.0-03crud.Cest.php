<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License Version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbDelivery_address120crudCest
 * @since 2.5.0
 */
class SiteRedshopbDelivery_address120crudCest
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
	protected $name1;

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
	protected $id;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $updatedName1;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function create(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new delivery address');
		$this->faker = Faker\Factory::create();
		$this->name1 = $this->faker->bothify('SiteRedshopbDelivery_Address120CrudCest address ?##?');
		$this->ErpId = (int) $this->faker->numberBetween(100, 1000);
		$this->mainCompanyId = $I->getMainCompanyId('1.4.0');
		$this->address_line1 = 'test address';
		$this->zip = $this->faker->postcode;
		$this->city = $this->faker->city;
		$this->country_code = 'DK';

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&name1=$this->name1"
			. "&id=$this->ErpId"
			. "&company_id=$this->mainCompanyId"
			. "&address_line1=$this->address_line1"
			. "&zip=$this->zip"
			. "&city=$this->city"
			. "&country_code=$this->country_code"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created delivery address with name '$this->name1' is: '$this->id'");
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing delivery address");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name1' => $this->name1]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of delivery address");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name1' => $this->name1]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a new delivery address using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->updatedName1 = 'new_' . $this->name1;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
			. "&name1=$this->updatedName1"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name1' => $this->name1]);
		$I->seeResponseContainsJson(['name1' => $this->updatedName1]);

		$I->comment("The delivery address name1 has been modified to: $this->updatedName1");
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a new delivery address using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(["message" => "Item not found with given key."]);
	}
}