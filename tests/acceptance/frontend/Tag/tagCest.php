<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cest
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Frontend\tagSteps;
class tagCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.4.0
	 */
	protected $faker;

	/**
	 * @var array
	 * @since 2.4.0
	 */
	protected $tag;

	/**
	 * @var string
	 * @since 2.4.0
	 */
	protected $nameEdit;

	/**
	 * tagCest constructor.
	 * @since 2.4.0
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();
		$this->tag = array(
			"name" =>  $this->faker->bothify('tagCest?##?'),
			"status" => "Published"
		);
		$this->nameEdit = $this->faker->bothify('edit?###');
	}

	/**
	 * @param AcceptanceTester $client
	 * @throws Exception
	 */
	public function _before(AcceptanceTester $client)
	{
		$client->doFrontEndLogin();
	}

	/**
	 * @param tagSteps $client
	 * @throws Exception
	 */
	public function create(tagSteps $client)
	{
		$client->am('Administrator');
		$client->wantToTest('Tag creation in Frontend');
		$client->create($this->tag);
		$client->doFrontendLogout();
	}

	/**
	 * @depends create
	 * @throws Exception
	 */
	public function edit(tagSteps $client)
	{
		$client->wantToTest('Edit name of tag');
		$client->editName($this->tag['name'], $this->nameEdit);
		$client->doFrontendLogout();
	}

	/**
	 * @depends edit
	 * @throws Exception
	 */
	public function delete(tagSteps $client)
	{
		$client->wantToTest('Delete the tag by name');
		$client->delete($this->nameEdit);
	}
}
