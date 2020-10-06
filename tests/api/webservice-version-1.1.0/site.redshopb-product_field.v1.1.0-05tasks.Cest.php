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
 * Class SiteRedshopbProductField110tasksCest
 * @since 2.8.0
 */
class SiteRedshopbProductField110tasksCest
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
	protected $product_field;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $product_field_id;

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('POST a new product field');
		$this->faker = Faker\Factory::create();

		$I->comment('product_fieldA');
		$this->product_field['name'] = $this->faker->bothify('SiteRedshopbProductField110tasksCest field ?##?');
		$this->product_field['title'] = $this->faker->bothify('SiteRedshopbProductField110tasksCest field ?##?');
		$this->product_field['type_code'] = 'textboxstring';
		$this->product_field['filter_type_code'] = 'textboxstring';

		$I->comment('I create the product_field');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
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
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function taskUnpublish(redshopb2b $I)
	{
		$I->wantTo('unpublish a product field using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => false]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function taskPublish(redshopb2b $I)
	{
		$I->wantTo('publish a product field using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&task=publish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => true]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function translate(redshopb2b $I)
	{
		$I->wantTo('Create a translation for the product field using POST');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->product_field['translated_name'] = 'French-' . $this->product_field['name'];

		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
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
			. '&webserviceVersion=1.1.0'
			. '&language=fr-FR'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->dontSeeResponseContainsJson(['name' => $this->product_field['name']]);
		$I->seeResponseContainsJson(['name' => $this->product_field['translated_name']]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function translateRemove(redshopb2b $I)
	{
		$I->wantTo('Delete a translation for the product field using POST');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translateRemove'
			. '&language=fr-FR'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&language=fr-FR'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);

		$I->dontSeeResponseContainsJson(['name' => $this->product_field['translated_name']]);
		$I->seeResponseContainsJson(['name' => $this->product_field['name']]);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('product_field', $this->product_field_id, '1.1.0');

	}
}