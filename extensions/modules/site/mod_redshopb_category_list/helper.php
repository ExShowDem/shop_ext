<?php
/**
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_category_list
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Helper for mod_redshopb_category_list
 *
 * @package     Aesir.E-Commerce.Site
 * @subpackage  mod_redshopb_category_list
 * @since       1.0.0
 */
class ModRedshopbCategoryListHelper
{
	/**
	 * Check if Category has children
	 *
	 * @param   int  $id  The category ID
	 *
	 * @return mixed
	 */
	public static function checkChildExists($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(id)')
			->from($db->qn('#__redshopb_category'))
			->where('state = 1')
			->where('parent_id = ' . (int) $id);

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Get a list of the sub-categories.
	 *
	 * @param   Registry  $params  The module options.
	 *
	 * @return  null|object
	 */
	public static function getList(&$params)
	{
		$app        = Factory::getApplication();
		$input      = $app->input;
		$categoryId = $params->get('category_id');

		// For option get category Id from current page.
		if ($categoryId == 'current')
		{
			if (!$input->get('option') == 'com_redshopb' || !$input->get('view') == 'shop' || !$input->get('layout') == 'category')
			{
				return null;
			}

			$categoryId = $input->getId('id', 0);
		}
		else
		{
			$categoryId = (int) $categoryId;
		}

		// Re-check again. Make sure category id is available and it's has sub-categories
		if (!$categoryId || !self::checkChildExists($categoryId))
		{
			return null;
		}

		$customerId   = $app->getUserState('shop.customer_id', 0);
		$customerType = $app->getUserState('shop.customer_type', '');
		$user         = Factory::getUser();

		$url          = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=shop&layout=categories');
		$uri          = Uri::getInstance($url);
		$rootRedShopB = (int) $uri->getVar('Itemid', 0);

		$values = new stdClass;
		RedshopbHelperShop::setUserStates($values);

		// Just get 1 level sub-categories
		$endLevel = 1;

		// Get configuration from module
		$hideEmpty = (boolean) $params->get('hide_empty', true);
		$order     = (string) $params->get('order', 'c.lft');
		$limit     = (int) $params->get('count', 5);

		$collections = RedshopbHelperCollection::getCustomerCollectionsForShop($values->customerId, $values->customerType);

		// Load list of sub-categories with just 1 level
		$categories = RedshopbHelperACL::listAvailableCategories(
			$user->id,
			$categoryId,
			$endLevel,
			$values->companyId,
			$collections,
			'objectList',
			'',
			'redshopb.category.view',
			0,
			$limit,
			false,
			$hideEmpty,
			$order,
			array(),
			false
		);

		if (empty($categories))
		{
			return null;
		}

		$categoryIds = array();

		foreach ($categories as $category)
		{
			$categoryIds[] = $category->id;
		}

		// Cache parent categories
		RedshopbHelperCategory::getParentCategories($customerId, $customerType, $categoryIds, true);

		foreach ($categories as $category)
		{
			$category->url = RedshopbHelperRoute::getRoute('index.php?option=com_redshopb&view=shop&layout=category&id=' . $category->id);
		}

		return $categories;
	}
}
