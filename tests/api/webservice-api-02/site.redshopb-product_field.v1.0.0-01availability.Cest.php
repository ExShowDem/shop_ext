<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProduct_field100availabilityCest
{
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("check the availability of the webservice");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_field'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
		);

		// Checking for 204 code since there are no records before inserting them
		$I->seeResponseCodeIs(500);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(["message" => "Webservice redshopb-product_field (version 1.0.0) is not installed."]);
	}
}

