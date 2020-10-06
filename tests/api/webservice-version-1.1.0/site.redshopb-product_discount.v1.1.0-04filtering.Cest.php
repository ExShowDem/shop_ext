<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProductDiscount110filteringCest
 * @since 2.6.0
 */
class SiteRedshopbProductDiscount110filteringCest
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
	protected $companyA;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $companyB;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $productA;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $productB;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $currency_codeA;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $currency_codeB;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $product_discount_total;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $product_discountA_id;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $product_discountB_id;

	/**
	 * Prepares the following structure
	 *
	 * +-------------------+------------+------------+---------------+-------------+
	 * | Product_discount  | Product_id | Company_id | Currency_code |   Status    |
	 * +-------------------+------------+------------+---------------+-------------+
	 * | Product_discountA |  ProductA  |  CompanyA  |      DKK      | Published   |
	 * | Product_discountB |  ProductB  |  CompanyB  |      USD      | Unpublished |
	 * +-------------------+------------+------------+---------------+-------------+
	 */

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function prepare (Step\Api\redshopb2b $I)
	{
		$I->wantTo('I will create what is necessary for the tests.');
		$this->faker = Faker\Factory::create();

		$I->comment('I create a category');
		$this->category['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110filteringCest category ?##?');
		$this->category['id']   = (int) $I->createCategory($this->category['name']);

		$I->comment('I create two companies');
		$this->companyA['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110filteringCest companyA ?##?');
		$this->companyA['id'] = (int) $I->createCompany($this->companyA['name']);

		$this->companyB['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110filteringCest companyB ?##?');
		$this->companyB['id'] = (int) $I->createCompany($this->companyB['name']);

		$I->comment('I create two products');
		$this->productA['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110filteringCest productA ?##?');
		$this->productA['sku']  = $this->faker->numberBetween(100, 1000);
		$this->productA['id'] = (int) $I->createProduct($this->productA['name'], $this->productA['sku'], $this->category['id']);

		$this->productB['name'] = $this->faker->bothify('SiteRedshopbProduct_Discount110filteringCest productB ?##?');
		$this->productB['sku']  = $this->faker->numberBetween(100, 1000);
		$this->productB['id'] = (int) $I->createProduct($this->productB['name'], $this->productB['sku'], $this->category['id']);

		$I->comment('I will create the necessary');
		$this->currency_codeA = 'DKK';
		$this->currency_codeB = 'USD';
		$this->product_discount_total = '10';
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function create(Step\Api\redshopb2b $I)
	{
		$I->wantTo('POST a new product_discountA');
		$I->comment('I create the product_discountA');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&kind=1'
			. "&total=$this->product_discount_total"
			. "&currency_code=$this->currency_codeA"
			. "&product_id=" . $this->productA['id']
			. "&company_id=" . $this->companyA['id']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->product_discountA_id = $ids[0];

		$I->comment('I create the product_discountB');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&kind=1'
			. "&total=$this->product_discount_total"
			. "&currency_code=$this->currency_codeB"
			. "&product_id=" . $this->productB['id']
			. "&company_id=" . $this->companyB['id']
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->product_discountB_id = $ids[0];

		$I->comment('I unpublish the product_discountB to be filtered by state');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->product_discountB_id"
		);

		$I->seeResponseCodeIs(200);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByProductId(ApiTester $I)
	{
		$I->wantTo('GET a list of product_discount filtered by product id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[product_id]=" . $this->productA['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['product_id' => $this->productA['id']]);
		$I->seeResponseContainsJson(['id' => $this->product_discountA_id]);
		$I->dontSeeResponseContainsJson(['product_id' => $this->productB['id']]);
		$I->dontSeeResponseContainsJson(['id' => $this->product_discountB_id]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByCompanyId(ApiTester $I)
	{
		$I->wantTo('GET a list of product_discount filtered by company id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[company_id]=" . $this->companyA['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['company_id' => $this->companyA['id']]);
		$I->seeResponseContainsJson(['id' => $this->product_discountA_id]);
		$I->dontSeeResponseContainsJson(['company_id' => $this->companyB['id']]);
		$I->dontSeeResponseContainsJson(['id' => $this->product_discountB_id]);
	}

	// The filtering by currency_code not working in product_discount webservice 1.1.0

//    public function readListFilteredByCurrencyCode(ApiTester $I)
//    {
//        $I->wantTo('GET a list of product_discount filtered by currency code');
//        $I->amHttpAuthenticated('admin', 'admin');
//        $I->sendGET('index.php'
//            . '?option=redshopb&view=product_discount'
//            . '&api=Hal'
//            . '&webserviceClient=site'
//            . '&webserviceVersion=1.1.0'
//            . '&list[ordering]=id'
//            . '&list[direction]=desc'
//            . "&filter[currency_code]=$this->currency_codeA"
//        );
//
//        $I->seeResponseCodeIs(200);
//        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson(['currency_code' => $this->currency_codeA]);
//        $I->seeResponseContainsJson(['id' => $this->product_discountA_id]);
//        $I->dontSeeResponseContainsJson(['currency_code' => $this->currency_codeB]);
//        $I->dontSeeResponseContainsJson(['id' => $this->product_discountB_id]);
//    }

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByState(ApiTester $I)
	{
		$I->wantTo('GET a list of product_discount filtered by State');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_discount'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[state]=false"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->product_discountB_id]);
		$I->dontSeeResponseContainsJson(['id' => $this->product_discountA_id]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->webserviceCrudDelete('product_discount', $this->product_discountA_id, '1.1.0');
		$I->webserviceCrudDelete('product_discount', $this->product_discountB_id, '1.1.0');
		$I->deleteProduct($this->productA['id']);
		$I->deleteProduct($this->productB['id']);
		$I->deleteCategory($this->category['id']);
		$I->deleteCompany($this->companyA['id']);
		$I->deleteCompany($this->companyB['id']);
	}
}