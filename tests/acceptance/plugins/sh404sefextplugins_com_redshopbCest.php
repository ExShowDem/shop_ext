<?php

class Sh404sefextcom_redshopbCest
{
	public function install(AcceptanceTester $I)
	{
		$I->am('administrator');
		$I->wantTo('Install sh404sefextcom/redshopb Plugin');
		$I->doAdministratorLogin();
		$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'plugins/plg_sh404sefextplugins_com_redshopb.zip');
	}
}
