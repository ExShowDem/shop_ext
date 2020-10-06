<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProduct130filteringCest
 * @since 2.6.0
 */
class SiteRedshopbProduct130filteringCest
{
	/**
	 * @var array $productA to be filtered by search filter
	 * @since 2.6.0
	 */
	protected $productA = array();

	/**
	 * @var array $productB to be filtered by sku filter
	 * @since 2.6.0
	 */
	protected $productB = array();

	/**
	 * @var array $productC to be filtered by manufacturer_sku
	 * @since 2.6.0
	 */
	protected $productC = array();

	/**
	 * @var array $productD to be filtered by related_sku
	 * @since 2.6.0
	 */
	protected $productD = array();

	/**
	 * @var array $productE to be filtered by template_code
	 * @since 2.6.0
	 */
	protected $productE = array();

	/**
	 * @var array $productF to be filtered by company_id
	 * @since 2.6.0
	 */
	protected $productF = array();

	/**
	 * @var array $productG to be filtered by category_id
	 * @since 2.6.0
	 */
	protected $productG = array();

	/**
	 * @var array $productH to be filtered by manufacturer_id
	 * @since 2.6.0
	 */
	protected $productH = array();

	/**
	 * @var array $productI to be filtered by service
	 * @since 2.6.0
	 */
	protected $productI = array();

	/**
	 * @var array $productJ to be filtered by featured
	 * @since 2.6.0
	 */
	protected $productJ = array();

	/**
	 * @var array $productK to be filtered by state
	 * @since 2.6.0
	 */
	protected $productK = array();

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $categoryA;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $categoryB;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $manufacturerA;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $manufacturer_sku;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $related_sku;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $template_code;

