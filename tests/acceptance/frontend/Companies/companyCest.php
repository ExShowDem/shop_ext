<?php
use Step\Frontend\CompanySteps as CompanySteps;

class companyCest
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
	 * @since 2.8.0
	 */
	protected $nameSecond;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $customerNumber;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $customerNumberSecond;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $city;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $postcode;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $address;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $country;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $editName;

	/**
	 * @var string
	 * @since 2.8.0
	 */
	protected $phone;

	/**
	 * companyCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->name = 'companyCest Company' . $this->faker->randomNumber();

		$this->nameSecond = 'companyCest Company second' . $this->faker->randomNumber();

		$this->customerNumber = 'CustomerNumber' . $this->faker->randomNumber();

		$this->customerNumberSecond = 'CustomerNumberSecond' . $this->faker->randomNumber();

		$this->city = 'companyCest Cty' .$this->faker->city;

		$this->postcode = 'companyCest PostCode' .$this->faker->postcode;

		$this->address = 'companyCest Address' .$this->faker->address;

		$this->company = 'Main Company';

		$this->country = 'companyCest Country' .$this->faker->country;

		$this->editName = $this->name.'Edit';

		$this->phone = $this->faker->phoneNumber;
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
	 * @param CompanySteps $I
	 * 
	 * Method for create update and delete company
	 * @throws Exception
	 */
	public function create(CompanySteps $I)
	{
		$I->wantToTest('Company creation in Frontend');
		$I->create($this->name,$this->city,$this->postcode, $this->address, $this->customerNumber, $this->company, $this->country);
		$I->wait(1);
		$I->doFrontendLogout();
	}

	/**
	 * @param \Step\Frontend\CompanySteps $I
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function createCompanyWithPhone(CompanySteps $I)
	{
		$I->wantToTest('Company creation with phone number in Frontend');
		$I->createCompanyWithPhoneNumber($this->nameSecond, $this->city, $this->postcode, $this->address, $this->customerNumberSecond, $this->company, $this->country, $this->phone);
		$I->doFrontendLogout();
	}
	
	/**
	 * @param   CompanySteps $client Edit company
	 * @return void
	 * @throws Exception
	 */
	public function editCompany(CompanySteps $client)
	{
		$client->wantToTest('Edit name of company');
		$client->edit($this->name,$this->editName);
		$client->doFrontendLogout();
	}

	/**
	 * @param   CompanySteps $client delete Company and Main company
	 * @return void
	 * @throws Exception
	 */
	public function deleteData(CompanySteps $client)
	{
		$client->wantToTest('Delete company');
		$client->delete($this->editName,'company');
		$client->delete($this->nameSecond,'company');

		$client->wantToTest('Delete main company');
		$client->delete($this->name,'main');
		$client->doFrontendLogout();
	}

	/**
	 * @param   CompanySteps $I Check create company with missing data
	 * @return void
	 * Method check create company with missing fields
	 * @throws Exception
	 */
	public function createMissingData(CompanySteps $I)
	{
		$I->wantToTest('Company creation missing at Company in Frontend');
		$I->createMissingData($this->name,$this->city,$this->postcode, $this->address, $this->customerNumber,
			$this->company, $this->country,'company');

		$I->wantToTest('Company creation missing name in Frontend');
		$I->createMissingData($this->name,$this->city,$this->postcode, $this->address, $this->customerNumber, $this->company, $this->country,'name');

		$I->wantToTest('Company creation missing at customer Number in Frontend');
		$I->createMissingData($this->name,$this->city,$this->postcode, $this->address, $this->customerNumber, $this->company, $this->country,'customerNumber');

		$I->wantToTest('Company creation missing at address in Frontend');
		$I->createMissingData($this->name,$this->city,$this->postcode, $this->address, $this->customerNumber, $this->company, $this->country,'address');

		$I->wantToTest('Company creation missing at zip in Frontend');
		$I->createMissingData($this->name,$this->city,$this->postcode, $this->address, $this->customerNumber, $this->company, $this->country,'zip');

		$I->wantToTest('Company creation missing at city in Frontend');
		$I->createMissingData($this->name,$this->city,$this->postcode, $this->address, $this->customerNumber, $this->company, $this->country,'city');
	}
}
