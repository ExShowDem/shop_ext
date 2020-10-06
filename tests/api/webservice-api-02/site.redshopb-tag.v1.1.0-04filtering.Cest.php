<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbTag110FilteringCest
{
	/**
	 * Prepares the following structure
	 *
	 * +------+-------------+----------------+--------+-------------+
	 * | Tag  |    Type     |    Company     | Parent |   Status    |
	 * +------+-------------+----------------+--------+-------------+
	 * | TagA |             | Main Warehouse | none   | published   |
	 * | TagB | electronics | Main Company   | none   | published   |
	 * | TagC |             | Main Warehouse | TagA   | published   |
	 * | TagD |             | Main Warehouse | none   | published   |
	 * | TagE |             | Main Warehou   | none   | UNpublished |
	 * +------+-------------+----------------+--------+-------------+
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$I->wantTo('Create one tag per each filter to be tested');

		$I->comment('I create a tagA to be filtered by SEARCH');
		$this->tagA['name'] = (string) $this->faker->bothify('SiteRedshopbTag110FilteringCest tagA ?##?');
		$this->tagA['id'] = (int) $I->createTag($this->tagA['name'], '1.1.0');

		$I->comment('I create a TagB to be filtered by Type');
		$this->tagB['name']  = (string) $this->faker->bothify('SiteRedshopbTag110FilteringCest tagB ?##?');
		$this->tagB['id'] = (int) $I->createTag(
			$this->tagB['name'],
			'1.1.0',
			[
				'type' => 'electronics'
			]
		);

		$I->comment('I create a TagC to be filtered by Company ID');
		$this->tagC['name']  = (string) $this->faker->bothify('SiteRedshopbTag110FilteringCest tagC ?##?');
		$this->tagC['id'] = (int) $I->createTag(
			$this->tagC['name'],
			'1.1.0',
			[
				'company_id' => 2
			]
		);

		$I->comment('I create a TagD to be filtered by Parent ID');
		$this->tagD['name']  = (string) $this->faker->bothify('SiteRedshopbTag110FilteringCest tagD ?##?');
		$this->tagD['id'] = (int) $I->createTag(
			$this->tagD['name'],
			'1.1.0',
			[
				'parent_id' => $this->tagA['id']
			]
		);

		$I->comment('I create a TagE to be filtered by Status');
		$this->tagE['name']  = (string) $this->faker->bothify('SiteRedshopbTag110FilteringCest tagE ?##?');
		$this->tagE['id'] = (int) $I->createTag($this->tagE['name'], '1.1.0');
		$I->unpublishTag($this->tagE['id'], '1.1.0');
	}

	public function readListFilterSearch(ApiTester $I)
	{
		$I->wantTo("GET a list of tag");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[search]=' . $this->tagA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->tagA['id']]);
		$I->seeResponseContainsJson(['name' => $this->tagA['name']]);
		$I->dontSeeResponseContainsJson(['id' => $this->tagB['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagB['name']]);
		$I->dontSeeResponseContainsJson(['id' => $this->tagC['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagC['name']]);
	}



	public function readListFilterType(ApiTester $I)
	{
		$I->wantTo("GET a list of tag filtered by type");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=tag'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.1.0'
					. '&filter[type]=electronics'
					. '&list[ordering]=id'
					. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['id' => $this->tagA['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagA['name']]);
		$I->seeResponseContainsJson(['id' => $this->tagB['id']]);
		$I->seeResponseContainsJson(['name' => $this->tagB['name']]);
		$I->dontSeeResponseContainsJson(['id' => $this->tagC['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagC['name']]);
	}

	public function readListFilterCompanyId(ApiTester $I)
	{
		$I->wantTo("GET a list of tag filtered by company id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
					. '?option=redshopb&view=tag'
					. '&api=Hal'
					. '&webserviceClient=site'
					. '&webserviceVersion=1.1.0'
					. '&filter[type]='
					. '&filter[company_id]=2'
					. '&filter[parent_id]='
					. '&filter[state]='
					. '&list[ordering]=id'
					. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['id' => $this->tagA['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagA['name']]);
		$I->dontseeResponseContainsJson(['id' => $this->tagB['id']]);
		$I->dontseeResponseContainsJson(['name' => $this->tagB['name']]);
		$I->seeResponseContainsJson(['id' => $this->tagC['id']]);
		$I->seeResponseContainsJson(['name' => $this->tagC['name']]);
		$I->dontSeeResponseContainsJson(['id' => $this->tagD['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagD['name']]);
	}

	public function readListFilterParentId(ApiTester $I)
	{
		$I->wantTo("GET a list of tag filtered by parent id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&filter[type]='
			. '&filter[company_id]='
			. '&filter[parent_id]=' . $this->tagA['id']
			. '&filter[state]='
			. '&list[ordering]=id'
			. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->dontSeeResponseContainsJson(['id' => $this->tagA['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagA['name']]);
		$I->dontseeResponseContainsJson(['id' => $this->tagB['id']]);
		$I->dontseeResponseContainsJson(['name' => $this->tagB['name']]);
		$I->dontSeeResponseContainsJson(['id' => $this->tagC['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagC['name']]);
		$I->seeResponseContainsJson(['id' => $this->tagD['id']]);
		$I->seeResponseContainsJson(['name' => $this->tagD['name']]);
	}



	public function readListFilterState(ApiTester $I)
	{
		$I->wantTo("GET a list of tag filtered by state");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=tag'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&filter[type]='
			. '&filter[company_id]='
			. '&filter[parent_id]='
			. '&filter[state]=false'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->dontSeeResponseContainsJson(['id' => $this->tagA['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagA['name']]);
		$I->dontseeResponseContainsJson(['id' => $this->tagB['id']]);
		$I->dontseeResponseContainsJson(['name' => $this->tagB['name']]);
		$I->dontSeeResponseContainsJson(['id' => $this->tagC['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagC['name']]);
		$I->dontSeeResponseContainsJson(['id' => $this->tagD['id']]);
		$I->dontSeeResponseContainsJson(['name' => $this->tagD['name']]);
		$I->seeResponseContainsJson(['id' => $this->tagE['id']]);
		$I->seeResponseContainsJson(['name' => $this->tagE['name']]);
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->deleteTag($this->tagA['id'], '1.1.0');
		$I->deleteTag($this->tagB['id'], '1.1.0');
		$I->deleteTag($this->tagC['id'], '1.1.0');
		// $I->deleteTag($this->tagD['id'], '1.1.0');  Already removed when removing the parent TagA
		$I->deleteTag($this->tagE['id'], '1.1.0');
	}
}
