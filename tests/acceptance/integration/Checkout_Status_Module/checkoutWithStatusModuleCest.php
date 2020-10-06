<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir . E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Page\Frontend\OrderPage as OrderPage;
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Frontend\UserSteps as UserSteps;
use Step\Acceptance\redshopb2b as redshopb2b;

class checkoutWithStatusModuleCest
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
	protected $products;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category;

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
	protected $employeeWithLoginName;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $walletEmployee;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $childCompanyCategory;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $EmployeeWithLogin;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $user;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $vatSetting;

	/**
	 * checkoutWithStatusModuleCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{	$this->faker = Faker\Factory::create();

		//information of product
		$this->products = array();
		$this->products['name'] = $this->faker->bothify('Product ?###?');
		$this->products['sku'] = 'SKU' . $this->faker->randomNumber();
		$this->products['price'] = '100';
		$this->category =  $this->faker->bothify('Category ?###?');
		$this->vendor = $this->faker->bothify('Vendor ?###?');
		$this->company = $this->faker->bothify('Company ?###?');
		$this->employeeWithLoginName = $this->faker->bothify('Employee ?###?');
		$this->walletEmployee = $this->faker->numberBetween(30000,100000);
		$this->childCompanyCategory = "- - ($this->company) $this->company";

		$this->EmployeeWithLogin =
			[
				'name' => $this->employeeWithLoginName,
				'phone' => $this->faker->phoneNumber,
				'address' => $this->faker->bothify('UserAddress ?##?'),
				'country' => 'Aruba',
				'city' =>'Ho Chi Minh',
				'zip' => $this->faker->postcode,
				'role' => '05 :: Employee with login',
				'hasmail' => 'Yes',
				'email' => $this->faker->email,
				'sendMail' => 'Yes',
				'company' => $this->childCompanyCategory,
				'a_name' => $this->faker->name(),
				'a_address' => $this->faker->address,
				'a_second' => $this->faker->bothify('addressSecond ?##?'),
				'a_zip' => $this->faker->postcode,
				'a_city' => $this->faker->city,
				'a_country' => 'Vietnam',
				'a_phone' => $this->faker->phoneNumber,
				'a_cphone' => $this->faker->phoneNumber
			];

		$this->user =
			[
				'username' => $this->employeeWithLoginName,
				'email' => $this->faker->email,
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
	 * @param AdministratorSteps $client
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function prepare(OrderSteps $client, \Codeception\Scenario $scenario)
	{
		$client->doAdministratorLogin();
		$client = new AdministratorSteps($scenario);
		$client->comment('setting VAT  , just with default install');
		$client->vatSetting($this->vatSetting);

		$client->doFrontEndLogin();
		$client->amGoingTo('Create a vendor company to be used by the Checkout Process');
		$client->createRedshopbCompany(
			$this->vendor,
			$this->vendor,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);

		$client->amGoingTo('Create a customer company to be used by the Checkout Process');
		$client->createRedshopbCompany(
			$this->company,
			$this->company,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			"- $this->vendor"
		);

		$ownerCompanyCategory = "- ($this->vendor) $this->vendor";
		$client->amGoingTo('Create a category to be used by the Checkout Process');
		$client = new CategorySteps($scenario);
		$client->createCategory($this->category, $ownerCompanyCategory);
		$client->amGoingTo('Create a product to be used by the Checkout Process');

		$ownerCompanyProduct  = "($this->vendor) $this->vendor";
		$client = new ProductSteps($scenario);
		$client->create($this->products['name'], $this->products['sku'], $this->category, $this->products['price'],
			$this->products['price'], $ownerCompanyProduct, 'save&close');

		$client->amGoingTo('Create user with login to be used by the Checkout Process');
		$client = new UserSteps($scenario);
		$client->createUserRole($this->EmployeeWithLogin);
		$client->doFrontendLogout();

		$client->amGoingTo('Add credit for this user');
		$client->doFrontEndLogin();
		$client->addCreditToEmployeeWithLogin($this->employeeWithLoginName, $this->vatSetting['defaultCurrency'], $this->walletEmployee);
		$client->doFrontEndLogout();

		$client->wantTo('Checkout with employee login');
		$client->checkoutWithAesirECStatusModule($this->user, $this->category,  '100,00 €', $this->products['name']);

		$client->am('Administrator');
		$client->comment('Checking order is created after checkout');
		$client->doFrontEndLogin();
		$client->amOnPage(OrderPage::$Url);
		$client->searchForItemInFrontend($this->EmployeeWithLogin['name'], ['search field locator id' => OrderPage::$searchOrder]);
		$client->see("Company: $this->company", OrderPage::$adminForm);
		$client->see("Employee: $this->employeeWithLoginName", OrderPage::$adminForm);
		$client = new OrderSteps($scenario);
		$client->changeOrderStatusToConfirmed($this->employeeWithLoginName, 'Employee');
	}

	/**
	 * @param OrderSteps $client
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 */
	public function cleanUp(OrderSteps $client, \Codeception\Scenario $scenario)
	{
		$client->wantTo('Clean up');
		$client->doFrontEndLogin();
		$client->deleteAllOrder();
		$client->deleteRedshopbCompany($this->company);
		$client->deleteRedshopbCompany($this->vendor);
		$client->doFrontendLogout();
	}
}