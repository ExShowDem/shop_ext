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
use Joomla\CMS\Table\Usergroup;
/**
 * A Role helper.
 *
 * @package     Aesir.E-Commerce.Libraries
 * @subpackage  Helper
 * @since       1.0
 */
final class RedshopbHelperRole
{
	/**
	 * Get the role types ids.
	 *
	 * @param   boolean  $companyRole  optional		Return only the company role
	 *
	 * @return  array  An array of role type id as keys and name as values.
	 */
	public static function getTypeIds($companyRole = false)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select(
				array(
					$db->qn('id'),
					$db->qn('name'),
					$db->qn('allow_access'),
					$db->qn('allowed_rules')
				)
			)
			->from($db->qn('#__redshopb_role_type'))
			->where($db->qn('id') . ' > 0')
			->where($db->qn('company_role') . ' = ' . ($companyRole ? '1' : '0'))
			->where($db->qn('hidden') . ' = 0');

		$db->setQuery($query);

		// If it wants the company role only, it returns its ID
		if ($companyRole)
		{
			return $db->loadResult();
		}

		// Otherwise, returns the whole object list
		$ids = $db->loadObjectList();

		if (!is_array($ids))
		{
			return array();
		}

		return $ids;
	}

	/**
	 * Get the first joomla group id for a given role type id and company id.
	 *
	 * @param   integer  $companyId     The company id.
	 * @param   integer  $roleTypeId    The role type id.
	 * @param   string   $roleTypeType  The role type 'type'.
	 *
	 * @return  mixed  The joomla group id or null.
	 */
	public static function getJoomlaGroupId($companyId, $roleTypeId = null, $roleTypeType = '')
	{
		static $joomlaGroupIds = array();

		if ($roleTypeId && isset($joomlaGroupIds[$companyId][$roleTypeId]))
		{
			return $joomlaGroupIds[$companyId][$roleTypeId];
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('r.joomla_group_id')
			->from($db->qn('#__redshopb_role', 'r'))
			->join('inner', $db->qn('#__redshopb_role_type', 'rt') . ' ON rt.id = r.role_type_id AND rt.hidden = 0')
			->join('left', $db->qn('#__usergroups', 'ug') . ' ON ug.id = r.joomla_group_id')
			->where('ug.id IS NOT NULL')
			->where('r.company_id = ' . (int) $companyId);

		if ($roleTypeId)
		{
			$query->where('r.role_type_id = ' . (int) $roleTypeId);
		}

		if ($roleTypeType)
		{
			$query->where('rt.type = ' . $db->q($roleTypeType));
		}

		$db->setQuery($query);
		$joomlaGroupId = $db->loadResult();

		if ($joomlaGroupId)
		{
			if ($roleTypeId)
			{
				$joomlaGroupIds[$companyId][$roleTypeId] = (int) $joomlaGroupId;

				return $joomlaGroupIds[$companyId][$roleTypeId];
			}

			return $joomlaGroupId;
		}

		if ($roleTypeId)
		{
			$joomlaGroupId = self::createCompanyRoleGroup($companyId, $roleTypeId);

			if ($joomlaGroupId)
			{
				$joomlaGroupIds[$companyId][$roleTypeId] = (int) $joomlaGroupId;

				return $joomlaGroupIds[$companyId][$roleTypeId];
			}
		}

		return null;
	}

	/**
	 * Get the first joomla group id for a given role type id and company id.
	 *
	 * @param   integer  $companyId   The company id.
	 * @param   integer  $roleTypeId  The role type id.
	 *
	 * @return  mixed  The joomla group id or null.
	 */
	public static function createCompanyRoleGroup($companyId, $roleTypeId)
	{
		$db     = Factory::getDbo();
		$roleId = 0;

		/** @var RedshopbTableUsergroup $groupTable */
		$groupTable = RedshopbTable::getAdminInstance('Usergroup');
		$groupTable->setOption('disableReorder', false);

		/** @var RedshopbTableRole $roleTable */
		$roleTable = RedshopbTable::getAdminInstance('Role');

		/** @var RedshopbEntityCompany $company */
		$company = RedshopbEntityCompany::getInstance($companyId)->loadItem();

		// Find the parent group Id for the role type to be created
		$query         = $db->getQuery(true)
			->select('joomla_group_id')
			->from('#__redshopb_role')
			->where($db->qn('role_type_id') . ' = ' . $roleTypeId)
			->where($db->qn('company_id') . ' IS NULL');
		$parentGroupId = $db->setQuery($query)->loadResult();

		if (!$parentGroupId)
		{
			$parentGroupId = 1;
		}

		// Prepare the group title
		$groupTitle = (
			$company->get('customer_number', '') != '' ? $company->get('customer_number') . ' ' : '') .
			$company->get('name') .
			($company->get('name2', '') != '' ? ' ' . $company->get('name2', '') : '');
		$groupTitle = $company->getUniqueUserGroupName($groupTitle, $parentGroupId);

		if (!$groupTable->load(
			array(
				'title' => $groupTitle,
				'parent_id' => $parentGroupId,
			)
		))
		{
			if (!$groupTable->save(
				array(
					'title' => $groupTitle,
					'parent_id' => $parentGroupId,
				)
			))
			{
				return null;
			}
		}

		if ($roleTable->load(array('role_type_id' => $roleTypeId, 'company_id' => $companyId)))
		{
			$roleId = $roleTable->get('id');
		}

		// Create the role.
		if (!$roleTable->save(
			array(
				'id'              => $roleId,
				'role_type_id'    => $roleTypeId,
				'company_id'      => $companyId,
				'joomla_group_id' => $groupTable->id,
			)
		))
		{
			return null;
		}

		// Rebuilds ACL for the company
		RedshopbHelperACL::rebuildCompanyACL($companyId);

		return $groupTable->id;
	}

	/**
	 * Get the role type id for a given company and group.
	 *
	 * @param   integer  $companyId      The company id.
	 * @param   integer  $joomlaGroupId  The joomla group id.
	 *
	 * @return  mixed  The role type id or null.
	 */
	public static function getRoleTypeId($companyId, $joomlaGroupId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('role_type_id')
			->from('#__redshopb_role')
			->where('company_id = ' . $db->quote($companyId))
			->where('joomla_group_id = ' . $db->quote($joomlaGroupId));

		$db->setQuery($query);

		$roleTypeId = $db->loadResult();

		if (!empty($roleTypeId))
		{
			return (int) $roleTypeId;
		}

		return null;
	}

	/**
	 * Get the role type id for a given user in a company.
	 *
	 * @param   integer  $companyId     The company id.
	 * @param   integer  $joomlaUserId  The joomla user id.
	 *
	 * @return  mixed  The role type id or null.
	 */
	public static function getUserRoleTypeId($companyId, $joomlaUserId = null)
	{
		if (is_null($joomlaUserId))
		{
			$joomlaUserId = Factory::getUser()->id;
		}

		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('rt.id')
			->from('#__redshopb_role_type AS rt')
			->innerJoin('#__redshopb_role AS r ON r.role_type_id = rt.id')
			->where('r.company_id = ' . $db->quote($companyId))
			->innerJoin('#__user_usergroup_map AS map ON map.group_id = r.joomla_group_id')
			->where('map.user_id = ' . $db->quote($joomlaUserId));

		$db->setQuery($query);

		$roleTypeId = $db->loadResult();

		if (empty($roleTypeId))
		{
			return null;
		}

		return (int) $roleTypeId;
	}

	/**
	 * Get the role ID based on name
	 *
	 * @param   string  $roleName  The role name
	 *
	 * @return  integer  The integer of the respective role
	 */
	public static function getRoleIdByName($roleName)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('rt.id')
			->from($db->quoteName('#__redshopb_role_type', 'rt'))
			->where(
				$db->quoteName('rt.name') . ' LIKE ' .
				$db->quote('%' . $roleName . '%')
			);
		$db->setQuery($query);

		$roleTypeId = $db->loadResult();

		if (empty($roleTypeId))
		{
			return null;
		}

		return (int) $roleTypeId;
	}

	/**
	 * Get the id of the admin role of a given company
	 *
	 * @param   integer  $companyId  The company id
	 * @param   string   $type       The role type to select
	 *
	 * @return  integer The integer of the role
	 */
	public static function getCompanyRoleIdByType($companyId, $type)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('r.id')
			->from($db->qn('#__redshopb_role', 'r'))
			->join('inner', $db->qn('#__redshopb_role_type', 'rt') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('rt.id'))
			->where($db->qn('r.company_id') . ' = ' . (int) $companyId)
			->where($db->qn('rt.type') . ' = ' . $db->q($type));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Determines if the Role type is limited (for sales persons) or regular
	 *
	 * @param   integer  $roleId  Role ID
	 *
	 * @return  boolean
	 */
	public static function isRoleLimited($roleId)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('rt.limited')
			->from($db->qn('#__redshopb_role_type', 'rt'))
			->join('inner', $db->qn('#__redshopb_role', 'r') . ' ON ' . $db->qn('r.role_type_id') . ' = ' . $db->qn('rt.id'))
			->where($db->qn('r.id') . ' = ' . (int) $roleId);
		$db->setQuery($query);

		$limited = $db->loadResult();

		if ($limited == 1)
		{
			return true;
		}

		return false;
	}
}
