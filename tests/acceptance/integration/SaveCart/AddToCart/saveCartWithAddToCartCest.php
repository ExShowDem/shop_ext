<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Acceptance\redshopb2b as redshopb2b;

/**
 * Class saveCartWithAddToCartCest
 *
 * Important note, this test requires the module status to be installed first.
 * Make sure the tests/modules/site_mod_redshob_statusCest.php test is executed first
 */
class saveCartWithAddToCartCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.4.1
	 */
	protected $faker;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $product1;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $product2;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $sku1;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $sku2;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $category1;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $category2;

	/**
	 * @var string
	 * @since 2.4.1
	 */
	protected $company;

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
	protected $cart;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $saveToCartBy;

	/**
	 * @var array
	 * @since 2.4.1
	 */
	protected $settingCartCheckoutWithAddToCart;

	/**
	 * saveCartWithAddToCartCest constructor.
	 * @since 2.4.1
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->product1 = 'cartCestProduct1' . $this->faker->randomNumber();

		$this->product2 = 'cartCestProduct2' . $this->faker->randomNumber();

		$this->sku1 = 'SKU1' . $this->faker->randomNumber();

		$this->sku2 = 'SKU2' . $this->faker->randomNumber();

		$this->category1 = 'cartCestCategory1' . $this->faker->randomNumber();

		$this->category2 = 'cartCestCategory2' . $this->faker->randomNumber();

		$this->company = 'cartCestCustomerCompany' . $this->faker->randomNumber();

		$this->vendor = 'cartCestVendorCompany' . $this->faker->randomNumber();

		$this->employeeWithLogin = 'cartCestEmployee' . $this->faker->randomNumber();

		$this->cart = 'Test save cart' . $this->faker->bothify('##??');

		$this->saveToCartBy =
			[
				'Add to cart' => 'Add to cart',
				'Overwrite Cart' => 'Overwrite Cart'
			];

		$this->settingCartCheckoutWithAddToCart =
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
				'invoiceMail' => 'Yes',
				'saveToCartBy' => 'Add to cart'
			];
	}

	/**
	 * @param redshopb2b $I
	 * @param \Codeception\Scenario       $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function prepare(redshopb2b $I, \Codeception\Scenario $scenario)
	{
		$I->doAdministratorLogin();
		$I = new AdministratorSteps($scenario);
		$I->wantTo('setting Cart Checkout with Save to cart by is Add to cart');
		$I->settingCartCheckout($this->settingCartCheckoutWithAddToCart);

		$I->doFrontEndLogin();
		$I->createRedshopbCompany(
			$this->vendor,
			$this->vendor,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);

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
		$I->createRedshopbCategory($this->category1, "- ($this->vendor) $this->vendor");
		$I->createRedshopbCategory($this->category2, "- ($this->vendor) $this->vendor");
		$I->amGoingTo('Create a product to be used by the Checkout Process');

		$I = new ProductSteps($scenario);
		$I->create($this->product1, $this->sku1, $this->category1, 20,
			20, "($this->vendor) $this->vendor", 'save&close');
		$I->create($this->product2, $this->sku2, $this->category2, 30,
			30, "($this->vendor) $this->vendor", 'save&close');

		$I->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			"- - ($this->company) $this->company"
		);

		$I->addCreditToEmployeeWithLogin($this->employeeWithLogin, 'Euro', 30000);
	}

	/**
	 * @param AdministratorSteps    $I
	 * @param \Codeception\Scenario $scenario
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function saveCartWithFunctionSaveToCartByAddToCart(AdministratorSteps $I, \Codeception\Scenario $scenario)
	{
		$I->doFrontEndLogin($this->employeeWithLogin, $this->employeeWithLogin);
		$I->comment('Test save cart with user login');
		$I->saveCartWithFunctionSaveToCartBy($this->category1, $this->category2, $this->cart, $this->product1, $this->product2, $this->saveToCartBy['Add to cart']);
		$I->deleteSaveCart($this->cart);
		$I->doFrontendLogout();
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.4.1
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->doFrontEndLogin();
		$I->wantTo('Cleans up');
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}
