<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbStockroom_product_item100StructureCest
{
	/**
	 * @var Product to be used in the test
	 */
	public $product;

	/**
	 * @var Product to be used in the test
	 */
	public $product_attribute;

	/**
	 * @var Product to be used in the test
	 */
	public $product_attribute_value;

	/**
	 * @var Product to be used in the test
	 */
	public $product_item;

	/**
	 * @var Stockroom to be used in the test
	 */
	public $stockroom;

	/**
	 * @var Product stockroom to be used in the test
	 */
	public $stockroom_product_item;

	/**
	 * @var The webservice version to be used in the test
	 */
	public $webserviceVersion = '1.0.0';

	public function prepare(\Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new stockroom for a product item');
		$this->faker = Faker\Factory::create();

		$this->category['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100StructureCest category ?##?');
		$this->category['id'] = $I->webserviceCrudCreate(
			'category',
			[
				'name' => $this->category['name']
			]
		);

		$this->product['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100StructureCest product ?##?');
		$this->product['sku'] = $this->faker->randomNumber(3);
		$this->product['id'] = $I->webserviceCrudCreate(
			'product',
			[
				'name' => $this->product['name'],
				'sku' => $this->product['sku'],
				'category' => $this->category['id']
			]
		);

		$this->product_attribute['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100StructureCest product_attribute ?##?');
		$this->product_attribute['id'] = $I->webserviceCrudCreate(
			'product_attribute',
			[
				'name' => $this->product_attribute['name'],
				'product_id' => $this->product['id'],
				'type_id' => '1'
			]
		);

		$this->product_attribute_value['sku'] = $this->faker->randomNumber(3);
		$this->product_attribute_value['value'] = $this->faker->bothify('?##?');
		$this->product_attribute_value['id'] = $I->webserviceCrudCreate(
			'product_attribute_value',
			[
				'product_attribute_id' => $this->product_attribute['id'],
				'sku' => $this->product_attribute_value['sku'],
				'value' => $this->product_attribute_value['value']
			]
		);

		$this->product_item['id'] = $I->webserviceCrudCreate(
			'product_item',
			[
				'product_id' => $this->product['id'],
				'product_attribute_value_ids' => $this->product_attribute_value['id']
			]
		);

		$this->stockroom['name'] = $this->faker->bothify('SiteRedshopbStockroom_product_item100StructureCest stockroom ?##?');
		$this->stockroom['id'] = $I->webserviceCrudCreate(
			'stockroom',
			[
				'name' => $this->stockroom['name']
			]
		);

		$this->stockroom_product_item['amount'] = $this->faker->randomNumber(2);
		$this->stockroom_product_item['id'] = $I->webserviceCrudCreate(
			'stockroom_product_item',
			[
				'stockroom_id' => $this->stockroom['id'],
				'product_item_id' => $this->product_item['id'],
				'amount' => $this->stockroom_product_item['amount']
			],
			$this->webserviceVersion
		);

		$I->comment('The id of the newly created product stockroom is:' . $this->stockroom_product_item['id']);
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing product stockroom with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
				'index.php'
				. '?option=redshopb&view=stockroom_product_item'
				. '&api=Hal'
				. '&webserviceClient=site'
				. '&webserviceVersion=' . $this->webserviceVersion
				. '&id=' . $this->stockroom_product_item['id']
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
										'href' => "$baseUrl/index.php?option=com_redshopb&view=stockroom_product_item&webserviceVersion=$this->webserviceVersion&webserviceClient=site&format=doc&api=Hal#{rel}",
										'title' => 'Documentation',
										'name' => 'redshopb-stockroom_product_item',
										'templated' => true,
									],
							],
						'base' =>
							[
								'href' => "$baseUrl/?api=Hal",
								'title' => 'Default page',
							],
						'redshopb-stockroom_product_item:list' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=stockroom_product_item&webserviceVersion=$this->webserviceVersion&webserviceClient=site&api=Hal",
							],
						'redshopb-stockroom_product_item:self' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=stockroom_product_item&webserviceVersion=$this->webserviceVersion&webserviceClient=site&id=" . $this->stockroom_product_item['id'] . "&api=Hal",
							],
						'redshopb-stockroom' =>
							[
								'href' => "$baseUrl/index.php?option=redshopb-stockroom&webserviceVersion=1.0.0&webserviceClient=site&id=" . $this->stockroom['id'] . "&api=Hal",
								'title' => 'Aesir E-Commerce - Stockroom Webservice',
								'templated' => true,
							],
						'redshopb-product-item' =>
							[
								'href' => "$baseUrl/index.php?option=redshopb-product_item&webserviceVersion=1.0.0&webserviceClient=site&id=" . $this->product_item['id'] . "&api=Hal",
								'title' => 'Aesir E-Commerce - Product Item Webservice',
								'templated' => true,
							],
					],
				'id' => $this->stockroom_product_item['id'],
				'id_others' => [],
				'stockroom_id' => $this->stockroom['id'],
				'stockroom_id_others' => [],
				'product_item_id' => $this->product_item['id'],
				'product_item_id_others' => [],
				'amount' => $this->stockroom_product_item['amount'],
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
