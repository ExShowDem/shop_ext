<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Frontend\Newsletter_listsSteps as Newsletter_listsSteps;

class Newsletter_listsCest
{
	/**
	 * Newsletter_listsCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		$this->newsLetter = array(
			"name" =>  $this->faker->bothify('newLetter  ?##?'),
			"status" => "Published"
		);

		$this->nameEdit = $this->faker->bothify('edit newLetter ??####?');

	}

	/**
	 * @param Newsletter_listsSteps $client
	 * @throws Exception
	 * @since 2.4.0
	 */
	public function create(Newsletter_listsSteps $client)
	{
		$client->doFrontEndLogin();
		$client->wantTo('Create news letter');
		$client->createNewsletter($this->newsLetter);
		$client->doFrontendLogout();
	}

	/**
	 * @param Newsletter_listsSteps $client
	 * @throws Exception
	 * @since 2.4.0
	 */
	public function edit(Newsletter_listsSteps $client)
	{
		$client->doFrontEndLogin();
		$client->wantTo('Edit name of news letter');
		$client->editNameNewLetter($this->newsLetter['name'], $this->nameEdit);
		$client->doFrontendLogout();
	}

	/**
	 * @param Newsletter_listsSteps $client
	 * @throws Exception
	 * @since 2.4.0
	 */
	public function delete(Newsletter_listsSteps $client)
	{
		$client->doFrontEndLogin();
		$client->wantTo('Delete news letter');
		$client->deleteNewLetter($this->nameEdit);
		$client->doFrontendLogout();
	}
}