	/**
	 * @var
	 * @since 2.6.0
	 */
	protected $companyA;

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function prepare(Step\Api\redshopb2b $I)
	{
		$I->wantTo('I will create what is necessary for the tests.');
		$this->faker = Faker\Factory::create();

		$I->comment('I create two categories');
		$this->categoryA['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest categoryA ?##?');
		$this->categoryA['id']   = (int) $I->createCategory($this->categoryA['name']);
		$this->categoryB['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest categoryB ?##?');
		$this->categoryB['id']   = (int) $I->createCategory($this->categoryB['name']);

		$I->comment('I create a manufacturer');
		$this->manufacturerA['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest manufacturerA ?##?');
		$this->manufacturerA['id'] = (int) $I->createManufacturer($this->manufacturerA['name']);

		$I->comment('I create a company');
		$this->companyA['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest companyA ?##?');
		$this->companyA['id'] = (int) $I->createCompany($this->companyA['name']);

		$I->comment('I will create the necessary');
		$this->manufacturer_sku = '1';
		$this->related_sku = '1';
		$this->template_code = 'product';
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function create(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Created the products per each filter to be tested');
		$this->faker = Faker\Factory::create();

		$I->comment('I create the productA to be filtered by search filter');
		$this->productA['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productA ?##?');
		$this->productA['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productA['name']
			. "&sku=" . $this->productA['sku']
			. "&category_id=" . $this->categoryA['id']
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productA['id'] = $ids[0];

		$I->comment('I create the productB to be filtered by sku filter');
		$this->productB['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productB ?##?');
		$this->productB['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productB['name']
			. "&sku=" . $this->productB['sku']
			. "&category_id=" . $this->categoryA['id']
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productB['id'] = $ids[0];

		$I->comment('I create the productC to be filtered by manufacturer_sku');
		$this->productC['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productC ?##?');
		$this->productC['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productC['name']
			. "&sku=" . $this->productC['sku']
			. "&category_id=" . $this->categoryA['id']
			. "&manufacturer_sku=$this->manufacturer_sku"
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productC['id'] = $ids[0];

		$I->comment('I create the productD to be filtered by related_sku');
		$this->productD['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productD ?##?');
		$this->productD['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productD['name']
			. "&sku=" . $this->productD['sku']
			. "&category_id=" . $this->categoryA['id']
			. "&related_sku=$this->related_sku"
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productD['id'] = $ids[0];

		$I->comment('I create the productE to be filtered by template_code');
		$this->productE['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productE ?##?');
		$this->productE['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productE['name']
			. "&sku=" . $this->productE['sku']
			. "&category_id=" . $this->categoryA['id']
			. "&template_code=$this->template_code"
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productE['id'] = $ids[0];

		$I->comment('I create the productF to be filtered by company_id');
		$this->productF['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productF ?##?');
		$this->productF['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productF['name']
			. "&sku=" . $this->productF['sku']
			. "&category_id=" . $this->categoryA['id']
			. "&company_id=" . $this->companyA['id']
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productF['id'] = $ids[0];

		$I->comment('I create the productG to be filtered by category_id');
		$this->productG['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productG ?##?');
		$this->productG['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productG['name']
			. "&sku=" . $this->productG['sku']
			. "&category_id=" . $this->categoryB['id']
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productG['id'] = $ids[0];

		$I->comment('I create the productH to be filtered by manufacturer_id');
		$this->productH['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productH ?##?');
		$this->productH['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productH['name']
			. "&sku=" . $this->productH['sku']
			. "&category_id=" . $this->categoryA['id']
			. "&manufacturer_id=" . $this->manufacturerA['id']
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productH['id'] = $ids[0];

		$I->comment('I create the productI to be filtered by service');
		$this->productI['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productI ?##?');
		$this->productI['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productI['name']
			. "&sku=" . $this->productI['sku']
			. "&category_id=" . $this->categoryA['id']
			. "&service=true"
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productI['id'] = $ids[0];

		$I->comment('I create the productJ to be filtered by featured');
		$this->productJ['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productJ ?##?');
		$this->productJ['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productJ['name']
			. "&sku=" . $this->productJ['sku']
			. "&category_id=" . $this->categoryA['id']
			. "&featured=true"
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productJ['id'] = $ids[0];

		$I->comment('I create the productK to be filtered by state');
		$this->productK['name'] = $this->faker->bothify('SiteRedshopbProduct130filteringCest productK ?##?');
		$this->productK['sku']  = $this->faker->numberBetween(100, 1000);
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. "&name=" . $this->productK['name']
			. "&sku=" . $this->productK['sku']
			. "&category_id=" . $this->categoryA['id']
		);

		$I->seeResponseCodeis(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->productK['id'] = $ids[0];

		$I->comment('I unpublish the productK to be filtered by state');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&id=' . $this->productK['id']
		);

        $I->seeResponseCodeIs(200);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredBySearch(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by search');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[search]=' . $this->productA['name']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productA['id']]);
		$I->seeResponseContainsJson(['name' => $this->productA['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productB['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productC['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredBySku(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by sku');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. '&filter[sku]=' . $this->productB['sku']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productB['id']]);
		$I->seeResponseContainsJson(['name' => $this->productB['name']]);
		$I->seeResponseContainsJson(['sku' => $this->productB['sku']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productC['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productD['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByManufacturerSku(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by manufacturer_sku');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[manufacturer_sku]=$this->manufacturer_sku"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productC['id']]);
		$I->seeResponseContainsJson(['name' => $this->productC['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productD['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productE['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByRelatedSku(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by related_sku');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[related_sku]=$this->related_sku"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productD['id']]);
		$I->seeResponseContainsJson(['name' => $this->productD['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productE['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productF['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByTemplateCode(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by template_code');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[template_code]=$this->template_code"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productE['id']]);
		$I->seeResponseContainsJson(['name' => $this->productE['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productF['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productG['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByCompanyId(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by company_id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[company_id]=" . $this->companyA['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productF['id']]);
		$I->seeResponseContainsJson(['name' => $this->productF['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productG['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productH['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByCategoryId(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by category_id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[category_id]=" . $this->categoryB['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productG['id']]);
		$I->seeResponseContainsJson(['name' => $this->productG['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productH['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productI['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByManufacturerId(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by manufacturer_id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[manufacturer_id]=" . $this->manufacturerA['id']
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productH['id']]);
		$I->seeResponseContainsJson(['name' => $this->productH['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productI['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productJ['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByService(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by service');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[service]=true"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productI['id']]);
		$I->seeResponseContainsJson(['name' => $this->productI['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productJ['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productK['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByFeatured(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by featured');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[featured]=true"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productJ['id']]);
		$I->seeResponseContainsJson(['name' => $this->productJ['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productK['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productA['name']]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function readListFilteredByState(ApiTester $I)
	{
		$I->wantTo('GET a list of products filtered by state');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
			. '&list[ordering]=id'
			. '&list[direction]=desc'
			. "&filter[state]=false"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->productK['id']]);
		$I->seeResponseContainsJson(['name' => $this->productK['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productA['name']]);
		$I->dontSeeResponseContainsJson(['name' => $this->productB['name']]);
	}

	/**
	 * @param \Step\Api\redshopb2b $I
	 * @since 2.6.0
	 */
	public function cleanUp(Step\Api\redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteProduct($this->productA['id'], '1.3.0');
		$I->deleteProduct($this->productB['id'], '1.3.0');
		$I->deleteProduct($this->productC['id'], '1.3.0');
		$I->deleteProduct($this->productD['id'], '1.3.0');
		$I->deleteProduct($this->productE['id'], '1.3.0');
		$I->deleteProduct($this->productF['id'], '1.3.0');
		$I->deleteProduct($this->productG['id'], '1.3.0');
		$I->deleteProduct($this->productH['id'], '1.3.0');
		$I->deleteProduct($this->productI['id'], '1.3.0');
		$I->deleteProduct($this->productJ['id'], '1.3.0');
		$I->deleteProduct($this->productK['id'], '1.3.0');
		$I->deleteCategory($this->categoryA['id']);
		$I->deleteCategory($this->categoryB['id']);
		$I->deleteManufacturer($this->manufacturerA['id']);
		$I->deleteCompany($this->companyA['id']);
	}
}