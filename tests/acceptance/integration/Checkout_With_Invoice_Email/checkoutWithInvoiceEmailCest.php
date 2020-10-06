<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Cest checkoutWithInvoiceEmail
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Frontend\UserSteps as UserSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
use Step\Integration\CheckoutWithInvoiceEmail\CheckoutWithInvoiceEmail as CheckoutWithInvoiceEmailSteps;

/**
 * Class checkoutWithInvoiceEmailCest
 * @since 2.8.0
 */
class checkoutWithInvoiceEmailCest
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
	protected $products;

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
	protected $employeeWithLoginName;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $walletEmployee;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $childCompanyCategory;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $EmployeeWithLogin;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $user;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $vatSetting;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $settingCartCheckout;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $function;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $country;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $mainCompany;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $customAt;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $settingShop;

	/**
	 * checkoutWithInvoiceEmailCest constructor.
	 * @since 2.8.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->products = array();
		$this->products['name'] = $this->faker->bothify('Product ?###?');
		$this->products['sku'] = 'SKU' . $this->faker->randomNumber();
		$this->products['price'] = $this->faker->numberBetween(1, 100);
		$this->products['quantity'] = $this->faker->numberBetween(1, 10);
		$this->category = $this->faker->bothify('Category ?###?');
		$this->vendor = $this->faker->bothify('Vendor ?###?');
		$this->company = $this->faker->bothify('Company ?###?');
		$this->employeeWithLoginName = $this->faker->bothify('Employee ?###?');
		$this->walletEmployee = $this->faker->numberBetween(30000, 100000);
		$this->country = 'Denmark';
		$this->mainCompany = 'Main Company';
		$this->customAt = '- '.$this->vendor;
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
				'a_second' => $this->faker->bothify('AddressSecond ?###?'),
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

		//setting cart checkout
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

		$this->function = 'save&close';
	}

	/**
	 * @param redshopb2b           $client
	 * @param \Codeception\Scenario $scenario
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $client, \Codeception\Scenario $scenario)
	{
		$client->doAdministratorLogin();
		$client = new AdministratorSteps($scenario);
		$client->comment('Setting VAT');
		$client->vatSetting($this->vatSetting);
		$client->comment('Setting Cart Checkout');
		$client->settingCartCheckout($this->settingCartCheckout);
		$client->comment('Setting Shop');
		$client->settingShop($this->settingShop);

		$client->doFrontEndLogin();
		$client->amGoingTo('Create a vendor company to be used by the Checkout Process');
		$client->createRedshopbCompany(
			$this->vendor,
			$this->vendor,
			$this->faker->address,
			$this->faker->postcode,
			$this->faker->city,
			$this->country,
			$this->mainCompany
		);

		$client->amGoingTo('Create a customer company to be used by the Checkout Process');
		$client->createRedshopbCompany(
			$this->company,
			$this->company,
			$this->faker->address,
			$this->faker->postcode,
			$this->faker->city,
			$this->country,
			$this->customAt
		);

		$ownerCompanyCategory = "- ($this->vendor) $this->vendor";
		$client->amGoingTo('Create a category to be used by the Checkout Process');
		$client = new CategorySteps($scenario);
		$client->createCategory($this->category, $ownerCompanyCategory);

		$client->amGoingTo('Create a product to be used by the Checkout Process');
		$ownerCompanyProduct  = "($this->vendor) $this->vendor";
		$client = new ProductSteps($scenario);
		$client->create($this->products['name'], $this->products['sku'], $this->category, $this->products['price'],
			$this->products['price'], $ownerCompanyProduct, $this->function);

		$client->amGoingTo('Create a user with login to be used by the Checkout Process');
		$client = new UserSteps($scenario);
		$client->createUserRole($this->EmployeeWithLogin);
		$client->doFrontendLogout();

		$client->amGoingTo('Add credit for this user');
		$client->doFrontEndLogin();
		$client->addCreditToEmployeeWithLogin($this->employeeWithLoginName, $this->vatSetting['defaultCurrency'], $this->walletEmployee);
		$client->doFrontEndLogout();

		$client->amGoingTo('Checkout with invoice email');
		$client = new CheckoutWithInvoiceEmailSteps($scenario);
		$client->checkoutWithInvoiceEmail($this->user, $this->category, $this->products, $this->vatSetting['currencySeparator'], $this->vatSetting['currencySymbol']);

		$client->doFrontEndLogin();
		$client->comment('Cleans all data');
		$client = new OrderSteps($scenario);
		$client->deleteAllOrder();
		$client = new redshopb2b($scenario);
		$client->deleteRedshopbCompany($this->company);
		$client->deleteRedshopbCompany($this->vendor);
		$client->doFrontendLogout();
	}
}