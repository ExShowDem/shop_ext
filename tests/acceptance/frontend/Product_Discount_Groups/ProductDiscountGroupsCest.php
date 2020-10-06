<?php
use \Step\Acceptance\redshopb2b as redshopb2b;
use \Step\Frontend\ProductDiscountGroupsSteps as ProductDiscountGroupsSteps;
use \Step\Frontend\ProductSteps as ProductSteps;
class ProductDiscountGroupsCest
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
	protected $name;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $product1;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $product2;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $sku1;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $sku2;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $price1;

	/**
	 * @var int
	 * @since 2.4.0
	 */
	protected $price2;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameDiscount;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameDiscountEdit;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $codeDiscount;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $products;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $mainWarehouse;

	/**
	 * ProductDiscountGroupsCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		
		$this->name = $this->faker->bothify('DiscountGroups Product ?##?');
		
		$this->category = $this->faker->bothify('DiscountGroups Category ?##?');
		
		$this->product1 = $this->faker->bothify('DiscountGroups Product 1 ?##?');
		
		$this->product2 = $this->faker->bothify('2DiscountGroups Product 2 ?##?');
		
		$this->sku1 = $this->faker->bothify('productSKU1 ?##?');
		
		$this->sku2 = $this->faker->bothify('productSKU2 ?##?');
		
		$this->price1 = $this->faker->numberBetween(1,100);
		
		$this->price2 = $this->faker->numberBetween(100,1000);

		//create product discount groups
		$this->nameDiscount = $this->faker->bothify('DiscountGroups Name?##?');
		
		$this->nameDiscountEdit = $this->nameDiscount.'Edit';
		
		$this->codeDiscount = $this->faker->bothify('codeDiscount ?##?');
		
		$this->products =  array();

		$this->mainWarehouse = 'Main Warehouse';
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 */
	public function _before(redshopb2b $I)
	{
		$I->doFrontEndLogin();
	}

	/**
	 * @param ProductDiscountGroupsSteps $I
	 * @param $scenario
	 * @throws Exception
	 */
	public function createData(ProductDiscountGroupsSteps $I, $scenario)
	{
		$I->am('Administrator');
		$I->wantToTest('Price creation in Frontend');
		$I->createRedshopbCategory($this->category);
		$I = new ProductSteps($scenario);
		$I->create($this->product1, $this->sku1, $this->category, $this->price1, $this->price1, null, 'save&close');
		$this->products[] = $this->sku1;

		$I = new ProductDiscountGroupsSteps($scenario);
		$I->wantTo('Create new discount group');
		$I->create($this->mainWarehouse, $this->nameDiscount, $this->codeDiscount, $this->products);
	}

	/**
	 * @param ProductDiscountGroupsSteps $client
	 * @throws Exception
	 */
	public function updateDiscount(ProductDiscountGroupsSteps $client)
	{
		$client->comment('Update name of this product discount');
		$client->update($this->nameDiscount, $this->nameDiscountEdit);
	}
	
	/**
	 * @param ProductDiscountGroupsSteps $I
	 * @throws Exception
	 */
	public function clearUp(ProductDiscountGroupsSteps $I)
	{
		$I->comment('Delete discount');
		$I->delete($this->nameDiscountEdit);
		
		$I->comment('Delete product1');
		$I->deleteRedshopbProduct($this->product1);

		$I->comment('Delete Category');
		$I->deleteRedshopbCategory($this->category);
	}
}