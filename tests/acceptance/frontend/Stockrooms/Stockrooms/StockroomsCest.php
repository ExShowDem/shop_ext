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
 * Class StockroomsCest
 * @since 2.6.0
 */
class StockroomsCest
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
	protected $stockRooms;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $stockRoomsEdit;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $stockRoomsMissingName;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $stockRoomsMissingMinDeliveryTime;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $stockRoomsMissingLowerLevel;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $stockRoomsWithMaxSmallerThanMinDelivery;

	/**
	 * @var array
	 * @since 2.6.0
	 */
	protected $stockroomsWithUpperSmallerThanLowerLevel;

	/**
	 * StockroomsCest constructor.
	 * @since 2.6.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->company = 'Company Stockrooms' . $this->faker->randomNumber();

		$this->stockRooms =
			[
				'name'              => $this->faker->bothify('Name Stockrooms ?##?'),
				'company'           => $this->company,
				'minDeliveryTime'   => $this->faker->numberBetween(1,10),
				'maxDeliveryTime'   => $this->faker->numberBetween(10,20),
				'lowerLevel'        => $this->faker->numberBetween(1,100),
				'upperLevel'        => $this->faker->numberBetween(100,200)
			];

		$this->stockRoomsEdit =
			[
				'name'              => $this->stockRooms['name'],
				'nameEdit'          => $this->faker->bothify('Name Stockrooms Edit ?##?'),
				'company'           => '(main) Main Company',
			];

		$this->stockRoomsMissingName =
			[
				'company'           => $this->company,
				'minDeliveryTime'   => $this->faker->numberBetween(1,10),
				'maxDeliveryTime'   => $this->faker->numberBetween(10,20),
				'lowerLevel'        => $this->faker->numberBetween(1,100),
				'upperLevel'        => $this->faker->numberBetween(100,200)
			];

		$this->stockRoomsMissingMinDeliveryTime =
			[
				'name'              => $this->faker->bothify('Name Stockrooms Missing Min Delivery Time ?##?'),
				'company'           => $this->company,
				'maxDeliveryTime'   => $this->faker->numberBetween(10,20),
				'lowerLevel'        => $this->faker->numberBetween(1,100),
				'upperLevel'        => $this->faker->numberBetween(100,200)
			];

		$this->stockRoomsMissingLowerLevel =
			[
				'name'              => $this->faker->bothify('Name Stockrooms Missing Lower Level ?##?'),
				'company'           => $this->company,
				'minDeliveryTime'   => $this->faker->numberBetween(1,10),
				'maxDeliveryTime'   => $this->faker->numberBetween(10,20),
				'upperLevel'        => $this->faker->numberBetween(100,200)
			];

		$this->stockRoomsWithMaxSmallerThanMinDelivery =
			[
				'name'              => $this->faker->bothify('Name Stockrooms With Max Smaller Than Min Delivery ?##?'),
				'minDeliveryTime'   => $this->faker->numberBetween(100,200),
				'maxDeliveryTime'   => $this->faker->numberBetween(1,50),
				'lowerLevel'        => $this->faker->numberBetween(1,50),
				'upperLevel'        => $this->faker->numberBetween(100,200)
			];

		$this->stockroomsWithUpperSmallerThanLowerLevel =
			[
				'name'              => $this->faker->bothify('Name Stockrooms With Upper Smaller Than Lower Level ?##?'),
				'minDeliveryTime'   => $this->faker->numberBetween(1,50),
				'maxDeliveryTime'   => $this->faker->numberBetween(100,200),
				'lowerLevel'        => $this->faker->numberBetween(100,200),
				'upperLevel'        => $this->faker->numberBetween(1,50)
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
		$I->wantToTest('Check all cases for stockrooms in frontend');
		$I->createStockrooms($this->stockRooms, 'save');
		$I->editStockrooms($this->stockRoomsEdit);
		$I->createMissingName($this->stockRoomsMissingName);
		$I->createMissingMinDeliveryTime($this->stockRoomsMissingMinDeliveryTime);
		$I->createMissingLowerLevel($this->stockRoomsMissingLowerLevel);
		$I->createStockroomsWithMaxSmallerThanMinDelivery($this->stockRoomsWithMaxSmallerThanMinDelivery);
		$I->createStockroomsWithUpperSmallerThanLowerLevel($this->stockroomsWithUpperSmallerThanLowerLevel);
		$I->deleteStockrooms($this->stockRoomsEdit['nameEdit']);
		$I->deleteStockrooms($this->stockRoomsMissingMinDeliveryTime['name']);
		$I->deleteStockrooms($this->stockRoomsMissingLowerLevel['name']);
		$I->deleteRedshopbCompany($this->company);
	}
}