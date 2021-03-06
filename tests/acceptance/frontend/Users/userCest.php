<?php

use Step\Frontend\UserSteps as UserSteps;
use  Page\Frontend\UserPage as UserPage;
class UserCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.4.0
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $user;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $companyName;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $companyNumber;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $vendor;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $userEdit;

	/**
	 * UserCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		$this->user = array();

		$this->user['name'] = $this->faker->bothify('userCest?##?');
		$this->user['hasmail'] = 'Yes';
		$this->companyName = $this->faker->bothify('userCompany ?##?');;
		$this->user['company'] = $this->companyName;
		$this->user['email'] = $this->faker->email;
		$this->user['sendMail'] = 'No';

		//infor about address of user
		$this->user['a_name'] = $this->faker->name;
		$this->user['a_address'] = $this->faker->address;
		$this->user['a_second'] = $this->faker->bothify('addressSecond ?##?');
		$this->user['a_zip'] = $this->faker->postcode;
		$this->user['a_city'] = $this->faker->city;
		$this->user['a_country'] = 'Denmark';
		$this->user['a_phone'] = $this->faker->phoneNumber;
		$this->user['a_cphone'] = $this->faker->phoneNumber;

		//company information
		$this->companyNumber = $this->faker->bothify('companyNumber ?##?');
		$this->vendor = $this->faker->bothify('vendorNumber ?##?');

		//user edit
		$this->userEdit = array();
		$this->userEdit['name'] = $this->faker->bothify('nameUserEdit ?##?');
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
	 * @param UserSteps $I
	 * @param $scenario
	 * @return void
	 * @since 2.0.3
	 * @throws Exception
	 */
	public function create(UserSteps $I, $scenario)
	{
		$I->comment('Create category');
		
		$I                  = new UserSteps($scenario);
		$userpage           = new UserPage();
		$this->user['role'] = $userpage->setRoleList(5);
		$I->comment($this->user['role']);
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
			$this->companyName,
			$this->companyNumber,
			'Blangstedgaardvej 1',
			'5220',
			'Odense SO',
			'Denmark',
			"- $this->vendor"
		);
		$I->wantToTest('Create new user');
		$this->user['company'] = '- - (' . $this->companyNumber . ') ' . $this->companyName;
		$I->createUserRole($this->user);
	}
	/**
	 * @param   UserSteps $I
	 *
	 * @depends create
	 */
	public function edit(UserSteps $I)
	{
		$I->wantTo('Change role is Employee Role and name');
		$userpage               = new UserPage();
		$this->userEdit['role'] = $userpage->setRoleList(3);
		$I->editUser($this->user['name'], $this->userEdit);
	}
	/**
	 * @param   UserSteps $client Delete user
	 * @depends edit
	 * @return void
	 */
	public function delete(UserSteps $client)
	{
		$client->wantTo('Delete user');
		$client->deleteUser($this->userEdit['name']);
	}

	/**
	 * @param   UserSteps $client Check Missing Create
	 *
	 * @depends delete
	 * @return void
	 */
	public function checkMissingCreate(UserSteps $client)
	{
		$client->wantToTest('Check all cases missing when create user');
		$client->createMissing($this->user);
	}

	/**
	 * @param   UserSteps $client Clear All Data
	 * @depends checkMissingCreate
	 * @return void
	 * @throws Exception
	 */
	public function cleanUp(UserSteps $client)
	{
		$client->am('Administrator');
		$client->comment('I remove the data generated by the test that is not anymore needed');
		$client->deleteRedshopbCompany($this->companyName);
		$client->deleteRedshopbCompany($this->vendor);
	}
}