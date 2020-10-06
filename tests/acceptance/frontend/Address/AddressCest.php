<?php
/**
 * @package     Aesir-ec
 * @subpackage  Cest Address
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/
use Step\Frontend\Address\Address as AddressStep;
use Step\Frontend\UserSteps;
use Step\Frontend\DepartmentSteps;
class AddressCest
{
	/**
	 * @var
	 * @since 2.5.0
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $address = array();

	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $addressNew = array();

	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $addressSaveClose = array();

	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $company = array();

	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $department = array();

	/**
	 * @var array
	 * @since 2.5.0
	 */
	protected $EmployeeWithLogin = array();

	/**
	 * AddressCest constructor.
	 * @since 2.5.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		$this->company['name'] = $this->faker->bothify('Company ?##?');

		$this->EmployeeWithLogin =
			[
				'name' => $this->faker->bothify('UserAddress ?##?'),
				'phone' => $this->faker->phoneNumber,
				'address' => $this->faker->bothify('UserAddress ?##?'),
				'country' => 'Aruba',
				'city' =>'Ho Chi Minh',
				'zip' => $this->faker->postcode,
				'role' => '05 :: Employee with login',
				'hasmail' => 'Yes',
				'email' => $this->faker->email,
				'sendMail' => 'Yes',
				'company' => '- (' . $this->company['name'] . ') ' . $this->company['name'],
				'a_name' => $this->faker->name(),
				'a_address' => $this->faker->address,
				'a_second' => $this->faker->bothify('addressSecond ?##?'),
				'a_zip' => $this->faker->postcode,
				'a_city' => $this->faker->city,
				'a_country' => 'Vietnam',
				'a_phone' => $this->faker->phoneNumber,
				'a_cphone' => $this->faker->phoneNumber
			];

		$this->department =
			[
				'name' => $this->faker->bothify('depart ?##?'),
				'number' => $this->faker->bothify('depart number ?##?'),
				'nameSecond' =>$this->faker->bothify('depart Second ?##?'),
				'company' => '- (' . $this->company['name'] . ') ' . $this->company['name'],
				'address' => $this->faker->bothify('address ?##??'),
				'zip' => $this->faker->numberBetween(1,1000),
				'city' => $this->faker->city,
				'country' => 'Denmark',
				'status' => 'Publish'
			];

		$this->address =
			[
				'name' => $this->faker->bothify('NameAddress ?##?'),
				'name_second' => $this->faker->bothify('secondName'),
				'address' => $this->faker->bothify('address'),
				'address_second' => $this->faker->bothify('addressSecond'),
				'code' => $this->faker->postcode,
				'city' => $this->faker->city,
				'email' => $this->faker->email,
				'phone' => $this->faker->phoneNumber,
				'country' => 'Denmark',
				'entity_type' => 'Employee',
				'entity_name' => $this->EmployeeWithLogin['name']
			];

		$this->addressNew = $this->address;
		$this->addressNew['name'] = $this->faker->bothify('updateName ?###?');

		$this->addressSaveClose = $this->addressNew;
		$this->addressSaveClose['phone'] = $this->faker->phoneNumber;
	}

	/**
	 * @param AddressStep $client
	 * @param $scenario
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function prepare(AddressStep $client, $scenario)
	{
		$client->doFrontEndLogin();
		$client->wantToTest('Create the new company');
		$client->createRedshopbCompany(
			$this->company['name'],
			$this->company['name'],
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);

		$client = new UserSteps($scenario);
		$client->wantToTest('Create new User belong company as step above');
		$client->createUserRole($this->EmployeeWithLogin);

		$client->doFrontendLogout();
		$client->doFrontEndLogin();
		$client->wantToTest('Create new department belong company as step above');
		$client = new DepartmentSteps($scenario);
		$client->createDepartment($this->department);

		$client = new AddressStep($scenario);
		$client->wantToTest('Create new Address for Employee');
		$client->create($this->address);

		$client->wantToTest('Edit Name for Address');
		$client->edit($this->address, $this->addressNew);

		$client->wantToTest('Edit Name for Address');
		$client->edit($this->addressNew, $this->addressSaveClose);

		$client->wantToTest('Delete address');
		$client->deleteAddress($this->addressNew);

		$client->wantToTest('Create with save and close button');
		$client->create($this->address, 'save&close');
		$client->deleteAddress($this->address);

		$client->wantToTest('Create and delete Address for Company');
		$this->address['entity_type'] = 'Company';
		$this->address['entity_name'] = '- ('.$this->company['name'].') ' . $this->company['name'];

		$client->wantToTest('Create new address for company');
		$client->create($this->address);
		$client->deleteAddress($this->address);

		$client->wantToTest('Create and delete Address for Department');
		$this->address['entity_type'] = 'Department';
		$this->address['entity_name'] = $this->department['name']. ' ('.$this->company['name'].')';
		$client->wantToTest('Create new address for department');
		$client->create($this->address);
		$client->deleteAddress($this->address);

		$client->wantToTest('Create new address then clicks on Cancel button');
		$client->create($this->address, 'cancel');

		$client->wantToTest('Check missing case');
		$client->createMissingValue($this->address);

		$client->wantToTest('Delete Company');
		$client->deleteRedshopbCompany($this->company['name']);
	}
}