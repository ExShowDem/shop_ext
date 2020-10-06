<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbStockroom120TasksCest
{
	/**
	 * @var Stockroom to be published
	 */
	public $stockroomA;

	/**
	 * @var Stockroom to be unpublished
	 */
	public $stockroomB;

	/**
	 * @var Version of webservice to use
	 */
	public $webserviceVersion = '1.2.0';

	public function __construct()
	{
		$this->faker = Faker\Factory::create();
	}

	public function taskPublish(Step\Api\redshopb2b $I)
	{
		$I->wantTo('PUBLISH a stockroom using GET');
		$I->comment('Create a stockroom unpublished');

		$this->stockroomA['name'] = $this->faker->bothify('SiteRedshopbStockroom120TasksCest stockroom ?##?');
		$this->stockroomA['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomA['name']
			],
			$this->webserviceVersion
		);

		$I->webserviceTaskUnpublish('stockroom', $this->stockroomA['id'], $this->webserviceVersion);

		$I->amHttpAuthenticated('admin', 'admin');

		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroomA['id']
			. '&task=publish'
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroomA['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['id' => $this->stockroomA['id']]);
		$I->seeResponseContainsJson(['name' => $this->stockroomA['name']]);
		$I->seeResponseContainsJson(['state' => true]);

	}

	public function taskUnpublish(Step\Api\redshopb2b $I)
	{
		$I->wantTo('UNPUBLISH a stockroom using GET');
		$I->comment('Create a stockroom published');

		$this->stockroomB['name'] = $this->faker->bothify('SiteRedshopbStockroom120TasksCest stockroom ?##?');
		$this->stockroomB['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroomB['name']
			],
			$this->webserviceVersion
		);

		$I->amHttpAuthenticated('admin', 'admin');

		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroomB['id']
			. '&task=unpublish'
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=stockroom'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=' . $this->webserviceVersion
			. '&id=' . $this->stockroomB['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['id' => $this->stockroomB['id']]);
		$I->seeResponseContainsJson(['name' => $this->stockroomB['name']]);
		$I->seeResponseContainsJson(['state' => false]);
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->webserviceCrudDelete('stockroom', $this->stockroomA['id']);
		$I->webserviceCrudDelete('stockroom', $this->stockroomB['id']);
	}
}
