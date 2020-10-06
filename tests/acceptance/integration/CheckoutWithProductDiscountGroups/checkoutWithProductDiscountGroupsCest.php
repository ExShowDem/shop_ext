<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Step\Frontend\OrderSteps as OrderSteps;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Frontend\AllDiscountsSteps as AllDiscountsSteps;
use \Step\Frontend\ProductDiscountGroupsSteps as ProductDiscountGroupsSteps;

/**
 * Class checkoutWithProductDiscountGroupsCest
 * @since 2.8.0
 */
class checkoutWithProductDiscountGroupsCest
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
	protected $products1;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $products2;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $product1;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $product2;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $sku1;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $sku2;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $price1;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $price2;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $category1;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $category2;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $vendor;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $employeeWithLogin;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $company;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $walletEmployee;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected  $discountType;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $salesType;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $status;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $currency;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $vatSetting;

	/**
	 * @var array
	 * @since 2.8.0
	 */
	protected $settingShop;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $nameProductDiscountGroup1;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $nameProductDiscountGroup2;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $codeDiscount1;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $codeDiscount2;

	/**
	 * checkoutWithDiscountPercentCest constructor.
	 * @since 2.8.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		//information of product and category
		$this->product1 = $this->faker->bothify('DiscountGroups Product 1 ?##?');

		$this->product2 = $this->faker->bothify('DiscountGroups Product 2 ?##?');

		$this->sku1 = $this->faker->bothify('productSKU1 ?##?');

		$this->sku2 = $this->faker->bothify('productSKU2 ?##?');

		$this->price1 = '200';

		$this->price2 = '150';

		$this->category1 =  $this->faker->bothify('Category1 ?###?');

		$this->category2 = $this->faker->bothify('Category2 ?###?');

		$this->vendor = $this->faker->bothify('Vendor ?###?');

		$this->company = $this->faker->bothify('Company ?###?');

		$this->employeeWithLogin = $this->faker->bothify('Employee ?###?');

		$this->walletEmployee = $this->faker->numberBetween(30000,100000);

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

		$this->nameProductDiscountGroup1 = $this->faker->bothify('DiscountGroups1 Name?##?');
		$this->nameProductDiscountGroup2 = $this->faker->bothify('DiscountGroups2 Name?##?');

		$this->codeDiscount1 = $this->faker->bothify('codeDiscount1 ?##?');
		$this->codeDiscount2 = $this->faker->bothify('codeDiscount2 ?##?');
		$this->products1 = array();
		$this->products2 = array();

		//discount
		$this->discountType = 'Product Discount Group';
		$this->salesType = 'All Debtor';
		$this->status = 'Published';
		$this->currency = 'Euro';
	}

	/**
	 * @param \Step\Frontend\OrderSteps $I
	 * @param \Codeception\Scenario     $scenario
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function prepare(OrderSteps $I, \Codeception\Scenario $scenario)
	{
		$I = new AdministratorSteps($scenario);
		$I->doAdministratorLogin();
		$I->wantTo('setting VAT, just with default install');

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

		$ownerCompanyCategory = "- ($this->vendor) $this->vendor";
		$I->amGoingTo('Create a category to be used by the Checkout Process');
		$I = new CategorySteps($scenario);
		$I->createCategory($this->category1, $ownerCompanyCategory);
		$I->createCategory($this->category2, $ownerCompanyCategory);

		$I->amGoingTo('Create a product to be used by the Checkout Process');
		$ownerCompanyProduct  = "($this->vendor) $this->vendor";
		$I = new ProductSteps($scenario);
		$I->create($this->product1, $this->sku1, $this->category1, $this->price1, $this->price1, $ownerCompanyProduct, 'save&close');
		$this->products1[] = $this->product1;
		$I->create($this->product2, $this->sku2, $this->category2, $this->price2, $this->price2, $ownerCompanyProduct, 'save&close');
		$this->products2[] = $this->product2;

		$I = new ProductDiscountGroupsSteps($scenario);
		$I->amGoingTo('Create new discount group');
		$ownerCompanyProductDiscountGroups = "- ($this->vendor) $this->vendor";
		$I->create($ownerCompanyProductDiscountGroups, $this->nameProductDiscountGroup1, $this->codeDiscount1, $this->products1);
		$I->create($ownerCompanyProductDiscountGroups, $this->nameProductDiscountGroup2, $this->codeDiscount2, $this->products2);

		$I = new AllDiscountsSteps($scenario);
		$I->amGoingTo('Create all discount to be used by the Discount');
		$I->createAllDiscountTypeProductDiscountGroup($this->discountType, $this->nameProductDiscountGroup1, $this->salesType, $this->status, 10, $this->currency, 'Percent');
		$I->createAllDiscountTypeProductDiscountGroup($this->discountType, $this->nameProductDiscountGroup2, $this->salesType, $this->status, 50, $this->currency, 'Total');

		$I->amGoingTo('Create new user and add credit cart');
		$I->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			"- - ($this->company) $this->company"
		);
		$I->addCreditToEmployeeWithLogin($this->employeeWithLogin, $this->vatSetting['defaultCurrency'], $this->walletEmployee);
		$I->doFrontEndLogout();

		$I->comment('Checkout with discount percent');
		$I->checkoutAllDiscount($this->employeeWithLogin, $this->category1, '20,00', '180,00 €', '200,00');
		$I->comment('Checkout with discount total');
		$I->checkoutAllDiscount($this->employeeWithLogin, $this->category2, '50,00', '100,00 €', '150,00');

		$I->doFrontEndLogin();
		$I->comment('Cleans up data');
		$I = new AllDiscountsSteps($scenario);
		$I->deleteDiscount();
		$I = new ProductDiscountGroupsSteps($scenario);
		$I->delete($this->nameProductDiscountGroup1);
		$I->delete($this->nameProductDiscountGroup2);
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}