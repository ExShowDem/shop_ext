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
 * Class SiteRedshopbProductField110erpCest
 * @since 2.8.0
 */
class SiteRedshopbProductField110erpCest
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
	protected $erpId;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $product_field;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $type_code;

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('prepare for POST a new product field with erp id');
		$this->faker = Faker\Factory::create();

		$this->erpId = (int) $this->faker->numberBetween(100, 1000);
		$this->product_field['name'] = $this->faker->bothify('SiteRedshopbProductField110erpCest field ?##?');
		$this->product_field['title'] = $this->faker->bothify('SiteRedshopbProductField110erpCest field ?##?');
		$this->type_code = 'textboxstring';
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function createWithErpId(redshopb2b $I)
	{
		$I->wantTo('POST a new product field with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->erpId"
			. "&name=" . $this->product_field['name']
			. "&title=" . $this->product_field['title']
			. "&type_code=$this->type_code"
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
		$I->wantTo('GET an existing product field with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['name' => $this->product_field['name']]);
		$I->seeResponseContainsJson(['title' => $this->product_field['title']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function updateWithErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a product field using PUT with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->product_field['new_name'] = "new_" . $this->product_field['name'];
		$I->sendPUT('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
			. "&name=" . $this->product_field['new_name']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponsecodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->product_field['name']]);
		$I->seeResponseContainsJson(['name' => $this->product_field['new_name']]);

		$this->product_field['name'] = $this->product_field['new_name'];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function taskUnpublishWithErpId(ApiTester $I)
	{
		$I->wantTo('unpublish a product field using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => false]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function taskPublishWithErpId(ApiTester $I)
	{
		$I->wantTo('publish a product field using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&task=publish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => true]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function translateWithErpId(ApiTester $I)
	{
		$I->wantTo('Create a translation for the product field using POST with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->product_field['translated_name'] = 'French-' . $this->product_field['name'];

		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translate'
			. '&language=fr-FR'
			. "&id=erp.$this->erpId"
			. "&name=" . $this->product_field['translated_name']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&language=fr-FR'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->dontSeeResponseContainsJson(['name' => $this->product_field['name']]);
		$I->seeResponseContainsJson(['name' => $this->product_field['translated_name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function translateRemoveWithErpId(ApiTester $I)
	{
		$I->wantTo('Delete a translation for the product field using POST with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translateRemove'
			. '&language=fr-FR'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&language=fr-FR'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->dontSeeResponseContainsJson(['name' => $this->product_field['translated_name']]);
		$I->seeResponseContainsJson(['name' => $this->product_field['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function deleteWithErpId(ApiTester $I)
	{
		$I->wantTo('DELETE a product field using DELETE with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsjson();
	}
}