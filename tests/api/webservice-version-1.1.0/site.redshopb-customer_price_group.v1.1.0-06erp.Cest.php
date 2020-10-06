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
 * Class SiteRedshopbCustomerPriceGroup110erpCest
 * @since 2.8.0
 */
class SiteRedshopbCustomerPriceGroup110erpCest
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
	protected $companyA;

	/**
	 * @var
	 * @since 2.8.0
	 */
	protected $new_name;

	/**
	 * @param redshopb2b $I
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function prepare(redshopb2b $I)
	{
		$I->wantTo('POST a new customer_price_group');
		$this->faker = Faker\Factory::create();

		$this->erpId = (int) $this->faker->numberBetween(100, 1000);
		$this->name = $this->faker->bothify('SiteRedshopbCustomer_Price_Group110erpCest ?##?');

		$I->comment('I create a company');
		$this->companyA['name'] = $this->faker->bothify('SiteRedshopbCustomer_Price_group110erpCest companyA ?##?');
		$this->companyA['id'] = (int) $I->createCompany($this->companyA['name']);
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function createWithErpId(redshopb2b $I)
	{
		$I->wantTo('POST a new customer_price_group with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendPOST('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=$this->erpId"
			. "&name=$this->name"
		);

		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function readWithErpId(ApiTester $I)
	{
		$I->wantTo('GET an existing customer_price_group with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['id_others' => ['erp.' . $this->erpId]]);
		$I->seeResponseContainsJson(['name' => $this->name]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function updateWithErpId(ApiTester $I)
	{
		$I->wantTo('UPDATE a customer_price_group using PUT with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$this->new_name = "new_" . $this->name;
		$I->sendPUT('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
			. "&name=$this->new_name"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsjson();
		$I->seeResponseContainsJson(['name' => $this->new_name]);
		$I->dontSeeResponseContainsJson(['name' => $this->name]);

		$this->name = $this->new_name;
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function taskUnpublishWithErpId(ApiTester $I)
	{
		$I->wantTo('unpublish a customer_price_group using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&task=unpublish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => false]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function taskPublishWithErpId(ApiTester $I)
	{
		$I->wantTo('publish a customer_price_group using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&task=publish'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['state' => true]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function taskMemberCompanyAddWithErpId(ApiTester $I)
	{
		$I->wantTo('customer_price_group member company add using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&task=memberCompanyAdd'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
			. "&company_id=" . $this->companyA['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['companies' => [0 => $this->companyA['id']]]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function taskMemberCompanyRemoveWithErpId(ApiTester $I)
	{
		$I->wantTo('customer_price_group member company remove using GET with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&task=memberCompanyRemove'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
			. "&company_id=" . $this->companyA['id']
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->dontSeeResponseContainsJson(['companies' => [0 => $this->companyA['id']]]);
	}

	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function deleteWithErpId(ApiTester $I)
	{
		$I->wantTo('DELETE a customer_price_group using DELETE with erp id');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendDELETE('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(200);

		$I->sendGET('index.php'
			. '?option=redshopb&view=customer_price_group'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
			. "&id=erp.$this->erpId"
		);

		$I->seeResponseCodeIs(404);
		$I->seeResponseIsJson();
	}

	/**
	 * @param redshopb2b $I
	 * @since 2.8.0
	 */
	public function cleanUp(redshopb2b $I)
	{
		$I->wantTo('Clear up all created items by the test');
		$I->deleteCompany($this->companyA['id']);
	}
}