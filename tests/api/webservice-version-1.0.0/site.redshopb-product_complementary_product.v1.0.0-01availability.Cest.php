<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbProductComplementaryProduct100AvailabilityCest
 * @since 2.5.1
 */
class SiteRedshopbProductComplementaryProduct100AvailabilityCest
{
	/**
	 * @param \ApiTester $I
	 * @since 2.5.1
	 */
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("Check the availability of the webservice");
		$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
		$I->amHttpAuthenticated("admin","admin");
		$I->sendGET('index.php'
			. '?option=redshopb&view=product_complementary_product'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.0.0'
		);
		
//		 Checking for 204 code since there are no records before inserting them
		$I->seeResponseCodeIs(204);
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-product_complementary_product');
		$I->seeHttpHeader('X-Webservice-version', '1.0.0');
	}
}
