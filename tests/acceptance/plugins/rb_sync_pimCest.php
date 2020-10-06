<?php

class rb_sync_pimCest
{
	public function install(\Step\Acceptance\redshopb2b $I)
	{
		$I->am('administrator');
		$I->wantTo('Install rb_sync/pim Plugin');
		$I->doAdministratorLogin();
		$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'plugins/plg_rb_sync_pim.zip');
	}
}
