<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProductDescription110erpCest
{
	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $name;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $erpid;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $erpid_update;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $description;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $description_new;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $product;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $id;

	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $new_translation;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.5.0
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product_description');
		$this->faker = Faker\Factory::create();
		$this->name  = $this->faker->bothify('SiteRedshopbProductDescription110erpCest product_description ?##?');
		$this->erpid = (int) $this->faker->numberBetween(1, 9999);
		$this->erpid_update = (int) $this->faker->numberBetween(1, 9999);
		$this->description = $this->faker->bothify('SiteRedshopbProductDescription110crudCest description ?##?');
		$this->description_new = $this->faker->bothify('SiteRedshopbProductDescription110crudCest description_new ?##?');

		$this->product['name'] = (string) $this->faker->bothify('SiteRedshopbProductDescription110erpCest product ?##?');
		$this->product['sku'] = (int) $this->faker->numberBetween(1, 9999);

		$this->product['category_name'] = (string) $this->faker->bothify('SiteRedshopbProductDescription110erpCest category ?##?');
		$this->product['category_id'] = (int)
		$I->createCategory($this->product['category_name']);
		$this->product['id'] = (int)
		$I->createProduct($this->product['name'], $this->product['sku'], $this->product['category_id']);
	}

	/**
	 * @param ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function createWithErpId(ApiTester $I)
	{
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&name=$this->name"
			. "&id=$this->erpid"
			. "&product_id=" . $this->product['id']
			. "&description=$this->description"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product_description:self']['href']);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.id');
		$this->id = $ids[0];

		$I->comment("The ERP Id of the new created product_description with name '$this->name' is: '$this->erpid'");
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function readItemUsingErpId(ApiTester $I)
	{
		$I->wantTo("GET an existing product_description with its ERP id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(
			[
				'id'                            => $this->id,
				'id_others'                     => ['erp.' . $this->erpid],
				'product_id'                    => $this->product['id'],
				'product_id_others'             => array (),
				'main_attribute_value_id'       => 0,
				'main_attribute_id_others'      => NULL,
				'description_intro'             => '<p>' . $this->description . '</p>',
				'description'                   => '<p>' . $this->description . '</p>'
			]
		);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function updateUsingErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a new description with PUT using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');

		$I->sendPUT('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid"
			. "&description=$this->description_new"
		);

		$I->seeResponseCodeIs(200);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(
			[
			'id'                        => $this->id,
			'id_others'                 => ['erp.' . $this->erpid],
			'product_id'                => $this->product['id'],
			'product_id_others'         => [],
			'main_attribute_value_id'   => 0,
			'main_attribute_id_others'  => NULL,
			'description_intro'         => '<p>' . $this->description . '</p>',
			'description'               => '<p>' . $this->description . '</p>' . '<hr id="system-readmore" />' .'<p>' . $this->description_new . '</p>'
			]
		);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function updateErpidUsingErpid(ApiTester $I)
	{
		$I->wantTo('UPDATE description ERP id using the ERP id');
		$I->amHttpAuthenticated('admin', 'admin');

		$I->sendPUT('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid"
			. "&erp_id=$this->erpid_update"
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid_update"
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['id_others' => ['erp.' . $this->erpid]]);
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->erpid_update]]);

		$I->comment("The description ERP ID has been modified to: $this->erpid_update");
	}	

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.5.0
	 */
	public function translateERP(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Create a translation description with POST using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->new_translation = '<p>French</p>' . $this->description_new;

		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translate'
			. '&language=fr-FR'
			. "&id=erp.$this->erpid_update"
			. "&description=$this->new_translation"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translate'
			. '&language=fr-FR'
			. "&id=erp.$this->erpid_update"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.5.0
	 */
	public function translateRemoveERP(Step\Api\redshopb2b $I)
	{
		$I->amHttpAuthenticated('admin', 'admin');
		$I->wantTo('Delete a translation description with POST using its ERP id');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translateRemove'
			. '&language=fr-FR'
			. "&id=erp.$this->erpid_update"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&task=translate'
			. '&language=fr-FR'
			. "&id=erp.$this->erpid_update"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function deleteERP(ApiTester $I)
	{
		$I->wantTo('DELETE a description using DELETE using its ERP id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid_update"
		);
		$I->seeResponseCodeIs(200);
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpid_update"
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.5.0
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteProduct($this->product['id']);
		$I->deleteCategory($this->product['category_id']);
	}
}