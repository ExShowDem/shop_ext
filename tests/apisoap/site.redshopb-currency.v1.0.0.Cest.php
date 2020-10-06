<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

class SiteRedshopbCurrency100SoapCest
{
	public function _before(ApisoapTester $I)
	{
		$endpoint = $I->getWebserviceBaseUrl()
			. '/administrator/index.php?webserviceClient=site&webserviceVersion=1.0.0&option=redshopb&view=currency&api=soap';
		$dynamicEndpoint = $I->getSoapWsdlDinamically($endpoint);
		$I->switchEndPoint($dynamicEndpoint);
	}

	public function readList(ApisoapTester $I)
	{
		$I->wantTo('list categories in Joomla using SOAP');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendSoapRequest('readList', "<filterSearch>Danish Krone</filterSearch>");
		$I->seeSoapResponseIncludes("<code>DKK</code>");
	}

	public function readItem(ApisoapTester $I, $scenario)
	{
		$scenario->skip('@todo: needs to wait for RSBTB-3039');

		$I->wantTo('read 1 category using SOAP');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendSoapRequest('readItem', "<code>DKK</code>");
		$I->seeSoapResponseIncludes("<name>Danish Krone</name>");
	}
}
