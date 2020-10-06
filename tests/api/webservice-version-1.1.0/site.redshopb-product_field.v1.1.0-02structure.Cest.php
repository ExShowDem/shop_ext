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
 * Class SiteRedshopbProductField110structureCest
 * @since 2.8.0
 */
class SiteRedshopbProductField110structureCest
{
	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $type_code;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $product_field;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $product_field_id;

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
		$I->wantTo('POST a new product_field');
		$this->faker = Faker\Factory::create();

		$this->product_field['name'] = $this->faker->bothify('SiteRedshopbProductField110structureCest ?##?');
		$this->product_field['title'] = $this->faker->bothify('SiteRedshopbProductField110structureCest ?##?');
		$this->type_code = 'textboxstring';

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&name=" . $this->product_field['name']
			. "&title=" . $this->product_field['title']
			. "&type_code=$this->type_code"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->product_field_id = $ids[0];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readItemAndCheckItsStructure(ApiTester $I)
	{
		$I->wantTo('GET an existing product_field');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_field_id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();

		$baseUrl = $I->getWebserviceBaseUrl();

		$I->seeResponseContainsJson(
			[
				'_links' => [
					'curies' => [
						0 => [
							'href'      => "$baseUrl/index.php?option=com_redshopb&view=product_field&webserviceVersion=1.1.0&webserviceClient=site&format=doc&api=Hal#{rel}",
							'title'     => 'Documentation',
							'name'      => 'redshopb-product_field',
							'templated' => true
						]
					],
					'base' => [
						'href'  => "$baseUrl/?api=Hal",
						'title' => 'Default page'
					],
					'redshopb-product_field:list' => [
						'href' => "$baseUrl/index.php?option=com_redshopb&view=product_field&webserviceVersion=1.1.0&webserviceClient=site&api=Hal"
					],
					'redshopb-product_field:self' => [
						'href' => "$baseUrl/index.php?option=com_redshopb&view=product_field&webserviceVersion=1.1.0&webserviceClient=site&id=$this->product_field_id&api=Hal"
					],
				],
				'id'                        => $this->product_field_id,
				'id_others'                 => [],
				'name'                      => $this->product_field['name'],
				'title'                     => $this->product_field['title'],
				'type_code'                 => $this->type_code,
				'filter_type_code'          => "",
				'values_field_id'           => NULL,
				'values_field_id_others'    => NULL,
				'description'               => "",
				'multiple_values'           => false,
				'only_available'            => false,
				'default_value'             => "",
				'ordering'                  => 1,
				'searchable_frontend'       => true,
				'searchable_backend'        => false,
				'params'                    => "",
				'state'                     => true,
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
		$I->webserviceCrudDelete('product_field', $this->product_field_id, '1.1.0');
	}
}