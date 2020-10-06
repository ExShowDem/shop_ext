<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbOrder150availabilityCest
{
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("check the availability of the webservice");
		$I->amHttpAuthenticated('admin', 'admin');

		$I->sendGET('index.php'
			. '?option=redshopb&view=order'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
		);

		// Checking for 204 code since there are no records before inserting them
		$I->seeResponseCodeIs(204);
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-order');
		$I->seeHttpHeader('X-Webservice-version', '1.5.0');
	}
}
