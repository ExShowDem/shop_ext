<?php

class rb_sync_fengelCest
{
	public function install(\Step\Acceptance\redshopb2b $I)
	{
		$I->am('administrator');
		$I->wantTo('Install rb_sync/fengel Plugin');
		$I->doAdministratorLogin();
		$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'plugins/plg_rb_sync_fengel.zip');
	}
}
