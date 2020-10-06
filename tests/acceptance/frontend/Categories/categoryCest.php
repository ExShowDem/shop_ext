<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Frontend\CategorySteps as CategorySteps;
class categoryCest
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
	protected $description;

	/**
	 * categoryCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		
		$this->name = 'categoryCest Category' . rand(1, 9999);
		
		$this->nameSaveClose = 'cateSaveClose'.rand(100,1000);
		
		$this->nameSaveNew = 'cateSaveNew'.rand(100,1200);
		
		$this->nameEdit = $this->faker->bothify('Category Edit ?##?');
		
		$this->description = 'Description testing';
	}
	
	/**
	 * @param AcceptanceTester $I
	 * @param $scenario
	 * @throws Exception
	 */
	public function create(AcceptanceTester $I, $scenario)
	{
		$I->doFrontEndLogin();
		$I->am('Administrator');
		$I->wantToTest('Category creation in Frontend');
		$I = new CategorySteps($scenario);
		$I->wantTo('Create category update and delete');
		$I->create($this->name,'save');
		$I->wantToTest('Category edit in Frontend');
		$I->create($this->nameSaveClose,'save&close');
		$I->create($this->nameSaveNew,'save&new');
		$I->create($this->name,'cancel');
	}
	
	/**
	 * @param AcceptanceTester $I
	 * @param $scenario
	 * @throws Exception
	 */
	public function changeStatusUnpublishCategory(AcceptanceTester $I,$scenario)
	{
		$I->am('Administrator');
		$I->wantToTest('Category edit in Frontend');
		$I->doFrontEndLogin();
		$I = new CategorySteps($scenario);
		$I->changeStatusCategoryByButton($this->nameSaveNew,'publish');
		$I->changeStatusCategoryByButton($this->nameSaveNew,'unpublish');
		$I->changeStatusCategoryByButton($this->nameSaveNew,'publishState');
		$I->changeStatusCategoryByButton($this->nameSaveNew,'unpublishState');
		$I->doFrontendLogout();
	}

	/**
	 * @depends create
	 * @throws Exception
	 */
	public function delete(AcceptanceTester $I,$scenario)
	{
		$I->am('Administrator');
		$I->wantToTest('Delete a category in Frontend');
		$I->doFrontEndLogin();
		$I= new CategorySteps($scenario);
		$I->delete($this->name);
		$I->delete($this->nameSaveClose);
		$I->delete($this->nameSaveNew);
		$I->doFrontendLogout();
	}
	
	/**
	 * @param CategorySteps $I
	 *
	 * @depends delete
	 * @throws Exception
	 */
	public function update(CategorySteps $I)
	{
		$I->doFrontEndLogin();
		$I->edit($this->name,$this->nameEdit);
		$I->delete($this->nameEdit);
		$I->doFrontendLogout();
	}
	
	/**
	 * @param AcceptanceTester $I
	 * @param $scenario
	 * @throws Exception
	 */
	public function missingTitle(AcceptanceTester $I, $scenario)
	{
		$I->am('Administrator');
		$I->wantToTest('create a category missing in Frontend');
		$I->doFrontEndLogin();
		$I= new CategorySteps($scenario);
		$I->createMissingTitle();
		$I->doFrontendLogout();
	}
	
	/**
	 * @param AcceptanceTester $I
	 * @param $scenario
	 * @throws Exception
	 */
	public function checkButton(AcceptanceTester $I, $scenario)
	{
		$I->am('Administrator');
		$I->wantToTest('create a category missing in Frontend');
		$I->doFrontEndLogin();
		$I= new CategorySteps($scenario);
		$I->checkButton('publish');
		$I->checkButton('unpublish');
		$I->checkButton('delete');
		$I->checkButton('edit');
	}
}
