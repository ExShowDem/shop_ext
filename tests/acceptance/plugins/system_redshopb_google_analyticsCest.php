<?php

class System_redshopb_google_analytics
{
	public function install(AcceptanceTester $I)
	{
		$I->am('administrator');
		$I->wantTo('Install system/redshopb Google Analytics Plugin');
		$I->doAdministratorLogin();
		$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'plugins/plg_system_redshopb_google_analytics.zip');
	}
}
