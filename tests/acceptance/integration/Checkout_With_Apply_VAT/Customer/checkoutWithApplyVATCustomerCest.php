<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Frontend\TaxGroupsSteps as TaxGroupsSteps;
use Step\Frontend\TaxSteps as TaxSteps;
use Step\Frontend\UserSteps as UserSteps;
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Integration\CheckoutWithTaxRate;
class checkoutWithApplyVATCustomerCest
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
	protected $product1;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $category1;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $product2;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $category2;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $vendor;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $walletEmployee;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $ownerCompanyCategory;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $childCompanyCategory;


	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $taxGroup2;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $status;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $countryTax1;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $countryTax2;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $tax2;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $taxRate1;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $taxRate2;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $vatSettingCustomer;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $EmployeeWithLogin;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $user;

	/**
	 * checkoutWithApplyVATCustomerCest constructor.
	 * @since 2.4.1
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		//information of products and categories
		$this->product1 = array();
		$this->product1['name'] = $this->faker->bothify('Product1 ?###?');
		$this->product1['sku'] = 'SKU1' . $this->faker->randomNumber();
		$this->product1['price'] = '150';

		$this->product2 = array();
		$this->product2['name'] = $this->faker->bothify('Product2 ?###?');
		$this->product2['sku'] = 'SKU2' . $this->faker->randomNumber();
		$this->product2['price'] = '200';

		$this->category1 = $this->faker->bothify('Category1 ?###?');
		$this->category2 = $this->faker->bothify('Category2 ?###?');
		$this->vendor = $this->faker->bothify('Vendor ApplyVAT ?##?');
		$this->company = $this->faker->bothify('Company ApplyVAT ?###?');
		$this->ownerCompanyCategory = "- ($this->vendor) $this->vendor";
		$this->childCompanyCategory = "- - ($this->company) $this->company";

		//setting Tax Group
		$this->taxGroup2 = $this->faker->bothify('Tax Group2 ?###?');
		$this->status = 'Publish';

		$this->tax2 = $this->faker->bothify('Tax2 ?###?');
		$this->countryTax2 = 'Vietnam';
		$this->taxRate2 = '0.2';

		$this->vatSettingCustomer =
			[
				'defaultCurrency' => "Euro",
				'currencySymbol'=> '€',
				'currencySeparator' => ",",
				'showPrice' => 'Yes',
				'outletProduct' => 'No',
				'lowestProduct' => 'No',
				'offSystem' => 'Yes',
				'vat' => 'Customer',
				'calculation' => 'Payment',
				'useTax' => 'No'
			];

		// information of employee
		$this->EmployeeWithLogin =
			[
				'name' => $this->faker->bothify('Checkout EmployeeWithLogin ?##?'),
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
				'a_second' => $this->faker->bothify('AddressSecond ?##?'),
				'a_zip' => $this->faker->postcode,
				'a_city' => $this->faker->city,
				'a_country' => 'Vietnam',
				'a_phone' => $this->faker->phoneNumber,
				'a_cphone' => $this->faker->phoneNumber
			];

		$this->user =
			[
				'username' => $this->EmployeeWithLogin['name'],
				'email' => $this->faker->email,
				'name' => $this->faker->name,
				'name2' => $this->faker->name('name 2 value ??##?'),
				'phone' => $this->faker->phoneNumber
			];

		$this->walletEmployee = $this->faker->numberBetween(30000,100000);
	}

	/**
	 * @param AdministratorSteps    $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function prepare(AdministratorSteps $I, \Codeception\Scenario $scenario)
	{
		$I->doAdministratorLogin();
		$I = new AdministratorSteps($scenario);
		$I->vatSetting($this->vatSettingCustomer);

		$I->doFrontEndLogin();
		$I->wantTo('Create all data for checking');
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
			'Vietnam',
			"- $this->vendor"
		);

		$I->amGoingTo('Create taxes group to be used by the Checkout Process');
		$I = new TaxGroupsSteps($scenario);
		$I->create($this->taxGroup2, 'Main Warehouse', $this->status, 'save&close');

		$I->amGoingTo('Create taxes to be used by the Checkout Process');
		$I = new TaxSteps($scenario);
		$I->createTaxWithTaxGroup($this->tax2, $this->taxRate2, $this->countryTax2, 'Main Warehouse', $this->taxGroup2);

		$I->amGoingTo('Create a category to be used by the Checkout Process');
		$I = new CategorySteps($scenario);
		$I->createCategory($this->category1, $this->ownerCompanyCategory);
		$I->createCategory($this->category2, $this->ownerCompanyCategory);

		$I->amGoingTo('Create a product to be used by the Checkout Process');
		$I = new ProductSteps($scenario);
		$I->create($this->product1['name'], $this->product1['sku'], $this->category1, $this->product1['price'],
			$this->product1['price'], "($this->vendor) $this->vendor", 'save&close');

		$I->create($this->product2['name'], $this->product2['sku'], $this->category2, $this->product2['price'],
			$this->product2['price'], "($this->vendor) $this->vendor", 'save&close');

		$I->amGoingTo('Create user with login to be used by the Checkout Process');
		$I = new UserSteps($scenario);
		$I ->createUserRole($this->EmployeeWithLogin);
		$I->doFrontendLogout();

		$I->amGoingTo('Add credit for this user');
		$I->doFrontEndLogin();
		$I->addCreditToEmployeeWithLogin($this->EmployeeWithLogin['name'], $this->vatSettingCustomer['defaultCurrency'], $this->walletEmployee);
		$I->doFrontEndLogout();
	}

	/**
	 * @param redshopb2b            $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function checkoutWithTaxBasedOnCustomerForProduct(CheckoutWithTaxRate $I, \Codeception\Scenario $scenario)
	{
		$I->doFrontEndLogin();
		$I = new ProductSteps($scenario);
		$I->editProductWithTaxGroup($this->product1['name'], $this->taxGroup2);
		$I->doFrontendLogout();

		$I = new CheckoutWithTaxRate($scenario);
		$I->wantToTest('Checkout with apply VAT/TAX rate based on Customer for Product');
		$I->checkoutWithApplyTaxRate($this->user, $this->category1, $this->vatSettingCustomer['currencySeparator'], $this->vatSettingCustomer['currencySymbol'], $this->product1, $this->tax2, $this->taxRate2, 'baseOnProduct');
	}

	/**
	 * @param redshopb2b            $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function checkoutWithTaxBasedCustomerForCompany(CheckoutWithTaxRate $I, \Codeception\Scenario $scenario)
	{
		$I->doFrontEndLogin();
		$I = new redshopb2b($scenario);
		$I->editRedshopbCompanyWithVAT($this->vendor, $this->taxGroup2, $this->vatSettingCustomer['vat']);
		$I->doFrontendLogout();

		$I = new CheckoutWithTaxRate($scenario);
		$I->wantToTest('Checkout with apply VAT/TAX rate based on Customer for Company');
		$I->checkoutWithApplyTaxRate($this->user, $this->category2, $this->vatSettingCustomer['currencySeparator'], $this->vatSettingCustomer['currencySymbol'], $this->product2, $this->tax2, $this->taxRate2, 'baseOnCompany');
	}

	/**
	 * @param redshopb2b            $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function checkoutWithTaxBasedCustomerForCompanyAndProduct(CheckoutWithTaxRate $I, \Codeception\Scenario $scenario)
	{
		$I->doFrontEndLogin();
		$I = new ProductSteps($scenario);
		$I->editProductWithTaxGroup($this->product2['name'], $this->taxGroup2);
		$I->doFrontendLogout();

		$I = new CheckoutWithTaxRate($scenario);
		$I->wantToTest('Checkout with apply VAT/TAX rate based on Customer for Company and Product');
		$I->checkoutWithApplyTaxRate($this->user, $this->category2, $this->vatSettingCustomer['currencySeparator'], $this->vatSettingCustomer['currencySymbol'], $this->product2, $this->tax2, $this->taxRate2, 'baseOnCompany&Product');
	}

	/**
	 * @param redshopb2b            $client
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function cleanUp(redshopb2b $client, \Codeception\Scenario $scenario)
	{
		$client->doFrontEndLogin();
		$client->wantToTest('Cleans up');
		$client->deleteRedshopbCompany($this->company);
		$client->deleteRedshopbCompany($this->vendor);

		$client = new TaxSteps($scenario);
		$client->delete($this->tax2);

		$client = new TaxGroupsSteps($scenario);
		$client->delete($this->taxGroup2);

		$client = new OrderSteps($scenario);
		$client->deleteAllOrder();
		$client->doFrontendLogout();
	}
}