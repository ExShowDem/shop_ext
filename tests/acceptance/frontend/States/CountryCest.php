<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019  Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Frontend\CountrySteps as CountrySteps;
class CountryCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.3.0
	 */
	protected $fake;

	/**
	 * @var array
	 * @since 2.3.0
	 */
	protected $country;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $postcode;

	/**
	 * @var string
	 * @since 2.3.0
	 */
	protected $city;

	/**
	 * CountryCest constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct()
	{
		$this->fake = Faker\Factory::create();
		$this->country = array();
		$this->country['name'] = $this->fake->bothify('CountryName ?##?');
		$this->country['code2'] = $this->fake->bothify('??');
		$this->country['code3'] = $this->fake->bothify('?#?');
		$this->country['numberCode'] = $this->fake->numberBetween(1,10000);
		$this->country['euro'] = 'No';
		$this->company = $this->fake->bothify('Company ?##?');
		$this->country['company'] = $this->company;
		$this->postcode = $this->fake->postcode;
		$this->city = $this->fake->city;
	}

	/**
	 * @param CountrySteps $client
	 *
	 * @return void
	 * @since 2.3.0
	 * @throws Exception
	 */
	public function _before(CountrySteps $client)
	{
		$client->doFrontEndLogin();
	}

	/***
	 * @param CountrySteps $I
	 *
	 * @return void
	 * @since 2.3.0
	 * @throws Exception
	 */
	public function create(CountrySteps $I)
	{
		$I->createRedshopbCompany(
			$this->country['company'],
			$this->country['company'],
			'address',
			$this->postcode,
			$this->city,
			'Denmark',
			'Main Company'
		);

		$this->country['company'] = "- ($this->company) $this->company";
		$I->createCountry($this->country);
	}

	/**
	 * @param CountrySteps $I
	 *
	 * @return void
	 * @since 2.3.0
	 * @throws Exception
	 */
	public function countryAlreadyCode(CountrySteps $I)
	{
		$I->wantToTest('Create country with all case already');
		$I->countryAlreadyCode($this->country);
	}

	/**
	 * @param CountrySteps $I
	 *
	 * @return void
	 * @since 2.3.0
	 * @throws Exception
	 */
	public function delete(CountrySteps $I)
	{
		$I->wantTo('Delete Country ');
		$I->delete($this->country);
	}

	/**
	 * @param CountrySteps $I
	 *
	 * @return void
	 * @since 2.3.0
	 * @throws Exception
	 */
	public function missing(CountrySteps $I)
	{
		$I->wantTo('Create with all case missing');
		$I->countryMissing($this->country);
		$I->deleteRedshopbCompany($this->company);
	}
}