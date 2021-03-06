<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProduct110SoapCest
{
	public function _before(ApisoapTester $I)
	{
		$endpoint = $I->getWebserviceBaseUrl()
			. '/administrator/index.php?webserviceClient=site&webserviceVersion=1.1.0&option=redshopb&view=product&api=soap';
		$dynamicEndpoint = $I->getSoapWsdlDinamically($endpoint);
		$I->switchEndPoint($dynamicEndpoint);
	}

	public function prepare(ApisoapTester $I)
	{
		$this->faker = Faker\Factory::create();
	}

	public function create(ApisoapTester $I)
	{
		$I->wantTo('POST a new product using SOAP');

		$this->name  = (string) $this->faker->bothify('SiteRedshopbProduct110SoapCest product ?##?');
		$I->amHttpAuthenticated('admin', 'admin');

		$I->comment($this->name);
		$I->sendSoapRequest('create',
			[
				'name' => $this->name,
				'sku' => $this->name,
				'company_id' => 2
			]
		);
		$I->seeSoapResponseContainsStructure("<result></result>");
		$I->dontSeeSoapResponseIncludes("<result>false</result>");
	}


	public function readList(ApisoapTester $I)
	{
		$I->wantTo('list products in Joomla using SOAP');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendSoapRequest('readList', "<filterSearch>$this->name</filterSearch>");
		$I->seeSoapResponseIncludes("<name>$this->name</name>");
		$this->id = $I->grabTextContentFrom('//list//item//id');
	}

	public function readItem(ApisoapTester $I)
	{
		$I->wantTo('read 1 product using SOAP');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendSoapRequest('readItem', "<id>$this->id</id>");
		$I->seeSoapResponseIncludes("<name>$this->name</name>");
		$I->seeSoapResponseIncludes("<state>true</state>");
	}

	public function translate(ApisoapTester $I)
	{
		$I->wantTo('translate 1 product using SOAP');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendSoapRequest('task_translate', [
				'id' => $this->id,
				'language' => 'fr-FR',
				'name' => "french-$this->name"
			]
		);
		$I->seeSoapResponseIncludes("<result>$this->id</result>");

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendSoapRequest('readItem', [
				'id' => $this->id,
				'language' => 'fr-FR'
			]
		);
		$I->seeSoapResponseIncludes("<name>french-$this->name</name>");
		$I->seeSoapResponseIncludes("<state>true</state>");
	}

	public function translateRemove(ApisoapTester $I)
	{
		$I->wantTo('delete a translation of 1 product using SOAP');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendSoapRequest('task_translateRemove', [
				'id' => $this->id,
				'language' => 'fr-FR',
			]
		);
		$I->dontSeeSoapResponseIncludes("<result>$this->id</result>");

		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendSoapRequest('readItem', [
				'id' => $this->id,
				'language' => 'fr-FR'
			]
		);
		$I->cantSeeSoapResponseIncludes("<name>french-$this->name</name>");
		$I->seeSoapResponseIncludes("<name>$this->name</name>");
		$I->seeSoapResponseIncludes("<state>true</state>");
	}

	public function unpublish(ApisoapTester $I)
	{
		$I->wantTo('unpublish 1 product using SOAP');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendSoapRequest('task_unpublish', "<id>$this->id</id>");
		$I->seeSoapResponseIncludes("<result>$this->id</result>");

		$I->sendSoapRequest('readItem', ['id' => $this->id]);
		$I->seeSoapResponseIncludes("<name>$this->name</name>");
		$I->seeSoapResponseIncludes("<state>false</state>");
	}

	public function cleanUp(ApisoapTester $I)
	{
		$I->wantTo('delete 1 product using SOAP');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendSoapRequest('delete',  "<id>$this->id</id>");
		$I->seeSoapResponseIncludes("<result>true</result>");

		$I->sendSoapRequest('readItem', ['id' => $this->id]);
		$I->dontSeeSoapResponseIncludes("<name>$this->name</name>");
	}
}
