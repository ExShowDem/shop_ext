<?php

/**
 * Class checkoutWithDiscountTotalCest
 *
 * Important note, this test requires the module status to be installed first.
 * Make sure the tests/modules/site_mod_redshob_statusCest.php test is executed first
 */

use Step\Frontend\OrderSteps as OrderSteps;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Frontend\AllDiscountsSteps as AllDiscountsSteps;

class checkoutWithDiscountTotalCest
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
	protected $products;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $category;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $vendor;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $employeeWithLogin;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $company;

	/**
	 * @var int
	 * @since 2.4.1
	 */
	protected $walletEmployee;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $totalDiscount;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected  $discountType;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $salesType;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $status;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $currency;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $vatSetting;

	/**
	 * checkoutWithDiscountTotalCest constructor.
	 * @since 2.4.1
	 */
	public function __construct()
	{	$this->faker = Faker\Factory::create();

		//information of product
		$this->products = array();
		$this->products['name'] = $this->faker->bothify('Product ?###?');
		$this->products['sku'] = 'SKU' . $this->faker->randomNumber();
		$this->products['price'] = '200';
		$this->category =  $this->faker->bothify('Category ?###?');
		$this->vendor = $this->faker->bothify('Vendor ?###?');
		$this->company = $this->faker->bothify('Company ?###?');
		$this->employeeWithLogin = $this->faker->bothify('Employee ?###?');
		$this->walletEmployee = $this->faker->numberBetween(30000,100000);

		//discount
		$this->totalDiscount = '20';
		$this->discountType = 'Product';
		$this->salesType = 'All Debtor';
		$this->status = 'Published';
		$this->currency = 'Euro';

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
	 * @param \Step\Frontend\OrderSteps $I
	 * @param \Codeception\Scenario     $scenario
	 *
	 * @throws \Exception
	 * @since 2.4.1
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
		$I->createCategory($this->category, $ownerCompanyCategory);
		$I->amGoingTo('Create a product to be used by the Checkout Process');

		$ownerCompanyProduct  = "($this->vendor) $this->vendor";
		$I = new ProductSteps($scenario);
		$I->create($this->products['name'], $this->products['sku'], $this->category, $this->products['price'],
			$this->products['price'], $ownerCompanyProduct, 'save&close');

		$I->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			"- - ($this->company) $this->company"
		);

		$I->addCreditToEmployeeWithLogin($this->employeeWithLogin, $this->vatSetting['defaultCurrency'], $this->walletEmployee);
		$I->doFrontEndLogout();
	}

	/**
	 * @param \Step\Frontend\OrderSteps $I
	 * @param \Codeception\Scenario     $scenario
	 *
	 * @throws \Exception
	 * @since 2.4.1
	 */
	public function checkoutDiscountTotal(OrderSteps $I, \Codeception\Scenario $scenario)
	{
		$I->doFrontEndLogin();
		$I->amGoingTo('Create all discount to be used by the Discount');
		$I = new AllDiscountsSteps($scenario);
		$I->createDiscountTotal($this->products['name'], $this->totalDiscount, $this->discountType, $this->salesType, $this->status, $this->currency);
		$I->doFrontEndLogout();

		$I->comment('Checkout with discount total');
		$I->checkoutAllDiscount($this->employeeWithLogin, $this->category, '20,00', '180,00 €', '200,00');
	}

	/**
	 * @param \Step\Frontend\OrderSteps $I
	 * @param                           $scenario
	 *
	 * @throws \Exception
	 * @since 2.4.1
	 */
	public function cleanUp(OrderSteps $I, $scenario)
	{
		$I->wantTo('Cleans up');
		$I->doFrontEndLogin();
		$I = new AllDiscountsSteps($scenario);
		$I->deleteDiscount();
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}
