<?php

/**
 * Class allDiscountsCest
 *
 * Important note, this test requires the module status to be installed first.
 * Make sure the tests/modules/site_mod_redshob_statusCest.php test is executed first
 */
use Page\Frontend\OrderPage as OrderPage;
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Frontend\CategorySteps as CategorySteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
class checkoutCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.4.1
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
	protected $employeeWithLogin;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $user;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $walletEmployee;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $vatSetting;

	/**
	 * Constructor
	 * @since 2.4.0
	 */
	public function __construct()
	{	$this->faker = Faker\Factory::create();
		
		//information of product
		$this->products = array();
		$this->products['name'] = $this->faker->bothify('Product ?###?');
		$this->products['sku'] = 'SKU' . $this->faker->randomNumber();
		$this->products['price'] = $this->faker->numberBetween(1,10);
		$this->category =  $this->faker->bothify('Category ?###?');
		$this->vendor = $this->faker->bothify('Vendor ?###?');
		$this->company = $this->faker->bothify('Company ?###?');
		$this->employeeWithLogin = $this->faker->bothify('Employee ?###?');
		$this->user =
			[
				'username' => $this->employeeWithLogin,
				'email' => $this->faker->email,
				'name' => $this->faker->name,
				'name2' => $this->faker->name('name 2 value ??##?'),
				'phone' => $this->faker->phoneNumber
			];
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
	}

	/**
	 * @param OrderSteps $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function prepare(OrderSteps $I, \Codeception\Scenario $scenario)
	{
		$I->wantTo('Check checkout with employee login');
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

		$I->comment('Check with employee login');
		$I->checkout($this->user, $this->category,$this->vatSetting['currencySeparator'], $this->vatSetting['currencySymbol'], $this->products);

		$I->am('Administrator');
		$I->comment('changing the status of an Order in Frontend');
		$I->doFrontEndLogin();
		$I->amOnPage(OrderPage::$Url);
		$I->searchForItemInFrontend($this->employeeWithLogin, ['search field locator id' => OrderPage::$searchOrder]);
		$I->see("Company: $this->company", OrderPage::$adminForm);
		$I->see("Employee: $this->employeeWithLogin", OrderPage::$adminForm);
	}

	/**
	 * @param OrderSteps $I
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function cleanUp(OrderSteps $I)
	{
		$I->wantTo('Cleans up');
		$I->doFrontEndLogin();
		$I->deleteOrder($this->employeeWithLogin, 'Employee');
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}
