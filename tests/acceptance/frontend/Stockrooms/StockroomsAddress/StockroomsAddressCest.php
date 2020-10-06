<?php
/**
 * @package     Aesir-ec
 * @subpackage  Cest Stockrooms
 * @copyright   Copyright (C) 2016 - 2019 Aesir-ec. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 **/

use Step\Acceptance\redshopb2b as redshopb2b;
use Step\Frontend\Stockrooms\Stockrooms as StockroomsStep;

/**
 * Class StockroomsAddressCest
 * @since 2.6.0
 */
class StockroomsAddressCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.6.0
	 */
	protected $faker;

	/**
	 * @var string
	 * @since 2.6.0
	 */
	protected $company;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $address;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $addressNew;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $stockRooms;

	/**
	 * StockroomsAddressCest constructor.
	 * @since 2.6.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->company = 'Company Stockrooms' . $this->faker->randomNumber();

		$this->address =
			[
				'name' => $this->faker->bothify('addressName ?##?'),
				'name_second' => $this->faker->bothify('addressSecondName'),
				'address' => $this->faker->bothify('address'),
				'address_second' => $this->faker->bothify('addressSecond'),
				'code' => $this->faker->postcode,
				'city' => $this->faker->city,
				'country' => 'Denmark',
			];

		$this->stockRooms =
			[
				'name'              => $this->faker->bothify('Name Stockrooms ?##?'),
				'company'           => $this->company,
				'minDeliveryTime'   => $this->faker->numberBetween(1,10),
				'maxDeliveryTime'   => $this->faker->numberBetween(10,20),
				'lowerLevel'        => $this->faker->numberBetween(1,100),
				'upperLevel'        => $this->faker->numberBetween(100,200)
			];
	}

	/**
	 * @param \Step\Acceptance\redshopb2b $I
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->doFrontEndLogin();
		$I->amGoingTo('Create a company to be used by the Stockrooms');
		$I->createRedshopbCompany(
			$this->company,
			$this->company,
			'address',
			$this->faker->postcode,
			$this->faker->city,
			'Denmark',
			'Main Company'
		);
		$I->doFrontendLogout();
	}

	/**
	 * @param \Step\Frontend\Stockrooms\Stockrooms $I
	 *
	 * @throws \Exception
	 * @since 2.6.0
	 */
	public function checkAllCaseStockrooms(StockroomsStep $I)
	{
		$I->doFrontEndLogin();
		$I->wantToTest('Check all cases for stockrooms address in frontend');
		$I->createStockrooms($this->stockRooms, 'save');
		$I->createStockroomsAddress($this->stockRooms['name'], $this->address);
		$I->deleteStockrooms($this->stockRooms['name']);
		$I->deleteRedshopbCompany($this->company);
	}
}