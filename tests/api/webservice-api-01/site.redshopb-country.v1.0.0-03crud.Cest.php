<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCountry100CrudCest
{
	public function readItem(ApiTester $I)
	{
		$I->wantTo("GET an existing country");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=country'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. "&code=DK" // The code of Denmark, see component/admin/sql/install/mysql/data.sql:68
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContains('"name":"Denmark"');
	}

	public function readList(ApiTester $I)
	{
		$I->wantTo("GET a list of country");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=country'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
			. '&filter[search]=Denmark'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$country = $I->grabDataFromResponseByJsonPath('$._embedded.item[0]');
		$I->assertEquals($country[0]['code'], 'DK');
		$I->assertEquals($country[0]['name'], 'Denmark');
	}
}

