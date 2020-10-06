<?php

/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
class OrderCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.4.0
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $product;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $productSecond;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $categorySecond;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $vendor;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $employeeWithLogin;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $user;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $products;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $vatSetting;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $walletEmployee;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $quantity;

	/**
	 * OrderCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->quantity = $this->faker->numberBetween(1,3);

		//information of product
		$this->product = array();

		$this->product['name'] = $this->faker->bothify('Product Order ?##?');

		$this->product['sku'] = 'SKU' . $this->faker->bothify('Order ?##?');

		$this->product['price'] = $this->faker->numberBetween(1,10);

		$this->product['quantity'] = $this->faker->numberBetween(1,4);

		$this->productSecond = array();

		$this->productSecond['name'] = $this->faker->bothify('ProductSecond ?##?');

		$this->productSecond['sku'] = 'SKU' . $this->faker->bothify('ProductSecond ?##?');

		$this->productSecond['price'] = $this->faker->numberBetween(1,10);

		$this->productSecond['quantity'] = $this->faker->numberBetween(1,4);

		$this->category =  $this->faker->bothify('Category ?##?');

		$this->categorySecond = $this->faker->bothify('CategorySecond ?##?');

		$this->vendor = $this->faker->bothify('Vendor ?##?');

		$this->company = $this->faker->bothify('CustomerCompany ?##?');

		$this->employeeWithLogin = $this->faker->bothify('Employee ?##?');

		$this->user =
			[
				'username' => $this->employeeWithLogin,
				'email' => $this->faker->email,
				'name' => $this->faker->name,
				'name2' => $this->faker->name('name 2 value ??##?'),
				'phone' => $this->faker->phoneNumber
			];

		$this->walletEmployee = $this->faker->numberBetween(30000,1000000);

		//multi product inside order
		$this->products = array();

		$this->products[] = $this->product;

		$this->products[] = $this->productSecond;

		//setting vat
		$this->vatSetting =
			[
				'defaultCurrency' => "Euro",
				'currencySymbol' => "€",
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
	 * @param AdministratorSteps $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 */
	public function prepare(AdministratorSteps $I, \Codeception\Scenario $scenario)
	{
		$I->doAdministratorLogin();
		$I->wantTo('setting VAT, just with default install');
		$I->vatSetting($this->vatSetting);
		$I->doFrontEndLogin();

		$I->amGoingTo('Create a vendor company to be used by the Checkout Process');
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
		$nameOwnerCompanyCategory = "- ($this->vendor) $this->vendor";
		$I->amGoingTo('Create a category to be used by the Checkout Process');
		$I->createRedshopbCategory($this->category, $nameOwnerCompanyCategory);
		$I->amGoingTo('Create second category for edit order');
		$I->createRedshopbCategory($this->categorySecond,$nameOwnerCompanyCategory);

		$I = new ProductSteps($scenario);
		$nameOwnerCompanyProduct = "($this->vendor) $this->vendor";
		$I->create($this->product['name'], $this->product['sku'], $this->category, $this->product['price'],
			$this->product['price'], $nameOwnerCompanyProduct, 'save&close');
		$I->amGoingTo('Create a product to be used by the Checkout Process');

		$I->amGoingTo('Create a product to be super add more product at edit order');
		$I->create($this->productSecond['name'], $this->productSecond['sku'], $this->categorySecond, $this->productSecond['price'],
			$this->productSecond['price'], $nameOwnerCompanyProduct, 'save&close');
		$I->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			"- - ($this->company) $this->company"
		);

		$I->addCreditToEmployeeWithLogin($this->employeeWithLogin, $this->vatSetting['defaultCurrency'], $this->walletEmployee);
		$I->doFrontEndLogout();
		$I->comment('Create order with employee with login');
		$I->checkout($this->user, $this->category, $this->vatSetting['currencySeparator'],
			$this->vatSetting['currencySymbol'], $this->product);
		$I->comment('product first add');
		$I->comment($this->product['name']);
		$I = new OrderSteps($scenario);
		$I->comment('Edit order with role is super uses');

		$I->comment('product second add');
		$I->comment($this->productSecond['name']);
		$I->wantTo('Change Item of order');
		$I->doFrontEndLogin();
		$I->changeOrderItem($this->employeeWithLogin, $this->categorySecond, $this->product, $this->productSecond,  $this->vatSetting);
		$I->doFrontendLogoutRetry();
	}
	
	/**
	 * @param OrderSteps $I Delete order and create other by super user
	 * @throws Exception
	 */
	public function deleteOrderFromEmployee(OrderSteps $I)
	{
		$I->doFrontEndLogin();
		$I->wantTo('Delete this order with super user admin');
		$I->deleteOrder($this->employeeWithLogin, 'Employee');

		$I->wantTo('Create new order by super role for child company');
		$I->createOrder($this->company, $this->product, $this->category, $this->vatSetting);
	}

	/**
	 * @param OrderSteps $I
	 * @throws Exception
	 */
	public function deleteOrderCompany(OrderSteps $I)
	{
		$I->doFrontEndLogin();
		$I->wantTo('Delete order by super role for company');
		$I->deleteOrder($this->company, 'Company');
	}
	/**
	 * @param OrderSteps $I Delete all data
	 * @throws Exception
	 */
	public function cleanUp(OrderSteps $I)
	{
		$I->doFrontEndLogin();
		$I->wantTo('Cleans up');
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}
