<?php
/**
 * @package     Aesir E-Commerce.
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir E-Commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

/**
 * Class SiteRedshopbDepartment120availabilityCest
 * @since 2.8.0
 */
class SiteRedshopbDepartment120availabilityCest
{
	/**
	 * @param ApiTester $I
	 * @since 2.8.0
	 */
	public function WebserviceIsAvailable(ApiTester $I)
	{
		$I->wantTo("check the availability of the webservice");
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET('index.php'
			. '?option=redshopb&view=department'
			. '&api=Hal'
			. '&webserviceClient=site'
			. '&webserviceVersion=1.2.0'
		);

		$I->seeResponseCodeIs(204);
		$I->seeHttpHeader('X-Webservice-name', 'redshopb-department');
		$I->seeHttpHeader('X-Webservice-version', '1.2.0');
	}
}