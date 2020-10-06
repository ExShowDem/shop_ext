<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Frontend\ManufactureSteps as ManufactureSteps;
class ManufactureCest
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
	protected $nameSaveClose;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameSaveNew;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameEdit;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $status;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $featured;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $category;

	/**
	 * ManufactureCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		
		$this->name = $this->faker->bothify('Manufacture ??##??');
		
		$this->nameSaveClose = $this->faker->bothify('SaveCloseManufacture ??##??');
		
		$this->nameSaveNew = $this->faker->bothify('NewManufacture ??##??');
		
		$this->nameEdit = $this->faker->bothify('Edit ??##??');
		
		$this->status = 'Publish';
		
		$this->featured = 'No';
		
		$this->category = $this->faker->bothify('Category ??##??');
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
	 * @param AcceptanceTester $I
	 * @param $scenario
	 * @return void
	 * @throws Exception
	 */
	public function create(AcceptanceTester $I, $scenario)
	{
		$I->am('Administrator');
		$I->wantToTest('Manufacture creation in Frontend');
		$I = new ManufactureSteps($scenario) ;
		$I->create($this->name, null, $this->status, $this->featured, $this->category,'save&close');
		$I->create($this->nameSaveNew, $this->name, $this->status, $this->featured, $this->category,'save&new');
		$I->doFrontendLogout();
	}

	/**
	 * @depends create
	 * @throws Exception
	 */
	public function delete(AcceptanceTester $I, $scenario)
	{
		$I->am('Administrator');
		$I->wantToTest('Delete manufacture in Frontend');
		$I = new ManufactureSteps($scenario) ;
		$I->delete($this->nameSaveNew);
	}

	/**
	 * @depends create
	 * @param ManufactureSteps $client
	 * @throws Exception
	 */
	public function editDelete(ManufactureSteps $client)
	{
		$client->wantToTest('Edit the manufacture in Frontend');
		$client->edit($this->name, $this->nameEdit);
		$client->wantToTest('Delete the manufacture after edit');
		$client->delete($this->nameEdit);
	}
}