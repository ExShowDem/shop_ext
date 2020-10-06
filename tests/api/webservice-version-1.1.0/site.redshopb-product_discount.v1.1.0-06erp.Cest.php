<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProductDiscount110erpCest
 * @since 2.6.0
 */
class SiteRedshopbProductDiscount110erpCest
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
	protected $erpId;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $product_discount_total;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $new_product_discount_total;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product and category');
		$this->faker = Faker\Factory::create();

		$this->product['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110erpCest product ?##?');
		$this->product['sku'] = $this->faker->numberBetween(100, 1000);

		$this->category['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110erpCest category ?##?');
		$this->category['id']   = (int) $I->createCategory($this->category['name']);

		$this->product['id'] = (int) $I->createProduct($this->product['name'], $this->product['sku'], $this->category['id']);

		$I->comment('I will create the necessary');
		$this->erpId = (int) $this->faker->numberBetween(100, 1000);
		$this->product_discount_total = '10';
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function createWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product_discount with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&kind=1'
			. '&currency_code=DKK'
			. "&id=$this->erpId"
			. "&total=$this->product_discount_total"
			. "&product_id=" . $this->product['id']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readItemWithErpId(ApiTester $I)
	{
		$I->wantTo("GET an existing product_discount with erp id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['product_id' => $this->product['id']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function updateWithErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a product_discount using PUT with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->new_product_discount_total = '25';
		$I->sendPUT('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
			. "&total=$this->new_product_discount_total"
		);
		$I->seeResponseCodeis(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponsecodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['total' => $this->product_discount_total]);
		$I->seeResponseContainsJson(['total' => $this->new_product_discount_total]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskUnpublishWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('unpublish a product_discount using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
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
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskPublishWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('publish a product_discount using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&task=publish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
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
	 * @since 2.6.0
	 */
	public function deleteWithErpId(ApiTester $I)
	{
		$I->wantTo('DELETE a product_discount using DELETE with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsjson();
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteProduct($this->product['id']);
		$I->deleteCategory($this->category['id']);
	}
}