<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Webservice Permission Helper
 *
 * @package     Aesir.E-Commerce
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperWebservice_Permission
{
	/**
	 * List of permissions from Redshopb table grouped by scope
	 *
	 * @var  array
	 */
	public static $permissions = array();

	/**
	 * Gets Webservice permissions for a specific scope
	 *
	 * @param   string  $scope  Only load specific scope
	 *
	 * @return  array
	 */
	public static function getWebservicePermissions($scope = '')
	{
		if (empty(self::$permissions))
		{
			$db = Factory::getDbo();

			// Get list executed in previous sync items
			$query = $db->getQuery(true)
				->select('wp.*')
				->from($db->qn('#__redshopb_webservice_permission', 'wp'))
				->where('wp.state = 1')
				->order($db->qn('wp.name') . ' ASC');

			$db->setQuery($query);

			$permissions       = $db->loadObjectList();
			self::$permissions = array();

			foreach ($permissions as $key => $permission)
			{
				self::$permissions[$permission->scope][$permission->id] = $permission;
			}
		}

		if (!empty($scope) && !empty(self::$permissions[$scope]))
		{
			return array($scope => self::$permissions[$scope]);
		}

		if (empty($scope))
		{
			return self::$permissions;
		}

		return array();
	}

	/**
	 * Gets Webservice permissions for a specific user
	 *
	 * @param   int  $userId  User ID
	 *
	 * @return  array
	 */
	public static function getWebservicePermissionsForUser($userId)
	{
		$db = Factory::getDbo();

		// Get list executed in previous sync items
		$query = $db->getQuery(true)
			->select('wpu.webservice_permission_id')
			->from($db->qn('#__redshopb_webservice_permission_user_xref', 'wpu'))
			->leftJoin($db->qn('#__redshopb_webservice_permission', 'wp') . ' ON wp.id = wpu.webservice_permission_id')
			->where('wp.state = 1')
			->where('wpu.user_id = ' . (int) $userId);

		$db->setQuery($query);

		return $db->loadColumn(0);
	}

	/**
	 * Calculates Permission IDs for a specific scope and item
	 *
	 * @param   mixed   $items  Item to check for permission rules
	 * @param   string  $scope  Only load specific scope
	 *
	 * @return  boolean
	 */
	public static function savePermissionsForProduct($items, $scope = 'product')
	{
		if (!is_array($items))
		{
			$items = array($items);
		}

		$model = RedshopbModel::getAdminInstance('Webservice_Permission_Item', array(), 'com_redshopb');

		foreach ($items as $item)
		{
			$permissions = self::getPermissionsIdsFromProductItem($item);

			$data = array(
				'item_id' => $item->id,
				'scope' => $scope,
				'webservice_permission_ids' => $permissions
			);

			if (!$model->save($data))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Gets Permission IDs for a specific scope and item
	 *
	 * @param   object  $item   Item to check for permission rules
	 * @param   string  $scope  Only load specific scope
	 *
	 * @return  array of allowed permission IDs
	 */
	public static function getPermissionsIdsFromProductItem($item, $scope = 'product')
	{
		$permissions        = self::getWebservicePermissions($scope);
		$allowedPermissions = array();

		if (isset($permissions[$scope]))
		{
			foreach ($permissions[$scope] as $permission)
			{
				switch ($permission->name)
				{
					case 'Plumbing (VVS)':
						if (self::startsWith($item->sku, '1'))
						{
							$allowedPermissions[] = $permission->id;
						}
						break;
					case 'Steel (Stål)':
						if (self::startsWith($item->sku, '2') || self::startsWith($item->sku, '102'))
						{
							$allowedPermissions[] = $permission->id;
						}
						break;
					case 'Tools (Værktøj)':
						if (self::startsWith($item->sku, '3'))
						{
							$allowedPermissions[] = $permission->id;
						}
						break;
				}
			}
		}

		return $allowedPermissions;
	}

	/**
	 * Check if the string starts with specific pattern
	 *
	 * @param   string  $haystack  String to search on
	 * @param   string  $needle    Needle we are looking for
	 *
	 * @return  boolean
	 */
	public static function startsWith($haystack, $needle)
	{
		return substr($haystack, 0, strlen($needle)) === $needle;
	}

	/**
	 * Check WS permission restrictions for the specific item ID
	 *
	 * @param   int     $id      Item identifier
	 * @param   object  $model   Model
	 *
	 * @return  boolean
	 */
	public static function checkWSPermissionRestriction($id, &$model)
	{
		if (RedshopbApp::getConfig()->get('use_webservice_permission_restriction', 0) == 1)
		{
			$modelName = $model->getName();

			// We can check here restrictions on primary key for the getItem function
			switch ($modelName)
			{
				// Customized function for categories because they are derived from product items
				case 'category':
					return self::checkWSPermissionRestrictionCategory($id, 'category') == 1;

				// Default function that should work properly for all items
				case 'product':
					return self::checkWSPermissionRestrictionItem($id, 'product') == 1;

				// Default function that should work properly for all items
				case 'field':
					return self::checkWSPermissionRestrictionItem($id, 'field') == 1;
			}
		}

		return true;
	}

	/**
	 * Check WS permission restrictions for the specific ID
	 *
	 * @param   object  $item   Loaded item object
	 * @param   object  $model  Model
	 *
	 * @return  boolean
	 */
	public static function checkWSPermissionRestrictionRelations(&$item, &$model)
	{
		if ($item && RedshopbApp::getConfig()->get('use_webservice_permission_restriction', 0) == 1)
		{
			$modelName = $model->getName();

			// We can check here restrictions on relation key for the getItem function
			switch ($modelName)
			{
				// Default function that should work properly for all items
				case 'field_data':
				case 'field_value':
					return self::checkWSPermissionRestrictionItem($item->field_id, 'field') == 1;
			}
		}

		return true;
	}

	/**
	 * Check Item WS permission restrictions for the specific ID
	 *
	 * @param   int     $id     The input query from getListQuery function
	 * @param   string  $scope  Scope
	 *
	 * @return  boolean
	 */
	public static function checkWSPermissionRestrictionItem($id, $scope)
	{
		$db = Factory::getDbo();

		$query = self::addWSPermissionRestrictionQueryItemBase($scope)
			->where('wpi.item_id = ' . $id);

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Check Item WS permission restrictions for the specific ID
	 *
	 * @param   int     $id     The input query from getListQuery function
	 * @param   string  $scope  Scope
	 *
	 * @return  boolean
	 */
	public static function checkWSPermissionRestrictionCategory($id, $scope)
	{
		// We change scope to product since this is customization for categories
		$scope = 'product';
		$db    = Factory::getDbo();

		$productAvailable = self::addWSPermissionRestrictionQueryItemBase($scope)
			->innerJoin($db->qn('#__redshopb_product', 'pref') . ' ON ' . $db->qn('wpi.item_id') . ' = ' . $db->qn('pref.id'))
			->where('prodcat.id = pref.category_id');

		$categoryAvailable = $db->getQuery(true)
			->select('prodcat.id, prodcat.lft')
			->from($db->qn('#__redshopb_category', 'prodcat'))
			->where('(' . $productAvailable . ' LIMIT 0, 1) IS NOT NULL')
			->group('prodcat.id');

		$query = $db->getQuery(true)
			->select('1')
			->from($db->qn('#__redshopb_category', 'allow_categories'))
			->leftJoin('(' . $categoryAvailable . ') AS pr ON pr.lft BETWEEN allow_categories.lft AND allow_categories.rgt')
			->where('allow_categories.id = ' . (int) $id)
			->where('pr.id IS NOT NULL');

		return $db->setQuery($query)
			->loadResult();
	}

	/**
	 * Adds WS permission restrictions to the query using a specific field and mapping
	 *
	 * @param   JDatabaseQuery  $query  The input query from getListQuery function
	 * @param   object          $model  Model
	 *
	 * @return  void
	 */
	public static function addWSPermissionRestrictionQuery(&$query, &$model)
	{
		if (RedshopbApp::getConfig()->get('use_webservice_permission_restriction', 0) == 1)
		{
			$modelName  = $model->getName();
			$identifier = 'id';

			switch ($modelName)
			{
				// Customized function for categories because they are derived from product items
				case 'categories':
					self::addWSPermissionRestrictionQueryCategory($query, $model, 'category', $identifier);
					break;

				// Default function that should work properly for all items
				case 'products':
					self::addWSPermissionRestrictionQueryItem($query, $model, 'product', $identifier);
					break;

				// Default function that should work properly for all items
				case 'fields':
				case 'field_datas':
				case 'field_values':
					$identifier = $modelName != 'fields' ? 'field_id' : $identifier;
					self::addWSPermissionRestrictionQueryItem($query, $model, 'field', $identifier);
					break;
			}
		}
	}

	/**
	 * Adds Item WS permission restrictions to the query using a specific field and mapping
	 *
	 * @param   JDatabaseQuery  $query       The input query from getListQuery function
	 * @param   object          $model       Field names to map
	 * @param   string          $scope       Scope name
	 * @param   string          $identifier  Identifier key
	 *
	 * @return  void
	 */
	public static function addWSPermissionRestrictionQueryItem(&$query, &$model, $scope = 'product', $identifier = 'id')
	{
		$mainPrefix = $model->get('mainTablePrefix', '') ? $model->get('mainTablePrefix', '') . '.' : '';
		$subQuery   = self::addWSPermissionRestrictionQueryItemBase($scope)
			->where('wpi.item_id = ' . $mainPrefix . $identifier);

		$query->where('EXISTS (' . $subQuery . ')');
	}

	/**
	 * Adds Item WS permission restrictions to the query using a specific field and mapping
	 *
	 * @param   JDatabaseQuery  $query       The input query from getListQuery function
	 * @param   object          $model       Field names to map
	 * @param   string          $scope       Scope name
	 * @param   string          $identifier  Identifier key
	 *
	 * @return  void
	 */
	public static function addWSPermissionRestrictionQueryCategory(&$query, &$model, $scope = 'category', $identifier = 'id')
	{
		// We change scope to product since this is customization for categories
		$scope            = 'product';
		$db               = Factory::getDbo();
		$mainPrefix       = $model->get('mainTablePrefix', '') ? $model->get('mainTablePrefix', '') . '.' : '';
		$productAvailable = self::addWSPermissionRestrictionQueryItemBase($scope)
			->innerJoin($db->qn('#__redshopb_product', 'pref') . ' ON ' . $db->qn('wpi.item_id') . ' = ' . $db->qn('pref.id'))
			->where('prodcat.id = pref.category_id');

		$categoryAvailable = $db->getQuery(true)
			->select('prodcat.id, prodcat.lft')
			->from($db->qn('#__redshopb_category', 'prodcat'))
			->where('(' . $productAvailable . ' LIMIT 0, 1) IS NOT NULL')
			->group('prodcat.id');

		$treeCategoriesAvailable = $db->getQuery(true)
			->select('allow_categories.id')
			->from($db->qn('#__redshopb_category', 'allow_categories'))
			->leftJoin('(' . $categoryAvailable . ') AS pr ON pr.lft BETWEEN allow_categories.lft AND allow_categories.rgt')
			->where('pr.id IS NOT NULL')
			->group('allow_categories.id');

		$query->where($mainPrefix . $identifier . ' IN (' . $treeCategoriesAvailable . ')');
	}

	/**
	 * Adds Item WS permission restrictions base query
	 *
	 * @param   string  $scope  Scope name
	 *
	 * @return  JDatabaseQuery
	 */
	public static function addWSPermissionRestrictionQueryItemBase($scope = 'product')
	{
		$user   = Factory::getUser();
		$userId = $user->get('id');
		$db     = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('1')
			->from($db->qn('#__redshopb_webservice_permission_item_xref', 'wpi'))
			->leftJoin($db->qn('#__redshopb_webservice_permission', 'wp') . ' ON wp.id = wpi.webservice_permission_id')
			->leftJoin(
				$db->qn('#__redshopb_webservice_permission_user_xref', 'wpu') . ' ON wpu.webservice_permission_id = wpi.webservice_permission_id'
			)
			->where('wp.state = 1')
			->where('wpi.scope = ' . $db->q($scope))
			->where('wpu.user_id = ' . (int) $userId);

		return $query;
	}
}
