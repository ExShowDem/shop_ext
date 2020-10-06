<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbUser100StructureCest
{
	public function prepare(ApiTester $I)
	{
		$I->wantTo('POST a new user');
		$faker = Faker\Factory::create();
		$this->name  = $faker->bothify('SiteRedshopbUser100StructureCest user ?##?');
		$this->erpid = $faker->numberBetween(10, 9999);
		$this->username = $faker->userName;
		$this->password = $faker->userName;
		$this->role_id = 2; // Administrator
		$this->company = 2; // Main Company
		$this->email = $faker->email;
		$faker->addProvider(new Faker\Provider\en_US\Address($faker));
		$this->address_line1 = "$faker->streetName $faker->buildingNumber";
		$this->zip = $faker->postcode;
		$this->city = $faker->city;
		$this->country_code = 'DK';
		$this->phone = $faker->phoneNumber;

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
			'index.php'
			. '?option=redshopb&view=user'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0',
			[
				'id'			=> $this->erpid,
				'name'			=> $this->name,
				'username'		=> $this->username,
				'password'		=> $this->password,
				'role_id'		=> $this->role_id,
				'company_id'	=> $this->company,
				'address_line1'	=> $this->address_line1,
				'city'			=> $this->city,
				'zip'			=> $this->zip,
				'email'			=> $this->email,
				'country_code'	=> $this->country_code,
				'phone'			=> $this->phone,
			]
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created user with name '$this->name' is: $this->id");
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing user with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
			'index.php'
			. '?option=redshopb&view=user'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links'            => [
					'curies'                   => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=user&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-user',
							'templated' => true
						]
					],
					'base'                     => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page',
					],
					'redshopb-user:list'   => [
						'href' => "$baseUrl/index.php?option=com_redshopb&view=user&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
					],
					'redshopb-user:self'   => [
						'href' => "$baseUrl/index.php?option=com_redshopb&view=user&webserviceVersion=1.0.0&webserviceClient=site&id=$this->id&api=Hal",
					],
					'redshopb-company' =>
						array (
							'href' => "$baseUrl/index.php?option=redshopb&view=company&webserviceVersion=1.1.0&webserviceClient=site&id=$this->company&api=Hal",
							'title' => 'Aesir E-Commerce - Company Webservice',
							'templated' => true,
						),
					'redshopb-department' =>
						array (
							'href' => "$baseUrl/index.php?option=redshopb&view=department&webserviceVersion=1.0.0&webserviceClient=site&id=0&api=Hal",
							'title' => 'Aesir E-Commerce - Department Webservice',
							'templated' => true,
						),
					'redshopb-role' =>
						array (
							'href' => "$baseUrl/index.php?option=redshopb&view=role&webserviceVersion=1.0.0&webserviceClient=site&id=$this->role_id&api=Hal",
							'title' => 'Aesir E-Commerce - Role Webservice',
							'templated' => true,
						),
					'redshopb-country' =>
						array (
							'href' => "$baseUrl/index.php?option=redshopb&view=country&webserviceVersion=1.0.0&webserviceClient=site&code=DK&api=Hal",
							'title' => 'Aesir E-Commerce - Country Webservice',
							'templated' => true,
						),

				],
				'id' => $this->id,
				'id_others' => ["erp.$this->erpid"],
				'username' => $this->username,
				'role_id' => 2,
				'name' => $this->name,
				'name2' => '',
				'printed_name' => '',
				'company_id' => 2,
				'company_id_others' => ['erp.main'],
				'department_id' => 0,
				'department_id_others' => NULL,
				'address_name1' => '',
				'address_name2' => '',
				'address_line1' => $this->address_line1,
				'address_line2' => '',
				'zip' => $this->zip,
				'city' => $this->city,
				'country_code' => 'DK',
				'phone' => $this->phone,
				'email' => $this->email,
				'no_email' => false,
				'send_email' => true,
				'blocked' => false,
				'default_delivery_address_id' =>
					array (
						0 => NULL,
					),
				'default_delivery_address_other_ids' =>
					array (
					),
				'delivery_addresses' =>
					array (
					),
				'delivery_addresses_other_ids' =>
					array (
					),
			]
		);
	}

	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=user'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}
