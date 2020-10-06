<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProductField130crudCest
 * @since 2.6.1
 */
class SiteRedshopbProductField130crudCest
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
	protected $type_code;

	/**
	 * @var
	 * @since 2.6.1
	 */
	protected $product_field;

	/**
	 * @var
	 * @since 2.6.1
	 */
	protected $product_field_id;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.1
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('prepare for POST a new product_field');
		$this->faker = Faker\Factory::create();

		$this->product_field['name'] = $this->faker->bothify('SiteRedshopbProduct_Field130crudCest ?##?');
		$this->product_field['title'] = $this->faker->bothify('SiteRedshopbProduct_Field130crudCest ?##?');
		$this->type_code = 'textboxstring';
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.1
	 */
	public function create(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product_field');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->product_field['name']
			. "&title=" . $this->product_field['title']
			. "&type_code=$this->type_code"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->product_field_id = $ids[0];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function readItem(ApiTester $I)
	{
		$I->wantTo('GET an existing product_field');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['name' => $this->product_field['name']]);
		$I->seeResponseContainsJson(['title' => $this->product_field['title']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function readList(ApiTester $I)
	{
		$I->wantTo('GET a list of product_field');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id' => $this->product_field_id]);
		$I->seeResponseContainsJson(['name' => $this->product_field['name']]);
		$I->seeResponseContainsJson(['title' => $this->product_field['title']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a product_field using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->product_field['new_name'] = "new_" . $this->product_field['name'];
		$I->sendPUT('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->product_field_id"
			. "&name=" . $this->product_field['new_name']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->product_field_id"
		);

		$I->seeResponsecodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['name' => $this->product_field['name']]);
		$I->seeResponseContainsJson(['name' => $this->product_field['new_name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.1
	 */
	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a product_field using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsjson();
	}
}