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
 * Class SiteRedshopbFilterFieldset110structureCest
 * @since 2.8.0
 */
class SiteRedshopbFilterFieldset110structureCest
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
	protected $name;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $erpId;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $baseUrl;

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('Prepare data needed for the test');

		$this->faker = Faker\Factory::create();
		$this->name = $this->faker->bothify('SiteRedshopbFilterFieldset110structureCest filter ?##?');
		$this->erpId = (int) $this->faker->numberBetween(100, 1000);

		$I->comment('I create a new filter fieldset');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->erpId"
			. "&name=$this->name"
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
		$I->wantTo('GET an existing filter fieldset');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' => [
					'curies' => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=filter_fieldset&webserviceVersion=1.1.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-filter_fieldset',
							'templated' => true
						]
					],
					'base' => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page'
					],
					'redshopb-filter_fieldset:list' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=filter_fieldset&webserviceVersion=1.1.0&webserviceClient=site&api=Hal"
					],
					'redshopb-filter_fieldset:self' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=filter_fieldset&webserviceVersion=1.1.0&webserviceClient=site&id=$this->id&api=Hal"
					],
				],
				'id'                => $this->id,
				'id_others'         => ["erp.$this->erpId"],
				'name'              => $this->name,
				'state'             => true,
			]
		);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('filter_fieldset', $this->id, '1.1.0');
	}
}