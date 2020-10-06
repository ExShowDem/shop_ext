<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir . E-commerce. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Page\Acceptance\AdminJoomla\MenuItem;
use Page\Frontend\Redshopb2bPage as Redshopb2bPage;

/**
 * Class MenuItem
 *
 * @package Page\Acceptance\AdminJoomla\MenuItem
 * @since 2.8.0
 */
class MenuItem extends Redshopb2bPage
{
	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $menuItemURL = '/administrator/index.php?option=com_menus&view=menus';

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $menuTitle = 'Menus';

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $menuItemsTitle = 'Menus: Items';

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $menuNewItemTitle = 'Menus: New Item';

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $menuItemType = 'Menu Item Type';

	/**
	 * Menu item title
	 * @var string
	 * @since 2.8.0
	 */
	public static $menItemTitle = "#jform_title";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $buttonSelect = "//button[contains(text(),'Select')]";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $messageMenuItemSuccess = 'Menu item saved';

	/**
	 * Menu Type Modal
	 * @var string
	 * @since 2.8.0
	 */
	public static $menuTypeModal = "#menuTypeModal";

	/**
	 * @var string
	 * @since 2.8.0
	 */
	public static $h1 = array('css' => 'h1');

	/**
	 * @param $menuCategory
	 * @return array
	 * @since 2.8.0
	 */
	public static function getMenuCategory($menuCategory)
	{
		$menuCate = ["link" => $menuCategory];
		return $menuCate;
	}

	/**
	 * @param $menuItem
	 * @return string
	 * @since 2.8.0
	 */
	public static function returnMenuItem($menuItem)
	{
		$path = "//a[contains(text()[normalize-space()], '$menuItem')]";
		return $path;
	}
}