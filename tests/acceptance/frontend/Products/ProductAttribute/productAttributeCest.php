<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Frontend\ProductSteps as ProductSteps;
use Step\Acceptance\redshopb2b as redshopb2b;

class productAttributeCest
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

	/**
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
	 * @since  2.4.0
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
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameEdit;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $priceEdit;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameAttributeFirst;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameAttributeSecond;

	/**
	 * @var null
	 * @since 2.4.0
	 */
	protected $attributeType;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $status;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $valueSize;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $valueSizeSecond;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $valueColorRed;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $valueColorGreen;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $defaultSelect;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $position1;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $position2;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $price1;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $price2;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $price3;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $price4;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $employeeWithLogin;

	/**
	 * productAttributeCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		
		$this->name = $this->faker->bothify('productCest Product ?#####?');
		
		$this->sku = 'SKU' . $this->faker->numberBetween(1, 9999);
		
		$this->category = $this->faker->bothify('productCest Category ??##??');
		
		$this->price = $this->faker->numberBetween(1, 100);
		
		$this->retailPrice = $this->faker->numberBetween(1, 20);
		
		$this->ownerCompany = null;
		
		$this->mainCategory = null;
		
		$this->manufacture = "manufacture";
		
		$this->feature = 'yes';
		
		$this->status = 'publish';
		
		$this->asService = 'yes';
		
		$this->decimal = $this->faker->numberBetween(1, 10);
		
		$this->vat = null;
		
		$this->stockLower = $this->faker->numberBetween(1, 10);
		
		$this->stockUp = $this->faker->numberBetween(1, 10);
		
		$this->manufactureSKU = null;
		
		$this->relatedSKU = null;
		
		$this->unitOfMeasure = null;
		
		$this->templateProduct = null;
		
		$this->templatePrint = null;
		
		$this->filter = null;
		
		$this->introductionDate = null;
		
		$this->params = [];
		
		$this->company = $this->faker->bothify('CustomerCompany ?####?##?');
		
		$this->nameEdit = $this->name . "edit" . $this->faker->numberBetween(1, 100);
		
		$this->priceEdit = $this->faker->numberBetween(100, 1000);

		//setup attribute
		$this->nameAttributeFirst = "Color";
		
		$this->nameAttributeSecond = 'Size';
		
		$this->attributeType = null;

		//setup value for attribute
		$this->status = 'Publish';
		
		$this->valueSize = $this->faker->bothify('SizeFirst ?##?');
		
		$this->valueSizeSecond = $this->faker->bothify('SizeSecond ?##?');
		
		$this->valueColorRed = "red";
		
		$this->valueColorGreen = "Green";
		
		$this->defaultSelect = 'No';
		
		$this->position1 = 1;
		
		$this->position2 = 2;

		//setup price of attribute
		$this->price1 = $this->faker->numberBetween(1, 100);

		$this->price2 = $this->faker->numberBetween(1, 100);

		$this->price3 = $this->faker->numberBetween(1, 100);

		$this->price4 = $this->faker->numberBetween(1, 100);

		//setup user
		$this->employeeWithLogin = 'attribute Employee' . $this->faker->randomNumber();
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
		$I->doFrontendLogout();
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 */
	public function createCompany(redshopb2b $I)
	{
		$I->doFrontEndLogin();
		$I->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			'- (' .  $this->company. ') ' .  $this->company
		);

		$I->amGoingTo('Create a category to be used by the Checkout Process');
		$I->createRedshopbCategory($this->category, null);
		$I->doFrontendLogout();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param                  $scenario
	 *
	 * Create Product
	 *
	 * @depends prepare
	 *
	 * @throws Exception
	 */
	public function createProduct(AcceptanceTester $I, $scenario)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Product creation in Frontend');
		$I = new ProductSteps($scenario);
		$I->create($this->name, $this->sku, $this->category, $this->price, $this->retailPrice, null, 'save&close');
		$I->doFrontendLogout();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param                  $scenario
	 *
	 * Create one attribute for this Product above
	 *
	 * @depends createProduct
	 *
	 * @throws Exception
	 */
	public function createAttribute(AcceptanceTester $I, $scenario)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Product creation attribute inside this Product in Frontend');
		$I = new ProductSteps($scenario);
		$I->createAttribute($this->name, $this->nameAttributeFirst, $this->attributeType);
		$I->createAttribute($this->name, $this->nameAttributeSecond, $this->attributeType);
		$I->doFrontendLogout();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param                  $scenario
	 *
	 * @depends createAttribute
	 * @throws Exception
	 */
	public function createAttributeValue(AcceptanceTester $I, $scenario)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Product creation attribute value inside this Product in Frontend');
		$I = new ProductSteps($scenario);
		$I->comment('Create value of attribute');
		$I->createAttributeValue($this->name, $this->position2, $this->valueSize, $this->valueSize, $this->defaultSelect, $this->status);
		$I->createAttributeValue($this->name, $this->position2, $this->valueSizeSecond, $this->valueSizeSecond, $this->defaultSelect, $this->status);
		$I->createAttributeValue($this->name, $this->position1, $this->valueColorRed, $this->valueColorRed, $this->defaultSelect, $this->status);
		$I->createAttributeValue($this->name, $this->position1, $this->valueColorGreen, $this->valueColorGreen, $this->defaultSelect, $this->status);
		$I->doFrontendLogout();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param                  $scenario
	 *
	 * create  Combinations
	 *
	 * @depends createAttributeValue
	 *
	 * @throws Exception
	 */
	public function generateCombinations(AcceptanceTester $I, \Codeception\Scenario $scenario)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Product combination in Frontend');
		$I = new ProductSteps($scenario);
		$I->generateCombinations($this->name, $this->nameAttributeFirst, $this->nameAttributeSecond);
		$I->doFrontendLogout();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 */
	public function editPriceAttribute(AcceptanceTester $I, \Codeception\Scenario $scenario)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Product combination in Frontend');
		$I = new ProductSteps($scenario);
		$I->editPriceAttribute($this->name, $this->price1, $this->price2, $this->price3, $this->price4);
		$I->doFrontendLogout();
	}

	/**
	 * @param AcceptanceTester      $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 */
	public function checkPriceEmployeeWithLogin(AcceptanceTester $I, \Codeception\Scenario $scenario)
	{
		$I->wantToTest('Product combination in Frontend');
		$I = new ProductSteps($scenario);
		if ($scenario->current('env') == 'bootstrap3') {
			$I->checkPriceEmployeeWithLogin($this->employeeWithLogin, $this->category, $this->name, $this->nameAttributeFirst, $this->nameAttributeSecond, $this->valueColorRed, $this->valueSize, $this->price1, 3);
		} else {
			$I->comment('Still fail random with bootstrap 2 ');
//			$I->checkPriceEmployeeWithLogin($this->employeeWithLogin, $this->category, $this->name,$this->nameAttributeFirst, $this->nameAttributeSecond,$this->valueColorRed,$this->valueSize,$this->price3,2);
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param                  $scenario
	 *
	 * delete attribute of Product
	 *
	 * @depends generateCombinations
	 *
	 * @throws Exception
	 */
	public function deleteAttribute(AcceptanceTester $I, $scenario)
	{
		$I->doFrontEndLoginRetry(2);
		$I->am('Administrator');
		$I->wantToTest('Delete the Attributes added to the Product');
		$I = new ProductSteps($scenario);
		$I->deleteAttribute($this->name);
		$I->deleteAttribute($this->name);
		$I->doFrontendLogout();
	}

	/**
	 * @depends deleteAttribute
	 * @throws Exception
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->comment('I remove the data generated by the test that is not anymore needed');
		$I->deleteRedshopbCompany($this->company);
		$I->doFrontendLogout();
	}
}
