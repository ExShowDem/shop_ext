<?php
	/**
	 * @package     Aesir.E-Commerce
	 * @subpackage  Cest
	 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE.txt
	 */
use Step\Frontend\ShippingRateSteps as ShippingRateSteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
class ShippingRateCest
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
	protected $currency;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $currencySymbol;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameShipping;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $shippingRates;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $products;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $companies;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $categories;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $countries;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $shippingRate;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $shippingRateSecond;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameShippingRateEdit;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $vendor;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $product;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $employeeWithLogin;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $walletEmployWithLogin;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $debtorGroup;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $shippingMethod;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $company;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $vatSetting;

	/**
	 * ShippingRateCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		
		$this->currency = 'US Dollar';
		
		$this->currencySymbol = '$';
		
		$this->nameShipping = $this->faker->bothify('shippingName ?###?');
		
		$this->shippingRates = array();
	 
		$this->products = array();
		
		$this->companies = array();
		
		$this->categories = array();
		
		$this->countries = array();
		
		$this->countries[] = 'Denmark';

		$this->countries[] = 'Vietnam';
		
		//shipping rate first
		$this->shippingRate = array();
		
		$this->shippingRate['name'] = $this->nameShipping;
		
		$this->shippingRate['priority'] = 0;
		
		$this-> shippingRate['price'] = 10;
		
		$this->shippingRate['status'] = 'Publish';
		
		//shipping rate second
		$this->shippingRateSecond = array();
		
		$this->shippingRateSecond['name'] = $this->faker->bothify('ShRateSecond ?###?');
		
		$this->shippingRateSecond['priority'] = 0;
		
		$this-> shippingRateSecond['price'] = 2;
		
		$this->shippingRateSecond['status'] = 'Publish';

		$this->nameShippingRateEdit = $this->faker->bothify('RateEdit ?###?');
		
		$this->vendor = $this->faker->bothify('Customer Company ?###?');

		//product
		$this->product = array();
		
		
		$this->product['name'] = $this->faker->bothify('Product ?###?');
		
		$this->product['sku'] = $this->faker->bothify('SKU ?###?');
		
		$this->product['price'] = 4;


		//category
		$this->category = $this->faker->bothify('Category ?###?');

		//employee
		$this->employeeWithLogin = $this->faker->bothify('Employee ?###?');
		
		$this->walletEmployWithLogin = $this->faker->numberBetween(30000,100000);


		//debtor group
		$this->debtorGroup = array();

		$this->debtorGroup['name'] = $this->faker->bothify('Group ?###?');

		$this->debtorGroup['code'] = $this->faker->bothify('PShipping Debtor ?###?');

		//shipping method
		 $this->shippingMethod = array();
		 
		$this->shippingMethod['title'] = $this->faker->bothify('Shipment ?###?');

		//company
		$this->company = array();
		$this->company = $this->faker->bothify('PShipping Company ?###?');

		//setting vat
		$this->vatSetting =
			[
				'defaultCurrency' => "US Dollar",
				'currencySymbol' => "$",
				'currencySeparator' => ",",
				'showPrice' => 'Yes',
				'outletProduct' => 'No',
				'lowestProduct' => 'No',
				'offSystem' => 'Yes',
				'vat' => 'Vendor',
				'calculation' => 'Payment',
				'useTax' => 'No'
			];
	}


	/**
	 * Creates the following elements that will be needed for the test:
	 *
	 * - "Vendor": company that sells the product
	 * - "Product" to be selled by the "Vendor"
	 * - "Company": the customer B2B company that purchases product belong Denmak country
	 * - Debtor Group
	 * - A Shipping Method
	 * - A Shipping Rate for the shipping Method assign for Denmark country and products and categoris and shipping rates
	 * - Check value wil multi shipping rate
	 * - Employee With login
	 * - Shipping Method
	 * @throws Exception
	 */
	public function prepare(ShippingRateSteps $I, \Codeception\Scenario $scenario)
	{
		$I->wantTo('setting VAT, just with default install');
		$I->doAdministratorLogin();
		$I = new AdministratorSteps($scenario);
		$I->vatSetting($this->vatSetting);
		$I->doFrontEndLogin();

		$this->faker = Faker\Factory::create();

		$I->amGoingTo('Create a VENDOR company to be used by the Checkout Process');

		$I->createRedshopbCompany(
			$this->vendor,
			$this->vendor,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company',
			[
				'Company Currency' => $this->currency
			]
		);

		$this->companies[] = $this->vendor;
		$I->amGoingTo('Create a CATEGORY to be used by the Checkout Process');
		$I->createRedshopbCategory(
			$this->category,
			"- ($this->vendor) $this->vendor"
		);

		$this->categories[]= $this->category;
		$I->amGoingTo('Create a PRODUCT to be used by the Checkout Process');

		$I = new ProductSteps($scenario);
		$I->create($this->product['name'], $this->product['sku'], $this->category, $this->product['price'], $this->product['price'], "($this->vendor) $this->vendor", 'save&close');
		$this->products[] = $this->product['name'];

		$I->amGoingTo('Create a customer COMPANY to be used by the Checkout Process');

		$I->createRedshopbCompany(
			$this->company,
			$this->company,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			"- $this->vendor",
			[
				'Company Currency' => $this->currency
			]
		);

		$I->amGoingTo('Create a DEBTOR GROUP which will Hold our Company');

		$I->createRedshopbDebtorGroup(
			$this->debtorGroup['name'],
			$this->debtorGroup['code'],
			'(main) Main Company',
			'- - ('.$this->company.') ' . $this->company
		);

		$I->amGoingTo('Create a SHIPPING METHOD for this Debtor Group');

		$I->createRedshopbShippingMethod(
			"Default shipping",
			$this->debtorGroup['name'],
			$this->shippingMethod['title']
		);

		$I->amGoingTo('Create a SHIPPING RATE for the Shipping method');
		$I->doFrontendLogout();
	}

	/**
	 * @param ShippingRateSteps $I
	 * @throws Exception
	 */
	public function createShippingRate(ShippingRateSteps $I)
	{
		$I->doFrontEndLogin();
		$this->shippingMethod['title'] = $this->debtorGroup['name']. "- ". $this->shippingMethod['title'];
		$I->createShippingRate(
			$this->shippingMethod['title'],
			$this->countries,
			$this->categories,
			$this->products,
			$this->shippingRateSecond
		);

		$I->doFrontendLogout();
	}

	/**
	 * @param ShippingRateSteps $client
	 * @throws Exception
	 */
	public function checkoutShipping(ShippingRateSteps $client)
	{
		$this->shippingRates[] = $this->shippingRateSecond;
		$client->doFrontEndLogin();
		$client->wantTo('Create new user for checkout');
		$client->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			"- - ($this->company) $this->company"
		);

		$client->addCreditToEmployeeWithLogin($this->employeeWithLogin, $this->currency, $this->walletEmployWithLogin);
		$client->doFrontEndLogout();
		$client->wantTo('Checkout with user');
		$client->checkoutWithShippingRate($this->employeeWithLogin,  $this->category, $this->currencySymbol, $this->shippingRates, $this->product['price']);
	}

	/**
	 * @param ShippingRateSteps $client
	 * @throws Exception
	 */
	public function createEdit(ShippingRateSteps $client)
	{
		$client->doFrontEndLogin();
		$client->wantToTest('Crate the shipping rate');
		$client->createShippingRate(
			$this->shippingMethod['title'],
			$this->countries,
			$this->categories,
			$this->products,
			$this->shippingRate
		);

		$client->wantToTest('Edit the shipping rate');
		$client->createShippingEdit($this->shippingRate['name'], $this->nameShippingRateEdit);
		$client->amGoingTo('Create a EMPLOYEE WITH LOGIN');

		$this->shippingRate['name'] = $this->nameShippingRateEdit;

		$this->shippingRates[] = $this->shippingRate;

		$client->createWrongValue( $this->shippingMethod['title'],  $this->shippingRate['price']);
	}
	/**
	 * @param ShippingRateSteps $I
	 * @throws Exception
	 */
	public function cleanUp(ShippingRateSteps $I)
	{
		$I->doFrontEndLogin();
		$I->deleteRedshopbProduct($this->product['name']);
		$I->deleteRedshopbCategory($this->category);
		$I->deleteRedshopbUser($this->employeeWithLogin);
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
		$I->deleteRedshopbShippingRate($this->shippingRate['name']);
		$I->deleteRedshopbShippingRate($this->shippingRateSecond['name']);
		$I->deleteRedshopbDebtorGroup($this->debtorGroup['name']);
	}
}