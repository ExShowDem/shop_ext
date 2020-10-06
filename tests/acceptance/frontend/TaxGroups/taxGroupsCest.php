<?php

/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Frontend\TaxGroupsSteps as TaxGroupsSteps;
class taxGroupsCest
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
	protected $editName;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $company;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $status;

	/**
	 * taxGroupsCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		$this->name = $this->faker->bothify('tagGroupsCest  ?##?');

		$this->editName = $this->name.'Edit';

		$this->company = '(main) Main Company';

		$this->status = 'Publish';
	}
	
	/**
	 * @param TaxGroupsSteps $client
	 * @throws Exception
	 */
	public function _before(TaxGroupsSteps $client)
	{
		$client->am('Administrator');
		$client->wantToTest('Tag creation in Frontend');
		$client->doFrontEndLogin();
	}

	/**
	 * @param   TaxGroupsSteps $client Create with save and new button
	 * @return void
	 * @throws Exception
	 */
	public function createUpdateDelete(TaxGroupsSteps $client)
	{
		$client->comment('create one tax groups with save&new action');
		$client->create($this->name, $this->company, $this->status,'save&new');
		$client->doFrontendLogout();
	}

	/**
	 * @param TaxGroupsSteps $client Edit Delete with save and new button
	 * @throws Exception
	 */
	public function editDeleteNew(TaxGroupsSteps $client)
	{
		$client->comment('Edit this tax groups with save&new action');
		$client->edit($this->name, $this->editName, 'save&new');

		$client->comment('Delete this tax group');
		$client->delete($this->editName);
		$client->doFrontendLogout();
	}

	/**
	 * @param   TaxGroupsSteps $client Create Edit Delete with save button
	 * @return void
	 * @throws Exception
	 */
	public function saveTaxGroup(TaxGroupsSteps $client)
	{
		$client->comment('create one tax groups with save action');
		$client->create($this->name, $this->company, $this->status, 'save');

		$client->wait(1);
		$client->comment('Edit this tax groups with save action');
		$client->edit($this->name, $this->editName, 'save');
		
		$client->wait(1);
		$client->comment('Delete this tax group');
		$client->delete($this->editName);
		$client->doFrontendLogout();
	}

	/**
	 * @param   TaxGroupsSteps $client  Create Edit Delete with save and close button
	 * @return void
	 * @throws Exception
	 *
	 */
	public function saveCloseTaxGroups(TaxGroupsSteps $client)
	{
		$client->comment('create one tax groups with save&close action');
		$client->create($this->name, $this->company, $this->status, 'save&close');
		$client->doFrontendLogout();

	}

	/**
	 * @param TaxGroupsSteps $client
	 * @throws Exception
	 */
	public function editSaveClose(TaxGroupsSteps $client)
	{
		$client->wait(1);
		$client->comment('Edit this tax groups with save&close action');
		$client->edit($this->name, $this->editName, 'save&close');

		$client->wait(1);

		$client->comment('Delete this tax group');
		$client->delete($this->editName);
		$client->doFrontendLogout();
	}
}