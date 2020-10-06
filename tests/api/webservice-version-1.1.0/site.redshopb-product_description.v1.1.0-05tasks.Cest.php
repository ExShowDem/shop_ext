<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProductDescription110tasksCest
{
	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $name;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $description;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $product;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $id;

	/**
	 * @param \Step\Api\redshopb2b $I
	 *
	 * @throws \Exception
	 * @since 2.5.0
	 */
	 public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product_description');
		$this->faker = Faker\Factory::create();
		$this->name  = $this->faker->bothify('SiteRedshopbProductDescription110tasksCest product_description ?##?');
		$this->description = $this->faker->bothify('SiteRedshopbProductDescription110crudCest description ?##?');

		$this->product['name'] = (string) $this->faker->bothify('SiteRedshopbProductDescription110tasksCest product ?##?');
		$this->product['sku'] = (int) $this->faker->numberBetween(1, 9999);

		$this->product['category_name'] = (string) $this->faker->bothify('SiteRedshopbProductDescription110tasksCest category ?##?');
		$this->product['category_id'] = (int)
		$I->createCategory($this->product['category_name']);
		$this->product['id'] = (int)
		$I->createProduct($this->product['name'], $this->product['sku'], $this->product['category_id']);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&name=$this->name"
			. "&product_id=" . $this->product['id']
			. "&description=$this->description"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product_description:self']['href']);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];

		$I->comment("The id of the new created product_description with name '$this->name' is: '$this->id'");
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.5.0
	 */
	public function translate(Step\Api\redshopb2b $I)
	{
		$I->amHttpAuthenticated('admin', 'admin');
		$this->new_translation = 'French ' . $this->description;

		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translate'
			. '&language=fr-FR'
			. "&id=$this->id"
			. "&description=$this->new_translation"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translate'
			. '&language=fr-FR'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.5.0
	 */
	public function translateRemove(Step\Api\redshopb2b $I)
	{
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translateRemove'
			. '&language=fr-FR'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translate'
			. '&language=fr-FR'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.5.0
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteProduct($this->product['id']);
		$I->deleteCategory($this->product['category_id']);
	}
}