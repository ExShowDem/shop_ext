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
 * Class SiteRedshopbDepartment120filteringCest
 * @since 2.8.0
 */
class SiteRedshopbDepartment120filteringCest
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
	protected $departmentA;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $departmentA_id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $departmentB;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $departmentB_id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $departmentC;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $departmentC_id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $companyA;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $companyB;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $mainCompany_id;

	/**
	 * Prepares the following structure
	 *
	 * +-------------+------------+------------+--------------+
	 * | Department  | Company_id |  parent_id  |   Status    |
	 * +-------------+------------+------------+--------------+
	 * | DepartmentA |  CompanyA  |     null    | Published   |
	 * | DepartmentB |  CompanyB  |     null    | Unpublished |
	 * | DepartmentC |  CompanyB  | DepartmentA | Published   |
	 * +-------------+------------+------------+--------------+
	 */

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('I prepare for POST a new department');
		$this->faker = Faker\Factory::create();

		$I->comment('I prepare three departments');
		$this->departmentA['erpId'] = (int) $this->faker->numberBetween(100, 1000);
		$this->departmentA['name'] = $this->faker->bothify('SiteRedshopbDepartment120filteringCest departmentA ?##?');
		$this->departmentA['number'] = (int) $this->faker->numberBetween(100, 1000);
		$this->mainCompany_id = $I->getMainCompanyId();

		$this->departmentB['erpId'] = (int) $this->faker->numberBetween(100, 1000);
		$this->departmentB['name'] = $this->faker->bothify('SiteRedshopbDepartment120filteringCest departmentB ?##?');
		$this->departmentB['number'] = (int) $this->faker->numberBetween(100, 1000);

		$this->departmentC['erpId'] = (int) $this->faker->numberBetween(100, 1000);
		$this->departmentC['name'] = $this->faker->bothify('SiteRedshopbDepartment120filteringCest departmentC ?##?');
		$this->departmentC['number'] = (int) $this->faker->numberBetween(100, 1000);

		$I->comment('I create two companies');
		$this->companyA['name'] = $this->faker->bothify('SiteRedshopbDepartment120filteringCest companyA ?##?');
		$this->companyA['id'] = (int) $I->createCompany($this->companyA['name']);

		$this->companyB['name'] = $this->faker->bothify('SiteRedshopbDepartment120filteringCest companyB ?##?');
		$this->companyB['id'] = (int) $I->createCompany($this->companyB['name']);
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function create(redshopb2b $I)
	{
		$I->wantTo('POST a new department');
		$I->comment('I create the departmentA');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=" . $this->departmentA['erpId']
			. "&name=" . $this->departmentA['name']
			. "&company_id=" . $this->companyA['id']
			. "&department_number=" . $this->departmentA['number']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->departmentA_id = $ids[0];

		$I->comment('I create the departmentB');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=" . $this->departmentB['erpId']
			. "&name=" . $this->departmentB['name']
			. "&company_id=" . $this->companyB['id']
			. "&department_number=" . $this->departmentB['number']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->departmentB_id = $ids[0];

		$I->comment('I unpublish the departmentB to be filtered by state');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->departmentB_id"
		);

		$I->seeResponseCodeIs(200);

		$I->comment('I create the departmentC to be filtered by parent id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=" . $this->departmentC['erpId']
			. "&name=" . $this->departmentC['name']
			. "&company_id=" . $this->companyB['id']
			. "&parent_id=$this->departmentA_id"
			. "&department_number=" . $this->departmentC['number']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->departmentC_id = $ids[0];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of departments filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[search]=" . $this->departmentA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->departmentA['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->departmentB['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readListFilteredByCompanyId(ApiTester $I)
	{
		$I->wantTo('GET a list of departments filtered by company id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[company_id]=" . $this->companyA['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['company_id' => $this->companyA['id']]);
		$I->seeResponseContainsJson(['id' => $this->departmentA_id]);
		$I->dontSeeResponseContainsJson(['company_id' => $this->companyB['id']]);
		$I->dontSeeResponseContainsJson(['id' => $this->departmentB_id]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readListFilteredByParentId(ApiTester $I)
	{
		$I->wantTo('GET a list of departments filtered by parent id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[parent_id]=$this->departmentA_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->departmentC_id]);
		$I->seeResponseContainsJson(['name' => $this->departmentC['name']]);
		$I->dontSeeResponseContainsJson(['id' => $this->departmentB_id]);
		$I->dontSeeResponseContainsJson(['name' => $this->departmentB['name']]);
	}

	// The filtering by state not working in department webservice 1.2.0

//    public function readListFilteredByState(ApiTester $I)
//    {
//        $I->wantTo('GET a list of departments filtered by state');
//        $I->amHttpAuthenticated('admin', 'admin');
//        $I->sendGET('index.php'
//            . '?option=redshopb&view=department'
//            . '&api=Hal'
//            . '&webserviceClient=site'
//            . '&webserviceVersion=1.2.0'
//            . '&list[ordering]=id'
//            . '&list[direction]=desc'
//            . "&filter[state]=false"
//        );
//
//        $I->seeResponseCodeIs(200);
//        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson(['id' => $this->departmentB_id]);
//        $I->seeResponseContainsJson(['name' => $this->departmentB['name']]);
//        $I->dontSeeResponseContainsJson(['id' => $this->departmentA_id]);
//        $I->dontSeeResponseContainsJson(['name' => $this->departmentA['name']]);
//    }

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('department', $this->departmentC_id, '1.2.0');
		$I->webserviceCrudDelete('department', $this->departmentA_id, '1.2.0');
		$I->webserviceCrudDelete('department', $this->departmentB_id, '1.2.0');
		$I->deleteCompany($this->companyA['id']);
		$I->deleteCompany($this->companyB['id']);
	}
}