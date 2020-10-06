<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbType100CrudCest
{
	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing type");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=type'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&code=textboxstring"
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"name":"Textbox - string"');
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of type");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=type'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&filter[search]=Textbox%20-%20string'
		);

		$type = $I->grabDataFromResponseByJsonPath('$._embedded.item[0]');
		$I->assertEquals($type[0]['code'], 'textboxstring');
		$I->assertEquals($type[0]['name'], 'Textbox - string');
	}
}

