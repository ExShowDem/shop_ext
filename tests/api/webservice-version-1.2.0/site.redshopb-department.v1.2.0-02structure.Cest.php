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
 * Class SiteRedshopbDepartment120structureCest
 * @since 2.8.0
 */
class SiteRedshopbDepartment120structureCest
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
	protected $erpId;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $name;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $company_id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $department_number;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $baseUrl;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $alias;

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('POST a new department');
		$this->faker = Faker\Factory::create();

		$this->erpId = (int) $this->faker->numberBetween(100, 1000);
		$this->company_id = $I->getMainCompanyId();
		$this->name = $this->faker->bothify('SiteRedshopbDepartment120structureCest ?##?');

		$this->department_number = (int) $this->faker->numberBetween(100, 1000);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->erpId"
			. "&name=$this->name"
			. "&company_id=$this->company_id"
			. "&department_number=$this->department_number"
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
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo('GET an existing department');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();

		$baseUrl = $I->getWebserviceBaseUrl();
		$this->alias = $I->getAlias($this->name);

		$I->seeResponseContainsJson(
			[
				'_links' => [
					'curies' => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=department&webserviceVersion=1.2.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-department',
							'templated' => true
						]
					],
					'base' => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page'
					],
					'redshopb-department:list' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=department&webserviceVersion=1.2.0&webserviceClient=site&api=Hal"
					],
					'redshopb-department:self' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=department&webserviceVersion=1.2.0&webserviceClient=site&id=$this->id&api=Hal"
					]
				],
				'id'                        => $this->id,
				'department_number'         => "$this->department_number",
				'id_others'                 => ['erp.' . $this->erpId],
				'name'                      => $this->name,
				'name2'                     => '',
				'alias'                     => $this->alias,
				'image'                     => null,
				'company_id'                => $this->company_id,
				'company_id_others'         => ['erp.main'],
				'parent_id'                 => 0,
				'parent_id_others'          => null,
				'address_name1'             => null,
				'address_name2'             => '',
				'address_line1'             => null,
				'address_line2'             => null,
				'zip'                       => '',
				'city'                      => '',
				'country_code'              => '',
				'state'                     => true,
			]
		);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('clear up all created items by the test');
		$I->webserviceCrudDelete('department', $this->id, '1.2.0');
	}
}