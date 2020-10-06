<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License Version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbDelivery_address110availabilityCest
 * @since 2.5.0
 */
class SiteRedshopbDelivery_address110availabilityCest
{
	/**
	 * @param ApiTester $I
	 * @throws Exception
	 * @since 2.5.0
	 */
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("Check the availability of the webservice");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=delivery_address'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
		);

		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-delivery_address');
		$I->seeHttpHeader('X-Webservice-version', '1.1.0');
	}
}