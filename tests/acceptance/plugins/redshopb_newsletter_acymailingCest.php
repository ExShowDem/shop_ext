<?php

class Redshopb_newsletter_acymailingCest
{
	public function install(AcceptanceTester $I)
	{
		$I->am('administrator');
		$I->wantTo('Install redshopb_newsletter/acymailingCest Plugin');
		$I->doAdministratorLogin();
		$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'plugins/plg_redshopb_newsletter_acymailing.zip');
	}
}
