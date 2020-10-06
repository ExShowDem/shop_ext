<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbStockroom_product100StructureCest
{
	/**
	 * @var Category to be used in the test
	 */
	public $category;

	/**
	 * @var Product to be used in the test
	 */
	public $product;

	/**
	 * @var Stockroom to be used in the test
	 */
	public $stockroom;

	/**
	 * @var Product stockroom to be used in the test
	 */
	public $stockroom_product;

	/**
	 * @var The webservice version to be used in the test
	 */
	public $webserviceVersion = '1.0.0';

	public function prepare(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new stockroom for a product');
		$this->faker = Faker\Factory::create();

		$this->category['name'] = $this->faker->bothify('SiteRedshopbStockroom_product100StructureCest category ?##?');
		$this->category['id'] = $I->webserviceCrudCreate(
			'category',
			[
				'name' => $this->category['name']
			]
		);

		$this->product['name'] = $this->faker->bothify('SiteRedshopbStockroom_product100StructureCest product ?##?');
		$this->product['sku'] = $this->faker->randomNumber(3);
		$this->product['id'] = $I->webserviceCrudCreate(
			'product',
			[
				'name' => $this->product['name'],
				'sku' => $this->product['sku'],
				'category' => $this->category['id']
			]
		);

		$this->stockroom['name'] = $this->faker->bothify('SiteRedshopbStockroom_product100StructureCest stockroom ?##?');
		$this->stockroom['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroom['name']
			]
		);

		$this->stockroom_product['amount'] = $this->faker->randomNumber(2);
		$this->stockroom_product['id'] = $I->webserviceCrudCreate(
			'stockroom_product',
			[
				'stockroom_id' => $this->stockroom['id'],
				'product_id' => $this->product['id'],
				'amount' => $this->stockroom_product['amount']
			],
			$this->webserviceVersion
		);

		$I->comment('The id of the newly created product stockroom is:' . $this->stockroom_product['id']);
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing product stockroom with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=stockroom_product'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=' . $this->webserviceVersion
				. '&id=' . $this->stockroom_product['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' =>
					[
						'curies' =>
							[
								0 =>
									[
										'href' => "$baseUrl/index.php?option=com_redshopb&view=stockroom_product&webserviceVersion=$this->webserviceVersion&webserviceClient=site&format=doc&api=Hal#{rel}",
										'title' => 'Documentation',
										'name' => 'redshopb-stockroom_product',
										'templated' => true,
									],
							],
						'base' =>
							[
								'href' => "$baseUrl/?api=Hal",
								'title' => 'Default page',
							],
						'redshopb-stockroom_product:list' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=stockroom_product&webserviceVersion=$this->webserviceVersion&webserviceClient=site&api=Hal",
							],
						'redshopb-stockroom_product:self' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=stockroom_product&webserviceVersion=$this->webserviceVersion&webserviceClient=site&id=" . $this->stockroom_product['id'] . "&api=Hal",
							],
						'redshopb-stockroom' =>
							[
								'href' => "$baseUrl/index.php?option=redshopb-stockroom&webserviceVersion=1.0.0&webserviceClient=site&id=" . $this->stockroom['id'] . "&api=Hal",
								'title' => 'Aesir E-Commerce - Stockroom Webservice',
								'templated' => true,
							],
						'redshopb-product' =>
							[
								'href' => "$baseUrl/index.php?option=redshopb-product&webserviceVersion=1.0.0&webserviceClient=site&id=" . $this->product['id'] . "&api=Hal",
								'title' => 'Aesir E-Commerce - Product Webservice',
								'templated' => true,
							],
					],
				'id' => $this->stockroom_product['id'],
				'id_others' => [],
				'stockroom_id' => $this->stockroom['id'],
				'stockroom_id_others' => [],
				'product_id' => $this->product['id'],
				'product_id_others' => [],
				'amount' => $this->stockroom_product['amount'],
				'unlimited' => false,
				'stock_upper_level' => 0,
				'stock_lower_level' => 0
			]
		);
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Remove all items created by the test');
		$I->webserviceCrudDelete('product', $this->product['id']);
		$I->webserviceCrudDelete('category', $this->category['id']);
		$I->webserviceCrudDelete('stockroom', $this->stockroom['id']);
	}
}
