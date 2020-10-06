<?php

class Redshipping_default_shippingCest
{
	public function install(AcceptanceTester $I)
	{
		$I->am('administrator');
		$I->wantTo('Install redshipping/default_shipping Plugin');
		$I->doAdministratorLogin();
		$I->installExtensionFromUrl($I->getConfig('redshopb packages url') . 'plugins/plg_redshipping_default_shipping.zip');
	}
}
