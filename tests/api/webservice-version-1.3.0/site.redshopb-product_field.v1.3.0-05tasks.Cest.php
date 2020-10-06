<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProductField130tasksCest
 * @since 2.6.1
 */
class SiteRedshopbProductField130tasksCest
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
	protected $product_field;

	/**
	 * @var
	 * @since 2.6.1
	 */
	protected $product_field_id;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.1
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product_field');
		$this->faker = Faker\Factory::create();

		$I->comment('product_fieldA');
		$this->product_field['name'] = $this->faker->bothify('SiteRedshopbProduct_Field130tasksCest field ?##?');
		$this->product_field['title'] = $this->faker->bothify('SiteRedshopbProduct_Field130tasksCest field ?##?');
		$this->product_field['type_code'] = 'textboxstring';
		$this->product_field['filter_type_code'] = 'textboxstring';

		$I->comment('I create the product_field');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->product_field['name']
			. "&title=" . $this->product_field['title']
			. "&type_code=" . $this->product_field['type_code']
			. "&filter_type_code=" . $this->product_field['filter_type_code']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->product_field_id = $ids[0];
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.1
	 */
	public function taskUnpublish(Step\Api\redshopb2b $I)
	{
		$I->wantTo('unpublish a product_field using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&task=unpublish'
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

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => false]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.1
	 */
	public function taskPublish(Step\Api\redshopb2b $I)
	{
		$I->wantTo('publish a product_field using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&task=publish'
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

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => true]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.1
	 */
	public function translate(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Create a translation for the product_field using POST');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->product_field['translated_name'] = 'French-' . $this->product_field['name'];

		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&task=translate'
			. '&language=fr-FR'
			. "&id=$this->product_field_id"
			. "&name=" . $this->product_field['translated_name']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&language=fr-FR'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->dontSeeResponseContainsJson(['name' => $this->product_field['name']]);
		$I->seeResponseContainsJson(['name' => $this->product_field['translated_name']]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.1
	 */
	public function translateRemove(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Delete a translation for the product_field using POST');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&task=translateRemove'
			. '&language=fr-FR'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&language=fr-FR'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);

		$I->dontSeeResponseContainsJson(['name' => $this->product_field['translated_name']]);
		$I->seeResponseContainsJson(['name' => $this->product_field['name']]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.1
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('product_field', $this->product_field_id, '1.3.0');

	}
}