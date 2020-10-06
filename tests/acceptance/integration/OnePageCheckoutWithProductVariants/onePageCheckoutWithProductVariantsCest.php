<?php
/**
 * @package     Aesir-ec
 * @subpackage  Cest onePageCheckoutWithProductVariants
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

use \Step\Acceptance\redshopb2b as redshopb2b;
use Step\Frontend\CompanySteps as CompanySteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\UserSteps as UserSteps;
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Integration\OnePageCheckout\ProductVariants\onePageCheckoutWithProductVariants as onePageCheckoutWithProductVariantsSteps;

/**
 * Class onePageCheckoutWithProductVariantsCest
 * @since 2.8.0
 */
class onePageCheckoutWithProductVariantsCest
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
	protected $settingCartCheckout;

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
	 * @var array
	 * @since 2.8.0
	 */
	protected $company1_A;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $EmployeeWithLogin1_A;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $EmployeeWithLogin1_B;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $Main_Company;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $Main_Company_Category_Product;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $vendorOrder;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $company1B2C;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $cart;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $postcode;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $city;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $companyCountry;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $register;

	/**
	 * onePageCheckoutWithProductVariantsCest constructor.
	 * @since 2.8.0
	 */
	public function __construct()
	{
		$this->faker                = Faker\Factory::create();
		$this->product              = 'Product' . $this->faker->randomNumber();
		$this->sku                  = 'SKU' . $this->faker->randomNumber();
		$this->category             = 'Category' . $this->faker->randomNumber();
		$this->employeeWithLogin    = 'Employee' . $this->faker->randomNumber();
		$this->postcode             = $this->faker->postcode;
		$this->city                 = $this->faker->city;
		$this->companyCountry       = 'Denmark';

		//setup attribute
		$this->nameAttributeFirst   = 'Color';
		$this->nameAttributeSecond  = 'Size';
		$this->attributeType        = null;

		$this->company1B2C['name']  = $this->faker->bothify('CheckoutCest company1B2C ?##?');
		$this->company1B2C['number'] = $this->faker->bothify('company1B2C number ?##?');

		$this->company1_A   =
			[
				'name'      => $this->faker->bothify('Checkout company1_A_Name ?##?'),
				'number'    => $this->faker->bothify('Checkout company1_A_Number ?##?'),
				'address'   => $this->faker->bothify('Checkout company1_A_Name ?##?')
			];

		$this->EmployeeWithLogin1_A =
			[
				'name'              => $this->faker->bothify('EmployeeWithLogin_A ?##?'),
				'email'             => $this->faker->email,
				'phone'             => $this->faker->phoneNumber,
				'address'           => $this->faker->bothify('UserAddress ?##?'),
				'country'           => 'Aruba',
				'city'              => 'Ho Chi Minh',
				'zip'               => $this->postcode,
				'role'              => '05 :: Employee with login',
				'hasmail'           => 'Yes',
				'sendMail'          => 'No',
				'company'           => '- - (' .  $this->company1_A['number'] . ') ' . $this->company1_A['name'],
				'a_name'            => $this->faker->name(),
				'a_address'         => $this->faker->address,
				'a_second'          => $this->faker->bothify('addressSecond ?##?'),
				'a_zip'             => $this->postcode,
				'a_city'            => $this->city,
				'a_country'         => 'Denmark',
				'a_phone'           => $this->faker->phoneNumber,
				'a_cphone'          => $this->faker->phoneNumber
			];

		$this->EmployeeWithLogin1_B =
			[
				'name'              => $this->faker->bothify('EmployeeWithLogin_B ?##?'),
				'email'             => $this->faker->email,
				'phone'             => $this->faker->phoneNumber,
				'address'           => $this->faker->bothify('UserAddress ?##?'),
				'country'           => 'Aruba',
				'city'              =>'Ho Chi Minh',
				'zip'               => $this->postcode,
				'role'              => '05 :: Employee with login',
				'hasmail'           => 'Yes',
				'sendMail'          => 'No',
				'company'           => '- - (' .  $this->company1_A['number'] . ') ' . $this->company1_A['name'],
				'a_name'            => $this->faker->name(),
				'a_address'         => $this->faker->address,
				'a_second'          => $this->faker->bothify('addressSecond ?##?'),
				'a_zip'             => $this->postcode,
				'a_city'            => $this->city,
				'a_country'         => 'Denmark',
				'a_phone'           => $this->faker->phoneNumber,
				'a_cphone'          => $this->faker->phoneNumber
			];

		$this->walletEmployee   = $this->faker->numberBetween(30000,100000);

		$this->Main_Company     = 'Main Company';

		$this->Main_Company_Category_Product = 'Main Warehouse';

		//Setup cart checkout -> enable one page checkout
		$this->settingCartCheckout =
			[
				'addToCart'                     => 'Modal',
				'cartBy'                        => 'By Quantity',
				'showImageInCart'               => 'No',
				'showTaxInCart'                 => 'Yes',
				'checkoutRegister'              => 'Registration Optional',
				'guestUserDefault'              => "guest",
				'checkoutMode'                  => 'One Page',
				'showImageProductCheckout'      => 'No',
				'showStockPresent'              => 'Semaphore',
				'enableShipping'                => 'No',
				'timeShipping'                  => 'Hours',
				'clearCartBeforeAddFavourite'   => 'Yes',
				'redirectAfterAdd'              => 'No',
				'checkoutRedirect'              => 'Cart',
				'invoiceMail'                   => 'Yes'
			];

		$this->vatSetting =
			[
				'defaultCurrency'       => "Euro",
				'currencySeparator'     => ",",
				'showPrice'             => 'Yes',
				'outletProduct'         => 'No',
				'lowestProduct'         => 'No',
				'offSystem'             => 'Yes',
				'vat'                   => 'Vendor',
				'calculation'           => 'Payment',
				'useTax'                => 'No',
				'currencySymbol'        => '€'
			];

		$this->vendorOrder =
			[
				'requisition'   => 'This is demo',
				'invoice_email' => $this->faker->email
			];

		//setting shop
		$this->settingShop =
			[
				'showCategoryProduct'           => 'No',
				'ajaxCategory'                  => 'No',
				'dayProduct'                    => 16,
				'showProductPrintOption'        => 'No',
				'compareUsingOption'            => 'End customers (level3+)',
				'showShopAs'                    => 'Categories',
				'defaultLayout'                 => 'List',
				'defaultAccessory'              => 'Checkbox Input',
				'showInlineCategory'            => 'Yes',
				'showShopCollection'            => 'Yes',
			];

		//setup value for attribute
		$this->status           = 'Publish';
		$this->valueSize        = 'S';
		$this->valueSizeSecond  = 'M';
		$this->valueColorRed    = "Red";
		$this->valueColorGreen  = "Green";
		$this->defaultSelect    = 'No';
		$this->position1        = 1;
		$this->position2        = 2;

		//setup prices of attribute
		$this->price1 = $this->faker->numberBetween(1, 100);
		$this->price2 = $this->faker->numberBetween(1, 100);
		$this->price3 = $this->faker->numberBetween(1, 100);
		$this->price4 = $this->faker->numberBetween(1, 100);

		//quantity
		$this->quantity = $this->faker->numberBetween(1, 10);
		$this->cart     = 'Test save cart' . $this->faker->bothify('##??');

		$this->register     =
			[
				'guest'     => 'guest',
				'log_in'    => 'log_in',
				'sign_up'   => 'sign_up',
			];
	}

	/**
	 * @param AdministratorSteps $client
	 * @param \Codeception\Scenario               $scenario
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function prepare(AdministratorSteps $client, \Codeception\Scenario $scenario)
	{
		$client->wantToTest('Setup configuration for One Page Checkout');
		$client->doAdministratorLogin();
		$client->settingCartCheckout($this->settingCartCheckout);

		$client->doFrontEndLogin();
		$client->comment('Create Category belong Main Warehouse ');
		$client->createRedshopbCategory($this->category, $this->Main_Company_Category_Product );

		$client->comment('Create Product belong Category');
		$client = new ProductSteps($scenario);
		$client->create($this->product, $this->sku, $this->category, 20,
			20, $this->Main_Company_Category_Product, 'save&close');

		$client->comment('Create company is B2C');
		$client->createRedshopbCompany($this->company1B2C['name'], $this->company1B2C['number'], 'address', $this->postcode,
			$this->city, $this->companyCountry, $this->Main_Company, $this->company1B2C['name']);

		$client = new CompanySteps($scenario);
		$client->editB2CCompany($this->company1B2C['name'],'Yes');

		$client->comment('Create Child company is Company1_A ');
		$client = new redshopb2b($scenario);
		$client->createRedshopbCompany($this->company1_A['name'], $this->company1_A['number'], 'address', $this->postcode,
			$this->city, $this->companyCountry, '- '.$this->company1B2C['name'], $this->company1_A['name']);

		$client = new UserSteps($scenario);
		$client->createUserRole($this->EmployeeWithLogin1_A);
		$client->doFrontendLogout();

		$client->comment('Add user success and add credit for this user');
		$client->doFrontEndLogin();
		$client->addCreditToEmployeeWithLogin($this->EmployeeWithLogin1_A['name'],
			$this->vatSetting['defaultCurrency'], $this->walletEmployee);
	}

	/**
	 * @param ProductSteps $I
	 *
	 * @throws \Exception
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
		$I->wantTo('Product combination in frontend');
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
		$I->wantTo('Edit price attributes in frontend');
		$I->editPriceAttribute($this->product, $this->price1, $this->price2, $this->price3, $this->price4);
	}

	/**
	 * @param AdministratorSteps $client
	 * @param \Codeception\Scenario               $scenario
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function checkoutAsGuest(AdministratorSteps $client, \Codeception\Scenario $scenario)
	{
		$client->wantToTest('One page checkout as guest');
		$client = new onePageCheckoutWithProductVariantsSteps($scenario);
		$client->onePageCheckoutWithProductVariants($this->EmployeeWithLogin1_A, $this->category, $this->product, $this->nameAttributeFirst, $this->nameAttributeSecond,
			$this->valueColorRed, $this->valueSize, $this->price1, $this->quantity, $this->cart, $this->vatSetting, $this->EmployeeWithLogin1_A, $this->register['guest'], $this->vendorOrder, $this->walletEmployee, $scenario);
	}

	/**
	 * @param AdministratorSteps $client
	 * @param \Codeception\Scenario               $scenario
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function checkoutWithLogin(AdministratorSteps $client, \Codeception\Scenario $scenario)
	{
		$client->wantToTest('One page checkout with login');
		$client = new onePageCheckoutWithProductVariantsSteps($scenario);
		$client->onePageCheckoutWithProductVariants($this->EmployeeWithLogin1_A, $this->category, $this->product, $this->nameAttributeFirst, $this->nameAttributeSecond,
			$this->valueColorRed, $this->valueSize, $this->price1, $this->quantity, $this->cart, $this->vatSetting, $this->EmployeeWithLogin1_A, $this->register['log_in'], $this->vendorOrder, $this->walletEmployee, $scenario);
	}

	/**
	 * @param AdministratorSteps $client
	 * @param \Codeception\Scenario               $scenario
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function checkoutWithSignUp(AdministratorSteps $client, \Codeception\Scenario $scenario)
	{
		$client->wantToTest('One page checkout with sign up new account');
		$client = new onePageCheckoutWithProductVariantsSteps($scenario);
		$client->onePageCheckoutWithProductVariants($this->EmployeeWithLogin1_B, $this->category, $this->product, $this->nameAttributeFirst, $this->nameAttributeSecond,
			$this->valueColorRed, $this->valueSize, $this->price1, $this->quantity, $this->cart, $this->vatSetting, $this->EmployeeWithLogin1_B, $this->register['sign_up'], $this->vendorOrder, $this->walletEmployee, $scenario);
	}

	/**
	 * @param AdministratorSteps $client
	 * @param \Codeception\Scenario               $scenario
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function deleteAllData(AdministratorSteps $client, \Codeception\Scenario $scenario)
	{
		$client->wantToTest('Cleans the data generated by the test that is not anymore needed');
		$client->doFrontEndLogin();
		$client = new OrderSteps($scenario);
		$client->deleteAllOrder();
		$client = new redshopb2b($scenario);
		$client->deleteSaveCart($this->cart);
		$client->deleteRedshopbProduct($this->product);
		$client->deleteRedshopbCategory($this->category);
		$client->deleteRedshopbUser($this->EmployeeWithLogin1_A['name']);
		$client->deleteRedshopbCompany($this->company1_A['name']);
		$client->deleteRedshopbCompany($this->company1B2C['name']);
	}
}