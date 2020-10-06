<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
class productCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.4.0
	 */
	protected $faker;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $name;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameEdit;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $sku;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $price;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $retailPrice;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $ownerCompany;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $mainCategory;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $manufacture;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $feature;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $status;

	/***
	 * @var string
	 * @since 2.4.0
	 */
	protected $asService;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $decimal;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $vat;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $stockLower;

	/***
	 * @var int
	 * @since 2.4.0
	 */
	protected $stockUp;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $manufactureSKU;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $relatedSKU;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $unitOfMeasure;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $templateProduct;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $templatePrint;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $filter;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $introductionDate;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $params;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $company;

	/**
	 * @var
	 * @since 2.4.0
	 */
	protected $ameEdit;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $priceEdit;

	/**
	 * productCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->name = $this->faker->bothify('productCest Product ?##?');

		$this->sku = 'SKU' . $this->faker->numberBetween(1, 9999);

		$this->category = $this->faker->bothify('productCest Category ?##?');

		$this->price =  $this->faker->numberBetween(1, 100);

		$this->retailPrice = $this->faker->numberBetween(1, 20);

		$this->ownerCompany = null;

		$this->mainCategory = null;

		$this->manufacture = "manufacture";

		$this->feature = 'yes';

		$this->status = 'publish';

		$this->asService = 'yes';

		$this->decimal = $this->faker->numberBetween(1, 10);

		$this->vat = null;

		$this->stockLower =  $this->faker->numberBetween(1, 10);

		$this->stockUp =  $this->faker->numberBetween(1, 10);

		$this->manufactureSKU = null;

		$this->relatedSKU = null;

		$this->unitOfMeasure = null;

		$this->templateProduct = null;

		$this->templatePrint = null;

		$this->filter = null;

		$this->introductionDate = null;

		$this->params = [];

		$this->company = $this->faker->bothify('CustomerCompany ?##?');

		$this->nameEdit = $this->name."edit". $this->faker->numberBetween(1, 100);

		$this->priceEdit = $this->faker->numberBetween(100, 1000);
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 */
	public function prepare(redshopb2b $I)
	{
		$I->doFrontEndLogin();
		$I->amGoingTo('Create a vendor company to be used by the Checkout Process');
		$I->createRedshopbCompany(
			$this->company,
			$this->company,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);
		$I->amGoingTo('Create a category to be used by the Checkout Process');
		$nameOwnerCompanyCategory = "- ($this->company) $this->company";
		$I->createRedshopbCategory($this->category, $nameOwnerCompanyCategory);
		$I->doFrontendLogout();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param                  $scenario
	 *
	 * Create Product
	 *
	 * @depends prepare
	 * @throws Exception
	 */
	public function createProduct(AcceptanceTester $I, $scenario)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Product creation in Frontend');
		$I = new ProductSteps($scenario) ;
		$nameOwnerCompanyProduct = "($this->company) $this->company";
		$I->create($this->name,$this->sku, $this->category, $this->price, $this->retailPrice, $nameOwnerCompanyProduct, 'save&close');
		$I->doFrontendLogout();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param                  $scenario
	 *
	 * edit name and price this Product
	 *
	 * @depends createProduct
	 * @throws Exception
	 */
	public function editProduct(AcceptanceTester $I, $scenario)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Product edit in Frontend');
		$I = new ProductSteps($scenario);
		$I->edit($this->name, $this->nameEdit, $this->priceEdit);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param                  $scenario
	 *
	 * @depends editProduct
	 * @throws Exception
	 */
	public function delete(AcceptanceTester $I,$scenario){
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Product creation in Frontend');
		$I = new ProductSteps($scenario);
		$I->deleteProduct($this->nameEdit);
		$I->doFrontendLogout();
	}

	/**
	 * @depends delete
	 * @throws Exception
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->am('Administrator');
		$I->doFrontEndLogin();
		$I->comment('I remove the data generated by the test that is not anymore needed');
		$I->deleteRedshopbCategory($this->category);
		$I->deleteRedshopbCompany($this->company);
	}
}
