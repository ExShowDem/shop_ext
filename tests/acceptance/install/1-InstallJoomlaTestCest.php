<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cept
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

class InstallJoomlaTestCest
{
	/**
	 * @param \Step\Acceptance\redshopb2b $I
	 * @throws Exception
	 */
	public function installJoomla(\Step\Acceptance\redshopb2b $I)
	{
		$I->wantTo('Execute Joomla Installation');
		$I->installJoomlaMultilingualSite();
		$I->doAdministratorLogin();
		$I->setErrorReportingtoDevelopment();
	}
}
