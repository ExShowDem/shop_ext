<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbStockroom120FilteringCest
{
	/**
	 * @var Stockroom to be filtered by Search filter
	 */
	public $stockroomA;

	/**
	 * @var Stockroom to be filtered by Search filter
	 */
	public $stockroomB;

	/**
	 * @var Stockroom to be filtered by Zip code
	 */
	public $stockroomC;

	/**
	 * @var Stockroom to be filtered by City
	 */
	public $stockroomD;

	/**
	 * @var Stockroom to be filtered by State
	 */
	public $stockroomE;

	/**
	 * @var Stockroom to be filtered by Country
	 */
	public $stockroomF;

	/**
	 * @var Version of webservice to use
	 */
	public $webserviceVersion = '1.2.0';


	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$I->comment('I create a Stockroom to be filtered by SEARCH');
		$this->stockroomA['name'] = $this->faker->bothify('SiteRedshopbStockroom120FilteringCest stockroom ?##?');
		$this->stockroomA['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomA['name']
			],
			$this->webserviceVersion
		);

		$I->comment('I create a Stockroom to be filtered by Zip Code');
		$this->stockroomB['name'] = (string) $this->faker->bothify('SiteRedshopbStockroom120FilteringCest stockroom ?##?');
		$this->stockroomB['zip'] = $this->faker->postcode;
		$this->stockroomB['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomB['name'],
				'zip' => $this->stockroomB['zip']
			],
			$this->webserviceVersion
		);

		$I->comment('I create a Stockroom to be filtered by City');
		$this->stockroomC['name'] = $this->faker->bothify('SiteRedshopbStockroom120FilteringCest stockroom ?##?');
		$this->stockroomC['city'] = $this->faker->city;
		$this->stockroomC['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomC['name'],
				'city' => $this->stockroomC['city']
			],
			$this->webserviceVersion
		);

		$I->comment('I create a Stockroom to be filtered by State');
		$this->stockroomD['name'] = $this->faker->bothify('SiteRedshopbStockroom120FilteringCest stockroom ?##?');
		$this->stockroomD['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomD['name']
			],
			$this->webserviceVersion
		);
		$I->webserviceTaskUnpublish('stockroom', $this->stockroomD['id'], $this->webserviceVersion);

		$I->comment('I create a Stockroom to be filtered by Country');
		$this->stockroomE['name'] = $this->faker->bothify('SiteRedshopbStockroom120FilteringCest stockroom ?##?');
		$this->stockroomE['country'] = (string) 'DK';
		$this->stockroomE['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomE['name'],
				'country_code' => $this->stockroomE['country']
			],
			$this->webserviceVersion
		);

		$I->comment('I create a Stockroom to be filtered by Company');
		$this->stockroomF['name'] = $this->faker->bothify('SiteRedshopbStockroom120FilteringCest stockroom ?##?');
		$this->stockroomF['company_name'] = $this->faker->bothify('SiteRedshopbStockroom120FilteringCest company ?##?');
		$this->stockroomF['company_id'] = (int) $I->createCompany($this->stockroomF['company_name']);

		$this->stockroomF['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomF['name'],
				'company_id' => $this->stockroomF['company_id']
			],
			$this->webserviceVersion
		);
	}

	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo('GET a list of stockrooms filtered by SEARCH');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[search]=' . $this->stockroomA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->stockroomA['id']]);
		$I->seeResponseContainsJson(['name' => $this->stockroomA['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomB['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomC['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomD['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomE['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomF['name']]);
	}

	public function readListFilteredByZip(ApiTester $I)
	{
		$I->wantTo('GET a list of stockrooms filtered by zip');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[zip]=' . $this->stockroomB['zip']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomA['name']]);
		$I->seeResponseContainsJson(['id' => $this->stockroomB['id']]);
		$I->seeResponseContainsJson(['name' => $this->stockroomB['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomC['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomD['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomE['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomF['name']]);
	}

	public function readListFilteredByCity(ApiTester $I)
	{
		$I->wantTo('GET a list of stockrooms filtered by city');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[city]=' . $this->stockroomC['city']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomA['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomB['name']]);
		$I->seeResponseContainsJson(['id' => $this->stockroomC['id']]);
		$I->seeResponseContainsJson(['name' => $this->stockroomC['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomD['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomE['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomF['name']]);
	}

	public function readListFilteredByState(ApiTester $I)
	{
		$I->wantTo('GET a list of stockrooms filtered by state');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[state]=0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomA['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomB['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomC['name']]);
		$I->seeResponseContainsJson(['id' => $this->stockroomD['id']]);
		$I->seeResponseContainsJson(['name' => $this->stockroomD['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomE['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomF['name']]);
	}

	public function readListFilteredByCountry(ApiTester $I)
	{
		$I->wantTo('GET a list of stockrooms filtered by city');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[country_code]=' . $this->stockroomE['country']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomA['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomB['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomC['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomD['name']]);
		$I->seeResponseContainsJson(['id' => $this->stockroomE['id']]);
		$I->seeResponseContainsJson(['name' => $this->stockroomE['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomF['name']]);
	}

	public function readListFilteredByCompany(ApiTester $I)
	{
		$I->wantTo('GET a list of stockrooms filtered by company');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[company_id]=' . $this->stockroomF['company_id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomA['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomB['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomC['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomD['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->stockroomE['name']]);
		$I->seeResponseContainsJson(['id' => $this->stockroomF['id']]);
		$I->seeResponseContainsJson(['name' => $this->stockroomF['name']]);
	}


	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->comment('I want to delete stockrooms created for the test');
		$I->webserviceCrudDelete('stockroom', $this->stockroomA['id'], $this->webserviceVersion);
		$I->webserviceCrudDelete('stockroom', $this->stockroomB['id'], $this->webserviceVersion);
		$I->webserviceCrudDelete('stockroom', $this->stockroomC['id'], $this->webserviceVersion);
		$I->webserviceCrudDelete('stockroom', $this->stockroomD['id'], $this->webserviceVersion);
		$I->webserviceCrudDelete('stockroom', $this->stockroomE['id'], $this->webserviceVersion);
		$I->webserviceCrudDelete('stockroom', $this->stockroomF['id'], $this->webserviceVersion);
		$I->webserviceCrudDelete('company', $this->stockroomF['company_id']);
	}
}
