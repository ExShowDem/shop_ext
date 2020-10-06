<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Frontend\AllDiscountsSteps as AllDiscountsSteps;
use Step\Frontend\ProductSteps as ProductSteps;
class allDiscountsCest
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
	protected $product;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $sku;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $vendor;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $vendorNumber;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $companyNumber;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $newProduct;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $newSku;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $prices;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $percentDiscount;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $totalDiscount;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $newPercentDiscount;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $newTotalDiscount;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $discountType;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $salesType;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $status;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $currency;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $addressCompany;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $postcode;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $city;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $country;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $companyMain;

	/**
	 * allDiscountsCest constructor.
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		
		$this->product = 'allDiscounts Product' . $this->faker->randomNumber();
		
		$this->category = 'allDiscounts Category' . $this->faker->randomNumber();
		
		$this->sku = $this->faker->bothify('SKU ??##??');

		$this->vendor = 'allDiscounts Vendor' . $this->faker->randomNumber();
		
		$this->vendorNumber = 'vendor' . $this->faker->randomNumber();
		
		$this->company = 'allDiscounts Company' . $this->faker->randomNumber();
		
		$this->companyNumber = 'company' . $this->faker->randomNumber();

		$this->newProduct = 'new Product allDiscounts' . $this->faker->randomNumber();
		
		$this->newSku = $this->faker->bothify('New SKU ??##??');

		$this->prices = rand(1,200);

		$this->percentDiscount = rand(1, 10);

		$this->totalDiscount = rand(1, 10);

		$this->newPercentDiscount = rand(1, 10);

		$this->newTotalDiscount = rand(1, 10);

		$this->discountType = 'Product';

		$this->salesType = 'All Debtor';

		$this->status = 'Published';

		$this->currency = 'Euro';

		//company
		$this->addressCompany = 'companyCest Address' .$this->faker->address;

		$this->postcode = 'companyCest PostCode' .$this->faker->postcode;

		$this->city = 'companyCest Cty' .$this->faker->city;

		$this->country = 'Denmark';

		$this->companyMain = 'Main Company';
	}

	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doFrontEndLogin();
	}

	/**
	 * @param AllDiscountsSteps $I
	 * @throws Exception
	 */
	public function create(AllDiscountsSteps $I, \Codeception\Scenario $scenario)
	{
		$I->amGoingTo('Create a company to be used as customer vendor');
		$I->createRedshopbCompany($this->vendor, $this->vendorNumber, $this->addressCompany, $this->postcode, $this->city, $this->country, $this->companyMain);

		$I->amGoingTo('Create a company to be used as customer company');
		$I->createRedshopbCompany($this->company, $this->companyNumber, '14 Phan Ton', '95000', 'Ho Chi Minh', 'Viet Nam', '- ' . $this->vendor);

		$I->amGoingTo('Create a category to be used by the Discount');
		$I->createRedshopbCategory($this->category, '- (' . $this->vendorNumber . ') '. $this->vendor);

		$I->amGoingTo('Create a product to be used by the Discount');
		$ownerCompanyProduct  = "($this->vendorNumber) $this->vendor";
		$I = new ProductSteps($scenario);
		$I->create($this->product, $this->sku, $this->category, $this->prices, $this->prices, $ownerCompanyProduct, 'save&close');

		$I->amGoingTo('Create a product to be used by the Discount');
		$ownerCompanyProduct  = "($this->vendorNumber) $this->vendor";
		$I = new ProductSteps($scenario);
		$I->create($this->newProduct, $this->newSku, $this->category, $this->prices, $this->prices, $ownerCompanyProduct, 'save&close');

		$I->doFrontendLogout();
	}

	/**
	 * @param AllDiscountsSteps $I
	 * @throws Exception
	 */
	public function discountPercent(AllDiscountsSteps $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Discount creation in Frontend');

		$I->amGoingTo('Create all discount with discount application is percent to be used by the Discount');
		$I->createDiscountPercent($this->product, $this->percentDiscount, $this->discountType, $this->salesType, $this->status, $this->currency);

		$I->amGoingTo('Edit all discount to be used by the Discount');
		$I->editDiscountPercent($this->newProduct, $this->newPercentDiscount);

		$I->amGoingTo('Delete all discount');
		$I->deleteDiscount();

		$I->doFrontendLogout();
	}

	/**
	 * @param AllDiscountsSteps $I
	 * @throws Exception
	 */
	public function discountTotal(AllDiscountsSteps $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Discount creation in Frontend');

		$I->amGoingTo('Create all discount with discount application is total to be used by the Discount');
		$I->createDiscountTotal($this->product, $this->totalDiscount, $this->discountType, $this->salesType, $this->status, $this->currency);
		$I->doFrontendLogout();
	}

	/**
	 * @param AllDiscountsSteps $I
	 * @throws Exception
	 */
	public function editTotal(AllDiscountsSteps $I)
	{
		$I->amGoingTo('Edit all discount to be used by the Discount');
		$I->editDiscountTotal($this->newProduct, $this->newTotalDiscount);

		$I->amGoingTo('Delete all discount');
		$I->deleteDiscount();

		$I->doFrontendLogout();
	}

	/**
	 * @param \Step\Acceptance\redshopb2b $I
	 * @throws Exception
	 */
	public function cleanUp(\Step\Acceptance\redshopb2b $I)
	{
		$I->am('Administrator');
		$I->comment('I remove the data generated by the test that is not anymore needed');
		$I->deleteRedshopbCompany($this->company);
		$I->deleteRedshopbCompany($this->vendor);
	}
}
