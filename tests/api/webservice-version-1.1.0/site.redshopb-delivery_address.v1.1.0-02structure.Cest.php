<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License Version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbDelivery_address110structureCest
 * @since 2.5.0
 */
class SiteRedshopbDelivery_address110structureCest
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
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function prepare(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new delivery address');
		$this->faker = Faker\Factory::create();
		$this->name1 = $this->faker->bothify('SiteRedshopbDelivery_Address110StructureCest address ?##?');
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
			. '&webserviceVersion=1.1.0'
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
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing address with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' => [
					'curies' => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=delivery_address&webserviceVersion=1.1.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-delivery_address',
							'templated' => true
						]
					],
					'base' => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page'
					],
					'redshopb-delivery_address:list' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=delivery_address&webserviceVersion=1.1.0&webserviceClient=site&api=Hal"
					],
					'redshopb-delivery_address:self' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=delivery_address&webserviceVersion=1.1.0&webserviceClient=site&id=$this->id&api=Hal"
					],
					'redshopb-country' => [
						'href'  => "$baseUrl/index.php?option=redshopb&view=country&webserviceVersion=1.0.0&webserviceClient=site&code=$this->country_code&api=Hal",
						'title' => 'Aesir E-Commerce - Country Webservice'
					],
					'redshopb-company' => [
						'href'  => "$baseUrl/index.php?option=redshopb&view=company&webserviceVersion=1.1.0&webserviceClient=site&id=$this->mainCompanyId&api=Hal"
					]
				],
				'id'                => $this->id,
				'id_others'         => ["erp.$this->ErpId"],
				'name1'             => $this->name1,
				'name2'             => '',
				'address_line1'     => $this->address_line1,
				'address_line2'     => '',
				'zip'               => $this->zip,
				'city'              => $this->city,
				'country_code'      => $this->country_code,
				'company_id'        => $this->mainCompanyId,
				'department_id'     => null,
				'user_id'           => null,
				'delivery_default'  => false,
				'type'              => 1,
			]
		);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}