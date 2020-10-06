<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use \Step\Acceptance\redshopb2b as redshopb2b;
use Step\Frontend\CompanySteps as CompanySteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\CheckoutSteps as CheckoutSteps;
use Step\Frontend\UserSteps as UserSteps;
use Step\Frontend\OrderSteps as OrderSteps;
use Page\Frontend\OrderPage as OrderPage;
use Step\Frontend\ProductSteps as ProductSteps;

/**
 * Class onePageCheckoutCest
 * @since 2.4.1
 */
class onePageCheckoutCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.4.1
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	public $product0;

	/**
	 * @var
	 * @since 2.4.1
	 */
	public $category0;

	/**
	 * @var
	 * @since 2.4.1
	 */
	public $company1B2C;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	public $administrator_1B2C;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	public $company1_A;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	public $EmployeeWithLogin1_A;

	/**
	 * @var int
	 * @since 2.4.1
	 */
	protected $walletEmployee;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $Main_Company;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $Main_Company_Category_Product;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $settingCartCheckout;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $vatSetting;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $vendorOrder;

	/**
	 * onePageCheckoutCest constructor.
	 * @since 2.4.1
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		/**
		 * setup product and category
		 */
		$this->product0 = array();
		
		$this->product0['name'] = $this->faker->bothify('OPCheckout Product0');
		
		$this->product0['sku'] = 'SKU' .$this->faker->randomNumber();
		
		$this->product0['price'] = $this->faker->numberBetween(1,10);
		
		$this->category0['name'] =  $this->faker->bothify('Category0 CategoryWarehouse ?##?');
		
		$this->product0['category'] = $this->category0['name'];
		
		$this->company1B2C['name'] = $this->faker->bothify('CheckoutCest company1B2C ?##?');
		
		$this->company1B2C['number'] = $this->faker->bothify('company1B2C number ?##?');
		
		$this->administrator_1B2C =
			[
				'name' => $this->faker->bothify('administrator_1B2C ?##?'),
				'hasmail' => 'Yes',
				'company' => '- (' .  $this->company1B2C['number'] . ') ' . $this->company1B2C['name'],
				'email' => $this->faker->email,
				'sendMail' => 'No',
				'role' => '01 :: Administrator',
				'a_name' => $this->faker->name(),
				'a_address' => $this->faker->address,
				'a_second' => $this->faker->bothify('addressSecond ?##?'),
				'a_zip' => $this->faker->postcode,
				'a_city' => $this->faker->city,
				'a_country' => 'Denmark',
				'a_phone' => $this->faker->phoneNumber,
				'a_cphone' => $this->faker->phoneNumber
			];
		
		$this->company1_A =
			[
				'name' => $this->faker->bothify('Checkout company1_A_Name ?##?'),
				 'number' => $this->faker->bothify('Checkout company1_A_Number ?##?'),
				 'address' => $this->faker->bothify('Checkout company1_A_Name ?##?')
			];
		
		$this->EmployeeWithLogin1_A =
			[
				'name' => $this->faker->bothify('Checkout EmployeeWithLogin1_A ?##?'),
				'email' => $this->faker->email,
				'phone' => $this->faker->phoneNumber,
				'address' => $this->faker->bothify('UserAddress ?##?'),
				'country' => 'Aruba',
				'city' =>'Ho Chi Minh',
				'zip' => $this->faker->postcode,
				'role' => '05 :: Employee with login',
				'hasmail' => 'Yes',
				'sendMail' => 'No',
				'company' => '- - (' .  $this->company1_A['number'] . ') ' . $this->company1_A['name'],
				'a_name' => $this->faker->name(),
				'a_address' => $this->faker->address,
				'a_second' => $this->faker->bothify('addressSecond ?##?'),
				'a_zip' => $this->faker->postcode,
				'a_city' => $this->faker->city,
				'a_country' => 'Denmark',
				'a_phone' => $this->faker->phoneNumber,
				'a_cphone' => $this->faker->phoneNumber
			 ];
		
		$this->walletEmployee = $this->faker->numberBetween(30000,100000);
		
		$this->Main_Company = 'Main Company';
		
		$this->Main_Company_Category_Product = '(main) Main Company';

		/**
		 * Setup cart checkout -> enable one page checkout
		 */
		$this->settingCartCheckout =
			[
				'addToCart' => 'Modal',
				'cartBy' => 'By Quantity',
				'showImageInCart' => 'No',
				'showTaxInCart' => 'Yes',
				'checkoutRegister' => 'Registration Optional',
				'guestUserDefault' => "guest",
				'checkoutMode' => 'Default',
				'showImageProductCheckout' => 'No',
				'showStockPresent' => 'Semaphore',
				'enableShipping' => 'No',
				'timeShipping' => 'Hours',
				'clearCartBeforeAddFavourite' => 'Yes',
				'redirectAfterAdd' => 'No',
				'checkoutRedirect' => 'Cart',
				'invoiceMail' => 'Yes'
			];

		/**
		 * Setup vat for system
		 */
		$this->vatSetting =
			[
				'defaultCurrency' => "Euro",
				'currencySeparator' => ",",
				'showPrice' => 'Yes',
				'outletProduct' => 'No',
				'lowestProduct' => 'No',
				'offSystem' => 'Yes',
				'vat' => 'Vendor',
				'calculation' => 'Payment',
				'useTax' => 'No',
				'currencySymbol' => '€'
			];
		
		$this->vendorOrder =
			[
				'requisition' => 'This is demo',
				'invoice_email' => $this->faker->email
			];
	}
	
	/**
	 * @param AdministratorSteps $client
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function prepare(AdministratorSteps $client, \Codeception\Scenario $scenario)
	{
		$client->wantToTest('Setup configuration for One Page Checkout');
		$client->doAdministratorLogin();
		$client->settingCartCheckout($this->settingCartCheckout);

		$client->doFrontEndLogin();
		$client->comment('Create Category0 belong Main Warehouse ');
		$client->createRedshopbCategory($this->category0['name'], $this->Main_Company_Category_Product );

		$client->comment('Create Product0 belong Category0');
		$client = new ProductSteps($scenario);
		$client->create($this->product0['name'],$this->product0['sku'], $this->category0['name'], $this->product0['price'], $this->product0['price'], $this->Main_Company_Category_Product,'save&close');

		$client->comment('Create company is B2C');
		$client->createRedshopbCompany($this->company1B2C['name'], $this->company1B2C['number'], 'address', $this->faker->postcode,
			$this->faker->city, "Denmark", $this->Main_Company, $this->company1B2C['name']);

		$client = new CompanySteps($scenario);
		$client->editB2CCompany($this->company1B2C['name'],'Yes');

		$client->comment('Create Child company is Company1_A ');
		$client = new redshopb2b($scenario);
		$client->createRedshopbCompany($this->company1_A['name'], $this->company1_A['number'], 'address', $this->faker->postcode,
			$this->faker->city, "Denmark", '- '.$this->company1B2C['name'], $this->company1_A['name']);

		$client = new UserSteps($scenario);
		$client->createUserRole($this->administrator_1B2C);
		$client->createUserRole($this->EmployeeWithLogin1_A);
		$client->doFrontendLogout();

		$client->comment('Add user success and add credit for this user');
		$client->doFrontEndLogin();
		$client->addCreditToEmployeeWithLogin($this->EmployeeWithLogin1_A['name'],
			$this->vatSetting['defaultCurrency'], $this->walletEmployee);
	}

	/**
	 * @param AdministratorSteps $client
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function checkoutAsGuest(AdministratorSteps $client, \Codeception\Scenario $scenario)
	{
		$client->wantToTest('Create order with guest checkout');
		$client = new CheckoutSteps($scenario);
		$client->deliverySteps($this->EmployeeWithLogin1_A, $this->vatSetting, $this->product0,
			$this->EmployeeWithLogin1_A, 'guest', $this->vendorOrder);
		$client->doFrontEndLogin();
		$client = new OrderSteps($scenario);
		$client->changeOrderStatusToConfirmed($this->company1B2C['name'], 'Company');
	}

	/**
	 * @param redshopb2b $client
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function checkoutWithLogin(redshopb2b $client, \Codeception\Scenario $scenario)
	{
		$client->wantToTest('Create order with with login');
		$client = new CheckoutSteps($scenario);
		$client->deliverySteps($this->EmployeeWithLogin1_A, $this->vatSetting, $this->product0,
			$this->EmployeeWithLogin1_A, 'log_in', $this->vendorOrder);
		$client->confirmStepsForEmployee($this->EmployeeWithLogin1_A, $this->company1_A, $this->vatSetting, $this->product0);
	}
	
	/**
	 * @param redshopb2b $client
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function OrderIsCreatedAfterCheckout(redshopb2b $client, \Codeception\Scenario $scenario)
	{
		$client->wantToTest('Changing the status of an Order in Frontend');
		$client->doFrontEndLogin($this->administrator_1B2C['name'], $this->administrator_1B2C['name']);
		$client->amOnPage(OrderPage::$Url);
		$client = new OrderSteps($scenario);
		$client->searchOrder($this->EmployeeWithLogin1_A['name']);
		$client->see( $this->company1_A['name']);
		$client->see($this->EmployeeWithLogin1_A['name']);
	}
	
	/**
	 * @param AdministratorSteps $client
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function deleteAllData(AdministratorSteps $client, \Codeception\Scenario $scenario)
	{
		$client->wantToTest('Cleans up');
		$client->doFrontEndLogin();
		$client = new OrderSteps($scenario);
		$client->deleteAllOrder();
		$client = new redshopb2b($scenario);
		$client->deleteRedshopbProduct($this->product0['name']);
		$client->deleteRedshopbCategory($this->category0['name']);
		$client->deleteRedshopbUser($this->administrator_1B2C['name']);
		$client->deleteRedshopbUser($this->EmployeeWithLogin1_A['name']);
		$client->deleteRedshopbCompany($this->company1_A['name']);
		$client->deleteRedshopbCompany($this->company1B2C['name']);
	}
}