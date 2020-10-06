<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\DepartmentSteps as DepartmentSteps;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Frontend\UserSteps as UserSteps;
use Step\Acceptance\Configuration\SettingsRole as SettingsRoleStep;
use Step\Frontend\CollectionSteps as CollectionSteps;
use Step\Frontend\OrderSteps as OrderSteps;

/**
 * Class adminCollectionAddToCartFromProductListCest
 * @since 2.8.0
 */
class adminCollectionAddToCartFromProductListCest
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
	protected $product;

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
	protected $walletEmployee;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $ownerCompanyCategory;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $childCompanyCategory;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $vatSetting;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $admin;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $user;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $settingShop;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $settingRoleNo;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $settingRoleYes;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $nameCollection;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $department;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $position1;

	/**
	 * adminCollectionAddToCartFromProductListCest constructor.
	 * @since 2.8.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		//information of product and category
		$this->product = array();
		$this->product['name'] = $this->faker->bothify('Product ?###?');
		$this->product['sku'] = 'SKU1' . $this->faker->randomNumber();
		$this->product['price'] = '150';

		$this->category = $this->faker->bothify('Category ?###?');
		$this->vendor = $this->faker->bothify('Vendor ?##?');
		$this->company = $this->faker->bothify('Company ?###?');
		$this->ownerCompanyCategory = "- ($this->vendor) $this->vendor";
		$this->childCompanyCategory = "- - ($this->company) $this->company";

		$this->nameCollection = $this->faker->bothify('nameCollection ?###?');

		$this->department = $this->faker->bothify('Department ?###?');

		// setting vat
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

		// information of admin
		$this->admin =
			[
				'name' => $this->faker->bothify('Checkout Admin ?##?'),
				'phone' => $this->faker->phoneNumber,
				'address' => $this->faker->bothify('UserAddress ?##?'),
				'country' => 'Aruba',
				'city' =>'Ho Chi Minh',
				'zip' => $this->faker->postcode,
				'role' => '01 :: Administrator',
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
				'username' => $this->admin['name'],
				'department' => $this->department. ' ' . "($this->company)"
			];

		$this->walletEmployee = $this->faker->numberBetween(30000,100000);

		//setting shop
		$this->settingShop =
			[
				'showCategoryProduct' => 'No',
				'ajaxCategory' => 'No',
				'dayProduct' => 16,
				'showProductPrintOption' => 'No',
				'compareUsingOption' => 'End customers (level3+)',
				'showShopAs' => 'Collections',
				'defaultLayout' => 'List',
				'defaultAccessory' => 'Checkbox Input',
				'showInlineCategory' => 'Yes',
				'showShopCollection' => 'Yes',
			];

		//setting role
		$this->settingRoleNo =
			[
				'allCollectionsToAdministrator'             => 'No',
			];

		$this->settingRoleYes =
			[
				'allCollectionsToAdministrator'             => 'Yes',
			];

		$this->position1 = '1';
	}

	/**
	 * @param AdministratorSteps $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(AdministratorSteps $I, \Codeception\Scenario $scenario)
	{
		$I->doAdministratorLogin();
		$I = new AdministratorSteps($scenario);
		$I->amGoingTo('Setting VAT');
		$I->vatSetting($this->vatSetting);
		$I->amGoingTo('Setting Shop');
		$I->settingShop($this->settingShop);

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

		$I->amGoingTo('Create a category to be used by the Checkout Process');
		$I = new CategorySteps($scenario);
		$I->createCategory($this->category, $this->ownerCompanyCategory);

		$I->amGoingTo('Create a product to be used by the Checkout Process');
		$I = new ProductSteps($scenario);
		$I->create($this->product['name'], $this->product['sku'], $this->category, $this->product['price'],
			$this->product['price'], "($this->vendor) $this->vendor", 'save&close');

		$I->comment('Create a department to be used by the Collection');
		$I->createRedshopbDepartment($this->department, $this->childCompanyCategory);

		$I->comment('Create a collection');
		$I = new CollectionSteps($scenario);
		$I->create($this->department, $this->nameCollection, $this->childCompanyCategory, $this->vatSetting['defaultCurrency'], 'Publish', $this->product);
		$I->editCollectionWithPrices($this->nameCollection, $this->product, $this->position1);

		$I->amGoingTo('Create admin to be used by the Checkout Process');
		$I = new UserSteps($scenario);
		$I->createUserRole($this->admin);
		$I->doFrontendLogout();

		$I->amGoingTo('Add credit for this admin');
		$I->doFrontEndLogin();
		$I->addCreditToEmployeeWithLogin($this->admin['name'], $this->vatSetting['defaultCurrency'], $this->walletEmployee);
	}

	/**
	 * @param \Step\Acceptance\AdministratorSteps $I
	 * @param \Codeception\Scenario               $scenario
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function collectionAdminAddToCartFromProductsListWithRoleYes(AdministratorSteps $I, \Codeception\Scenario $scenario)
	{
		$I->doAdministratorLogin();
		$I = new SettingsRoleStep($scenario);
		$I->amGoingTo('Setting Role');
		$I->settingsRole($this->settingRoleYes);

		$I->wantToTest('Collection - Admin add to cart form products list with role yes');
		$I->checkout($this->user, $this->category, $this->vatSetting['currencySeparator'], $this->vatSetting['currencySymbol'], $this->product);
	}

	/**
	 * @param \Step\Acceptance\AdministratorSteps $I
	 * @param \Codeception\Scenario               $scenario
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function collectionAdminAddToCartFromProductsListWithRoleNo(AdministratorSteps $I, \Codeception\Scenario $scenario)
	{
		$I->doAdministratorLogin();
		$I = new SettingsRoleStep($scenario);
		$I->amGoingTo('Setting Role');
		$I->settingsRole($this->settingRoleNo);

		$I->doFrontEndLogin();
		$I = new UserSteps($scenario);
		$I->amGoingTo('Edit admin with department');
		$I->editUserWithDepartment($this->user);
		$I->doFrontendLogout();

		$I->wantToTest('Collection - Admin add to cart form products list with role no');
		$I->checkout($this->user, $this->category, $this->vatSetting['currencySeparator'], $this->vatSetting['currencySymbol'], $this->product);
	}

	/**
	 * @param \Step\Acceptance\AdministratorSteps $I
	 * @param \Codeception\Scenario               $scenario
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function cleansUp(AdministratorSteps $I, \Codeception\Scenario $scenario)
	{
		$I->wantToTest('Clear ups data');
		$I->doFrontEndLogin();
		$I = new OrderSteps($scenario);
		$I->deleteAllOrder();
		$I = new CollectionSteps($scenario);
		$I->delete($this->nameCollection);
		$I = new DepartmentSteps($scenario);
		$I->deleteDepartment($this->department);
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}