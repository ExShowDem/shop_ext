<?php
/**
 * @package     Aesir
 * @subpackage  Cest
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Frontend\DebtorGroupsDiscountSteps as DebtorGroupsDiscountSteps;

class debtorGroupsDiscountCest
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
	protected $nameEdit;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $companySecond;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $code;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $ownerCompany;

	/**
	 * debtorGroupsDiscountCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker         = Faker\Factory::create();

		$this->name          = $this->faker->bothify('debtorGroupsDiscountCest ?##?');

		$this->nameEdit      = $this->name . 'Edit';

		$this->company       = $this->faker->bothify('debtorGroupsDiscountCest Company ?##?');

		$this->companySecond = $this->faker->bothify('DebtorGroupsDiscountCestSecond Company ?##?');

		$this->code          = $this->faker->bothify('DebtorCode ?##?');

		$this->ownerCompany  = 'Main';

		$this->companies     = array();
	}

	/**
	 * @param DebtorGroupsDiscountSteps $I
	 * @throws Exception
	 */
	public function _before(DebtorGroupsDiscountSteps $I)
	{
		$I->doFrontEndLogin();
	}

	/**
	 * @param DebtorGroupsDiscountSteps $I
	 * @throws Exception
	 */
	public function create(DebtorGroupsDiscountSteps $I)
	{
		$I->am('Administrator');
		$I->wantToTest('Offer creation in Frontend');
		$I->amGoingTo('Create a Company to be used by the Offer');
		$I->amGoingTo('Create a customer company to be used by the Checkout Process');
		$I->createRedshopbCompany(
			$this->company,
			$this->company,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);
		$this->companies[] = $this->company;

		$I->createRedshopbCompany(
			$this->companySecond,
			$this->companySecond,
			'address',
			$this->faker->postcode . 'edit',
			$this->faker->city,
			'Denmark',
			'Main Company'
		);

		$this->companies[] = $this->companySecond;
		$I->create($this->name, $this->code, $this->ownerCompany, $this->companies);
	}
// Open the code until AESEC-5327 fix
//	/**
//	 * @param DebtorGroupsDiscountSteps $I
//	 *
//	 * @depends create
//	 */
//	public function edit(DebtorGroupsDiscountSteps $I)
//	{
//		$I->wantToTest('Edit Debtor Discount groups save ');
//		$I->editName($this->name, $this->nameEdit, $this->companies, 'save');
//
//		$I->wantToTest('Edit Debtor Discount groups Save Close');
//		$I->editName($this->nameEdit, $this->name, $this->companies, 'save&close');
//
//		$I->wantToTest('Edit Debtor Discount groups Save New');
//		$I->editName($this->name, $this->nameEdit, $this->companies, 'save&new');
//	}

	/**
	 * @param DebtorGroupsDiscountSteps $I
	 * @throws Exception
	 */
	public function createWithWrongCase(DebtorGroupsDiscountSteps $I)
	{
		$I->comment('Check Missing Companies');
		$I->createWithWrongCase($this->name, $this->code, $this->ownerCompany, $this->companies);

		$I->wantToTest('Check code already taken');
		$I->debtorGroupsAlreadyTaken($this->name, $this->code, $this->ownerCompany, $this->companies);
	}

	/**
	 * @param DebtorGroupsDiscountSteps $I
	 *
	 * @throws Exception
	 */
	public function delete(DebtorGroupsDiscountSteps $I)
	{
		$I->comment('Delete Debtor Groups Discount');
		$I->delete($this->name);

		$I->comment('Delete company Name');
		$I->deleteRedshopbCompany($this->company);

		$I->comment('Delete Company Second');
		$I->deleteRedshopbCompany($this->companySecond);
	}
}