<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir - EC. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Frontend\UserSteps as UserSteps;
use Step\Frontend\OfferSteps as OfferSteps;
use Step\Acceptance\redshopb2b as redshopb2b;

class checkoutWithOfferCest
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
	protected $user;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameOffer;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $descriptionOffer;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $vendor;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $currency;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $status;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $product1;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $product2;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category1;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category2;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $offer;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $customerNumber;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $employeeWithLogin;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $walletEmployee;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $vatSetting;

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
	 * @var array
	 * @since 2.4.0
	 */
	protected $EmployeeWithLogin;

	/**
	 * checkoutWithOfferCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		//add this class do not support collection
		$this->faker = Faker\Factory::create();

		$this->offer                    = array();
		$this->offer['name']            = $this->faker->bothify('offerName ??###?');
		$this->offer['description']     = $this->faker->bothify('offerDescription ??###?');
		$this->offer['discount']        = '10';
		$this->offer['status']          = 'Sent';

		$this->currency = 'Euro';

		$this->status ='Unpublished';

		//information of product and category
		$this->product1 = array();
		$this->product1['name'] = $this->faker->bothify('Product1 ?###?');
		$this->product1['sku'] = 'SKU1' . $this->faker->randomNumber();
		$this->product1['price'] = '100';

		$this->product2 = array();
		$this->product2['name'] = $this->faker->bothify('Product2 ?###?');
		$this->product2['sku'] = 'SKU2' . $this->faker->randomNumber();
		$this->product2['price'] = '200';

		$this->category1 =  $this->faker->bothify('Category1 ?###?');
		$this->category2 =  $this->faker->bothify('Category2 ?###?');
		$this->vendor = $this->faker->bothify('Vendor ?###?');
		$this->company = $this->faker->bothify('Company ?###?');
		$this->walletEmployee = $this->faker->numberBetween(30000,100000);
		$this->ownerCompanyCategory = "- ($this->vendor) $this->vendor";
		$this->childCompanyCategory = "- - ($this->company) $this->company";

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
				'username' => $this->EmployeeWithLogin['name'],
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
	 * @param OfferSteps $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 */
	public function prepare(OfferSteps $I, \Codeception\Scenario $scenario)
	{
		$I->doAdministratorLogin();
		$I = new AdministratorSteps($scenario);
		$I->comment('setting VAT  , just with default install');
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
		$I->createCategory($this->category1, $this->ownerCompanyCategory);
		$I->createCategory($this->category2, $this->ownerCompanyCategory);

		$I->amGoingTo('Create products to be used by the Checkout Process');
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
		$I->addCreditToEmployeeWithLogin($this->EmployeeWithLogin['name'], $this->vatSetting['defaultCurrency'], $this->walletEmployee);
		$I->doFrontEndLogout();

		$I = new OfferSteps($scenario);
		$I->wantTo('Check create new offer by employee login');
		$I->createOfferByEmployee($this->user, $this->category1, $this->product1, $this->offer);
	}

	/**
	 * @param OfferSteps $I
	 * @throws Exception
	 */
	public function sendOfferByAdministrator(OfferSteps $I)
	{
		$I->doFrontEndLogin();
		$I->wantTo('Check send offer by administrator');
		$I->sendOfferByAdministrator($this->offer);
	}

	/**
	 * @param OfferSteps $I
	 * @throws Exception
	 */
	public function checkoutWithOfferAndUpdateCartModule(OfferSteps $I)
	{
		$I->wantTo('Checkout with offer and update cart module');
		$I->checkoutWithOfferAndUpdateCartModule($this->user, $this->offer, $this->category2, $this->product1, $this->product2, $this->vatSetting['currencySeparator'], $this->vatSetting['currencySymbol']);
	}

	/**
	 * @param \Step\Frontend\OrderSteps $I
	 * @param \Codeception\Scenario     $scenario
	 *
	 * @throws \Exception
	 */
	public function cleanUp(OrderSteps $I, \Codeception\Scenario $scenario)
	{
		$I->doFrontEndLogin();
		$I->wantTo('Cleans up');
		$I->deleteAllOrder();

		$I = new OfferSteps($scenario);
		$I->deleteOffer($this->offer['name']);
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}