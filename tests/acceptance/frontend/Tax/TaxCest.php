<?php

/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Frontend\TaxSteps as TaxSteps;
class TaxCest
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
	 * @var float
	 * @since 2.4.0
	 */
	protected $taxRate;

	/**
	 * @var float
	 * @since 2.4.0
	 */
	protected $taxRateEdit;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $country;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $company;

	/**
	 * TaxCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		
		$this->name = $this->faker->bothify('Taxes Product ?##?');
		
		$this->nameEdit = $this->name.'Edit';
		
		$this->taxRate = 0.1;
		
		$this->taxRateEdit = 0.2;
		
		$this->country = 'Denmark';
		
		$this->company = '(main) Main Company';
	}
	
	/**
	 * @param TaxSteps $cliebnt
	 * @throws Exception
	 */
	public function _before(TaxSteps $cliebnt)
	{
		$cliebnt->doFrontEndLogin();
	}
	
	/**
	 * @param TaxSteps $client
	 * @throws Exception
	 */
	public function createUpdateDelete(TaxSteps $client)
	{

		$client->comment('Test with save & new button');
		$client->create($this->name, $this->taxRate,$this->country, $this->company, 'save&new');
		$client->doFrontendLogout();
	}

	/**
	 * @param TaxSteps $client
	 * @throws Exception
	 */
	public function editDelete(TaxSteps $client)
	{
		$client->wait(1);
		$client->comment('Edit value of tax rates with save & close button');
		$client->edit($this->name,$this->nameEdit,$this->taxRateEdit,'save&new');

		$client->comment('Edit value of tax rates with Close button');
		$client->edit($this->nameEdit,$this->nameEdit,$this->taxRateEdit,'close');

		$client->comment('Delete Tax rate');
		$client->delete($this->nameEdit);
		$client->doFrontendLogout();
	}
	
	/**
	 * @param TaxSteps $client
	 * @throws Exception
	 */
	public function createUpdateDeleteSave(TaxSteps $client)
	{
		$client->comment('Create new Taxes with save button');
		$client->create($this->name, $this->taxRate,$this->country, $this->company, 'save');
		$client->comment('Edit value of tax rates with save button');
		$client->edit($this->name,$this->nameEdit,$this->taxRateEdit,'save');
		$client->comment('Delete Tax rate');
		$client->delete($this->nameEdit);
		$client->doFrontendLogout();
	}
	
	/**
	 * @param TaxSteps $client
	 * @throws Exception
	 */
	public function createUpdateDeleteSaveClose(TaxSteps $client)
	{
		$client->comment('Test with save & Close button');
		$client->create($this->name, $this->taxRate,$this->country, $this->company, 'save&close');
		$client->comment('Edit value of tax rates with save & close button');
		$client->doFrontendLogout();
	}

	/**
	 * @param TaxSteps $client
	 * @throws Exception
	 */
	public function updateAndClose(TaxSteps $client)
	{
		$client->wait(1);
		$client->edit($this->name,$this->nameEdit,$this->taxRateEdit,'save&close');
		$client->comment('Delete Tax rate');
		$client->delete($this->nameEdit);
		$client->doFrontendLogout();
	}

	/**
	 * @param TaxSteps $client
	 * @throws Exception
	 */
	public function createCancel(TaxSteps $client)
	{
		
		$client->comment('Check Cancel butotn');
		$client->create($this->name, $this->taxRate,$this->country, $this->company, 'cancel');
		$client->doFrontendLogout();
	}
	
	/**
	 * @param TaxSteps $client
	 * @throws Exception
	 */
	public function checkMissing(TaxSteps $client)
	{
		$client->comment('Check missing value');
		$client->checkMissing($this->name, $this->taxRate);
		$client->doFrontendLogout();
	}
}