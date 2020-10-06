<?php
use Step\Frontend\ProductSteps as ProductSteps;
/**
 * Class checkoutCest
 *
 * Important note, this test requires the module status to be installed first.
 * Make sure the tests/modules/site_mod_redshob_statusCest.php test is executed first
 */
class checkoutWithPaymentAndShippingCest
{
	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $currency;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $currencySymbol;

	/**
	 * @var \Faker\Generator
	 * @since 2.4.0
	 */
	protected $faker;

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
	 * @var array
	 * @since 2.4.0
	 */
	protected $vatSetting;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $product;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $shippingRate;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $vendor;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $company;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $debtorGroup;

	/**
	 * @var
	 * @since 2.5.1
	 */
	protected $shippingMethod;

	/**
	 * Constructor
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->currency       = 'US Dollar';
		$this->currencySymbol = '$';
		$this->faker = Faker\Factory::create();
		$this->employeeWithLogin = $this->faker->bothify('PaShipping Employee ?##?');
		$this->user =
			[
				'username' => $this->employeeWithLogin,
				'email' => $this->faker->email,
				'name' => $this->faker->name,
				'name2' => $this->faker->name('name 2 value ??##?'),
				'phone' => $this->faker->phoneNumber
			];

		//setting vat
		$this->vatSetting =
			[
				'defaultCurrency' => "US Dollar",
				'currencySymbol' => "$",
				'currencySeparator' => ",",
				'showPrice' => 'Yes',
				'outletProduct' => 'No',
				'lowestProduct' => 'No',
				'offSystem' => 'Yes',
				'vat' => 'Vendor',
				'calculation' => 'Payment',
				'useTax' => 'No'
			];

		$this->product = array();
		$this->shippingRate = array();
		$this->category = $this->faker->bothify('PaShipping Category ?##?');
	}

	/**
	 * @param \Step\Acceptance\redshopb2b $I
	 * @throws Exception
	 */
	public function activatePaypalPayment(\Step\Acceptance\redshopb2b $I, $scenario)
	{
		$I->doAdministratorLogin();
		$I->wantToTest('Active the paypal payment');
		$I->activatePaypalPayment();
		$I = new \Step\Acceptance\AdministratorSteps($scenario);
		$I->wantToTest('Setup the vat for site');
		$I->vatSetting($this->vatSetting);
	}

	/**
	 * Creates the following elements that will be needed for the test:
	 *
	 * - "Vendor": company that sells the product
	 * - "Product" to be selled by the "Vendor"
	 * - "Company": the customer B2B company that purchases product
	 * - Debtor Group
	 * - A Payment Method using Paypal
	 * - A Shipping Method
	 * - A Shipping Rate for the shipping Method
	 * - Employee With login
	 * - Shipping Method
	 * @throws Exception
	 */
	public function prepare(\Step\Acceptance\redshopb2b $I, $scenario)
	{
		$I->doFrontEndLogin();
		$I->amGoingTo('Create a VENDOR company to be used by the Checkout Process');
		$this->vendor = $this->faker->bothify('PaShipping CuCompany ?##?');
		$I->createRedshopbCompany(
			$this->vendor,
			$this->vendor,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company',
			[
				'Company Currency' => $this->currency
			]
		);

		$I->amGoingTo('Create a CATEGORY to be used by the Checkout Process');
		$I->createRedshopbCategory(
			$this->category,
			"- ($this->vendor) $this->vendor"
		);

		$I->amGoingTo('Create a PRODUCT to be used by the Checkout Process');
		$this->product['name'] = $this->faker->bothify('PaShipping Product ?##?');
		$this->product['sku']  = $this->faker->bothify('SKU?##?');
		$I                     = new ProductSteps($scenario);
		$this->product['price'] = 1;
		$I->create($this->product['name'], $this->product['sku'], $this->category,  $this->product['price'],  $this->product['price'],
			"($this->vendor) $this->vendor", 'save&close');

		$I->amGoingTo('Create a customer COMPANY to be used by the Checkout Process');
		$this->company = $this->faker->bothify('PaShipping Company ?##?');
		$I->createRedshopbCompany(
			$this->company,
			$this->company,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			"- $this->vendor",
			[
				'Company Currency' => $this->currency
			]
		);

		$I->amGoingTo('Create a DEBTOR GROUP which will Hold our Company');
		$this->debtorGroup['name'] = $this->faker->bothify('PaShipping Name?##?');
		$this->debtorGroup['code'] = $this->faker->bothify('PaShippingCode ?##?');
		$I->createRedshopbDebtorGroup(
			$this->debtorGroup['name'],
			$this->debtorGroup['code'],
			'Main Company',
			"- - ($this->company) $this->company"
		);

		$I->amGoingTo('Create a SHIPPING METHOD for this Debtor Group');
		$this->shippingMethod['title'] = $this->faker->bothify('PaShipping Shipment ?##?');
		$I->createRedshopbShippingMethod(
			"Default shipping",
			$this->debtorGroup['name'],
			$this->shippingMethod['title']
		);

		$I->amGoingTo('Create a SHIPPING RATE for the Shipping method');
		$this->shippingRate['name'] = $this->faker->bothify('PaShipping Shipping Rate ?##?');
		$I->createRedshopbShippingRate(
			$this->debtorGroup['name'] . ' - ' . $this->shippingMethod['title'],
			$this->shippingRate['name'],
			[$this->product['name']],
			['Price' => '2.55']
		);

		$I->amGoingTo('Create a EMPLOYEE WITH LOGIN');
		$I->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			"- - ($this->company) $this->company"
		);

		$I->addCreditToEmployeeWithLogin($this->employeeWithLogin, $this->currency, 3000);
		$I->doFrontEndLogout();
	}

	/**
	 * @param   \Step\Acceptance\redshopb2b $I
	 *
	 * @depends prepare
	 * @throws Exception
	 */
	public function checkoutWithShippingAndPayment(\Step\Acceptance\redshopb2b $I)
	{
		$I->wantToTest('Checkout with shipping and payment method');
		$I->checkoutWithShippingAndPayment($this->user, $this->category, $this->vatSetting['currencySeparator'], $this->vatSetting['currencySymbol'], $this->product, $this->shippingRate);
	}

	/**
	 * @param \Step\Acceptance\redshopb2b $I
	 * @throws Exception
	 */
	public function cleanUp(\Step\Acceptance\redshopb2b $I)
	{
		$I->doFrontEndLogin();
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
		$I->deleteRedshopbDebtorGroup($this->debtorGroup['name']);
	}
}
