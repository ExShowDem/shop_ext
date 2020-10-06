<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  mod_redshopb_sidebar
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * Redshopb Topnav Helper
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Module
 * @since       1.0.0
 */
class ModRedshopbTopnavHelper
{
	/**
	 * Get menu items
	 *
	 * @param   bool    $menuInclude  Specifies if a menu must be returned
	 * @param   string  $menu         Menu to be returned
	 *
	 * @return  array   Menu items array
	 */
	public function getMenuItems($menuInclude, $menu)
	{
		$menuItems = array();
		$db        = Factory::getDBO();

		if ($menuInclude)
		{
			$query = $db->getQuery(true);
			$query->select(array($db->qn('id'), $db->qn('title'), $db->qn('link'), $db->qn('type'), $db->qn('params')))
				->from('#__menu')
				->where($db->qn('menutype') . ' = ' . $db->q($menu))
				->where($db->qn('published') . ' = 1')
				->where($db->qn('level') . ' = 1')
				->order($db->qn('rgt'));
			$db->setQuery($query);

			$menuItems = $db->loadObjectList();

			foreach ($menuItems as $i => $menuItem)
			{
				// For alias menu items, gets link and type from redirected menu item
				if ($menuItem->type == 'alias')
				{
					$aliasParams = json_decode($menuItem->params);

					if (isset($aliasParams->aliasoptions))
					{
						$query->clear()
							->select(array($db->qn('link'), $db->qn('type')))
							->from('#__menu')
							->where($db->qn('id') . ' = ' . (int) $aliasParams->aliasoptions);
						$db->setQuery($query);

						$aliasMenu = $db->loadObject();

						$menuItem->link = $aliasMenu->link;
						$menuItem->type = $aliasMenu->type;
						$menuItem->id   = $aliasMenu->id;

						$menuItems[$i] = $menuItem;
					}
				}
			}
		}

		return $menuItems;
	}
}
