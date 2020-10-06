<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License Version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbDelivery_address110filteringCest
 * @since 2.5.0
 */
class SiteRedshopbDelivery_address110filteringCest
{
	/**
	 * @var Address to be filtered by Search filter
	 * @since 2.5.0
	 */
	protected $addressA_ErpId, $addressA_name1, $addressA_line1, $addressA_zip, $addressA_city, $addressA_country_code, $addressA_id;

	/**
	 * @var Address to be filtered by zip
	 * @since 2.5.0
	 */
	protected $addressB_ErpId, $addressB_name1, $addressB_line1, $addressB_zip, $addressB_city, $addressB_country_code, $addressB_id;

	/**
	 * @var Address to be filtered by city
	 * @since 2.5.0
	 */
	protected $addressC_ErpId, $addressC_name1, $addressC_line1, $addressC_zip, $addressC_city, $addressC_country_code, $addressC_id;

	/**
	 * @var Address to be filtered by country_code
	 * @since 2.5.0
	 */
	protected $addressD_ErpId, $addressD_name1, $addressD_line1, $addressD_zip, $addressD_city, $addressD_country_code, $addressD_id;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $mainCompanyId;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $faker;

	/**
	 * Prepares the following structure
	 *
	 * +--------------+--------------------+-------+--------------+---------+------------------+
	 * |   Company    |  Delivery Address  |  Zip  |     City     | Country |      Name1       |
	 * +--------------+--------------------+-------+--------------+---------+------------------+
	 * | Main Company |   test address A   |  5220 | Odense       | DK      |  addressA_name1  |
	 * | Main Company |   test address B   | 08018 | Odense       | DK      |  addressB_name1  |
	 * | Main Company |   test address C   |  5220 | Buenos Aires | DK      |  addressC_name1  |
	 * | Main Company |   test address D   |  5220 | Odense       | AR      |  addressD_name1  |
	 * +--------------+--------------------+-------+--------------+---------+------------------+
	 */

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function prepare(\Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$I->wantTo('Create a new delivery address per each filter to be tested');

		// Delivery Address A

		$I->comment('I create a delivery address to be filtered by Search');
		$this->addressA_name1 = $this->faker->bothify('SiteRedshopbDelivery_Address110FilteringCest addressA ?##?');
		$this->addressA_ErpId = (int) $this->faker->numberBetween(100, 1000);
		$this->mainCompanyId = $I->getMainCompanyId('1.4.0');
		$this->addressA_line1 = 'test address A';
		$this->addressA_zip = '5220';
		$this->addressA_city = 'Odense';
		$this->addressA_country_code = 'DK';

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&name1=$this->addressA_name1"
			. "&id=$this->addressA_ErpId"
			. "&company_id=$this->mainCompanyId"
			. "&address_line1=$this->addressA_line1"
			. "&zip=$this->addressA_zip"
			. "&city=$this->addressA_city"
			. "&country_code=$this->addressA_country_code"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->addressA_id = $ids[0];

		// Delivery Address B

		$I->comment('I create a delivery address to be filtered by Zip');
		$this->addressB_name1 = $this->faker->bothify('SiteRedshopbDelivery_Address110FilteringCest addressB ?##?');
		$this->addressB_ErpId = (int) $this->faker->numberBetween(100, 1000);
		$this->addressB_line1 = 'test address B';
		$this->addressB_zip = '8018';
		$this->addressB_city = 'Odense';
		$this->addressB_country_code = 'DK';

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&name1=$this->addressB_name1"
			. "&id=$this->addressB_ErpId"
			. "&company_id=$this->mainCompanyId"
			. "&address_line1=$this->addressB_line1"
			. "&zip=$this->addressB_zip"
			. "&city=$this->addressB_city"
			. "&country_code=$this->addressB_country_code"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->addressB_id = $ids[0];

		// Delivery Address C

		$I->comment('I create a delivery address to be filtered by City');
		$this->addressC_name1 = $this->faker->bothify('SiteRedshopbDelivery_Address110FilteringCest addressC ?##?');
		$this->addressC_ErpId = (int) $this->faker->numberBetween(100, 1000);
		$this->addressC_line1 = 'test address B';
		$this->addressC_zip = '5220';
		$this->addressC_city = 'Buenos Aires';
		$this->addressC_country_code = 'DK';

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&name1=$this->addressC_name1"
			. "&id=$this->addressC_ErpId"
			. "&company_id=$this->mainCompanyId"
			. "&address_line1=$this->addressC_line1"
			. "&zip=$this->addressC_zip"
			. "&city=$this->addressC_city"
			. "&country_code=$this->addressC_country_code"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->addressC_id = $ids[0];

		// Delivery Address D

		$I->comment('I create a delivery address to be filtered by Country_Cody');
		$this->addressD_name1 = $this->faker->bothify('SiteRedshopbDelivery_Address110FilteringCest addressD ?##?');
		$this->addressD_ErpId = (int) $this->faker->numberBetween(100, 1000);
		$this->addressD_line1 = 'test address B';
		$this->addressD_zip = '5220';
		$this->addressD_city = 'Odense';
		$this->addressD_country_code = 'AR';

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&name1=$this->addressD_name1"
			. "&id=$this->addressD_ErpId"
			. "&company_id=$this->mainCompanyId"
			. "&address_line1=$this->addressD_line1"
			. "&zip=$this->addressD_zip"
			. "&city=$this->addressD_city"
			. "&country_code=$this->addressD_country_code"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->addressD_id = $ids[0];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of address filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[search]=$this->addressA_name1"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->addressA_id]);
		$I->seeResponseContainsJson(['name1' => $this->addressA_name1]);
		$I->dontseeResponseContainsJson(['id' => $this->addressB_id]);
		$I->dontseeResponseContainsJson(['name1' => $this->addressB_name1]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function readListFilterByZip(ApiTester $I)
	{
		$I->wantTo("GET a list of address filtered by zip");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[zip]=$this->addressB_zip"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->addressB_id]);
		$I->seeResponseContainsJson(['name1' => $this->addressB_name1]);
		$I->seeResponseContainsJson(['zip' => $this->addressB_zip]);
		$I->dontseeResponseContainsJson(['name1' => $this->addressA_name1]);
		$I->dontseeResponseContainsJson(['name1' => $this->addressC_name1]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function readListFilterByCity(ApiTester $I)
	{
		$I->wantTo("GET a list of address filtered by city");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[city]=$this->addressC_city"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id' => $this->addressC_id]);
		$I->seeResponseContainsjson(['name1' => $this->addressC_name1]);
		$I->seeResponseContainsJson(['city' => $this->addressC_city]);
		$I->dontseeResponseContainsJson(['name1' => $this->addressA_name1]);
		$I->dontseeResponseContainsJson(['name1' => $this->addressB_name1]);
		$I->dontseeResponseContainsJson(['name1' => $this->addressD_name1]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function readListFilterByCountry_code(ApiTester $I)
	{
		$I->wantTo("GET a list of address filtered by country_code");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[country_code]=$this->addressD_country_code"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id' => $this->addressD_id]);
		$I->seeResponseContainsjson(['name1' => $this->addressD_name1]);
		$I->seeResponseContainsJson(['country_code' => $this->addressD_country_code]);
		$I->dontseeResponseContainsJson(['name1' => $this->addressA_name1]);
		$I->dontseeResponseContainsJson(['name1' => $this->addressB_name1]);
		$I->dontseeResponseContainsJson(['name1' => $this->addressC_name1]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.5.0
	 */
	public function webserviceCrudDelete(\Step\Api\redshopb2b $I)
	{
		$I->webserviceCrudDelete('delivery_address', "$this->addressA_id", '1.1.0');
		$I->webserviceCrudDelete('delivery_address', "$this->addressB_id", '1.1.0');
		$I->webserviceCrudDelete('delivery_address', "$this->addressC_id", '1.1.0');
		$I->webserviceCrudDelete('delivery_address', "$this->addressD_id", '1.1.0');
	}
}