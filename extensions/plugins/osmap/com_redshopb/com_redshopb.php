<?php
/**
 * @package    Plugin
 *
 * @copyright  Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use \Alledia\OSMap\Sitemap\Collector;
use \Alledia\OSMap\Sitemap\Item;
use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Uri\Uri;


/**
 * Class osmap_com_redshopb
 *
 * @since  1
 */
class Osmap_Com_Redshopb
{
	/**
	 * @var array
	 */
	protected static $categoriesCache = array();

	/**
	 * @var string
	 */
	protected static $language = null;

	/**
	 * prepareMenuItem
	 *
	 * @param   Item   $node    Node Object
	 * @param   array  $params  Params array
	 *
	 * @return  boolean
	 */
	public static function prepareMenuItem($node, &$params)
	{
		$linkQuery = parse_url($node->link);

		parse_str(html_entity_decode($linkQuery['query']), $linkVars);

		$id         = ArrayHelper::getValue($linkVars, 'id', null);
		$categoryId = ArrayHelper::getValue($linkVars, 'category_id', null);
		$view       = ArrayHelper::getValue($linkVars, 'view', '');
		$layout     = ArrayHelper::getValue($linkVars, 'layout', '');

		if (!($view == 'shop' && in_array($layout, array('category', 'product', 'categories'))))
		{
			return false;
		}

		if ($layout == 'category' && !$id)
		{
			return false;
		}

		if ($layout == 'product' && (!$categoryId || !$id))
		{
			return false;
		}

		$node->uid  = 'redshopb.' . $view . '.layout.' . $layout . '.' . $id;
		$node->info = $linkVars;
		$node->type = $layout;

		if ($layout == 'category')
		{
			$category = RedshopbEntityCategory::getInstance($id);
			$category->getItem();

			if ($category->get('state', 0) != 1)
			{
				return false;
			}

			$modifiedDate = Date::getInstance($category->get('modified_date', '0'))->toUnix();
			$createdDate  = Date::getInstance($category->get('created_date', '0'))->toUnix();
			$modified     = max($createdDate, $modifiedDate);

			if ($modified > 0)
			{
				$node->modified = $modified;
			}
		}

		return true;
	}

	/**
	 * getTree
	 *
	 * @param   Collector  $collector   Controller Object
	 * @param   Item       $parent      Parent Object
	 * @param   array      $params      Params
	 *
	 * @return boolean
	 */
	public static function getTree($collector, $parent, $params)
	{
		$params = new Registry($params);

		if ($parent->type == 'category')
		{
			self::getCategoryTree($parent->info['id'], $collector, $parent, $params);
		}
		elseif ($parent->type == 'categories')
		{
			self::getCategoryTree(1, $collector, $parent, $params);
		}

		return true;
	}

	/**
	 * getCategoryTree
	 *
	 * @param   integer    $categoryId  CategoryId
	 * @param   Collector  $collector   Controller Object
	 * @param   Item       $parent      Parent Object
	 * @param   Registry   $params      Params
	 *
	 * @return boolean
	 */
	protected static function getCategoryTree($categoryId, $collector, $parent, $params)
	{
		$category = RedshopbEntityCategory::getInstance($categoryId);
		$category->getItem();

		if ($category->get('state', 0) != 1)
		{
			return false;
		}

		$children = $category->getChildren();

		if (!empty($children))
		{
			$collector->changeLevel(1);

			/** @var RedshopbEntityCategory $row */
			foreach ($children AS $row)
			{
				if ($row->get('state', 0) != 1)
				{
					continue;
				}

				$node = new stdClass;

				$node->id         = $parent->id;
				$node->uid        = $parent->uid . '.category.' . $row->get('id', null);
				$node->browserNav = $parent->browserNav;
				$node->name       = htmlspecialchars_decode(stripslashes($row->get('name', null)));
				$node->link       = 'index.php?option=com_redshopb&view=shop&layout=category&id=' . $row->get('id', null);

				$modifiedDate = Date::getInstance($row->get('modified_date', '0'))->toUnix();
				$createdDate  = Date::getInstance($row->get('created_date', '0'))->toUnix();
				$modified     = max($createdDate, $modifiedDate);

				if ($modified > 0)
				{
					$node->modified = $modified;
				}

				if ($params->get('category_priority', '-1') == '-1')
				{
					$node->priority = $parent->priority;
				}
				else
				{
					$node->priority = $params->get('category_priority', '-1');
				}

				if ($params->get('category_changefreq', '-1') == '-1')
				{
					$node->changefreq = (string) $parent->changefreq;
				}
				else
				{
					$node->changefreq = (string) $params->get('category_changefreq', 'monthly');
				}

				if ($collector->printNode($node) !== false)
				{
					self::getCategoryTree($row->get('id'), $collector, $parent, $params);
				}
			}

			$collector->changeLevel(-1);
		}

		// In version i test of redshopb this not working.
		$products = self::getProducts($categoryId); /** $category->searchProducts(array('list.ordering' => 'p.name')); */

		if (!empty($products))
		{
			self::buildProductTree($products, $collector, $parent, $params);
		}

		return true;
	}

	/**
	 * Builds the product links for a category
	 *
	 * @param   array       $products    The products to create links for
	 * @param   Collector   $collector   Collector
	 * @param   Item        $parent      Item
	 * @param   Registry    $params      Params
	 *
	 * @return   void
	 */
	protected static function buildProductTree($products, $collector, $parent, $params)
	{
		$collector->changeLevel(1);

		foreach ($products AS $row)
		{
			if ($row->state != 1)
			{
				continue;
			}

			$row  = new Registry($row);
			$node = new stdClass;

			$node->id         = $parent->id;
			$node->uid        = $parent->uid . '.product.' . $row->get('id', null);
			$node->browserNav = $parent->browserNav;
			$node->name       = htmlspecialchars_decode(stripslashes($row->get('name', null)));
			$node->link       = 'index.php?' . http_build_query(
				array(
					'option'        => 'com_redshopb',
					'category_id'   => $row->get('category_id'),
					'id'            => $row->get('id'),
					'layout'        => 'product',
					'view'          => 'shop'
				)
			);

			$modifiedDate = Date::getInstance($row->get('modified_date', '0'))->toUnix();
			$createdDate  = Date::getInstance($row->get('created_date', '0'))->toUnix();
			$modified     = max($createdDate, $modifiedDate);

			if ($modified > 0)
			{
				$node->modified = $modified;
			}

			if ($params->get('product_priority', '-1') == '-1')
			{
				$node->priority = $parent->priority;
			}
			else
			{
				$node->priority = $params->get('product_priority', 0);
			}

			if ($params->get('product_changefreq', '-1') == '-1')
			{
				$node->changefreq = (string) $parent->changefreq;
			}
			else
			{
				$node->changefreq = (string) $params->get('product_changefreq', 'monthly');
			}

			$collector->printNode($node);
		}

		$collector->changeLevel(-1);
	}

	/**
	 * getProducts
	 *
	 * @param   integer  $categoryId  Category Id
	 *
	 * @return array
	 */
	protected static function getProducts($categoryId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->from('#__redshopb_product')
			->select('id, category_id, name, modified_date, created_date, state')
			->where($db->qn('state') . ' = ' . $db->q(1))
			->where($db->qn('category_id') . ' = ' . $db->q($categoryId));

		return $db->setQuery($query)->loadObjectList();
	}
}
