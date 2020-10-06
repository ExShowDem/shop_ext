<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Frontend\UserSteps as UserSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
use Step\Frontend\Stockrooms\Stockrooms as StockroomsStep;
use Step\Integration\CheckoutWithStockroomOfProduct\CheckoutWithStockroomOfProduct as CheckoutWithStockroomOfProductSteps;

/**
 * Class checkoutWithStockroomOfProductCest
 * @since 2.8.0
 */
class checkoutWithStockroomOfProductCest
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
	 * @var string
	 * @since 2.8.0
	 */
	protected $stockRooms;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $settingShop;

	/**
	 * checkoutWithStockroomOfProductCest constructor.
	 * @since 2.8.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->products = array();
		$this->products['name'] = $this->faker->bothify('Product ?###?');
		$this->products['sku'] = 'SKU' . $this->faker->randomNumber();
		$this->products['price'] = $this->faker->numberBetween(1, 20);
		$this->products['quantity'] = '10';
		$this->products['outOfStock'] = $this->faker->numberBetween(200, 400);
		$this->products['stockNumber'] = $this->faker->numberBetween(50, 100);
		$this->category = $this->faker->bothify('Category ?###?');
		$this->vendor = $this->faker->bothify('Vendor ?###?');
		$this->company = $this->faker->bothify('Company ?###?');
		$this->employeeWithLoginName = $this->faker->bothify('Employee ?###?');
		$this->walletEmployee = $this->faker->numberBetween(30000, 100000);
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
				'name' => $this->faker->name,
				'name2' => $this->faker->name('Name 2 value ??###?'),
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

		$this->stockRooms =
			[
				'name'              => $this->faker->bothify('Name Stockrooms ?##?'),
				'company'           => $this->vendor,
				'minDeliveryTime'   => $this->faker->numberBetween(1,10),
				'maxDeliveryTime'   => $this->faker->numberBetween(10,20),
				'lowerLevel'        => $this->faker->numberBetween(100,200),
				'upperLevel'        => $this->faker->numberBetween(250,400)
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
	}

	/**
	 * @param \Step\Acceptance\redshopb2b $client
	 * @param \Codeception\Scenario       $scenario
	 *                                             
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function checkoutWithStockroomOfProduct(redshopb2b $client, \Codeception\Scenario $scenario)
	{
		$client->doAdministratorLogin();
		$client = new AdministratorSteps($scenario);
		$client->comment('Setting VAT');
		$client->vatSetting($this->vatSetting);
		$client->comment('Setting Shop');
		$client->settingShop($this->settingShop);

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

		$client->amGoingTo('Create a stockroom');
		$client = new StockroomsStep($scenario);
		$client->createStockrooms($this->stockRooms, 'save&close');

		$ownerCompanyCategory = "- ($this->vendor) $this->vendor";
		$client->amGoingTo('Create a category to be used by the Checkout Process');
		$client = new CategorySteps($scenario);
		$client->createCategory($this->category, $ownerCompanyCategory);

		$client->amGoingTo('Create a product to be used by the Checkout Process');
		$ownerCompanyProduct  = "($this->vendor) $this->vendor";
		$client = new ProductSteps($scenario);
		$client->create($this->products['name'], $this->products['sku'], $this->category, $this->products['price'],
			$this->products['price'], $ownerCompanyProduct, 'save&close');
		$client->editProductWithStockroom($this->products, $this->stockRooms['name']);

		$client->amGoingTo('Create a user with login to be used by the Checkout Process');
		$client = new UserSteps($scenario);
		$client->createUserRole($this->EmployeeWithLogin);
		$client->doFrontendLogout();

		$client->amGoingTo('Add credit for this user');
		$client->doFrontEndLogin();
		$client->addCreditToEmployeeWithLogin($this->employeeWithLoginName, $this->vatSetting['defaultCurrency'], $this->walletEmployee);
		$client->doFrontEndLogout();

		$client = new CheckoutWithStockroomOfProductSteps($scenario);
		$client->amGoingTo('Check case add to cart with product is out of stock');
		$client->productIsOutOfStock($this->user, $this->category, $this->products);
		$client->amGoingTo('Checkout with stockroom of product');
		$client->checkoutWithStockroomOfProduct($this->user, $this->category, $this->products, $this->vatSetting['currencySeparator'], $this->vatSetting['currencySymbol']);

		$client->doFrontEndLogin();
		$client->amGoingTo('Check number of Stockroom after checkout');
		$client = new ProductSteps($scenario);
		$client->checkNumberOfStockroomAfterCheckout($this->products, $this->stockRooms['name']);

		$client->comment('Cleans all data');
		$client = new OrderSteps($scenario);
		$client->deleteAllOrder();
		$client = new StockroomsStep($scenario);
		$client->deleteStockrooms($this->stockRooms['name']);
		$client = new redshopb2b($scenario);
		$client->deleteRedshopbCompany($this->company);
		$client->deleteRedshopbCompany($this->vendor);
		$client->doFrontendLogout();
	}
}