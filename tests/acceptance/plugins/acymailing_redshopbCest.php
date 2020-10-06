<?php

class Acymailing_redshopbCest
{
	public function install(\Step\Acceptance\redshopb2b $I)
	{
		$I->am('administrator');
		$I->wantTo('Install Acymailing Plugin');
		$I->doAdministratorLogin();
		$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'plugins/plg_acymailing_redshopb.zip');
	}
}
