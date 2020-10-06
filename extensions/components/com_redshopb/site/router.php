<?php
/**
 * @package    Redshopb.Site
 *
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Factory;

/**
 * Routing class from com_redshopb
 *
 * @since  3.3
 */
class RedshopbRouter extends RouterBase
{
	/**
	 * Build the route for the com_redshopb component
	 *
	 * @param   array  $query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	public function build(&$query)
	{
		$segments = array();

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem      = $this->menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem      = $this->menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && (isset($menuItem) ? $menuItem->component != 'com_redshopb' : true))
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		if (!isset($query['view']))
		{
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}

		if ($menuItemGiven ? (!isset($menuItem->query['view']) || $query['view'] != $menuItem->query['view']) : true)
		{
			$segments[] = $query['view'];
		}

		unset($query['view']);

		/*
		 * If the layout is specified and it is the same as the layout in the menu item, we
		 * unset it so it doesn't go into the query string.
		 */
		if (isset($query['layout']))
		{
			if ($menuItemGiven && isset($menuItem->query['layout']))
			{
				if ($query['layout'] == $menuItem->query['layout'])
				{
					unset($query['layout']);
				}
			}
			else
			{
				if ($query['layout'] == 'default')
				{
					unset($query['layout']);
				}
			}
		}

		if (isset($query['layout']))
		{
			$segments[] = $query['layout'];
			unset($query['layout']);
		}

		if (isset($query['collection']) && $query['collection'] == 0)
		{
			unset($query['collection']);
		}

		if (isset($query['id']))
		{
			$segments[] = $query['id'];
			unset($query['id']);
		}

		return $segments;
	}

	/**
	 * Generic method to preprocess a URL
	 *
	 * @param   array  $query  An associative array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   2.4.1
	 * @throws Exception
	 */
	public function preprocess($query)
	{
		$queryString = parse_url(
			RedshopbHelperRoute::getRoute(
				'index.php?' . urldecode(
					http_build_query(
						$query,
						'',
						'&'
					)
				)
			),
			PHP_URL_QUERY
		);

		$query = [];
		parse_str($queryString, $query);

		return $query;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  $segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 */
	public function parse(&$segments)
	{
		$vars = array();
		$item = $this->menu->getActive();

		// Count route segments
		$count = count($segments);

		/*
		 * Standard routing for articles.  If we don't pick up an Itemid then we get the view from the segments
		 * the first segment is the view and the last segment is the id of the item.
		 */
		if (!isset($item) && isset($segments[0]))
		{
			$vars['view'] = $segments[0];

			if (isset($segments[1]) && !is_numeric($segments[1]))
			{
				$vars['layout'] = $segments[1];
			}

			if (is_numeric($segments[$count - 1]))
			{
				$vars['id'] = $segments[$count - 1];
			}
		}
		else
		{
			foreach ($item->query as $key => $queryValue)
			{
				$vars[$key] = $queryValue;
			}

			if (isset($vars['view']) && $vars['view'] == 'shop')
			{
				if (isset($segments[0]) && !is_numeric($segments[0]))
				{
					$vars['layout'] = $segments[0];
				}
			}
			else
			{
				if (isset($segments[0]) && !is_numeric($segments[0]))
				{
					$vars['view'] = $segments[0];
				}

				if (isset($segments[1]) && !is_numeric($segments[1]))
				{
					$vars['layout'] = $segments[1];
				}
			}

			if (is_numeric($segments[$count - 1]))
			{
				$vars['id'] = $segments[$count - 1];
			}
		}

		return $vars;
	}
}

/**
 * Content router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function redshopbBuildRoute(&$query)
{
	$router = new RedshopbRouter;

	return $router->build($query);
}

/**
 * Parse the segments of a URL.
 *
 * This function is a proxy for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @since   3.3
 * @deprecated  4.0  Use Class based routers instead
 */
function redshopbParseRoute($segments)
{
	$router = new RedshopbRouter;

	return $router->parse($segments);
}
