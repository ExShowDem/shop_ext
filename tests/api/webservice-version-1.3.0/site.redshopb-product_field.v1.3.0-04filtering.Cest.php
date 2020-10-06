<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProductField130filteringCest
 * @since 2.6.1
 */
class SiteRedshopbProductField130filteringCest
{
	/**
	 * @var
	 * @since 2.6.1
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.6.1
	 */
	protected $product_fieldA;

	/**
	 * @var
	 * @since 2.6.1
	 */
	protected $product_fieldA_id;

	/**
	 * @var
	 * @since 2.6.1
	 */
	protected $product_fieldB;

	/**
	 * @var
	 * @since 2.6.1
	 */
	protected $product_fieldB_id;

	/**
	 * Prepares the following structure
	 *
	 * +----------------+---------------+------------------+-----------------+---------------------+--------------------+-------+
	 * | product_field  |   type_code   | filter_type_code | multiple_values | searchable_frontend | searchable_backend | state |
	 * +----------------+---------------+------------------+-----------------+---------------------+--------------------+-------+
	 * | Product_fieldA | textboxstring |  textboxstring   |      true       |        true         |        true        | true  |
	 * | Product_fieldB | textboxfloat  |  textboxfloat    |      false      |        false        |        false       | false |
	 * +----------------+---------------+------------------+-----------------+---------------------+--------------------+-------+
	 */

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.1
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product_field');
		$this->faker = Faker\Factory::create();

		$I->comment('product_fieldA');
		$this->product_fieldA['name'] = $this->faker->bothify('SiteRedshopbProduct_Field130filteringCest fieldA ?##?');
		$this->product_fieldA['title'] = $this->faker->bothify('SiteRedshopbProduct_Field130filteringCest fieldA ?##?');
		$this->product_fieldA['type_code'] = 'textboxstring';
		$this->product_fieldA['filter_type_code'] = 'textboxstring';

		$I->comment('product_fieldB');
		$this->product_fieldB['name'] = $this->faker->bothify('SiteRedshopbProduct_Field130filteringCest fieldB ?##?');
		$this->product_fieldB['title'] = $this->faker->bothify('SiteRedshopbProduct_Field130filteringCest fieldB ?##?');
		$this->product_fieldB['type_code'] = 'textboxfloat';
		$this->product_fieldB['filter_type_code'] = 'textboxfloat';
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.1
	 */
	public function create(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST two product_field to filtering');
		$I->comment('I create the product_fieldA');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->product_fieldA['name']
			. "&title=" . $this->product_fieldA['title']
			. "&type_code=" . $this->product_fieldA['type_code']
			. "&filter_type_code=" . $this->product_fieldA['filter_type_code']
			. "&multiple_values=true"
			. "&searchable_frontend=true"
			. "&searchable_backend=true"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->product_fieldA_id = $ids[0];

		$I->comment('I create the product_fieldB');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->product_fieldB['name']
			. "&title=" . $this->product_fieldB['title']
			. "&type_code=" . $this->product_fieldB['type_code']
			. "&filter_type_code=" . $this->product_fieldB['filter_type_code']
			. "&multiple_values=false"
			. "&searchable_frontend=false"
			. "&searchable_backend=false"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->product_fieldB_id = $ids[0];

		$I->comment('I unpublish the product_fieldB to be filtered by state');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->product_fieldB_id"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of product_field filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[search]=" . $this->product_fieldA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product_fieldA['name']]);
		$I->seeResponseContainsJson(['title' => $this->product_fieldA['title']]);
		$I->dontseeResponseContainsJson(['name' => $this->product_fieldB['name']]);
		$I->dontseeResponseContainsJson(['title' => $this->product_fieldB['title']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function readListFilteredByTypeCode(ApiTester $I)
	{
		$I->wantTo("GET a list of product_field filtered by type_code");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[type_code]=" . $this->product_fieldA['type_code']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product_fieldA['name']]);
		$I->seeResponseContainsJson(['title' => $this->product_fieldA['title']]);
		$I->dontseeResponseContainsJson(['name' => $this->product_fieldB['name']]);
		$I->dontseeResponseContainsJson(['title' => $this->product_fieldB['title']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function readListFilteredByFilterTypeCode(ApiTester $I)
	{
		$I->wantTo("GET a list of product_field filtered by filter_type_code");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[filter_type_code]=" . $this->product_fieldA['filter_type_code']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product_fieldA['name']]);
		$I->seeResponseContainsJson(['title' => $this->product_fieldA['title']]);
		$I->dontseeResponseContainsJson(['name' => $this->product_fieldB['name']]);
		$I->dontseeResponseContainsJson(['title' => $this->product_fieldB['title']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function readListFilteredByMultipleValues(ApiTester $I)
	{
		$I->wantTo("GET a list of product_field filtered by multiple_values");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[multiple_values]=true"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product_fieldA['name']]);
		$I->seeResponseContainsJson(['title' => $this->product_fieldA['title']]);
		$I->dontseeResponseContainsJson(['name' => $this->product_fieldB['name']]);
		$I->dontseeResponseContainsJson(['title' => $this->product_fieldB['title']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function readListFilteredBySearchableFrontend(ApiTester $I)
	{
		$I->wantTo("GET a list of product_field filtered by searchable_frontend");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[searchable_frontend]=true"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product_fieldA['name']]);
		$I->seeResponseContainsJson(['title' => $this->product_fieldA['title']]);
		$I->dontseeResponseContainsJson(['name' => $this->product_fieldB['name']]);
		$I->dontseeResponseContainsJson(['title' => $this->product_fieldB['title']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function readListFilteredBySearchableBackend(ApiTester $I)
	{
		$I->wantTo("GET a list of product_field filtered by searchable_backend");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[searchable_backend]=true"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product_fieldA['name']]);
		$I->seeResponseContainsJson(['title' => $this->product_fieldA['title']]);
		$I->dontseeResponseContainsJson(['name' => $this->product_fieldB['name']]);
		$I->dontseeResponseContainsJson(['title' => $this->product_fieldB['title']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function readListFilteredByState(ApiTester $I)
	{
		$I->wantTo("GET a list of product_field filtered by state");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[state]=true"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product_fieldA['name']]);
		$I->seeResponseContainsJson(['title' => $this->product_fieldA['title']]);
		$I->dontseeResponseContainsJson(['name' => $this->product_fieldB['name']]);
		$I->dontseeResponseContainsJson(['title' => $this->product_fieldB['title']]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.1
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('product_field', $this->product_fieldA_id, '1.3.0');
		$I->webserviceCrudDelete('product_field', $this->product_fieldB_id, '1.3.0');
	}
}