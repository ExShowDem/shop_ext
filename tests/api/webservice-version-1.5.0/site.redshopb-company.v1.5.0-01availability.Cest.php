<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCompany150availabilityCest
{
	/**
	 * @param \ApiTester $I
	 * @since 2.5.0
	 */
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("check the availability of the webservice");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=company'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.5.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-company');
		$I->seeHttpHeader('X-Webservice-version', '1.5.0');
	}
}

