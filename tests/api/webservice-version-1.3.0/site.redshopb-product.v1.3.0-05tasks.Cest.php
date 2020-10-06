<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProduct130tasksCest
 * @since 2.6.0
 */
class SiteRedshopbProduct130tasksCest
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
		$I->wantTo('I will create what is necessary for the tests.');
		$this->faker = Faker\Factory::create();

		$I->comment('I create a category');
		$this->category['name'] = $this->faker->bothify('SiteRedshopbProduct130tasksCest category ?##?');
		$this->category['id'] = (int) $I->createCategory($this->category['name']);

		$I->comment('I create a company');
		$this->company['name'] = $this->faker->bothify('SiteRedshopbProduct130tasksCest company ?##?');
		$this->company['id'] = (int) $I->createCompany($this->company['name']);

		$I->comment('I create a tag');
		$this->tag['name'] = $this->faker->bothify('SiteRedshopbProduct130tasksCest tag ?##?');
		$this->tag['id'] = (int) $I->createTag($this->tag['name']);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function create(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Created the product to be tested');
		$this->faker = Faker\Factory::create();

		$I->comment('I create the product to be tested');
		$this->product['name'] = $this->faker->bothify('SiteRedshopbProduct130tasksCest product ?##?');
		$this->product['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->product['name']
			. "&sku=" . $this->product['sku']
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->product['id'] = $ids[0];
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskUnpublish(Step\Api\redshopb2b $I)
	{
		$I->wantTo('unpublish a product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskPublish(Step\Api\redshopb2b $I)
	{
		$I->wantTo('publish a product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=publish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskSetPrice(Step\Api\redshopb2b $I)
	{
		$I->wantTo('setPrice a product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=setPrice'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
			. '&price=10'
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskSetRetailPrice(Step\Api\redshopb2b $I)
	{
		$I->wantTo('setRetailPrice a product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=setRetailPrice'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
			. '&retail_price=5'
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskFeature(Step\Api\redshopb2b $I)
	{
		$I->wantTo('feature a product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=feature'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskUnfeature(Step\Api\redshopb2b $I)
	{
		$I->wantTo('unfeature a product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=unfeature'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskCategoryAdd(Step\Api\redshopb2b $I)
	{
		$I->wantTo('categoryAdd to the product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=categoryAdd'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
			. '&category_id=' . $this->category['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskCategoryRemove(Step\Api\redshopb2b $I)
	{
		$I->wantTo('categoryRemove to the product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=categoryRemove'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
			. '&category_id=' . $this->category['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskTagAdd(Step\Api\redshopb2b $I)
	{
		$I->wantTo('tagAdd to the product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=tagAdd'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
			. '&tag_id=' . $this->tag['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskTagRemove(Step\Api\redshopb2b $I)
	{
		$I->wantTo('tagRemove to the product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=tagRemove'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
			. '&tag_id=' . $this->tag['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskCompanyLimitationAdd(Step\Api\redshopb2b $I)
	{
		$I->wantTo('companyLimitationAdd to the product using GET');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=companyLimitationAdd'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
			. '&company_id=' . $this->company['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->seeResponseContainsJson(['company_limits' => [0 => $this->company['id']]]);
	}

	// The Task CompanyLimitationRemove not working in product webservice 1.3.0 or 1.4.0

//    public function taskCompanyLimitationRemove(Step\Api\redshopb2b $I)
//    {
//        $I->wantTo('companyLimitationRemove to the product using GET');
//        $I->amHttpAuthenticated('admin', 'admin');
//        $I->sendGET('index.php'
//            . '?option=redshopb&view=product'
//            . '&task=companyLimitationRemove'
//            . '&api=hal'
//            . '&webserviceClient=site'
//            . '&webserviceVersion=1.3.0'
//            . '&id=' . $this->product['id']
//            . '&company_id=' . $this->company['id']
//        );
//
//        $I->seeResponseCodeIs(200);
//
//        $I->sendGET('index.php'
//            . '?option=redshopb&view=product'
//            . '&api=Hal'
//            . '&webserviceClient=site'
//            . '&webserviceVersion=1.3.0'
//            . '&id=' . $this->product['id']
//        );
//
//        $I->seeResponseCodeIs(200);
//        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson(['name' => $this->product['name']]);
//        $I->dontSeeResponseContainsJson(['company_limits' => [0 => $this->company['id']]]);
//    }

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function taskTranslate(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Create a translation for a product with POST');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->product['translatedName'] = 'french-' . $this->product['name'];
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&task=translate'
			. '&language=fr-FR'
			. '&id=' . $this->product['id']
			. '&name=' . $this->product['translatedName']
		);

		$I->seeResponseCodeis(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
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
	public function taskTranslateRemove(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Remove the translation for the product');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&task=translateRemove'
			. '&language=fr-FR'
			. '&id=' . $this->product['id']
		);

		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->product['id']
			. '&language=fr-FR'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->seeResponseContainsJson(['name' => $this->product['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->product['translatedName']]);
	}

	// The Task Discontinue not working in product webservice 1.3.0 but this is fixed in version 1.4.0

//    public function taskDiscontinue(Step\Api\redshopb2b $I)
//    {
//        $I->wantTo('discontinue a product using GET');
//        $I->amHttpAuthenticated('admin', 'admin');
//        $I->sendGET('index.php'
//            . '?option=redshopb&view=product'
//            . '&task=discontinue'
//            . '&api=hal'
//            . '&webserviceClient=site'
//            . '&webserviceVersion=1.3.0'
//            . '&id=' . $this->product['id']
//        );
//
//        $I->seeResponseCodeIs(200);
//
//        $I->sendGET('index.php'
//            . '?option=redshopb&view=product'
//            . '&api=Hal'
//            . '&webserviceClient=site'
//            . '&webserviceVersion=1.3.0'
//            . '&id=' . $this->product['id']
//        );
//
//        $I->seeResponseCodeIs(200);
//        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson(['name' => $this->product['name']]);
//        $I->seeResponseContainsJson(['discontinued' => true]);
//    }

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteProduct($this->product['id']);
		$I->deleteCategory($this->category['id']);
		$I->deleteCompany($this->company['id']);
		$I->deleteTag($this->tag['id']);
	}
}