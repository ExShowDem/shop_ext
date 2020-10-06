<?php

class System_redshopb_stockroom_groups
{
	public function install(AcceptanceTester $I)
	{
		$I->am('administrator');
		$I->wantTo('Install system/redshopb Stockroom Groups Plugin');
		$I->doAdministratorLogin();
		$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'plugins/plg_system_redshopb_stockroom_groups.zip');
	}
}
