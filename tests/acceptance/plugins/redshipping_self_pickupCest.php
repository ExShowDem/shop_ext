<?php

class Redshipping_self_pickupCest
{
	public function install(AcceptanceTester $I)
	{
		$I->am('administrator');
		$I->wantTo('Install redshipping/self_pickup Plugin');
		$I->doAdministratorLogin();
		$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'plugins/plg_redshipping_self_pickup.zip');
	}
}
