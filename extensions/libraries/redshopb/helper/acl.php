<?php
/**
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\HTML\HTMLHelper;


/**
 * ACL helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperACL
{
	/**
	 * Gets the permission for an specific (or core) action.  Rule = <component>.<objectType>.<permission>[.own]
	 *
	 * @param   string  $permission       required   Permission to check
	 * @param   string  $objectType       optional   Object to check permission from
	 * @param   array   $corePermissions  optional   Core permissions to check with the first rule (create, delete, etc), additional to it (AND).
	 *                                               Only one core permission has to match at least
	 * @param   boolean $checkOwn         optional   For creating purposes, managing your own object may be enough (own)
	 * @param   int     $assetId          optional   Asset ID to check on (if ommited it will check on the core component)
	 * @param   string  $component        optional   Component to check out from
	 * @param   int     $userId           optional   Joomla User id used for getting permission
	 *
	 * @return  boolean
	 */
	public static function getPermission(
		$permission, $objectType = '', $corePermissions = array(),
		$checkOwn = true, $assetId = 0, $component = 'redshopb', $userId = 0
	)
	{
		$user = RedshopbHelperCommon::getUser($userId);

		// If Super Admin, returns true
		if ($user->authorise('core.admin'))
		{
			return true;
		}

		$redshopUser = $userId ? RedshopbEntityUser::loadFromJoomlaUser($userId) : RedshopbEntityUser::loadFromJoomlaUser();

		// If core component given, uses default Joomla ACL
		if ($component == 'core')
		{
			$view = Factory::getApplication()->input->get('view');

			if (!$view)
			{
				$view = Factory::getApplication()->getUserState('user.view');
			}

			// If b2c Mode enable, return true on specific permission on specific view.
			if (($permission == 'manage') && $user->b2cMode && in_array($view, array('shop', 'dashboard', 'campaign_products')))
			{
				return true;
			}

			return $user->authorise($component . '.' . $permission, 'com_redshopb');
		}

		/** @var RedshopbModelACL $aclModel */
		$aclModel = RedshopbModel::getAdminInstance('ACL', array(), 'com_redshopb');

		$returnVal = false;

		// If an asset ID is set, looks it up in the asset table.  Otherwise it uses the component asset
		if (!$assetId)
		{
			// Use default component
			if (in_array($component, array('core', 'redshopb')))
			{
				$assetId = RedshopbApp::getRootAsset()->id;
			}
			// Assemble default Asset name (core component asset)
			else
			{
				$assetTable = Table::getInstance('Asset');
				$assetTable->load(Array('name' => 'com_' . $component));
				$assetId = $assetTable->id;
			}
		}

		// Assembles the rule to check
		$rule = $component . ($objectType != '' ? '.' . $objectType : '') . '.' . $permission;

		// Gets B2B user ID from Joomla User ID
		$roleId = $redshopUser->getRole()->get('id');

		// Gets Access list and looks up the rule
		$access = $aclModel->getSingleAccess($rule);

		if ($access)
		{
			$aclRule = $aclModel->getEffectiveRule($access->id, $roleId, $assetId);

			if ($aclRule && $aclRule->id)
			{
				$returnVal = $aclRule->granted;
			}
		}

		// If own objects are also ways to authorize this rule, checks it out adding .own (OR joint)
		if ($checkOwn && !$returnVal)
		{
			$rule  .= '.own';
			$access = $aclModel->getSingleAccess($rule);

			if ($access)
			{
				$aclRule = $aclModel->getEffectiveRule($aclModel->getSingleAccess($rule)->id, $roleId, $assetId);

				if ($aclRule && $aclRule->id)
				{
					$returnVal = $aclRule->granted;
				}
			}
		}

		// If a core permission is being cleared, checks it
		if (!empty($corePermissions) <> '' && $returnVal)
		{
			$coreVal = false;

			foreach ($corePermissions as $corePermission)
			{
				$coreVal |= $user->authorise('core.' . $corePermission, 'com_redshopb');
			}

			$returnVal &= $coreVal;
		}

		return $returnVal;
	}

	/**
	 * Checks if some permission is granted in any part in or below a certain asset id
	 *
	 * @param   string $permission Permission to check
	 * @param   string $objectType optional   Object to check permission from
	 * @param   int    $assetId    optional   Asset ID to check on (if ommited it will check on the core component)
	 * @param   string $component  optional   Component to check out from
	 * @param   int    $userId     optional   Joomla User id used for getting permission
	 *
	 * @return  boolean
	 */
	public static function getPermissionInto(
		$permission, $objectType = '', $assetId = 0, $component = 'redshopb', $userId = 0
	)
	{
		$user = RedshopbHelperCommon::getUser($userId);

		// If Super Admin, returns true
		if ($user->authorise('core.admin'))
		{
			return true;
		}

		$redshopUser = $userId ? RedshopbEntityUser::loadFromJoomlaUser($userId) : RedshopbApp::getUser();

		/** @var RedshopbModelACL $aclModel */
		$aclModel = RedshopbModel::getAdminInstance('ACL');

		// If an asset ID is set, looks it up in the asset table.  Otherwise it uses the component asset
		if (!$assetId)
		{
			// Use default component
			if (in_array($component, array('core', 'redshopb')))
			{
				$assetId = RedshopbApp::getRootAsset()->id;
			}
			// Assemble default Asset name (core component asset)
			else
			{
				$assetTable = Table::getInstance('Asset');
				$assetTable->load(Array('name' => 'com_' . $component));
				$assetId = $assetTable->id;
			}
		}

		// Assembles the rule to check
		$rule = $component . ($objectType != '' ? '.' . $objectType : '') . '.' . $permission;

		// Gets B2B user ID from Joomla User ID
		$role   = $redshopUser->getRole();
		$roleId = $role ? $role->id : 0;

		// Gets Access list and looks up the rule
		$access = $aclModel->getSingleAccess($rule);

		if ($access)
		{
			return $aclModel->grantExistsInside($access->id, $roleId, $assetId);
		}

		return false;
	}

	/**
	 * Gets a global permission (non asset-specific) for a B2C user
	 *
	 * @param   string $permission Permission to check
	 * @param   string $objectType optional   Object to check permission from
	 *
	 * @return  boolean
	 */
	public static function getGlobalB2CPermission($permission, $objectType = '')
	{
		$user = RedshopbHelperCommon::getUser();

		if (!$user->b2cMode)
		{
			return false;
		}

		$assetId = RedshopbApp::getRootAsset()->id;

		// Assembles the rule to check
		$rule = 'redshopb' . ($objectType != '' ? '.' . $objectType : '') . '.' . $permission;

		// Looks for the employee role (the one with real access) to set it as a base
		$company = RedshopbEntityCompany::load($user->b2cCompany);
		$roles   = $company->searchRoles(
			array(
				'filter.allow_access' => 1,
				'filter.type'         => 'employee'
			)
		);

		if (!$roles->count())
		{
			return false;
		}

		$roleId = $roles->current()->getItem()->id;

		/** @var RedshopbModelACL $aclModel */
		$aclModel = RedshopbModel::getAdminInstance('ACL');

		// Gets Access list and looks up the rule
		$access = $aclModel->getSingleAccess($rule);

		if ($access)
		{
			return $aclModel->grantExistsInside($access->id, $roleId, $assetId);
		}

		return false;
	}

	/**
	 * Using global permission set, determines easily if the logged in user is a super admin
	 * (since it won't have a company or department assigned but possesses global permissions)
	 *
	 * @param   int $userId Joomla User ID for check if this user is super admin
	 *
	 * @return  boolean
	 */
	public static function isSuperAdmin($userId = 0)
	{
		return self::getPermission('admin', '', array(), false, 0, 'core', $userId);
	}

	/**
	 * Get a comma separated list of values from the session variables, if available
	 *
	 * @param   string $id         The ID (company/dept/user or other) trying to retrieve the list from
	 * @param   string $listName   Name of the list trying to get
	 * @param   string $permission Permission granted to this list
	 *
	 * @return  mixed    Comma separated list or FALSE if not found
	 */
	protected static function getSessionList($id, $listName, $permission)
	{
		$session    = Factory::getSession();
		$returnList = false;

		// Sets max expiration time to an hour
		if (time() - $session->get('aclList' . $listName . '_' . $id . '_' . $permission . '_Time', 0, 'com_redshopb') < 3600)
		{
			$returnList = $session->get('aclList' . $listName . '_' . $id . '_' . $permission, false, 'com_redshopb');
		}

		return $returnList;
	}

	/**
	 * Store a comma separated list of values to the session variables
	 *
	 * @param   integer $id         The ID (company/dept/user or other) owner of the list
	 * @param   string  $listName   Name of the list trying to store
	 * @param   string  $listValues Comma separated list so be stored
	 * @param   string  $permission Permission granted to this list
	 *
	 * @return  void
	 */
	protected static function setSessionList($id, $listName, $listValues, $permission)
	{
		$session = Factory::getSession();

		$session->set('aclList' . $listName . '_' . $id . '_' . $permission . '_Time', time(), 'com_redshopb');
		$session->set('aclList' . $listName . '_' . $id . '_' . $permission, $listValues, 'com_redshopb');

		// Inserts the list into the lists array, to keep track of the session variables stored so far
		$aclListsArray = Array();
		$aclLists      = $session->get('aclLists', '', 'com_redshopb');

		if ($aclLists != '')
		{
			$aclListsArray = json_decode($aclLists);
		}

		if (!in_array($listName . '_' . $id . '_' . $permission, $aclListsArray))
		{
			$aclListsArray[] = $listName . '_' . $id . '_' . $permission;
		}

		$aclLists = json_encode($aclListsArray);
		$session->set('aclLists', $aclLists, 'com_redshopb');
	}

	/**
	 * Checks the stored session lists and resets them
	 *
	 * @return  void
	 */
	public static function resetSessionLists()
	{
		$session = Factory::getSession();

		$aclLists = $session->get('aclLists', '', 'com_redshopb');

		if ($aclLists != '')
		{
			$aclListsArray = Array();
			$aclListsArray = json_decode($aclLists);

			foreach ($aclListsArray as $aclList)
			{
				$session->set('aclList' . $aclList . '_Time', 0, 'com_redshopb');
			}
		}
	}

	/**
	 * Checks the stored session list and reset it
	 *
	 * @param   string $listName List name to reset
	 *
	 * @return  void
	 */
	public static function resetSessionList($listName)
	{
		$session = Factory::getSession();
		$session->set('aclList' . $listName . '_Time', 0, 'com_redshopb');
	}

	/**
	 * List the available companies for a user view
	 *
	 * @param   integer $userId               The user ID trying to list companies
	 * @param   string  $listType             optional   'comma' (default) for comma separated IDs for dB
	 *                                                   'dropdown' for Drop down Select Box with items
	 *                                                   'objectList' for an objectList direct from the DB (queries all fields)
	 * @param   integer $parentId             optional   Parent company id for getting companies which are under parent one (parent included)
	 * @param   string  $tagProperties        optional   HTML tag properties for the drop down
	 * @param   string  $permission           optional   Permission to look out for
	 * @param   string  $filterName           optional   Search for some company name (does not cache)
	 * @param   bool    $includeParents       optional  Include parent companies for the given role
	 * @param   boolean $anotherUser          optional  Defines if it's querying for another user
	 *                                                  and not the logged in one to avoid checking super admin
	 * @param   boolean $excludeParentCompany optional  When using $parentId, excludes that company
	 * @param   boolean $excludeMainCompany   optional  Exclude the companies with type "main"
	 * @param   integer $start                optional  Query start
	 * @param   integer $limit                optional  Query limit
	 * @param   bool    $hideDeleted          optional  Select deleted companies or not
	 *
	 * @return  mixed    List of companies in the format selected
	 */
	public static function listAvailableCompanies(
		$userId, $listType = 'comma', $parentId = 0,
		$tagProperties = '', $permission = 'redshopb.company.view',
		$filterName = '', $includeParents = false, $anotherUser = false,
		$excludeParentCompany = false, $excludeMainCompany = false,
		$start = 0, $limit = 0, $hideDeleted = true
	)
	{
		if (self::isSuperAdmin())
		{
			return self::listAvailableCompaniesbyRole(
				0, $includeParents, $anotherUser, $listType,
				$parentId, $tagProperties, $permission, $filterName, $excludeParentCompany,
				$excludeMainCompany, $userId, $start, $limit, $hideDeleted
			);
		}
		else
		{
			$redshopUser = RedshopbEntityUser::loadFromJoomlaUser($userId);
			$roleId      = $redshopUser->getRole()->id;

			if ($roleId)
			{
				return self::listAvailableCompaniesbyRole(
					$roleId, $includeParents, $anotherUser, $listType, $parentId,
					$tagProperties, $permission, $filterName, $excludeParentCompany,
					$excludeMainCompany, $userId, $start, $limit, $hideDeleted
				);
			}
		}

		return '0';
	}

	/**
	 * List the available companies for a role (effective queries for listAvailableCompanies)
	 *
	 * @param   integer $roleId               Role ID trying to list companies
	 * @param   bool    $includeParents       optional  Include parent companies for the given role
	 * @param   boolean $anotherUser          optional  Defines if it's querying for another user and
	 *                                                  not the logged in one to avoid checking super admin
	 * @param   string  $listType             optional  'comma' (default) for comma separated IDs for dB
	 *                                                  'dropdown' for Drop down Select Box with items
	 *                                                  'objectList' for an objectList direct from the DB (queries all fields)
	 * @param   integer $parentId             optional  Parent company id for getting companies which are under parent one (parent included)
	 * @param   string  $tagProperties        optional  HTML tag properties for the drop down
	 * @param   string  $permission           optional  Permission to look out for
	 * @param   string  $filterName           optional  Search for some company name (does not cache)
	 * @param   boolean $excludeParentCompany optional  When using $parentId, excludes that company
	 * @param   boolean $excludeMainCompany   optional  Exclude the companies with type "main"
	 * @param   integer $salesUserId          optional  Joomla User ID to check for sales person permissions over companies
	 * @param   integer $start                optional  Query start
	 * @param   integer $limit                optional  Query limit
	 * @param   bool    $hideDeleted          optional  Select deleted companies or not
	 *
	 * @return  mixed    List of companies in the format selected
	 */
	public static function listAvailableCompaniesbyRole(
		$roleId, $includeParents = false, $anotherUser = false, $listType = 'comma',
		$parentId = 0, $tagProperties = '', $permission = 'redshopb.company.view',
		$filterName = '', $excludeParentCompany = false, $excludeMainCompany = false,
		$salesUserId = 0, $start = 0, $limit = 0, $hideDeleted = true
	)
	{
		$funcArgs = get_defined_vars();

		// Remove some not necessary values in key
		unset($funcArgs['tagProperties']);
		static $companies = array();

		if ($listType != 'count')
		{
			$objectListArgs             = $funcArgs;
			$objectListArgs['listType'] = 'objectList';
			$key                        = serialize($objectListArgs);
		}
		else
		{
			$key = serialize($funcArgs);
		}

		if (!array_key_exists($key, $companies))
		{
			$db    = Factory::getDBO();
			$query = $db->getQuery(true)
				->from($db->qn('#__redshopb_company', 'c'));

			// Selected fields, grabbing all if it's returned as an Object List
			if ($listType == 'objectList' || $listType == 'count')
			{
				$query->select('c.*');
			}
			else
			{
				$query->select(array($db->qn('c.id'), $db->qn('c.name'), $db->qn('c.lft')));
			}

			// Global parent Id restriction (and optionally excluding the parent company)
			if ($parentId != 0)
			{
				$query->leftJoin($db->qn('#__redshopb_company', 'parent') . ' ON c.lft BETWEEN parent.lft AND parent.rgt')
					->where('parent.id = ' . (int) $parentId);

				if ($excludeParentCompany)
				{
					$query->where($db->qn('c.id') . ' != ' . (int) $parentId);
				}
			}

			if ($excludeMainCompany)
			{
				$query->where('(' . $db->qn('c.type') . ' <> ' . $db->q('main') . ')');
			}

			// Filter by name
			if ($filterName != '')
			{
				$query->where('(c.name like ' . $db->q('%' . $db->escape($filterName, true) . '%') .
					' OR c.customer_number like ' . $db->q('%' . $db->escape($filterName, true) . '%') .
					' OR c.name2 like ' . $db->q('%' . $db->escape($filterName, true) . '%') . ')'
				);
			}

			if ($hideDeleted)
			{
				$query->where($db->qn('c.deleted') . ' = 0');
			}

			// Regular queries to load a new list (non-cached)
			if (self::isSuperAdmin() && !$anotherUser)
			{
				$query
					->innerJoin($db->qn('#__assets', 'a') . ' ON ' . $db->qn('a.id') . ' = ' . $db->qn('c.asset_id'))
					->where($db->qn('c.id') . ' > 1')
					->where($db->qn('c.state') . ' = 1')
					->group($db->qn('c.id'))
					->order($db->qn('c.lft'));
			}
			else
			{
				$orderBy = 'c.lft';

				$limitedRole = RedshopbHelperRole::isRoleLimited($roleId);

				$query->innerJoin($db->qn('#__assets', 'a') . ' ON ' . $db->qn('a.id') . ' = ' . $db->qn('c.asset_id'))
					->leftJoin(
						$db->qn('#__redshopb_department', 'd') . ' ON ' .
						$db->qn('d.company_id') . ' = ' . $db->qn('c.id') . ' AND ' .
						$db->qn('d.level') . ' = 1 AND ' .
						($hideDeleted ? $db->qn('d.deleted') . ' = 0 AND ' : '') .
						$db->qn('d.state') . ' = 1'
					)
					->where($db->qn('c.id') . ' > 1')
					->where($db->qn('c.state') . ' = 1')
					->group($db->qn('c.id'));

				if ($limitedRole)
				{
					// If role is limited (sales persons) checks only the specifically granted companies
					$query
						->innerJoin($db->qn('#__redshopb_acl_rule', 'r') . ' ON ' . $db->qn('a.id') . ' = ' . $db->qn('r.joomla_asset_id'))
						->innerJoin($db->qn('#__redshopb_acl_access', 'ac') . ' ON ' . $db->qn('ac.id') . ' = ' . $db->qn('r.access_id'))
						->where($db->qn('r.role_id') . ' = ' . (int) $roleId)
						->where($db->qn('ac.name') . ' = ' . $db->quote($permission))
						->where($db->qn('r.granted') . ' = 1');
				}
				else
				{
					// If role is not limited (regular) checks all the root company tree for inherited access
					$query2 = $db->getQuery(true);
					$query3 = $db->getQuery(true);

					// Subquery for max-level rule applied
					$query3->select(Array('max(' . $db->qn('ap2.level') . ')'))
						->from($db->qn('#__assets', 'ap2'))
						->innerJoin($db->qn('#__redshopb_acl_rule', 'r2') . ' ON ' . $db->qn('ap2.id') . ' = ' . $db->qn('r2.joomla_asset_id'))
						->where($db->qn('ap2.level') . ' <= ' . $db->qn('a.level'))
						->where($db->qn('ap2.lft') . ' <= ' . $db->qn('a.lft'))
						->where($db->qn('ap2.rgt') . ' >= ' . $db->qn('a.rgt'))
						->where($db->qn('r2.role_id') . ' = ' . $db->qn('r.role_id'))
						->where($db->qn('r2.access_id') . ' = ' . $db->qn('r.access_id'));

					// Subquery for checking wether the last rule grants access
					$query2->select(Array('1'))
						->from($db->qn('#__assets', 'ap'))
						->innerJoin($db->qn('#__redshopb_acl_rule', 'r') . ' ON ' . $db->qn('ap.id') . ' = ' . $db->qn('r.joomla_asset_id'))
						->innerJoin($db->qn('#__redshopb_acl_access', 'ac') . ' ON ' . $db->qn('ac.id') . ' = ' . $db->qn('r.access_id'))
						->where($db->qn('ap.level') . ' <= ' . $db->qn('a.level'))
						->where($db->qn('ap.lft') . ' <= ' . $db->qn('a.lft'))
						->where($db->qn('ap.rgt') . ' >= ' . $db->qn('a.rgt'))
						->where($db->qn('r.role_id') . ' = ' . (int) $roleId)
						->where($db->qn('ac.name') . ' = ' . $db->quote($permission))
						->where($db->qn('r.granted') . ' = 1')
						->where($db->qn('ap.level') . ' = (' . $query3->__toString() . ')');

					$query->where('EXISTS (' . $query2 . ')');
				}

				if ($includeParents)
				{
					$query2 = clone $query;

					$query3 = $db->getQuery(true);

					if ($listType == 'objectList' || $listType == 'count')
					{
						$query3->select('cp.*');
					}
					else
					{
						$query3->select(array($db->qn('cp.id'), $db->qn('cp.name'), $db->qn('cp.lft')));
					}

					$query3->from($db->qn('#__redshopb_company', 'c'))
						->join('inner', $db->qn('#__redshopb_role', 'ro') . ' ON ' . $db->qn('ro.company_id') . ' = ' . $db->qn('c.id'))
						->join(
							'inner',
							$db->qn('#__redshopb_company', 'cp') .
							' ON ' . $db->qn('cp.level') . ' < ' . $db->qn('c.level') .
							' AND ' . $db->qn('cp.lft') . ' < ' . $db->qn('c.lft') .
							' AND ' . $db->qn('cp.rgt') . ' > ' . $db->qn('c.rgt') .
							($hideDeleted ? ' AND ' . $db->qn('cp.deleted') . ' = 0' : '')
						)
						->where($db->qn('ro.id') . ' = ' . (int) $roleId)
						->where($db->qn('cp.id') . ' > 1')
						->where($db->qn('cp.state') . ' = 1')
						->where($db->qn('cp.level') . ' > 0');

					if ($hideDeleted)
					{
						$query->where($db->qn('c.deleted') . ' = 0');
					}

					// Filter by name
					if ($filterName != '')
					{
						$query3->where('(c.name like ' . $db->q('%' . $db->escape($filterName, true) . '%') .
							' OR c.customer_number like ' . $db->q('%' . $db->escape($filterName, true) . '%') . ')'
						);
					}

					$query->clear();

					if ($listType == 'objectList' || $listType == 'count')
					{
						$query->select('*');
					}
					else
					{
						$query->select(array($db->qn('id'), $db->qn('name'), $db->qn('lft')));
					}

					$query->from('(' . $query2 . ' UNION ' . $query3 . ') AS companies');
					$orderBy = 'companies.lft';
				}

				if ($salesUserId)
				{
					$query2        = clone $query;
					$mainCompanyId = RedshopbApp::getMainCompany()->get('id');

					$query3 = $db->getQuery(true);

					if ($listType == 'objectList' || $listType == 'count')
					{
						$query3->select('csp.*');
					}
					else
					{
						$query3->select(array($db->qn('csp.id'), $db->qn('csp.name'), $db->qn('csp.lft')));
					}

					$query3->from($db->qn('#__redshopb_company', 'csp'))
						->join(
							'inner',
							$db->qn('#__redshopb_company_sales_person_xref', 'cspx') . ' ON ' .
							$db->qn('cspx.company_id') . ' = ' .
							$db->qn('csp.id')
						)
						->join('inner', $db->qn('#__redshopb_user', 'ru') . ' ON ' . $db->qn('ru.id') . ' = ' . $db->qn('cspx.user_id'))
						->where($db->qn('ru.joomla_user_id') . ' = ' . $salesUserId);

					if ($hideDeleted)
					{
						$query->where($db->qn('csp.deleted') . ' = 0');
					}

					// Shop view fix for "Open" company
					if ($parentId != $mainCompanyId && $parentId != 0)
					{
						$query3->where($db->qn('cspx.company_id') . ' = ' . $parentId);
					}

					// Filter by name
					if ($filterName != '')
					{
						$query3->where('(csp.name like ' . $db->q('%' . $db->escape($filterName, true) . '%') .
							' OR csp.customer_number like ' . $db->q('%' . $db->escape($filterName, true) . '%') . ')'
						);
					}

					$query->clear();

					if ($listType == 'objectList' || $listType == 'count')
					{
						$query->select('*');
					}
					else
					{
						$query->select(array($db->qn('id'), $db->qn('name'), $db->qn('lft')));
					}

					$query->from('(' . $query2 . ' UNION ' . $query3 . ') AS companies_sp');
					$orderBy = 'companies_sp.lft';
				}

				$query->order($db->qn($orderBy));
			}

			if ($listType == 'count')
			{
				$countQuery = $db->getQuery(true);
				$countQuery->select('COUNT(*)')
					->from('(' . $query . ') AS ' . $db->qn('count'));
				$companies[$key] = (int) $db->setQuery($countQuery, 0, 1)
					->loadResult();
			}
			else
			{
				$companies[$key] = $db->setQuery($query, (int) $start, (int) $limit)
					->loadObjectList();
			}
		}

		switch ($listType)
		{
			case 'count':
			case 'objectList':
				$result = $companies[$key];
				break;

			case 'comma':
			default:
				$result = self::processOutputList($companies[$key], $listType, 'companies', $tagProperties);

				if (!$result && $listType == 'comma')
				{
					$result = 0;
				}

				break;
		}

		return $result;
	}

	/**
	 * List the available departments for a user view
	 *
	 * @param   integer $userId        The user ID trying to list departments
	 * @param   string  $listType      optional  'comma' (default) for comma separated IDs for dB
	 *                                           'dropdown' for Dropdown Select Box with items
	 *                                           'objectList' for an objectList direct from the DB (queries all fields)
	 * @param   integer $companyId     optional  Parent company ID to list departments from
	 * @param   boolean $anotherUser   optional  Defines if it's querying for another user and not the logged in one to avoid checking super admin
	 * @param   integer $parentId      optional  Parent department ID to list departments from
	 * @param   string  $tagProperties optional  HTML tag properties for the dropdown
	 * @param   string  $permission    optional  Permission to look out for
	 * @param   string  $filterName    optional  Search for some company name (does not cache)
	 * @param   integer $start         optional  Query start
	 * @param   integer $limit         optional  Query limit
	 * @param   bool    $hideDeleted   optional  Select deleted companies or not
	 *
	 * @return  mixed    List of departments in the format selected
	 */
	public static function listAvailableDepartments(
		$userId, $listType = 'comma', $companyId = 0, $anotherUser = false, $parentId = 0,
		$tagProperties = '', $permission = 'redshopb.department.view', $filterName = '', $start = 0, $limit = 0,
		$hideDeleted = true
	)
	{
		$funcArgs = get_defined_vars();

		// Remove some not necessary values in key
		unset($funcArgs['tagProperties']);
		static $departments = array();

		if ($listType != 'count')
		{
			$objectListArgs             = $funcArgs;
			$objectListArgs['listType'] = 'objectList';
			$key                        = serialize($objectListArgs);
		}
		else
		{
			$key = serialize($funcArgs);
		}

		if (!array_key_exists($key, $departments))
		{
			$db         = Factory::getDBO();
			$query      = $db->getQuery(true);
			$querySP    = $db->getQuery(true);
			$queryUnion = $db->getQuery(true);

			// Department table alias (it can be changed in some specific cases later)
			$departmentAlias = 'd';

			// If it's a super admin, selects every department
			if (self::isSuperAdmin() && !$anotherUser)
			{
				$query->from($db->qn('#__redshopb_department', 'd'))
					->innerJoin($db->qn('#__assets', 'a') . ' ON ' . $db->qn('a.id') . ' = ' . $db->qn('d.asset_id'))
					->where($db->qn('d.id') . ' > 1')
					->where(($hideDeleted ? $db->qn('d.deleted') . ' = 0 AND ' : '') . $db->qn('d.state') . ' = 1');
			}
			else
			{
				$roleId          = RedshopbHelperUser::getUserRoleId($userId, 'joomla');
				$limitedRole     = RedshopbHelperRole::isRoleLimited($roleId);
				$departmentLimit = self::isRuleDeparmentLimited($permission, $userId);

				if ($departmentLimit > 0)
				{
					$departmentAlias = 'cd';
				}

				$query2 = $db->getQuery(true);
				$query3 = $db->getQuery(true);

				// Subquery for max-level rule applied
				$query3->select(Array('max(' . $db->qn('ap2.level') . ')'))
					->from($db->qn('#__assets', 'ap2'))
					->innerJoin($db->qn('#__redshopb_acl_rule', 'r2') . ' ON ' . $db->qn('ap2.id') . ' = ' . $db->qn('r2.joomla_asset_id'))
					->where($db->qn('ap2.level') . ' <= ' . $db->qn('a.level'))
					->where($db->qn('ap2.lft') . ' <= ' . $db->qn('a.lft'))
					->where($db->qn('ap2.rgt') . ' >= ' . $db->qn('a.rgt'))
					->where($db->qn('r2.role_id') . ' = ' . $db->qn('r.role_id'))
					->where($db->qn('r2.access_id') . ' = ' . $db->qn('r.access_id'));

				if ($limitedRole)
				{
					$query3->innerJoin(
						$db->qn('#__redshopb_department', 'd2') . ' ON ' .
						$db->qn('ap2.id') . ' = ' . $db->qn('d2.asset_id') . ' AND ' .
						($hideDeleted ? $db->qn('d2.deleted') . ' = 0 AND ' : '') . $db->qn('d2.state') . ' = 1'
					);

					$query4 = $db->getQuery(true);
					$query5 = $db->getQuery(true);

					$query5->select(Array('1'))
						->from($db->qn('#__redshopb_company', 'c2'))
						->innerJoin($db->qn('#__assets', 'acc') . ' ON ' . $db->qn('c2.asset_id') . ' = ' . $db->qn('acc.id'))
						->where($db->qn('acc.parent_id') . ' = ' . $db->qn('ap3.id'))
						->where($db->qn('acc.level') . ' <= ' . $db->qn('a.level'))
						->where($db->qn('acc.lft') . ' <= ' . $db->qn('a.lft'))
						->where($db->qn('acc.rgt') . ' >= ' . $db->qn('a.rgt'));

					if ($hideDeleted)
					{
						$query5->where($db->qn('c2.deleted') . ' = 0');
					}

					$query4->select(Array('max(' . $db->qn('ap3.level') . ')'))
						->from($db->qn('#__assets', 'ap3'))
						->innerJoin($db->qn('#__redshopb_acl_rule', 'r3') . ' ON ' . $db->qn('ap3.id') . ' = ' . $db->qn('r3.joomla_asset_id'))
						->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON ' . $db->qn('ap3.id') . ' = ' . $db->qn('c.asset_id'))
						->where($db->qn('ap3.level') . ' <= ' . $db->qn('a.level'))
						->where($db->qn('ap3.lft') . ' <= ' . $db->qn('a.lft'))
						->where($db->qn('ap3.rgt') . ' >= ' . $db->qn('a.rgt'))
						->where($db->qn('r3.role_id') . ' = ' . $db->qn('r.role_id'))
						->where($db->qn('r3.access_id') . ' = ' . $db->qn('r.access_id'))
						->where('NOT EXISTS (' . $query5 . ')');

					if ($hideDeleted)
					{
						$query4->where($db->qn('c.deleted') . ' = 0');
					}
				}

				// Subquery for checking wether the last rule grants access
				$query2->select(Array('1'))
					->from($db->qn('#__assets', 'ap'))
					->innerJoin($db->qn('#__redshopb_acl_rule', 'r') . ' ON ' . $db->qn('ap.id') . ' = ' . $db->qn('r.joomla_asset_id'))
					->innerJoin($db->qn('#__redshopb_acl_access', 'ac') . ' ON ' . $db->qn('ac.id') . ' = ' . $db->qn('r.access_id'))
					->innerJoin($db->qn('#__redshopb_role', 'ro') . ' ON ' . $db->qn('ro.id') . ' = ' . $db->qn('r.role_id'))
					->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ' . $db->qn('ro.joomla_group_id') . ' = ' . $db->qn('ug.group_id'))
					->where($db->qn('ap.level') . ' <= ' . $db->qn('a.level'))
					->where($db->qn('ap.lft') . ' <= ' . $db->qn('a.lft'))
					->where($db->qn('ap.rgt') . ' >= ' . $db->qn('a.rgt'))
					->where($db->qn('ug.user_id') . ' = ' . (int) $userId)
					->where($db->qn('ac.name') . ' = ' . $db->quote($permission))
					->where($db->qn('r.granted') . ' = 1');

				if ($limitedRole)
				{
					$query2->where(
						'('
						. $db->qn('ap.level') . ' = (' . $query3 . ')'
						. ' OR '
						. $db->qn('ap.level') . ' = (' . $query4 . ')'
						. ')'
					);
				}
				else
				{
					$query2->where($db->qn('ap.level') . ' = (' . $query3 . ')');
				}

				$query->from($db->qn('#__redshopb_department', 'd'))
					->where(($hideDeleted ? $db->qn('d.deleted') . ' = 0 AND ' : '') . $db->qn('d.state') . ' = 1')
					->innerJoin($db->qn('#__assets', 'a') . ' ON ' . $db->qn('a.id') . ' = ' . $db->qn('d.asset_id'))
					->where('EXISTS (' . $query2 . ')')
					->where($db->qn('d.id') . ' > 1');

				// If the rule is limited by a department (HODs), queries that specific department
				if ($departmentLimit > 0)
				{
					$query->innerJoin(
						$db->qn('#__assets', 'ca') . ' ON (' .
						$db->qn('ca.lft') . ' > ' . $db->qn('a.lft') . ' AND ' .
						$db->qn('ca.rgt') . ' < ' . $db->qn('a.rgt') . ') OR ' .
						$db->qn('ca.id') . ' = ' . $db->qn('a.id')
					)
						->innerJoin(
							$db->qn('#__redshopb_department', 'cd') . ' ON ' .
							$db->qn('ca.id') . ' = ' .
							$db->qn('cd.asset_id') . ($hideDeleted ? ' AND ' .
								$db->qn('cd.deleted') . ' = 0' : '') . ' AND ' .
							$db->qn('cd.state') . ' = 1'
						)
						->where($db->qn('d.id') . ' = ' . (int) $departmentLimit);
				}
			}

			// Selected fields according to selected alias (default 'd.') and listType
			if ($listType == 'objectList' || $listType == 'count')
			{
				$query->select($departmentAlias . '.*');
				$querySP->select('dsp.*');
				$queryUnion->select('departments.*');
			}
			else
			{
				$query->select(
					array(
						$db->qn($departmentAlias . '.id'),
						$db->qn($departmentAlias . '.name'),
						$db->qn($departmentAlias . '.lft'),
						$db->qn($departmentAlias . '.company_id'),
						$db->qn($departmentAlias . '.level'),
						$db->qn($departmentAlias . '.parent_id')
					)
				);

				$querySP->select(
					array(
						$db->qn('dsp.id'),
						$db->qn('dsp.name'),
						$db->qn('dsp.lft'),
						$db->qn('dsp.company_id'),
						$db->qn('dsp.level'),
						$db->qn('dsp.parent_id')
					)
				);

				$queryUnion->select(array($db->qn('departments.id'), $db->qn('departments.name')));
			}

			// Selected company Id restriction
			if ($companyId > 0)
			{
				$queryUnion->where('departments.company_id = ' . (int) $companyId);

				if ($parentId == 0)
				{
					$queryUnion->where('departments.level = 1');
				}
			}

			// Selected parent Id restriction
			if ($parentId > 0)
			{
				$queryUnion->where('departments.parent_id = ' . (int) $parentId);
			}

			// Filter by name
			if ($filterName != '')
			{
				$queryUnion->where('departments.name like ' . $db->q('%' . $db->escape($filterName, true) . '%'));
			}

			$queryUnion->order('departments.lft');

			// Sales persons query
			$querySP->from($db->qn('#__redshopb_department', 'dsp'))
				->where(($hideDeleted ? $db->qn('dsp.deleted') . ' = 0 AND ' : '') . $db->qn('dsp.state') . ' = 1')
				->join('inner', $db->qn('#__redshopb_company', 'csp') . ' ON ' . $db->qn('csp.id') . ' = ' . $db->qn('dsp.company_id'))
				->join(
					'inner',
					$db->qn('#__redshopb_company_sales_person_xref', 'cspx') . ' ON ' .
					$db->qn('cspx.company_id') . ' = ' .
					$db->qn('csp.id')
				)
				->join('inner', $db->qn('#__redshopb_user', 'ru') . ' ON ' . $db->qn('ru.id') . ' = ' . $db->qn('cspx.user_id'))
				->where($db->qn('ru.joomla_user_id') . ' = ' . $userId);

			$queryUnion->from('(' . $query . ' UNION ' . $querySP . ') AS departments');

			if ($listType == 'count')
			{
				$countQuery = $db->getQuery(true);
				$countQuery->select('COUNT(*)')
					->from('(' . $queryUnion . ') AS ' . $db->qn('count'));
				$departments[$key] = (int) $db->setQuery($countQuery, 0, 1)
					->loadResult();
			}
			else
			{
				$departments[$key] = $db->setQuery($queryUnion, (int) $start, (int) $limit)
					->loadObjectList();
			}
		}

		switch ($listType)
		{
			case 'objectList':
			case 'count':
				$result = $departments[$key];
				break;
			case 'comma':
			default:
				$result = self::processOutputList($departments[$key], $listType, 'departments', $tagProperties);

				if (!$result && $listType == 'comma')
				{
					$result = 0;
				}

				break;
		}

		return $result;
	}

	/**
	 * List the available employees for a user view
	 *
	 * @param   integer $companyId                  optional   Parent company ID to list employees from
	 * @param   integer $departmentId               optional   Department ID to list employees from
	 * @param   string  $listType                   optional   'dropdown' for Dropdown Select Box with items,
	 *                                              'comma' for comma separated IDs for dB,
	 *                                              'objectList' for an objectList direct from the DB
	 * @param   string  $tagProperties              optional   HTML tag properties for the dropdown
	 * @param   string  $filterName                 optional   Search for some company name (does not cache)
	 * @param   int     $start                      optional   Query start
	 * @param   int     $limit                      optional   Query limit
	 * @param   string  $permission                 optional   Permission to check on companies and departments
	 *
	 * @return  mixed    List of employees in the format selected
	 */
	public static function listAvailableEmployees($companyId = 0, $departmentId = 0, $listType = 'dropdown',
		$tagProperties = '', $filterName = '', $start = 0, $limit = 0, $permission = 'redshopb.order.impersonate'
	)
	{
		$funcArgs = get_defined_vars();

		// Remove some not necessary values in key
		unset($funcArgs['tagProperties']);
		static $users = array();

		if ($listType != 'count')
		{
			$objectListArgs             = $funcArgs;
			$objectListArgs['listType'] = 'objectList';
			$key                        = serialize($objectListArgs);
		}
		else
		{
			$key = serialize($funcArgs);
		}

		if (!array_key_exists($key, $users))
		{
			if (self::getPermissionInto('impersonate', 'order'))
			{
				$user = Factory::getUser();
				$db   = Factory::getDBO();

				$query = $db->getQuery(true);
				$query->select(
					array(
						'ru.*',
						$db->qn('ju.name', 'name'),
						$db->qn('c.name', 'company'),
						$db->qn('d.name', 'department'),
						$db->qn('rt.type'),
						$db->qn('rt.name', 'role'),
						$db->qn('umc.company_id', 'company_id')
					)
				)
					->from($db->qn('#__redshopb_user', 'ru'))
					->leftJoin(
						$db->qn('#__redshopb_user_multi_company', 'umc') . ' ON umc.user_id = ru.id '
					)
					->innerJoin($db->qn('#__users', 'ju') . ' ON ' . $db->qn('ru.joomla_user_id') . ' = ' . $db->qn('ju.id'))
					->innerJoin($db->qn('#__redshopb_company', 'c') . ' ON c.id = umc.company_id AND ' . $db->qn('c.deleted') . ' = 0')
					->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ru.joomla_user_id = ug.user_id')
					->innerJoin($db->qn('#__redshopb_role', 'r') . ' ON r.joomla_group_id = ug.group_id')
					->innerJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON r.role_type_id = rt.id')
					->leftJoin(
						$db->qn('#__redshopb_department', 'd') . ' ON d.id = ru.department_id AND ' .
						$db->qn('d.deleted') . ' = 0 AND ' .
						$db->qn('d.state') . ' = 1'
					);

				if ($companyId)
				{
					$query->where($db->qn('umc.company_id') . ' = ' . (int) $companyId);
				}

				if ($departmentId)
				{
					$query->where($db->qn('ru.department_id') . ' = ' . (int) $departmentId);
				}

				// Filter by name
				if ($filterName != '')
				{
					$query->where(
						'((' . $db->qn('ju.name') . ' LIKE ' . $db->q('%' . $db->escape($filterName, true) . '%') . ')' .
						' OR (' . $db->qn('ru.employee_number') . ' LIKE ' . $db->q('%' . $db->escape($filterName, true) . '%') . '))'
					);
				}

				if (!self::isSuperAdmin())
				{
					$userCompanies   = self::listAvailableCompaniesByPermission($user->id, $permission);
					$userDepartments = self::listAvailableDepartmentsByPermission($user->id, $permission);

					$query->where(
						'(' . $db->qn('umc.company_id') . ' IN (' . $userCompanies . ')' .
						' OR ' . $db->qn('ru.department_id') . ' IN (' . $userDepartments . '))'
					);

					if (!$departmentId && $filterName == '' && !count($userDepartments))
					{
						$query->where($db->qn('ru.department_id') . ' IS NULL');
					}
				}

				$query->group($db->qn('ru.id'));

				if ($listType == 'count')
				{
					$countQuery = $db->getQuery(true);
					$countQuery->select('COUNT(*)')
						->from('(' . $query . ') AS ' . $db->qn('count'));
					$users[$key] = (int) $db->setQuery($countQuery, 0, 1)
						->loadResult();
				}
				else
				{
					$users[$key] = $db->setQuery($query, (int) $start, (int) $limit)
						->loadObjectList();
				}
			}
			else
			{
				$users[$key] = null;
			}
		}

		switch ($listType)
		{
			case 'objectList':
			case 'count':
				$result = $users[$key];
				break;
			case 'comma':
			default:
				$result = self::processOutputList($users[$key], $listType, 'employees', $tagProperties);

				if (!$result && $listType == 'comma')
				{
					$result = 0;
				}

				break;
		}

		return $result;
	}

	/**
	 * List the available addresses for a address view
	 *
	 * @param   integer $userId                     The user ID trying to list addresses
	 * @param   string  $listType                   optional   'comma' (default) for comma separated IDs for dB
	 *                                              'dropdown' for Drop down Select Box with items
	 *                                              'objectList' for an objectList direct from the DB (queries all fields)
	 * @param   string  $tagProperties              optional   HTML tag properties for the drop down
	 * @param   string  $permission                 optional   Permission to look out for
	 * @param   string  $filterName                 optional   Search for some address name (does not cache)
	 *
	 * @return  mixed    List of addresses in the format selected
	 */
	public static function listAvailableAddresses(
		$userId, $listType = 'comma',
		$tagProperties = '', $permission = 'redshopb.address.view',
		$filterName = ''
	)
	{
		$funcArgs = get_defined_vars();

		// Remove some not necessary values in key
		unset(
			$funcArgs['tagProperties'],
			$funcArgs['permission']
		);
		static $addresses           = array();
		$objectListArgs             = $funcArgs;
		$objectListArgs['listType'] = 'objectList';
		$key                        = serialize($objectListArgs);

		if (!array_key_exists($key, $addresses))
		{
			$db    = Factory::getDBO();
			$query = $db->getQuery(true)
				->select('a.*');

			// Filter by name
			if ($filterName != '')
			{
				$query->where('a.address like ' . $db->q('%' . $db->escape($filterName, true) . '%'));
			}

			$query->order('a.country_id, a.city, a.address');

			// Regular queries to load a new list (non-cached)
			if (self::isSuperAdmin())
			{
				$query->from($db->qn('#__redshopb_address', 'a'))
					->where($db->qn('a.id') . ' > 1')
					->group('a.id');
			}
			else
			{
				$availableCompanies   = self::listAvailableCompanies($userId);
				$availableDepartments = self::listAvailableDepartments($userId);
				$companyId            = RedshopbHelperUser::getUserCompanyId($userId);
				$departmentId         = RedshopbHelperUser::getUserDepartmentId($userId);
				$availableEmployees   = self::listAvailableEmployees($companyId, 0, 'comma');

				$query->from($db->qn('#__redshopb_address', 'a'))
					->where($db->qn('a.id') . ' > 1')
					->group('a.id');

				$whereFilter = array();

				if (!empty($availableCompanies))
				{
					$availableCompanies .= ',' . $companyId;
				}
				else
				{
					$availableCompanies = $companyId;
				}

				if (!empty($availableDepartments))
				{
					$availableDepartments .= ',' . $departmentId;
				}
				else
				{
					$availableDepartments = $departmentId;
				}

				if (!empty($availableEmployees))
				{
					$availableEmployees .= ',' . $userId;
				}
				else
				{
					$availableEmployees = $userId;
				}

				$whereFilter[] = '(a.customer_type = ' . $db->q('company') . ' AND a.customer_id IN (' . $availableCompanies . '))';

				$whereFilter[] = '(a.customer_type = ' . $db->q('department') . ' AND a.customer_id IN (' . $availableDepartments . '))';

				$whereFilter[] = '(a.customer_type = ' . $db->q('employee') . ' AND a.customer_id IN (' . $availableEmployees . '))';

				$whereFilter = implode(' OR ', $whereFilter);
				$query->where('(' . $whereFilter . ')');
			}

			$addresses[$key] = $db->setQuery($query)
				->loadObjectList();
		}

		switch ($listType)
		{
			case 'objectList':
				$result = $addresses[$key];
				break;
			case 'comma':
			default:
				$result = self::processOutputList($addresses[$key], $listType, 'addresses', $tagProperties);

				if (!$result && $listType == 'comma')
				{
					$result = 0;
				}

				break;
		}

		return $result;
	}

	/**
	 * List the available companies for a user view, just changing permission and list type, without the other fields.
	 * Just a shortcut for listAvailableCompanies.
	 *
	 * @param   integer $userId                  The user ID trying to list companies
	 * @param   string  $permission              Permission to look out for
	 * @param   string  $listType                optional   'comma' (default) for comma separated IDs for dB
	 *                                           'dropdown' for Drop down Select Box with items
	 *                                           'objectList' for an objectList direct from the DB (queries all fields)
	 * @param   bool    $hideDeleted             optional  Select deleted companies or not
	 *
	 * @return  mixed    List of companies in the format selected
	 */
	public static function listAvailableCompaniesByPermission($userId, $permission, $listType = 'comma', $hideDeleted = true)
	{
		return self::listAvailableCompanies(
			$userId, $listType, 0, '', $permission, '', false, false, false, false, 0, 0, $hideDeleted
		);
	}

	/**
	 * List the available departments for a user view, just changing permission and list type, without the other fields.
	 * Just a shortcut for listAvailableDepartments.
	 *
	 * @param   integer $userId                  The user ID trying to list departments
	 * @param   string  $permission              Permission to look out for
	 * @param   string  $listType                optional   'comma' (default) for comma separated IDs for dB
	 *                                           'dropdown' for Drop down Select Box with items
	 *                                           'objectList' for an objectList direct from the DB (queries all fields)
	 * @param   bool    $hideDeleted             optional  Select deleted companies or not
	 *
	 * @return  mixed    List of companies in the format selected
	 */
	public static function listAvailableDepartmentsByPermission($userId, $permission, $listType = 'comma', $hideDeleted = true)
	{
		return self::listAvailableDepartments(
			$userId, $listType, 0, false, 0, '', $permission, '', 0, 0, $hideDeleted
		);
	}

	/**
	 * List the available companies for a user view, and also its parents
	 *
	 * @param   integer $userId      The user ID trying to list companies
	 * @param   boolean $anotherUser optional   Defines if it's querying for another user and not the logged in one to avoid checking super admin
	 * @param   string  $listType    optional   'comma' (default) for comma separated IDs for dB
	 *                                          'dropdown' for Drop down Select Box with items
	 *                                          'objectList' for an objectList direct from the DB (queries all fields)
	 *
	 * @return  mixed    List of companies in the format selected
	 */
	public static function listAvailableCompaniesAndParents($userId, $anotherUser = false, $listType = 'comma')
	{
		return self::listAvailableCompanies($userId, $listType, 0, '', 'redshopb.company.view', '', true, $anotherUser);
	}

	/**
	 * Lists only the child companies of a selected company, using an specific user id ACL
	 *
	 * @param   integer $userId                  The user ID trying to list companies
	 * @param   integer $companyId               The company ID trying to list children from
	 * @param   string  $permission              optional   Permission to look out for
	 * @param   string  $listType                optional   'comma' (default) for comma separated IDs for dB
	 *                                           'dropdown' for Drop down Select Box with items
	 *                                           'objectList' for an objectList direct from the DB (queries all fields)
	 *
	 * @return  mixed    List of companies in the format selected
	 */
	public static function listAvailableChildCompanies($userId, $companyId, $permission = 'redshopb.company.view', $listType = 'comma')
	{
		return self::listAvailableCompanies($userId, $listType, $companyId, '', $permission, '', false, false, true);
	}

	/**
	 * List available categories for shop display.
	 *
	 * @param   integer  $userId           The user ID trying to list categories.
	 * @param   mixed    $parent           optional   Category parent id.
	 * @param   integer  $levels           optional   Levels below the parent that must be checked
	 * @param   integer  $companyId        optional   Company Id to limit categories for
	 * @param   mixed    $collections      optional   Check that the categories or their parents have products in certain collections
	 * @param   string   $listType         optional   'comma' (default) for comma separated IDs for dB
	 *                                              'dropdown' for Drop down Select Box with items
	 *                                              'objectList' for an objectList direct from the DB (queries all fields)
	 *                                              'count' get count categories
	 * @param   string   $tagProperties    optional   HTML tag properties for the drop down
	 * @param   string   $permission       optional   Permission to look out for.
	 * @param   int      $start            optional  Query start
	 * @param   int      $limit            optional  Query limit
	 * @param   boolean  $forceImage       optional  Force image for category
	 * @param   boolean  $noEmpty          optional  True for just show category has product (child-categories indeed). False for show all.
	 * @param   string   $order            optional  Options for order result. ("random" for special randomly order)
	 * @param   array    $levelsImages     optional  Which categories levels to forc images for
	 * @param   boolean  $showHidden       optional  Show hidden categories
	 * @param   boolean  $requireTopLevel  optional  Check if only top level is required.
	 * @param   string   $layout           optional  Check if it's category og categories
	 *
	 * @return  mixed    List of categories in the format selected.
	 */
	public static function listAvailableCategories(
		$userId, $parent = false, $levels = 1, $companyId = 0, $collections = false, $listType = 'comma',
		$tagProperties = '', $permission = 'redshopb.category.view', $start = 0, $limit = 0, $forceImage = false,
		$noEmpty = false, $order = 'c.id', $levelsImages = array(), $showHidden = true, $requireTopLevel = false, $layout = null
	)
	{

		if (empty($layout))
		{
			$layout = Factory::getApplication()->input->get('layout');
		}

		$funcArgs = get_defined_vars();

		if (empty($collections))
		{
			$collections = RedshopbHelperCollection::getCustomerCollectionsForShop();
		}

		// Remove some not necessary values in key
		unset(
			$funcArgs['forceImage'],
			$funcArgs['tagProperties'],
			$funcArgs['permission'],
			$funcArgs['companyId']
		);
		static $categories = array();
		$db                = Factory::getDBO();

		if ($listType != 'count')
		{
			$objectListArgs             = $funcArgs;
			$objectListArgs['listType'] = 'objectList';
			$key                        = serialize($objectListArgs);
		}
		else
		{
			$key = serialize($funcArgs);
		}

		if (!array_key_exists($key, $categories))
		{
			$query = $db->getQuery(true);

			// Selected fields, grabbing all if it's returned as an Object List
			if ($listType == 'objectList')
			{
				if ($collections !== false && !empty($collections))
				{
					$query->select(
						array(
							'c.*',
							'wpx.collection_id AS collectionId'
						)
					);
				}
				else
				{
					$query->select('c.*');
				}
			}
			else
			{
				$query->select(array($db->qn('c.id'), $db->qn('c.name')));
			}

			if ($noEmpty)
			{
				$countProducts = $db->getQuery(true)
					->select('product.id')
					->from($db->qn('#__redshopb_product', 'product') . ' FORCE INDEX(PRIMARY)')
					->innerJoin(
						$db->qn('#__redshopb_product_category_xref', 'ref') . ' FORCE INDEX(#__rs_prod_cat_fk2) ON ' .
						$db->qn('product.id') . ' = ' .
						$db->qn('ref.product_id')
					)
					->where($db->qn('product.state') . ' = 1')
					->where($db->qn('product.discontinued') . ' = 0')
					->where($db->qn('product.service') . ' = 0')
					->where($db->qn('prodcat.id') . ' = ' . $db->qn('ref.category_id'));

				$countQuery = $db->getQuery(true)
					->select('prodcat.id, prodcat.lft')
					->from($db->qn('#__redshopb_category', 'prodcat') . ' FORCE INDEX(PRIMARY)')
					->select('(' . $countProducts . ' LIMIT 0, 1) AS product_id')
					->group('prodcat.id');

				$query->leftJoin('(' . $countQuery . ') AS pr ON pr.lft BETWEEN c.lft AND c.rgt')
					->where('pr.product_id IS NOT NULL');
			}

			$query->from($db->qn('#__redshopb_category', 'c') . ' FORCE INDEX(idx_state)')
				->where('c.state = 1')
				->group($db->qn('c.id'))
				// Remove categories with unpublish parents
				->leftJoin(
					$db->qn('#__redshopb_category', 'parent') .
					' FORCE INDEX(idx_state) ON c.lft BETWEEN parent.lft AND parent.rgt AND parent.state = 0'
				)
				->where('parent.id IS NULL');

			// Custom made only to retrieve parent categories for main category page.
			if ($requireTopLevel)
			{
				$query->where("c.level = 1");
			}

			if ($showHidden === false)
			{
				$query->where('c.hide = 0');
			}

			// Company limitations considering the logged in user
			$companies = array();

			if (self::isSuperAdmin() || self::getPermissionInto('impersonate', 'order'))
			{
				$customerId        = Factory::getApplication()->getUserState('shop.customer_id', '0');
				$customerType      = Factory::getApplication()->getUserState('shop.customer_type', 'employee');
				$isFromMainCompany = RedshopbHelperUser::isFromMainCompany($customerId, $customerType);

				if (!$isFromMainCompany)
				{
					$impersonateCompany = RedshopbHelperCompany::getCompanyByCustomer($customerId, $customerType);

					if ($impersonateCompany)
					{
						$companies = RedshopbEntityCompany::getInstance($impersonateCompany->id)
							->getTree(false, false);
					}
				}
			}
			else
			{
				$isFromMainCompany = RedshopbHelperUser::isFromMainCompany();

				if (!$isFromMainCompany && !RedshopbHelperCommon::getUser()->b2cMode)
				{
					$companies = self::listAvailableCompaniesAndParents($userId);
					$companies = explode(',', $companies);

					if (!empty($companies))
					{
						// Exclude current company of user
						$userCompany = RedshopbHelperUser::getUserCompany();

						if ($userCompany && in_array($userCompany->id, $companies))
						{
							unset($companies[array_search($userCompany->id, $companies)]);
						}
					}
				}
			}

			// Avoid any categories for main company users
			if ($isFromMainCompany)
			{
				$query->where('1 = 0');
			}
			else
			{
				if (empty($companies))
				{
					if (!RedshopbHelperCommon::getUser($userId)->b2cMode)
					{
						$query->where($db->qn('c.company_id') . ' IS NULL');
					}
				}
				else
				{
					$query->where(
						'(' . $db->qn('c.company_id') . ' IN (' . implode(',', $companies) . ') OR ' . $db->qn('c.company_id') . ' IS NULL)'
					);
				}
			}

			if ($parent !== false)
			{
				$query->innerJoin(
					$db->qn('#__redshopb_category', 'cp') . ' FORCE INDEX(PRIMARY) ON c.lft BETWEEN cp.lft AND cp.rgt'
					. ' AND ' . $db->qn('c.level') . ' <= ' . $db->qn('cp.level') . ' + ' . (int) $levels
				);
				$query->where($db->qn('cp.id') . ' = ' . (int) $parent)
					->where('c.id != ' . (int) $parent);
			}

			if ($collections !== false && !empty($collections))
			{
				$collections = Joomla\Utilities\ArrayHelper::toInteger($collections);
				$query->innerJoin(
					$db->qn('#__redshopb_category', 'cc') . ' ON ' .
					$db->qn('c.lft') . ' <= ' . $db->qn('cc.lft') . ' AND ' .
					$db->qn('c.rgt') . ' >= ' . $db->qn('cc.rgt') . ' AND ' .
					$db->qn('c.level') . ' <= ' . $db->qn('cc.level')
				)
					->innerJoin(
						$db->qn('#__redshopb_product_category_xref', 'pcx') . ' ON ' .
						$db->qn('pcx.category_id') . ' = ' . $db->qn('cc.id')
					)
					->innerJoin(
						$db->qn('#__redshopb_collection_product_xref', 'wpx') . ' ON ' .
						$db->qn('wpx.product_id') . ' = ' . $db->qn('pcx.product_id')
					)
					->where($db->qn('wpx.collection_id') . ' IN (' . implode(',', $collections) . ')');
			}

			if ($listType == 'count')
			{
				$query->clear('select')
					->clear('group')
					->select('COUNT(DISTINCT(c.id))');

				$categories[$key] = (int) $db->setQuery($query, 0, 1)
					->loadResult();
			}
			else
			{
				if ($order == 'random')
				{
					$query->order('RAND()');
				}
				else
				{
					$query->order($db->qn($order));
				}

				$categories[$key] = $db->setQuery($query, (int) $start, (int) $limit)
					->loadObjectList();
			}
		}

		switch ($listType)
		{
			case 'objectList':
				if ($forceImage !== false && !empty($categories[$key]))
				{
					$keyCategories = array();

					foreach ($categories[$key] as $category)
					{
						$keyCategories[] = $category->id;
					}

					$cache = Cache::getInstance('output', array('defaultgroup' => 'com_redshopb_image_categories'));
					$cache->setCaching(true);
					$cache->setLifeTime((int) Factory::getConfig()->get('cachetime', 86400));
					$output    = (array) $cache->get('relations');
					$needStore = false;

					foreach ($output as $outKey => $value)
					{
						$values = explode('|', $value);

						if (count($values) < 2
							|| !JFile::exists(
								JPATH_ROOT . '/' . RedshopbHelperThumbnail::getFullImagePath(
									$values[1], $values[0], isset($values[2]) ? $values[2] : ''
								)
							)
						)
						{
							unset($output[$outKey]);
							$needStore = true;
						}
					}

					$cacheIds = array_keys($output);
					$diff     = array_diff($keyCategories, $cacheIds);

					if (count($diff) > 0)
					{
						$subQuery2 = $db->getQuery(true)
							->select('CONCAT_WS(' . $db->q('|') . ', pim.name, pim.remote_path) as name')
							->from($db->qn('#__redshopb_media', 'pim'))
							->innerJoin(
								$db->qn('#__redshopb_product_category_xref', 'ref') . ' ON ' .
								$db->qn('pim.product_id') . ' = ' .
								$db->qn('ref.product_id')
							)
							->where('ref.category_id = pimc.id')
							->where('pim.state = 1')
							->where('pim.name != ' . $db->q(''));

						$productImage = $db->getQuery(true)
							->select('pimc.id as category_id, (' . $subQuery2 . ' LIMIT 0, 1) AS name')
							->from($db->qn('#__redshopb_category', 'pimc'));

						$subQuery = $db->getQuery(true)
							->select(
								'CASE WHEN LENGTH(node.image) > 0 THEN CONCAT_WS('
								. $db->q('|') . ', ' . $db->q('categories') . ',node.image) '
								. ' WHEN LENGTH(pi.name) > 0 THEN CONCAT_WS('
								. $db->q('|') . ', ' . $db->q('products') . ',pi.name)'
								. ' ELSE NULL'
								. ' END AS pimage'
							)
							->select('parent.id')
							->from($db->qn('#__redshopb_category', 'parent'))
							->leftJoin($db->qn('#__redshopb_category', 'node') . ' ON node.lft BETWEEN parent.lft AND parent.rgt')
							->leftJoin('(' . $productImage . ') AS pi ON pi.category_id = node.id')
							->where('(node.image != ' . $db->q('') . ' OR pi.name != ' . $db->q('') . ')')
							->where('node.state = 1')
							->where('parent.image = ' . $db->q(''))
							->where('parent.id IN (' . implode(',', $diff) . ')')
							->group('parent.id')
							->order('node.lft');

						if (!empty($levelsImages))
						{
							$levelsImages = ArrayHelper::toInteger($levelsImages);
							$subQuery->where('parent.level IN (' . implode(',', $levelsImages) . ')');
						}

						$results = $db->setQuery($subQuery)
							->loadObjectList('id');
					}

					foreach ($categories[$key] as $category)
					{
						if (isset($results[$category->id]) && $results[$category->id]->pimage)
						{
							$values = explode('|', $results[$category->id]->pimage);

							if (count($values) >= 2
								&& JFile::exists(
									JPATH_ROOT . '/' . RedshopbHelperThumbnail::getFullImagePath(
										$values[1], $values[0], isset($values[2]) ? $values[2] : ''
									)
								)
							)
							{
								$output[$category->id] = $results[$category->id]->pimage;
								$category->pimage      = $results[$category->id]->pimage;
								$needStore             = true;
							}
							else
							{
								$category->pimage = '';
							}
						}
						elseif (isset($output[$category->id]))
						{
							$category->pimage = $output[$category->id];
						}
						else
						{
							$category->pimage = '';
						}
					}

					if ($needStore)
					{
						$cache->store($output, 'relations');
					}
				}

				$result = $categories[$key];
				break;

			case 'count':
				$result = $categories[$key];
				break;

			case 'comma':
			default:

				$result = self::processOutputList($categories[$key], $listType, 'categories', $tagProperties);

				if (!$result && $listType == 'comma')
				{
					$result = 0;
				}

				break;
		}

		return $result;
	}

	/**
	 * Seeks whether a user has a permission restricted for its department only or not (for HODs)
	 *
	 * @param   string  $permission Permission to seek out in the ACL schema
	 * @param   integer $userId     User Id
	 *
	 * @return  integer  0 if not, department Id if limited, -1 if user not valid (super admin)
	 */
	protected static function isRuleDeparmentLimited($permission, $userId)
	{
		$db     = Factory::getDBO();
		$query  = $db->getQuery(true);
		$query2 = $db->getQuery(true);

		// Subquery to determine the maximum level for the rule searched
		$query2->select(Array('max(' . $db->qn('ap2.level') . ')'))
			->from($db->qn('#__assets', 'ap2'))
			->innerJoin($db->qn('#__redshopb_acl_rule', 'r2') . ' ON ' . $db->qn('ap2.id') . ' = ' . $db->qn('r2.joomla_asset_id'))
			->where($db->qn('ap2.level') . ' <= ' . $db->qn('a.level'))
			->where($db->qn('ap2.lft') . ' <= ' . $db->qn('a.lft'))
			->where($db->qn('ap2.rgt') . ' >= ' . $db->qn('a.rgt'))
			->where($db->qn('r2.role_id') . ' = ' . $db->qn('r.role_id'))
			->where($db->qn('r2.access_id') . ' = ' . $db->qn('r.access_id'));

		// Query to determine if the access for this userId is mainly department-based (HOD)
		$query->select(Array($db->qn('d.id')))
			->from($db->qn('#__assets', 'a'))
			->innerJoin(
				$db->qn('#__assets', 'ap') . ' ON ' .
				$db->qn('ap.level') . ' <= ' . $db->qn('a.level') . ' AND ' .
				$db->qn('ap.lft') . ' <= ' . $db->qn('a.lft') . ' AND ' .
				$db->qn('ap.rgt') . ' >= ' . $db->qn('a.lft')
			)
			->innerJoin($db->qn('#__redshopb_acl_rule', 'r') . ' ON ' . $db->qn('ap.id') . ' = ' . $db->qn('r.joomla_asset_id'))
			->innerJoin($db->qn('#__redshopb_acl_access', 'ac') . ' ON ' . $db->qn('ac.id') . ' = ' . $db->qn('r.access_id'))
			->innerJoin($db->qn('#__redshopb_role', 'ro') . ' ON ' . $db->qn('ro.id') . ' = ' . $db->qn('r.role_id'))
			->innerJoin($db->qn('#__user_usergroup_map', 'ug') . ' ON ' . $db->qn('ug.group_id') . ' = ' . $db->qn('ro.joomla_group_id'))
			->innerJoin(
				$db->qn('#__redshopb_department', 'd') .
				' ON ' . $db->qn('ap.id') . ' = ' . $db->qn('d.asset_id') .
				' AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
			)
			->where($db->qn('ug.user_id') . ' = ' . (int) $userId)
			->where($db->qn('ac.name') . ' = ' . $db->quote($permission))
			->where($db->qn('ap.level') . ' = (' . $query2->__toString() . ')');
		$db->setQuery($query, 0, 1);

		// If permission is department-based, it now extracts user department
		if ($db->loadResult())
		{
			$query->clear();
			$query->select(Array($db->qn('d.id')))
				->from($db->qn('#__redshopb_user', 'u'))
				->innerJoin(
					$db->qn('#__redshopb_department', 'd') . ' ON ' .
					$db->qn('u.department_id') . ' = ' . $db->qn('d.id') .
					' AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
				)
				->where($db->qn('u.joomla_user_id') . ' = ' . (int) $userId);
			$db->setQuery($query, 0, 1);

			$departmentId = $db->loadResult();

			if (!$departmentId)
			{
				return -1;
			}

			return $departmentId;
		}

		return 0;
	}

	/**
	 * Grants a set of rules, either updating or creating the record
	 *
	 * @param   integer $accessRules Access rules in JSON format (as stored in role_type table)
	 * @param   integer $roleId      Role ID
	 * @param   integer $assetId     Asset ID
	 * @param   integer $granted     (optional) Grant or deny access (1 or 0)
	 *
	 * @return  void
	 */
	protected static function grantACLRules($accessRules, $roleId, $assetId, $granted = 1)
	{
		$aclModel = RedshopbModel::getAdminInstance('ACL');

		// Gets access list with IDs
		$accessList = $aclModel->getAccessList($accessRules);

		if ($accessList)
		{
			// Grant specific permissions
			foreach ($accessList as $access)
			{
				self::grantACLRule($access->id, $roleId, $assetId, $granted);
			}
		}
	}

	/**
	 * Grants a rule of "simple" ACL management, saving on all related records
	 *
	 * @param   integer $accessId Access ID
	 * @param   integer $roleId   Role ID
	 * @param   integer $assetId  Asset ID
	 * @param   integer $granted  (optional) Grant or deny access (1 or 0).  null for reset (delete rule)
	 *
	 * @return  boolean  Success or not
	 */
	public static function grantSimpleACLRule($accessId, $roleId, $assetId, $granted = 1)
	{
		// ACL Model
		$aclModel = RedshopbModel::getAdminInstance('ACL');

		// Checks current simple ACL rule, if it has the same value, skips re-storing it
		$rules = $aclModel->getRuleCollection($roleId, $assetId, '', true, $accessId);

		if ($rules)
		{
			if ($rules[0]->granted == $granted)
			{
				return true;
			}
		}

		$db  = Factory::getDBO();
		$res = true;

		$query = $db->getQuery(true);
		$query->select(
			array(
				$db->qn('eac.id'), $db->qn('sax.scope'),
				'ac.name', 'ac.title',
				'rt.type', $db->qn('rt.name', 'roleTypeName')
			)
		)
			->from($db->qn('#__redshopb_acl_access', 'ac'))
			->join(
				'inner',
				$db->qn('#__redshopb_acl_simple_access_xref', 'sax') . ' ON ' .
				$db->qn('ac.id') . ' = ' .
				$db->qn('sax.simple_access_id')
			)
			->join('inner', $db->qn('#__redshopb_acl_access', 'eac') . ' ON ' . $db->qn('eac.id') . ' = ' . $db->qn('sax.access_id'))
			->join('inner', $db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('sax.role_type_id'))
			->leftJoin($db->qn('#__redshopb_role_type', 'rt') . ' ON rt.id = sax.role_type_id')
			->where($db->qn('ac.id') . ' = ' . $accessId)
			->where($db->qn('r.id') . ' = ' . $roleId);
		$db->setQuery($query);

		$effectiveAccesses = $db->loadObjectList();

		if ($effectiveAccesses)
		{
			foreach ($effectiveAccesses as $effectiveAccess)
			{
				switch ($effectiveAccess->scope)
				{
					case 'global':

						// ROOT asset object (component)
						$rootAsset = Table::getInstance('Asset');
						$rootAsset->load(Array('name' => 'com_redshopb'));
						$res = $res && self::grantACLRule($effectiveAccess->id, $roleId, $rootAsset->get('id'), $granted);
						break;

					case 'company':
						$res = $res && self::grantACLRule($effectiveAccess->id, $roleId, $assetId, $granted);
						break;

					case 'department':
						$query->clear()
							->select(array($db->qn('ad.id')))
							->from($db->qn('#__assets', 'a'))
							->join(
								'inner', $db->qn('#__assets', 'ad') .
								' ON ' . $db->qn('a.lft') . ' < ' . $db->qn('ad.lft') .
								' AND ' . $db->qn('a.rgt') . ' > ' . $db->qn('ad.rgt') .
								' AND ' . $db->qn('a.level') . ' = ' . $db->qn('ad.level') . ' - 1'
							)
							->join(
								'inner', $db->qn('#__redshopb_department', 'd') .
								' ON ' . $db->qn('d.asset_id') . ' = ' . $db->qn('ad.id') .
								' AND ' . $db->qn('d.deleted') . ' = 0 AND ' . $db->qn('d.state') . ' = 1'
							)
							->where($db->qn('a.id') . ' = ' . $assetId);
						$departments = $db->setQuery($query)->loadObjectList();

						if ($departments)
						{
							foreach ($departments as $department)
							{
								$res = $res && self::grantACLRule($effectiveAccess->id, $roleId, $department->id, $granted);
							}
						}
						break;
				}

				Log::add(
					Text::sprintf(
						'COM_REDSHOPB_LOGS_ACL_GRANTED', $granted, $effectiveAccess->scope, $effectiveAccess->id,
						$effectiveAccess->roleTypeName, $effectiveAccess->type,
						$effectiveAccess->name, Text::_($effectiveAccess->title)
					), Log::INFO, 'ACL'
				);
			}
		}

		return $res;
	}

	/**
	 * Grants a rule, either updating or creating the record
	 *
	 * @param   integer $accessId Access ID
	 * @param   integer $roleId   Role ID
	 * @param   integer $assetId  Asset ID
	 * @param   integer $granted  (optional) Grant or deny access (1 or 0).  null for reset (delete rule)
	 *
	 * @return  boolean  Success or not
	 */
	public static function grantACLRule($accessId, $roleId, $assetId, $granted = 1)
	{
		$aclRule = RedshopbTable::getAdminInstance('Acl_Rule');

		// Gets the Rule record if it exists
		$aclRule->id = null;
		$aclRule->reset();
		$ruleData = Array('access_id' => $accessId, 'role_id' => $roleId, 'joomla_asset_id' => $assetId);
		$aclRule->load($ruleData);

		if ($aclRule->id)
		{
			if ($granted === null)
			{
				if (!$aclRule->delete())
				{
					Factory::getApplication()->enqueueMessage($aclRule->getError(), 'error');

					return false;
				}
			}
			else
			{
				// Stores rule granting it
				$aclRule->granted = $granted;

				if (!$aclRule->store())
				{
					Factory::getApplication()->enqueueMessage($aclRule->getError(), 'error');

					return false;
				}
			}
		}
		else
		{
			if ($granted !== null)
			{
				// Creates a new rule with the given values
				$aclRule->id = null;
				$aclRule->reset();
				$ruleData['granted'] = $granted;

				if (!$aclRule->save($ruleData))
				{
					Factory::getApplication()->enqueueMessage($aclRule->getError(), 'error');

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Rebuilds user permissions for assets, according to the ownership and role type
	 *
	 * @param   integer $assetId              Asset ID to rebuild
	 * @param   array   $assetRoles           Array with the roles to check from this asset to grant/deny permissions
	 *                                        to other assets (id, joomla_group_id, allowed_rules,
	 *                                        allowed_roles_main_company, allowed_rules_customers,
	 *                                        allowed_rules_company, allowed_rules_department)
	 * @param   integer $assetType            C for companies, D for departments
	 * @param   integer $limitCompanyLevels   (optional) limit the number of levels of propagation for company level rules
	 * @param   boolean $rebuildingAll        Specifies if it's a full rebuild, to avoid duplicating common tasks
	 *
	 * @return  boolean  Success or not
	 */
	protected static function rebuildAssetACL($assetId, $assetRoles, $assetType, $limitCompanyLevels = 0, $rebuildingAll = true)
	{
		// Part 1: Define this asset's roles permissions for other assets
		$aclModel = RedshopbModel::getAdminInstance('ACL');

		// This asset's object
		$refAsset = $aclModel->getAsset($assetId);

		// ROOT asset object (component)
		$rootAsset = Table::getInstance('Asset');
		$rootAsset->load(Array('name' => 'com_redshopb'));

		// Iterates on company roles (it ensures it is a company)
		if ($assetRoles && $refAsset->company_id)
		{
			foreach ($assetRoles as $role)
			{
				// Roles reset (set everything to false for this role)
				$aclModel->resetAccessRules($role->id);

				// Component level rules: get the rules and combine with root company's rules if it applies
				$componentRules = $role->allowed_rules;

				// For customers and main companies, applies customer rules at global (component) level
				if ($refAsset->company_type == 'customer' || $refAsset->company_type == 'main')
				{
					if ($componentRules != '' && $role->allowed_rules_customers != '')
					{
						$componentRules = json_encode(array_merge(json_decode($componentRules), json_decode($role->allowed_rules_customers)));
					}
					elseif ($role->allowed_rules_customers != '')
					{
						$componentRules = $role->allowed_rules_customers;
					}
				}

				// For main company, applies main company rules at global (component) level
				if ($refAsset->company_type == 'main')
				{
					if ($componentRules != '' && $role->allowed_rules_main_company != '')
					{
						$componentRules = json_encode(array_merge(json_decode($componentRules), json_decode($role->allowed_rules_main_company)));
					}
					elseif ($role->allowed_rules_main_company != '')
					{
						$componentRules = $role->allowed_rules_main_company;
					}
				}

				// Grants rules in B2B schema
				self::grantACLRules($componentRules, $role->id, $rootAsset->id);

				// Applies company rules to the ref Asset
				$companyRules    = $role->allowed_rules_company;
				$ownCompanyRules = $role->allowed_rules_own_company;
				$departmentRules = $role->allowed_rules_department;

				if ($companyRules != '' && $ownCompanyRules != '')
				{
					$companyRules = json_encode(array_merge(json_decode($companyRules), json_decode($ownCompanyRules)));
				}
				elseif ($ownCompanyRules != '')
				{
					$companyRules = $ownCompanyRules;
				}

				self::grantACLRules($companyRules, $role->id, $refAsset->id);

				if ($ownCompanyRules != '')
				{
					// Gets company child assets (first level only to deny own company rules, so we can avoid propagation)
					$childCompanies = $aclModel->getChildAssets($refAsset->id, 1, 'C');

					if ($childCompanies)
					{
						foreach ($childCompanies as $companyAsset)
						{
							self::grantACLRules($ownCompanyRules, $role->id, $companyAsset->id, 0);
						}
					}
				}

				// Gets department child assets (first level only to apply department rules)
				$childDepts = $aclModel->getChildAssets($refAsset->id, 1, 'D');

				if ($childDepts)
				{
					foreach ($childDepts as $deptAsset)
					{
						self::grantACLRules($departmentRules, $role->id, $deptAsset->id);
					}
				}

				/**
				 * Gets company grandchild assets, to deny access to this company's roles (except if it's a root company).
				 * Exact level separation given by $limitCompanyLevels.
				 */
				if ($refAsset->company_type == 'end_customer')
				{
					$grandChildCompanies = $aclModel->getChildAssets($refAsset->id, $limitCompanyLevels, 'C');

					if ($grandChildCompanies)
					{
						// Concatenates Company and Component rules to deny access on grandchilds
						if ($companyRules != '' && $componentRules != '')
						{
							$companyRules = json_encode(array_merge(json_decode($componentRules), json_decode($companyRules)));
						}
						elseif ($componentRules != '')
						{
							$companyRules = $componentRules;
						}

						// Concatenates Company and Department rules to deny access on grandchilds
						if ($companyRules != '' && $departmentRules != '')
						{
							$companyRules = json_encode(array_merge(json_decode($departmentRules), json_decode($companyRules)));
						}
						elseif ($departmentRules != '')
						{
							$companyRules = $departmentRules;
						}

						foreach ($grandChildCompanies as $companyAsset)
						{
							self::grantACLRules($companyRules, $role->id, $companyAsset->id, 0);
						}
					}
				}
			}
		}

		// Part 2: Define other asset's roles (companies) for this asset
		$companyRoles = $aclModel->getCompanyRolesTree();

		foreach ($companyRoles as $role)
		{
			// Checks if it's a child asset to grant access
			if ($refAsset->level > $role->level
				&& $refAsset->lft > $role->lft
				&& $refAsset->rgt < $role->rgt
			)
			{
				// If asset is a company's child department, applies department rules
				if ($assetType == 'D' && $refAsset->level == $role->level + 1)
				{
					$departmentRules = $role->allowed_rules_department;

					self::grantACLRules($departmentRules, $role->id, $refAsset->id);
				}
				// If asset is a grandchild company other than a root asset, denies component, company and department rules
				elseif ($assetType == 'C' && $refAsset->level == $role->level + $limitCompanyLevels)
				{
					$rootCompany = (($role->level - $rootAsset->level) <= 2);

					if (!$rootCompany)
					{
						$componentRules  = $role->allowed_rules;
						$companyRules    = $role->allowed_rules_company;
						$departmentRules = $role->allowed_rules_department;

						// Concatenates Company and Component rules to deny access on grandchilds
						if ($companyRules != '' && $componentRules != '')
						{
							$companyRules = json_encode(array_merge(json_decode($componentRules), json_decode($companyRules)));
						}
						elseif ($componentRules != '')
						{
							$companyRules = $componentRules;
						}

						// Concatenates Company and Department rules to deny access on grandchilds
						if ($companyRules != '' && $departmentRules != '')
						{
							$companyRules = json_encode(array_merge(json_decode($departmentRules), json_decode($companyRules)));
						}
						elseif ($departmentRules != '')
						{
							$companyRules = $departmentRules;
						}

						self::grantACLRules($companyRules, $role->id, $refAsset->id, 0);
					}
				}
				// If asset is a child company, denies own company rules from parent company (to restrict parent company's HODs)
				elseif ($assetType == 'C' && $refAsset->level == $role->level + 1)
				{
					$ownCompanyRules = $role->allowed_rules_own_company;

					self::grantACLRules($ownCompanyRules, $role->id, $refAsset->id, 0);
				}
			}
		}

		self::resetSessionLists();

		return true;
	}

	/**
	 * Rebuilds user permissions for a company, according to the ownership and role type
	 *
	 * @param   integer $companyId Company ID to rebuild
	 *
	 * @return  boolean  Success or not
	 */
	public static function rebuildCompanyACL($companyId)
	{
		RedshopbModel::getFrontInstance('roles')->clearListStaticCache(true);
		$company      = RedshopbEntityCompany::load($companyId);
		$companyRoles = $company->getRoles()->toObjects();

		return self::rebuildAssetACL($company->asset_id, $companyRoles, 'C', 2, false);
	}

	/**
	 * Rebuilds user permissions for a department, according to the ownership and role type
	 *
	 * @param   integer $departmentId Department ID to rebuild
	 *
	 * @return  boolean  Success or not
	 */
	public static function rebuildDepartmentACL($departmentId)
	{
		/** @var RedshopbModelACL $aclModel */
		$aclModel   = RedshopbModel::getAdminInstance('ACL');
		$department = $aclModel->getDepartment($departmentId);

		return self::rebuildAssetACL($department->asset_id, null, 'D', 0, false);
	}

	/**
	 * Rebuilds ACL base permissions
	 *
	 * @return        boolean        Success or not
	 */
	public static function rebuildACLBase()
	{
		$aclModel = RedshopbModel::getAdminInstance('ACL');

		// Initializes ACL groups in case they do not exist
		self::initializeACL();

		// Empties the ACL rules table
		$aclModel->emptyACLRules();

		// Rsets the session lists to ensure right session listing on the next query
		self::resetSessionLists();

		return true;
	}

	/**
	 * Initializes ACL in the install process
	 *
	 * @return        boolean        Success or not
	 */
	public static function initializeACL()
	{
		/** @var TableUsergroup $groupTable */
		$groupTable = Table::getInstance('Usergroup');

		/** @var RedshopbTableRole $roleTable */
		$roleTable = RedshopbTable::getAdminInstance('Role');

		// Checks if the primary group for redSHOPB2B exists or it creates it
		if (!$groupTable->load(
			array(
				'title'     => 'redSHOPB2B',
				'parent_id' => 1,
			)
		)
		)
		{
			if (!$groupTable->save(
				array(
					'title'     => 'redSHOPB2B',
					'parent_id' => 1,
				)
			)
			)
			{
				Factory::getApplication()->enqueueMessage($groupTable->getError(), 'error');

				return false;
			}
		}

		$mainGroupId = $groupTable->id;

		// Get the role type ids
		$types = RedshopbHelperRole::getTypeIds();

		// Empties root component rules (reset)
		$rootAsset = Table::getInstance('Asset');
		$rootAsset->load(Array('name' => 'com_redshopb'));
		$rootAssetRules = new Rules('');

		foreach ($types as $type)
		{
			$groupTable->id = null;
			$groupTable->reset();
			$roleTable->id = null;
			$roleTable->reset();

			// Creates each main Joomla group under the redSHOPB2B group
			if (!$groupTable->load(
				array(
					'title'     => $type->name,
					'parent_id' => $mainGroupId,
				)
			)
			)
			{
				if (!$groupTable->save(
					array(
						'title'     => $type->name,
						'parent_id' => $mainGroupId,
					)
				)
				)
				{
					Factory::getApplication()->enqueueMessage($groupTable->getError(), 'error');

					return false;
				}
			}

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select(array($db->qn('id'), $db->qn('joomla_group_id')))
				->from($db->qn('#__redshopb_role'))
				->where($db->qn('role_type_id') . ' = ' . $type->id)
				->where($db->qn('company_id') . ' IS NULL');
			$db->setQuery($query);

			$roleRecord = $db->loadObject();

			// If the role doesn't exist, it creates it
			if (!$roleRecord)
			{
				if (!$roleTable->save(
					array(
						'company_id'      => null,
						'joomla_group_id' => $groupTable->id,
						'role_type_id'    => $type->id,
					)
				)
				)
				{
					Factory::getApplication()->enqueueMessage($roleTable->getError(), 'error');

					return false;
				}
			}

			$allowedRules = json_decode($type->allowed_rules);

			if ($allowedRules)
			{
				foreach ($allowedRules as $rule)
				{
					if (substr($rule, 0, 5) == 'core.')
					{
						$rootAssetRules->mergeAction($rule, array($groupTable->id => true));
					}
				}
			}
		}

		// Stores component Joomla rules
		$rootAsset->rules = (string) $rootAssetRules;

		if (!$rootAsset->store())
		{
			Factory::getApplication()->enqueueMessage($rootAsset->getError(), 'error');

			return false;
		}
	}

	/**
	 * Helper to process a DB object list with id and name fields, converting it to the selected output
	 *
	 * @param   array  $rows                                  DB array of rows with id and name properties
	 * @param   string $listType                              'dropdown' for Dropdown Select Box with items
	 *                                                        'comma' for comma separated IDs for dB
	 * @param   string $listName                              optional    name for the dropdown
	 * @param   string $tagProperties                         optional    HTML tag properties for the dropdown
	 *
	 * @return  string        List of objects in the format selected
	 */
	protected static function processOutputList($rows, $listType, $listName = '', $tagProperties = '')
	{
		$objects = Array();

		if ($rows)
		{
			foreach ($rows as $row)
			{
				switch ($listType)
				{
					case 'dropdown':
						$objects[] = array('id' => $row->id, 'name' => $row->name);
						break;

					case 'comma':
						$objects[] = $row->id;
						break;

					default:
						break;
				}
			}
		}

		switch ($listType)
		{
			case 'dropdown':
				$dropdown = HTMLHelper::_('select.genericlist', $objects, $listName, $tagProperties, 'id', 'name');

				return $dropdown;
			case 'comma':
				return implode(',', $objects);
			default:
				return '';
		}
	}

	/**
	 * Check allow display view
	 *
	 * @param   string $view      View name
	 * @param   string $failScope Returns name fail scope
	 *
	 * @return  boolean
	 */
	public static function allowDisplayView($view, &$failScope = null)
	{
		if (empty($view) || static::isSuperAdmin())
		{
			return true;
		}

		// Get views which don`t need login
		if (!in_array($view, RedshopbHelperCommon::getNoLoginViews())
			&& !self::getPermission('manage', '', array(), true, 0, 'core')
		)
		{
			$failScope = 'login';

			return false;
		}

		$isFromMainCompany = false;
		$rsbUserId         = RedshopbHelperUser::getUserRSid();

		if ($rsbUserId)
		{
			$isFromMainCompany = RedshopbHelperUser::isFromMainCompany($rsbUserId, 'employee');
		}

		if (in_array($view, RedshopbHelperCommon::getPriceViews()))
		{
			$config = RedshopbApp::getConfig();
			$isShop = RedshopbHelperPrices::displayPrices();

			if ($isFromMainCompany ? !$config->getInt('show_price', 1) : (!$isShop && $isShop !== false))
			{
				$failScope = 'price';

				return false;
			}
		}

		if (in_array($view, array('offer', 'offers', 'myoffer', 'myoffers'))
			&& !RedshopbEntityConfig::getInstance()->getInt('enable_offer', 1)
		)
		{
			$failScope = 'offer';

			return false;
		}

		if ($isFromMainCompany
			&& in_array(
				$view,
				array(
					'mywallet',
					'myoffers'
				)
			)
		)
		{
			$failScope = 'main_company';

			return false;
		}

		if (strcmp($view, "import_cart") === 0)
		{
			if (static::getPermission('import', 'order'))
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		$views = array(
			'offers'                  => 'product',
			'users'                   => 'user',
			'addresses'               => 'address',
			'companies'               => 'company',
			'departments'             => 'department',
			'manufacturers'           => 'product',
			'collections'             => 'mainwarehouse',
			'products'                => 'product',
			'stockrooms'              => 'company',
			'all_discounts'           => 'product',
			'discount_debtor_groups'  => 'product',
			'product_discount_groups' => 'product',
			'all_prices'              => 'product',
			'price_debtor_groups'     => 'product',
			'categories'              => 'category',
			'orders'                  => 'order',
			'return_orders'           => 'order',
			'layouts'                 => 'layout',
			'tags'                    => 'tag',
			'wash_care_specs'         => 'mainwarehouse',
			'fields'                  => 'mainwarehouse',
			'field_groups'            => 'mainwarehouse',
			'filter_fieldsets'        => 'product',
			'newsletter_lists'        => 'mainwarehouse',
			'newsletters'             => 'mainwarehouse',
			'shipping_rates'          => 'product',
			'reports'                 => 'product',
			'templates'               => 'product',
			'table_locks'             => 'company',
			'layout_list'             => 'product',
			'layout_item'             => 'product',
			'unit_measures'           => 'product',
			'words'                   => 'mainwarehouse',
			'taxes'                   => 'product',
			'tax_groups'              => 'product',
			'currencies'              => 'product',
			'states'                  => 'product',
			'countries'               => 'product',
			'holidays'                => 'mainwarehouse',
			'free_shippings'          => 'product'
		);

		RFactory::getDispatcher()->trigger('onAfterComRedshopbGetViewsACL', array(&$views));

		if (array_key_exists($view, $views))
		{
			$result = self::getViewPermissions($views[$view]);

			if (!($result === true))
			{
				$failScope = 'acl';

				return false;
			}

			return true;
		}

		$pluralize = RInflector::pluralize($view);

		if (array_key_exists($pluralize, $views))
		{
			$result = self::getViewPermissions($views[$pluralize]);

			if (!($result === true))
			{
				$failScope = 'acl';

				return false;
			}

			return true;
		}

		return true;
	}

	/**
	 * Get an array with the permissions granted to the logged in user from the redSHOP B2B default options
	 *
	 * @param   string $permission $permission name
	 *
	 * @return  array|boolean
	 */
	public static function getViewPermissions($permission = '')
	{
		static $permissions;

		if (!$permissions)
		{
			$permissions['user']          = self::getPermission('view', 'user')
				|| self::getPermission('manage', 'user', array(), true);
			$permissions['address']       = self::getPermission('view', 'address')
				|| self::getPermission('manage', 'address', array(), true);
			$permissions['company']       = self::getPermission('view', 'company')
				|| self::getPermission('manage', 'company', array(), true);
			$permissions['department']    = self::getPermission('view', 'department')
				|| self::getPermission('manage', 'department', array(), true);
			$permissions['collection']    = self::getPermission('view', 'collection')
				|| self::getPermission('manage', 'collection', array(), true);
			$permissions['product']       = self::getPermission('view', 'product')
				|| self::getPermission('manage', 'product', array(), true);
			$permissions['category']      = self::getPermission('view', 'category')
				|| self::getPermission('manage', 'category', array(), true);
			$permissions['layout']        = self::getPermission('view', 'layout')
				|| self::getPermission('manage', 'layout', array(), true);
			$permissions['tag']           = self::getPermission('view', 'tag')
				|| self::getPermission('manage', 'tag', array(), true);
			$permissions['mainwarehouse'] = self::getPermission('view', 'mainwarehouse')
				|| self::getPermission('manage', 'mainwarehouse', array(), true);
			$permissions['order']         = self::getPermission('view', 'order')
				|| self::getPermission('manage', 'order', array(), true);
		}

		if ($permission)
		{
			if (array_key_exists($permission, $permissions))
			{
				return $permissions[$permission];
			}
			else
			{
				return true;
			}
		}
		else
		{
			return $permissions;
		}
	}
}
