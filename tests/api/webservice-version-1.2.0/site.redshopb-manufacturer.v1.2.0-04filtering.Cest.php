<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbManufacturer120FilteringCest
{
	/**
	 * @var Manufacturer to be filtered by Search filter
	 */
	public $manufacturerA = array();

	/**
	 * @var Manufacturer to be filtered by Parent_id
	 */
	public $manufacturerB = array();

	/**
	 * @var Manufacturer to be filtered by Status
	 */
	public $manufacturerC = array();

	/**
	* Prepares the following structure
	*
	* +---------------+---------------+-------------+
	* | Manufacturer  |   Parent_id   |   Status    |
	* +---------------+---------------+-------------+
	* | manufacturerA | None          | Published   |
	* | manufacturerB | manufacturerA | Published   |
	* | manufacturerC | None          | Unpublished |
	* +---------------+---------------+-------------+
	*/

	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$I->wantTo('Create one manufacturer per each filter to be tested');

		$I->comment('I create a manufacturerA to be filtered by SEARCH');
		$this->manufacturerA['name'] = (string) $this->faker->bothify('SiteRedshopbManufacturer120FilteringCest manufacturerA ?##?');
		$this->manufacturerA['id'] = (int) $I->createManufacturer($this->manufacturerA['name'], '1.2.0');

		$I->comment('I create a manufacturerB to be filtered by Parent Id. Parent is manufacturerA');
		$this->manufacturerB['name'] = (string) $this->faker->bothify('SiteRedshopbManufacturer120FilteringCest manufacturerB ?##?');
		$this->manufacturerB['parentId'] = $this->manufacturerA['id'];
		$this->manufacturerB['id'] = (int) $I->createManufacturer(
				$this->manufacturerB['name'],
				'1.2.0',
				['parent_id' => $this->manufacturerB['parentId']]
		);

		$I->comment('I create a manufacturerC to be filtered by Status as unpublished');
		$this->manufacturerC['name'] = (string) $this->faker->bothify('SiteRedshopbManufacturer120FilteringCest manufacturerC ?##?');
		$this->manufacturerC['id'] = (int) $I->createManufacturer($this->manufacturerC['name'], '1.2.0');
		$I->unpublishManufacturer($this->manufacturerC['id'], '1.2.0');
	}

	/**
	 * @param ApiTester $I
	 */
	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of manufacturer filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[search]=' . $this->manufacturerA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->manufacturerA['id']]);
		$I->seeResponseContainsJson(['name' => $this->manufacturerA['name']]);
	}

	/**
	 * @param ApiTester $I
	 */
	public function readListFilterParentId(ApiTester $I)
	{
		$I->wantTo("GET a list of manufacturer filtered by parent id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. '&filter[parent_id]=' . $this->manufacturerA['id']
			. '&filter[state]='
			. '&list[ordering]=id'
			. '&list[direction]=desc'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->manufacturerB['id']]);
		$I->seeResponseContainsJson(['name' => $this->manufacturerB['name']]);
		$I->seeResponseContainsJson(['parent_id' => $this->manufacturerA['id']]);
		$I->dontseeResponseContainsJson(['id' => $this->manufacturerA['id']]);
		$I->dontseeResponseContainsJson(['name' => $this->manufacturerA['name']]);
	}

	/**
	 * @param ApiTester $I
	 */
	public function readListFilterState(ApiTester $I)
	{
		$I->wantTo("GET a list of manufacturer filtered by state");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=manufacturer'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. '&list[ordering]=id'
			. '&filter[state]=false'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['id' => $this->manufacturerA['id']]);
		$I->dontseeResponseContainsJson(['id' => $this->manufacturerB['id']]);
		$I->seeResponseContainsJson(['id' => $this->manufacturerC['id']]);
		$I->seeResponseContainsJson(['name' => $this->manufacturerC['name']]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		// ManufacturerB is child of ManufacturerA so it needs to be removed first.
		$I->deleteManufacturer($this->manufacturerB['id'], '1.2.0');
		$I->deleteManufacturer($this->manufacturerA['id'], '1.2.0');
		$I->deleteManufacturer($this->manufacturerC['id'], '1.2.0');
	}
}
