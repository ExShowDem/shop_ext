<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class siteredshopbProduct130availabilityCest
 * @since 2.6.0
 */
class SiteRedshopbProduct130availabilityCest
{
	/**
	 * @param ApiTester $I
	 * @since 2.6.0
	 */
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("check the availability of the webservice");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.3.0'
		);

		$I->seeResponseCodeIs(204);
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-product');
		$I->seeHttpHeader('X-Webservice-version', '1.3.0');
	}
}