<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_categories
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
/**
 * Helper for mod_redshopb_categories
 *
 * @package    Aesir.E-Commerce.Site
 * @subpackage mod_redshopb_categories
 * @since      1.6.33
 */
class ModRedshopbCategoriesHelper
{
	/**
	 * Get a list of the menu items.
	 *
	 * @param   Registry $params The module options.
	 *
	 * @return null|object
	 */
	public static function getList(&$params)
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		if ($input->get('view') == 'shop' && $input->get('layout') == 'categories')
		{
			return null;
		}

		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$url          = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=shop&layout=categories');
		$uri          = Uri::getInstance($url);
		$rootRedShopB = (int) $uri->getVar('Itemid', 0);
		$menu         = $app->getMenu();
		$parent       = $menu->getItem($rootRedShopB);
		$values       = new stdClass;
		RedshopbHelperShop::setUserStates($values);
		$end              = (int) $params->get('endLevel', 999);
		$hideEmpty        = (boolean) $params->get('hide_empty', true);
		$usingCollections = (boolean) $app->input->get('mycollections', false)
			|| RedshopbHelperShop::inCollectionMode(RedshopbEntityCompany::getInstance($customerId));

		$id                   = $input->getInt('id', 0);
		$layout               = $input->getCmd('layout', '');
		$parent->redshopbPath = array();
		$parent->current      = 0;

		if ($input->getCmd('option', '') == 'com_redshopb'
			&& $input->getCmd('view', '') == 'shop'
		)
		{
			if ($layout == 'category' && $id)
			{
				if ($params->get('hideForEndLevel', 0) == 1 && !self::checkChildExists($id))
				{
					return null;
				}

				$parent->current = $id;

				$result = RedshopbHelperCategory::getParentCategories($customerId, $customerType, $id, true);

				if ($result)
				{
					$parent->redshopbPath = $result;
				}
			}
			elseif ($layout == 'product' && $id)
			{
				if ($params->get('hideForEndLevel', 0) == 1)
				{
					return null;
				}

				$product = RedshopbHelperProduct::loadProduct($id);

				if ($product)
				{
					if (!empty($product->categories))
					{
						$categoryId = $input->getInt('category_id', 0);

						if (in_array($categoryId, $product->categories))
						{
							$parent->current = $categoryId;
						}
						else
						{
							$parent->current = $product->categories[0];
						}

						$result = RedshopbHelperCategory::getParentCategories($customerId, $customerType, $parent->current, true);

						if ($result)
						{
							$parent->redshopbPath = $result;
						}
					}
				}
			}

			$input              = Factory::getApplication()->input->getArray();
			$layout             = (isset($input['collection_id']) ? true : false);
			$collections        = RedshopbHelperCollection::getCustomerCollectionsForShop(
				$values->customerId, $values->customerType, array(), $layout
			);
			$redshopbCategories = RedshopbHelperACL::listAvailableCategories(
				Factory::getUser()->id,
				1,
				$end,
				$values->companyId,
				$collections,
				'objectList',
				'',
				'redshopb.category.view',
				0,
				0,
				false,
				$hideEmpty,
				'c.lft',
				array(),
				false
			);

			$lastRBItem  = 0;
			$countChilds = array();
			$ids         = array();
			$start       = (int) $params->get('startLevel');
			$categories  = array();

			foreach ($redshopbCategories as $i => $redshopbCategory)
			{
				   $id              = $redshopbCategory->id;
				   $categories[$id] = $redshopbCategory;

				if (!isset($categories[$id]->tree))
				{
					$categories[$id]->tree = array();
				}

				if ($parent->current == $id)
				{
					$start = 2;
				}

					  $categories[$id]->tree = self::setCategoryTree($categories, $id);
			}

			// Cache parent categories
			RedshopbHelperCategory::getParentCategories($customerId, $customerType, array_keys($categories), true);

			foreach ($redshopbCategories as $i => $redshopbCategory)
			{
					 $relationLevel = $redshopbCategory->level;

				if (($start && $start > $relationLevel)
					|| ($end && $relationLevel > $end)
					|| ($start > 1 && !in_array($redshopbCategory->tree[$start - 2], $parent->redshopbPath))
				)
				{
					unset($redshopbCategories[$i]);
					continue;
				}

				if (!isset($countChilds[$redshopbCategory->parent_id]))
				{
					$countChilds[$redshopbCategory->parent_id] = 0;
				}

					 $countChilds[$redshopbCategory->parent_id]++;
					 $redshopbCategory->deeper        = false;
					 $redshopbCategory->shallower     = false;
					 $redshopbCategory->level_diff    = 0;
					 $redshopbCategory->relationLevel = $relationLevel;
					 $ids[$redshopbCategory->id]      = $i;
					 $redshopbCategory->parent        = false;

				if (isset($redshopbCategories[$lastRBItem]))
				{
					$redshopbCategories[$lastRBItem]->deeper     = ($relationLevel > $redshopbCategories[$lastRBItem]->relationLevel);
					$redshopbCategories[$lastRBItem]->shallower  = ($relationLevel < $redshopbCategories[$lastRBItem]->relationLevel);
					$redshopbCategories[$lastRBItem]->level_diff = ($redshopbCategories[$lastRBItem]->relationLevel - $relationLevel);
				}

				if (isset($ids[$redshopbCategory->parent_id]))
				{
					$parentId                              = $ids[$redshopbCategory->parent_id];
					$redshopbCategories[$parentId]->parent = true;
				}

					 $lastRBItem               = $i;
					 $redshopbCategory->active = false;

				if ($layout == 'collection')
				{
					$redshopbCategory->flink = RedshopbRoute::_(
						'index.php?option=com_redshopb&view=shop&layout=category&id=' . $redshopbCategory->id .
						'&collection_id=' . (is_array($input['collection_id']) ? $input['collection_id'][0] : $input['collection_id'])
					);
				}
				else
				{
					$redshopbCategory->flink = RedshopbRoute::_(
						'index.php?option=com_redshopb&view=shop&layout=category&id=' . $redshopbCategory->id .
						($usingCollections ? '&mycollections=1' : '')
					);
				}

					 $redshopbCategory->browserNav = $parent->browserNav;

					 // We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
					 // when the cause of that is found the argument should be removed
					 $redshopbCategory->title = htmlspecialchars($redshopbCategory->name, ENT_COMPAT, 'UTF-8', false);
			}

			if (isset($redshopbCategories[$lastRBItem]))
			{
					 $redshopbCategories[$lastRBItem]->deeper     = (($start ? $start : 1) > $redshopbCategories[$lastRBItem]->relationLevel);
					 $redshopbCategories[$lastRBItem]->shallower  = (($start ? $start : 1) < $redshopbCategories[$lastRBItem]->relationLevel);
					 $redshopbCategories[$lastRBItem]->level_diff = ($redshopbCategories[$lastRBItem]->relationLevel - ($start ? $start : 1));
			}

			$parent->redShopBCategories = $redshopbCategories;
			$parent->countChilds        = $countChilds;
		}

