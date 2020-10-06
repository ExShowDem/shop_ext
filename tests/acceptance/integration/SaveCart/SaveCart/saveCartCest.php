<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
use Step\Frontend\ProductSteps as ProductSteps;
use Step\Frontend\OrderSteps as OrderSteps;
use Step\Acceptance\redshopb2b as redshopb2b;

/**
 * Class saveCartCest
 *
 * Important note, this test requires the module status to be installed first.
 * Make sure the tests/modules/site_mod_redshob_statusCest.php test is executed first
 */
class saveCartCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.4.0
	 */
	protected $faker;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $product1;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $sku1;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category1;

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
	protected $employeeWithLogin;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $cart;

	/**
	 * saveCartCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->product1 = 'cartCest Product1' . $this->faker->randomNumber();

		$this->sku1 = 'SKU1' . $this->faker->randomNumber();

		$this->category1 = 'cartCest Category1' . $this->faker->randomNumber();

		$this->company = 'cartCest CustomerCompany' . $this->faker->randomNumber();

		$this->vendor = 'cartCest VendorCompany' . $this->faker->randomNumber();

		$this->employeeWithLogin = 'cartCest Employee' . $this->faker->randomNumber();

		$this->cart = 'Test save cart' . $this->faker->bothify('##??');
	}

	/**
	 * @param redshopb2b $I
	 * @param \Codeception\Scenario       $scenario
	 * @throws Exception
	 */
	public function saveCart(redshopb2b $I, \Codeception\Scenario $scenario)
	{
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
		$I->amGoingTo('Create a product to be used by the Checkout Process');
		$I = new ProductSteps($scenario);

		$I->create($this->product1, $this->sku1, $this->category1, 20,
			20, "($this->vendor) $this->vendor", 'save&close');

		$I->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			"- - ($this->company) $this->company"
		);

		$I->addCreditToEmployeeWithLogin($this->employeeWithLogin, 'Euro', 30000);
		$I->doFrontEndLogout();

		$I->doFrontEndLogin($this->employeeWithLogin, $this->employeeWithLogin);
		$I->comment('Create save cart with user login');
		$I->saveCart($this->category1,$this->cart);
	}

	/**
	 * @depends saveCart
	 * @throws Exception
	 */
	public function checkoutSavedCart(redshopb2b $I)
	{
		$I->am('Administrator');
		$I->wantToTest('checkout a saved cart in frontend');
		$I->doFrontEndLogin($this->employeeWithLogin, $this->employeeWithLogin);
		$I->saveCartCheckout($this->cart);
		$I->deleteSaveCart($this->cart);
	}

	/**
	 * @param OrderSteps $I
	 * @throws Exception
	 */
	public function cleanUp(OrderSteps $I)
	{
		$I->doFrontEndLogin();
		$I->wantTo('Cleans up');
		$I->deleteAllOrder();
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}
