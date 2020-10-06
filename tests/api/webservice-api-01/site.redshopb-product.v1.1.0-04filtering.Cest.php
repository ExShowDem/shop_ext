<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProduct110FilteringCest
{
	/**
	 * @var Product to be filtered by Search filter
	 */
	public $productA = array();

	/**
	 * @var Product to be filtered by Company
	 */
	public $productB = array();

	/**
	 * @var Product to be filtered by Parent_id
	 */
	public $productC = array();

	/**
	 * @var Product to be filtered by Status
	 */
	public $productD = array();

	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$I->wantTo('Create one product per each filter to be tested');

		$I->comment('I create a product to be filtered by SEARCH');
		$this->productA['name'] = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest product ?##?');
		$this->productA['sku']  = $this->faker->randomNumber(3);
		$this->productA['category_name'] = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest category ?##?');
		$this->productA['category_id']  = (int) $I->createCategory($this->productA['category_name']);
		$this->productA['id'] = (int) $I->createProduct($this->productA['name'], $this->productA['sku'], $this->productA['category_id'], '1.1.0');

		$I->comment('I create a ProductB to be filtered by Category');
		$this->productB['name'] = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest product ?##?');
		$this->productB['sku']  = $this->faker->randomNumber(3);
		$this->productB['category_name'] = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest category ?##?');
		$this->productB['category_id']  = (int) $I->createCategory($this->productB['category_name']);
		$this->productB['id'] = (int) $I->createProduct($this->productB['name'], $this->productB['sku'], $this->productB['category_id'], '1.1.0');

		$I->comment('I create a ProductC to be filtered by Sku');
		$this->productC['name'] = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest product ?##?');
		$this->productC['sku']  = $this->faker->randomNumber(3);
		$this->productC['category_name'] = (string) $this->faker->bothify('SiteRedshopbProduct110FilteringCest category ?##?');
		$this->productC['category_id']  = (int) $I->createCategory($this->productC['category_name']);
		$this->productC['id'] = (int) $I->createProduct($this->productC['name'], $this->productC['sku'], $this->productC['category_id'], '1.1.0');
	}

	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of categories filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[search]=' . $this->productA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productA['id']]);
		$I->seeResponseContainsJson(['name' => $this->productA['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productB['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productC['name']]);
	}

	public function readListFilteredByCategoryId(ApiTester $I)
	{
		$I->wantTo("GET a list of product filtered by Category id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&filter[category_id]=' . $this->productB['category_id']
			. '&list[ordering]=id'
			. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productB['id']]);
		$I->seeResponseContainsJson(['name' => $this->productB['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->productA['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->productC['name']]);
	}

	public function readListFilteredBySku(ApiTester $I)
	{
		$I->wantTo("GET a list of product filtered by parent id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&filter[sku]=' . $this->productC['sku']
			. '&list[ordering]=id'
			. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productC['id']]);
		$I->seeResponseContainsJson(['name' => $this->productC['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->productA['name']]);
		$I->dontseeResponseContainsJson(['name' => $this->productB['name']]);
	}

	/*
	 * @todo: other filters to be tested
	 *
	 * list[ordering] (string, Optional),
	 * list[direction] (string, Optional),
	 * filter[manufacturer_sku] (string, Optional),
	 * filter[related_sku] (string, Optional),
	 * filter[template_code] (string, Optional),
	 * filter[company_id] (int, Optional),
	 * filter[manufacturer_id] (int, Optional),
	 * filter[filter_fieldset_id] (int, Optional),
	 * filter[unit_measure_code] (string, Optional),
	 * filter[service] (boolean, Optional),
	 * filter[discontinued] (boolean, Optional),
	 * filter[featured] (boolean, Optional),
	 * filter[state] (boolean, Optional)
	 */

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->deleteProduct($this->productA['id'], '1.1.0');
		$I->deleteProduct($this->productB['id'], '1.1.0');
		$I->deleteProduct($this->productC['id'], '1.1.0');

		$I->deleteCategory($this->productA['category_id'], '1.1.0');
		$I->deleteCategory($this->productB['category_id'], '1.1.0');
		$I->deleteCategory($this->productC['category_id'], '1.1.0');
	}
}
