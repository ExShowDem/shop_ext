<?php
/**
 * @package     Aesir-ec
 * @subpackage  Cest saveCartWithProductVariant
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

use Step\Frontend\ProductSteps as ProductSteps;
use Step\Acceptance\AdministratorSteps as AdministratorSteps;
use Step\Acceptance\redshopb2b as redshopb2b;
use Step\Integration\SaveCartWithProductVariant as SaveCartWithProductVariant;

/**
 * Class saveCartWithProductVariantCest
 * @since 2.6.0
 */
class saveCartWithProductVariantCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.6.0
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $product1;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $sku1;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $category1;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $vendor;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $walletEmployee;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $vatSetting;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $employeeWithLogin;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $cart;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $user;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $nameAttributeFirst;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $nameAttributeSecond;

	/**
	 * @var null
	 * @since 2.6.0
	 */
	protected $attributeType;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $settingCartCheckout;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $status;

	/**
	 * @var int
	 * @since 2.6.0
	 */
	protected $valueSize;

	/**
	 * @var int
	 * @since 2.6.0
	 */
	protected $valueSizeSecond;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $valueColorRed;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $valueColorGreen;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $defaultSelect;

	/**
	 * @var int
	 * @since 2.6.0
	 */
	protected $position1;

	/**
	 * @var int
	 * @since 2.6.0
	 */
	protected $position2;

	/**
	 * @var int
	 * @since 2.6.0
	 */
	protected $price1;

	/**
	 * @var int
	 * @since 2.6.0
	 */
	protected $price2;

	/**
	 * @var int
	 * @since 2.6.0
	 */
	protected $price3;

	/**
	 * @var int
	 * @since 2.6.0
	 */
	protected $price4;

	/**
	 * @var int
	 * @since 2.8.0
	 */
	protected $quantity;

	/**
	 * saveCartWithProductVariantCest constructor.
	 * @since 2.6.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		$this->product1 = 'cartCestProduct1' . $this->faker->randomNumber();
		$this->sku1 = 'SKU1' . $this->faker->randomNumber();
		$this->category1 = 'cartCestCategory1' . $this->faker->randomNumber();
		$this->company = 'cartCestCustomerCompany' . $this->faker->randomNumber();
		$this->vendor = 'cartCestVendorCompany' . $this->faker->randomNumber();
		$this->employeeWithLogin = 'cartCestEmployee' . $this->faker->randomNumber();
		$this->cart = 'Test save cart' . $this->faker->bothify('###???');

		//setup attribute
		$this->nameAttributeFirst = 'Color';
		$this->nameAttributeSecond = 'Size';
		$this->attributeType = null;

		$this->settingCartCheckout =
			[
				'addToCart'                     => 'Modal',
				'cartBy'                        => 'By Quantity',
				'showImageInCart'               => 'No',
				'showTaxInCart'                 => 'Yes',
				'checkoutRegister'              => 'Registration Optional',
				'guestUserDefault'              => "guest",
				'checkoutMode'                  => 'Default',
				'showImageProductCheckout'      => 'No',
				'showStockPresent'              => 'Semaphore',
				'enableShipping'                => 'No',
				'timeShipping'                  => 'Hours',
				'clearCartBeforeAddFavourite'   => 'Yes',
				'redirectAfterAdd'              => 'No',
				'checkoutRedirect'              => 'Cart',
				'invoiceMail'                   => 'Yes',
				'saveToCartBy'                  => 'Add to cart'
			];

		//setting vat
		$this->vatSetting =
			[
				'defaultCurrency'       => "Euro",
				'currencySymbol'        => '€',
				'currencySeparator'     => ",",
				'showPrice'             => 'Yes',
				'outletProduct'         => 'No',
				'lowestProduct'         => 'No',
				'offSystem'             => 'Yes',
				'vat'                   => 'Vendor',
				'calculation'           => 'Payment',
				'useTax'                => 'No'
			];

		//setup value for attribute
		$this->status = 'Publish';
		$this->valueSize = $this->faker->bothify('SizeFirst ?##?');
		$this->valueSizeSecond = $this->faker->bothify('SizeSecond ?##?');
		$this->valueColorRed = "Red";
		$this->valueColorGreen = "Green";
		$this->defaultSelect = 'No';
		$this->position1 = 1;
		$this->position2 = 2;
		$this->quantity = $this->faker->numberBetween(1, 10);

		//setup price of attribute
		$this->price1 = $this->faker->numberBetween(1, 100);
		$this->price2 = $this->faker->numberBetween(1, 100);
		$this->price3 = $this->faker->numberBetween(1, 100);
		$this->price4 = $this->faker->numberBetween(1, 100);
	}

	/**
	 * @param redshopb2b $I
	 * @param \Codeception\Scenario       $scenario
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function prepare(redshopb2b $I, \Codeception\Scenario $scenario)
	{
		$I->doAdministratorLogin();
		$I = new AdministratorSteps($scenario);
		$I->vatSetting($this->settingCartCheckout);
		$I->vatSetting($this->vatSetting);

		$I->doFrontEndLogin();
		$I->wantTo('Create all data for checking');
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

		$I->amGoingTo('Create categories to be used by the Checkout Process');
		$I->createRedshopbCategory($this->category1, "- ($this->vendor) $this->vendor");

		$I->amGoingTo('Create products to be used by the Checkout Process');
		$I = new ProductSteps($scenario);
		$I->create($this->product1, $this->sku1, $this->category1, 20,
			20, "($this->vendor) $this->vendor", 'save&close');

		$I->createRedshopbUserEmployeeWithLogin(
			$this->employeeWithLogin,
			$this->faker->email,
			"- - ($this->company) $this->company"
		);

		$I->addCreditToEmployeeWithLogin($this->employeeWithLogin, 'Euro', 10000);
	}

	/**
	 * @param ProductSteps $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function createAttribute(ProductSteps $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantTo('Product creation attribute inside this Product in Frontend');
		$I->createAttribute($this->product1, $this->nameAttributeFirst, $this->attributeType);
		$I->createAttribute($this->product1, $this->nameAttributeSecond, $this->attributeType);
	}

	/**
	 * @param ProductSteps $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function createAttributeValue(ProductSteps  $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantTo('Product creation attribute value inside this Product in Frontend');
		$I->createAttributeValue($this->product1, $this->position2, $this->valueSize, $this->valueSize, $this->defaultSelect, $this->status);
		$I->createAttributeValue($this->product1, $this->position2, $this->valueSizeSecond, $this->valueSizeSecond, $this->defaultSelect, $this->status);
		$I->createAttributeValue($this->product1, $this->position1, $this->valueColorRed, $this->valueColorRed, $this->defaultSelect, $this->status);
		$I->createAttributeValue($this->product1, $this->position1, $this->valueColorGreen, $this->valueColorGreen, $this->defaultSelect, $this->status);
	}

	/**
	 * @param ProductSteps $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function generateCombinations(ProductSteps $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantTo('Product combination in Frontend');
		$I->generateCombinations($this->product1, $this->nameAttributeFirst, $this->nameAttributeSecond);
	}

	/**
	 * @param ProductSteps $I
	 * @throws Exception
	 * @since 2.6.0
	 */
	public function editPriceAttribute(ProductSteps $I)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantTo('Edit price attribute in Frontend');
		$I->editPriceAttribute($this->product1, $this->price1, $this->price2, $this->price3, $this->price4);
	}

	/**
	 * @param SaveCartWithProductVariant $I
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function saveCartWithProductItem(SaveCartWithProductVariant $I)
	{
		$I->doFrontEndLogin($this->employeeWithLogin, $this->employeeWithLogin);
		$I->wantTo('Test save cart with product variants');
		$I->saveCartWithProductVariant($this->category1, $this->product1, $this->cart, $this->nameAttributeFirst, $this->nameAttributeSecond, $this->valueColorRed, $this->valueSize, $this->price1, $this->quantity, $this->vatSetting);
		$I->deleteSaveCart($this->cart);
	}

	/**
	 * @param redshopb2b $I
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->am('Administrator');
		$I->doFrontEndLogin();
		$I->wantTo('I remove the data generated by the test that is not anymore needed');
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}
