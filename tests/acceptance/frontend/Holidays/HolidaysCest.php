<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019  Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Frontend\HolidaySteps as HolidaySteps;

/**
 * Class HolidaysCest
 * @since 2.5.1
 */
class HolidaysCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.5.1
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.5.1
	 */
	protected $holidays;

	/**
	 * @var array
	 * @since 2.5.1
	 */
	protected $holidaysEdit;

	/**
	 * @var array
	 * @since 2.5.1
	 */
	protected $holidaysWrong;

	/**
	 * HolidaysCest constructor.
	 * @since 2.5.1
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->holidays = array();
		$this->holidays['name']      = $this->faker->bothify('HolidaysName ?##?');
		$this->holidays['day']       = $this->faker->numberBetween(1, 28);
		$this->holidays['month']     = $this->faker->numberBetween(1, 12);
		$this->holidays['year']      = '2019';
		$this->holidays['country']   = 'Vietnam';

		$this->holidaysEdit = array();
		$this->holidaysEdit['name']      = $this->holidays['name'];
		$this->holidaysEdit['year']      = '2022';
		$this->holidaysEdit['country']   = 'Denmark';

		$this->holidaysWrong = array();
		$this->holidaysWrong['name']      = $this->faker->bothify('HolidaysName ?##?');
		$this->holidaysWrong['day']       = $this->faker->numberBetween(32, 50);
		$this->holidaysWrong['month']     = $this->faker->numberBetween(13, 20);
	}

	/**
	 * @param \Step\Frontend\HolidaySteps $I
	 * @throws \Exception
	 * @since 2.5.1
	 */
	public function createEditDeleteHolidays(HolidaySteps $I)
	{
		$I->wantTo('Test create, edit and delete Holidays');
		$I->doFrontEndLogin();
		$I->createHolidays($this->holidays);
		$I->createHolidaysMissingName();
		$I->createHolidaysWrongData($this->holidaysWrong);
		$I->editHolidays($this->holidaysEdit);
		$I->deleteHolidays($this->holidays);
	}
}