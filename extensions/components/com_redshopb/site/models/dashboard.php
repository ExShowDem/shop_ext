<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Dashboard Model
 *
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Models
 * @since       2.0
 *
 * @todo move menu related access checks to this model, so getMenuItems can exclude items
 */
class RedshopbModelDashboard extends RedshopbModel
{
	/**
	 * Method to get a structured array of menu items from menu.xml
	 *
	 * @param   string  $menuName  optional menu name to limit return a single menu
	 *
	 * @return array
	 */
	public function getMenuItems($menuName = '')
	{
		$menuDefinition = simplexml_load_file(JPATH_SITE . '/components/com_redshopb/views/dashboard/menu.xml');
		$user           = RedshopbEntityUser::loadActive();

		if (!empty($menuName))
		{
			return $this->getSubmenu($menuName, $menuDefinition, $user);
		}

		$types = array();

		foreach ($menuDefinition->children() AS $menu)
		{
			$menuList = $this->convertMenuXmlToArray($menu, $user);

			$types = array_merge($types, $menuList);
		}

		return $types;
	}

	/**
	 * Method to get a menu by name
	 *
	 * @param   string              $menuName           name of the menu to get
	 * @param   SimpleXMLElement    $xmlMenuDefinition  menu definition
	 * @param   RedshopbEntityUser  $user               current user
	 *
	 * @return array
	 */
	protected function getSubmenu($menuName, $xmlMenuDefinition, $user)
	{
		$menu = $xmlMenuDefinition->xpath('//menu[@name="' . $menuName . '"]');

		if (empty($menu))
		{
			return array();
		}

		$menu = $menu[0];

		return $this->convertMenuXmlToArray($menu, $user);

	}

	/**
	 * Method to get a menu xml as an array
	 *
	 * @param   SimpleXMLElement    $xmlMenuDefinition  menu definition
	 * @param   RedshopbEntityUser  $user               current user
	 *
	 * @return array
	 */
	protected function convertMenuXmlToArray($xmlMenuDefinition, $user)
	{
		$menuAttr = Vanir\Utility\Xml::getAttributes($xmlMenuDefinition);

		$menuList = $this->convertMenuLinksXmlToArray($xmlMenuDefinition);

		RFactory::getDispatcher()->trigger('onRedshopbAddMenuItem', array(&$menuList));

		if (empty($menuList))
		{
			return array();
		}

		if (empty($menuAttr['text']) || empty($user->getId()))
		{
			return $menuList;
		}

		$menuAttr['heading'] = true;
		array_unshift($menuList, $menuAttr);

		return $menuList;
	}

	/**
	 * Method to convert an xml menu definition into an array
	 *
	 * @param   SimpleXmlElement  $menuXml  xml definition
	 *
	 * @return array
	 */
	protected function convertMenuLinksXmlToArray($menuXml)
	{
		$menuList = array();

		foreach ($menuXml->children() AS $link)
		{
			$linkAttr  = Vanir\Utility\Xml::getAttributes($link);
			$queryVars = $link->query;

			if (empty($linkAttr['view']))
			{
				continue;
			}

			$linkAttr['query'] = '';

			if (!empty($queryVars))
			{
				$inputVars = array('view' => $linkAttr['view']);

				foreach ($queryVars->children() AS $queryVar)
				{
					$query              = \Vanir\Utility\Xml::getAttributes($queryVar);
					$linkAttr['query'] .= '&' . $query['name'] . '=' . (string) $query['value'];

					$inputVars[$query['name']] = $query['value'];
				}

				if (!empty($linkAttr['query']))
				{
					$linkAttr['query'] = '?' . substr($linkAttr['query'], 1);
				}
			}

			$submenuLinks = $link->submenu;

			if (!empty($submenuLinks))
			{
				$linkAttr['childs'] = $this->convertMenuLinksXmlToArray($submenuLinks);
			}

			$menuList[] = $linkAttr;
		}

		return $menuList;
	}

	/**
	 * Method to convert a hierarchical array strucuture to a flat array.
	 *
	 * @param   array  $items  menu items
	 *
	 * @return array
	 */
	public function flattenMenu($items)
	{
		$flattened = array();

		foreach ($items as $item)
		{
			if (!isset($item['view']) || !RedshopbHelperACL::allowDisplayView($item['view']))
			{
				continue;
			}

			$item = $this->getPrepareItem($item);

			if (!$item)
			{
				continue;
			}

			if (empty($item['childs']))
			{
				$flattened[] = $item;

				continue;
			}

			$flatChildren = $this->flattenMenu($item['childs']);

			if (empty($flatChildren))
			{
				$flattened[] = $item;

				continue;
			}

			$flattened = array_merge($flattened, $flatChildren);
		}

		return $flattened;
	}

	/**
	 * Method to prepare the item for display
	 *
	 * @param   array  $item  the menu item
	 *
	 * @return array|boolean prepared item or false if item isn't supposed to be included in the menu
	 */
	private function getPrepareItem($item)
	{
		$config = RedshopbEntityConfig::getInstance();
		$isShop = RedshopbHelperPrices::displayPrices();

		// If Return orders feature is turned off in backend, don't show the button
		if (!$config->getString('order_return', 0) && $item['view'] == 'return_orders')
		{
			return false;
		}

		// If shop use as catalog, then disable all view relate with checkout and prices
		if (!$isShop && in_array($item['view'], RedshopbHelperCommon::getPriceViews()))
		{
			return false;
		}

		if (!empty($item['heading']) || $item['view'] == 'dashboard')
		{
			return false;
		}

		$item['text'] = Text::_($item['text']);

		return $item;
	}
}
