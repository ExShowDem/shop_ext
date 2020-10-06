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
use Joomla\CMS\Language\Text;
/**
 * Helper class for sidebar
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Module.Sidebar
 * @since       1.0
 */
class ModRedshopbSidebarHelper
{
	/**
	 * Function for getting the sidebar types (menu items)
	 *
	 * @return  array  Sidebar menus
	 */
	public function getTypes()
	{
		$user      = Factory::getUser();
		$rsbUserId = RedshopbHelperUser::getUserRSid($user->id);
		/** @var RedshopbModelDashboard $model */
		$model = RedshopbModel::getFrontInstance('Dashboard');

		$myPageMenu         = array();
		$returnOrdersGlobal = 0;
		$displayOrdersViews = RedshopbHelperACL::isSuperAdmin();
		$isRsbUser          = ($rsbUserId != 0 && $rsbUserId != '');

		if ($isRsbUser)
		{
			$company = RedshopbEntityCompany::load(RedshopbEntityUser::getInstance($rsbUserId)->getCompany()->id);

			$contactInfo        = RedshopbHelperCommon::checkContactInfor($company);
			$displayPriceViews  = (RedshopbHelperPrices::displayPrices() || RedshopbHelperPrices::displayPrices() === false);
			$displayOrdersViews = (RedshopbHelperPrices::displayPrices()
				&& (RedshopbHelperACL::getPermission('view', 'order') || RedshopbHelperACL::getPermission('manage', 'order', array(), true)));
			$config             = RedshopbEntityConfig::getInstance();
			$returnOrdersGlobal = 1;

			if ($config)
			{
				$returnOrdersGlobal = $config->getInt('order_return', 1);
			}

			$myPageMenu = $model->getMenuItems('mypages');

			if (!$displayPriceViews || !$company->get('use_wallets'))
			{
				array_pop($myPageMenu);
			}

			if (!empty($contactInfo))
			{
				array_pop($myPageMenu);
			}

			if ($displayPriceViews && $displayOrdersViews)
			{
				$orderMenu = $model->getMenuItems('orders');

				if (!$returnOrdersGlobal)
				{
					array_pop($orderMenu);
				}

				$myPageMenu = array_merge($myPageMenu, $orderMenu);
			}

			if ($displayPriceViews)
			{
				$productMenu = $model->getMenuItems('products');

				if (RedshopbHelperShop::inCollectionMode($company))
				{
					unset($productMenu[1]);
				}

				$myPageMenu = array_merge($myPageMenu, $productMenu);
			}
		}

		$otherMenus = $model->getMenuItems('others');

		if (!RedshopbHelperACL::getPermission('manage', 'mainwarehouse'))
		{
			// Remove holiday menu item
			array_pop($otherMenus);
		}

		foreach ($otherMenus AS $key => $item)
		{
			if (!isset($item['view']) || ($item['view'] != 'orders' && $item['view'] != 'return_orders'))
			{
				continue;
			}

			if ($isRsbUser || !$displayOrdersViews)
			{
				unset($otherMenus[$key]);
			}
		}

		$myPageMenu = array_merge($myPageMenu, $otherMenus);

		RFactory::getDispatcher()->trigger('onAfterModRedshopbSidebarHelperGetTypes', array(&$myPageMenu));

		return $myPageMenu;
	}

	/**
	 * Renderer Sidebar
	 *
	 * @param   array   $types    Data menu
	 * @param   string  $active   Name active view
	 * @param   bool    $isChild  Flag generate child menu
	 *
	 * @return array
	 */
	public function rendererSidebar($types, $active, $isChild = false)
	{
		if ($isChild === false)
		{
			if (RHtmlMedia::$frameworkSuffix == 'bs2')
			{
				$class = 'nav nav-list redcore';
			}
			else
			{
				$class = 'nav nav-pills nav-stacked redcore';
			}
		}
		else
		{
			$class = 'dropdown-menu';
		}

		$html        = '<ul class="' . $class . '">';
		$childActive = false;
		$config      = RedshopbEntityConfig::getInstance();
		$isShop      = $config->getInt('show_price', 1);

		// Checks ACL for displaying prices
		if ($isShop)
		{
			$user = RedshopbHelperCommon::getUser();

			if ($user->b2cMode)
			{
				$isShop = (int) RedshopbHelperACL::getGlobalB2CPermission('view', 'shopprice');
			}
			else
			{
				$isShop = (int) RedshopbHelperACL::getPermission('view', 'shopprice');
			}
		}

		$heading           = '';
		$isFromMainCompany = false;
		$rsbUserId         = RedshopbHelperUser::getUserRSid();

		if ($rsbUserId)
		{
			$isFromMainCompany = RedshopbHelperUser::isFromMainCompany($rsbUserId, 'employee');
		}

		$cGroup = 'group-submenu';

		foreach ($types as $type)
		{
			$view   = (!empty($type['view'])) ? $type['view'] : '';
			$query  = (!empty($type['query'])) ? $type['query'] : '';
			$text   = (!empty($type['text'])) ? Text::_($type['text']) : '';
			$icon   = (!empty($type['icon'])) ? '<i class="' . $type['icon'] . '"></i> ' : '';
			$class  = (!empty($type['class'])) ? $type['class'] : '';
			$layout = (!empty($type['layout'])) ? $type['layout'] : null;

			if (isset($type['heading']) && ($type['heading'] === true))
			{
				$cGroup  = 'group-' . $class;
				$heading = '<li class="' . $cGroup . '"><h3>' . $icon . $text . '</h3></li>';

				continue;
			}

			if (($view == 'dashboard' && !in_array(true, RedshopbHelperACL::getViewPermissions()))
				|| ($view != 'dashboard' && !RedshopbHelperACL::allowDisplayView($view)))
			{
				continue;
			}

			// If shop use as catalog, then disable all view relate with checkout and prices
			if (!$isShop && in_array($view, RedshopbHelperCommon::getPriceViews()))
			{
				continue;
			}

			if ($isFromMainCompany && $view == 'mypage')
			{
				continue;
			}

			$item       = 'item-' . $class;
			$class      = 'class="' . $cGroup . ' ' . $item;
			$childsHtml = '';

			if (isset($type['childs']) && count($type['childs']) > 0)
			{
				$class       .= ' dropdown-submenu';
				$rendererData = $this->rendererSidebar($type['childs'], $active, true);
				$childsHtml   = $rendererData['html'];
				$childActive  = $rendererData['childActive'];
			}

			$class  .= '"';
			$html   .= $heading . '<li ' . $class . '>';
			$heading = '';
			$class   = '';
			$icon    = '';

			if ($active === $view)
			{
				$class       = ' class="active"';
				$childActive = true;
			}
			elseif (isset($type['childs']) && count($type['childs']) > 0 && $childActive == true)
			{
				$class       = ' class="active"';
				$childActive = false;
			}

			if (isset($type['icon']))
			{
				$icon = '<i class="' . $type['icon'] . '"></i> ';
			}

			$link = 'index.php?option=com_redshopb&view=' . $type['view'];

			if (null !== $layout)
			{
				$link .= '&layout=' . $layout;
			}

			$anchor = '<a' . $class . ' href="' . RedshopbRoute::_($link);

			$anchor .= $query . '">' . $icon . $text . '</a>' . $childsHtml;
			$html   .= $anchor . '</li>';
		}

		return array('html' => $html . '</ul>', 'childActive' => $childActive);
	}
}
