<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCompany100FilteringCest
{
	/**
	 * @var Company to be filtered by Search filter
	 */
	public $companyA = array();

	/**
	 * @var Company to be filtered by Parent_id
	 */
	public $companyB = array();

	/**
	 * @var Company to be filtered by Zip
	 */
	public $companyC = array();

	/**
	 * @var Company to be filtered by City
	 */
	public $companyD = array();

	/**
	 * @var Company to be filtered by country_code
	 */
	public $companyF = array();

	/**
	 * @var Company to be filtered by currency_code
	 */
	public $companyG = array();

	/**
	 * @var Company to be filtered by Status
	 */
	public $companyH = array();

	/**
	 * Prepares the following structure
	 *
	 * +----------+----------+-------+--------------+---------+----------+-----+-------------+
	 * | Company  |  Parent  |  Zip  |     City     | Country | Currency | B2C |   Status    |
	 * +----------+----------+-------+--------------+---------+----------+-----+-------------+
	 * | CompanyA | main     |  5220 | Odense       | DK      | EUR      |   0 | Published   |
	 * | CompanyB | CompanyA |  5220 | Odense       | DK      | EUR      |   0 | Published   |
	 * | CompanyC | main     | 08018 | Odense       | DK      | EUR      |   0 | Published   |
	 * | CompanyD | main     |  5220 | Buenos Aires | DK      | EUR      |   0 | Published   |
	 * | CompanyE | main     |  5220 | Odense       | AR      | EUR      |   0 | Published   |
	 * | CompanyF | main     |  5220 | Odense       | DK      | ARS      |   0 | Published   |
	 * | CompanyG | main     |  5220 | Odense       | DK      | EUR      |   1 | Published   |
	 * | CompanyH | main     |  5220 | Odense       | DK      | EUR      |   0 | UNpublished |
	 * +----------+----------+-------+--------------+---------+----------+-----+-------------+
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$I->wantTo('Create one company per each filter to be tested');

		$I->comment('I create a company to be filtered by SEARCH');
		$this->companyA['name'] = (string) $this->faker->bothify('SiteRedshopbCompany100FilteringCest company ?##?');
		$this->companyA['id'] = (int) $I->createCompany($this->companyA['name'], '1.0.0');

		$I->comment('I create a CompanyB to be filtered by Parent_id (Company A is parent of Company B');
		$this->companyB['name']  = (string) $this->faker->bothify('SiteRedshopbCompany100FilteringCest company ?##?');
		$this->companyB['id'] = (int) $I->createCompany(
			$this->companyB['name'],
			'1.0.0',
			['parent_id' => $this->companyA['id']]
		);

		$I->comment('I create a CompanyC to be filtered by ZIP: 09899');
		$this->companyC['name']  = (string) $this->faker->bothify('SiteRedshopbCompany100FilteringCest company ?##?');
		$this->companyC['id'] = (int) $I->createCompany(
			$this->companyC['name'],
			'1.0.0',
			['zip' => '08018']
		);

		$I->comment('I create a CompanyD to be filtered by City: Buenos Aires');
		$this->companyD['name']  = (string) $this->faker->bothify('SiteRedshopbCompany100FilteringCest company ?##?');
		$this->companyD['id'] = (int) $I->createCompany(
			$this->companyD['name'],
			'1.0.0',
			['city' => 'Buenos Aires']
		);

		$I->comment('I create a CompanyE to be filtered by country_code: AR');
		$this->companyE['name']  = (string) $this->faker->bothify('SiteRedshopbCompany100FilteringCest company ?##?');
		$this->companyE['id'] = (int) $I->createCompany(
			$this->companyE['name'],
			'1.0.0',
			['country_code' => 'AR']
		);

		$I->comment('I create a CompanyF to be filtered by currency_code ARS');
		$this->companyF['name']  = (string) $this->faker->bothify('SiteRedshopbCompany100FilteringCest company ?##?');
		$this->companyF['currency_code'] = 'ARS';
		$this->companyF['id'] = (int) $I->createCompany(
			$this->companyF['name'],
			'1.0.0',
			['currency_code' => 'ARS']
		);

		$I->comment('I create a CompanyG to be filtered by B2C');
		$this->companyG['name']  = (string) $this->faker->bothify('SiteRedshopbCompany100FilteringCest company ?##?');
		$this->companyG['id'] = (int) $I->createCompany(
			$this->companyG['name'],
			'1.0.0',
			['b2c' => true]
		);

		$I->comment('I create a CompanyH to be filtered by Status as unpublished');
		$this->companyH['name']  = (string) $this->faker->bothify('SiteRedshopbCompany100FilteringCest company ?##?');
		$this->companyH['id'] = (int) $I->createCompany($this->companyH['name'], '1.0.0');
		$I->unpublishCompany($this->companyH['id'], '1.0.0');
	}


	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of categories filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[search]=' . $this->companyA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->companyA['id']]);
		$I->seeResponseContainsJson(['name' => $this->companyA['name']]);
		$I->dontseeResponseContainsJson(['id' => $this->companyB['id']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyB['name']]);
	}

	public function readListFilterByParent_id(ApiTester $I)
	{
		$I->wantTo("GET a list of company filtered by Parent_id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&filter[parent_id]=' . $this->companyA['id']
			. '&list[ordering]=id'
			. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['id' => $this->companyC['id']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyC['name']]);
		$I->seeResponseContainsJson(['id' => $this->companyB['id']]);
		$I->seeResponseContainsJson(['name' => $this->companyB['name']]);
	}

	public function readListFilterByZip(ApiTester $I)
	{
		$I->wantTo("GET a list of company filtered by parent id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&filter[zip]=08018'
			. '&filter[state]='
			. '&list[ordering]=id'
			. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->companyC['id']]);
		$I->seeResponseContainsJson(['name' => $this->companyC['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyA['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyB['name']]);
	}

	public function readListFilterByCity(ApiTester $I)
	{
		$I->wantTo("GET a list of company filtered by parent id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=company'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.0.0'
					. '&filter[city]=Buenos Aires'
					. '&filter[state]='
					. '&list[ordering]=id'
					. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->companyD['id']]);
		$I->seeResponseContainsJson(['name' => $this->companyD['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyA['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyB['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyC['name']]);
	}

	public function readListFilterByCountry_code(ApiTester $I)
	{
		$I->wantTo("GET a list of company filtered by parent id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=company'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.0.0'
					. '&filter[country_code]=AR'
					. '&filter[state]='
					. '&list[ordering]=id'
					. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->companyE['id']]);
		$I->seeResponseContainsJson(['name' => $this->companyE['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyA['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyB['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyC['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyD['name']]);
	}

	public function readListFilterByCurrency_code(ApiTester $I)
	{
		$I->wantTo("GET a list of company filtered by parent id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=company'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.0.0'
					. '&filter[currency_code]=ARS'
					. '&filter[state]='
					. '&list[ordering]=id'
					. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->companyF['id']]);
		$I->seeResponseContainsJson(['name' => $this->companyF['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyA['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyB['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyC['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyD['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyE['name']]);
	}

	public function readListFilterByB2c(ApiTester $I)
	{
		$I->wantTo("GET a list of company filtered by parent id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=company'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.0.0'
					. '&filter[b2c]=1'
					. '&filter[state]='
					. '&list[ordering]=id'
					. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->companyG['id']]);
		$I->seeResponseContainsJson(['name' => $this->companyG['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyA['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyB['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyC['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyD['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyE['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyF['name']]);
	}

	public function readListFilterState(ApiTester $I)
	{
		$I->wantTo("GET a list of company filtered by state");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&list[ordering]=id'
			. '&filter[state]=false'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->companyH['id']]);
		$I->seeResponseContainsJson(['name' => $this->companyH['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyA['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyB['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyC['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyD['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyE['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyF['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->companyG['name']]);
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		// CompanyB is child of CompanyA so it needs to be removed first.
		$I->deleteCompany($this->companyB['id'], '1.0.0');
		$I->deleteCompany($this->companyA['id'], '1.0.0');
		$I->deleteCompany($this->companyC['id'], '1.0.0');
		$I->deleteCompany($this->companyD['id'], '1.0.0');
		$I->deleteCompany($this->companyE['id'], '1.0.0');
		$I->deleteCompany($this->companyF['id'], '1.0.0');
		$I->deleteCompany($this->companyG['id'], '1.0.0');
		$I->deleteCompany($this->companyH['id'], '1.0.0');
	}
}
