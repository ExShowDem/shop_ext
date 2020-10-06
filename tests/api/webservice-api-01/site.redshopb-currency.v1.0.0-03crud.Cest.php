<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCurrency100CrudCest
{
	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing currency");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=currency'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&code=DKK"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"name":"Danish Krone"');
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of currencies filtered");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=currency'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&filter[search]=Danish%20Krone'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$currency = $I->grabDataFromResponseByJsonPath('$._embedded.item[0]');
		$I->assertEquals($currency[0]['code'], 'DKK');
		$I->assertEquals($currency[0]['name'], 'Danish Krone');
	}
}

