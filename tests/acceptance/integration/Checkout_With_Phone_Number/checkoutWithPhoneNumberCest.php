<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
use Step\Frontend\UserSteps as UserSteps;
use Step\Frontend\OrderSteps as OrderSteps;

class checkoutWithPhoneNumberCest
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
	protected $walleteEmployee;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $vatSetting;

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
	 * @var string
	 * @since 2.4.0
	 */
	protected $ownerCompanyCategory;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $childCompanyCategory;

	/**
	 * checkoutWithUpdateCartModuleCest constructor.
	 */
	public function __construct()
	{	$this->faker = Faker\Factory::create();

		//information of product and category
		$this->product = array();
		$this->product['name'] = $this->faker->bothify('Product1 ?##?');
		$this->product['sku'] = 'SKU1' . $this->faker->randomNumber();
		$this->product['price'] = '150';

		$this->category =  $this->faker->bothify('Category1 ?##?');
		$this->vendor = $this->faker->bothify('Vendor ?##?');
		$this->company = $this->faker->bothify('Company ?##?');
		$this->walleteEmployee = $this->faker->numberBetween(30000,100000);
		$this->ownerCompanyCategory = "- ($this->vendor) $this->vendor";
		$this->childCompanyCategory = "- - ($this->company) $this->company";

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

		//  information of employee
		$this->EmployeeWithLogin =
			[
				'name' => $this->faker->bothify('Checkout EmployeeWithLogin ?##?'),
				'phone' => $this->faker->phoneNumber,
				'address' => $this->faker->bothify('UserAddress ?##?'),
				'country' => 'Aruba',
				'city' =>'Ho Chi Minh',
				'zip' => $this->faker->postcode,
				'role' => '05 :: Employee with login',
				'hasmail' => 'No',
				'sendMail' => 'No',
				'company' => $this->childCompanyCategory,
				'a_name' => $this->faker->name(),
				'a_address' => $this->faker->address,
				'a_second' => $this->faker->bothify('addressSecond ?##?'),
				'a_zip' => $this->faker->postcode,
				'a_city' => $this->faker->city,
				'a_country' => 'Vietnam',
				'a_phone' => '0976543216',
				'a_cphone' => '0976543211'
			];

		$this->user =
			[
				'username' => $this->EmployeeWithLogin['name'],
				'phone' => $this->faker->phoneNumber,
				'email' => $this->faker->email,
				'name'  => $this->faker->address,
				'name2' => $this->faker->address
			];
	}

	/**
	 * @param AdministratorSteps    $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 */
	public function prepare(AdministratorSteps $I, \Codeception\Scenario $scenario)
	{
		$I->doAdministratorLogin();
		$I = new AdministratorSteps($scenario);
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

		$I->amGoingTo('Create categories to be used by the Checkout Process');
		$I = new CategorySteps($scenario);
		$I->createCategory($this->category, $this->ownerCompanyCategory);

		$I->amGoingTo('Create products to be used by the Checkout Process');
		$I = new ProductSteps($scenario);
		$I->create($this->product['name'], $this->product['sku'], $this->category, $this->product['price'],
			$this->product['price'], "($this->vendor) $this->vendor", 'save&close');

		$I->amGoingTo('Create user with login to be used by the Checkout Process');
		$I = new UserSteps($scenario);
		$I ->createUserRole($this->EmployeeWithLogin);
		$I->doFrontendLogout();

		$I->amGoingTo('Add credit for this user');
		$I->doFrontEndLogin();
		$I->addCreditToEmployeeWithLogin($this->EmployeeWithLogin['name'], $this->vatSetting['defaultCurrency'], $this->walleteEmployee);
		$I->doFrontEndLogout();
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 */
	public function checkoutWithEmployeePhoneNumber(redshopb2b $I)
	{
		$I->wantTo('Checkout with employee and check update phone number');
		$I->checkoutWithUpdatePhoneNumber($this->user, $this->category, $this->product, $this->vatSetting['currencySeparator'], $this->vatSetting['currencySymbol']);
	}

	/**
	 * @param OrderSteps $I
	 * @throws Exception
	 */
	public function cleanUp(OrderSteps $I)
	{
		$I->doFrontEndLogin();
		$I->comment('Cleans up');
		$I->deleteAllOrder();
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}