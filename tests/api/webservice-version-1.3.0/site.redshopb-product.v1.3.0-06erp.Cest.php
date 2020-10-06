<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProduct130erpCest
 * @since 2.6.0
 */
class SiteRedshopbProduct130erpCest
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
	protected $category;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $company;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $tag;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $product;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('I will create what is necessary fo the tests');
		$this->faker = Faker\Factory::create();

		$I->comment('I create a category');
		$this->category['name'] = $this->faker->bothify('SiteRedshopbProduct130erpCest category ?##?');
		$this->category['id'] = (int) $I->createCategory($this->category['name']);

		$I->comment('I create a company');
		$this->company['name'] = $this->faker->bothify('SiteRedshopbProduct130erpCest company ?##?');
		$this->company['id'] = (int) $I->createCompany($this->company['name']);

		$I->comment('I create a tag');
		$this->tag['name'] = $this->faker->bothify('SiteRedshopbProduct130erpCest tag ?##?');
		$this->tag['id'] = (int) $I->createTag($this->tag['name']);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function createWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('I created the products with Erp Id');
		$this->faker = Faker\Factory::create();

		//Crud and Tasks
		$I->comment('I create the product to be tested');
		$this->product['name'] = $this->faker->bothify('SiteRedshopbProduct130erpCest product ?##?');
		$this->product['sku']  = $this->faker->numberBetween(100, 1000);
		$this->product['erpId'] = $this->faker->numberBetween(100, 1000);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->product['name']
			. "&sku=" . $this->product['sku']
			. "&id=" . $this->product['erpId']
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$links = $I->grabDataFromResponseByJsonPath('$._links');
		$I->sendGET($links[0]['redshopb-product:self']['href']);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->product['erpId']]]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskUnpublishWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('unpublish a product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['state' => false]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskPublishWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('publish a product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=publish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['state' => true]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskSetPriceWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('setPrice a product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=setPrice'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
			. '&price=10'
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['price' => 10]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskSetRetailPriceWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('setRetailPrice a product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=setRetailPrice'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
			. '&retail_price=5'
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['retail_price' => 5]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskFeatureWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('feature a product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=feature'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['featured' => true]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskUnfeatureWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('unfeature a product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=unfeature'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['featured' => false]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskCategoryAddWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('categoryAdd to the product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=categoryAdd'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
			. '&category_id=' . $this->category['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['categories' => [0 => $this->category['id']]]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskCategoryRemoveWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('categoryRemove to the product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=categoryRemove'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
			. '&category_id=' . $this->category['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->dontSeeResponseContainsJson(['categories' => [0 => $this->category['id']]]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskTagAddWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('tagAdd to the product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=tagAdd'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
			. '&tag_id=' . $this->tag['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['tags' => [0 => $this->tag['id']]]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskTagRemoveWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('tagRemove to the product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=tagRemove'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
			. '&tag_id=' . $this->tag['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->dontSeeResponseContainsJson(['tags' => [0 => $this->tag['id']]]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskCompanyLimitationAddWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('companyLimitationAdd to the product using GET with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=companyLimitationAdd'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
			. '&company_id=' . $this->company['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['company_limits' => [0 => $this->company['id']]]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskTranslateWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Create a translation for a product with POST with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->product['translatedName'] = 'french-' . $this->product['name'];
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&task=translate'
			. '&language=fr-FR'
			. '&id=erp.' . $this->product['erpId']
			. '&name=' . $this->product['translatedName']
		);

		$I->seeResponseCodeis(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
			. '&language=fr-FR'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['translatedName']]);
		$I->dontSeeResponseContainsJson(['name' => $this->product['name']]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskTranslateRemoveWithErpId(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Remove the translation for the product with Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&task=translateRemove'
			. '&language=fr-FR'
			. '&id=erp.' . $this->product['erpId']
		);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=erp.' . $this->product['erpId']
			. '&language=fr-FR'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->product['translatedName']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readItemUsingErpId(ApiTester $I)
	{
		$I->wantTo("GET an existing product with its Erp Id");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=erp." . $this->product['erpId']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['state' => true]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function updateUsingErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a product with PUT using its Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->product['updateName'] = 'new_erp_' . $this->product['name'];
		$I->sendPUT('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=erp." . $this->product['erpId']
			. "&name=" . $this->product['updateName']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=erp." . $this->product['erpId']
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontseeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['name' => $this->product['updateName']]);

		$this->product['name'] = $this->product['updateName'];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function deleteUsingErpId(ApiTester $I)
	{
		$I->wantTo('DELETE a product using its Erp Id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=erp." . $this->product['erpId']
		);
		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&id=erp." . $this->product['erpId']
		);
		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"message":"Item not found with given key.","code":404,"type":"Exception"');
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteCategory($this->category['id']);
		$I->deleteCompany($this->company['id']);
		$I->deleteTag($this->tag['id']);
	}
}
