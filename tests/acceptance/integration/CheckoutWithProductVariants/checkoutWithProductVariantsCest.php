<?php
/**
 * @package     Aesir-ec
 * @subpackage  Cest saveCartWithProductVariant
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

use Step\Frontend\ProductSteps as ProductSteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Integration\CheckoutWithProductVariants\CheckoutWithProductVariants as CheckoutWithProductVariants;

/**
 * Class checkoutWithProductVariantsCest
 * @since 2.8.0
 */
class checkoutWithProductVariantsCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.8.0
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $product;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $sku;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $category;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $vendor;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $walletEmployee;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $vatSetting;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $settingShop;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $employeeWithLogin;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $user;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $nameAttributeFirst;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $nameAttributeSecond;

	/**
	 * @var null
	 * @since 2.8.0
	 */
	protected $attributeType;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $status;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $valueSize;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $valueSizeSecond;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $valueColorRed;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $valueColorGreen;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $defaultSelect;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $position1;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $position2;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $price1;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $price2;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $price3;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $price4;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $quantity;

	/**
	 * checkoutWithProductVariantsCest constructor.
	 * @since 2.8.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		$this->product = 'Product' . $this->faker->randomNumber();
		$this->sku = 'SKU' . $this->faker->randomNumber();
		$this->category = 'Category' . $this->faker->randomNumber();
		$this->company = 'CustomerCompany' . $this->faker->randomNumber();
		$this->vendor = 'VendorCompany' . $this->faker->randomNumber();
		$this->employeeWithLogin = 'Employee' . $this->faker->randomNumber();

		//setup attribute
		$this->nameAttributeFirst = 'Color';
		$this->nameAttributeSecond = 'Size';
		$this->attributeType = null;

		//setting vat
		$this->vatSetting =
			[
				'defaultCurrency'       => "Euro",
				'currencySymbol'        => '€',
				'currencySeparator'     => ",",
				'showPrice'             => 'Yes',
				'outletProduct'         => 'No',
				'lowestProduct'         => 'No',
				'offSystem'             => 'Yes',
				'vat'                   => 'Vendor',
				'calculation'           => 'Payment',
				'useTax'                => 'No'
			];

		//setting shop
		$this->settingShop =
			[
				'showCategoryProduct' => 'No',
				'ajaxCategory' => 'No',
				'dayProduct' => 16,
				'showProductPrintOption' => 'No',
				'compareUsingOption' => 'End customers (level3+)',
				'showShopAs' => 'Categories',
				'defaultLayout' => 'List',
				'defaultAccessory' => 'Checkbox Input',
				'showInlineCategory' => 'Yes',
				'showShopCollection' => 'Yes',
			];

		//setup value for attribute
		$this->status = 'Publish';
		$this->valueSize = 'S';
		$this->valueSizeSecond = 'M';
		$this->valueColorRed = "Red";
		$this->valueColorGreen = "Green";
		$this->defaultSelect = 'No';
		$this->position1 = 1;
		$this->position2 = 2;

		//setup prices of attribute
		$this->price1 = $this->faker->numberBetween(1, 100);
		$this->price2 = $this->faker->numberBetween(1, 100);
		$this->price3 = $this->faker->numberBetween(1, 100);
		$this->price4 = $this->faker->numberBetween(1, 100);

		//quantity
		$this->quantity = $this->faker->numberBetween(1, 10);
	}

	/**
	 * @param redshopb2b $I
	 * @param \Codeception\Scenario       $scenario
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I, \Codeception\Scenario $scenario)
	{
		$I->doAdministratorLogin();
		$I = new AdministratorSteps($scenario);
		$I->amGoingTo('Setting VAT');
		$I->vatSetting($this->vatSetting);
		$I->amGoingTo('Setting Shop');
		$I->settingShop($this->settingShop);

		$I->doFrontEndLogin();
		$I->amGoingTo('Create Vendor company for checking');
		$I->createRedshopbCompany(
			$this->vendor,
			$this->vendor,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);

		$I->amGoingTo('Create Customer company for checking');
		$I->createRedshopbCompany(
			$this->company,
			$this->company,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			"- $this->vendor"
		);

		$I->amGoingTo('Create category to be used by the Checkout Process');
		$I->createRedshopbCategory($this->category, "- ($this->vendor) $this->vendor");

		$I->amGoingTo('Create product to be used by the Checkout Process');
		$I = new ProductSteps($scenario);
		$I->create($this->product, $this->sku, $this->category, 20,
			20, "($this->vendor) $this->vendor", 'save&close');

		$I->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			"- - ($this->company) $this->company"
		);

		$I->addCreditToEmployeeWithLogin($this->employeeWithLogin, 'Euro', 10000);
	}

	/**
	 * @param ProductSteps $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function createAttribute(ProductSteps $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantTo('Product creation attributes inside this Product in Frontend');
		$I->createAttribute($this->product, $this->nameAttributeFirst, $this->attributeType);
		$I->createAttribute($this->product, $this->nameAttributeSecond, $this->attributeType);
	}

	/**
	 * @param ProductSteps $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function createAttributeValue(ProductSteps  $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantTo('Product creation attributes value inside this Product in Frontend');
		$I->createAttributeValue($this->product, $this->position2, $this->valueSize, $this->valueSize, $this->defaultSelect, $this->status);
		$I->createAttributeValue($this->product, $this->position2, $this->valueSizeSecond, $this->valueSizeSecond, $this->defaultSelect, $this->status);
		$I->createAttributeValue($this->product, $this->position1, $this->valueColorRed, $this->valueColorRed, $this->defaultSelect, $this->status);
		$I->createAttributeValue($this->product, $this->position1, $this->valueColorGreen, $this->valueColorGreen, $this->defaultSelect, $this->status);
	}

	/**
	 * @param ProductSteps $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function generateCombinations(ProductSteps $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantTo('Product combination in Frontend');
		$I->generateCombinations($this->product, $this->nameAttributeFirst, $this->nameAttributeSecond);
	}

	/**
	 * @param ProductSteps $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function editPriceAttribute(ProductSteps $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantTo('Edit price attributes in Frontend');
		$I->editPriceAttribute($this->product, $this->price1, $this->price2, $this->price3, $this->price4);
	}

	/**
	 * @param \Step\Integration\CheckoutWithProductVariants\CheckoutWithProductVariants $I
	 * @param \Codeception\Scenario                                                     $scenario
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function checkoutWithProductVariants(CheckoutWithProductVariants $I, \Codeception\Scenario $scenario)
	{
		$I->wantTo('Test checkout with product variants on category page');
		$I = new CheckoutWithProductVariants($scenario);
		$I->CheckoutWithProductVariants($this->employeeWithLogin, $this->category, $this->product, $this->nameAttributeFirst, $this->nameAttributeSecond, $this->valueColorRed, $this->valueSize, $this->price1, $this->vatSetting['currencySeparator'], $this->vatSetting['currencySymbol'], $this->quantity);
	}

	/**
	 * @param \Step\Frontend\OrderSteps $I
	 * @param                           $scenario
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function cleanUp(OrderSteps $I, $scenario)
	{
		$I->am('Administrator');
		$I->doFrontEndLogin();
		$I->wantTo('Clear the data generated by the test that is not anymore needed');
		$I = new OrderSteps($scenario);
		$I->deleteAllOrder();
		$I = new redshopb2b($scenario);
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
		$I->doFrontendLogout();
	}
}