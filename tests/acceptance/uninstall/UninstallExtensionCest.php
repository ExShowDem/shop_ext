<?php
/**
 * @package     Aesir.E-Commerce
 * @subpackage  Cept
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class ZZUninstallExtensionCest
 *
 * @package  AcceptanceTester
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage
 *
 * @since    1.4
 */
class UninstallExtensionCest
{
	/**
	 * Function to Uninstall redSHOP extension
	 *
	 * @return void
	 */
	public function uninstallExtension(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Uninstall Aesir E-Commerce Extensions');
		$I->doAdministratorLogin();
		$I->amOnPage('/administrator/index.php?option=com_installer&view=manage');
		$I->click("//button[@class='btn hasTooltip js-stools-btn-filter']");
		$I->waitForElementChange(
			['xpath' => '//*[@id="j-main-container"]/div[1]/div[2]'],
			function ($el)
			{
				return ('display: block;' === $el->getAttribute('style'));
			},
			30
		);

		$I->selectOptionInChosen('#filter_type', 'Component');
		$I->fillField('#filter_search', 'Aesir E-Commerce');
		$I->pressKey(['id' => 'filter_search'], WebDriverKeys::ENTER);
		$I->waitForElement('#manageList');
		$I->click("//input[@id='cb0']");
		$I->click("Uninstall");
		$I->acceptPopup();
		$I->see('Uninstalling the component was successful', '#system-message-container');
		$I->fillField('#filter_search', 'Aesir E-Commerce Media Manager');
		$I->pressKey(['id' => 'filter_search'], WebDriverKeys::ENTER);
		$I->waitForElement('#manageList');
		$I->click("//input[@id='cb0']");
		$I->click("Uninstall");
		$I->acceptPopup();
		$I->see('Uninstalling the component was successful', '#system-message-container');
		$I->fillField('#filter_search', 'redSHOPB2B');
		$I->pressKey(['id' => 'filter_search'], WebDriverKeys::ENTER);
		$I->waitForText('There are no extensions installed matching your query.', 10, '.alert-no-items');
		$I->see('There are no extensions installed matching your query.', '.alert-no-items');
		$I->selectOptionInChosen('#filter_type', '- Select Type -');
	}
}
