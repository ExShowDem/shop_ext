<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbTemplate100CrudCest
{
	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing template");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=template'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&code=generic-mail-template"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"name":"Generic mail template"');
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of template");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=template'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&filter[search]=Generic%20mail%20template'
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$mail = $I->grabDataFromResponseByJsonPath('$._embedded.item[0]');
		$I->assertEquals($mail[0]['name'], 'Generic mail template');
		$I->assertEquals($mail[0]['scope'], 'email');
	}
}

