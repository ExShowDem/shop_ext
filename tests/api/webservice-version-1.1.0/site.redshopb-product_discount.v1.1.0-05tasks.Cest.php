<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProductDiscount110tasksCest
 * @since 2.6.0
 */
class SiteRedshopbProductDiscount110tasksCest
{
	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $product;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $category;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $product_discount_total;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $product_discount_id;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product, category and product_discount');
		$this->faker = Faker\Factory::create();

		$this->product['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110tasksCest product ?##?');
		$this->product['sku'] = $this->faker->numberBetween(100, 1000);

		$this->category['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110tasksCest category ?##?');
		$this->category['id']   = (int) $I->createCategory($this->category['name']);

		$this->product['id'] = (int) $I->createProduct($this->product['name'], $this->product['sku'], $this->category['id']);

		$I->amHttpAuthenticated('admin', 'admin');
		$this->product_discount_total = '10';
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&kind=1'
			. '&currency_code=DKK'
			. "&total=$this->product_discount_total"
			. "&product_id=" . $this->product['id']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->product_discount_id = $ids[0];
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskUnpublish(Step\Api\redshopb2b $I)
	{
		$I->wantTo('unpublish a product_discount using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_discount_id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_discount_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => false]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskPublish(Step\Api\redshopb2b $I)
	{
		$I->wantTo('publish a product_discount using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&task=publish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_discount_id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_discount_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => true]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('product_discount', $this->product_discount_id, '1.1.0');
		$I->deleteProduct($this->product['id']);
		$I->deleteCategory($this->category['id']);
	}
}