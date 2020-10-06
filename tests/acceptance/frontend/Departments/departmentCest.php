<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Frontend\DepartmentSteps as DepartmentSteps;
class departmentCest
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
	protected $department;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $name;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $customerNumber;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameEdit;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $company;

	/**
	 * departmentCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		
		$this->department = array();
		
		$this->name = 'departmentCest Department' . rand(1, 9999);
		
		$this->customerNumber = 'CustomerNumber' . rand(1, 9999);
		
		$this->nameEdit = $this->faker->bothify('Edit name ?##?');

		//create company
		$this->company = $this->faker->bothify('Company for department ?##?');
		
		//add value inside department array
		$this->department['number'] = $this->customerNumber;
		
		$this->department['name'] = $this->name;
		
		$this->department['nameSecond'] = $this->faker->bothify('departement Second ?##?');
		
		$this->department['company'] = $this->company;
		
		$this->department['address'] = $this->faker->address;
		
		$this->department['addressSecond'] = $this->faker->address;
		
		$this->department['zip'] = $this->faker->numberBetween(1,1000);
		
		$this->department['city'] = $this->faker->city;
		
		$this->department['country'] = 'Denmark';
		
		$this->department['status'] = 'Publish';
	}

	/**
	 * @param DepartmentSteps $I
	 * @throws Exception
	 */
	public function _before(DepartmentSteps $I)
	{
		$I->doFrontEndLogin();
	}

	/**
	 * @param DepartmentSteps $I
	 *
	 * @throws Exception
	 * Create department will touch for all fields
	 */
	public function create(DepartmentSteps $I)
	{
		$I->amGoingTo('Create a vendor company to be used by the Checkout Process');
		$I->createRedshopbCompany(
			$this->department['company'],
			$this->department['company'],
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);
		$I->doFrontendLogout();
	}

	/**
	 * @param   string   DepartmentSteps $client create new department
	 * @throws Exception
	 */
	public function createDepartment(DepartmentSteps $client)
	{
		
		$client->am('Administrator');
		$client->wantToTest('Department creation in Frontend');
		$this->department['company'] = '- (' . $this->department['company'] . ') ' .$this->department['company'];
		$client->comment($this->department['company']);
		$client->createDepartment($this->department);
		$client->doFrontendLogout();
	}

	/**
	 * @depends create
	 * @throws Exception
	 */
	public function edit(DepartmentSteps $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Department edit in Frontend');
		$I->editDepartment($this->department['name'], $this->nameEdit);
        $I->doFrontendLogout();
	}
	
	/**
	 * @depends edit
	 * @throws Exception
	 */
	public function delete(DepartmentSteps $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Delete a department in Frontend');
		$I->deleteDepartment($this->nameEdit);
		$I->wantToTest('Delete company');
		$I->deleteRedshopbCompany($this->company);
        $I->doFrontendLogout();
	}
	
	/**
	 * @param DepartmentSteps $client Check bad case
	 * @return void
	 * @since 2.0.3
	 */
	public function missing(DepartmentSteps $client)
	{
		$client->am('Check missing for all cases');
		$client->createMissing($this->department);
	}
	
	/**
	 * @param DepartmentSteps $client
	 * @return void
	 * @internal param DepartmentSteps $I Checking missing name
	 */
	public function missingName(DepartmentSteps $client)
	{
		$client->am('Check missing name department');
		$client->createMissingName($this->department);
	}
}
