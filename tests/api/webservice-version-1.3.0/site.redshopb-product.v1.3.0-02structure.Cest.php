<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProduct130structureCest
 * @since 2.6.0
 */
class SiteRedshopbProduct130structureCest
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
	protected $name;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $sku;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $category;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $id;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product');
		$this->faker = Faker\Factory::create();

		$this->name = $this->faker->bothify('SiteRedshopbProduct130structureCest product ?##?');
		$this->sku  = $this->faker->numberBetween(100, 1000);

		$this->category['name'] = $this->faker->bothify('SiteRedshopbProduct130structureCest category ?##?');
		$this->category['id']   = (int) $I->createCategory($this->category['name']);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=$this->name"
			. "&sku=$this->sku"
			. "&category_id=" . $this->category['id']
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
		$I->comment("The id of the new created product with name '$this->name' is: '$this->id'");
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo("GET an existing product with its internal id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseisJson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' => [
					'curies' => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=product&webserviceVersion=1.3.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-product',
							'templated' => true
						]
					],
					'base' => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page'
					],
					'redshopb-product:list' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=product&webserviceVersion=1.3.0&webserviceClient=site&api=Hal"
					],
					'redshopb-product:self' => [
						'href'  => "$baseUrl/index.php?option=com_redshopb&view=product&webserviceVersion=1.3.0&webserviceClient=site&id=$this->id&api=Hal"
					],
				],
				'id'                        => $this->id,
				'id_others'                 => [],
				'name'                      => $this->name,
				'alias'                     => $I->getAlias($this->name),
				'sku'                       => $this->sku,
				'manufacturer_sku'          => '',
				'related_sku'               => '',
				'date_new'                  => '',
				'stock_upper_level'         => 0,
				'stock_lower_level'         => 0,
				'template_code'             => '',
				'company_id'                => 0,
				'company_id_others'         => NULL,
				'category_id'               => $this->category['id'],
				'category_id_others'        => [],
				'manufacturer_id'           => 0,
				'decimal_position'          => 0,
				'manufacturer_id_others'    => NULL,
				'filter_fieldset_id'        => 0,
				'filter_fieldset_id_others' => NULL,
				'unit_measure_code'         => '',
				'price'                     => 0,
				'retail_price'              => 0,
				'service'                   => false,
				'discontinued'              => false,
				'featured'                  => false,
				'state'                     => true,
				'categories'                => [0 => $this->category['id']],
				'categories_other_ids'      => [],
				'tags'                      => [],
				'tags_other_ids'            => [],
				'company_limits'            => [],
				'company_limits_other_ids'  => [],
				'min_sale'                  => 0,
				'max_sale'                  => 0,
				'pkg_size'                  => 1,
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
		$I->deleteProduct($this->id, '1.3.0');
		$I->deleteCategory($this->category['id']);
	}
}