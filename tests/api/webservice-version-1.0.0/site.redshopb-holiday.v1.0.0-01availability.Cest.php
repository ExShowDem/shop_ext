<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbHoliday100AvailabilityCest
{
	/**
	 * @param \ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("Check the availability of the webservice");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=holiday'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
		);

		// Checking for 200 code since there are records before inserting them
		$I->seeResponseCodeIs(200);
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-holiday');
		$I->seeHttpHeader('X-Webservice-version', '1.0.0');
	}
}
