<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProductFieldGroup100StructureCest
{
	public function prepare(ApiTester $I)
	{
		$I->wantTo('POST a new product field group');
		$this->faker = Faker\Factory::create();
		$this->name  = $this->faker->bothify('SiteRedshopbProductFieldGroup100AvailabilityCest product-field-group ?##?');

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST(
				'index.php'
				. '?option=redshopb&view=product_field_group'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=1.0.0'
				. "&name=$this->name"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created product_field_group with name '$this->name' is: $this->id");
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{

		$I->wantTo("GET an existing product_field_group with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=product_field_group'
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
									'href'      => "$baseUrl/index.php?option=com_redshopb&view=product_field_group&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
									'title'     => 'Documentation',
									'name'      => 'redshopb-product_field_group',
									'templated' => true
							]
					],
					'base'                     => [
							'href'  => "$baseUrl/?api=Hal",
							'title' => 'Default page',
					],
					'redshopb-product_field_group:list'   => [
							'href' => "$baseUrl/index.php?option=com_redshopb&view=product_field_group&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
					],
					'redshopb-product_field_group:self'   => [
							'href' => "$baseUrl/index.php?option=com_redshopb&view=product_field_group&webserviceVersion=1.0.0&webserviceClient=site&id=$this->id&api=Hal",
					],
					'redshopb-type'                       => [
						"href" => "?api=Hal",
                        "title" => "Aesir E-Commerce - Type Webservice",
			            "templated" => true
					],
					'redshopb-filter-type'                 =>  [
						"href" => "?api=Hal",
                        "title" => "Aesir E-Commerce - Filter Type Webservice",
                        "templated" => true
					]
				],
				'id'                => $this->id,
				'name'              => $this->name,
				'alias'             => $I->getAlias('product-'.$this->name),
				'ordering'          => '1'
			]
		);
	}

	public function cleanUp(ApiTester $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product_field_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);
		$I->seeResponseCodeIs(200);
	}
}
