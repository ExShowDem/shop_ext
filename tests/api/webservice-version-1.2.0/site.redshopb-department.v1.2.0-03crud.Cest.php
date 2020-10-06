<?php
/**
 * @package     Aesir E-Commerce.
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

use Step\Api\redshopb2b;

/**
 * Class SiteRedshopbDepartment120crudCest
 * @since 2.8.0
 */
class SiteRedshopbDepartment120crudCest
{
	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $faker;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $erpId;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $name;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $company_id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $id;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $department_number;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $new_name;

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('prepare for POST a new department');
		$this->faker = Faker\Factory::create();

		$this->erpId = (int) $this->faker->numberBetween(100, 1000);
		$this->company_id = $I->getMainCompanyId();
		$this->name = $this->faker->bothify('SiteRedshopbDepartment120crudCest ?##?');

		$this->department_number = (int) $this->faker->numberBetween(100, 1000);
	}

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function create(redshopb2b $I)
	{
		$I->wantTo('POST a new department');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->erpId"
			. "&name=$this->name"
			. "&company_id=$this->company_id"
			. "&department_number=$this->department_number"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();

		$ids = $I->grabDataFromResponseByJsonPath('$.result');
		$this->id = $ids[0];
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readItem(ApiTester $I)
	{
		$I->wantTo('GET an existing department');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readList(ApiTester $I)
	{
		$I->wantTo('GET a list of department');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id' => $this->id]);
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function update(ApiTester $I)
	{
		$I->wantTo('UPDATE a department using PUT');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->new_name = "new_" . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
			. "&name=$this->new_name"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['name' => $this->new_name]);
		$I->dontSeeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function delete(ApiTester $I)
	{
		$I->wantTo('DELETE a department using DELETE');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
			. "&id=$this->id"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
	}
}