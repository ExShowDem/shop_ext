<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Frontend\OfferSteps as OfferSteps;
use Step\Frontend\DepartmentSteps as DepartmentSteps;
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
use Step\Frontend\CollectionSteps as CollectionSteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps ;
class offerCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.3.0
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.3.0
	 */
	protected $user;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $name;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $nameEdit;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $vendor;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $deparment;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $currency;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $status;

	/**
	 * @var array
	 * @since 2.3.0
	 */
	protected $product0;

	/**
	 * @var array
	 * @since 2.3.0
	 */
	protected $product1;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $category;

	/**
	 * @var array
	 * @since 2.3.0
	 */
	protected $offer;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $customerNumber;

	/**
	 * @var array
	 * @since 2.3.0
	 */
	protected $department;

	/**
	 * @var array
	 * @since 2.3.0
	 */
	protected $offerDepartment;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $employeeWithLogin;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $walleteEmployee;

	/**
	 * @var array
	 * @since 2.3.0
	 */
	protected $offerEmployeee;

	/**
	 * @var array
	 * @since 2.3.0
	 */
	protected $vatSetting;

	public function __construct()
	{
		//add this class do not support collection
		$this->faker = Faker\Factory::create();

		$this->offer = array();

		$this->name = $this->faker->bothify('offerCest ??###?');

		$this->nameEdit = $this->name.'Edit'.$this->faker->bothify('edit Name ??###?');

		$this->company = $this->faker->bothify('offerCest Company ??###?');

		$this->vendor = $this->faker->bothify('offerCest Vendor ??###?');

		$this->deparment = $this->faker->bothify('collectionCest Department ??###?');

		$this->currency = 'Euro';

		$this->status ='Unpublished';

		// product and category info
		// first product
		$this->product0 =
			[
				'name' => $this->faker->bothify('offer ProductFirst ??###?'),
				'sku' => $this->faker->bothify('p1 SKU ?###?'),
				'price' => $this->faker->numberBetween(1,100)
			];

		// second product
		$this->product1 =
			[
				'name' => $this->faker->bothify('offer ProductSecond ??###?'),
				'sku' => $this->faker->bothify('p1 SKU ?###?'),
				'price' => $this->faker->numberBetween(1,100)
			];
		// info category
		$this->category =  $this->faker->bothify('offerCest Category ??###?');

		// add all value for offer
		$this->offer =
			[
				'name' => $this->name,
				'expiration'  => 'No',
				'type' => 'Company',
				'customer' => $this->company
			];

		$this->customerNumber = $this->faker->bothify('Customer number ??###?');

		//add value inside department array
		$this->department =
			[
				'number' => $this->customerNumber,
				'name' => $this->name,
				'nameSecond' => $this->faker->bothify('department Second ??###?'),
				'company' => ' - - (' . $this->company. ') ' .$this->company,
				'address' => $this->faker->address,
				'addressSecond' => $this->faker->address,
				'zip' => $this->faker->numberBetween(1,1000),
				'city' => $this->faker->city,
				'country' => 'Denmark',
				'status' => 'Publish'
			];

		// Create order for department
		$this->offerDepartment =
			[
				'name' => $this->faker->bothify('Offer Full For Department ??###?'),
				'status' => 'Sent',
				'type' => 'Department',
				'customer' => $this->department['name']. " ($this->company)"
			];

		//create user belong company . and this user will get product
		$this->employeeWithLogin = $this->faker->bothify('offer Employee ??###?');

		$this->walleteEmployee = $this->faker->numberBetween(1000,5000);

		// Create offer for employee and create order
		$this->offerEmployeee =
			[
				'name' => $this->faker->bothify('Offer Full Employee'),
				'status' => 'Sent',
				'type' => 'Employee',
				'customer' => $this->employeeWithLogin
			];

		$this->user =
			[
				'username' => $this->employeeWithLogin,
				'email' => "nhung@redweb.dk",
				'name' => $this->faker->name,
				'name2' => $this->faker->name('name 2 value ??##?'),
				'phone' => $this->faker->phoneNumber
			];
		//setting vat
		$this->vatSetting =
			[
				'defaultCurrency' => "Euro",
				'currencySymbol'=> '€',
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
	 * @param OfferSteps $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 */
	public function createFirstCompany(OfferSteps $I, \Codeception\Scenario $scenario)
	{
		$I->amGoingTo('Create a Company to be used by the Offer');
		$I->amGoingTo('Create a customer company to be used by the Checkout Process');

		$I->doFrontEndLogin();

		$I->createRedshopbCompany(
			$this->vendor,
			$this->vendor,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);

		$I->amGoingTo('Create a customer company to be used by the Checkout Process');
		$I->createRedshopbCompany(
			$this->company,
			$this->company,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			"- $this->vendor"
		);

		$I->am('Administrator');
		$I->wantTo('Create Department for create offer');
		$I = new DepartmentSteps($scenario);
		$I->comment($this->department['company']);
		$I->createDepartment($this->department);

		$I->wantTo('Create category for offer');
		$I = new CategorySteps($scenario) ;
		$nameOwnerCompanyCategory = "- ($this->vendor) $this->vendor";
		$nameOwnerCompanyProduct = "($this->vendor) $this->vendor";
		$I->createRedshopbCategory($this->category,$nameOwnerCompanyCategory);

		$I->wantTo('Create new product 1 belong category above');

		$I = new ProductSteps($scenario);

		$I->create($this->product0['name'], $this->product0['sku'], $this->category,  $this->product0['price'],$this->product0['price'],$nameOwnerCompanyProduct,'save&close');

		$I->wantTo('Create new product 2 belong category above');
		$I->create($this->product1['name'], $this->product1['sku'], $this->category,  $this->product1['price'],$this->product1['price'],$nameOwnerCompanyProduct,'save&close');

		$I->wantTo('Create new employee with login for create offer');
		$I->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			"- - ($this->company) $this->company"
		);

		$I->doFrontendLogout();
	}

	/**
	 * @param OfferSteps $I
	 *
	 * Create offer for employee
	 * @throws Exception
	 */
	public function offerForEmployee(OfferSteps $I)
	{
		$I->doFrontEndLogin();
		$I->wantTo('Create offer for employee ');
		$I->createFullPotions($this->offerEmployeee);
		$I->offerWithProduct($this->offerEmployeee['name'], $this->product0, $this->product1);
		$I->addCreditToEmployeeWithLogin($this->employeeWithLogin, $this->vatSetting['defaultCurrency'], $this->walleteEmployee);
		$I->doFrontendLogout();
	}

	/**
	 * @param OfferSteps $I
	 * @param \Codeception\Scenario $scenario
	 *
	 * User login and approved offer
	 * @throws Exception
	 */
	public function checkoutWithEmployee(OfferSteps $I)
	{
		$I->comment('Check with employee login');
		$I->checkoutForOffer($this->user, $this->offerEmployeee, $this->product0, $this->product1, $this->vatSetting['currencySeparator'], $this->vatSetting['currencySymbol']);
		$I->doFrontendLogout();
	}

	/**
	 * @param OrderSteps $I
	 * @param \Codeception\Scenario $scenario
	 *
	 * Delete Order
	 * @throws Exception
	 */
	public function changeStatusOfOrderAndDelete(OrderSteps $I)
	{
		$I->doFrontEndLogin();
		$I->changeOrderStatusToConfirmed($this->employeeWithLogin, 'Employee');
		$I->deleteOrder($this->employeeWithLogin, 'Employee');
	}

	/**
	 * @param OfferSteps $I
	 *
	 * Create Offer for Department
	 * @throws Exception
	 */
	public function createOfferForDepartment(OfferSteps $I)
	{
		$I->doFrontEndLogin();
		$I->wantToTest('Create offer for Department');
		$I->createFullPotions($this->offerDepartment);
		$I->deleteOffer($this->offerDepartment['name']);
	}

	/**
	 * @param OfferSteps $I
	 * @throws Exception
	 * Create offer for product with products
	 *
	 */
	public function createOfferForCompany(OfferSteps $I)
	{
		$I->doFrontEndLogin();
		$I->wantTo('Create offer for company');
		$nameCompany = "- - ($this->company) $this->company";
		$I->createOffer($this->name, $nameCompany);
		$I->offerWithProduct($this->name, $this->product0, $this->product1);
		$I->doFrontendLogout();
	}
	/**
	 * @depends createOfferForCompany
	 *
	 * Edit name of offer
	 * @throws Exception
	 */
	public function edit(OfferSteps $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Offer edit in Frontend');
		$I->editOffer($this->name, $this->nameEdit);
		$I->doFrontendLogout();
	}

	/**
	 * @depends edit
	 *
	 * Delete Offer
	 * @throws Exception
	 *
	 */
	public function delete(OfferSteps $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Delete an offer in Frontend');
		$I->deleteOffer($this->nameEdit);
		$I->doFrontendLogout();
	}

	/**
	 * @param \Step\Acceptance\redshopb2b $I
	 *
	 * Clear all data
	 * @throws Exception
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->comment('I remove the data generated by the test that is not anymore needed');
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}