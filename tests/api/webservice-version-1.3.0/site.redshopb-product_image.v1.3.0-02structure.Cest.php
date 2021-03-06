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
 * Class SiteRedshopbProductImage130StructureCest
 * @since 2.8.0
 */
class SiteRedshopbProductImage130StructureCest
{
	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $product;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $productImage;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $category;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $baseUrl;

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('Prepare data needed for the test');
		$this->faker = Faker\Factory::create();

		$I->comment('I create a product image');
		$this->productImage['name'] = $this->faker->bothify('SiteRedshopbProductImage130StructureCest product image ?##?');
		$this->productImage['erp_id'] = $this->faker->bothify('?##?');

		$I->comment('I create a category');
		$this->category['name'] = $this->faker->bothify('SiteRedshopbProductImage130StructureCest category ?##?');
		$this->category['id'] = (int) $I->createCategory($this->category['name'], '1.6.0');

		$I->comment('I create a product');
		$this->product['name'] = $this->faker->bothify('SiteRedshopbProductImage130StructureCest product ?##?');
		$this->product['sku'] = (int) $this->faker->numberBetween(100, 1000);
		$this->product['id'] = (int) $I->createProduct($this->product['name'], $this->product['sku'], $this->category['id'], '1.4.0');

		// The following base64 code is a representation of a green 16px x 16px image
		$this->product['image'] = "iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAIAAACRXR/mAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBNYWNpbnRvc2giIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RDUxRjY0ODgyQTkxMTFFMjk0RkU5NjI5MEVDQTI2QzUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDUxRjY0ODkyQTkxMTFFMjk0RkU5NjI5MEVDQTI2QzUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpENTFGNjQ4NjJBOTExMUUyOTRGRTk2MjkwRUNBMjZDNSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpENTFGNjQ4NzJBOTExMUUyOTRGRTk2MjkwRUNBMjZDNSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PuT868wAAABESURBVHja7M4xEQAwDAOxuPw5uwi6ZeigB/CntJ2lkmytznwZFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYW1qsrwABYuwNkimqm3gAAAABJRU5ErkJggg==";
	}

	/**
	 * @param ApiTester $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function create(ApiTester $I)
	{
		$I->wantTo('Upload a Base64 encoded Image');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_image'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=" . $this->productImage['erp_id']
			. "&product_id=" . $this->product['id']
			. "&image=" . $this->productImage['name']
			. "&image_upload=" . $this->product['image']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product_image:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->productImage['id'] = $ids[0];
		$I->comment('The id of the new created product-image with name ' . $this->productImage['name'] . ' is:' . $this->productImage['id']);
	}

	/**
	 * @param ApiTester $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing product with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET(
			'index.php'
			. '?option=redshopb&view=product_image'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->productImage['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$tmpUrl = $I->grabDataFromResponseByJsonPath('$.image');
		$this->productImage['url'] = $tmpUrl[0];
		$tmpAlts = $I->grabDataFromResponseByJsonPath('$.alt');
		$this->productImage['alt'] = $tmpAlts[0];

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' =>
					[
						'curies' =>
							[
								0 =>
									[
										'href'      => "$baseUrl/index.php?option=com_redshopb&view=product_image&webserviceVersion=1.3.0&webserviceClient=site&format=doc&api=Hal#{rel}",
										'title'     => 'Documentation',
										'name'      => 'redshopb-product_image',
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
								'href' => "$baseUrl/index.php?option=com_redshopb&view=product_image&webserviceVersion=1.3.0&webserviceClient=site&api=Hal",
							],
						'redshopb-product_image:self' =>
							[
								'href' => "$baseUrl/index.php?option=com_redshopb&view=product_image&webserviceVersion=1.3.0&webserviceClient=site&id=" . $this->productImage['id'] . "&api=Hal",
							],
						'redshopb-product' =>
							[
								'href' => "$baseUrl/index.php?option=redshopb-product&webserviceVersion=1.2.0&webserviceClient=site&id=" . $this->product['id'] . "&api=Hal",
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
				'id'                                => $this->productImage['id'],
				'id_others'                         => ['erp.' . $this->productImage['erp_id']],
				'image'                             => $this->productImage['url'],
				'product_id'                        => $this->product['id'],
				'product_id_others'                 => [],
				'product_attribute_value_id'        => NULL,
				'product_attribute_value_id_others' => NULL,
				'alt'                               => $this->productImage['alt'],
				'view'                              => 0,
				'state'                             => true,
			]
		);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteProduct($this->product['id']);
		$I->deleteCategory($this->category['id']);
	}
}