		return $parent;
	}

	/**
	 * Check if Category has children
	 *
	 * @param   int $id The category ID
	 *
	 * @return mixed
	 */
	public static function checkChildExists($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshopb_category'))
			->where('state = 1')
			->where('parent_id = ' . (int) $id);

		return $db->setQuery($query, 0, 1)->loadResult();
	}

	/**
	 * Ste category tree
	 *
	 * @param   array $categories All categories
	 * @param   int   $id         Id current category
	 *
	 * @return array
	 */
	public static function setCategoryTree(&$categories, $id)
	{
		$tree = array();

		if ($categories[$id]->level > 1)
		{
			$tree   = array_merge($tree, self::setCategoryTree($categories, $categories[$id]->parent_id));
			$tree[] = (int) $categories[$id]->parent_id;
		}

		return $tree;
	}

	/**
	 * Build link attributes to string
	 *
	 * @param   array $attr Array link attributes
	 *
	 * @return string
	 */
	public static function getLinkAttributes($attr)
	{
		return implode(
			' ',
			array_map(
				function ($val, $var) {
					return sprintf('%s="%s"', $var, $val);
				},
				$attr,
				array_keys($attr)
			)
		);
	}

	/**
	 * Set browser navigation attributes
	 *
	 * @param   object $item Current menu item
	 * @param   array  $attr Array item attributes
	 *
	 * @return array
	 */
	public static function setBrowserNav($item, $attr = array())
	{
		switch ($item->browserNav)
		{
			case 1:
				// _blank
				$attr['target'] = '_blank';
			break;
			case 2:
				// Use JavaScript "window.open"
				$attr['onclick'] = "window.open(this.href,'targetWindow'"
				. ",'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;";
			break;
		}

		return $attr;
	}
}
