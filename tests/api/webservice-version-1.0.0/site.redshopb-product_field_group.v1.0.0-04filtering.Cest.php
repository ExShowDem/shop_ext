<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProductFieldGroup100FilteringCest
{
	/**
	 * @var array
	 */
	public $product_field_group1 = array();
	/**
	 * @var array
	 */
	public $product_field_group2 = array();
	/**
	 * @param \Step\Api\redshopb2b $I
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$this->faker = Faker\Factory::create();

		$I->wantTo('Create one product_field_group per each filter to be tested');

		$I->comment('I create a product_field_group1 to be filtered by SEARCH');
		$this->product_field_group1['name'] = (string) $this->faker->bothify('SiteRedshopbProductFieldGroup100FilteringCest product-field-group1 ?##?');
		$this->product_field_group1['id'] = (int)
		$I->createProductFieldGroup($this->product_field_group1['name'], '1.0.0');

		$I->comment('I create a product_field_group2 to be filtered by SEARCH');
		$this->product_field_group2['name'] = (string) $this->faker->bothify('SiteRedshopbProductFieldGroup100FilteringCest product-field-group2 ?##?');
		$this->product_field_group2['id'] = (string)
		$I->createProductFieldGroup($this->product_field_group2['name'], '1.0.0');
	}

	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo("GET a list of categories filtered by search");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&list[ordering]=id'
			. '&filter[search]=' . $this->product_field_group1['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->product_field_group1['id']]);
		$I->seeResponseContainsJson(['name' => $this->product_field_group1['name']]);
	}

	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->deleteProductFieldGroup($this->product_field_group1['id'], '1.0.0');
		$I->deleteProductFieldGroup($this->product_field_group2['id'], '1.0.0');
	}
}
