<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir.E-Commerce All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbProductDescription110AvailabilityCest
{
	/**
	 * @param ApiTester $I
	 * @since 2.5.0
	 */
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("Check the availability of the webservice product description");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_description'
			. '&api=hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.1.0'
		);

//		 Checking for 204 code since there are no records before inserting them
		$I->seeResponseCodeIs(204);
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-product_description');
		$I->seeHttpHeader('X-Webservice-version', '1.1.0');
	}
}