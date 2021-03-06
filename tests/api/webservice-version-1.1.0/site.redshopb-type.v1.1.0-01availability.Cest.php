<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbType110AvailabilityCest
 * @since 2.5.0
 */
class SiteRedshopbType110AvailabilityCest
{
	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("check the availability of the webservice");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=type'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-type');
		$I->seeHttpHeader('X-Webservice-version', '1.1.0');
	}
}
