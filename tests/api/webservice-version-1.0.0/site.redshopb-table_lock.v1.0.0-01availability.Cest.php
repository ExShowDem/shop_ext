<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbTableLock100AvailabilityCest
{
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("check the availability of the webservice");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=table_lock'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
		);

		// Checking for 204 code since there are no records before inserting them
		$I->seeResponseCodeIs(204);
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-table_lock');
		$I->seeHttpHeader('X-Webservice-version', '1.0.0');
	}
}
