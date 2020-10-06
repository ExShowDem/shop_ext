<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir . E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Step\Acceptance\AdminJoomla\MenuItem;
use Page\Acceptance\AdminJoomla\MenuItem\MenuItem as menuItemPage;
use Step\Acceptance\redshopb2b;

/**
 * Class MenuItem
 *
 * @package Step\Acceptance\AdminJoomla\MenuItem
 * @since 2.8.0
 */
class MenuItem extends redshopb2b
{
	/**
	 * @param        $menuTitle
	 * @param        $menuCategory
	 * @param        $menuItem
	 * @param string $menu
	 *
	 * @throws \Exception
	 * @since 2.8.0
	 */
	public function createNewMenuItem($menuTitle, $menuCategory, $menuItem, $menu = 'Main Menu')
	{
		$I = $this;
		$I->wantTo("I open the menus page");
		$I->amOnPage(menuItemPage::$menuItemURL);
		$I->waitForText(menuItemPage::$menuTitle, 5, menuItemPage::$h1);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I click in the menu: $menu");
		$I->waitForElementVisible(array('link' => $menu), 30);
		$I->click(array('link' => $menu));
		$I->waitForText(menuItemPage::$menuItemsTitle, 5,menuItemPage::$h1);
		$I->checkForPhpNoticesOrWarnings();

		$I->wantTo("I click new");
		$I->waitForElementVisible(menuItemPage::$newButton, 30);
		$I->click(menuItemPage::$newButton);
		$I->waitForText(menuItemPage::$menuNewItemTitle, 5, menuItemPage::$h1);
		$I->checkForPhpNoticesOrWarnings();
		$I->fillField(menuItemPage::$menItemTitle, $menuTitle);

		$I->wantTo("Open the menu types iframe");
		$I->waitForElementVisible(menuItemPage::$buttonSelect, 30);
		$I->click(menuItemPage::$buttonSelect);
		$I->waitForElement(menuItemPage::$menuTypeModal, 5);
		$I->switchToIFrame(menuItemPage::$menuItemType);

		$I->wantTo("Open the menu category: $menuCategory");
		$I->waitForElementVisible(menuItemPage::getMenuCategory($menuCategory), 5);
		$I->click(menuItemPage::getMenuCategory($menuCategory));

		$I->wantTo("Choose the menu item type: $menuItem");
		$I->wait(0.5);
		$I->waitForElementVisible(menuItemPage::returnMenuItem($menuItem),5);
		$I->click(menuItemPage::returnMenuItem($menuItem));
		$I->wantTo('I switch back to the main window');
		$I->switchToIFrame();
		$I->wantTo('I leave time to the iframe to close');
		$I->waitForText(menuItemPage::$menuNewItemTitle, '30',menuItemPage::$h1);
		$I->wantTo('I save the menu');
		$I->waitForElementVisible(menuItemPage::$saveButton, 30);
		$I->click(menuItemPage::$saveButton);
		$I->waitForText(menuItemPage::$messageMenuItemSuccess, 5, menuItemPage::$systemContainer);
	}
}