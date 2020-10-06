<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCategory160FilteringCest
{
	/**
	 * @var Category to be filtered by Search filter
	 */
	public $categoryA = array();

	/**
	 * @var Category to be filtered by Company
	 */
	public $categoryB = array();

	/**
	 * @var Category to be filtered by Parent_id
	 */
	public $categoryC = array();

	/**
	 * @var Category to be filtered by Status
	 */
	public $categoryD = array();

	/**
	 * Prepares the following structure
	 *
	 * +-----------+---------------------------+-----------+-------------+
	 * | Category  |          Company          | Parent_id |   Status    |
	 * +-----------+---------------------------+-----------+-------------+
	 * | CategoryA | Default (Main Wharehouse) | None      | Published   |
	 * | CategoryB | 2 (Main Company)          | CategoryA | Published   |
	 * | CategoryC | Default (Main Wharehouse) | None      | Published   |
	 * | CategoryD | Default (Main Wharehouse) | None      | Unpublished |
	 * +-----------+---------------------------+-----------+-------------+
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$I->wantTo('Create one category per each filter to be tested');

		$I->comment('I create a category to be filtered by SEARCH');
		$this->categoryA['name'] = (string) $this->faker->bothify('SiteRedshopbCategory160FilteringCest category ?##?');
		$this->categoryA['id'] = (int) $I->createCategory($this->categoryA['name'], '1.6.0');

		$I->comment('I create a CategoryB to be filtered by Company');
		$this->categoryB['name']  = (string) $this->faker->bothify('SiteRedshopbCategory160FilteringCest category ?##?');
		$this->categoryB['company'] = 2;
		$this->categoryB['id'] = (int) $I->createCategory(
				$this->categoryB['name'],
				'1.6.0',
				['company_id' => $this->categoryB['company']]
		);

		$I->comment('I create a CategoryC to be filtered by Parent Id. Parent is CategoryA');
		$this->categoryC['name']  = (string) $this->faker->bothify('SiteRedshopbCategory160FilteringCest category ?##?');
		$this->categoryC['parentId'] = $this->categoryA['id'];
		$this->categoryC['id'] = (int) $I->createCategory(
				$this->categoryC['name'],
				'1.6.0',
				['parent_id' => $this->categoryC['parentId']]
		);

		$I->comment('I create a CategoryD to be filtered by Status as unpublished');
		$this->categoryD['name']  = (string) $this->faker->bothify('SiteRedshopbCategory160FilteringCest category ?##?');
		$this->categoryD['id'] = (int) $I->createCategory($this->categoryD['name'], '1.0.0');
		$I->unpublishCategory($this->categoryD['id'], '1.6.0');
	}

	/**
	 * @param ApiTester $I
	 */
	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of categories filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=category'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[search]=' . $this->categoryA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->categoryA['id']]);
		$I->seeResponseContainsJson(['name' => $this->categoryA['name']]);
	}

	/**
	 * @param ApiTester $I
	 */
	public function readListFilterByCompanyId(ApiTester $I)
	{
		$I->wantTo("GET a list of category filtered by company id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=category'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. '&filter[company_id]=' . $this->categoryB['company']
			. '&filter[parent_id]='
			. '&filter[state]='
			. '&list[ordering]=id'
			. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['id' => $this->categoryA['id']]);
		$I->dontseeResponseContainsJson(['name' => $this->categoryA['name']]);
		$I->seeResponseContainsJson(['id' => $this->categoryB['id']]);
		$I->seeResponseContainsJson(['name' => $this->categoryB['name']]);
	}

	/**
	 * @param ApiTester $I
	 */
	public function readListFilterParentId(ApiTester $I)
	{
		$I->wantTo("GET a list of category filtered by parent id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=category'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. '&filter[company_id]='
			. '&filter[parent_id]=' . $this->categoryA['id']
			. '&filter[state]='
			. '&list[ordering]=id'
			. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->categoryC['id']]);
		$I->seeResponseContainsJson(['name' => $this->categoryC['name']]);
		$I->seeResponseContainsJson(['parent_id' => $this->categoryA['id']]);
		$I->dontseeResponseContainsJson(['id' => $this->categoryA['id']]);
		$I->dontseeResponseContainsJson(['name' => $this->categoryA['name']]);
	}

	/**
	 * @param ApiTester $I
	 */
	public function readListFilterState(ApiTester $I)
	{
		$I->wantTo("GET a list of category filtered by state");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=category'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.6.0'
			. '&list[ordering]=id'
			. '&filter[state]=false'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['id' => $this->categoryA['id']]);
		$I->dontseeResponseContainsJson(['id' => $this->categoryB['id']]);
		$I->dontseeResponseContainsJson(['id' => $this->categoryC['id']]);
		$I->seeResponseContainsJson(['id' => $this->categoryD['id']]);
		$I->seeResponseContainsJson(['name' => $this->categoryD['name']]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		// CategoryC is child of CategoryA so it needs to be removed first.
		$I->deleteCategory($this->categoryC['id'], '1.6.0');
		$I->deleteCategory($this->categoryA['id'], '1.6.0');
		$I->deleteCategory($this->categoryB['id'], '1.6.0');
		$I->deleteCategory($this->categoryD['id'], '1.6.0');
	}
}
