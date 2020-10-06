<?php
use Step\Frontend\StateSteps as StateSteps;
class StatesCest
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
	protected $vendor;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $name;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $state;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameMissing;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameEdit;

	/**
	 * StatesCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		
		$this->vendor = $this->faker->bothify('StateCompanyCest ?##?');
		
		$this->name = $this->faker->bothify('productCest Product ?##?');
		
		$this->state = array(
			'name' =>$this->name,
			'company' => $this->vendor,
			'alpha2' =>$this->faker->numberBetween(10, 99),
			'alpha3' => $this->faker->numberBetween(100, 999),
			'country' => 'Denmark'
		);
		$this->nameMissing = $this->faker->bothify('productCest Product ?##?');

		$this->nameEdit = $this->faker->bothify('StateEdit?##?');
	}

	/**
	 * @param StateSteps $I
	 * @throws Exception
	 */
	public function _before(StateSteps $I)
	{
		$I->doFrontEndLogin();
	}

	/**
	 * @param StateSteps $I
	 * @throws Exception
	 */
	public function create(StateSteps $I)
	{
		$I->createRedshopbCompany(
			$this->vendor,
			$this->vendor,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);

		$I->comment('Create new State');
		$I->create($this->state);

		$this->state['name'] = $this->nameMissing;

		$I->comment('Check bad case about missing and already');
		$I->checkMissing($this->state);
	}

	/**
	 * @param StateSteps $cliet
	 * @return void
	 * @throws Exception
	 */
	public function editState(StateSteps $cliet)
	{
		$this->state['name'] = $this->name;
		$cliet->comment('Edit name State');
		$cliet->edit($this->state['name'],$this->nameEdit);

		$cliet->comment('Delete state');
		$cliet->delete($this->nameEdit);

		$cliet->comment('Delete company ');
		$cliet->deleteRedshopbCompany($this->vendor);
	}
}