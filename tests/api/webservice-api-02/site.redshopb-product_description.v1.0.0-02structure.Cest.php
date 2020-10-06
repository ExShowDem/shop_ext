<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProduct_description100structureCest
{
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product_description');
		$this->faker = Faker\Factory::create();
		$this->name  = $this->faker->bothify('SiteRedshopbProduct_description100structureCest product_description ?##?');
		$this->erpId  = (int) $this->faker->randomNumber(3);

		$this->product['name'] = (string) $this->faker->bothify('SiteRedshopbProduct_description100structureCest product ?##?');
		$this->product['sku']  = (int) $this->faker->randomNumber(3);
		$this->product['category_name'] = (string) $this->faker->bothify('SiteRedshopbProduct_description100structureCest category ?##?');
		$this->product['category_id']  = (int) $I->createCategory($this->product['category_name']);
		$this->product['id'] = (int) $I->createProduct($this->product['name'], $this->product['sku'], $this->product['category_id']);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0',
			[
				'name'			=> $this->name,
				'id'			=> $this->erpId,
				'product_id'	=> $this->product['id'],
				'description'	=> '<p>introduction text</p><hr id="system-readmore" /><p>a paragraph of <b>text</b></p>'
			]
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product_description:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];
		$I->comment("The id of the new created product_description with name '$this->name' is: $this->id");
	}

	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing product_description with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
			'index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			array (
				'_links' =>
					array (
						'curies' =>
							[
								0 =>
									[
										'href' => "$baseUrl/index.php?option=com_redshopb&view=product_description&webserviceVersion=1.0.0&webserviceClient=site&format=doc&api=Hal#{rel}",
										'title' => 'Documentation',
										'name' => 'redshopb-product_description',
										'templated' => true,
									],
							],
						'base' =>
							array (
								'href' => "$baseUrl/?api=Hal",
								'title' => 'Default page',
							),
						'redshopb-product_description:list' =>
							array (
								'href' => "$baseUrl/index.php?option=com_redshopb&view=product_description&webserviceVersion=1.0.0&webserviceClient=site&api=Hal",
							),
						'redshopb-product_description:self' =>
							array (
								'href' => "$baseUrl/index.php?option=com_redshopb&view=product_description&webserviceVersion=1.0.0&webserviceClient=site&id=$this->id&api=Hal",
							),
						'redshopb-product' =>
							array (
								'href' => "$baseUrl/index.php?option=redshopb&view=product&webserviceVersion=1.1.0&webserviceClient=site&id=" . $this->product['id'] . "&api=Hal",
								'title' => 'Aesir E-Commerce - Product Webservice',
								'templated' => true,
							),
						'redshopb-product_attribute_value' =>
							array (
								'href' => "$baseUrl/index.php?option=redshopb&view=product_attribute_value&webserviceVersion=1.1.0&webserviceClient=site&id=0&api=Hal",
								'title' => 'Aesir E-Commerce - Product Attribute Value Webservice',
								'templated' => true,
							),
					),
				'id' => $this->id,
				'id_others' =>
					array (
						0 => 'erp.' . $this->erpId,
					),
				'product_id' => $this->product['id'] ,
				'product_id_others' =>
					array (
					),
				'main_attribute_value_id' => 0,
				'main_attribute_id_others' => NULL,
				'description_intro' => "<p>introduction text</p>",
				'description' => "<p>introduction text</p><hr id=\"system-readmore\" /><p>a paragraph of <b>text</b></p>"
			)
		);
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteProduct($this->product['id'], '1.0.0');
		$I->deleteCategory($this->product['category_id'], '1.0.0');
	}
}
