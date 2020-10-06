<?php

class Site_mod_redshopb_statusCest
{
	public function install(\Step\Acceptance\redshopb2b $I)
	{
	}

	public function display(\Step\Acceptance\redshopb2b $I)
	{
		$I->am('administrator');
		$I->wantTo('Display in frontend mod_redshopb_status Module');
		$I->doAdministratorLogin();
		$I->publishModule('Aesir E-Commerce Status');
		$I->displayModuleOnAllPages('Aesir E-Commerce Status');

		$I->setModulePosition('Aesir E-Commerce Status', 'position-7');

		$I->amOnPage('index.php');
		$I->waitForElement(['class' => 'modRedshopbStatus'], 30);
		$I->waitForElement(['xpath' => "//div[@id='aside']//h3[contains(text(), 'Aesir E-Commerce Status')]"], 30);
	}
}
