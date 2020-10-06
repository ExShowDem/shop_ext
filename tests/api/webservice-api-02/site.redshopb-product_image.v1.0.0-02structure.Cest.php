<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProduct_image100structureCest
{
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product image uploading a Base64 encoded Image');
		$this->faker = Faker\Factory::create();

		$this->producImage['name'] = $this->faker->bothify('SiteRedshopbProduct100CrudCest product-image ?##?');
		$this->producImage['erp_id'] = $this->faker->bothify('?##?');
		$this->product['name'] = $this->faker->bothify('SiteRedshopbProduct100CrudCest product ?##?');
		$this->product['sku'] = $this->faker->randomNumber(3);
		$this->product['category'] = $this->faker->bothify('SiteRedshopbProduct100CrudCest category ?##?');
		$this->product['category_id']  = (int) $I->createCategory($this->product['category'], '1.1.0');
		$this->product['id'] = (int) $I->createProduct(
			$this->product['name'],
			$this->product['sku'],
			$this->product['category_id'],
			'1.1.0'
		);

		// The following base64 code is a representation of a green 16px x 16px image
		$this->product['image'] = "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";

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_image'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0',
			[
				'id' => $this->producImage['erp_id'],
				'product_id' => $this->product['id'],
				'image' => $this->producImage['name'],
				'image_upload' => $this->product['image']
			]
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product_image:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->producImage['id'] = $ids[0];
		$I->comment('The id of the new created product-image with name ' . $this->producImage['name'] . ' is:' . $this->producImage['id']);
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing product with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
			'index.php'
			. '?option=redshopb&view=product_image'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&id=' . $this->producImage['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$tmpUrl = $I->grabDataFromResponseByJsonPath('$.image');
		$this->producImage['url'] = $tmpUrl[0];
		$tmpAlts = $I->grabDataFromResponseByJsonPath('$.alt');
		$this->producImage['alt'] = $tmpAlts[0];

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' =>
					[
						'curies' =>
							[
								0 =>
									[
										'href' => "$baseUrl/index.php?option=com_redshopb&view=product_image&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
										'title' => 'Documentation',
										'name' => 'redshopb-product_image',
										'templated' => true,
									],
							],
						'base' =>
							[
								'href' => "$baseUrl/?api=Hal",
								'title' => 'Default page',
							],
						'redshopb-product_image:list' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=product_image&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
							],
						'redshopb-product_image:self' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=product_image&webserviceVersion=1.0.0&webserviceClient=site&id=" . $this->producImage['id'] . "&api=Hal",
							],
						'redshopb-product' =>
						[
							'href' => "$baseUrl/index.php?option=redshopb-product&webserviceVersion=1.0.0&webserviceClient=site&id=" . $this->product['id'] . "&api=Hal",
							'title' => 'Aesir E-Commerce - Product Webservice',
							'templated' => true,
						],
						'redshopb-product_attribute_value' =>
						[
							'href' => '?api=Hal',
							'title' => 'Aesir E-Commerce - Product Attribute Value Webservice',
							'templated' => true,
						],
					],
				'id' => $this->producImage['id'],
				'id_others' => ['erp.' . $this->producImage['erp_id']],
				'image' => $this->producImage['url'],
				'product_id' => $this->product['id'],
				'product_id_others' =>
					[],
				'product_attribute_value_id' => NULL,
				'product_attribute_value_id_others' => NULL,
				'alt' => $this->producImage['alt'],
				'view' => 1,
				'state' => true,
			]
		);
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteProduct($this->product['id'], '1.1.0');
		$I->deleteCategory($this->product['category_id'], '1.1.0');
	}
}
