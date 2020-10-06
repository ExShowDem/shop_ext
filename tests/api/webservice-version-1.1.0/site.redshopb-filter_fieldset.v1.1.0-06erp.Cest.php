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
 * Class SiteRedshopbFilterFieldset110erpCest
 * @since 2.8.0
 */
class SiteRedshopbFilterFieldset110erpCest
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
	protected $filterName;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $filterErpId;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $filterNewName;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $productField;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $productFieldId;

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('Prepare data needed for the tests');
		$this->faker = Faker\Factory::create();

		$this->filterName = $this->faker->bothify('SiteRedshopbFilterFieldset110erpCest filter ?##?');
		$this->filterErpId = (int) $this->faker->numberBetween(100, 1000);
		$this->filterNewName = 'new_' . $this->filterName;

		$I->comment('I create a new field');
		$this->productField['erpId'] = (int) $this->faker->numberBetween(100, 1000);
		$this->productField['name'] = $this->faker->bothify('SiteRedshopbFilterFieldset110erpCest field ?##?');
		$this->productField['title'] = $this->faker->bothify('SiteRedshopbFilterFieldset110erpCest field ?##?');
		$this->productField['type_code'] = 'textboxstring';
		$this->productField['filter_type_code'] = 'textboxstring';

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&id=' . $this->productField['erpId']
			. "&name=" . $this->productField['name']
			. "&title=" . $this->productField['title']
			. "&type_code=" . $this->productField['type_code']
			. "&filter_type_code=" . $this->productField['filter_type_code']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productFieldId = $ids[0];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function createWithErpId(ApiTester $I)
	{
		$I->wantTo('POST a new filter fieldset with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->filterErpId"
			. "&name=$this->filterName"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readWithErpId(ApiTester $I)
	{
		$I->wantTo('GET an existing filter fieldset with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['name' => $this->filterName]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function updateWithErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a filter fieldset using PUT with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPUT('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
			. "&name=$this->filterNewName"
		);
		$I->seeResponseCodeis(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
		);

		$I->seeResponsecodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->filterName]);
		$I->seeResponseContainsJson(['name' => $this->filterNewName]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function taskUnpublishWithErpId(redshopb2b $I)
	{
		$I->wantTo('unpublish a filter using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => false]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function taskPublishWithErpId(redshopb2b $I)
	{
		$I->wantTo('publish a filter fieldset using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&task=publish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => true]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function taskFieldAddWithErpId(redshopb2b $I)
	{
		$I->wantTo('add a field to filter fieldset using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&task=fieldAdd'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
			. "&field_id=erp." . $this->productField['erpId']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['fields_other_ids' => ["erp." . $this->productField['erpId']]]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function taskFieldRemoveWithErpId(redshopb2b $I)
	{
		$I->wantTo('remove a field to filter fieldset using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&task=fieldRemove'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
			. "&field_id=erp." . $this->productField['erpId']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['fields_other_ids' => ["erp." . $this->productField['erpId']]]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function deleteWithErpId(ApiTester $I)
	{
		$I->wantTo('DELETE a filter fieldset using DELETE with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=filter_fieldset'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->filterErpId"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsjson();
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('product_field', $this->productFieldId, '1.1.0');
	}
}