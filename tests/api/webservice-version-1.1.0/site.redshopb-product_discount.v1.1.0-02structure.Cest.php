<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProductDiscount110structureCest
 * @since 2.6.0
 */
class SiteRedshopbProductDiscount110structureCest
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
	protected $product_discount_id;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $product_discount_total;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $baseUrl;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product_discount');
		$this->faker = Faker\Factory::create();

		$this->product['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110structureCest product ?##?');
		$this->product['sku'] = $this->faker->numberBetween(100, 1000);

		$this->category['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110structureCest category ?##?');
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
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo('GET an existing product_discount');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_discount_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' => [
					'curies' => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=product_discount&webserviceVersion=1.1.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-product_discount',
							'templated' => true
						]
					],
					'base' => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page'
					],
					'redshopb-product_discount:list' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=product_discount&webserviceVersion=1.1.0&webserviceClient=site&api=Hal"
					],
					'redshopb-product_discount:self' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=product_discount&webserviceVersion=1.1.0&webserviceClient=site&id=$this->product_discount_id&api=Hal"
					],
				],
				'id'                                    => $this->product_discount_id,
				'quantity_max'                          => 0,
				'quantity_min'                          => 0,
				'total'                                 => $this->product_discount_total,
				'kind'                                  => 1,
				'id_others'                             => [],
				'product_id'                            => $this->product['id'],
				'product_id_others'                     => [],
				'product_discount_group_id'             => 0,
				'product_discount_group_id_others'      => NULL,
				'company_id'                            => 0,
				'company_id_others'                     => NULL,
				'customer_discount_group_id'            => 0,
				'customer_discount_group_id_others'     => NULL,
				'currency_code'                         => 'DKK',
				'percent'                               => 0,
				'starting_date'                         => "",
				'ending_date'                           => "",
				'state'                                 => true,
			]
		);
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