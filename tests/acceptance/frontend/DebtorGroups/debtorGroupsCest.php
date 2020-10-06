<?php
	/**
	 * @package     Aesir.E-Commerce
	 * @subpackage  Cest
	 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE.txt
	 */
use Step\Frontend\DebtorGroupSteps as DebtorGroupSteps;
class DebtorGroupsCest
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
	protected $debtorGroup;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameEdit ;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $vendor;

	/**
	 * @var
	 * @since 2.4.0
	 */
	protected $debtorEdit;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $vendorNumber;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameCompany;

	/**
	 * DebtorGroupsCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->debtorGroup = array();

		$this->debtorGroup['name']		= $this->faker->bothify('debtorGroupName ?##?');
		
		$this->debtorGroup['code']		= $this->faker->bothify('debtorGroupCode ?##?');
		
		$this->nameEdit                 = $this->debtorGroup['name'].'Edit';
		
		$this->vendor                   = $this->faker->bothify('Vendor ?##?');
		
		$this->debtorEdit['nameEdit']   = $this->faker->bothify('DGroupsEdit ?##?');
		
		$this->debtorEdit['codeEdit']   = $this->faker->bothify('CodeGroupEdit ?##?');
		
		$this->vendorNumber             = $this->faker->bothify('VendorNumber ?##?');
		
		$this->nameCompany = '- ('.$this->vendorNumber .') ' . $this->vendor;
	}

	/**
	 * @param DebtorGroupSteps $client
	 * @throws Exception
	 * @since 2.4.0
	 */
	public function _before(DebtorGroupSteps $client)
	{
		$client->doFrontEndLogin();
	}
	
	/**
	 * @param DebtorGroupSteps $client
	 * @throws Exception
	 */
	public function createUpdateDelete(DebtorGroupSteps $client)
	{
		$client->comment('I want to create new Company');
		$client->createRedshopbCompany($this->vendor, $this->vendorNumber, 'Blangstedgaardvej 1', '5220', 'Odense SO', 'Denmark', 'Main Company');

		$client->comment('I want to create new debtor groups');
		$client->createRedshopbDebtorGroup(
			$this->debtorGroup['name'],
			$this->debtorGroup['code'],
			'Main Company',
			$this->nameCompany
		);

		$client->createRedshopbDebtorGroup(
			$this->debtorEdit['nameEdit'],
			$this->debtorEdit['codeEdit'],
			'Main Company',
			$this->nameCompany
		);

		$client->comment('I want to edit name of debtor group with save button');
		$client->edit($this->debtorGroup['name'],$this->nameEdit,'save');

		$client->comment('I want to edit name of debtor groups with save&close button');
		$client->edit($this->nameEdit, $this->debtorGroup['name'],'save&close');

		$client->comment('I want to edit name of debtor group with save&new button');
		$client->edit($this->debtorGroup['name'],$this->nameEdit,'save&close');

		$client->comment('I want to create debtor for edit code');

		$client->comment('I want to edit with code already ');
		$client->editDebtorGroupReadyCode($this->debtorEdit['nameEdit'], $this->debtorGroup['code']);


		$client->comment('I want to check missing to create  debtor groups');
		$client->createMissing(
			$this->debtorEdit['nameEdit'],
			$this->nameCompany, 'Main Company',
			$this->debtorEdit['codeEdit']);

		$client->comment('Delete debtor Groups');
		$client->deleteRedshopbDebtorGroup($this->nameEdit);
		$client->deleteRedshopbDebtorGroup($this->debtorEdit['nameEdit']);
		$client->deleteRedshopbCompany($this->vendor);
	}
}