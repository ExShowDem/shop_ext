<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbStockroom110StructureCest
{
	/**
	 * @var Stockroom to be used in the test
	 */
	public $stockroom;

	/**
	 * @var The webservice version to be used
	 */
	public $webserviceVersion = '1.1.0';

	public function prepare(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new stockroom');
		$this->faker = Faker\Factory::create();
		$this->stockroom['name']  = $this->faker->bothify('SiteRedshopbStockroom120StructureCest stockroom ?##?');
		$this->stockroom['company_id'] = $I->getMainCompanyId();

		$this->stockroom['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroom['name'],
				'company_id' => $this->stockroom['company_id']
			],
			$this->webserviceVersion
		);

		$I->comment('The id of the newly created stockroom with name ' . $this->stockroom['name'] . ' is: ' . $this->stockroom['id']);
	}
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing stockroom with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=stockroom'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=' . $this->webserviceVersion
				. '&id=' . $this->stockroom['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' =>
					[
						'curies' =>
							[
								0 =>
									[
										'href' => "$baseUrl/index.php?option=com_redshopb&view=stockroom&webserviceVersion=$this->webserviceVersion&webserviceClient=site&format=doc&api=Hal#{rel}",
										'title' => 'Documentation',
										'name' => 'redshopb-stockroom',
										'templated' => true,
									],
							],
						'base' =>
							[
								'href' => "$baseUrl/?api=Hal",
								'title' => 'Default page',
							],
						'redshopb-stockroom:list' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=stockroom&webserviceVersion=$this->webserviceVersion&webserviceClient=site&api=Hal",
							],
						'redshopb-stockroom:self' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=stockroom&webserviceVersion=$this->webserviceVersion&webserviceClient=site&id=" . $this->stockroom['id'] . "&api=Hal",
							],
						'redshopb-company' =>
							[
								'href' => "$baseUrl/index.php?option=redshopb-company&webserviceVersion=1.1.0&webserviceClient=site&id=" . $this->stockroom['company_id'] . "&api=Hal",
								'title' => 'Aesir E-Commerce - Company Webservice',
								'templated' => true,
							],
						'redshopb-country' =>
							[
								'href' => "$baseUrl/index.php?option=redshopb-country&webserviceVersion=1.0.0&webserviceClient=site&code=&api=Hal",
								'title' => 'Aesir E-Commerce - Country Webservice',
								'templated' => true,
							],
					],
				'id' => $this->stockroom['id'],
				'id_others' => [],
				'name' => $this->stockroom['name'],
				'alias' => $I->getAlias($this->stockroom['name']),
				'company_id' => $this->stockroom['company_id'],
				'company_id_others' =>
					[
						0 => 'erp.main',
					],
				'address_name1' => null,
				'address_name2' => '',
				'address_line1' => null,
				'address_line2' => null,
				'zip' => '',
				'city' => '',
				'country_code' => '',
				'min_delivery_time' => 0,
				'max_delivery_time' => 0,
				'stock_upper_level' => 0,
				'stock_lower_level' => 0,
				'ordering' => 1,
				'state' => true,
				'color' => ''
			]
		);
	}

	public function cleanUp(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('stockroom', $this->stockroom['id'], $this->webserviceVersion);
	}
}