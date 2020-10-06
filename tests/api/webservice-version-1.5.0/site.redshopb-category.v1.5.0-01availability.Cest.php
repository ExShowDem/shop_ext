<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCategory150AvailabilityCest
{
	/**
	 * @param ApiTester $I
	 */
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("Check the availability of the webservice category");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=category'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
		);

//		 Checking for 204 code since there are no records before inserting them
		$I->seeResponseCodeIs(204);
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-category');
		$I->seeHttpHeader('X-Webservice-version', '1.5.0');
	}
}